<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Fighter $model */
/** @var app\models\FighterModerationForm $formModel */

$this->title = 'Проверка бойца: ' . $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Модерация бойцов', 'url' => ['verify']];
$this->params['breadcrumbs'][] = $model->fullName;
?>

<div class="moderation-review">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Назад к списку', ['verify'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="row gy-4">
        <div class="col-lg-8">
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Основные сведения</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'label' => 'ФИО',
                                'value' => $model->fullName,
                            ],
                            [
                                'label' => 'Дата рождения',
                                'value' => $model->birthDate ?: 'Не указана',
                            ],
                            [
                                'label' => 'Место рождения',
                                'value' => $model->birth_place ?: 'Не указано',
                            ],
                            [
                                'label' => 'Место призыва',
                                'value' => $model->conscription_place ?: 'Не указано',
                            ],
                            [
                                'label' => 'Воинское звание',
                                'value' => $model->rankName,
                            ],
                            [
                                'label' => 'Воинская часть',
                                'value' => $model->military_unit ?: 'Не указана',
                            ],
                            [
                                'label' => 'Пользователь',
                                'value' => $model->user ? $model->user->getFullName() . ' (' . $model->user->email . ')' : 'Не указан',
                            ],
                            [
                                'label' => 'Создан',
                                'format' => ['datetime', 'php:d.m.Y H:i'],
                                'value' => $model->created_at,
                            ],
                            [
                                'label' => 'Обновлен',
                                'format' => ['datetime', 'php:d.m.Y H:i'],
                                'value' => $model->updated_at,
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <?php if (!empty($model->biography)): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Биография</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($model->biography)) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($model->additional_info)): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Дополнительная информация</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(Html::encode($model->additional_info)) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($model->awards): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Награды</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($model->awardsWithInfo as $award): ?>
                                <li class="list-group-item">
                                    <strong><?= Html::encode($award->militaryAward->name ?? 'Награда') ?></strong>
                                    <?php if ($award->award_date): ?>
                                        <span class="text-muted"> — <?= Html::encode($award->award_date) ?></span>
                                    <?php endif; ?>
                                    <?php if ($award->award_reason): ?>
                                        <div class="small text-muted mt-1"><?= Html::encode($award->award_reason) ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($model->captures): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Сведения о пленении</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($model->capturesWithData as $capture): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div><strong>Дата пленения:</strong> <?= Html::encode($capture->capture_date ?: 'Не указана') ?></div>
                                <div><strong>Место:</strong> <?= Html::encode($capture->capture_place ?: 'Не указано') ?></div>
                                <?php if ($capture->capture_circumstances): ?>
                                    <div class="small text-muted mt-1"><?= Html::encode($capture->capture_circumstances) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <?php if ($model->mainPhoto && $model->mainPhoto->thumbnail_data): ?>
                        <img src="<?= Html::encode('data:' . $model->mainPhoto->mime_type . ';base64,' . base64_encode($model->mainPhoto->thumbnail_data)) ?>"
                            class="img-fluid rounded mb-3"
                            alt="<?= Html::encode($model->fullName) ?>">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height:240px;">
                            <span class="text-muted">Нет фото</span>
                        </div>
                    <?php endif; ?>

                    <div class="mb-2">
                        <span class="badge bg-secondary"><?= Html::encode($model->statusName) ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="badge <?= Html::encode($model->getReturnStatusCssClass()) ?>">
                            <?= Html::encode($model->getReturnStatusText()) ?>
                        </span>
                    </div>
                    <?php if ($model->moderation_comment): ?>
                        <div class="alert alert-warning text-start">
                            <div class="fw-bold mb-1">Комментарий предыдущей проверки:</div>
                            <div class="mb-0"><?= nl2br(Html::encode($model->moderation_comment)) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Решение модератора</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'moderation-form',
                    ]); ?>
                        <?= $form->field($formModel, 'decision')->radioList(
                            $formModel->getDecisionOptions(),
                            [
                                'itemOptions' => [
                                    'class' => 'form-check',
                                ],
                            ]
                        ) ?>
                        <?= $form->field($formModel, 'comment')->textarea([
                            'rows' => 4,
                            'placeholder' => 'Комментарий увидит автор, если требуется доработка или блокировка.',
                        ]) ?>
                        <div class="d-grid gap-2">
                            <?= Html::submitButton('Применить решение', ['class' => 'btn btn-primary btn-lg']) ?>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
