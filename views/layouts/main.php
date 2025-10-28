<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*           Главный модуль программы
* 
* @file views/site/main.php
* @version 0.0.1
*
* @author Александр Васильков
* @author Home Lab, Пенза (с), 2025
* @author E-Mail bac@sura.ru
* @var yii\web\View $this
* 
* @date 07.09.2025
*
**********************************************************************
*/

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerCssFile('@web/css/main.css');
$this->registerCssFile('@web/css/layout.css');
$this->registerCssFile('@web/css/auth-forms.css');
$this->registerCssFile('@web/css/search.css');
$this->registerCssFile('@web/css/gallery.css');
$this->registerCssFile('@web/css/returnStatus.css', ['depends' => [yii\bootstrap5\BootstrapAsset::class]]);
$this->registerCssFile('@web/css/bootstrap-icons.css');
if (isset($this->context->module) && $this->context->module->id == 'admin') {
    $this->registerCssFile('@web/css/admin.css');
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <link rel="shortcut icon" href="<?= Url::base() ?>/images/favicon.ico" type="image/x-icon">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);
    
    $menuItems = [
        ['label' => 'Домой', 'url' => ['/site/index']],
    ];

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'О программе', 'url' => ['/site/about']];
        $menuItems[] = ['label' => 'Вход', 'url' => ['/site/login']];
    } else {
        // Для администраторов и модераторов
        if (Yii::$app->user->identity->isAdmin() || Yii::$app->user->identity->isModerator()) {
            $menuItems[] = [
                'label' => 'Инструменты',
                'items' => Yii::$app->user->identity->isAdmin() ? [
                    ['label' => 'Пользователи', 'url' => ['/admin/users']],
                    ['label' => 'Администрирование', 'url' => ['/admin/dashboard']],
                ] : [
                    ['label' => 'Проверка', 'url' => ['/moderation/verify']],
                    ['label' => 'Обработка фото', 'url' => ['/moderation/photos']],
                ]
            ];
        }

        $menuItems[] = ['label' => 'Бойцы', 'url' => ['/site/user-fighters']]; 
        $menuItems[] = ['label' => 'Профиль', 'url' => ['/site/profile']]; 
        $menuItems[] = ['label' => 'О программе', 'url' => ['/site/about']];
        
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
            . Html::submitButton(
                'Выход (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }   

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto'],
        'items' => $menuItems,
    ]);
    
    // Информация о пользователе в правой части навбара
    if (!Yii::$app->user->isGuest) {
        echo '<div class="navbar-text ms-3 d-none d-md-block">';
        echo '<small class="text-light">';
        echo Html::encode(Yii::$app->user->identity->fullName);
        echo ' <span class="badge bg-secondary">' . Html::encode(Yii::$app->user->identity->roleName) . '</span>';
        echo '</small>';
        echo '</div>';
    }
    
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">
                &copy; ООО "Башир", г.Пенза <?= date('Y') ?>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <?= Yii::powered() ?>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isDevelop()): ?>
                    | <span class="text-info">Режим разработчика</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
