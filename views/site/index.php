<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\helpers\Url;
use app\helpers\PhotoHelper;

$this->title = 'Мемориал бойцов Великой Отечественной войны';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-index">
    <!-- Герой-секция с поиском - показываем только гостям -->
    <?php if ($showSearch ?? true): ?>
    <div class="jumbotron hero-section">
        <div class="container">
            <h1 class="hero-title"><?= Html::encode($this->title) ?></h1>
            
            <!-- Упрощенная поисковая форма -->
            <div class="search-form-wrapper">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options' => [
                        'class' => 'main-search-form',
                        'autocomplete' => 'off',
                    ],
                ]); ?>

                <div class="row">
                    <div class="col-md-10 col-sm-12 mx-auto">
                        <div class="input-group input-group-lg">
                            <?= $form->field($searchModel, 'q', [
                                'template' => '{input}',
                                'options' => ['class' => 'form-group search-input-group'],
                            ])->textInput([
                                'placeholder' => 'Например: Иванов Иван 1915-1925',
                                'class' => 'form-control search-input',
                                'autofocus' => true,
                            ]) ?>
                            
                            <div class="input-group-append">
                                <?= Html::submitButton('<i class="glyphicon glyphicon-search"></i> Найти', [
                                    'class' => 'btn btn-primary search-btn',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Основной контент -->
    <div class="main-content-section">
        <div class="container">
            <div class="row">
                <!-- Основная область с результатами -->
                <div class="<?= ($showStats ?? true) ? 'col-md-9 col-sm-12' : 'col-12' ?>">
                    <?php if ($dataProvider->getCount() > 0): ?>
                        <?php if (!empty($searchModel->q) && ($showSearch ?? true)): ?>
                            <div class="search-info mb-3">
                                <p class="text-muted">
                                    Результаты поиска по запросу: 
                                    "<strong><?= Html::encode($searchModel->q) ?></strong>"
                                </p>
                            </div>
                        <?php endif; ?>

                        <?= ListView::widget([
                            'dataProvider' => $dataProvider,
                            'layout' => "{items}\n{pager}",
                            'itemView' => '_fighter_card',
                            'viewParams' => ['totalCount' => $dataProvider->getTotalCount()],
                            'options' => ['class' => 'fighters-grid'],
                            'itemOptions' => ['class' => 'fighter-card-item'],
                            'emptyText' => '',
                            'pager' => [
                                'options' => ['class' => 'pagination justify-content-center mt-4'],
                                'linkOptions' => ['class' => 'page-link'],
                                'activePageCssClass' => 'active',
                            ],
                        ]); ?>

                    <?php elseif (!empty($searchModel->q) && ($showSearch ?? true)): ?>
                        <div class="no-results text-center py-5">
                            <div class="no-results-icon">
                                <i class="glyphicon glyphicon-search" style="font-size: 64px; color: #ccc;"></i>
                            </div>
                            <p class="text-muted">
                                По запросу "<strong><?= Html::encode($searchModel->q) ?></strong>" ничего не найдено.
                            </p>
                            <?= Html::a('Показать всех бойцов', ['index'], ['class' => 'btn btn-primary mt-3']) ?>
                        </div>
                    <?php else: ?>
                        <!-- Для авторизованных пользователей показываем приветствие -->
                        <?php if (!Yii::$app->user->isGuest): ?>
                            <div class="welcome-message mb-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h3 class="card-title">Добро пожаловать, <?= Yii::$app->user->identity->first_name ?>!</h3>
                                        <p class="card-text">Здесь отображаются последние добавленные бойцы в мемориал.</p>
                                        <div class="mt-3">
                                            <?= Html::a('<i class="bi bi-person-plus"></i> Добавить бойца', ['fighter/create'], [
                                                'class' => 'btn btn-success me-2'
                                            ]) ?>
                                            <?= Html::a('<i class="bi bi-list-ul"></i> Мои бойцы', ['site/user-fighters'], [
                                                'class' => 'btn btn-outline-primary'
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Показываем бойцов -->
                        <?= ListView::widget([
                            'dataProvider' => $dataProvider,
                            'layout' => "{items}\n{pager}",
                            'itemView' => '_fighter_card',
                            'viewParams' => ['totalCount' => $dataProvider->getTotalCount()],
                            'options' => ['class' => 'fighters-grid'],
                            'itemOptions' => ['class' => 'fighter-card-item'],
                            'pager' => [
                                'options' => ['class' => 'pagination justify-content-center mt-4'],
                            ],
                        ]); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Боковая панель со статистикой - показываем только гостям -->
                <?php if ($showStats ?? true): ?>
                <div class="col-md-3 col-sm-12">
                    <div class="stats-sidebar">
                        <div class="stats-content">
                            <div class="stat-item-vertical">
                                <div class="stat-number"><span class="stat-label">Всего данных: </span><?= $stats['total'] ?></div>
                            </div>
                            <div class="stat-item-vertical">
                                <div class="stat-number text-success"><span class="stat-label">Вернулись с войны: </span><?= $stats['returned'] ?></div>
                            </div>
                            <div class="stat-item-vertical">
                                <div class="stat-number text-danger"><span class="stat-label">Погибли: </span><?= $stats['killed'] ?></div>
                            </div>
                            <div class="stat-item-vertical">
                                <div class="stat-number text-warning"><span class="stat-label">Пропали без вести:</span><?= $stats['missing'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Автофокус на поле поиска только для гостей
if ($showSearch ?? true) {
    $this->registerJs(<<<JS
    $(document).ready(function() {
        $('#fightersearch-q').focus();
    });
    JS
    );
}
?>