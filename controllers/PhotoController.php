<?php
namespace app\controllers;

use Yii;
use app\models\FighterPhoto;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

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

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'thumbnail'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['upload'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['moderate', 'bulk-moderate'],
                        'roles' => ['moderator'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($id)
    {
        $startTime = microtime(true);
        $id = (int)$id;
        
        if ($id <= 0) {
            throw new NotFoundHttpException('Неверный идентификатор фотографии');
        }
        
        try {
            $photo = $this->findModel($id);
            
            // Проверяем права доступа
            if ($photo->status !== FighterPhoto::STATUS_APPROVED && !Yii::$app->user->can('moderator')) {
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

    public function actionThumbnail($id)
    {
        $startTime = microtime(true);
        $id = (int)$id;
        
        if ($id <= 0) {
            throw new NotFoundHttpException('Неверный идентификатор фотографии');
        }
        
        try {
            $photo = $this->findModel($id);
            
            if ($photo->status !== FighterPhoto::STATUS_APPROVED && !Yii::$app->user->can('moderator')) {
                Yii::warning("Попытка доступа к миниатюре немодерированной фотографии ID: $id пользователем: " . Yii::$app->user->id);
                throw new \yii\web\ForbiddenHttpException('Фотография находится на модерации');
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            
            // Используем миниатюру если есть, иначе создаем на лету
            $imageData = $photo->thumbnail_data;
            $mimeType = 'image/jpeg'; // Миниатюры всегда JPEG
            
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
            
            // Сохраняем миниатюру в базу данных (асинхронно если возможно)
            $this->updateThumbnailInDatabase($photo->id, $thumbnailData);
            
            return $thumbnailData;
            
        } catch (\Imagine\Exception\RuntimeException $e) {
            Yii::error('Ошибка обработки изображения: ' . $e->getMessage());
            // Возвращаем заглушку для битых изображений
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

    /**
     * Загрузка новой фотографии
     */
    public function actionUpload()
    {
        if (!Yii::$app->user->can('uploadPhoto')) {
            throw new \yii\web\ForbiddenHttpException('Нет прав для загрузки фотографий');
        }
        
        $model = new FighterPhoto();
        
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $uploadedFile = UploadedFile::getInstance($model, 'photo_file');
            
            if ($uploadedFile) {
                // Проверка размера файла
                if ($uploadedFile->size > self::MAX_FILE_SIZE) {
                    Yii::$app->session->setFlash('error', 'Размер файла не должен превышать 10MB');
                    return $this->refresh();
                }
                
                // Проверка типа файла
                if (!in_array($uploadedFile->type, self::ALLOWED_MIME_TYPES)) {
                    Yii::$app->session->setFlash('error', 'Недопустимый формат файла. Разрешены: JPEG, PNG, GIF, WebP');
                    return $this->refresh();
                }
                
                // Проверка содержимого файла
                $tempPath = $uploadedFile->tempName;
                if (!@getimagesize($tempPath)) {
                    Yii::$app->session->setFlash('error', 'Файл не является корректным изображением');
                    return $this->refresh();
                }
                
                $model->photo_data = file_get_contents($tempPath);
                $model->mime_type = $uploadedFile->type;
                $model->file_name = $uploadedFile->name;
                $model->file_size = $uploadedFile->size;
                $model->uploaded_by = Yii::$app->user->id;
                $model->status = FighterPhoto::STATUS_PENDING; // На модерации
                
                if ($model->save()) {
                    Yii::info("Пользователь {$model->uploaded_by} загрузил фото ID: {$model->id}");
                    Yii::$app->session->setFlash('success', 'Фотография успешно загружена и отправлена на модерацию');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::error('Ошибка сохранения фото: ' . implode(', ', $model->getFirstErrors()));
                    Yii::$app->session->setFlash('error', 'Ошибка при сохранении фотографии');
                }
            } else {
                Yii::$app->session->setFlash('error', 'Необходимо выбрать файл для загрузки');
            }
        }
        
        return $this->render('upload', [
            'model' => $model,
        ]);
    }

    /**
     * Модерация фотографии
     */
    public function actionModerate($id, $status)
    {
        if (!Yii::$app->user->can('moderator')) {
            throw new \yii\web\ForbiddenHttpException('Нет прав для модерации');
        }
        
        $photo = $this->findModel($id);
        $oldStatus = $photo->status;
        
        if (!in_array($status, [FighterPhoto::STATUS_APPROVED, FighterPhoto::STATUS_REJECTED])) {
            throw new \yii\web\BadRequestHttpException('Неверный статус');
        }
        
        $photo->status = $status;
        $photo->moderated_by = Yii::$app->user->id;
        $photo->moderated_at = new \yii\db\Expression('NOW()');
        
        if ($photo->save()) {
            Yii::info("Модератор {$photo->moderated_by} изменил статус фото ID: {$photo->id} с {$oldStatus} на {$status}");
            Yii::$app->session->setFlash('success', 'Статус фотографии обновлен');
        } else {
            Yii::error('Ошибка обновления статуса фото: ' . implode(', ', $photo->getFirstErrors()));
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении статуса');
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
    }

    /**
     * Массовая модерация
     */
    public function actionBulkModerate()
    {
        if (!Yii::$app->user->can('moderator')) {
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
            
            Yii::info("Модератор {$photo->moderated_by} массово изменил статус {$count} фото на {$status}");
            Yii::$app->session->setFlash('success', "Обновлено {$count} фотографий");
        }
        
        return $this->redirect(Yii::$app->request->referrer ?: ['site/index']);
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