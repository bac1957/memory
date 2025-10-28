<?php
// views/fighter/create.php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Fighter */
/* @var $captures app\models\FighterCapture[] */
/* @var $awards app\models\FighterAward[] */
/* @var $capturesCount integer */
/* @var $awardsCount integer */

$this->title = 'Добавить бойца';
$this->params['breadcrumbs'][] = ['label' => 'Мои бойцы', 'url' => ['site/user-fighters']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fighter-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'captures' => $captures,
        'capturesCount' => $capturesCount,
        'awards' => $awards,
        'awardsCount' => $awardsCount, 
    ]) ?>
</div>
