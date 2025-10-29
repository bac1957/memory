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
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
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
            $this->debugLoadIssue($model, $postData);
        
            if ($model->load($postData)) {
                // Загружаем основные данные бойца
                if ($model->load($postData)) {
                    // Убеждаемся, что обязательные поля установлены
                    $model->user_id = Yii::$app->user->id;

                    // Устанавливаем статус по умолчанию, если не установлен
                    if (empty($model->status_id)) {
                        $model->status_id = FighterStatus::find()->where(['name' => 'Черновик'])->one()->id ?? 4;
                    }
                    
                    Yii::info('Before save - returnStatus: ' . $model->returnStatus . ', status_id: ' . $model->status_id, 'fighter');
                    
                    // Валидация перед сохранением
                    if (!$model->validate()) {
                        Yii::$app->session->setFlash('error', 'Ошибка валидации: ' . $this->getErrorsString($model->errors));
                    } elseif ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Боец успешно создан.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::$app->session->setFlash('error', 'Ошибка при сохранении бойца: ' . $this->getErrorsString($model->errors));
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при загрузке данных формы.');
                }
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
            // Загрузка данных о пленениях
            $captures = [];
            $captureData = Yii::$app->request->post('FighterCapture', []);
            foreach ($captureData as $i => $captureItem) {
                if (isset($captureItem['id']) && !empty($captureItem['id'])) {
                    $capture = FighterCapture::findOne($captureItem['id']);
                    if (!$capture) {
                        $capture = new FighterCapture();
                    }
                } else {
                    $capture = new FighterCapture();
                }
                // Правильная загрузка данных
                foreach ($captureItem as $key => $value) {
                    if (property_exists($capture, $key)) {
                        $capture->$key = $value;
                    }
                }
                $captures[] = $capture;
            }

            // Загрузка данных о наградах
            $awards = [];
            $awardData = Yii::$app->request->post('FighterAward', []);
            foreach ($awardData as $i => $awardItem) {
                if (isset($awardItem['id']) && !empty($awardItem['id'])) {
                    $award = FighterAward::findOne($awardItem['id']);
                    if (!$award) {
                        $award = new FighterAward();
                    }
                } else {
                    $award = new FighterAward();
                }
                // Правильная загрузка данных
                foreach ($awardItem as $key => $value) {
                    if (property_exists($award, $key)) {
                        $award->$key = $value;
                    }
                }
                $awards[] = $award;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Проверяем валидацию перед сохранением
                if (!$model->validate()) {
                    Yii::error('Fighter validation errors: ' . print_r($model->errors, true));
                    throw new \Exception('Ошибка валидации данных бойца: ' . $this->getErrorsString($model->errors));
                }
                
                if ($model->save()) {
                    Yii::info('Fighter updated with status: ' . $model->status_id . ', ReturnStatus: ' . $model->returnStatus, 'fighter');
                    
                    $hasCaptureData = false;
                    $hasAwardData = false;
                    
                    // Удаляем только те записи, которые больше не существуют
                    $existingCaptureIds = array_filter(array_map(function($c) { 
                        return $c->id ?? null; 
                    }, $captures));
                    if (!empty($existingCaptureIds)) {
                        FighterCapture::deleteAll([
                            'and', 
                            ['fighter_id' => $model->id],
                            ['not in', 'id', $existingCaptureIds]
                        ]);
                    } else {
                        FighterCapture::deleteAll(['fighter_id' => $model->id]);
                    }
                    
                    // Сохраняем пленения
                    foreach ($captures as $capture) {
                        if ($this->hasCaptureData($capture)) {
                            $capture->fighter_id = $model->id;
                            if (!$capture->save()) {
                                Yii::error('Ошибка сохранения пленения: ' . print_r($capture->errors, true));
                                throw new \Exception('Ошибка сохранения данных о пленении: ' . print_r($capture->errors, true));
                            }
                            $hasCaptureData = true;
                        }
                    }
                    
                    // Удаляем только те записи, которые больше не существуют
                    $existingAwardIds = array_filter(array_map(function($a) { 
                        return $a->id ?? null; 
                    }, $awards));
                    if (!empty($existingAwardIds)) {
                        FighterAward::deleteAll([
                            'and', 
                            ['fighter_id' => $model->id],
                            ['not in', 'id', $existingAwardIds]
                        ]);
                    } else {
                        FighterAward::deleteAll(['fighter_id' => $model->id]);
                    }
                    
                    // Сохраняем награды
                    foreach ($awards as $award) {
                        if ($this->hasAwardData($award)) {
                            $award->fighter_id = $model->id;
                            if (!$award->save()) {
                                Yii::error('Ошибка сохранения награды: ' . print_r($award->errors, true));
                                throw new \Exception('Ошибка сохранения данных о награде: ' . print_r($award->errors, true));
                            }
                            $hasAwardData = true;
                        }
                    }
                    
                    $transaction->commit();
                    
                    Yii::info("User " . Yii::$app->user->id . " updated fighter ID: " . $model->id, 'fighter');
                    
                    $message = 'Данные бойца успешно обновлены (статус: Черновик).';
                    if ($hasCaptureData) $message .= ' Данные о пленении обновлены.';
                    if ($hasAwardData) $message .= ' Данные о наградах обновлены.';
                    
                    Yii::$app->session->setFlash('success', $message);
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::error('Ошибка сохранения бойца: ' . print_r($model->errors, true));
                    throw new \Exception('Ошибка сохранения бойца: ' . print_r($model->errors, true));
                }
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
     * Проверяет, есть ли данные для сохранения пленения
     */
    private function hasCaptureData($capture)
    {
        return !empty(trim($capture->capture_date ?? '')) || 
               !empty(trim($capture->capture_place ?? '')) || 
               !empty(trim($capture->camp_name ?? '')) ||
               !empty(trim($capture->capture_circumstances ?? '')) ||
               !empty(trim($capture->liberated_date ?? '')) ||
               !empty(trim($capture->liberated_by ?? '')) ||
               !empty(trim($capture->liberation_circumstances ?? '')) ||
               !empty(trim($capture->additional_info ?? ''));
    }

    /**
     * Проверяет, есть ли данные для сохранения награды
     */
    private function hasAwardData($award)
    {
        return !empty($award->award_id) || 
               !empty(trim($award->award_date ?? '')) || 
               !empty(trim($award->award_reason ?? '')) ||
               !empty(trim($award->document_description ?? ''));
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

    private function debugLoadIssue($model, $postData)
    {
        Yii::info('=== DEBUG LOAD ISSUE ===', 'fighter');
        Yii::info('POST keys: ' . print_r(array_keys($postData), true), 'fighter');
        Yii::info('Model formName: ' . $model->formName(), 'fighter');
        
        if (isset($postData[$model->formName()])) {
            Yii::info('Data for model: ' . print_r($postData[$model->formName()], true), 'fighter');
        } else {
            Yii::info('No data found for model: ' . $model->formName(), 'fighter');
        }
        
        // Проверяем обязательные поля
        $requiredFields = [];
        foreach ($model->rules() as $rule) {
            if (isset($rule[1]) && $rule[1] === 'required') {
                $requiredFields = array_merge($requiredFields, (array)$rule[0]);
            }
        }
        Yii::info('Required fields: ' . print_r($requiredFields, true), 'fighter');
    }   
}