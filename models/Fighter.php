<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * Мodel for table "fighter"
 */
class Fighter extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 4; 
    const RETURN_STATUS_RETURNED = 'returned';
    const RETURN_STATUS_DIED = 'died';
    const RETURN_STATUS_MISSING = 'missing';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fighter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Обязательные поля
            [['last_name', 'first_name', 'status_id', 'user_id', 'returnStatus'], 'required'],
            
            // Целочисленные поля
            [['user_id', 'status_id', 'military_rank_id', 'birth_year', 'birth_month', 'birth_day', 'death_year'], 'integer'],
            
            // Текстовые поля
            [['biography', 'additional_info', 'moderation_comment'], 'string'],
            
            // Дата-время поля
            [['created_at', 'updated_at', 'moderated_at'], 'safe'],
            
            // Строковые поля с ограничением длины
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 100],
            [['birth_place', 'conscription_place', 'military_unit', 'burial_place'], 'string', 'max' => 500],
            
            // Валидация для returnStatus
            ['returnStatus', 'in', 'range' => array_keys(self::getReturnStatusOptions())],
            
            // Связи
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => FighterStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['military_rank_id'], 'exist', 'skipOnError' => true, 'targetClass' => MilitaryRank::class, 'targetAttribute' => ['military_rank_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            
            // Кастомные валидации для дат
            ['birth_year', 'validateBirthYear'],
            ['death_year', 'validateDeathYear'],
            [['birth_year', 'birth_month', 'birth_day'], 'validateBirthDate'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'status_id' => 'Статус модерации',
            'returnStatus' => 'Судьба бойца',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'birth_year' => 'Год рождения',
            'birth_month' => 'Месяц рождения',
            'birth_day' => 'День рождения',
            'death_year' => 'Год смерти',
            'birth_place' => 'Место рождения',
            'conscription_place' => 'Место призыва',
            'military_unit' => 'Воинская часть',
            'military_rank_id' => 'Воинское звание',
            'biography' => 'Биография',
            'burial_place' => 'Место захоронения',
            'additional_info' => 'Дополнительная информация',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'moderated_at' => 'Дата модерации',
            'moderator_id' => 'Модератор',
            'moderation_comment' => 'Комментарий модератора',
        ];
    }

    /**
     * @return array
     */
    public static function getReturnStatusOptions()
    {
        return [
            self::RETURN_STATUS_RETURNED => 'Вернулся с войны',
            self::RETURN_STATUS_DIED => 'Погиб',
            self::RETURN_STATUS_MISSING => 'Пропал без вести',
        ];
    }
    
    /**
     * Получить опции для RadioButton с HTML классами для стилизации
     */
    public static function getReturnStatusRadioOptions()
    {
        return [
            self::RETURN_STATUS_RETURNED => [
                'label' => 'Вернулся с войны',
                'value' => self::RETURN_STATUS_RETURNED,
                'class' => 'return-status-returned'
            ],
            self::RETURN_STATUS_DIED => [
                'label' => 'Погиб',
                'value' => self::RETURN_STATUS_DIED,
                'class' => 'return-status-died'
            ],
            self::RETURN_STATUS_MISSING => [
                'label' => 'Пропал без вести',
                'value' => self::RETURN_STATUS_MISSING,
                'class' => 'return-status-missing'
            ],
        ];
    }
    
    /**
     * @return string|null
     */
    public function getReturnStatusLabel()
    {
        $options = self::getReturnStatusOptions();
        return $options[$this->returnStatus] ?? null;
    }

    /**
     * Получить текстовое представление returnStatus
     */
    public function getReturnStatusText()
    {
        return $this->getReturnStatusLabel() ?? 'Не указано';
    }

    /**
     * Получить CSS класс для стилизации по судьбе бойца
     */
    public function getReturnStatusCssClass()
    {
        $classes = [
            self::RETURN_STATUS_RETURNED => 'bg-success',
            self::RETURN_STATUS_DIED => 'bg-danger',
            self::RETURN_STATUS_MISSING => 'bg-warning',
        ];
        
        return $classes[$this->returnStatus] ?? 'bg-secondary';
    }

    /**
     * Получить иконку для судьбы бойца
     */
    public function getReturnStatusIcon()
    {
        $icons = [
            self::RETURN_STATUS_RETURNED => '✓',
            self::RETURN_STATUS_DIED => '✗',
            self::RETURN_STATUS_MISSING => '?',
        ];
        
        return $icons[$this->returnStatus] ?? '';
    }

    /**
     * После загрузки данных
     */
    public function afterFind()
    {
        parent::afterFind();
        
    }

    /**
     * Валидация года рождения
     */
    public function validateBirthYear($attribute, $params)
    {
        if (!empty($this->birth_year)) {
            if ($this->birth_year < 1800 || $this->birth_year > date('Y')) {
                $this->addError($attribute, 'Год рождения должен быть между 1800 и текущим годом.');
            }
            
            if (!empty($this->death_year) && $this->birth_year > $this->death_year) {
                $this->addError($attribute, 'Год рождения не может быть позже года смерти.');
            }
        }
    }

    /**
     * Валидация года смерти
     */
    public function validateDeathYear($attribute, $params)
    {
        if (!empty($this->death_year)) {
            if ($this->death_year < 1800 || $this->death_year > date('Y')) {
                $this->addError($attribute, 'Год смерти должен быть между 1800 и текущим годом.');
            }
            
            if (!empty($this->birth_year) && $this->death_year < $this->birth_year) {
                $this->addError($attribute, 'Год смерти не может быть раньше года рождения.');
            }
        }
    }

    /**
     * Валидация даты рождения
     */
    public function validateBirthDate($attribute, $params)
    {
        if (!empty($this->birth_year) && !empty($this->birth_month) && !empty($this->birth_day)) {
            if (!checkdate($this->birth_month, $this->birth_day, $this->birth_year)) {
                $this->addError($attribute, 'Неверная дата рождения.');
            }
        }
    }

    /**
     * Gets query for [[User]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Status]].
     */
    public function getStatus()
    {
        return $this->hasOne(FighterStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[MilitaryRank]].
     */
    public function getMilitaryRank()
    {
        return $this->hasOne(MilitaryRank::class, ['id' => 'military_rank_id']);
    }

    /**
     * Gets query for [[Moderator]].
     */
    public function getModerator()
    {
        return $this->hasOne(User::class, ['id' => 'moderator_id']);
    }

    /**
     * Gets query for [[Captures]].
     */
    public function getCaptures()
    {
        return $this->hasMany(FighterCapture::class, ['fighter_id' => 'id']);
    }

    /**
     * Gets query for [[Awards]].
     */
    public function getAwards()
    {
        return $this->hasMany(FighterAward::class, ['fighter_id' => 'id']);
    }

    /**
     * Gets query for [[MilitaryAwards]] through [[Awards]].
     */
    public function getMilitaryAwards()
    {
        return $this->hasMany(MilitaryAward::class, ['id' => 'award_id'])
            ->via('awards');
    }

    /**
     * Получить полное ФИО бойца
     */
    public function getFullName()
    {
        $parts = [
            $this->last_name,
            $this->first_name,
            $this->middle_name
        ];
        
        return implode(' ', array_filter($parts));
    }

    /**
     * Получить краткое ФИО (Фамилия И.О.)
     */
    public function getShortName()
    {
        $shortFirstName = !empty($this->first_name) ? mb_substr($this->first_name, 0, 1) . '.' : '';
        $shortMiddleName = !empty($this->middle_name) ? mb_substr($this->middle_name, 0, 1) . '.' : '';
        
        return $this->last_name . ' ' . $shortFirstName . $shortMiddleName;
    }

    /**
     * Получить дату рождения в формате
     */
    public function getBirthDate()
    {
        if (empty($this->birth_year)) {
            return null;
        }
        
        $parts = [];
        if (!empty($this->birth_day)) {
            $parts[] = $this->birth_day;
        }
        if (!empty($this->birth_month)) {
            $parts[] = $this->birth_month;
        }
        $parts[] = $this->birth_year;
        
        return implode('.', $parts);
    }

    /**
     * Получить возраст бойца
     */
    public function getAge()
    {
        if (empty($this->birth_year)) {
            return null;
        }
        
        $endYear = !empty($this->death_year) ? $this->death_year : date('Y');
        return $endYear - $this->birth_year;
    }

    /**
     * Получить количество наград
     */
    public function getAwardsCount()
    {
        return $this->getAwards()->count();
    }

    /**
     * Получить количество записей о пленениях
     */
    public function getCapturesCount()
    {
        return $this->getCaptures()->count();
    }

    /**
     * Получить награды с информацией
     */
    public function getAwardsWithInfo()
    {
        return $this->getAwards()
            ->joinWith('award')
            ->orderBy(['award_date' => SORT_ASC])
            ->all();
    }

    /**
     * Получить пленения с данными
     */
    public function getCapturesWithData()
    {
        return $this->getCaptures()
            ->orderBy(['capture_date' => SORT_ASC])
            ->all();
    }

    /**
     * Проверить, является ли боец участником ВОВ
     */
    public function isWWIIParticipant()
    {
        return !empty($this->birth_year) && $this->birth_year <= 1927;
    }

    /**
     * Получить звание бойца
     */
    public function getRankName()
    {
        return $this->militaryRank ? $this->militaryRank->name : 'Не указано';
    }

    /**
     * Получить статус бойца
     */
    public function getStatusName()
    {
        return $this->status ? $this->status->name : 'Не указано';
    }

    /**
     * Получить цвет статуса
     */
    public function getStatusColor()
    {
        return $this->status ? $this->status->color : '#6c757d';
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Автоматическая установка user_id при создании
        if ($insert && empty($this->user_id)) {
            $this->user_id = Yii::$app->user->id;
        }

        // Обновление даты модерации при изменении статуса
        if ($this->isAttributeChanged('status_id')) {
            $this->moderated_at = new Expression('NOW()');
            $this->moderator_id = Yii::$app->user->id;
        }

        // Обновление даты обновления
        if (!$insert) {
            $this->updated_at = new Expression('NOW()');
        }

        return true;
    }

    /**
     * После сохранения
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        $action = $insert ? 'создан' : 'обновлен';
        Yii::info("Боец {$action} ID: {$this->id}, ФИО: {$this->fullName}", 'fighter');
    }

    /**
     * Поиск по ФИО
     */
    public static function findByFullName($lastName, $firstName = null, $middleName = null)
    {
        $query = self::find()->where(['last_name' => $lastName]);
        
        if ($firstName) {
            $query->andWhere(['first_name' => $firstName]);
        }
        
        if ($middleName) {
            $query->andWhere(['middle_name' => $middleName]);
        }
        
        return $query->all();
    }

    /**
     * Поиск по пользователю
     */
    public static function findByUser($userId)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])
            ->all();
    }

    /**
     * Поиск по судьбе бойца
     */
    public static function findByReturnStatus($returnStatus)
    {
        return self::find()
            ->where(['returnStatus' => $returnStatus])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])
            ->all();
    }

    /**
     * Получить бойцов для dropdown
     */
    public static function getFightersForDropdown()
    {
        $fighters = self::find()
            ->select(['id', 'last_name', 'first_name', 'middle_name'])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($fighters as $fighter) {
            $name = $fighter['last_name'] . ' ' . $fighter['first_name'];
            if (!empty($fighter['middle_name'])) {
                $name .= ' ' . $fighter['middle_name'];
            }
            $result[$fighter['id']] = $name;
        }

        return $result;
    }

    /**
     * Проверить, может ли пользователь редактировать бойца
     */
    public function canEdit($user)
    {
        if (!$user) {
            return false;
        }
        
        return $this->user_id === $user->id || $user->isAdmin();
    }

    /**
     * Получить статистику по бойцу
     */
    public function getStats()
    {
        return [
            'awards_count' => $this->getAwardsCount(),
            'captures_count' => $this->getCapturesCount(),
            'photos_count' => $this->getPhotosCount(),
            'approved_photos_count' => $this->getApprovedPhotosCount(),
            'age' => $this->getAge(),
            'is_wwii' => $this->isWWIIParticipant(),
            'return_status' => $this->returnStatusText,
        ];
    }
 
    /**
     * Gets query for [[Photos]].
     */
    public function getPhotos()
    {
        return $this->hasMany(FighterPhoto::class, ['fighter_id' => 'id']);
    }

    /**
     * Gets query for [[MainPhoto]].
     */
    public function getMainPhoto()
    {
        return $this->hasOne(FighterPhoto::class, ['fighter_id' => 'id'])
            ->where(['is_main' => 1]);
    }

    /**
     * Gets query for [[ApprovedPhotos]].
     */
    public function getApprovedPhotos()
    {
        return $this->hasMany(FighterPhoto::class, ['fighter_id' => 'id'])
            ->where(['status' => 'approved']);
    }

    /**
     * Получить количество фотографий
     */
    public function getPhotosCount()
    {
        return $this->getPhotos()->count();
    }

    /**
     * Получить количество одобренных фотографий
     */
    public function getApprovedPhotosCount()
    {
        return $this->getApprovedPhotos()->count();
    }

    /**
     * Получить главную фотографию
     */
    public function getMainPhotoData()
    {
        $mainPhoto = $this->mainPhoto;
        if ($mainPhoto && $mainPhoto->thumbnail_data) {
            return 'data:' . $mainPhoto->mime_type . ';base64,' . base64_encode($mainPhoto->thumbnail_data);
        }
        return null;
    }

    /**
     * Получить бойцов по статусу судьбы для статистики
     */
    public static function getStatsByReturnStatus()
    {
        return self::find()
            ->select(['returnStatus', 'COUNT(*) as count'])
            ->groupBy('returnStatus')
            ->asArray()
            ->all();
    }

    /**
     * Получить всех бойцов с определенной судьбой
     */
    public static function getAllByReturnStatus($status)
    {
        return self::find()
            ->where(['returnStatus' => $status])
            ->with(['user', 'militaryRank', 'status'])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC])
            ->all();
    }
}
