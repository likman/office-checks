<?php

namespace app\models\checks;

use app\components\Helper;
use app\components\PermissionManager;
use app\components\QueryBuilder;
use app\models\Role;
use kartik\grid\GridView;
use Yii;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class EventHumanSearch extends EventHuman
{
    public $human_name;
    public $telephone;
    public $email;
    public $id_role;

    public function rules()
    {
        $array = parent::rules();
        $array[] = [['id_role', 'telephone'], 'integer'];
        $array[] = [['id', 'id_event', 'id_human', 'modified_by'], 'integer'];
        $array[] = [['id_event',], 'required'];
        $array[] = [['human_name',], 'string'];
        $array[] = ['email', 'email'];
        return $array;
    }

    public function search($id_event, $attributes)
    {
        $this->id_event = $id_event;
        if (!$this->validate(['id_event'])) {
            return null;
        }
        $this->load($attributes);
        if (!$this->validate()) {
            foreach ($this->attributes() as $name) {
                if ($this->$name != '') {
                    $this->$name = '';
                }
            }
        }

        $sql = "select event_human_bindings.id, event_human_bindings.id_human, humans.id_role, humans.name as human_name, 
              humans.telephone, humans.email, event_human_bindings.modified_by, event_human_bindings.created_at,
              event_human_bindings.modified_at, roles.name as role_name
              FROM event_human_bindings
              left join humans on humans.id=event_human_bindings.id_human
              left join roles on roles.id=humans.id_role
              where event_human_bindings.id_event=:id_event and event_human_bindings.is_active='1' and humans.is_active='1'
                                           ";
        $builder = QueryBuilder::build()->setSQL($sql)
            ->filterByModel($this, [
                ['id', 'EVENT_HUMAN_BINDINGS.ID'],
                ['id_role', 'HUMANS.ID_ROLE'],
                ['human_name', 'HUMANS.NAME', 'like'],
                ['telephone', 'HUMANS.TELEPHONE', 'like'],
                ['email', 'HUMANS.EMAIL', 'like'],
                ['modified_by', 'EVENT_HUMAN_BINDINGS.MODIFIED_BY'],
            ])
            ->appendParams([':id_event' => $this->id_event]);
        $count = $builder->makeCountQuery();
        $sql = $builder->getSQL();
        $params = $builder->getParams();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 100,
            ],
            'sort' => [
                'attributes' => $this->attributes(),
            ],
        ]);
        return $dataProvider;
    }

    public function getColumns()
    {
        $columns = [
            'id',
            'id_human',
            'human_name',
            [
                'attribute' => 'id_role',
                'value' => 'role_name',
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ArrayHelper::map(Role::getList(), 'id', 'name'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Не важно'],
            ],
            'telephone',
            'email',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        if (!PermissionManager::can("EventHuman update"))
                            return '';
                        $customurl = Yii::$app->getUrlManager()->createUrl(['eventhuman/delete', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-remove" style="color:red;" ></span>', $customurl,
                            ['title' => 'Удалить', 'data-pjax' => '0', 'target' => '_blank']);
                    },

                ],
            ],
            'created_at',
            'modified_at',
            'modified_by',
        ];
        return Helper::makeColumnsAdaptive($columns, $this->attributeLabels());
    }

    public function attributeLabels()
    {
        $array = parent::attributeLabels();
        $array['id_human'] = 'Код сотрудника';
        $array['human_name'] = 'Сотрудник';
        $array['email'] = 'Почта';
        $array['telephone'] = 'Телефон';
        $array['id_role'] = 'Роль';
        return $array;
    }
}
