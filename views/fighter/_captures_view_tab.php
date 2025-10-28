<?php
// views/fighter/_captures_view_tab.php

use yii\helpers\Html;

/* @var $model app\models\Fighter */
/* @var $captures app\models\FighterCapture[] */
?>

<div class="captures-tab">
    <?php if ($captures): ?>
        <?php foreach ($captures as $capture): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        Пленение 
                        <?php if ($capture->capture_date): ?>
                            - <?= Yii::$app->formatter->asDate($capture->capture_date) ?>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($capture->capture_place): ?>
                            <div class="col-md-6">
                                <strong>Место пленения:</strong><br>
                                <?= Html::encode($capture->capture_place) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($capture->camp_name): ?>
                            <div class="col-md-6">
                                <strong>Лагерь:</strong><br>
                                <?= Html::encode($capture->camp_name) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($capture->liberated_date): ?>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Дата освобождения:</strong><br>
                                <?= Yii::$app->formatter->asDate($capture->liberated_date) ?>
                            </div>
                            <?php if ($capture->liberated_by): ?>
                                <div class="col-md-6">
                                    <strong>Кем освобожден:</strong><br>
                                    <?= Html::encode($capture->liberated_by) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($capture->capture_circumstances): ?>
                        <div class="mt-2">
                            <strong>Обстоятельства пленения:</strong><br>
                            <?= nl2br(Html::encode($capture->capture_circumstances)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($capture->liberation_circumstances): ?>
                        <div class="mt-2">
                            <strong>Обстоятельства освобождения:</strong><br>
                            <?= nl2br(Html::encode($capture->liberation_circumstances)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($capture->additional_info): ?>
                        <div class="mt-2">
                            <strong>Дополнительная информация:</strong><br>
                            <?= nl2br(Html::encode($capture->additional_info)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">
            Информация о пленениях отсутствует.
        </div>
    <?php endif; ?>
</div>