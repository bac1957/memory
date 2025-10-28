<?php
namespace app\models;

use Yii;
use yii\base\Model;

class SignupForm extends Model
{
    public $username;
    public $last_name;
    public $first_name;
    public $middle_name;
    public $email;
    public $password;
    public $password_repeat;

    public function rules()
    {
        return [
            [['username', 'last_name', 'first_name', 'middle_name', 'email', 'password', 'password_repeat'], 'required'],
            [['username', 'last_name', 'first_name', 'middle_name', 'email'], 'trim'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'Этот логин уже занят.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 255],
            
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже занят.'],

            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Логин',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'email' => 'Email',
            'password' => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            Yii::error('Validation failed: ' . print_r($this->errors, true));
            return null;
        }
        
        $user = new User();
        $user->username = $this->username;
        $user->last_name = $this->last_name;
        $user->first_name = $this->first_name;
        $user->middle_name = $this->middle_name;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_PENDING;
        $user->role = User::ROLE_USER;
        
        if ($user->save()) {
            Yii::info('User registered: ' . $user->username);
            return $user;
        } else {
            Yii::error('User save failed: ' . print_r($user->errors, true));
            return null;
        }
    }
}