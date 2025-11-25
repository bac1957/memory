<?php
// views/fighter/_photos_tab.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\FighterPhoto;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-images"></i> Фотографии бойца
            </h5>
            <?= Html::a('<i class="bi bi-plus-circle"></i> Добавить фото', ['photo/upload', 'fighterId' => $model->id], [
                'class' => 'btn btn-primary btn-sm'
            ]) ?>
        </div>
    </div>
    <div class="card-body">
        
        <?php if ($model->photos): ?>
            <div class="row">
                <?php foreach ($model->photos as $photo): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 photo-card">
                            <!-- Эскиз фотографии -->
                            <div class="photo-container position-relative">
                                <?php if ($photo->thumbnail_data): ?>
                                    <!-- Показываем эскиз из базы данных -->
                                    <img src="data:image/jpeg;base64,<?= base64_encode($photo->thumbnail_data) ?>" 
                                         class="card-img-top" 
                                         alt="Эскиз фото бойца"
                                         style="height: 200px; object-fit: contain; cursor: pointer; background: #f8f9fa;"
                                         onclick="openPhotoModal('<?= Url::to(['photo/view', 'id' => $photo->id]) ?>')"
                                         title="Кликните для просмотра оригинала">
                                <?php else: ?>
                                    <!-- Если эскиза нет, используем миниатюру из actionThumbnail -->
                                    <img src="<?= Url::to(['photo/thumbnail', 'id' => $photo->id]) ?>" 
                                         class="card-img-top" 
                                         alt="Эскиз фото бойца"
                                         style="height: 200px; object-fit: contain; cursor: pointer; background: #f8f9fa;"
                                         onclick="openPhotoModal('<?= Url::to(['photo/view', 'id' => $photo->id]) ?>')"
                                         title="Кликните для просмотра оригинала">
                                <?php endif; ?>
                                
                                <!-- Бейдж статуса -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    <?php
                                    $statusBadge = [
                                        FighterPhoto::STATUS_PENDING => ['class' => 'bg-warning', 'text' => 'На модерации'],
                                        FighterPhoto::STATUS_APPROVED => ['class' => 'bg-success', 'text' => 'Одобрено'],
                                        FighterPhoto::STATUS_REJECTED => ['class' => 'bg-danger', 'text' => 'Отклонено'],
                                    ][$photo->status];
                                    ?>
                                    <span class="badge <?= $statusBadge['class'] ?>">
                                        <?= $statusBadge['text'] ?>
                                    </span>
                                </div>
                                
                                <!-- Бейдж основной фотографии -->
                                <?php if ($photo->is_main): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-primary">
                                            <i class="bi bi-star-fill"></i> Основная
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Индикатор эскиза -->
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <small class="badge bg-dark bg-opacity-75">
                                        <i class="bi bi-zoom-in"></i> Эскиз
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Описание фотографии -->
                                <div class="mb-2 flex-grow-1">
                                    <?php if ($photo->description): ?>
                                        <p class="card-text small"><?= Html::encode($photo->description) ?></p>
                                    <?php else: ?>
                                        <p class="card-text small text-muted">Описание отсутствует</p>
                                    <?php endif; ?>
                                    
                                    <?php if ($photo->photo_year): ?>
                                        <p class="card-text small">
                                            <strong>Год:</strong> <?= Html::encode($photo->photo_year) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Информация о качестве -->
                                    <p class="card-text small text-muted">
                                        <i class="bi bi-image"></i> 
                                        <?= $photo->file_size ? Yii::$app->formatter->asShortSize($photo->file_size) : 'Размер неизвестен' ?>
                                    </p>
                                </div>
                                
                                <!-- Кнопки управления -->
                                <div class="btn-group w-100" role="group">
                                    <!-- Кнопка редактирования -->
                                    <?= Html::a('<i class="bi bi-pencil"></i>', 
                                        ['photo/update', 'id' => $photo->id], 
                                        [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'title' => 'Редактировать описание'
                                        ]
                                    ) ?>
                                    
                                    <!-- Кнопка установки как основной -->
                                    <?php if ($photo->status === FighterPhoto::STATUS_APPROVED && !$photo->is_main): ?>
                                        <?= Html::a('<i class="bi bi-star"></i>', 
                                            ['photo/set-main', 'id' => $photo->id], 
                                            [
                                                'class' => 'btn btn-outline-warning btn-sm',
                                                'title' => 'Сделать основной',
                                                'data' => [
                                                    'confirm' => 'Установить эту фотографию как основную для бойца?',
                                                    'method' => 'post',
                                                ]
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled title="Недоступно">
                                            <i class="bi bi-star"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Кнопка удаления -->
                                    <?= Html::a('<i class="bi bi-trash"></i>', 
                                        ['photo/delete', 'id' => $photo->id], 
                                        [
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'title' => 'Удалить фотографию',
                                            'data' => [
                                                'confirm' => 'Вы уверены, что хотите удалить эту фотографию?',
                                                'method' => 'post',
                                            ]
                                        ]
                                    ) ?>
                                </div>
                                
                                <!-- Информация о загрузке -->
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Загружено: <?= Yii::$app->formatter->asDate($photo->created_at, 'short') ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Информация о просмотре -->
            <div class="alert alert-info mt-3">
                <small>
                    <i class="bi bi-info-circle"></i> 
                    <strong>Просмотр фотографий:</strong> Нажмите на эскиз для просмотра оригинальной фотографии в полном размере.
                    Эскизы сохраняют пропорции оригинальных изображений.
                </small>
            </div>
            
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-muted">Фотографии еще не добавлены</h5>
                <p class="text-muted mb-3">Добавьте фотографии бойца для лучшего представления</p>
                <?= Html::a('<i class="bi bi-plus-circle"></i> Добавить первую фотографию', ['photo/upload', 'fighterId' => $model->id], [
                    'class' => 'btn btn-primary'
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно для просмотра оригинальной фото -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-image"></i> Оригинальная фотография
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="loading-spinner mb-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-2 text-muted">Загрузка фотографии...</p>
                </div>
                <img id="modalPhoto" src="" alt="Оригинальная фотография" 
                     class="img-fluid" style="max-height: 80vh; display: none;">
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">
                    Для закрытия нажмите ESC или кликните вне изображения
                </small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript для модального окна и управления фото
$this->registerJs(<<<JS
function openPhotoModal(photoUrl) {
    const modal = $('#photoModal');
    const modalImg = $('#modalPhoto');
    const spinner = $('.loading-spinner');
    
    // Показываем спиннер, скрываем изображение
    spinner.show();
    modalImg.hide();
    
    // Открываем модальное окно
    modal.modal('show');
    
    // Загружаем изображение
    modalImg.on('load', function() {
        spinner.hide();
        $(this).show();
    }).on('error', function() {
        spinner.html('<div class="alert alert-danger">Ошибка загрузки фотографии</div>');
    }).attr('src', photoUrl);
}

// Закрытие модального окна по ESC
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        $('#photoModal').modal('hide');
    }
});

// Сброс модального окна при закрытии
$('#photoModal').on('hidden.bs.modal', function () {
    $('#modalPhoto').attr('src', '').hide();
    $('.loading-spinner').show();
});

// Подтверждение действий
$(document).on('click', '[data-confirm]', function(e) {
    const message = $(this).data('confirm');
    if (!confirm(message)) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});
JS
);
?>

<style>
.photo-card {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #e9ecef;
}

.photo-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.photo-container {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.photo-container img {
    transition: transform 0.3s ease;
    padding: 5px;
}

.photo-container img:hover {
    transform: scale(1.03);
}

.btn-group .btn {
    border-radius: 0;
    flex: 1;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

/* Стили для бейджей */
.badge {
    font-size: 0.7em;
    backdrop-filter: blur(10px);
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .col-md-4 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>