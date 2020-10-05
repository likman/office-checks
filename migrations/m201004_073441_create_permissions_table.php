<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%permissions}}`.
 */
class m201004_073441_create_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%permissions}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull()
        ]);

        $this->batchInsert(
            'permissions',
            ['name'],
            [
                ['Human'],
                ['Human update'],
                ['EventType'],
                ['EventType update'],
                ['Event'],
                ['Event update'],
                ['EventHuman'],
                ['EventHuman update'],
                ['EventCheck'],
                ['EventCheck self check'],
                ['EventCheck others check'],
                ['Import'],
                ['RdpAuth']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%permissions}}');
    }
}
