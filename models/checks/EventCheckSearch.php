<?php

namespace app\models\checks;

use app\components\Helper;
use app\components\PermissionManager;
use app\components\QueryBuilder;
use app\models\Role;
use app\models\User;
use kartik\grid\GridView;
use Yii;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class EventCheckSearch extends EventCheck
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

        $sql = "select event_human_bindings.id_human, humans.id_role, humans.name as human_name, 
              humans.telephone, humans.email, event_checks.modified_by, roles.name as role_name,
              event_checks.check_time_in
              FROM event_human_bindings
              left join humans on humans.id=event_human_bindings.id_human
              left join roles on roles.id=humans.id_role
              left join event_checks on event_checks.id_human=event_human_bindings.id_human and event_checks.id_event=event_human_bindings.id_event
              where event_human_bindings.id_event=:id_event and event_human_bindings.is_active='1' and humans.is_active='1'
                                           ";
        $builder = QueryBuilder::build()->setSQL($sql)
            ->filterByModel($this, [
                ['id_role', 'HUMANS.ID_ROLE'],
                ['human_name', 'HUMANS.NAME', 'like'],
                ['telephone', 'HUMANS.TELEPHONE', 'like'],
                ['email', 'HUMANS.EMAIL', 'like'],
                ['modified_by', 'EVENT_CHECKS.MODIFIED_BY'],
            ])
            ->appendParams([':id_event' => $this->id_event]);
        if (!PermissionManager::can("EventCheck others check")) {
            $builder->appendSql(" and humans.id=:my_id ");
            $builder->appendParams([':my_id' => User::getCurrentUser()->id]);
        }

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
        $event = Event::findOne(['id' => $this->id_event]);
        $event_date = Helper::convertDate($event->start_time, 'Y-m-d H:i:s', 'Y-m-d');
        $is_event_date = false;
        if ($event_date == date('Y-m-d')) {
            $is_event_date = true;
        }
        $columns = [
            'id_human',
            'human_name',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{check_in}',
                'buttons' => [
                    'check_in' => function ($url, $model) use ($is_event_date) {
                        if (!PermissionManager::can("EventCheck self check")) {
                            return '';
                        }
                        if (Helper::isOk($model['check_time_in'])) {
                            return '';
                        }
                        if (!$is_event_date) {
                            return '';
                        }
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['eventcheck/checkin', 'id_event' => $this->id_event, 'id_human' => $model['id_human']]);
                        return Html::a('<button class="glyphicon glyphicon-arraw-right grid-mini-button" style="color:green;">Отметить прибытие</button>', $custom_url,
                            ['title' => "Отметить прибытие", 'data-pjax' => '0',]);
                    },
                ],
            ],
            'check_time_in',
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
            'modified_by',
        ];
        return Helper::makeColumnsAdaptive($columns, $this->attributeLabels());
    }

    public function attributeLabels()
    {
        $array = parent::attributeLabels();
        $array['human_name'] = 'Сотрудник';
        $array['email'] = 'Почта';
        $array['telephone'] = 'Телефон';
        $array['id_role'] = 'Роль';
        return $array;
    }
}
