<?php
namespace app\controllers;

use Yii;
use app\models\FighterPhoto;
use app\models\Fighter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;

class PhotoController extends Controller
{
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    const THUMBNAIL_WIDTH = 400;
    const THUMBNAIL_HEIGHT = 400;
    const CACHE_DURATION = 86400; // 24 часа

    /**
     * Проверка роли пользователя
     */
    private function checkUserRole($allowedRoles)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        $userRole = Yii::$app->user->identity->role;
        return in_array($userRole, (array)$allowedRoles);
    }

    /**
     * Проверка прав доступа к бойцу
     */
    private function checkFighterAccess($fighter)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        // Владелец бойца имеет полный доступ
        if ($fighter->user_id == Yii::$app->user->id) {
            return true;
        }
        
        // Модераторы и админы имеют доступ ко всем бойцам
        return $this->checkUserRole(['Moderator', 'Admin', 'Develop']);
    }

    /**
     * Проверка прав модератора
     */
    private function isModerator()
    {
        return $this->checkUserRole(['Moderator', 'Admin', 'Develop']);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'thumbnail'],
                        'roles' => ['?', '@'], // Все пользователи
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'upload', 'set-main', 'delete'],
                        'roles' => ['@'], // Только авторизованные
                    ],
                    [
                        'allow' => true,
                        'actions' => ['moderate', 'bulk-moderate'],
                        'roles' => ['@'], // Проверка роли будет в методах
                    ],
                ],
            ],
        ];
    }

    /**
     * Список фотографий бойца
     */
    public function actionIndex($fighterId)
    {
        $fighterId = (int)$fighterId;
        
        // Проверяем существование бойца
        $fighter = Fighter::findOne($fighterId);
        if (!$fighter) {
            throw new NotFoundHttpException('Боец не найден.');
        }
        
        // Проверяем права доступа
        if (!$this->checkFighterAccess($fighter)) {
            throw new \yii\web\ForbiddenHttpException('У вас нет доступа к фотографиям этого бойца.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => FighterPhoto::find()
                ->where(['fighter_id' => $fighterId])
                ->orderBy(['is_main' => SORT_DESC, 'created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'fighter' => $fighter,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр фотографии
     */
    public function actionView($id)
    {
        $startTime = microtime(true);
        $id = (int)$id;
        
        if ($id <= 0) {
            throw new NotFoundHttpException('Неверный идентификатор фотографии');
        }
        
        try {
            $photo = $this->findModel($id);
            $fighter = $photo->fighter;
            
            // Проверяем права доступа для немодерированных фото
            if ($photo->status !== FighterPhoto::STATUS_APPROVED && !$this->checkFighterAccess($fighter)) {
                Yii::warning("Попытка доступа к немодерированной фотографии ID: $id пользователем: " . Yii::$app->user->id);
                throw new \yii\web\ForbiddenHttpException('Фотография находится на модерации');
            }

            // Добавляем заголовки безопасности
            Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
            Yii::$app->response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->set('Content-Type', $photo->mime_type);
            Yii::$app->response->headers->set('Content-Length', strlen($photo->photo_data));
            Yii::$app->response->headers->set('Cache-Control', 'public, max-age=' . self::CACHE_DURATION);
            Yii::$app->response->headers->set('Pragma', 'cache');
            
            // Логируем успешный доступ
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Yii::info("Фото ID: $id показано за {$duration}ms");
            
            return $photo->photo_data;
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Yii::error("Ошибка при показе фото ID: $id - {$e->getMessage()} за {$duration}ms");
            throw $e;
        }
    }

    /**
     * Загрузка новой фотографии для бойца
     */
    public function actionUpload($fighterId)
    {
        $fighterId = (int)$fighterId;
        
        $fighter = Fighter::findOne($fighterId);
        if (!$fighter) {
            throw new NotFoundHttpException('Боец не найден.');
        }
        
        // Проверяем права доступа
        if (!$this->checkFighterAccess($fighter)) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для загрузки фотографий этого бойца.');
        }

        $model = new FighterPhoto();
        $model->fighter_id = $fighterId;
        $model->scenario = 'upload'; // Устанавливаем сценарий

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->photo_file = UploadedFile::getInstance($model, 'photo_file');
            
            if ($model->photo_file) {
                // Используем метод uploadToDb из модели для создания миниатюры
                if ($model->uploadToDb() && $model->save()) {
                    Yii::info("Пользователь " . Yii::$app->user->id . " загрузил фото ID: {$model->id} для бойца ID: $fighterId");
                    Yii::$app->session->setFlash('success', 'Фотография успешно загружена и отправлена на модерацию');
                    return $this->redirect(['index', 'fighterId' => $fighterId]);
                } else {
                    Yii::error('Ошибка сохранения фото: ' . implode(', ', $model->getFirstErrors()));
                    Yii::$app->session->setFlash('error', 'Ошибка при сохранении фотографии: ' . implode(', ', $model->getFirstErrors()));
                }
            } else {
                Yii::$app->session->setFlash('error', 'Необходимо выбрать файл для загрузки');
            }
        }

        return $this->render('upload', [
            'model' => $model,
            'fighter' => $fighter,
        ]);
    }

    /**
     * Редактирование описания фотографии
     */
    public function actionUpdate($id)
    {
        $photo = $this->findModel($id);
        $fighter = $photo->fighter;
        
        // Проверяем права доступа
        if (!$this->checkFighterAccess($fighter)) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для редактирования этой фотографии.');
        }

        if (Yii::$app->request->isPost) {
            $photo->load(Yii::$app->request->post());
            if ($photo->save()) {
                Yii::info("Пользователь " . Yii::$app->user->id . " отредактировал фото ID: $id");
                Yii::$app->session->setFlash('success', 'Описание фотографии обновлено');
                return $this->redirect(['index', 'fighterId' => $fighter->id]);
            }
        }

        return $this->render('update', [
            'model' => $photo,
            'fighter' => $fighter,
        ]);
    }
    
    /**
     * Просмотр миниатюры
     */
    public function actionThumbnail($id)
    {
        $startTime = microtime(true);
        $id = (int)$id;
        
        if ($id <= 0) {
            throw new NotFoundHttpException('Неверный идентификатор фотографии');
        }
        
        try {
            $photo = $this->findModel($id);
            $fighter = $photo->fighter;
            
            if ($photo->status !== FighterPhoto::STATUS_APPROVED && !$this->checkFighterAccess($fighter)) {
                Yii::warning("Попытка доступа к миниатюре немодерированной фотографии ID: $id пользователем: " . Yii::$app->user->id);
                throw new \yii\web\ForbiddenHttpException('Фотография находится на модерации');
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            
            // Используем миниатюру из базы данных
            $imageData = $photo->thumbnail_data;
            $mimeType = 'image/jpeg'; // Миниатюры всегда JPEG
            
            // Если миниатюры нет в базе, создаем на лету
            if (!$imageData) {
                $imageData = $this->createThumbnailOnFly($photo);
            }
            
            // Добавляем заголовки безопасности
            Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
            Yii::$app->response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            Yii::$app->response->headers->set('Content-Type', $mimeType);
            Yii::$app->response->headers->set('Content-Length', strlen($imageData));
            Yii::$app->response->headers->set('Cache-Control', 'public, max-age=' . self::CACHE_DURATION);
            
            // Логируем успешный доступ
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Yii::info("Миниатюра фото ID: $id показана за {$duration}ms");
            
            return $imageData;
            
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Yii::error("Ошибка при показе миниатюры фото ID: $id - {$e->getMessage()} за {$duration}ms");
            throw $e;
        }
    }

    /**
     * Установка фотографии как основной
     */
    public function actionSetMain($id)
    {
        $photo = $this->findModel($id);
        $fighter = $photo->fighter;
        
        // Проверяем права доступа
        if (!$this->checkFighterAccess($fighter)) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для изменения этого бойца.');
        }
        
        // Проверяем, что фото утверждено
        if ($photo->status != FighterPhoto::STATUS_APPROVED) {
            Yii::$app->session->setFlash('error', 'Нельзя установить немодерированную фотографию как основную');
            return $this->redirect(['index', 'fighterId' => $fighter->id]);
        }

        if ($photo->setAsMain()) {
            Yii::info("Пользователь " . Yii::$app->user->id . " установил фото ID: $id как основное для бойца ID: {$fighter->id}");
            Yii::$app->session->setFlash('success', 'Основная фотография успешно изменена');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при изменении основной фотографии');
        }

        return $this->redirect(['index', 'fighterId' => $fighter->id]);
    }

    /**
     * Удаление фотографии
     */
    public function actionDelete($id)
    {
        $photo = $this->findModel($id);
        $fighter = $photo->fighter;
        
        // Проверяем права доступа
        if (!$this->checkFighterAccess($fighter)) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для удаления этой фотографии.');
        }

        if ($photo->delete()) {
            Yii::info("Пользователь " . Yii::$app->user->id . " удалил фото ID: $id бойца ID: {$fighter->id}");
            Yii::$app->session->setFlash('success', 'Фотография успешно удалена');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении фотографии');
        }

        return $this->redirect(['index', 'fighterId' => $fighter->id]);
    }

    /**
     * Модерация фотографии
     */
    public function actionModerate($id, $status)
    {
        // Проверяем права доступа
        if (!$this->isModerator()) {
            throw new \yii\web\ForbiddenHttpException('Нет прав для модерации');
        }
        
        $photo = $this->findModel($id);
        $oldStatus = $photo->status;
        
        if (!in_array($status, [FighterPhoto::STATUS_APPROVED, FighterPhoto::STATUS_REJECTED])) {
            throw new \yii\web\BadRequestHttpException('Неверный статус');
        }
        
        if ($status == FighterPhoto::STATUS_APPROVED) {
            $result = $photo->approve(Yii::$app->user->id);
        } else {
            $result = $photo->reject(Yii::$app->user->id);
        }
        
        if ($result) {
            Yii::info("Модератор " . Yii::$app->user->id . " изменил статус фото ID: {$photo->id} с {$oldStatus} на {$status}");
            Yii::$app->session->setFlash('success', 'Статус фотографии обновлен');
        } else {
            Yii::error('Ошибка обновления статуса фото: ' . implode(', ', $photo->getFirstErrors()));
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении статуса');
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: ['index', 'fighterId' => $photo->fighter_id]);
    }

    /**
     * Массовая модерация
     */
    public function actionBulkModerate()
    {
        // Проверяем права доступа
        if (!$this->isModerator()) {
            throw new \yii\web\ForbiddenHttpException('Нет прав для модерации');
        }
        
        if (Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('ids', []);
            $status = Yii::$app->request->post('status');
            
            if (empty($ids) || !in_array($status, [FighterPhoto::STATUS_APPROVED, FighterPhoto::STATUS_REJECTED])) {
                Yii::$app->session->setFlash('error', 'Неверные параметры запроса');
                return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
            }
            
            $count = FighterPhoto::updateAll([
                'status' => $status,
                'moderated_by' => Yii::$app->user->id,
                'moderated_at' => new \yii\db\Expression('NOW()')
            ], ['id' => $ids]);
            
            Yii::info("Модератор " . Yii::$app->user->id . " массово изменил статус {$count} фото на {$status}");
            Yii::$app->session->setFlash('success', "Обновлено {$count} фотографий");
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
    }

    /**
     * Создание миниатюры на лету с кешированием
     */
    private function createThumbnailOnFly($photo)
    {
        // Проверяем кеш
        $cacheKey = "thumbnail_{$photo->id}_v{$photo->updated_at}";
        $cachedThumbnail = Yii::$app->cache->get($cacheKey);
        
        if ($cachedThumbnail !== false) {
            return $cachedThumbnail;
        }
        
        $tempFile = null;
        
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'thumb_');
            file_put_contents($tempFile, $photo->photo_data);
            
            // Проверяем, является ли файл валидным изображением
            $imageInfo = @getimagesize($tempFile);
            if (!$imageInfo) {
                throw new \Exception('Некорректный файл изображения');
            }
            
            $image = \yii\imagine\Image::getImagine()->open($tempFile);
            
            // Ограничиваем максимальный размер для предотвращения DoS
            $size = $image->getSize();
            if ($size->getWidth() > 5000 || $size->getHeight() > 5000) {
                throw new \Exception('Изображение слишком большое для обработки');
            }
            
            $thumbnail = $image->thumbnail(
                new \Imagine\Image\Box(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT),
                \Imagine\Image\ImageInterface::THUMBNAIL_INSET
            );
            
            $thumbnailData = $thumbnail->get('jpg', ['quality' => 85]);
            
            // Сохраняем в кеш
            Yii::$app->cache->set($cacheKey, $thumbnailData, self::CACHE_DURATION);
            
            // Сохраняем миниатюру в базу данных
            $this->updateThumbnailInDatabase($photo->id, $thumbnailData);
            
            return $thumbnailData;
            
        } catch (\Imagine\Exception\RuntimeException $e) {
            Yii::error('Ошибка обработки изображения: ' . $e->getMessage());
            return $this->getPlaceholderImage();
        } catch (\Exception $e) {
            Yii::error('Ошибка создания миниатюры: ' . $e->getMessage());
            return $this->getPlaceholderImage();
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Обновление миниатюры в базе данных
     */
    private function updateThumbnailInDatabase($photoId, $thumbnailData)
    {
        try {
            $photo = FighterPhoto::findOne($photoId);
            if ($photo) {
                $photo->thumbnail_data = $thumbnailData;
                $photo->save(false, ['thumbnail_data']);
            }
        } catch (\Exception $e) {
            Yii::error('Ошибка сохранения миниатюры в БД: ' . $e->getMessage());
        }
    }

    /**
     * Заглушка для битых изображений
     */
    private function getPlaceholderImage()
    {
        $placeholder = imagecreate(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        $backgroundColor = imagecolorallocate($placeholder, 240, 240, 240);
        $textColor = imagecolorallocate($placeholder, 150, 150, 150);
        
        $text = 'Image not available';
        $textWidth = imagefontwidth(5) * strlen($text);
        $x = (self::THUMBNAIL_WIDTH - $textWidth) / 2;
        $y = (self::THUMBNAIL_HEIGHT - imagefontheight(5)) / 2;
        
        imagestring($placeholder, 5, $x, $y, $text, $textColor);
        
        ob_start();
        imagejpeg($placeholder);
        $imageData = ob_get_clean();
        imagedestroy($placeholder);
        
        return $imageData;
    }

    protected function findModel($id)
    {
        if (($model = FighterPhoto::findOne($id)) !== null) {
            return $model;
        }
        
        Yii::warning("Попытка доступа к несуществующей фотографии ID: $id");
        throw new NotFoundHttpException('Фотография не найдена.');
    }

    public function actionTest($id)
    {
        $photo = FighterPhoto::findOne($id);
        if (!$photo) {
            return "Фото не найдено";
        }
    
        return $this->render('test', [
            'photo' => $photo
        ]);
    }

}
