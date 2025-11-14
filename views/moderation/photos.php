<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Модерация фотографий';
$this->params['breadcrumbs'][] = ['label' => 'Модерация бойцов', 'url' => ['verify']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="moderation-photos text-center py-5">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="lead">Раздел находится в разработке.</p>
    <p>Скоро здесь появятся инструменты для проверки загруженных фотографий.</p>
    <?= Html::a('Вернуться к бойцам', ['verify'], ['class' => 'btn btn-primary mt-3']) ?>
</div>
