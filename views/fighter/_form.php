<?php
// views/fighter/_form.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\Tabs;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $captures app\models\FighterCapture[] */
/* @var $capturesCount integer */
/* @var $awards app\models\FighterAward[] */
/* @var $awardsCount integer */

// Получаем список наград для JavaScript
$awardsList = \yii\helpers\ArrayHelper::map(
    \app\models\MilitaryAward::find()->where(['status' => 'active'])->all(), 
    'id', 
    'name'
);

// Формируем JavaScript массив наград
$awardsJsArray = [];
foreach ($awardsList as $id => $name) {
    $awardsJsArray[] = [
        'id' => $id,
        'name' => Html::encode($name)
    ];
}
$awardsJson = json_encode($awardsJsArray);

// JavaScript для динамического добавления/удаления полей пленений и наград
$this->registerJs(<<<JS
    let captureIndex = {$capturesCount};
    let awardIndex = {$awardsCount};
    const awardsList = {$awardsJson};
    
console.log('Form loaded');

$('#fighter-form').on('submit', function(e) {
    console.log('=== FORM SUBMISSION DEBUG ===');
    
    // Проверяем наличие обязательных полей
    const requiredFields = [
        'Fighter[last_name]',
        'Fighter[first_name]', 
        'Fighter[returnStatus]'
    ];
    
    let isValid = true;
    requiredFields.forEach(function(fieldName) {
        const field = document.querySelector('[name="' + fieldName + '"]');
        if (field && !field.value) {
            console.log('Empty required field:', fieldName);
            isValid = false;
        }
    });
    
    // Проверяем radio buttons для returnStatus
    const returnStatus = document.querySelector('input[name="Fighter[returnStatus]"]:checked');
    if (!returnStatus) {
        console.log('returnStatus not selected');
        isValid = false;
    } else {
        console.log('returnStatus selected:', returnStatus.value);
    }
    
    console.log('Form validation result:', isValid);
    console.log('Form data:', $(this).serialize());
    
    if (!isValid) {
        e.preventDefault();
        alert('Пожалуйста, заполните все обязательные поля (Фамилия, Имя, Судьба бойца)');
    } else {
        this.submit();
    }
});
    // Функция для генерации options наград
    function generateAwardOptions() {
        let options = '<option value="">Выберите награду</option>';
        awardsList.forEach(function(award) {
            options += '<option value="' + award.id + '">' + award.name + '</option>';
        });
        return options;
    }
    
    // Функции для пленений
    $('#add-capture').on('click', function() {
        const template = `
            <div class="capture-item dynamic-item">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Дата пленения</label>
                            <input type="date" class="form-control" name="FighterCapture[\${captureIndex}][capture_date]">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Дата освобождения</label>
                            <input type="date" class="form-control" name="FighterCapture[\${captureIndex}][liberated_date]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Место пленения</label>
                            <input type="text" class="form-control" name="FighterCapture[\${captureIndex}][capture_place]" maxlength="500">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Название лагеря</label>
                            <input type="text" class="form-control" name="FighterCapture[\${captureIndex}][camp_name]" maxlength="255">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Кем освобожден</label>
                            <input type="text" class="form-control" name="FighterCapture[\${captureIndex}][liberated_by]" maxlength="255">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Обстоятельства пленения</label>
                            <textarea class="form-control" name="FighterCapture[\${captureIndex}][capture_circumstances]" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Обстоятельства освобождения</label>
                            <textarea class="form-control" name="FighterCapture[\${captureIndex}][liberation_circumstances]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Дополнительная информация</label>
                    <textarea class="form-control" name="FighterCapture[\${captureIndex}][additional_info]" rows="2"></textarea>
                </div>
                
                <button type="button" class="btn btn-sm btn-danger remove-capture remove-btn">
                    <i class="bi bi-trash"></i> Удалить
                </button>
            </div>
        `;
        
        $('#captures-container').append(template);
        captureIndex++;
    });
    
    // Функции для наград
    $('#add-award').on('click', function() {
        const template = `
            <div class="award-item dynamic-item">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Награда</label>
                            <select class="form-select" name="FighterAward[\${awardIndex}][award_id]">
                                \${generateAwardOptions()}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Дата награждения</label>
                            <input type="text" class="form-control" name="FighterAward[\${awardIndex}][award_date]" maxlength="100" placeholder="1943, январь 1944, 10.05.1945">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">За что награжден</label>
                    <textarea class="form-control" name="FighterAward[\${awardIndex}][award_reason]" rows="2" placeholder="Описание подвига или заслуг"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Описание документа</label>
                    <textarea class="form-control" name="FighterAward[\${awardIndex}][document_description]" rows="2" placeholder="Информация о наградном документе"></textarea>
                </div>
                
                <button type="button" class="btn btn-sm btn-danger remove-award remove-btn">
                    <i class="bi bi-trash"></i> Удалить
                </button>
            </div>
        `;
        
        $('#awards-container').append(template);
        awardIndex++;
    });
    
    $(document).on('click', '.remove-capture', function() {
        $(this).closest('.capture-item').remove();
    });
    
    $(document).on('click', '.remove-award', function() {
        $(this).closest('.award-item').remove();
    });
    
    // Валидация дат
    $(document).on('change', 'input[type="date"]', function() {
        const input = $(this);
        const value = input.val();
        if (value) {
            const date = new Date(value);
            const year = date.getFullYear();
            if (year < 1800 || year > new Date().getFullYear()) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback">Год должен быть между 1800 и текущим</div>');
            } else {
                input.removeClass('is-invalid');
                input.next('.invalid-feedback').remove();
            }
        }
    });
JS
);

$this->registerCssFile('@web/css/returnStatus.css', [
    'depends' => [yii\bootstrap5\BootstrapAsset::class]
]);
$this->registerCssFile('@web/css/tab.css', [
    'depends' => [yii\bootstrap5\BootstrapAsset::class]
]);
?>

<div class="fighter-form">
    <?php $form = ActiveForm::begin([
        'id' => 'fighter-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => false,
    ]); ?>

    <?= Tabs::widget([
        'items' => [
            [
                'label' => '<i class="bi bi-person-vcard"></i> Общие сведения',
                'content' => $this->render('_general_tab', [
                    'form' => $form,
                    'model' => $model,
                ]),
                'active' => true,
                'encode' => false,
            ],
            [
                'label' => '<i class="bi bi-images"></i> Фотографии',
                'content' => $this->render('_photos_tab', [
                    'model' => $model,
                ]),
                'encode' => false,
            ],
            [
                'label' => '<i class="bi bi-award"></i> Награды',
                'content' => $this->render('_awards_tab', [
                    'form' => $form,
                    'awards' => $awards,
                    'awardsList' => $awardsList,
                ]),
                'encode' => false,
            ],
            [
                'label' => '<i class="bi bi-shield-exclamation"></i> Пленения',
                'content' => $this->render('_captures_tab', [
                    'form' => $form,
                    'captures' => $captures,
                    'capturesCount' => $capturesCount,
                ]),
                'encode' => false,
            ],
            [
                'label' => '<i class="bi bi-file-text"></i> Документы',
                'content' => $this->render('_documents_tab', [
                    'model' => $model,
                ]),
                'encode' => false,
            ],
            [
                'label' => '<i class="bi bi-envelope"></i> Письма',
                'content' => $this->render('_letters_tab', [
                    'model' => $model,
                ]),
                'encode' => false,
            ],
        ],
        'options' => ['class' => 'custom-tabs'],
    ]); ?>

    <div class="form-actions">
        <div class="row">
            <div class="col-md-6">
                <?= Html::a('<i class="bi bi-arrow-left"></i> Отмена', ['site/user-fighters'], [
                    'class' => 'btn btn-secondary',
                    'onclick' => 'return confirm("Все несохраненные данные будут потеряны. Продолжить?");'
                ]) ?>
            </div>
            <div class="col-md-6 text-end">
                <?= Html::submitButton('<i class="bi bi-check-lg"></i> Сохранить бойца', [
                    'class' => 'btn btn-success btn-lg',
                    'name' => 'save-button'
                ]) ?>
                <?= Html::submitButton('<i class="bi bi-check-circle"></i> Сохранить и добавить еще', [
                    'class' => 'btn btn-primary',
                    'name' => 'save-and-add-button'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>