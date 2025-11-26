<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Fighter;
use app\models\FighterModerationForm;
use app\models\FighterStatus;

class ModerationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function () {
                    throw new ForbiddenHttpException('Доступ разрешен только модераторам.');
                },
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->isModerator();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionVerify()
    {
        $request = Yii::$app->request;
        $statusFilter = $request->get('status', FighterStatus::STATUS_MODERATION);
        $search = trim((string)$request->get('q', ''));

        $query = Fighter::find()
            ->with(['user', 'status', 'mainPhoto'])
            ->orderBy(['created_at' => SORT_DESC]);

        if ($statusFilter !== 'all') {
            $query->andWhere(['status_id' => (int)$statusFilter]);
        }

        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'fighter.last_name', $search],
                ['like', 'fighter.first_name', $search],
                ['like', 'fighter.middle_name', $search],
                ['like', 'fighter.birth_place', $search],
                ['like', 'fighter.conscription_place', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $stats = [
            'pending' => Fighter::find()->where(['status_id' => FighterStatus::STATUS_MODERATION])->count(),
            'revision' => Fighter::find()->where(['status_id' => FighterStatus::STATUS_REJECTED])->count(),
            'blocked' => Fighter::find()->where(['status_id' => FighterStatus::STATUS_BLOCKED])->count(),
        ];

        return $this->render('verify', [
            'dataProvider' => $dataProvider,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'stats' => $stats,
            'statusList' => FighterStatus::getAllStatuses(),
        ]);
    }

    public function actionReview($id)
    {
        $model = $this->findModel($id);
        $formModel = new FighterModerationForm([
            'comment' => $model->moderation_comment,
        ]);

        if (Yii::$app->request->isPost) {
            if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
                $this->applyDecision($model, $formModel);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Решение успешно применено.');
                    return $this->redirect(['verify']);
                } 
                Yii::$app->session->setFlash('error', 'Не удалось сохранить решение. Попробуйте еще раз.');
            }
        }

        return $this->render('review', [
            'model' => $model,
            'formModel' => $formModel,
        ]);
    }

    public function actionPhotos()
    {
        return $this->render('photos');
    }

    protected function applyDecision(Fighter $model, FighterModerationForm $formModel): void
    {
        switch ($formModel->decision) {
            case FighterModerationForm::DECISION_APPROVE:
                $model->status_id = FighterStatus::STATUS_PUBLISHED;
                $model->moderation_comment = null;
                break;
            case FighterModerationForm::DECISION_REVISE:
                $model->status_id = FighterStatus::STATUS_REJECTED;
                $model->moderation_comment = $formModel->comment;
                break;
            case FighterModerationForm::DECISION_BLOCK:
                $model->status_id = FighterStatus::STATUS_BLOCKED;
                $model->moderation_comment = $formModel->comment;
                break;
        }

        $model->moderated_at = new Expression('NOW()');
        $model->moderator_id = Yii::$app->user->id;
    }

    protected function findModel($id): Fighter
    {
        $model = Fighter::find()
            ->where(['id' => $id])
            ->with(['user', 'status', 'militaryRank', 'awards.award', 'captures', 'photos'])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Боец не найден.');
        }

        return $model;
    }
}
