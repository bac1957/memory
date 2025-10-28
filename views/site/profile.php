<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\User $userModel */
/** @var app\models\Profile $profileModel */

$this->title = 'Профиль пользователя';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="profile-page">
    <div class="row">
        <div class="col-md-3">
            <!-- Боковая панель с информацией -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Информация о пользователе</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <span class="text-white h4 mb-0">
                                <?= strtoupper(mb_substr($profileModel->first_name, 0, 1) . mb_substr($profileModel->last_name, 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    
                    <h5 class="text-center"><?= Html::encode($profileModel->getFullName()) ?></h5>
                    <p class="text-muted text-center mb-2">@<?= Html::encode($profileModel->username) ?></p>
                    
                    <div class="user-info">
                        <div class="mb-2">
                            <strong>Роль:</strong>
                            <span class="badge badge-primary float-right"><?= $profileModel->getRoleText() ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Статус:</strong>
                            <span class="badge badge-<?= $profileModel->user_status == 1 ? 'success' : 'warning' ?> float-right">
                                <?= $profileModel->getStatusText() ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Email:</strong>
                            <span class="float-right"><?= Html::encode($profileModel->email) ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Регистрация:</strong>
                            <span class="float-right"><?= Yii::$app->formatter->asDate($profileModel->created_at) ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>В системе:</strong>
                            <span class="float-right"><?= $profileModel->days_since_registration ?> дней</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Быстрые действия -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Быстрые действия</h6>
                </div>
                <div class="card-body">
                    <?= Html::a('Мои бойцы', ['user-fighters'], ['class' => 'btn btn-outline-primary btn-block mb-2']) ?>
                    <?= Html::a('Добавить бойца', ['fighter/create'], ['class' => 'btn btn-outline-success btn-block mb-2']) ?>
                    <?= Html::a('Изменить пароль', ['site/change-password'], ['class' => 'btn btn-outline-warning btn-block']) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Статистика -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Статистика активности</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary"><?= $profileModel->total_fighters ?></h3>
                                <small class="text-muted">Всего бойцов</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success"><?= $profileModel->approved_photos ?></h3>
                                <small class="text-muted">Одобренных фото</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning"><?= $profileModel->total_awards ?></h3>
                                <small class="text-muted">Наград</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-info"><?= $profileModel->combat_path_records ?></h3>
                                <small class="text-muted">Боевых путей</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Детальная статистика -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Статусы бойцов:</h6>
                            <?php $fightersStats = $profileModel->getFightersStats(); ?>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: <?= $fightersStats['returned'] ?>%">
                                    <?= $fightersStats['returned'] ?>%
                                </div>
                                <div class="progress-bar bg-danger" style="width: <?= $fightersStats['killed'] ?>%">
                                    <?= $fightersStats['killed'] ?>%
                                </div>
                                <div class="progress-bar bg-warning" style="width: <?= $fightersStats['missing'] ?>%">
                                    <?= $fightersStats['missing'] ?>%
                                </div>
                            </div>
                            <small>
                                <span class="text-success">● Вернулись (<?= $profileModel->returned_fighters ?>)</span> |
                                <span class="text-danger">● Погибли (<?= $profileModel->killed_fighters ?>)</span> |
                                <span class="text-warning">● Пропали (<?= $profileModel->missing_fighters ?>)</span>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6>Статус фотографий:</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: <?= $profileModel->getApprovedPhotosPercent() ?>%">
                                    <?= $profileModel->getApprovedPhotosPercent() ?>%
                                </div>
                                <div class="progress-bar bg-warning" style="width: <?= $profileModel->pending_photos > 0 ? (($profileModel->pending_photos / $profileModel->total_photos) * 100) : 0 ?>%">
                                    <?= $profileModel->pending_photos ?>
                                </div>
                            </div>
                            <small>
                                <span class="text-success">● Одобрено (<?= $profileModel->approved_photos ?>)</span> |
                                <span class="text-warning">● На модерации (<?= $profileModel->pending_photos ?>)</span>
                                <?php if ($profileModel->rejected_photos > 0): ?>
                                | <span class="text-danger">● Отклонено (<?= $profileModel->rejected_photos ?>)</span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($profileModel->last_fighter_added): ?>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Последний боец добавлен: <?= Yii::$app->formatter->asDatetime($profileModel->last_fighter_added) ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Форма редактирования профиля -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Редактирование профиля</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'profile-form',
                        'options' => ['class' => 'form-horizontal'],
                    ]); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userModel, 'last_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($userModel, 'first_name')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userModel, 'middle_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($userModel, 'email')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userModel, 'username')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Отмена', ['site/index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// CSS стили
$this->registerCss('
.profile-page .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.profile-page .user-info div {
    border-bottom: 1px solid #f8f9fa;
    padding-bottom: 8px;
}
.profile-page .user-info div:last-child {
    border-bottom: none;
}
');
?>