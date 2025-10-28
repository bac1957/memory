<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profile".
 *
 * @property int $id
 * @property string $username
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $email
 * @property string $role
 * @property int $user_status
 * @property string $created_at
 * @property string $updated_at
 * @property int $total_fighters
 * @property int $returned_fighters
 * @property int $killed_fighters
 * @property int $missing_fighters
 * @property int $total_photos
 * @property int $approved_photos
 * @property int $pending_photos
 * @property int $rejected_photos
 * @property int $total_awards
 * @property int $combat_path_records
 * @property int $capture_records
 * @property string $last_fighter_added
 * @property int $days_since_registration
 */
class Profile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_status', 'total_fighters', 'returned_fighters', 'killed_fighters', 'missing_fighters', 'total_photos', 'approved_photos', 'pending_photos', 'rejected_photos', 'total_awards', 'combat_path_records', 'capture_records', 'days_since_registration'], 'integer'],
            [['username', 'last_name', 'first_name', 'middle_name', 'email'], 'string', 'max' => 255],
            [['role'], 'string', 'max' => 10],
            [['created_at', 'updated_at', 'last_fighter_added'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'email' => 'Email',
            'role' => 'Роль',
            'user_status' => 'Статус пользователя',
            'created_at' => 'Дата регистрации',
            'updated_at' => 'Дата обновления',
            'total_fighters' => 'Всего бойцов',
            'returned_fighters' => 'Вернулись с войны',
            'killed_fighters' => 'Погибли',
            'missing_fighters' => 'Пропали без вести',
            'total_photos' => 'Всего фотографий',
            'approved_photos' => 'Одобренных фото',
            'pending_photos' => 'На модерации',
            'rejected_photos' => 'Отклоненных фото',
            'total_awards' => 'Всего наград',
            'combat_path_records' => 'Записей боевого пути',
            'capture_records' => 'Записей о пленениях',
            'last_fighter_added' => 'Последний боец добавлен',
            'days_since_registration' => 'Дней с регистрации',
        ];
    }

    /**
     * Получить профиль текущего пользователя
     */
    public static function getCurrentUserProfile()
    {
        return static::find()->where(['id' => Yii::$app->user->id])->one();
    }

    /**
     * Получить статус пользователя в текстовом формате
     */
    public function getStatusText()
    {
        $statuses = [
            0 => 'Заблокирован',
            1 => 'Активен',
            2 => 'Ожидает подтверждения'
        ];
        
        return $statuses[$this->user_status] ?? 'Неизвестно';
    }

    /**
     * Получить роль пользователя в текстовом формате
     */
    public function getRoleText()
    {
        $roles = [
            'User' => 'Пользователь',
            'Moderator' => 'Модератор',
            'Admin' => 'Администратор',
            'Develop' => 'Разработчик'
        ];
        
        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Получить полное имя пользователя
     */
    public function getFullName()
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name);
    }

    /**
     * Получить процент одобренных фото
     */
    public function getApprovedPhotosPercent()
    {
        if ($this->total_photos == 0) {
            return 0;
        }
        
        return round(($this->approved_photos / $this->total_photos) * 100, 1);
    }

    /**
     * Получить статистику по статусам бойцов в процентах
     */
    public function getFightersStats()
    {
        if ($this->total_fighters == 0) {
            return [
                'returned' => 0,
                'killed' => 0,
                'missing' => 0,
            ];
        }
        
        return [
            'returned' => round(($this->returned_fighters / $this->total_fighters) * 100, 1),
            'killed' => round(($this->killed_fighters / $this->total_fighters) * 100, 1),
            'missing' => round(($this->missing_fighters / $this->total_fighters) * 100, 1),
        ];
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin()
    {
        return $this->role === 'Admin' || $this->role === 'Develop';
    }

    /**
     * Проверить, является ли пользователь модератором
     */
    public function isModerator()
    {
        return $this->role === 'Moderator' || $this->isAdmin();
    }
}