<?php
namespace app\controllers;

use Yii;
use app\models\Fighter;
use app\models\FighterCapture;
use app\models\FighterSearch;
use app\models\FighterStatus;
use app\models\FighterAward;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class FighterController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['view'], // Разрешаем view для всех
                        'allow' => true,
                        'roles' => ['?', '@'], // И гости, и авторизованные
                    ],
                    [
                        'actions' => ['create', 'update', 'delete', 'send-to-moderation'], // Остальные действия только для авторизованных
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Для действий update и delete проверяем статус
                            if (in_array($action->id, ['update', 'delete'])) {
                                $id = Yii::$app->request->get('id');
                                if ($id) {
                                    $model = Fighter::findOne($id);
                                    if ($model && $model->status_id == FighterStatus::STATUS_MODERATION) {
                                        Yii::$app->session->setFlash('error', 'Редактирование ограничено пока боец находится на модерации.');
                                        return false;
                                    }
                                }
                            }
                            return true;
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'send-to-moderation' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new Fighter();
        $captures = [new FighterCapture()];
        $awards = [new FighterAward()];

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            if ($model->load($postData)) {
                // Убеждаемся, что обязательные поля установлены
                $model->user_id = Yii::$app->user->id;

                // Устанавливаем статус по умолчанию, если не установлен
                if (empty($model->status_id)) {
                    $model->status_id = FighterStatus::find()->where(['name' => 'Черновик'])->one()->id ?? 1;
                }
                
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    // Сохраняем основную модель
                    if (!$model->save()) {
                        throw new \Exception('Ошибка сохранения бойца: ' . $this->getErrorsString($model->errors));
                    }
                    
                    // Сохраняем награды
                    $this->saveAwards($model->id, $postData);
                    
                    // Сохраняем пленения
                    $this->saveCaptures($model->id, $postData);
                    
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Боец успешно создан.');
                    return $this->redirect(['view', 'id' => $model->id]);
                    
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error('Transaction failed: ' . $e->getMessage());
                    Yii::$app->session->setFlash('error', 'Ошибка при сохранении: ' . $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при загрузке данных формы.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'captures' => $captures,
            'awards' => $awards,
            'capturesCount' => count($captures),
            'awardsCount' => count($awards),
        ]);
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        // Проверяем, что пользователь редактирует своего бойца
        if ($model->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для редактирования этого бойца.');
        }

        $captures = $model->captures ?: [new FighterCapture()];
        $awards = $model->awards ?: [new FighterAward()];

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Сохраняем основную модель
                if (!$model->save()) {
                    throw new \Exception('Ошибка сохранения бойца: ' . $this->getErrorsString($model->errors));
                }
                
                // Сохраняем награды
                $hasAwardData = $this->saveAwards($model->id, Yii::$app->request->post());
                
                // Сохраняем пленения
                $hasCaptureData = $this->saveCaptures($model->id, Yii::$app->request->post());
                
                $transaction->commit();
                
                $message = 'Данные бойца успешно обновлены.';
                if ($hasAwardData) $message .= ' Данные о наградах обновлены.';
                if ($hasCaptureData) $message .= ' Данные о пленении обновлены.';
                
                Yii::$app->session->setFlash('success', $message);
                return $this->redirect(['view', 'id' => $model->id]);
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Transaction failed: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Ошибка при сохранении: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'captures' => $captures,
            'capturesCount' => count($captures),
            'awards' => $awards,
            'awardsCount' => count($awards),
        ]);
    }

    /**
     * Отправка бойца на модерацию
     */
    public function actionSendToModeration($id)
    {
        $model = $this->findModel($id);
        
        // Проверяем права доступа
        if ($model->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для отправки этого бойца на модерацию.');
        }
        
        // Проверяем, можно ли отправить на модерацию
        $allowedStatuses = [
            FighterStatus::STATUS_DRAFT,
            FighterStatus::STATUS_REJECTED
        ];
        
        if (!in_array($model->status_id, $allowedStatuses)) {
            Yii::$app->session->setFlash('error', 'Боец не может быть отправлен на модерацию из текущего статуса.');
            return $this->redirect(['update', 'id' => $model->id]);
        }
        
        // Получаем ID статуса "На модерации"
        $moderationStatus = FighterStatus::find()->where(['name' => 'На модерации'])->one();
        if (!$moderationStatus) {
            Yii::$app->session->setFlash('error', 'Статус "На модерации" не найден в системе.');
            return $this->redirect(['update', 'id' => $model->id]);
        }
        
        $model->status_id = $moderationStatus->id;
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Боец успешно отправлен на проверку модератору. Ожидайте решения.');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при отправке на модерацию: ' . $this->getErrorsString($model->errors));
            return $this->redirect(['update', 'id' => $model->id]);
        }
    }

    /**
     * Сохраняет награды для бойца
     */
    private function saveAwards($fighterId, $postData)
    {
        $hasAwardData = false;
        $awardData = $postData['FighterAward'] ?? [];
        
        // Удаляем старые награды
        FighterAward::deleteAll(['fighter_id' => $fighterId]);
        
        foreach ($awardData as $awardItem) {
            // Пропускаем пустые награды (где не выбран тип награды)
            if (empty($awardItem['award_id'])) {
                continue;
            }
            
            $award = new FighterAward();
            $award->fighter_id = $fighterId;
            $award->award_id = $awardItem['award_id'];
            $award->award_date = $awardItem['award_date'] ?? null;
            $award->award_reason = $awardItem['award_reason'] ?? null;
            $award->document_description = $awardItem['document_description'] ?? null;
            
            if (!$award->save()) {
                throw new \Exception('Ошибка сохранения награды: ' . $this->getErrorsString($award->errors));
            }
            
            $hasAwardData = true;
        }
        
        return $hasAwardData;
    }

    /**
     * Сохраняет пленения для бойца
     */
    private function saveCaptures($fighterId, $postData)
    {
        $hasCaptureData = false;
        $captureData = $postData['FighterCapture'] ?? [];
        
        // Удаляем старые пленения
        FighterCapture::deleteAll(['fighter_id' => $fighterId]);
        
        foreach ($captureData as $captureItem) {
            // Пропускаем полностью пустые пленения
            if (!$this->hasCaptureData($captureItem)) {
                continue;
            }
            
            $capture = new FighterCapture();
            $capture->fighter_id = $fighterId;
            $capture->capture_date = $captureItem['capture_date'] ?? null;
            $capture->capture_place = $captureItem['capture_place'] ?? null;
            $capture->camp_name = $captureItem['camp_name'] ?? null;
            $capture->capture_circumstances = $captureItem['capture_circumstances'] ?? null;
            $capture->liberated_date = $captureItem['liberated_date'] ?? null;
            $capture->liberated_by = $captureItem['liberated_by'] ?? null;
            $capture->liberation_circumstances = $captureItem['liberation_circumstances'] ?? null;
            $capture->additional_info = $captureItem['additional_info'] ?? null;
            
            if (!$capture->save()) {
                throw new \Exception('Ошибка сохранения пленения: ' . $this->getErrorsString($capture->errors));
            }
            
            $hasCaptureData = true;
        }
        
        return $hasCaptureData;
    }

    /**
     * Проверяет, есть ли данные для сохранения пленения
     */
    private function hasCaptureData($captureItem)
    {
        return !empty(trim($captureItem['capture_date'] ?? '')) || 
               !empty(trim($captureItem['capture_place'] ?? '')) || 
               !empty(trim($captureItem['camp_name'] ?? '')) ||
               !empty(trim($captureItem['capture_circumstances'] ?? '')) ||
               !empty(trim($captureItem['liberated_date'] ?? '')) ||
               !empty(trim($captureItem['liberated_by'] ?? '')) ||
               !empty(trim($captureItem['liberation_circumstances'] ?? '')) ||
               !empty(trim($captureItem['additional_info'] ?? ''));
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Получаем связанные данные
        $photos = $model->photos;
        $awards = $model->awardsWithInfo;
        $captures = $model->capturesWithData;

        return $this->render('view', [
            'model' => $model,
            'photos' => $photos,
            'awards' => $awards,
            'captures' => $captures,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Проверяем, что пользователь удаляет своего бойца
        if ($model->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для удаления этого бойца.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Боец успешно удален.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении бойца.');
        }

        return $this->redirect(['site/user-fighters']);
    }

    protected function findModel($id)
    {
        if (($model = Fighter::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемый боец не найден.');
    }

    /**
     * Преобразует ошибки валидации в строку
     */
    private function getErrorsString($errors)
    {
        $messages = [];
        foreach ($errors as $attribute => $errorMessages) {
            $messages[] = $attribute . ': ' . implode(', ', $errorMessages);
        }
        return implode('; ', $messages);
    }
    
}