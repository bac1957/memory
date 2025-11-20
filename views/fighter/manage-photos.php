<?php
// views/fighter/manage-photos.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $photoModel app\models\FighterPhoto */
/* @var $photos app\models\FighterPhoto[] */

$this->title = 'Управление фотографиями: ' . $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = ['label' => $model->fullName, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Управление фотографиями';

// CSS для галереи
$this->registerCss(<<<CSS
.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.photo-item {
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.photo-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.photo-actions {
    position: absolute;
    top: 5px;
    right: 5px;
    display: flex;
    gap: 5px;
}

.photo-info {
    padding: 10px;
    background: white;
}

.photo-main-badge {
    position: absolute;
    top: 5px;
    left: 5px;
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    background: #fafafa;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.photo-placeholder {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    color: #6c757d;
}
CSS
);
?>

<div class="fighter-photos-manage">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Форма загрузки новой фотографии -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Загрузка новой фотографии</h5>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>

            <div class="upload-area">
                <div class="mb-3">
                    <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                    <h5>Перетащите файл сюда или нажмите для выбора</h5>
                    <p class="text-muted">Поддерживаемые форматы: JPG, PNG, GIF. Максимальный размер: 10MB</p>
                </div>
                
                <?= $form->field($photoModel, 'photo_data')->fileInput([
                    'class' => 'form-control',
                    'accept' => 'image/jpeg,image/png,image/gif'
                ])->label(false) ?>
            </div>

            <?= $form->field($photoModel, 'description')->textarea([
                'rows' => 3,
                'placeholder' => 'Описание фотографии (необязательно)'
            ]) ?>

            <?= $form->field($photoModel, 'photo_year')->textInput([
                'placeholder' => 'Год фотографии (необязательно)',
                'maxlength' => 4
            ]) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="bi bi-upload"></i> Загрузить фотографию', [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="bi bi-arrow-left"></i> Назад к редактированию', ['update', 'id' => $model->id], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Галерея существующих фотографий -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Загруженные фотографии 
                <span class="badge bg-secondary"><?= count($photos) ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($photos)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-images" style="font-size: 64px; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">Фотографии еще не загружены</p>
                </div>
            <?php else: ?>
                <div class="photo-gallery">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item">
                            <!-- Изображение -->
                            <?php if ($photo->thumbnail_data): ?>
                                <img src="data:<?= $photo->mime_type ?>;base64,<?= base64_encode($photo->thumbnail_data) ?>" 
                                     class="photo-image" 
                                     alt="Фотография бойца">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <span>Нет превью</span>
                                </div>
                            <?php endif; ?>

                            <!-- Бейдж главной фотографии -->
                            <?php if ($photo->is_main): ?>
                                <span class="badge bg-success photo-main-badge">
                                    <i class="bi bi-star-fill"></i> Главная
                                </span>
                            <?php endif; ?>

                            <!-- Кнопки действий -->
                            <div class="photo-actions">
                                <?php if (!$photo->is_main): ?>
                                    <?= Html::a('<i class="bi bi-star"></i>', 
                                        ['set-main-photo', 'id' => $model->id, 'photoId' => $photo->id], 
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'Сделать главной',
                                            'data' => ['method' => 'post']
                                        ]
                                    ) ?>
                                <?php endif; ?>
                                
                                <?= Html::a('<i class="bi bi-trash"></i>', 
                                    ['delete-photo', 'id' => $model->id, 'photoId' => $photo->id], 
                                    [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'Удалить',
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить эту фотографию?',
                                            'method' => 'post'
                                        ]
                                    ]
                                ) ?>
                            </div>

                            <!-- Информация о фотографии -->
                            <div class="photo-info">
                                <?php if (!empty($photo->description)): ?>
                                    <p class="small mb-1"><?= Html::encode($photo->description) ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($photo->photo_year)): ?>
                                    <span class="badge bg-light text-dark"><?= $photo->photo_year ?> год</span>
                                <?php endif; ?>
                                
                                <div class="small text-muted mt-1">
                                    <?= Yii::$app->formatter->asDate($photo->created_at, 'short') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
