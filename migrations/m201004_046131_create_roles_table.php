<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%roles}}`.
 */
class m201004_046131_create_roles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%roles}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->unique()
        ]);

        $this->insert('roles', [
            'name' => 'Администратор'
        ]);
        $this->insert('roles', [
            'name' => 'Сотрудник'
        ]);
        $this->insert('roles', [
            'name' => 'Удаленщик'
        ]);
        $this->insert('roles', [
            'name' => 'Робот'
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%roles}}');
    }
}
