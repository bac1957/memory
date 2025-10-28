<?php
use app\helpers\PhotoHelper;
?>
<h1>Тест отображения фото</h1>
<p>ID фото: <?= $photo->id ?></p>
<p>MIME type: <?= $photo->mime_type ?></p>
<p>Размер: <?= round($photo->file_size / 1024, 2) ?> KB</p>

<h2>Миниатюра:</h2>
<?= PhotoHelper::img($photo->id, ['style' => 'max-width: 400px']) ?>

<h2>Оригинал:</h2>
<img src="<?= PhotoHelper::getPhotoUrl($photo->id) ?>" 
     style="max-width: 100%" 
     alt="Тестовое фото">
