<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;
use yii\data\ActiveDataProvider;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные
                        'matchCallback' => function ($rule, $action) {
                            // Только администраторы и модераторы
                            return Yii::$app->user->identity->isAdmin() || 
                                   Yii::$app->user->identity->isModerator();
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Главная страница админки
     */
    public function actionIndex()
    {
        // Для администратора - список пользователей
        if (Yii::$app->user->identity->isAdmin()) {
            return $this->redirect(['admin/users']);
        }
        
        // Для модератора - страница модерации
        if (Yii::$app->user->identity->isModerator()) {
            return $this->redirect(['moderation/index']);
        }
        
        return $this->goHome();
    }

    /**
     * Управление пользователями (только для администраторов)
     */
    public function actionUsers()
    {
        if (!Yii::$app->user->identity->isAdmin()) {
        throw new \yii\web\ForbiddenHttpException('Доступ запрещен.');
        }

        $dataProvider = new ActiveDataProvider([
                'query' => User::find()->orderBy([
                'status' => SORT_ASC, // Сначала STATUS_PENDING (ожидающие)
                'created_at' => SORT_DESC,
                'last_name' => SORT_ASC,
                'first_name' => SORT_ASC,
            ]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('users', [
            'dataProvider' => $dataProvider,
        ]);
    }
   
    /**
     * Панель управления (только для администраторов)
     */
    public function actionDashboard()
    {
        if (!Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Доступ запрещен.');
        }

        // Статистика для админ-панели
        $stats = [
            'totalUsers' => User::find()->count(),
            'pendingUsers' => User::find()->where(['status' => User::STATUS_INACTIVE])->count(),
            'activeUsers' => User::find()->where(['status' => User::STATUS_ACTIVE])->count(),
        ];

        return $this->render('dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Активация пользователя
     */
    public function actionActivate($id)
    {
        if (!Yii::$app->user->identity->isAdmin()) {
                throw new \yii\web\ForbiddenHttpException('Доступ запрещен.');
            }

            $user = User::findOne($id);
            if ($user) {
                $user->status = User::STATUS_ACTIVE;
                if ($user->save()) {
                    Yii::$app->session->setFlash('success', 'Пользователь успешно активирован.');
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при активации пользователя.');
                }
            }

            return $this->redirect(['users']);
    }

    /**
     * Блокировка пользователя
     */
    public function actionBlock($id)
    {
        if (!Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Доступ запрещен.');
        }

        $user = User::findOne($id);
        if ($user) {
            $user->status = User::STATUS_BLOCKED;
            if ($user->save()) {
                Yii::$app->session->setFlash('success', 'Пользователь заблокирован.');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при блокировке пользователя.');
            }
        }

        return $this->redirect(['users']);
    }
}