<?php

use yii\db\Migration;

class m251114_102044_add_moderation_fields_to_fighter extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%fighter}}');
        if ($table !== null && $table->getColumn('moderation_comment') === null) {
            $this->addColumn(
                '{{%fighter}}',
                'moderation_comment',
                $this->text()->comment('Комментарий модератора')
            );
        }

        $exists = (new \yii\db\Query())
            ->from('{{%fighter_status}}')
            ->where(['name' => 'Заблокирован'])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('{{%fighter_status}}', [
                'name' => 'Заблокирован',
                'color' => '#343a40',
                'description' => 'Скрыт модератором за нарушение правил',
            ]);
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%fighter}}');
        if ($table !== null && $table->getColumn('moderation_comment') !== null) {
            $this->dropColumn('{{%fighter}}', 'moderation_comment');
        }

        $this->delete('{{%fighter_status}}', ['name' => 'Заблокирован']);
    }
}
