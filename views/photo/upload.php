<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FighterPhoto */
/* @var $fighter app\models\Fighter */

$this->title = 'Загрузка фотографии для бойца: ' . $fighter->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = ['label' => $fighter->fullName, 'url' => ['fighter/view', 'id' => $fighter->id]];
$this->params['breadcrumbs'][] = ['label' => 'Фотографии', 'url' => ['index', 'fighterId' => $fighter->id]];
$this->params['breadcrumbs'][] = 'Загрузка';
?>
<div class="photo-upload">

    <div class="row">
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="glyphicon glyphicon-arrow-left"></i> Назад к фотографиям', 
                ['index', 'fighterId' => $fighter->id], 
                ['class' => 'btn btn-default']
            ) ?>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>Внимание:</strong> Все загружаемые фотографии проходят проверку модератором перед публикацией.
    </div>

    <div class="photo-form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'photo_file')->fileInput([
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                ])->label('Выберите файл изображения') ?>

                <?= $form->field($model, 'description')->textarea(['rows' => 4])->label('Описание (необязательно)') ?>

                <div class="form-group">
                    <?= Html::submitButton('<i class="glyphicon glyphicon-upload"></i> Загрузить', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Требования к фотографиям</h3>
                    </div>
                    <div class="panel-body">
                        <ul>
                            <li><strong>Максимальный размер:</strong> 10MB</li>
                            <li><strong>Разрешенные форматы:</strong> JPEG, PNG, GIF, WebP</li>
                            <li><strong>Рекомендуемое разрешение:</strong> не менее 400x400 пикселей</li>
                            <li><strong>Содержание:</strong> только фотографии бойцов ВОВ</li>
                        </ul>
                        <div class="alert alert-warning">
                            <small>
                                <strong>Важно:</strong> Фотографии должны быть четкими, хорошо освещенными 
                                и содержать только соответствующие тематике изображения.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>