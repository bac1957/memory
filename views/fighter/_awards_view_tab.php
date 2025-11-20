<?php
// views/fighter/_awards_view_tab.php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $awards app\models\FighterAward[] */
?>

<div class="awards-tab">
    <?php if (empty($awards)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Информация о наградах отсутствует.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($awards as $award): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card award-card h-100">
                        <div class="card-body">
                            <!-- Название награды -->
                            <h6 class="card-title text-primary">
                                <?= Html::encode($award->awardName) ?>
                            </h6>
                            
                            <!-- Дата награждения -->
                            <?php if (!empty($award->award_date)): ?>
                                <div class="award-info-item">
                                    <span class="label">Дата награждения:</span>
                                    <span class="value"><?= Html::encode($award->award_date) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- За что награжден -->
                            <?php if (!empty($award->award_reason)): ?>
                                <div class="award-info-item">
                                    <span class="label">За что награжден:</span>
                                    <span class="value"><?= Html::encode($award->award_reason) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Описание документа -->
                            <?php if (!empty($award->document_description)): ?>
                                <div class="award-info-item">
                                    <span class="label">Описание документа:</span>
                                    <span class="value"><?= Html::encode($award->document_description) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Фото документа -->
                            <?php if (!empty($award->document_photo)): ?>
                                <div class="award-document mt-2">
                                    <a href="<?= Url::to(['award/document', 'id' => $award->id]) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       target="_blank">
                                        <i class="bi bi-image"></i> Посмотреть документ
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Дата создания/обновления -->
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                <?php if ($award->created_at): ?>
                                    Добавлено: <?= Yii::$app->formatter->asDate($award->created_at, 'short') ?>
                                <?php endif; ?>
                                <?php if ($award->updated_at && $award->updated_at != $award->created_at): ?>
                                    <br>Обновлено: <?= Yii::$app->formatter->asDate($award->updated_at, 'short') ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.award-card {
    border: 1px solid #e3f2fd;
    transition: all 0.3s ease;
}

.award-card:hover {
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.1);
    border-color: #2196f3;
}

.award-info-item {
    margin-bottom: 8px;
    display: flex;
    flex-direction: column;
}

.award-info-item .label {
    font-weight: 600;
    color: #666;
    font-size: 0.85rem;
}

.award-info-item .value {
    color: #333;
    word-break: break-word;
}

.card-title {
    border-bottom: 2px solid #2196f3;
    padding-bottom: 8px;
    margin-bottom: 15px;
}
</style>