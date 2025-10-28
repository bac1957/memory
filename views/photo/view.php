<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\helpers\PhotoHelper;

$this->title = $model->getFullName();
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="fighter-view">
    <div class="row">
        <div class="col-md-4">
            <?php if ($model->mainPhoto): ?>
                <div class="main-photo">
                    <a href="<?= PhotoHelper::getPhotoUrl($model->mainPhoto->id) ?>" 
                       data-lightbox="fighter-gallery" 
                       data-title="<?= Html::encode($model->getFullName()) ?>">
                        <?= PhotoHelper::img($model->mainPhoto->id, [
                            'class' => 'img-responsive img-thumbnail main-photo-img',
                            'alt' => $model->getFullName()
                        ]) ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="no-photo alert alert-info">
                    <i class="glyphicon glyphicon-picture"></i>
                    <p>Фотография отсутствует</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <h1><?= Html::encode($this->title) ?></h1>
            
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    // ... остальные атрибуты ...
                ],
            ]) ?>
        </div>
    </div>

    <!-- Галерея всех фотографий -->
    <?php if (count($model->photos) > 0): ?>
    <div class="photos-gallery mt-4">
        <h3>Фотогалерея</h3>
        <div class="row">
            <?php foreach ($model->photos as $photo): ?>
            <div class="col-md-3 col-sm-4 col-xs-6 photo-item">
                <div class="thumbnail">
                    <a href="<?= PhotoHelper::getPhotoUrl($photo->id) ?>" 
                       data-lightbox="fighter-gallery" 
                       data-title="<?= Html::encode($model->getFullName()) ?><?= $photo->photo_year ? ' (' . $photo->photo_year . ')' : '' ?>">
                        <?= PhotoHelper::img($photo->id, [
                            'class' => 'img-responsive gallery-thumb',
                            'alt' => $model->getFullName()
                        ]) ?>
                    </a>
                    <div class="caption">
                        <?php if ($photo->photo_year): ?>
                            <p class="text-muted small"><?= $photo->photo_year ?> год</p>
                        <?php endif; ?>
                        <?php if ($photo->description): ?>
                            <p class="small"><?= nl2br(Html::encode($photo->description)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Подключаем lightbox для галереи
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
?>
