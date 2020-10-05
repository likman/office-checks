<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'roles';
    }

    public static function getList()
    {
        $sql = "select id, name
              from roles
              order by name asc";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            ['id', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Код',
            'name' => 'Роль',
        ];
    }

}
