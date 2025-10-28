<?php
// views/fighter/_biography_tab.php

use yii\helpers\Html;

/* @var $model app\models\Fighter */
?>

<div class="biography-tab">
    <?php if ($model->biography): ?>
        <div class="card">
            <div class="card-body">
                <div class="biography-content">
                    <?= nl2br(Html::encode($model->biography)) ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Биография не заполнена.
        </div>
    <?php endif; ?>
</div>