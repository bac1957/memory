<?php
$this->title = 'Панель управления';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-dashboard">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h4 class="card-title"><?= $stats['totalUsers'] ?></h4>
                    <p class="card-text">Всего пользователей</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h4 class="card-title"><?= $stats['pendingUsers'] ?></h4>
                    <p class="card-text">Ожидают подтверждения</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h4 class="card-title"><?= $stats['activeUsers'] ?></h4>
                    <p class="card-text">Активных пользователей</p>
                </div>
            </div>
        </div>
    </div>
</div>
