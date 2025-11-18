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

    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle"></i> Всего бойцов: <strong><?= $totalCount ?></strong>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "{items}\n{pager}",
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            [
                'attribute' => 'id',
                'header' => 'ID',
                'contentOptions' => ['style' => 'width: 60px; text-align: center;'],
            ],
            [
                'attribute' => 'photo',
                'label' => 'Фото',
                'format' => 'raw',
                'value' => function($model) {
                    if ($model->mainPhoto && $model->mainPhoto->thumbnail_data) {
                        $base64 = base64_encode($model->mainPhoto->thumbnail_data);
                        $src = 'data:' . $model->mainPhoto->mime_type . ';base64,' . $base64;
                        return Html::img($src, [
                            'style' => 'width: 60px; height: 60px; object-fit: cover; border-radius: 4px;',
                            'alt' => 'Фото бойца',
                            'class' => 'img-thumbnail'
                        ]);
                    }
                    return Html::tag('div', 'Нет фото', [
                        'style' => 'width: 60px; height: 60px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #dee2e6;',
                        'class' => 'text-muted small'
                    ]);
                },
                'contentOptions' => ['style' => 'width: 80px; text-align: center; vertical-align: middle;'],
            ],
            [
                'attribute' => 'last_name',
                'label' => 'Фамилия',
                'value' => function($model) {
                    return Html::encode($model->last_name);
                },
            ],
            [
                'attribute' => 'first_name',
                'label' => 'Имя',
                'value' => function($model) {
                    return Html::encode($model->first_name);
                },
            ],
            [
                'attribute' => 'middle_name',
                'label' => 'Отчество',
                'value' => function($model) {
                    return Html::encode($model->middle_name ?: '');
                },
            ],
            [
                'label' => 'ФИО',
                'value' => function($model) {
                    return Html::encode(trim($model->last_name . ' ' . $model->first_name . ' ' . ($model->middle_name ?: '')));
                },
                'contentOptions' => ['style' => 'font-weight: 500;'],
            ],
            [
                'attribute' => 'birth_year',
                'label' => 'Год рождения',
                'value' => function($model) {
                    return $model->birth_year ? $model->birth_year : '<span class="text-muted">не указан</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'militaryRank.name',
                'label' => 'Звание',
                'value' => function($model) {
                    return $model->militaryRank ? Html::encode($model->militaryRank->name) : '<span class="text-muted">не указано</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'conscription_place',
                'label' => 'Место призыва',
                'value' => function($model) {
                    if (empty($model->conscription_place)) {
                        return '<span class="text-muted">не указано</span>';
                    }
                    return Html::encode(mb_strlen($model->conscription_place) > 50 
                        ? mb_substr($model->conscription_place, 0, 50) . '...' 
                        : $model->conscription_place);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'status.name',
                'label' => 'Статус',
                'value' => function($model) {
                    if (!$model->status) {
                        return '<span class="badge bg-secondary">не указан</span>';
                    }
                    
                    $badgeClass = 'bg-secondary';
                    $statusName = $model->status->name;
                    
                    // Цвета для разных статусов
                    if (stripos($statusName, 'чернов') !== false) {
                        $badgeClass = 'bg-secondary';
                    } elseif (stripos($statusName, 'опубли') !== false || stripos($statusName, 'актив') !== false) {
                        $badgeClass = 'bg-success';
                    } elseif (stripos($statusName, 'отклон') !== false || stripos($statusName, 'заблок') !== false) {
                        $badgeClass = 'bg-danger';
                    } elseif (stripos($statusName, 'ожидан') !== false || stripos($statusName, 'модерац') !== false) {
                        $badgeClass = 'bg-warning';
                    }
                    
                    return Html::tag('span', Html::encode($statusName), [
                        'class' => "badge {$badgeClass}"
                    ]);
                },
                'format' => 'raw',
                'contentOptions' => function($model) {
                    $class = '';
                    if ($model->status) {
                        $statusName = strtolower($model->status->name);
                        if (strpos($statusName, 'отклон') !== false || strpos($statusName, 'заблок') !== false) {
                            $class = 'table-danger';
                        } elseif (strpos($statusName, 'ожидан') !== false || strpos($statusName, 'модерац') !== false) {
                            $class = 'table-warning';
                        }
                    }
                    return ['class' => $class];
                },
            ],
            [
                'attribute' => 'returnStatus',
                'label' => 'Судьба',
                'value' => function($model) {
                    $statuses = [
                        'returned' => ['text' => 'Вернулся', 'class' => 'success'],
                        'died' => ['text' => 'Погиб', 'class' => 'danger'],
                        'missing' => ['text' => 'Пропал без вести', 'class' => 'warning'],
                    ];
                    
                    if (isset($statuses[$model->returnStatus])) {
                        $status = $statuses[$model->returnStatus];
                        return Html::tag('span', $status['text'], [
                            'class' => "badge bg-{$status['class']}"
                        ]);
                    }
                    
                    return '<span class="badge bg-secondary">не указана</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Добавлен',
                'format' => 'datetime',
                'contentOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'attribute' => 'moderation_comment',
                'label' => 'Комментарий модератора',
                'value' => function($model) {
                    if (empty($model->moderation_comment)) {
                        return '';
                    }
                    return Html::tag('small', Html::encode($model->moderation_comment), [
                        'class' => 'text-muted',
                        'title' => Html::encode($model->moderation_comment)
                    ]);
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'max-width: 200px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'width: 120px; text-align: center; white-space: nowrap;'],
                'buttons' => [
                    'view' => function($url, $model) {
                        return Html::a('<i class="bi bi-eye"></i>', ['fighter/view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Просмотр',
                        ]);
                    },
                    'update' => function($url, $model) {
                        return Html::a('<i class="bi bi-pencil"></i>', ['fighter/update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Редактировать',
                        ]);
                    },
                    'delete' => function($url, $model) {
                        return Html::a('<i class="bi bi-trash"></i>', ['fighter/delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
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

    <div class="mt-4">
        <?= Html::a('<i class="bi bi-plus-circle"></i> Добавить нового бойца', ['fighter/create'], [
            'class' => 'btn btn-success btn-lg'
        ]) ?>
        
        <?= Html::a('<i class="bi bi-house"></i> На главную', ['site/index'], [
            'class' => 'btn btn-outline-secondary ms-2'
        ]) ?>
    </div>
</div>