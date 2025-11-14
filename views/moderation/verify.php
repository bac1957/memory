<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|int $statusFilter */
/** @var string $search */
/** @var array $stats */
/** @var array $statusList */

$this->title = 'Модерация бойцов';
$this->params['breadcrumbs'][] = $this->title;

$statusOptions = ['all' => 'Все статусы'] + $statusList;
?>

<div class="moderation-verify">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">На модерации</div>
                    <div class="display-6"><?= Html::encode($stats['pending']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">На доработке</div>
                    <div class="display-6"><?= Html::encode($stats['revision']) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Заблокировано</div>
                    <div class="display-6"><?= Html::encode($stats['blocked']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'options' => ['class' => 'row g-3 align-items-end'],
            ]); ?>
                <div class="col-md-6">
                    <?= Html::label('Поиск по ФИО или месту', 'moderation-search', ['class' => 'form-label']) ?>
                    <?= Html::textInput('q', $search, [
                        'id' => 'moderation-search',
                        'class' => 'form-control',
                        'placeholder' => 'Например: Иванов или Москва',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= Html::label('Статус', 'moderation-status', ['class' => 'form-label']) ?>
                    <?= Html::dropDownList('status', $statusFilter, $statusOptions, [
                        'id' => 'moderation-status',
                        'class' => 'form-select',
                    ]) ?>
                </div>
                <div class="col-md-2 text-end">
                    <?= Html::submitButton('Применить', ['class' => 'btn btn-primary w-100']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover align-middle'],
        'columns' => [
            [
                'header' => 'Фото',
                'format' => 'raw',
                'contentOptions' => ['style' => 'width: 80px;'],
                'value' => function ($model) {
                    if ($model->mainPhoto && $model->mainPhoto->thumbnail_data) {
                        $src = 'data:' . $model->mainPhoto->mime_type . ';base64,' . base64_encode($model->mainPhoto->thumbnail_data);
                        return Html::img($src, ['class' => 'img-thumbnail', 'style' => 'width:70px;height:70px;object-fit:cover;']);
                    }
                    return Html::tag('div', 'Нет фото', [
                        'class' => 'text-muted text-center border rounded',
                        'style' => 'width:70px;height:70px;display:flex;align-items:center;justify-content:center;font-size:11px;',
                    ]);
                },
            ],
            [
                'attribute' => 'last_name',
                'label' => 'Боец',
                'format' => 'raw',
                'value' => function ($model) {
                    $name = Html::tag('strong', Html::encode($model->fullName));
                    $meta = Html::tag('div',
                        Html::encode($model->birth_place ?: 'Место неизвестно'),
                        ['class' => 'text-muted small']
                    );
                    return $name . $meta;
                },
            ],
            [
                'attribute' => 'status_id',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function ($model) {
                    $color = $model->status->color ?? '#6c757d';
                    return Html::tag('span', Html::encode($model->statusName), [
                        'class' => 'badge',
                        'style' => "background: {$color};",
                    ]);
                },
            ],
            [
                'attribute' => 'user_id',
                'label' => 'Автор',
                'value' => function ($model) {
                    return $model->user ? $model->user->fullName : '—';
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:d.m.Y H:i'],
                'label' => 'Создан',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{review}',
                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
                'buttons' => [
                    'review' => function ($url, $model) {
                        return Html::a('Проверить', ['review', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']);
                    },
                ],
            ],
        ],
    ]) ?>
</div>
