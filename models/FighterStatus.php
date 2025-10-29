<?php
namespace app\models;

use yii\db\ActiveRecord;

class FighterStatus extends ActiveRecord
{
    // Константы статусов
    const STATUS_DRAFT = 1;        // Черновик
    const STATUS_MODERATION = 2;   // На модерации
    const STATUS_PUBLISHED = 3;    // Опубликован
    const STATUS_REJECTED = 4;     // Отклонен
    const STATUS_ARCHIVE = 5;      // Архив

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

    /**
     * Получить название статуса по ID
     */
    public static function getStatusName($statusId)
    {
        $statuses = self::getStatusesList();
        return $statuses[$statusId] ?? 'Неизвестно';
    }

    /**
     * Получить массив всех статусов с константами
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_MODERATION => 'На модерации',
            self::STATUS_PUBLISHED => 'Опубликован',
            self::STATUS_REJECTED => 'Отклонен',
            self::STATUS_ARCHIVE => 'Архив',
        ];
    }

    /**
     * Проверить, является ли статус опубликованным
     */
    public static function isPublished($statusId)
    {
        return $statusId === self::STATUS_PUBLISHED;
    }

    /**
     * Проверить, может ли статус быть изменен пользователем
     */
    public static function canUserEdit($statusId)
    {
        return in_array($statusId, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Получить статусы доступные для выбора пользователем
     */
    public static function getUserSelectableStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_MODERATION => 'На модерации',
        ];
    }
}