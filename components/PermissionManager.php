<?php
namespace app\components;

use app\models\User;

class PermissionManager
{
    /**
     * @var Permissions
     */
    private static $_user_permissions;

    private static function loadPermissions($id_role)
    {
        self::$_user_permissions = new Permissions($id_role);
    }

    private static function loadPermissionsIfRequired()
    {
        if (!isset(self::$_user_permissions)) {
            $user = User::getCurrentUser();
            if (!isset($user)) {
                return false;
            }
            self::loadPermissions($user->id_role);
        }
        return true;
    }


    /**
     * Check user permission by name
     * @param $permissionName
     * @return bool
     */
    public static function can($permission_name)
    {
        if (!self::loadPermissionsIfRequired()) {
            return false;
        }
        if (!self::$_user_permissions->has($permission_name)) {
            return false;
        }
        return true;
    }
}