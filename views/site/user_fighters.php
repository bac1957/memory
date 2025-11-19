<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use app\models\FighterStatus;

$this->title = 'Мои бойцы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-user-fighters">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'mainPhoto.thumbnail_data',
                'label' => 'Фото',
                'format' => 'raw',
                'value' => function($model) {
                    if ($model->mainPhoto && $model->mainPhoto->thumbnail_data) {
                        $base64 = base64_encode($model->mainPhoto->thumbnail_data);
                        $src = 'data:' . $model->mainPhoto->mime_type . ';base64,' . $base64;
                        return Html::img($src, [
                            'style' => 'width: 80px; height: 80px; object-fit: cover; border-radius: 4px;',
                            'alt' => 'Фото бойца'
                        ]);
                    }
                    return Html::tag('div', 'Нет фото', [
                        'style' => 'width: 80px; height: 80px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;',
                        'class' => 'text-muted small'
                    ]);
                },
                'contentOptions' => ['style' => 'width: 100px; text-align: center;'],
            ],
            [
                'attribute' => 'fullName',
                'label' => 'ФИО',
                'value' => function($model) {
                    return $model->fullName;
                },
                'contentOptions' => function($model) {
                    $class = '';
                    if ($model->status_id == FighterStatus::STATUS_REJECTED) {
                        $class = 'text-danger';
                    } elseif ($model->status_id == FighterStatus::STATUS_BLOCKED) {
                        $class = 'text-warning';
                    }
                    return ['class' => $class];
                },
            ],
            [
                'attribute' => 'birth_year',
                'label' => 'Год рождения',
                'value' => function($model) {
                    return $model->birth_year ?: '';
                },
            ],
            [
                'attribute' => 'militaryRank',
                'label' => 'Звание',
                'value' => function($model) {
                    return $model->militaryRank ? Html::encode($model->militaryRank->name) : '';
                },
            ],
            [
                'attribute' => 'conscription_place',
                'label' => 'Место призыва',
                'value' => function($model) {
                    return $model->conscription_place ?: '';
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Дата обновления',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'status_id',
                'label' => 'Статус',
                'value' => function($model) {
                    // Проверяем существование связанной модели статуса
                    if (!$model->status) {
                        return Html::tag('span', 'Не указан', ['class' => 'badge bg-secondary']);
                    }
                    
                    $badgeClass = 'bg-secondary';
                    $statusName = $model->status->name;
                    
                    // Цвета для разных статусов
                    if ($model->status_id == FighterStatus::STATUS_DRAFT) {
                        $badgeClass = 'bg-secondary';
                    } elseif ($model->status_id == FighterStatus::STATUS_MODERATION) {
                        $badgeClass = 'bg-warning';
                    } elseif ($model->status_id == FighterStatus::STATUS_PUBLISHED) {
                        $badgeClass = 'bg-success';
                    } elseif ($model->status_id == FighterStatus::STATUS_REJECTED) {
                        $badgeClass = 'bg-danger';
                    } elseif ($model->status_id == FighterStatus::STATUS_ARCHIVE) {
                        $badgeClass = 'bg-info';
                    } elseif ($model->status_id == FighterStatus::STATUS_BLOCKED) {
                        $badgeClass = 'bg-dark';
                    }
                    
                    return Html::tag('span', Html::encode($statusName), [
                        'class' => "badge {$badgeClass}"
                    ]);
                },
                'format' => 'raw',
                'contentOptions' => function($model) {
                    $class = '';
                    if ($model->status_id == FighterStatus::STATUS_REJECTED) {
                        $class = 'table-danger';
                    } elseif ($model->status_id == FighterStatus::STATUS_MODERATION) {
                        $class = 'table-warning';
                    } elseif ($model->status_id == FighterStatus::STATUS_BLOCKED) {
                        $class = 'table-dark';
                    }
                    return ['class' => $class];
                },
            ],
            [
                'label' => 'Комментарий модератора',
                'format' => 'ntext',
                'value' => function($model) {
                    $needsComment = in_array($model->status_id, [
                        FighterStatus::STATUS_REJECTED,
                        FighterStatus::STATUS_BLOCKED
                    ]);
                    return $needsComment ? ($model->moderation_comment ?: 'Нет комментария') : '';
                },
                'contentOptions' => ['style' => 'max-width: 300px; white-space: normal;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'width: 100px; text-align: center;'],
                'buttons' => [
                    'update' => function($url, $model) {
                        return Html::a('<i class="bi bi-pencil"></i>', ['fighter/update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-primary',
                            'title' => 'Редактировать',
                        ]);
                    },
                    'delete' => function($url, $model) {
                        return Html::a('<i class="bi bi-trash"></i>', ['fighter/delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger',
                            'title' => 'Удалить',
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить этого бойца?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <div class="mt-3">
        <?= Html::a('<i class="bi bi-plus-circle"></i> Добавить бойца', ['fighter/create'], [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
</div>