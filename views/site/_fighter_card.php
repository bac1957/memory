<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var app\models\Fighter $model
 */
?>

<div class="fighter-card">
    <div class="card h-100">
        <!-- Фото бойца -->
        <div class="fighter-photo" style="height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
            <?php if ($model->mainPhoto && $model->mainPhoto->thumbnail_data): ?>
                <?= Html::img('data:' . $model->mainPhoto->mime_type . ';base64,' . base64_encode($model->mainPhoto->thumbnail_data), [
                    'class' => 'card-img-top fighter-thumbnail',
                    'alt' => $model->fullName,
                    'style' => 'max-width: 100%; max-height: 100%; width: auto; height: auto;' // Без масштабирования, только ограничение по размеру контейнера
                ]) ?>
            <?php else: ?>
                <div class="text-center">
                    <i class="bi bi-person" style="font-size: 64px; color: #ccc;"></i>
                    <p class="text-muted mt-2">Фото отсутствует</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <!-- ФИО -->
            <h5 class="card-title">
                <?= Html::encode($model->fullName) ?>
            </h5>
            
            <!-- Основная информация -->
            <div class="fighter-info">
                <?php if ($model->birthDate): ?>
                    <p class="card-text">
                        <small class="text-muted">
                            <?= Html::encode($model->birthDate) ?>
                        </small>
                    </p>
                <?php endif; ?>
                
                <?php if ($model->militaryRank): ?>
                    <p class="card-text">
                        <?= Html::encode($model->militaryRank->name) ?>
                    </p>
                <?php endif; ?>
                
                <!-- Судьба бойца -->
                <div class="return-status-badge">
                    <span class="badge <?= $model->returnStatusCssClass ?>">
                        <?= $model->returnStatusIcon ?> <?= $model->returnStatusText ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <?= Html::a('Подробнее', ['fighter/view', 'id' => $model->id], [
                'class' => 'btn btn-primary btn-sm'
            ]) ?>
        </div>
    </div>
</div>