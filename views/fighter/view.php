<?php
// views/fighter/view.php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\Tabs;
use yii\helpers\ArrayHelper;
use app\models\Fighter;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $photos app\models\FighterPhoto[] */
/* @var $awards app\models\FighterAward[] */
/* @var $captures app\models\FighterCapture[] */

$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = $this->title;

// Регистрируем CSS файлы
$this->registerCssFile('@web/css/returnStatus.css', [
    'depends' => [yii\bootstrap5\BootstrapAsset::class]
]);
$this->registerCssFile('@web/css/card.css', [
    'depends' => [yii\bootstrap5\BootstrapAsset::class]
]);

// Проверяем права доступа
$identity = Yii::$app->user->identity;
$canEdit = $identity ? $model->canEdit($identity) : false;
$canSeeModeratorComment = $identity && ($canEdit || $identity->isModerator());
?>
<div class="fighter-view">

    <!-- Заголовок и кнопки действий -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-end action-buttons">
            <div class="btn-group">
                <?= Html::a('Назад', ['site/user-fighters'], ['class' => 'btn btn-secondary']) ?>
                <?php if ($canEdit): ?>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Вы уверены, что хотите удалить этого бойца?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Основная информация и фото -->
    <div class="row mb-4">
        <!-- Фотография -->
        <div class="col-md-4">
            <div class="card fighter-card">
                <div class="card-body text-center">
                    <?php if ($model->mainPhoto): ?>
                        <img src="<?= \yii\helpers\Url::to(['photo/thumbnail', 'id' => $model->mainPhoto->id]) ?>"
                             class="img-fluid rounded mb-3" 
                             alt="<?= Html::encode($model->fullName) ?>"
                             style="max-height: 300px;">
                    <?php else: ?>
                        <div class="photo-placeholder rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 300px;">
                            <span class="text-muted">Нет фотографии</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Статус модерации -->
                    <div class="mb-3">
                        <span class="badge badge-status bg-secondary">
                            <?= $model->statusName ?>
                        </span>
                    </div>

                    <!-- Судьба бойца -->
                    <div class="mb-3">
                        <span class="<?= $model->getReturnStatusCssClass() ?>">
                            <span class="return-status-icon"><?= $model->getReturnStatusIcon() ?></span>
                            <?= $model->getReturnStatusText() ?>
                        </span>
                    </div>

                    <!-- Статистика -->
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?= $model->awardsCount ?></div>
                            <div class="stat-label">наград</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?= $model->photosCount ?></div>
                            <div class="stat-label">фото</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?= $model->capturesCount ?></div>
                            <div class="stat-label">пленений</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="col-md-8">
            <div class="card fighter-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'fullName',
                                'label' => 'ФИО',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::tag('strong', $model->fullName);
                                }
                            ],
                            [
                                'attribute' => 'birthDate',
                                'label' => 'Дата рождения',
                                'value' => function($model) {
                                    return $model->birthDate ?: 'Не указана';
                                }
                            ],
                            [
                                'attribute' => 'death_year',
                                'label' => 'Год смерти',
                                'value' => function($model) {
                                    return $model->death_year ?: 'Не указан';
                                }
                            ],
                            [
                                'attribute' => 'military_rank_id',
                                'label' => 'Воинское звание',
                                'value' => function($model) {
                                    return $model->rankName;
                                }
                            ],
                            [
                                'attribute' => 'birth_place',
                                'label' => 'Место рождения',
                                'value' => function($model) {
                                    return $model->birth_place ?: 'Не указано';
                                }
                            ],
                            [
                                'attribute' => 'conscription_place',
                                'label' => 'Место призыва',
                                'value' => function($model) {
                                    return $model->conscription_place ?: 'Не указано';
                                }
                            ],
                            [
                                'attribute' => 'military_unit',
                                'label' => 'Воинская часть',
                                'value' => function($model) {
                                    return $model->military_unit ?: 'Не указана';
                                }
                            ],
                            [
                                'attribute' => 'burial_place',
                                'label' => 'Место захоронения',
                                'value' => function($model) {
                                    return $model->burial_place ?: 'Не указано';
                                }
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => 'Дата создания',
                                'format' => 'datetime'
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Дата обновления',
                                'format' => 'datetime'
                            ],
                            [
                                'attribute' => 'moderation_comment',
                                'label' => 'Комментарий модератора',
                                'format' => 'ntext',
                                'visible' => $canSeeModeratorComment && !empty($model->moderation_comment),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Табы с дополнительной информацией -->
    <?= Tabs::widget([
        'items' => [
            [
                'label' => 'Биография',
                'content' => $this->render('_biography_tab', [
                    'model' => $model,
                ]),
                'active' => true,
            ],
            [
                'label' => 'Фотографии <span class="badge bg-secondary">' . count($photos) . '</span>',
                'content' => $this->render('_photos_view_tab', [
                    'model' => $model,
                    'photos' => $photos,
                ]),
                'encode' => false,
            ],
            [
                'label' => 'Награды <span class="badge bg-secondary">' . count($awards) . '</span>',
                'content' => $this->render('_awards_view_tab', [
                    'model' => $model,
                    'awards' => $awards,
                ]),
                'encode' => false,
            ],
            [
                'label' => 'Пленения <span class="badge bg-secondary">' . count($captures) . '</span>',
                'content' => $this->render('_captures_view_tab', [
                    'model' => $model,
                    'captures' => $captures,
                ]),
                'encode' => false,
            ],
            [
                'label' => 'Дополнительная информация',
                'content' => $this->render('_additional_info_tab', [
                    'model' => $model,
                ]),
            ],
        ],
    ]) ?>

</div>
