<?php

namespace app\models;

use yii\base\Model;

/**
 * Форма принятия решений модератором
 */
class FighterModerationForm extends Model
{
    public $decision;
    public $comment;

    public const DECISION_APPROVE = 'approve';
    public const DECISION_BLOCK = 'block';
    public const DECISION_REVISE = 'revise';

    public function rules()
    {
        return [
            [['decision'], 'required'],
            ['decision', 'in', 'range' => array_keys($this->getDecisionOptions())],
            ['comment', 'string'],
            [
                'comment',
                'required',
                'when' => function ($model) {
                    return $model->decision === self::DECISION_REVISE;
                },
                'whenClient' => "function(attribute, value){
                    return $('input[name=\"FighterModerationForm[decision]\"]:checked').val() === '" . self::DECISION_REVISE . "';
                }",
                'message' => 'Добавьте комментарий, чтобы отправить бойца на доработку.'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'decision' => 'Решение модератора',
            'comment' => 'Комментарий для автора',
        ];
    }

    /**
     * Возвращает список доступных решений
     */
    public function getDecisionOptions(): array
    {
        return [
            self::DECISION_APPROVE => 'Опубликовать',
            self::DECISION_REVISE => 'Доработать',
            self::DECISION_BLOCK => 'Заблокировать',
        ];
    }
}
