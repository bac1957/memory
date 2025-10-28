<?php
// views/fighter/_awards_tab.php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $awards app\models\FighterAward[] */
/* @var $awardsList array */

use yii\helpers\Html;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Награды</h5>
        <button type="button" id="add-award" class="btn btn-sm btn-success">+ Добавить награду</button>
    </div>
    <div class="card-body">
        <div id="awards-container">
            <?php foreach ($awards as $index => $award): ?>
                <div class="award-item border-bottom pb-3 mb-3">
                    <?= Html::activeHiddenInput($award, "[$index]id") ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($award, "[$index]award_id")->dropDownList(
                                $awardsList, 
                                ['prompt' => 'Выберите награду']
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($award, "[$index]award_date")->textInput([
                                'maxlength' => true, 
                                'placeholder' => '1943, январь 1944, 10.05.1945'
                            ]) ?>
                        </div>
                    </div>
                    
                    <?= $form->field($award, "[$index]award_reason")->textarea([
                        'rows' => 2, 
                        'placeholder' => 'Описание подвига или заслуг'
                    ]) ?>
                    
                    <?= $form->field($award, "[$index]document_description")->textarea([
                        'rows' => 2, 
                        'placeholder' => 'Информация о наградном документе'
                    ]) ?>
                    
                    <?php if ($index > 0 || (count($awards) > 1 && !empty($award->award_id))): ?>
                        <button type="button" class="btn btn-sm btn-danger remove-award">Удалить</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($awards) || (count($awards) === 1 && empty($awards[0]->award_id))): ?>
            <div class="alert alert-info">
                Наград пока нет. Нажмите "Добавить награду", чтобы добавить информацию о наградах.
            </div>
        <?php endif; ?>
    </div>
</div>