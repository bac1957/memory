<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\models\Fighter;
use app\models\FighterSearch;
use app\models\FighterPhoto;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\ContactForm;
use app\models\Profile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'profile', 'user-fighters'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'], // Только для гостей
                    ],
                    [
                        'actions' => ['logout', 'profile', 'user-fighters'],
                        'allow' => true,
                        'roles' => ['@'], // Только для авторизованных
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['login']);
                }
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'minLength' => 4,
                'maxLength' => 6,
            ],
        ];
    }

    /**
     * Отобразить домашнюю страницу
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new FighterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Если пользователь авторизован - не показываем банер поиска и статистику
        if (!Yii::$app->user->isGuest) {
            $dataProvider = $this->getBaseDataProvider();
            
            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'showSearch' => false,
                'showStats' => false,
            ]);
        }

        // Для гостей - полная версия с поиском и статистикой
        if (empty($searchModel->q)) {
            $dataProvider = $this->getBaseDataProvider();
        }

        $stats = $this->getStats();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'showSearch' => true,
            'showStats' => true,
        ]);
    }

    /**
     * Создает базовый DataProvider для списка бойцов
     */
    private function getBaseDataProvider()
    {
        return new ActiveDataProvider([
            'query' => Fighter::find()
                ->joinWith(['status', 'photos'])
                ->andWhere(['fighter_photo.status' => FighterPhoto::STATUS_APPROVED])
                ->groupBy('fighter.id')
                ->orderBy(['fighter.created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);
    }

    /**
     * Получить статистику
     */
    private function getStats()
    {
        $cacheKey = 'fighter_stats_' . date('Y-m-d');
        $stats = Yii::$app->cache->get($cacheKey);
        
        if ($stats === false) {
            $stats = [
                'total' => Fighter::find()->count(),
                'returned' => Fighter::find()->where(['status_id' => 1])->count(),
                'killed' => Fighter::find()->where(['status_id' => 2])->count(),
                'missing' => Fighter::find()->where(['status_id' => 3])->count(),
                'with_photos' => Fighter::find()
                    ->joinWith('photos')
                    ->andWhere(['fighter_photo.status' => FighterPhoto::STATUS_APPROVED])
                    ->count('DISTINCT fighter.id'),
            ];
            
            // Кэшируем статистику на 1 час
            Yii::$app->cache->set($cacheKey, $stats, 3600);
        }
        
        return $stats;
    }

    /**
     * Авторизация пользователя
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('info', 'Вы уже авторизованы.');
            return $this->goHome();
        }

        $model = new LoginForm();
        
        if ($model->load(Yii::$app->request->post())) {
            // Логируем попытку входа
            Yii::info("Login attempt for username: {$model->username}", 'auth');
            
            if ($model->login()) {
                $user = Yii::$app->user->identity;
                Yii::info("User {$user->username} successfully logged in", 'auth');
                
                // Если администратор или модератор - редирект на админ-панель
                if ($user->isAdmin() || $user->isModerator()) {
                    return $this->redirect(['admin/index']);
                }
                
                return $this->goBack();
            } else {
                Yii::warning("Failed login attempt for username: {$model->username}", 'auth');
            }
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Регистрация пользователя
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('info', 'Вы уже авторизованы.');
            return $this->goHome();
        }

        $model = new SignupForm();
        
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                Yii::info("New user registered: {$user->username}", 'auth');
                
                if (Yii::$app->getUser()->login($user)) {
                    Yii::$app->session->setFlash('success', 
                        'Регистрация прошла успешно! Ваш аккаунт ожидает подтверждения администратором.'
                    );
                    return $this->goHome();
                }
            } else {
                Yii::$app->session->setFlash('error', 
                    'Произошла ошибка при регистрации. Проверьте введенные данные.'
                );
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Выход из системы
     *
     * @return Response
     */
    public function actionLogout()
    {
        $username = Yii::$app->user->isGuest ? 'Unknown' : Yii::$app->user->identity->username;
        Yii::$app->user->logout();
        Yii::info("User {$username} logged out", 'auth');
        
        Yii::$app->session->setFlash('success', 'Вы успешно вышли из системы.');
        return $this->goHome();
    }

    /**
     * Страница контактов
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->contact(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно отправлено.');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Произошла ошибка при отправке сообщения.');
            }
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Страница "О программе"
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    /**
     * Профиль пользователя
     *
     * @return string|Response
     */
    public function actionProfile()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }

        // Получаем основную модель пользователя для редактирования
        $userModel = Yii::$app->user->identity;
        
        // Получаем модель профиля для отображения статистики
        $profileModel = Profile::getCurrentUserProfile();
        
        if ($userModel->load(Yii::$app->request->post())) {
            if ($userModel->save()) {
                Yii::$app->session->setFlash('success', 'Профиль успешно обновлен.');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при обновлении профиля.');
            }
        }

        return $this->render('profile', [
            'userModel' => $userModel,
            'profileModel' => $profileModel,
        ]);
    } 

    /**
     * Список бойцов пользователя
     *
     * @return string|Response
     */
    public function actionUserFighters()
    {
        $query = Fighter::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->joinWith(['mainPhoto', 'status'])
            ->orderBy(['fighter.created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('user_fighters', [
            'dataProvider' => $dataProvider,
            'totalCount' => $query->count(),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            // Логируем все действия контроллера
            Yii::info("Action: {$action->id}", 'controller');
            return true;
        }
        
        return false;
    }
}
