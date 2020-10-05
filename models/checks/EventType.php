<?php

namespace app\models\checks;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "event_types".
 *
 * @property int $id
 * @property string $name
 */
class EventType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'event_types';
    }

    public static function getList()
    {
        $sql = "select id, name
              from event_types
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
            'name' => 'Тип мероприятия',
        ];
    }
}
