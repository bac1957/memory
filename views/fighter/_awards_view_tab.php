<?php
// views/fighter/_awards_view_tab.php

use yii\helpers\Html;

/* @var $model app\models\Fighter */
/* @var $awards app\models\FighterAward[] */
?>

<div class="awards-tab">
    <?php if ($awards): ?>
        <div class="row">
            <?php foreach ($awards as $award): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title"><?= Html::encode($award->militaryAward->name ?? 'Неизвестная награда') ?></h6>
                            
                            <?php if ($award->award_date): ?>
                                <p class="card-text">
                                    <strong>Дата награждения:</strong><br>
                                    <?= Html::encode($award->award_date) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($award->award_reason): ?>
                                <p class="card-text">
                                    <strong>За что награжден:</strong><br>
                                    <?= nl2br(Html::encode($award->award_reason)) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($award->document_description): ?>
                                <p class="card-text">
                                    <strong>Описание документа:</strong><br>
                                    <?= nl2br(Html::encode($award->document_description)) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Награды не добавлены.
        </div>
    <?php endif; ?>
</div>