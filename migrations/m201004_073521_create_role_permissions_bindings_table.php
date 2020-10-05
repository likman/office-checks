<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%role_permissions_bindings}}`.
 */
class m201004_073521_create_role_permissions_bindings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%role_permissions_bindings}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'id_role' => $this->integer()->unsigned()->notNull(),
            'id_permission' => $this->integer()->unsigned()->notNull()
        ]);
        $this->addForeignKey(
            'fk-role_pb-id_role',
            'role_permissions_bindings',
            'id_role',
            'roles',
            'id');

        $this->addForeignKey(
            'fk-role_pb-id_permission',
            'role_permissions_bindings',
            'id_permission',
            'permissions',
            'id');

        $this->batchInsert('role_permissions_bindings', ['id_role', 'id_permission'],
            [
                [1, 1],
                [1, 2],
                [1, 3],
                [1, 4],
                [1, 5],
                [1, 6],
                [1, 7],
                [1, 8],
                [1, 9],
                [1, 10],
                [1, 11],
                [1, 12],
                [1, 13],
                [2, 5],
                [2, 9],
                [2, 13],
                [3, 5],
                [3, 9],
                [3, 10],
                [3, 13],
                [4, 5],
                [4, 9],
                [4, 10],
                [4, 11]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-role_pb-id_role', 'role_permissions_bindings');
        $this->dropForeignKey('fk-role_pb-id_permission', 'role_permissions_bindings');
        $this->dropTable('{{%role_permissions_binding}}');
    }
}
