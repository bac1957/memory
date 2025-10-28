<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PENDING = 2;
    
    const ROLE_USER = 'User';
    const ROLE_MODERATOR = 'Moderator';
    const ROLE_ADMIN = 'Admin';
    const ROLE_DEVELOP = 'Develop';

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['username', 'last_name', 'first_name', 'middle_name', 'email', 'password_hash'], 'required'],
            [['username', 'email'], 'unique'],
            ['email', 'email'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['status', 'in', 'range' => [self::STATUS_BLOCKED, self::STATUS_ACTIVE, self::STATUS_PENDING]],
            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [self::ROLE_USER, self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_DEVELOP]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Логин',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'email' => 'Email',
            'password_hash' => 'Пароль',
            'role' => 'Роль',
            'status' => 'Статус',
            'created_at' => 'Дата регистрации',
            'updated_at' => 'Дата обновления',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->auth_key = Yii::$app->security->generateRandomString();
            }
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
    }

    /**
     * IdentityInterface methods
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds user by username
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Validates password
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Полное имя пользователя
     */
    public function getFullName()
    {
        return $this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name;
    }

    /**
     * Короткое имя (Фамилия И.О.)
     */
    public function getShortName()
    {
        return $this->last_name . ' ' . mb_substr($this->first_name, 0, 1) . '.' . mb_substr($this->middle_name, 0, 1) . '.';
    }

    /**
     * Проверяет, является ли пользователь администратором
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_DEVELOP;
    }

    /**
     * Проверяет, является ли пользователь модератором
     */
    public function isModerator()
    {
        return in_array($this->role, [self::ROLE_MODERATOR, self::ROLE_ADMIN, self::ROLE_DEVELOP]);
    }

    /**
     * Проверяет, является ли пользователь разработчиком
     */
    public function isDevelop()
    {
        return $this->role === self::ROLE_DEVELOP;
    }

    /**
     * Проверяет, активен ли пользователь
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Проверяет, ожидает ли пользователь подтверждения
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Получить список ролей
     */
    public static function getRoles()
    {
        return [
            self::ROLE_USER => 'Пользователь',
            self::ROLE_MODERATOR => 'Модератор',
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_DEVELOP => 'Разработчик',
        ];
    }

    /**
     * Получить список статусов
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_BLOCKED => 'Заблокирован',
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_PENDING => 'Ожидает подтверждения',
        ];
    }

    /**
     * Получить название роли
     */
    public function getRoleName()
    {
        $roles = self::getRoles();
        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Получить название статуса
     */
    public function getStatusName()
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }
}
