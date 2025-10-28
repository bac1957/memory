<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*           Регистрация пользователя
* 
* @file views/site/signup.php
* @version 0.0.1
*
* @author Александр Васильков
* @author Home Lab, Пенза (с), 2025
* @author E-Mail bac@sura.ru
* @var yii\web\View $this
* 
* @date 08.09.2025
*
**********************************************************************
*/
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <div class="card mt-4">
                <div class="card-header">
                    <h1 class="card-title text-center mb-0"><?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'form-signup',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'form-label'],
                            'inputOptions' => ['class' => 'form-control'],
                            'errorOptions' => ['class' => 'invalid-feedback'],
                        ],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'last_name')->textInput([
                                'autofocus' => true,
                                'placeholder' => 'Фамилия'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'first_name')->textInput([
                                'placeholder' => 'Имя'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'middle_name')->textInput([
                                'placeholder' => 'Отчество'
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'username')->textInput([
                        'placeholder' => 'Придумайте логин'
                    ]) ?>

                    <?= $form->field($model, 'email')->input('email', [
                        'placeholder' => 'example@mail.ru'
                    ]) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Не менее 6 символов'
                    ]) ?>

                    <?= $form->field($model, 'password_repeat')->passwordInput([
                        'placeholder' => 'Повторите пароль'
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Зарегистрироваться', [
                            'class' => 'btn btn-success btn-block w-100', 
                            'name' => 'signup-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            После регистрации ваш аккаунт будет ожидать подтверждения администратором.
                        </small>
                    </div>

                    <div class="text-center">
                        <p>Уже есть аккаунт? <?= Html::a('Войдите', ['site/login']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>