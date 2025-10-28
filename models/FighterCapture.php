<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель для таблицы "fighter_capture"
 * 
 * @property int $id
 * @property int $fighter_id
 * @property string|null $capture_date
 * @property string|null $capture_place
 * @property string|null $camp_name
 * @property string|null $capture_circumstances
 * @property string|null $liberated_date
 * @property string|null $liberated_by
 * @property string|null $liberation_circumstances
 * @property int|null $duration_days
 * @property string|null $additional_info
 * @property string $created_at
 * @property string|null $updated_at
 * 
 * @property Fighter $fighter
 */
class FighterCapture extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fighter_capture';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fighter_id'], 'required'],
            [['fighter_id', 'duration_days'], 'integer'],
            [['capture_circumstances', 'liberation_circumstances', 'additional_info'], 'string'],
            [['capture_date', 'liberated_date'], 'safe'],
            [['capture_place'], 'string', 'max' => 500],
            [['camp_name', 'liberated_by'], 'string', 'max' => 255],
            
            // Валидация дат
            ['capture_date', 'validateDate'],
            ['liberated_date', 'validateDate'],
            
            // Валидация: если указана дата освобождения, должна быть указана дата пленения
            ['liberated_date', 'validateLiberationDate'],
            
            [['fighter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fighter::class, 'targetAttribute' => ['fighter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fighter_id' => 'Боец',
            'capture_date' => 'Дата пленения',
            'capture_place' => 'Место пленения',
            'camp_name' => 'Название лагеря',
            'capture_circumstances' => 'Обстоятельства пленения',
            'liberated_date' => 'Дата освобождения',
            'liberated_by' => 'Кем освобожден',
            'liberation_circumstances' => 'Обстоятельства освобождения',
            'duration_days' => 'Продолжительность плена (дней)',
            'additional_info' => 'Дополнительная информация',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Валидация даты
     */
    public function validateDate($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $date = \DateTime::createFromFormat('Y-m-d', $this->$attribute);
            if (!$date || $date->format('Y-m-d') !== $this->$attribute) {
                $this->addError($attribute, 'Неверный формат даты. Используйте формат ГГГГ-ММ-ДД.');
            }
        }
    }

    /**
     * Валидация даты освобождения
     */
    public function validateLiberationDate($attribute, $params)
    {
        if (!empty($this->liberated_date) && empty($this->capture_date)) {
            $this->addError($attribute, 'Если указана дата освобождения, должна быть указана дата пленения.');
        }
        
        if (!empty($this->capture_date) && !empty($this->liberated_date)) {
            $captureDate = new \DateTime($this->capture_date);
            $liberatedDate = new \DateTime($this->liberated_date);
            
            if ($liberatedDate < $captureDate) {
                $this->addError($attribute, 'Дата освобождения не может быть раньше даты пленения.');
            }
        }
    }

    /**
     * Gets query for [[Fighter]].
     */
    public function getFighter()
    {
        return $this->hasOne(Fighter::class, ['id' => 'fighter_id']);
    }

    /**
     * Автоматический расчет продолжительности плена
     */
    public function calculateDuration()
    {
        if (!empty($this->capture_date) && !empty($this->liberated_date)) {
            $capture = new \DateTime($this->capture_date);
            $liberation = new \DateTime($this->liberated_date);
            $interval = $capture->diff($liberation);
            $this->duration_days = $interval->days;
        }
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Рассчитываем продолжительность плена
        $this->calculateDuration();

        return true;
    }

    /**
     * Получить отформатированную дату пленения
     */
    public function getFormattedCaptureDate()
    {
        if (empty($this->capture_date)) {
            return 'Не указана';
        }
        
        $date = new \DateTime($this->capture_date);
        return $date->format('d.m.Y');
    }

    /**
     * Получить отформатированную дату освобождения
     */
    public function getFormattedLiberatedDate()
    {
        if (empty($this->liberated_date)) {
            return 'Не указана';
        }
        
        $date = new \DateTime($this->liberated_date);
        return $date->format('d.m.Y');
    }

    /**
     * Получить продолжительность плена в читаемом формате
     */
    public function getFormattedDuration()
    {
        if (empty($this->duration_days)) {
            return 'Неизвестно';
        }
        
        $years = floor($this->duration_days / 365);
        $months = floor(($this->duration_days % 365) / 30);
        $days = $this->duration_days % 30;
        
        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ' . $this->getNounPlural($years, 'год', 'года', 'лет');
        }
        if ($months > 0) {
            $parts[] = $months . ' ' . $this->getNounPlural($months, 'месяц', 'месяца', 'месяцев');
        }
        if ($days > 0 || empty($parts)) {
            $parts[] = $days . ' ' . $this->getNounPlural($days, 'день', 'дня', 'дней');
        }
        
        return implode(' ', $parts);
    }

    /**
     * Склонение существительных
     */
    private function getNounPlural($number, $one, $two, $five)
    {
        $number = abs($number);
        $number %= 100;
        if ($number >= 5 && $number <= 20) {
            return $five;
        }
        $number %= 10;
        if ($number == 1) {
            return $one;
        }
        if ($number >= 2 && $number <= 4) {
            return $two;
        }
        return $five;
    }

    /**
     * Проверить, заполнены ли основные данные
     */
    public function hasBasicData()
    {
        return !empty($this->capture_date) || !empty($this->capture_place);
    }
}
