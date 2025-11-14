<?php
// views/fighter/_general_tab.php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\models\FighterStatus;
use app\models\MilitaryRank;
use app\models\Fighter;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\Fighter */

// Регистрируем CSS файл
$this->registerCssFile('@web/css/returnStatus.css', [
    'depends' => [yii\bootstrap5\BootstrapAsset::class]
]);
?>

<div class="general-tab-content">
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'last_name')->textInput([
                'maxlength' => true,
                'placeholder' => 'Введите фамилию'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'first_name')->textInput([
                'maxlength' => true,
                'placeholder' => 'Введите имя'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'middle_name')->textInput([
                'maxlength' => true,
                'placeholder' => 'Введите отчество'
            ]) ?>
        </div>
    </div>

    <!-- Судьба бойца - RadioButton с Bootstrap 5 -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card return-status-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Судьба бойца <span class="text-danger">*</span></h5>
                </div>
                <div class="card-body">
                    <div class="return-status-group">
                        <?php
                        $returnStatusOptions = Fighter::getReturnStatusOptions();
                        $currentValue = $model->returnStatus;
                        
                        foreach ($returnStatusOptions as $value => $label): 
                            $isChecked = ($currentValue === $value) ? 'checked' : '';
                            $btnClass = 'btn btn-outline-primary';
                            
                            // Добавляем специальные классы для разных статусов
                            if ($value === Fighter::RETURN_STATUS_RETURNED) {
                                $btnClass = 'btn btn-outline-success';
                            } elseif ($value === Fighter::RETURN_STATUS_DIED) {
                                $btnClass = 'btn btn-outline-danger';
                            } elseif ($value === Fighter::RETURN_STATUS_MISSING) {
                                $btnClass = 'btn btn-outline-warning';
                            }
                        ?>
                            <div class="form-check form-check-inline">
                                <input class="btn-check" 
                                       type="radio" 
                                       id="returnStatus_<?= $value ?>" 
                                       name="Fighter[returnStatus]" 
                                       value="<?= $value ?>" 
                                       <?= $isChecked ?>>
                                <label class="<?= $btnClass ?>" for="returnStatus_<?= $value ?>">
                                    <?= $label ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Поле для отображения ошибок валидации -->
                    <?php if ($model->hasErrors('returnStatus')): ?>
                        <div class="invalid-feedback d-block">
                            <?= Html::error($model, 'returnStatus') ?>
                        </div>
                    <?php endif; ?>
                    
                    <small class="form-text text-muted return-status-help">
                        Выберите судьбу бойца по окончании войны
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Остальная часть формы без изменений -->
    <div class="row mb-3">
        <div class="col-md-12">
            <h6 class="border-bottom pb-2">Дата рождения</h6>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'birth_year')->textInput([
                'type' => 'number', 
                'min' => 1800, 
                'max' => date('Y'),
                'placeholder' => 'Год',
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'birth_month')->textInput([
                'type' => 'number',
                'min' => 1,
                'max' => 12,
                'placeholder' => 'Месяц (1-12)',
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'birth_day')->textInput([
                'type' => 'number',
                'min' => 1,
                'max' => 31,
                'placeholder' => 'День (1-31)',
                'class' => 'form-control'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'death_year')->textInput([
                'type' => 'number',
                'min' => 1800,
                'max' => date('Y'),
                'placeholder' => 'Год смерти',
                'class' => 'form-control'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'military_rank_id')->dropDownList(
                ArrayHelper::map(MilitaryRank::find()->orderBy('rank_order')->all(), 'id', 'name'),
                [
                    'prompt' => '-- Выберите звание --',
                    'class' => 'form-select'
                ]
            ) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <?= $form->field($model, 'birth_place')->textInput([
                'maxlength' => true,
                'placeholder' => 'Место рождения (город, область, страна)'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'conscription_place')->textInput([
                'maxlength' => true,
                'placeholder' => 'Место призыва (военкомат)'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'military_unit')->textInput([
                'maxlength' => true,
                'placeholder' => 'Воинская часть, соединение'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'burial_place')->textInput([
                'maxlength' => true,
                'placeholder' => 'Место захоронения (если известно)'
            ]) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <?= $form->field($model, 'biography')->textarea([
                'rows' => 6,
                'placeholder' => 'Биография, боевой путь, воспоминания...'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'additional_info')->textarea([
                'rows' => 4,
                'placeholder' => 'Дополнительная информация, которая может быть полезна...'
            ]) ?>
        </div>
    </div>

    <!-- Скрытые поля -->
    <?= $form->field($model, 'status_id')->hiddenInput([
        'value' => FighterStatus::find()->where(['name' => 'Черновик'])->one()->id ?? 4
    ])->label(false) ?>
</div>
