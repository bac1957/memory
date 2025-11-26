<?php
// views/fighter/view.php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap5\Tabs;
use yii\helpers\ArrayHelper;
use app\models\Fighter;
use app\models\FighterStatus;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $photos app\models\FighterPhoto[] */
/* @var $awards app\models\FighterAward[] */
/* @var $captures app\models\FighterCapture[] */

$this->title = $model->fullName;

// Для авторизованных пользователей показываем хлебные крошки
if (!Yii::$app->user->isGuest) {
    $this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
    $this->params['breadcrumbs'][] = $this->title;
} else {
    // Для гостей упрощенные крошки или без них
    $this->params['breadcrumbs'][] = ['label' => 'Мемориал', 'url' => ['site/index']];
    $this->params['breadcrumbs'][] = $this->title;
}

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

// Проверяем, находится ли боец на модерации
$isOnModeration = $model->status_id == FighterStatus::STATUS_MODERATION;
// Разрешаем редактирование только если боец не на модерации и пользователь авторизован
$allowEditing = !Yii::$app->user->isGuest && $canEdit && !$isOnModeration;

// Получаем фото для отображения (основное или последнее)
$displayPhoto = null;
if ($model->mainPhoto) {
    $displayPhoto = $model->mainPhoto; // Если есть основная фото
} else {
    // Если нет основной фото, берем самую последнюю по времени создания
    $displayPhoto = \app\models\FighterPhoto::find()
        ->where(['fighter_id' => $model->id])
        ->orderBy(['created_at' => SORT_DESC])
        ->one();
}
?>
<div class="fighter-view">

    <!-- Заголовок и кнопки действий -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
            <?php if ($isOnModeration && !Yii::$app->user->isGuest): ?>
                <div class="alert alert-warning mt-2 mb-0">
                    <i class="bi bi-clock-history"></i> 
                    <strong>Боец находится на проверке у модератора.</strong> 
                    Редактирование временно недоступно до завершения модерации.
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-end action-buttons">
            <div class="btn-group">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::a('Назад', ['site/user-fighters'], ['class' => 'btn btn-secondary']) ?>
                <?php endif; ?>
                
                <?php if ($allowEditing): ?>
                    <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Вы уверены, что хотите удалить этого бойца?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php elseif ($canEdit && $isOnModeration && !Yii::$app->user->isGuest): ?>
                    <!-- Показываем информацию о недоступности редактирования только для авторизованных -->
                    <button class="btn btn-outline-secondary" disabled title="Редактирование недоступно во время модерации">
                        Редактировать
                    </button>
                    <button class="btn btn-outline-secondary" disabled title="Удаление недоступно во время модерации">
                        Удалить
                    </button>
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
                    <?php if ($displayPhoto): ?>
                        <div class="position-relative">
                            <img src="<?= \yii\helpers\Url::to(['photo/thumbnail', 'id' => $displayPhoto->id]) ?>"
                                 class="img-fluid rounded mb-3" 
                                 alt="<?= Html::encode($model->fullName) ?>"
                                 style="max-height: 300px; object-fit: contain;">
                            
                            <!-- Бейдж статуса фото, если не одобрено -->
                            <?php if ($displayPhoto->status !== \app\models\FighterPhoto::STATUS_APPROVED): ?>
                                <div class="position-absolute top-0 start-0 m-2">
                                    <?php
                                    $photoStatusBadge = [
                                        \app\models\FighterPhoto::STATUS_PENDING => ['class' => 'bg-warning', 'text' => 'На модерации'],
                                        \app\models\FighterPhoto::STATUS_REJECTED => ['class' => 'bg-danger', 'text' => 'Отклонено'],
                                    ][$displayPhoto->status];
                                    ?>
                                    <span class="badge <?= $photoStatusBadge['class'] ?> small">
                                        <?= $photoStatusBadge['text'] ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Бейдж, если это не основная фото -->
                            <?php if (!$displayPhoto->is_main): ?>
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-info small" title="Самая последняя фотография">
                                        <i class="bi bi-clock"></i> Последняя
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Информация о фото -->
                        <div class="photo-info small text-muted mb-3">
                            <?php if ($displayPhoto->description): ?>
                                <div class="mb-1"><?= Html::encode($displayPhoto->description) ?></div>
                            <?php endif; ?>
                            <?php if ($displayPhoto->photo_year): ?>
                                <div class="mb-1">Год: <?= Html::encode($displayPhoto->photo_year) ?></div>
                            <?php endif; ?>
                            <div>Загружено: <?= Yii::$app->formatter->asDate($displayPhoto->created_at, 'short') ?></div>
                        </div>
                    <?php else: ?>
                        <div class="photo-placeholder rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 300px; background: #f8f9fa; border: 2px dashed #dee2e6;">
                            <div class="text-center">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <div class="mt-2 text-muted">Нет фотографий</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Статус модерации бойца -->
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
                                'format' => 'datetime',
                                'visible' => !Yii::$app->user->isGuest, // Только для авторизованных
                            ],
                            [
                                'attribute' => 'updated_at',
                                'label' => 'Дата обновления',
                                'format' => 'datetime',
                                'visible' => !Yii::$app->user->isGuest, // Только для авторизованных
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
                'visible' => !Yii::$app->user->isGuest, // Только для авторизованных
            ],
        ],
    ]) ?>

</div>

<style>
.action-buttons .btn-group {
    flex-wrap: wrap;
    gap: 5px;
}

.action-buttons .btn {
    margin-bottom: 5px;
}

/* Стили для заблокированных кнопок */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Стили для бейджей фото */
.position-relative .badge {
    font-size: 0.7em;
    backdrop-filter: blur(5px);
}

.photo-info {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .action-buttons .btn-group {
        justify-content: center;
    }
    
    .action-buttons .btn {
        flex: 1;
        min-width: 120px;
    }
}
</style>