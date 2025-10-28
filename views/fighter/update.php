<?php
// views/fighter/update.php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $captures app\models\FighterCapture[] */
/* @var $awards app\models\FighterAward[] */
/* @var $capturesCount integer */
/* @var $awardsCount integer */ 

$this->title = 'Редактировать бойца: ' . $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="fighter-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'captures' => $captures,
        'capturesCount' => $capturesCount,
        'awards' => $awards,
        'awardsCount' => $awardsCount, 
    ]) ?>
</div>