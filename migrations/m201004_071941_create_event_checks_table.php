<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_checks}}`.
 */
class m201004_071941_create_event_checks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%event_checks}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'id_event' => $this->bigInteger()->unsigned(),
            'id_human' => $this->integer()->unsigned(),
            'check_time_in' => $this->dateTime(),
            'modified_by' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey(
            'fk-event_checks-id_event',
            'event_checks',
            'id_event',
            'events',
            'id');

        $this->addForeignKey(
            'fk-event_checks-id_human',
            'event_checks',
            'id_human',
            'humans',
            'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-event_checks-id_event', 'event_checks');
        $this->dropForeignKey('fk-event_checks-id_human', 'event_checks');
        $this->dropTable('{{%event_checks}}');
    }
}
