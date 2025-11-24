<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FighterPhoto */
/* @var $fighter app\models\Fighter */

$this->title = 'Редактирование фотографии для бойца: ' . $fighter->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = ['label' => $fighter->fullName, 'url' => ['fighter/view', 'id' => $fighter->id]];
$this->params['breadcrumbs'][] = ['label' => 'Фотографии', 'url' => ['index', 'fighterId' => $fighter->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="photo-update">

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

    <div class="row">
        <div class="col-md-4">
            <div class="text-center">
                <h4>Миниатюра:</h4>
                <?= Html::img(['thumbnail', 'id' => $model->id], [
                    'class' => 'img-thumbnail',
                    'style' => 'max-width: 200px; max-height: 200px;',
                    'alt' => 'Миниатюра фото'
                ]) ?>
            </div>
        </div>
        <div class="col-md-8">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 6])->label('Описание фотографии') ?>
            
            <?= $form->field($model, 'photo_year')->textInput([
                'type' => 'number',
                'min' => 1900,
                'max' => 2025
            ])->label('Год фотографии') ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="glyphicon glyphicon-floppy-disk"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Отмена', ['index', 'fighterId' => $fighter->id], ['class' => 'btn btn-default']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>