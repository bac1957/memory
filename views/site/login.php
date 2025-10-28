<?php
/*
**********************************************************************
*			               М Е М О Р И А Л
*         Мемориал участников во Второй мировой войне 
* ==================================================================
*           Авторизация пользователя
* 
* @file views/site/login.php
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

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login">
    <div class="row justify-content-center">
        <!--div class="col-lg-6 col-md-8 col-sm-10" -->
            <div class="card mt-5">
                <div class="card-header">
                    <h1 class="card-title text-center mb-0"><?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'form-label'],
                            'inputOptions' => ['class' => 'form-control'],
                            'errorOptions' => ['class' => 'invalid-feedback'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'username')->textInput([
                        'autofocus' => true,
                        'placeholder' => 'Введите ваш логин'
                    ]) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => 'Введите ваш пароль'
                    ]) ?>

                    <?= $form->field($model, 'rememberMe')->checkbox([
                        'template' => "<div class=\"form-check\">{input} {label}</div>\n<div>{error}</div>",
                    ]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Войти', [
                            'class' => 'btn btn-primary btn-block w-100', 
                            'name' => 'login-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="text-center mt-3">
                        <p>Нет аккаунта? <?= Html::a('Зарегистрируйтесь', ['site/signup']) ?></p>
                    </div>
                </div>
            </div>
        <!-- /div -->
    </div>
</div>