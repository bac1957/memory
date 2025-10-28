<?php
/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Документы</h5>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
            <i class="fas fa-plus"></i> Добавить документ
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Здесь вы можете добавлять сканы документов: свидетельства о рождении, военные билеты, 
            наградные листы, архивные справки и другие документы, связанные с бойцом.
        </div>
        
        <?php if (false): // Заглушка для будущего функционала ?>
            <div class="documents-list">
                <div class="list-group">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Свидетельство о рождении</h6>
                            <small>Добавлено: 15.01.2024</small>
                        </div>
                        <p class="mb-1">Копия свидетельства о рождении из архивов</p>
                        <small class="text-muted">Размер: 2.3 MB</small>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary">Просмотреть</button>
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Функционал управления документами находится в разработке. 
                В будущем здесь можно будет загружать и просматривать сканы документов.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно для добавления документа -->
<div class="modal fade" id="addDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить документ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <small>Поддерживаемые форматы: PDF, JPG, PNG. Максимальный размер: 10MB</small>
                </div>
                <div id="document-form">
                    <div class="mb-3">
                        <label class="form-label">Тип документа</label>
                        <select class="form-control">
                            <option value="">Выберите тип документа</option>
                            <option value="birth_certificate">Свидетельство о рождении</option>
                            <option value="military_id">Военный билет</option>
                            <option value="award_document">Наградной документ</option>
                            <option value="archive_certificate">Архивная справка</option>
                            <option value="other">Другой документ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" rows="3" placeholder="Описание документа"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Файл документа</label>
                        <input type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary">Загрузить</button>
            </div>
        </div>
    </div>
</div>