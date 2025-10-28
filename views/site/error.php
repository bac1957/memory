<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception$exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Вышеупомянутая ошибка произошла, когда веб-сервер обрабатывал ваш запрос. </p>
    <p>
        Пожалуйста, свяжитесь с администратором, если вы считаете, что это ошибка сервера. Спасибо.
    </p>

</div>
