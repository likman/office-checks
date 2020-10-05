<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%events}}`.
 */
class m201004_070211_create_events_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%events}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'id_event_type' => $this->integer()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->string(1000)->null(),
            'start_time' => $this->dateTime(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->null(),
            'modified_at' => $this->timestamp()->null(),
            'modified_by' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey(
            'fk-events-id_event_type',
            'events',
            'id_event_type',
            'event_types',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-events-id_event_type', 'events');
        $this->dropTable('{{%events}}');
    }
}
