<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%humans}}`.
 */
class m201004_053933_create_humans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%humans}}', [
            'id' => $this->primaryKey()->unsigned(),
            'id_role' => $this->integer()->unsigned(),
            'name' => $this->string()->notNull(),
            'telephone' => $this->string(15)->notNull(),
            'email' => $this->string(100)->notNull(),
            'work_time_start' => $this->string(5)->null(),
            'work_time_end' => $this->string(5)->null(),
            'password_hash' => $this->string()->notNull(),
            'unique_code' => $this->string(255),
            'auth_token' => $this->string()->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->null(),
            'modified_at' => $this->timestamp()->null(),
            'modified_by' => $this->integer()->unsigned()
        ]);
        $this->createIndex(
            'idx-humans-telephone-pwd',
            'humans',
            ['telephone', 'password_hash']
        );

        $this->addForeignKey(
            'fk-humans-id_role',
            'humans',
            'id_role',
            'roles',
            'id');

        $this->insert('humans', [
            'id_role' => 1,
            'name' => 'Admin',
            'telephone' => '100500',
            'email' => Yii::$app->params['adminEmail'],
            'work_time_start' => '09:00',
            'work_time_end' => '18:00',
            'password_hash' => '$2y$13$FbQov7QN1XKYBJzOpOk/SODmJbWfJgJktOg/EeaOGLEC.h0yTKD9m',
            'unique_code' => Yii::$app->security->generateRandomString(),
            'auth_token' => Yii::$app->security->generateRandomString(),
            'is_active' => 1
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-humans-telephone-pwd', 'humans');
        $this->dropForeignKey('fk-humans-id_role', 'humans');
        $this->dropTable('{{%humans}}');
    }
}
