<?php
namespace app\models;

use yii\db\ActiveRecord;

class FighterStatus extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%fighter_status}}';
    }

    /**
     * Получить список статусов для dropdown
     */
    public static function getStatusesList()
    {
        return static::find()
            ->select(['name'])
            ->indexBy('id')
            ->orderBy('id')
            ->column();
    }

    /**
     * Получить цвет статуса по ID
     */
    public static function getStatusColor($statusId)
    {
        $status = static::findOne($statusId);
        return $status ? $status->color : '#6c757d';
    }
}
