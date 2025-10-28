<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class MilitaryRank extends ActiveRecord
{
    const CATEGORY_BEFORE_1943 = 'before_1943';
    const CATEGORY_AFTER_1943 = 'after_1943';

    public static function tableName()
    {
        return '{{%military_rank}}';
    }

    public function rules()
    {
        return [
            [['name', 'category', 'rank_order'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['category'], 'in', 'range' => [self::CATEGORY_BEFORE_1943, self::CATEGORY_AFTER_1943]],
            [['rank_order'], 'integer'],
            [['description'], 'string'],
            [['name'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование звания',
            'category' => 'Период',
            'rank_order' => 'Порядок сортировки',
            'description' => 'Описание',
        ];
    }

    public static function getCategories()
    {
        return [
            self::CATEGORY_BEFORE_1943 => 'До 1943 года',
            self::CATEGORY_AFTER_1943 => 'После 1943 года',
        ];
    }

    public function getCategoryName()
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? $this->category;
    }

    public static function getRanksByCategory($category = null)
    {
        $query = self::find()->orderBy(['rank_order' => SORT_ASC]);
        if ($category) {
            $query->andWhere(['category' => $category]);
        }
        return $query->all();
    }
}