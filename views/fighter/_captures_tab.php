<?php
// views/fighter/_captures_tab.php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $captures app\models\FighterCapture[] */
/* @var $capturesCount integer */
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Пленения</h5>
        <button type="button" id="add-capture" class="btn btn-sm btn-success">+ Добавить пленение</button>
    </div>
    <div class="card-body">
        <div id="captures-container">
            <?php foreach ($captures as $index => $capture): ?>
                <div class="capture-item border-bottom pb-3 mb-3">
                    <?= Html::activeHiddenInput($capture, "[$index]id") ?>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($capture, "[$index]capture_date")->input('date', [
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($capture, "[$index]liberated_date")->input('date', [
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($capture, "[$index]capture_place")->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Например: Под Сталинградом, у деревни...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($capture, "[$index]camp_name")->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Название лагеря военнопленных'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($capture, "[$index]liberated_by")->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Кем был освобожден'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($capture, "[$index]capture_circumstances")->textarea([
                                'rows' => 3,
                                'placeholder' => 'Обстоятельства пленения: где, при каких условиях...'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($capture, "[$index]liberation_circumstances")->textarea([
                                'rows' => 3,
                                'placeholder' => 'Как был освобожден, условия освобождения...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <?= $form->field($capture, "[$index]additional_info")->textarea([
                        'rows' => 2,
                        'placeholder' => 'Дополнительная информация о пленении'
                    ]) ?>
                    
                    <?php if ($index > 0 || (count($captures) > 1 && (!empty($capture->capture_date) || !empty($capture->capture_place)))): ?>
                        <button type="button" class="btn btn-sm btn-danger remove-capture">Удалить пленение</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($captures) || (count($captures) === 1 && empty($captures[0]->capture_date) && empty($captures[0]->capture_place))): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Информации о пленениях пока нет. Нажмите "Добавить пленение", чтобы добавить сведения о пленениях бойца.
            </div>
        <?php endif; ?>
        
        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-lightbulb"></i> <strong>Подсказка:</strong> Вы можете добавить несколько записей о пленениях, если боец был в плену несколько раз.
            </small>
        </div>
    </div>
</div>

<style>
.capture-item {
    position: relative;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    margin-bottom: 15px;
    background-color: #f8f9fa;
}

.capture-item:hover {
    background-color: #e9ecef;
}

.remove-capture {
    margin-top: 10px;
}

.form-group {
    margin-bottom: 1rem;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>