<?php

use yii\db\Migration;

class m251013_065550_add_user extends Migration
{
    public function safeUp()
    {
        // Добавляем администратора
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'last_name' => '',
            'first_name' => 'Администартор',
            'middle_name' => '',
            'email' => 'admin@memorial.ru',
            'password_hash' => Yii::$app->security->generatePasswordHash('123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'role' => 'Admin',
            'status' => 1, // активен
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Добавляем модератора
        $this->insert('{{%user}}', [
            'username' => 'moderator',
            'last_name' => '',
            'first_name' => 'Модератор',
            'middle_name' => '',
            'email' => 'moderator@memorial.ru',
            'password_hash' => Yii::$app->security->generatePasswordHash('123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'role' => 'Moderator',
            'status' => 1, // активен
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%user}}', ['username' => ['admin', 'moderator']]);
    }
}
