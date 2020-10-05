<?php

namespace app\models\checks;

use app\components\Helper;
use app\components\PermissionManager;
use app\components\QueryBuilder;
use app\models\User;
use kartik\grid\GridView;
use Yii;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class EventSearch extends Event
{

    public function search($attributes)
    {
        $this->load($attributes);
        if (!$this->validate()) {
            foreach ($this->attributes() as $name) {
                if ($this->$name != '') {
                    $this->$name = '';
                }
            }
        }

        $sql = "select events.*, event_types.name as event_type_name
              from events
              left join event_types on event_types.id=events.id_event_type
              where events.is_active='1'
                                           ";
        $builder = QueryBuilder::build()->setSQL($sql)
            ->filterByModel($this, [
                ['id', 'EVENTS.ID'],
                ['id_event_type', 'EVENTS.ID_EVENT_TYPE'],
                ['name', 'EVENTS.NAME', 'like'],
                ['description', 'EVENTS.DESCRIPTION', 'like'],
                ['is_active', 'EVENTS.IS_ACTIVE'],
                ['modified_by', 'EVENTS.MODIFIED_BY'],
            ]);
        if (!PermissionManager::can('Event update')) {
            $builder->appendSql(" and exists (select 1 from event_human_bindings
             where event_human_bindings.id_human=:id_human and event_human_bindings.id_event=events.id and event_human_bindings.is_active='1')");
            $builder->appendParams([':id_human' => User::getCurrentUser()->id]);
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
        $columns = [
            'id',
            'name',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view} {stewards} {bindings} {checks} {update} {restore} {delete}',
                'buttons' => [
                    'restore' => function ($url, $model) {
                        if ($model['is_active'] == 1)
                            return "";
                        if (!PermissionManager::can("Event update"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['event/restore', 'id' => $model['id']]);
                        return Html::a("<span class='glyphicon glyphicon-ok' style='color:green;'></span></a>", $custom_url,
                            ['title' => "Восстановить", 'data-pjax' => '0',]);
                    },
                    'update' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("Event update"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['event/update', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-pencil" ></span>', $custom_url,
                            ['title' => "Редактировать", 'data-pjax' => '0', 'target' => '_blank']);
                    },
                    'delete' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("Event update"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['event/delete', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-remove" style="color:red;"></span>', $custom_url,
                            ['title' => "Удалить", 'data-method' => 'post',
                                'data-confirm' => 'Вы уверены, что хотите удалить?',]);
                    },
                    'view' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("Event update"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['event/view', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $custom_url,
                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0', 'target' => '_blank']);
                    },
                    'bindings' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("EventHuman"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['eventhuman/index', 'id_event' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-user"></span>', $custom_url,
                            ['title' => "Записанные", 'data-pjax' => '0', 'target' => '_blank']);
                    },
                    'checks' => function ($url, $model) {
                        if (!PermissionManager::can("EventCheck"))
                            return '';
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['eventcheck/index', 'id_event' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-list"></span>', $custom_url,
                            ['title' => "Отметки", 'data-pjax' => '0', 'target' => '_blank']);
                    },
                ],
            ],
            'description',
            [
                'attribute' => 'id_event_type',
                'value' => 'event_type_name',
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ArrayHelper::map(EventType::getList(), "id", "name"),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Не важно'],
            ],
            'start_time',
            'created_at',
            'modified_at',
            ['class' => 'kartik\grid\BooleanColumn',
                'attribute' => 'is_active',
                'vAlign' => 'middle',
                'trueLabel' => 'Да',
                'falseLabel' => 'Нет',
                'trueIcon' => GridView::ICON_ACTIVE,
                'falseIcon' => GridView::ICON_INACTIVE,
            ],
            'modified_by',
        ];
        return Helper::makeColumnsAdaptive($columns, $this->attributeLabels());
    }
}
