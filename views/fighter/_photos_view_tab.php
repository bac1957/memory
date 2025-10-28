<?php
// views/fighter/_photos_view_tab.php

use yii\helpers\Html;

/* @var $model app\models\Fighter */
/* @var $photos app\models\FighterPhoto[] */
?>

<div class="photos-tab">
    <?php if ($photos): ?>
        <div class="row">
            <?php foreach ($photos as $photo): ?>
                <div class="col-md-4 mb-3">
                    <div class="card photo-card h-100">
                        <div class="card-body text-center">
                            <?php if ($photo->thumbnail_data): ?>
                                <img src="data:<?= $photo->mime_type ?>;base64,<?= base64_encode($photo->thumbnail_data) ?>" 
                                     class="img-fluid rounded" 
                                     alt="Фото бойца"
                                     style="max-height: 200px;">
                            <?php else: ?>
                                <div class="photo-placeholder rounded d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span class="text-muted">Нет изображения</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($photo->description): ?>
                                <div class="mt-2">
                                    <small class="text-muted"><?= Html::encode($photo->description) ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($photo->photo_year): ?>
                                <div class="mt-1">
                                    <span class="badge badge-status bg-secondary"><?= $photo->photo_year ?> год</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($photo->is_main): ?>
                                <div class="mt-1">
                                    <span class="badge badge-status bg-primary">Основное фото</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Фотографии не добавлены.
        </div>
    <?php endif; ?>
</div>