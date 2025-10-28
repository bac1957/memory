<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Панель администратора';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->isAdmin()): ?>
    <div class="row">
        <div class="col-md-12">
            <h3>Пользователи</h3>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'last_name',
                    'first_name',
                    'middle_name',
                    'username',
                    'email:email',
                    [
                        'attribute' => 'status',
                        'value' => function($model) {
                            return $model->getStatusLabel();
                        },
                        'filter' => [
                            0 => 'Не подтвержден',
                            1 => 'Активен',
                            2 => 'Заблокирован'
                        ]
                    ],
                    'created_at:datetime',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {activate}',
                        'buttons' => [
                            'activate' => function($url, $model) {
                                if ($model->status == 0) {
                                    return Html::a('Активировать', $url, [
                                        'class' => 'btn btn-xs btn-success',
                                        'data-method' => 'post'
                                    ]);
                                }
                                return '';
                            }
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (Yii::$app->user->identity->isModerator()): ?>
    <div class="row">
        <div class="col-md-12">
            <h3>Задачи модератора</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Проверка данных</h5>
                            <p class="card-text">Проверка и верификация информации о бойцах</p>
                            <?= Html::a('Перейти к проверке', ['moderation/verify'], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Обработка фото</h5>
                            <p class="card-text">Модерация и обработка загруженных фотографий</p>
                            <?= Html::a('Перейти к фото', ['moderation/photos'], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>