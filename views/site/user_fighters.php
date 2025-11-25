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
                    // Проверяем наличие эскиза
                    if ($model->mainPhoto && $model->mainPhoto->thumbnail_data) {
                        // Используем эскиз из базы данных
                        $src = 'data:image/jpeg;base64,' . base64_encode($model->mainPhoto->thumbnail_data);
                        return Html::img($src, [
                            'style' => 'width: 80px; height: 80px; object-fit: contain; background: #f8f9fa; border-radius: 4px;',
                            'alt' => 'Эскиз фото бойца',
                            'class' => 'img-thumbnail'
                        ]);
                    }
                    // Если есть фото, но нет эскиза - используем оригинал с ограничением размера
                    elseif ($model->mainPhoto && $model->mainPhoto->photo_data) {
                        $src = 'data:' . $model->mainPhoto->mime_type . ';base64,' . base64_encode($model->mainPhoto->photo_data);
                        return Html::img($src, [
                            'style' => 'width: 80px; height: 80px; object-fit: contain; background: #f8f9fa; border-radius: 4px;',
                            'alt' => 'Фото бойца',
                            'class' => 'img-thumbnail'
                        ]);
                    }
                    // Если фото нет вообще
                    else {
                        return Html::tag('div', 
                            Html::tag('i', '', ['class' => 'bi bi-image text-muted', 'style' => 'font-size: 1.5rem;']),
                            [
                                'style' => 'width: 80px; height: 80px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px; border: 1px solid #dee2e6;',
                                'class' => 'text-muted',
                                'title' => 'Нет фото'
                            ]
                        );
                    }
                },
                'contentOptions' => ['style' => 'width: 100px; text-align: center; vertical-align: middle;'],
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
                    return $model->birth_year ?: 'не указан';
                },
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'militaryRank',
                'label' => 'Звание',
                'value' => function($model) {
                    return $model->militaryRank ? Html::encode($model->militaryRank->name) : 'не указано';
                },
            ],
            [
                'attribute' => 'conscription_place',
                'label' => 'Место призыва',
                'value' => function($model) {
                    if ($model->conscription_place) {
                        // Обрезаем длинный текст
                        return mb_strlen($model->conscription_place) > 30 
                            ? mb_substr($model->conscription_place, 0, 30) . '...' 
                            : $model->conscription_place;
                    }
                    return 'не указано';
                },
                'contentOptions' => ['style' => 'max-width: 200px;'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'value' => function($model) {
                    return Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i');
                },
                'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Дата обновления',
                'value' => function($model) {
                    return $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d.m.Y H:i') : '';
                },
                'contentOptions' => ['style' => 'text-align: center; white-space: nowrap;'],
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
                    
                    if ($needsComment && $model->moderation_comment) {
                        // Обрезаем длинный комментарий
                        return mb_strlen($model->moderation_comment) > 50 
                            ? mb_substr($model->moderation_comment, 0, 50) . '...' 
                            : $model->moderation_comment;
                    }
                    
                    return $needsComment ? 'Нет комментария' : '';
                },
                'contentOptions' => ['style' => 'max-width: 200px; white-space: normal;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
                'buttons' => [
                    'view' => function($url, $model) {
                        return Html::a('<i class="bi bi-eye"></i>', ['fighter/view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-info',
                            'title' => 'Просмотреть',
                            'target' => '_blank'
                        ]);
                    },
                    'update' => function($url, $model) {
                        // Делаем кнопку недоступной для бойцов на модерации
                        $isDisabled = $model->status_id == FighterStatus::STATUS_MODERATION;
                        
                        return Html::a('<i class="bi bi-pencil"></i>', ['fighter/update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-primary' . ($isDisabled ? ' disabled' : ''),
                            'title' => $isDisabled ? 'Редактирование недоступно (бойец на модерации)' : 'Редактировать',
                            'data' => $isDisabled ? [
                                'toggle' => 'tooltip',
                                'placement' => 'top'
                            ] : [],
                            'onclick' => $isDisabled ? 'return false;' : null,
                        ]);
                    },
                    'delete' => function($url, $model) {
                        // Делаем кнопку недоступной для бойцов на модерации
                        $isDisabled = $model->status_id == FighterStatus::STATUS_MODERATION;
                        
                        return Html::a('<i class="bi bi-trash"></i>', ['fighter/delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger' . ($isDisabled ? ' disabled' : ''),
                            'title' => $isDisabled ? 'Удаление недоступно (бойец на модерации)' : 'Удалить',
                            'data' => $isDisabled ? [
                                'toggle' => 'tooltip',
                                'placement' => 'top'
                            ] : [
                                'confirm' => 'Вы уверены, что хотите удалить этого бойца?',
                                'method' => 'post',
                            ],
                            'onclick' => $isDisabled ? 'return false;' : null,
                        ]);
                    },
                ],
            ],
        ],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'summary' => 'Показано <b>{begin}-{end}</b> из <b>{totalCount}</b> бойцов',
        'emptyText' => 'У вас пока нет добавленных бойцов',
    ]); ?>

    <div class="mt-3">
        <?= Html::a('<i class="bi bi-plus-circle"></i> Добавить бойца', ['fighter/create'], [
            'class' => 'btn btn-success'
        ]) ?>
        
        <?= Html::a('<i class="bi bi-question-circle"></i> Справка по статусам', '#status-help', [
            'class' => 'btn btn-outline-info ms-2',
            'data-bs-toggle' => 'collapse'
        ]) ?>
    </div>

    <!-- Справка по статусам -->
    <div class="collapse mt-3" id="status-help">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Пояснение по статусам бойцов</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="badge bg-secondary">Черновик</span> - боец находится в процессе редактирования</p>
                        <p><span class="badge bg-warning">На модерации</span> - отправлен на проверку модератору <strong>(редактирование и удаление недоступны)</strong></p>
                        <p><span class="badge bg-success">Опубликован</span> - проверен и доступен для просмотра</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="badge bg-danger">Отклонен</span> - требуется исправление замечаний</p>
                        <p><span class="badge bg-info">В архиве</span> - временно скрыт из общего доступа</p>
                        <p><span class="badge bg-dark">Заблокирован</span> - доступ ограничен администрацией</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.img-thumbnail {
    padding: 2px;
    border: 1px solid #dee2e6;
}

/* Стили для недоступных кнопок */
.btn.disabled {
    opacity: 0.5;
    pointer-events: none;
    cursor: not-allowed;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.775rem;
    }
    
    /* Уменьшаем отступы для дат на мобильных */
    .table td {
        padding: 0.5rem;
    }
}
</style>

<script>
// Инициализация тултипов для недоступных кнопок
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>