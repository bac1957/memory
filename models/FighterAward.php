<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Модель для таблицы "fighter_award"
 * 
 * @property int $id
 * @property int $fighter_id
 * @property int $award_id
 * @property string|null $award_date
 * @property string|null $award_reason
 * @property resource|null $document_photo
 * @property string|null $document_mime_type
 * @property string|null $document_description
 * @property string $created_at
 * @property string|null $updated_at
 * 
 * @property Fighter $fighter
 * @property MilitaryAward $award
 */
class FighterAward extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fighter_award';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fighter_id', 'award_id'], 'required'],
            [['fighter_id', 'award_id'], 'integer'],
            [['award_reason', 'document_description'], 'string'],
            [['award_date'], 'string', 'max' => 100],
            [['document_mime_type'], 'string', 'max' => 50],
            [['document_photo'], 'safe'],
            
            [['fighter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fighter::class, 'targetAttribute' => ['fighter_id' => 'id']],
            [['award_id'], 'exist', 'skipOnError' => true, 'targetClass' => MilitaryAward::class, 'targetAttribute' => ['award_id' => 'id']],
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
            'award_id' => 'Награда',
            'award_date' => 'Дата награждения',
            'award_reason' => 'За что награжден',
            'document_photo' => 'Фото документа',
            'document_mime_type' => 'Тип документа',
            'document_description' => 'Описание документа',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * Gets query for [[Fighter]].
     */
    public function getFighter()
    {
        return $this->hasOne(Fighter::class, ['id' => 'fighter_id']);
    }

    /**
     * Gets query for [[Award]].
     */
    public function getAward()
    {
        return $this->hasOne(MilitaryAward::class, ['id' => 'award_id']);
    }

    /**
     * Получить название награды
     */
    public function getAwardName()
    {
        return $this->award ? $this->award->name : 'Неизвестная награда';
    }
}