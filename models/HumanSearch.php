<?php

namespace app\models;

use app\components\Helper;
use app\components\PermissionManager;
use app\components\QueryBuilder;
use kartik\grid\GridView;
use Yii;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class HumanSearch extends Human
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
        $sql = "SELECT HUMANS.*, ROLES.NAME as ROLE_NAME
              FROM HUMANS
              LEFT JOIN ROLES ON ROLES.ID=HUMANS.ID_ROLE
              WHERE 1=1
                                           ";
        $builder = QueryBuilder::build()->setSQL($sql)
            ->filterByModel($this, [
                ['id', 'HUMANS.ID'],
                ['id_role', 'HUMANS.ID_ROLE'],
                ['name', 'HUMANS.NAME', 'like'],
                ['telephone', 'HUMANS.TELEPHONE', 'like'],
                ['email', 'HUMANS.EMAIL', 'like'],
                ['work_time_start', 'HUMANS.WORK_TIME_START'],
                ['work_time_end', 'HUMANS.WORK_TIME_END'],
                ['is_active', 'HUMANS.IS_ACTIVE'],
                ['modified_by', 'HUMANS.MODIFIED_BY'],
            ]);

        $count = $builder->makeCountQuery();
        $sql = $builder->getSQL();
        $params = $builder->getParams();
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 50,
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
            'work_time_start',
            'work_time_end',
            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {restore} {delete}',
                'buttons' => [
                    'restore' => function ($url, $model) {
                        if ($model['is_active'] == 1) {
                            return '';
                        }
                        if (!PermissionManager::can("Human update")) {
                            return '';
                        }
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['human/restore', 'id' => $model['id']]);
                        return Html::a("<span class='glyphicon glyphicon-ok' style='color:green;'></span></a>", $custom_url,
                            ['title' => "Восстановить", 'data-pjax' => '0',]);
                    },
                    'update' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("Human update")) {
                            return '';
                        }
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['human/update', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-pencil" ></span>', $custom_url,
                            ['title' => "Редактировать", 'data-pjax' => '0', 'target' => '_blank']);
                    },
                    'delete' => function ($url, $model) {
                        if ($model['is_active'] == 0) {
                            return '';
                        }
                        if (!PermissionManager::can("Human update")) {
                            return '';
                        }
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['human/delete', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-remove" style="color:red;"></span>', $custom_url,
                            ['title' => "Удалить", 'data-method' => 'post',
                                'data-confirm' => 'Вы уверены, что хотите удалить?',]);
                    },
                    'view' => function ($url, $model) {
                        $custom_url = Yii::$app->getUrlManager()->createUrl(['human/view', 'id' => $model['id']]);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $custom_url,
                            ['title' => Yii::t('yii', 'View'), 'data-pjax' => '0', 'target' => '_blank']);
                    },

                ],
            ],
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
