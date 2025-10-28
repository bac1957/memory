<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

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
                    if ($model->status_id == 2) { // Погиб
                        $class = 'text-danger';
                    } elseif ($model->status_id == 3) { // Пропал без вести
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
                'attribute' => 'military_rank',
                'label' => 'Звание',
                'value' => function($model) {
                    return $model->military_rank ?: '';
                },
            ],
            [
                'attribute' => 'birth_place',
                'label' => 'Место призыва',
                'value' => function($model) {
                    return $model->birth_place ?: '';
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
                    return $model->status ? $model->status->name : '';
                },
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
