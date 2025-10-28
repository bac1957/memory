<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Модель для таблицы "military_award"
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $institution_date
 * @property resource|null $award_svg
 * @property string $status
 * @property string $created_at
 * 
 * @property FighterAward[] $fighterAwards
 */
class MilitaryAward extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'military_award';
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
                'updatedAtAttribute' => null,
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
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['institution_date'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 10],
            [['award_svg'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название награды',
            'description' => 'Описание',
            'institution_date' => 'Дата учреждения',
            'award_svg' => 'SVG изображение',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * Gets query for [[FighterAwards]].
     */
    public function getFighterAwards()
    {
        return $this->hasMany(FighterAward::class, ['award_id' => 'id']);
    }

    /**
     * Получить активные награды
     */
    public static function getActiveAwards()
    {
        return self::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * Получить награды для dropdown
     */
    public static function getAwardsForDropdown()
    {
        $awards = self::getActiveAwards();
        return \yii\helpers\ArrayHelper::map($awards, 'id', 'name');
    }

    /**
     * Получить количество бойцов с этой наградой
     */
    public function getFighterCount()
    {
        return $this->getFighterAwards()->count();
    }

    /**
     * Проверить, используется ли награда
     */
    public function isUsed()
    {
        return $this->getFighterCount() > 0;
    }

    /**
     * Получить SVG изображение как base64
     */
    public function getAwardSvgBase64()
    {
        if (!empty($this->award_svg)) {
            // Для BLOB поля в MySQL
            if (is_resource($this->award_svg)) {
                // Если это ресурс (stream), читаем его
                $content = stream_get_contents($this->award_svg);
                rewind($this->award_svg);
            } else {
                // Если это уже строка
                $content = $this->award_svg;
            }
            
            if (!empty($content)) {
                return 'data:image/svg+xml;base64,' . base64_encode($content);
            }
        }
        return null;
    }

    /**
     * Установить SVG из файла
     */
    public function setSvgFromFile($filePath)
    {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $this->award_svg = $content;
            return true;
        }
        return false;
    }

    /**
     * Установить SVG из строки
     */
    public function setSvgFromString($svgString)
    {
        $this->award_svg = $svgString;
        return true;
    }

    /**
     * Получить информацию о награде для API
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        
        // Добавляем дополнительные поля
        $data['fighter_count'] = $this->getFighterCount();
        $data['has_svg'] = !empty($this->award_svg);
        
        return $data;
    }

    /**
     * Поиск по названию
     */
    public static function findByName($name)
    {
        return self::find()
            ->where(['name' => $name])
            ->one();
    }

    /**
     * Получить награды, учрежденные в определенный период
     */
    public static function findByInstitutionPeriod($startYear, $endYear = null)
    {
        $query = self::find();
        
        if ($endYear === null) {
            $query->where(['institution_date' => $startYear]);
        } else {
            $query->where(['between', 'institution_date', $startYear, $endYear]);
        }
        
        return $query->all();
    }

    /**
     * Перед удалением
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->isUsed()) {
            Yii::$app->session->setFlash('error', 'Невозможно удалить награду, так как она используется у бойцов.');
            return false;
        }

        return true;
    }

    /**
     * После поиска
     */
    public function afterFind()
    {
        parent::afterFind();
        
        // Убедимся, что award_svg корректно обрабатывается
        if (is_string($this->award_svg) && empty($this->award_svg)) {
            $this->award_svg = null;
        }
    }

    /**
     * Проверить существование таблицы
     */
    public static function tableExists()
    {
        $tableName = self::tableName();
        $schema = Yii::$app->db->getSchema();
        return $schema->getTableSchema($tableName) !== null;
    }

    /**
     * Получить все награды с проверкой существования таблицы
     */
    public static function getAllAwards()
    {
        if (!self::tableExists()) {
            Yii::error("Таблица " . self::tableName() . " не существует");
            return [];
        }

        return self::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }
}
