<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\PhotoHelper;

/** @var $model \app\models\Fighter */
?>

<div class="fighter-card">
    <div class="fighter-card-inner">
        <!-- Фотография -->
        <div class="fighter-photo">
            <?php if ($model->mainPhoto): ?>
                <a href="<?= Url::to(['fighter/view', 'id' => $model->id]) ?>">
                    <?= PhotoHelper::img($model->mainPhoto->id, [
                        'class' => 'fighter-thumb',
                        'alt' => $model->getFullName(),
                    ]) ?>
                </a>
            <?php else: ?>
                <div class="no-photo-placeholder">
                    <i class="glyphicon glyphicon-user"></i>
                    <span>Фото отсутствует</span>
                </div>
            <?php endif; ?>
            
            <!-- Статус -->
            <div class="fighter-status-badge" style="background-color: <?= $model->status->color ?>">
                <?= $model->status->name ?>
            </div>
        </div>

        <!-- Информация -->
        <div class="fighter-info">
            <h4 class="fighter-name">
                <?= Html::a(
                    $model->getFullName(), 
                    ['fighter/view', 'id' => $model->id],
                    ['class' => 'fighter-link']
                ) ?>
            </h4>
            
            <div class="fighter-details">
                <?php if ($model->military_rank): ?>
                    <div class="fighter-rank">
                        <i class="glyphicon glyphicon-star"></i>
                        <?= Html::encode($model->military_rank) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($model->birth_year): ?>
                    <div class="fighter-years">
                        <i class="glyphicon glyphicon-calendar"></i>
                        <?= $model->getYearsOfLife() ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($model->military_unit): ?>
                    <div class="fighter-unit" title="<?= Html::encode($model->military_unit) ?>">
                        <i class="glyphicon glyphicon-map-marker"></i>
                        <?= Html::encode(mb_strimwidth($model->military_unit, 0, 50, '...')) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($model->awards): ?>
                <div class="fighter-awards">
                    <small class="text-muted">
                        <i class="glyphicon glyphicon-medkit"></i>
                        Награды: <?= Html::encode(mb_strimwidth($model->awards, 0, 60, '...')) ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>