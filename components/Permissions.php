<?php
namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;

class Permissions
{

    private $_id_role;
    private $_permissions;

    public function __construct($id_role)
    {
        $this->_id_role = $id_role;
        $this->loadPermissionsFromDb();
    }

    /**
     * Check permission
     * @param $permission_name
     * @return bool
     */
    public function has($permission_name)
    {
        $permission=$this->_permissions[$permission_name];
        if (!isset($permission)) {
            return false;
        }
        return true;
    }

    private function loadPermissionsFromDb()
    {
        $this->_permissions = [];
        $rows = Yii::$app->db->createCommand("SELECT ID, NAME
                                                FROM PERMISSIONS
                                                WHERE EXISTS (SELECT 1 FROM ROLE_PERMISSIONS_BINDINGS
                                                WHERE ROLE_PERMISSIONS_BINDINGS.ID_ROLE=:id_role AND ROLE_PERMISSIONS_BINDINGS.ID_PERMISSION=PERMISSIONS.ID)
                                              ")
            ->bindValues([':id_role' => $this->_id_role,])->cache(CacheManager::DEFAULT_CACHE_DURATION)->queryAll();
        $this->_permissions = ArrayHelper::map($rows, 'name', 'id');
    }


}
