<?php
/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Письма и воспоминания</h5>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addLetterModal">
            <i class="fas fa-plus"></i> Добавить письмо
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            В этом разделе можно хранить сканы фронтовых писем, воспоминания родственников, 
            выдержки из дневников и другие личные документы бойца.
        </div>
        
        <?php if (false): // Заглушка для будущего функционала ?>
            <div class="letters-list">
                <div class="list-group">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Фронтовое письмо от 12.03.1943</h6>
                            <small>Добавлено: 20.01.2024</small>
                        </div>
                        <p class="mb-1">Письмо родным с фронта. Сохранен оригинальный текст.</p>
                        <small class="text-muted">2 страницы</small>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary">Читать</button>
                            <button class="btn btn-sm btn-outline-secondary">Редактировать</button>
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Функционал управления письмами и воспоминаниями находится в разработке.
                В будущем здесь можно будет добавлять и читать фронтовые письма бойца.
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6><i class="fas fa-envelope text-primary"></i> Фронтовые письма</h6>
                            <p class="small text-muted">Сканы оригинальных писем с фронта с расшифровкой текста</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6><i class="fas fa-book text-success"></i> Воспоминания</h6>
                            <p class="small text-muted">Записи воспоминаний родственников и однополчан</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно для добавления письма -->
<div class="modal fade" id="addLetterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить письмо или воспоминание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="letter-form">
                    <div class="mb-3">
                        <label class="form-label">Тип записи</label>
                        <select class="form-control">
                            <option value="">Выберите тип</option>
                            <option value="front_letter">Фронтовое письмо</option>
                            <option value="memory">Воспоминание</option>
                            <option value="diary">Дневниковая запись</option>
                            <option value="interview">Интервью</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Дата документа</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Место</label>
                                <input type="text" class="form-control" placeholder="Место написания">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Заголовок</label>
                        <input type="text" class="form-control" placeholder="Краткое описание">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст</label>
                        <textarea class="form-control" rows="6" placeholder="Текст письма или воспоминания"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Скан оригинала (если есть)</label>
                        <input type="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>