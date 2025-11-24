<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\FighterPhoto;

/* @var $this yii\web\View */
/* @var $fighter app\models\Fighter */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Фотографии бойца: ' . $fighter->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = ['label' => $fighter->fullName, 'url' => ['fighter/view', 'id' => $fighter->id]];
$this->params['breadcrumbs'][] = 'Фотографии';
?>
<div class="photo-index">

    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить фото', 
                ['upload', 'fighterId' => $fighter->id], 
                ['class' => 'btn btn-success']
            ) ?>
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> Назад к бойцу', 
                ['fighter/view', 'id' => $fighter->id], 
                ['class' => 'btn btn-default']
            ) ?>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Статусы фотографий:</strong>
        <span class="label label-warning">На модерации</span> - 
        <span class="label label-success">Одобрено</span> - 
        <span class="label label-danger">Отклонено</span>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => "{items}\n{pager}",
        'columns' => [
            [
                'attribute' => 'thumbnail',
                'label' => 'Миниатюра',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        Html::img(
                            ['thumbnail', 'id' => $model->id],
                            [
                                'class' => 'img-thumbnail',
                                'style' => 'width: 100px; height: 100px; object-fit: cover;',
                                'alt' => 'Миниатюра'
                            ]
                        ),
                        ['view', 'id' => $model->id],
                        ['target' => '_blank', 'data-pjax' => 0]
                    );
                },
                'contentOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'file_name',
                'label' => 'Имя файла',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        Html::encode($model->file_name ?: 'Фото ' . $model->id),
                        ['view', 'id' => $model->id],
                        ['target' => '_blank', 'data-pjax' => 0]
                    );
                },
            ],
            [
                'attribute' => 'file_size',
                'label' => 'Размер',
                'value' => function ($model) {
                    return Yii::$app->formatter->asShortSize($model->file_size, 1);
                },
                'contentOptions' => ['style' => 'width: 100px;'],
            ],
            [
                'attribute' => 'status',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function ($model) {
                    $statusLabels = [
                        FighterPhoto::STATUS_PENDING => ['label' => 'warning', 'text' => 'На модерации'],
                        FighterPhoto::STATUS_APPROVED => ['label' => 'success', 'text' => 'Одобрено'],
                        FighterPhoto::STATUS_REJECTED => ['label' => 'danger', 'text' => 'Отклонено'],
                    ];
                    
                    $status = $statusLabels[$model->status] ?? ['label' => 'default', 'text' => 'Неизвестно'];
                    return '<span class="label label-' . $status['label'] . '">' . $status['text'] . '</span>';
                },
                'contentOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'attribute' => 'is_main',
                'label' => 'Основная',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->is_main) {
                        return '<span class="label label-primary"><i class="glyphicon glyphicon-star"></i> Основная</span>';
                    } elseif ($model->status == FighterPhoto::STATUS_APPROVED) {
                        return Html::a(
                            'Сделать основной',
                            ['set-main', 'id' => $model->id],
                            [
                                'class' => 'btn btn-xs btn-default',
                                'data' => [
                                    'confirm' => 'Установить эту фотографию как основную для бойца?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    }
                    return '';
                },
                'contentOptions' => ['style' => 'width: 140px;'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Загружена',
                'format' => 'datetime',
                'contentOptions' => ['style' => 'width: 150px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            ['view', 'id' => $model->id],
                            [
                                'title' => 'Просмотреть оригинал',
                                'target' => '_blank',
                                'data-pjax' => 0,
                            ]
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ['delete', 'id' => $model->id],
                            [
                                'title' => 'Удалить',
                                'data' => [
                                    'confirm' => 'Вы уверены, что хотите удалить эту фотографию?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },
                ],
                'contentOptions' => ['style' => 'width: 80px; text-align: center;'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <?php if ($dataProvider->getTotalCount() == 0): ?>
    <div class="alert alert-warning text-center">
        <h4>У этого бойца пока нет фотографий</h4>
        <p>Добавьте первую фотографию, нажав кнопку "Добавить фото"</p>
    </div>
    <?php endif; ?>

    <div class="well">
        <h4>Информация:</h4>
        <ul>
            <li>Все загружаемые фотографии проходят модерацию</li>
            <li>Только одобренные фотографии можно установить как основную</li>
            <li>Максимальный размер файла: 10MB</li>
            <li>Разрешенные форматы: JPEG, PNG, GIF, WebP</li>
        </ul>
    </div>
</div>