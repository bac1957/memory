<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\FighterStatus;

/** 
 * @var app\models\Fighter $model
 * @var yii\web\View $this 
 */
?>

<div class="fighter-card">
    <div class="card h-100">
        <!-- Бейдж статуса -->
        <div class="card-header status-badge">
            <?php
            $statusConfig = [
                FighterStatus::STATUS_DRAFT => ['class' => 'bg-secondary', 'label' => 'Черновик'],
                FighterStatus::STATUS_MODERATION => ['class' => 'bg-warning', 'label' => 'На модерации'],
                FighterStatus::STATUS_PUBLISHED => ['class' => 'bg-success', 'label' => 'Опубликован'],
                FighterStatus::STATUS_REJECTED => ['class' => 'bg-danger', 'label' => 'Отклонен'],
                FighterStatus::STATUS_ARCHIVE => ['class' => 'bg-info', 'label' => 'Архив'],
                FighterStatus::STATUS_BLOCKED => ['class' => 'bg-dark', 'label' => 'Заблокирован'],
            ];
            
            $status = $statusConfig[$model->status_id] ?? ['class' => 'bg-secondary', 'label' => 'Неизвестно'];
            ?>
            <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
            
            <?php if ($model->moderation_comment && in_array($model->status_id, [FighterStatus::STATUS_REJECTED, FighterStatus::STATUS_BLOCKED])): ?>
                <small class="text-muted d-block mt-1" title="<?= Html::encode($model->moderation_comment) ?>">
                    <i class="bi bi-chat-left-text"></i> 
                    <?= mb_strimwidth(Html::encode($model->moderation_comment), 0, 50, '...') ?>
                </small>
            <?php endif; ?>
        </div>

        <!-- Фото бойца -->
        <div class="fighter-photo-container">
            <?php if ($model->mainPhoto && $model->mainPhoto->thumbnail_data): ?>
                <?php
                $base64 = base64_encode($model->mainPhoto->thumbnail_data);
                $src = 'data:' . $model->mainPhoto->mime_type . ';base64,' . $base64;
                ?>
                <img src="<?= $src ?>" class="fighter-photo" alt="Фото бойца">
            <?php else: ?>
                <div class="fighter-no-photo">
                    <i class="bi bi-person" style="font-size: 48px; color: #ccc;"></i>
                    <span class="text-muted small">Нет фото</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Информация о бойце -->
        <div class="card-body">
            <h5 class="card-title fighter-name">
                <?= Html::encode($model->last_name) ?>
                <?= Html::encode($model->first_name) ?>
                <?= Html::encode($model->middle_name ?: '') ?>
            </h5>

            <div class="fighter-info">
                <?php if ($model->birth_year): ?>
                    <div class="fighter-info-item">
                        <span class="label">Год рождения:</span>
                        <span class="value"><?= Html::encode($model->birth_year) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($model->militaryRank): ?>
                    <div class="fighter-info-item">
                        <span class="label">Звание:</span>
                        <span class="value"><?= Html::encode($model->militaryRank->name) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($model->conscription_place): ?>
                    <div class="fighter-info-item">
                        <span class="label">Место призыва:</span>
                        <span class="value"><?= Html::encode($model->conscription_place) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Показываем дату создания для непромодерированных записей -->
                <?php if (!Yii::$app->user->isGuest && in_array($model->status_id, [FighterStatus::STATUS_DRAFT, FighterStatus::STATUS_MODERATION])): ?>
                    <div class="fighter-info-item">
                        <span class="label">Добавлен:</span>
                        <span class="value"><?= Yii::$app->formatter->asDate($model->created_at, 'short') ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Статус возвращения -->
            <div class="fighter-status mt-2">
                <?php
                $returnStatusClasses = [
                    'returned' => 'success',
                    'died' => 'danger', 
                    'missing' => 'warning',
                ];
                
                if (isset($returnStatusClasses[$model->returnStatus])): ?>
                    <span class="badge bg-<?= $returnStatusClasses[$model->returnStatus] ?>">
                        <?= $model->getReturnStatusLabel() ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Кнопка просмотра -->
        <div class="card-footer">
            <?= Html::a('Подробнее', ['fighter/view', 'id' => $model->id], [
                'class' => 'btn btn-primary btn-sm btn-block'
            ]) ?>
            
            <!-- Для непромодерированных записей показываем иконку редактирования -->
            <?php if (!Yii::$app->user->isGuest && in_array($model->status_id, [FighterStatus::STATUS_DRAFT, FighterStatus::STATUS_MODERATION, FighterStatus::STATUS_REJECTED])): ?>
                <?= Html::a('<i class="bi bi-pencil"></i>', ['fighter/update', 'id' => $model->id], [
                    'class' => 'btn btn-outline-secondary btn-sm mt-1',
                    'title' => 'Редактировать'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>