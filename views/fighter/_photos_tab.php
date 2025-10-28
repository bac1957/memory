<?php
/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Фотографии бойца</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Управление фотографиями:</strong> Вы можете добавлять, просматривать и управлять фотографиями бойца на отдельной странице.
        </div>
        
        <div class="text-center">
            <?= \yii\helpers\Html::a(
                '<i class="fas fa-images"></i> Управление фотографиями', 
                ['fighter-photo/index', 'fighterId' => $model->id], 
                ['class' => 'btn btn-primary btn-lg']
            ) ?>
        </div>
        
        <?php if ($model->photos): ?>
            <div class="mt-4">
                <h6>Текущие фотографии:</h6>
                <div class="row">
                    <?php foreach ($model->photos as $photo): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card">
                                <img src="<?= \yii\helpers\Url::to(['photo/thumbnail', 'id' => $photo->id]) ?>" 
                                     class="card-img-top" 
                                     alt="Фото бойца"
                                     style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <small class="text-muted">
                                        Статус: 
                                        <span class="badge badge-<?= $photo->status === 'approved' ? 'success' : ($photo->status === 'pending' ? 'warning' : 'danger') ?>">
                                            <?= $photo->status === 'approved' ? 'Одобрено' : ($photo->status === 'pending' ? 'На модерации' : 'Отклонено') ?>
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                Фотографии еще не добавлены. Нажмите кнопку выше, чтобы добавить фотографии бойца.
            </div>
        <?php endif; ?>
    </div>
</div>