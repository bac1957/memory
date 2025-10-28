<?php
// views/fighter/_additional_info_tab.php

use yii\helpers\Html;

/* @var $model app\models\Fighter */
?>

<div class="additional-info-tab">
    <?php if ($model->additional_info): ?>
        <div class="card">
            <div class="card-body">
                <div class="additional-info-content">
                    <?= nl2br(Html::encode($model->additional_info)) ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Дополнительная информация не заполнена.
        </div>
    <?php endif; ?>
</div>