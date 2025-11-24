<?php

use yii\db\Migration;

class m251124_074135_add_uploaded_by_to_fighter_photo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%fighter_photo}}', 'uploaded_by', $this->integer()->after('file_size'));
        
         $this->addForeignKey(
             'fk-fighter_photo-uploaded_by',
             '{{%fighter_photo}}',
             'uploaded_by',
             '{{%user}}',
             'id',
             'SET NULL',
             'CASCADE'
         );
        
        // Добавляем индекс для производительности
        $this->createIndex(
            'idx-fighter_photo-uploaded_by',
            '{{%fighter_photo}}',
            'uploaded_by'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // $this->dropForeignKey('fk-fighter_photo-uploaded_by', '{{%fighter_photo}}');
        $this->dropIndex('idx-fighter_photo-uploaded_by', '{{%fighter_photo}}');
        $this->dropColumn('{{%fighter_photo}}', 'uploaded_by');
    }
}