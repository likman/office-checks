<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_human_bindings}}`.
 */
class m201004_071556_create_event_human_bindings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%event_human_bindings}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'id_event' => $this->bigInteger()->unsigned(),
            'id_human' => $this->integer()->unsigned(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->null(),
            'modified_at' => $this->timestamp()->null(),
            'modified_by' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey(
            'fk-event_hb-id_event',
            'event_human_bindings',
            'id_event',
            'events',
            'id');

        $this->addForeignKey(
            'fk-event_hb-id_human',
            'event_human_bindings',
            'id_human',
            'humans',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-event_hb-id_event', 'event_human_bindings');
        $this->dropForeignKey('fk-event_hb-id_human', 'event_human_bindings');
        $this->dropTable('{{%event_human_bindings}}');
    }
}
