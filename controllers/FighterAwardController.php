<?php

namespace app\controllers;

use Yii;
use app\models\FighterAward;
use app\models\Fighter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\UploadedFile;

class FighterAwardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'upload-document' => ['POST'],
                    'remove-document' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Создание новой награды для бойца
     */
    public function actionCreate($fighterId)
    {
        $fighter = $this->findFighterModel($fighterId);
        
        // Проверяем права доступа
        if ($fighter->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для добавления наград этому бойцу.');
        }

        $model = new FighterAward();
        $model->fighter_id = $fighterId;

        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла документа
            $model->document_photo = UploadedFile::getInstance($model, 'document_photo');
            if ($model->document_photo) {
                $model->uploadDocument($model->document_photo);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Награда успешно добавлена.');
                return $this->redirect(['fighter/view', 'id' => $fighterId]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при сохранении награды.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'fighter' => $fighter,
        ]);
    }

    /**
     * Обновление награды
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $fighter = $model->fighter;

        // Проверяем права доступа
        if ($fighter->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для редактирования этой награды.');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Загрузка файла документа
            $documentFile = UploadedFile::getInstance($model, 'document_photo');
            if ($documentFile) {
                $model->uploadDocument($documentFile);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Награда успешно обновлена.');
                return $this->redirect(['fighter/view', 'id' => $model->fighter_id]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении награды.');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'fighter' => $fighter,
        ]);
    }

    /**
     * Удаление награды
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $fighterId = $model->fighter_id;
        $fighter = $model->fighter;

        // Проверяем права доступа
        if ($fighter->user_id !== Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('У вас нет прав для удаления этой награды.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Награда успешно удалена.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении награды.');
        }

        return $this->redirect(['fighter/view', 'id' => $fighterId]);
    }

    /**
     * Загрузка документа награды
     */
    public function actionUploadDocument($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $file = UploadedFile::getInstanceByName('document_file');

        if ($file && $model->uploadDocument($file) && $model->save(false)) {
            return [
                'success' => true,
                'message' => 'Документ успешно загружен.',
                'fileSize' => $model->getDocumentSize()
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при загрузке документа.'
        ];
    }

    /**
     * Удаление документа награды
     */
    public function actionRemoveDocument($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        
        if ($model->removeDocumentPhoto()) {
            return [
                'success' => true,
                'message' => 'Документ успешно удален.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при удалении документа.'
        ];
    }

    /**
     * Поиск модели FighterAward по ID
     */
    protected function findModel($id)
    {
        if (($model = FighterAward::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая награда не найдена.');
    }

    /**
     * Поиск модели Fighter по ID
     */
    protected function findFighterModel($id)
    {
        if (($model = Fighter::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемый боец не найден.');
    }
}
