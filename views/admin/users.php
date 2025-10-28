<?php
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\User;

$this->title = 'Управление пользователями';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-users">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'attribute' => 'last_name',
                'label' => 'Фамилия',
            ],
            [
                'attribute' => 'first_name', 
                'label' => 'Имя',
            ],
            [
                'attribute' => 'middle_name',
                'label' => 'Отчество',
            ],
            'username',
            'email:email',
            [
                'attribute' => 'status',
                'label' => 'Статус',
                'value' => function($model) {
                    return $model->getStatusName();
                },
                'filter' => [
                    User::STATUS_PENDING => 'Ожидает подтверждения',
                    User::STATUS_ACTIVE => 'Активен', 
                    User::STATUS_BLOCKED => 'Заблокирован'
                ]
            ],
            [
                'attribute' => 'role',
                'label' => 'Роль',
                'value' => function($model) {
                    return $model->getRoleName();
                },
                'filter' => User::getRoles()
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата регистрации',
                'format' => 'datetime',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{activate} {block} {view}',
                'header' => 'Действия',
                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 120px;'],
                'buttons' => [
                    'activate' => function($url, $model) {
                        if ($model->status == User::STATUS_PENDING) {
                            return Html::a('<i class="bi bi-check-circle"></i>', $url, [
                                'class' => 'btn btn-sm btn-success',
                                'title' => 'Активировать пользователя',
                                'data-method' => 'post',
                                'data-confirm' => 'Активировать пользователя?'
                            ]);
                        }
                        return '';
                    },
                    'block' => function($url, $model) {
                        if ($model->status == User::STATUS_ACTIVE) {
                            return Html::a('<i class="bi bi-slash-circle"></i>', $url, [
                                'class' => 'btn btn-sm btn-danger',
                                'title' => 'Заблокировать пользователя',
                                'data-method' => 'post', 
                                'data-confirm' => 'Заблокировать пользователя?'
                            ]);
                        }
                        return '';
                    },
                    'view' => function($url, $model) {
                        return Html::a('<i class="bi bi-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-info',
                            'title' => 'Просмотр пользователя'
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>
</div>