<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_types}}`.
 */
class m201004_070108_create_event_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%event_types}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull()
        ]);

        $this->insert('event_types', [
            'name' => 'Офис'
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%event_types}}');
    }
}
