<?php
/**
 * Description of AbstractPermissionFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
class AbstractPermissionFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'permission';
    protected static $models = array();
    protected static $modelsRefKey = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractPermission
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Permission($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * 
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractPermission
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractPermission
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param AbstractRole $role
     * @return AbstractPermission[]
     */
    public static function getPermissionsByRole(AbstractRole $role) {
        $roleId = $role->getProperty('id');
        $permissionTableName = static::getDbPrefix() . 'permission';
        $permissions = static::search()
                ->join('permission_link_to_role', 'permission_id', $permissionTableName, 'id', 'pltr')
                ->leftJoin('permission_category', 'id', $permissionTableName, 'permission_category_id', 'PCAT')
                ->ignoreStatus('PCAT')
                ->filter('pltr.role_id', $roleId)
                ->orderBy('PCAT.pos')
                ->orderBy('PCAT.title')
                ->orderBy('title')
                ->select();
        return $permissions;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractRole $role
     * @return boolean
     */
    public static function linkPermissionToRole(AbstractPermission $permission, AbstractRole $role) {
        $permissionId = $permission->getProperty('id');
        $roleId = $role->getProperty('id');
        
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_role');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filterNotNull('status')
                ->filter('role_id', $roleId)
                ->select();
        if($permissionLinkResult){
            $permissionLink = $permissionLinkResult[0];
            if($permissionLink->getProperty('status')){
                return true;
            } else {
                $permissionLink->setProperty('status', 1);
            }
        } else {
            $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $permissionLink = new $defualtDAOClass('permission_link_to_role');
        }
        $permissionLink->setProperty('permission_id', $permissionId);
        $permissionLink->setProperty('role_id', $roleId);
        if (!$permissionLink->save()) {
            return false;
        }
        return true;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractRole $role
     * @return boolean
     */
    public static function unlinkPermissionFromRole(AbstractPermission $permission, AbstractRole $role) {
        $permissionId = $permission->getProperty('id');
        $roleId = $role->getProperty('id');
        
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_role');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filter('role_id', $roleId)
                ->select();
        if($permissionLinkResult){
            $permissionLink = $permissionLinkResult[0];
            if(!$permissionLink->softDelete()){
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractUser $user
     * @return boolean
     */
    public static function linkPermissionToUser(AbstractPermission $permission, AbstractUser $user){
        $permissionId = $permission->getProperty('id');
        $userId = $user->getProperty('id');
        
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_user');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filterNotNull('status')
                ->filter('user_id', $userId)
                ->select();
        if($permissionLinkResult){
            $permissionLink = $permissionLinkResult[0];
            if($permissionLink->getProperty('status')){
                return true;
            } else {
                $permissionLink->setProperty('status', 1);
            }
        } else {
            $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $permissionLink = new $defualtDAOClass('permission_link_to_user');
        }
        $permissionLink->setProperty('permission_id', $permissionId);
        $permissionLink->setProperty('user_id', $userId);
        if (!$permissionLink->save()) {
            return false;
        }
        return true;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractUser $user
     * @return boolean
     */
    public static function unlinkPermissionFromUser(AbstractPermission $permission, AbstractUser $user) {
        $permissionId = $permission->getProperty('id');
        $userId = $user->getProperty('id');
        
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_user');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filter('user_id', $userId)
                ->select();
        if($permissionLinkResult){
            $permissionLink = $permissionLinkResult[0];
            if(!$permissionLink->softDelete()){
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractRole $role
     * @return boolean
     */
    public static function isPermissionLinkedToRole(AbstractPermission $permission, AbstractRole $role) {
        $permissionRef = $permission->getProperty('ref');
        $roleHasPermission = $role->hasPermission($permissionRef);
        if (!is_null($roleHasPermission)) {
            return $roleHasPermission;
        }
        $permissionId = $permission->getProperty('id');
        $roleId = $role->getProperty('id');
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_role');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filter('role_id', $roleId)
                ->select();
        if($permissionLinkResult){
            $role->setRoleHasPermission($permissionRef, true);
            return true;
        }
        $role->setRoleHasPermission($permissionRef, false);
        return false;
    }
    
    /**
     * @param AbstractPermission $permission
     * @param AbstractUser $user
     * @return boolean
     */
    public static function isPermissionLinkedToUser(AbstractPermission $permission, AbstractUser $user) {
        $permissionRef = $permission->getProperty('ref');
        $userHasPermission = $user->hasPermission($permissionRef);
        if (!is_null($userHasPermission)) {
            return $userHasPermission;
        }
        $permissionId = $permission->getProperty('id');
        $userId = $user->getProperty('id');
        $permissionLinkSearch = new GI_DataSearch('permission_link_to_user');
        $permissionLinkResult = $permissionLinkSearch->filter('permission_id', $permissionId)
                ->filter('user_id', $userId)
                ->select();
        
        if($permissionLinkResult){
            $user->setUserHasPermission($permissionRef, true);
            return true;
        }
        $user->setUserHasPermission($permissionRef, false);
        return false;
    }
    
    /**
     * @param AbstractUser $user
     * @return AbstractPermission[]
     */
    public static function getPermissionsLinkedToUser(AbstractUser $user, $restricted = true){
        $userId = $user->getProperty('id');
        $permissionTableName = static::getDbPrefix() . 'permission';
        if($restricted){
            $permissionSearch = static::searchRestricted();
        } else {
            $permissionSearch = static::search();
        }
        $permissionSearch->join('permission_link_to_user', 'permission_id', $permissionTableName, 'id', 'pltu')
                ->filter('pltu.user_id', $userId)
                ->filter('pltu.status', 1);
        
        $permissions = $permissionSearch->select();
        return $permissions;
    }
    
    public static function getPermissionsByUser(AbstractUser $user) {
        $userId = $user->getProperty('id');
        $permissionTableName = dbConfig::getDbPrefix() . 'permission';
        $permissions = static::search()
                ->setAutoStatus(false)
                ->leftJoin('permission_link_to_role', 'permission_id', $permissionTableName, 'id', 'pltr')
                ->leftJoin('role', 'id', 'pltr','role_id', 'pltrr')
                ->leftJoin('user_link_to_role', 'role_id', 'pltrr', 'id', 'ultr')
                ->leftJoin('permission_link_to_user', 'permission_id', $permissionTableName, 'id', 'pltu')
                ->leftJoin('permission_category', 'id', $permissionTableName, 'permission_category_id', 'PCAT')
                ->ignoreStatus('PCAT')
                ->filterGroup()
                    ->filterGroup()
                        ->filter('ultr.user_id', $userId)
                        ->filter('pltr.status', 1)
                        ->filter('pltrr.status', 1)
                        ->filter('ultr.status', 1)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->filter('pltu.user_id', $userId)
                        ->andIf()
                        ->filter('pltu.status', 1)
                    ->closeGroup()
                ->closeGroup()
                ->andIf()
                ->filter('status', 1)
                ->groupBy('permission.id')
                ->orderBy('PCAT.pos')
                ->orderBy('PCAT.title')
                ->orderBy('title')
                ->select();
        return $permissions;
    }

    public static function adjustUserPermissions(AbstractUser $user, $desiredPermissions) {
        if (!$user->clearCachedPermissions()) {
            return false;
        }
        $existingPermissions = static::getPermissionsLinkedToUser($user, false);
        if (empty($existingPermissions)) {
            $existingPermissions = array();
        }
        $permissionsToUnlink = array();
        foreach ($existingPermissions as $existingPermission) {
            $ref = $existingPermission->getProperty('ref');
            $permissionsToUnlink[$ref] = $existingPermission;
        }
        foreach ($desiredPermissions as $desiredPermission) {
            $desiredRef = $desiredPermission->getProperty('ref');
            if (isset($permissionsToUnlink[$desiredRef])) {
                unset($permissionsToUnlink[$desiredRef]);
            } else {
                $linkResult = static::linkPermissionToUser($desiredPermission, $user);
                if (!$linkResult) {
                    return false;
                }
            }
        }
        foreach ($permissionsToUnlink as $permissionToUnlink) {
            if(Permission::verifyByRef($permissionToUnlink->getProperty('ref'))){
                $unlinkResult = static::unlinkPermissionFromUser($permissionToUnlink, $user);
                if (!$unlinkResult) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function adjustRolePermissions(AbstractRole $role, $desiredPermissions) {
        if (!$role->clearCachedPermissions()) {
            return false;
        }
        $existingPermissions = $role->getPermissions();
        if (empty($existingPermissions)) {
            $existingPermissions = array();
        }
        $permissionsToUnlink = array();
        foreach ($existingPermissions as $existingPermission) {
            $ref = $existingPermission->getProperty('ref');
            $permissionsToUnlink[$ref] = $existingPermission;
        }
        foreach ($desiredPermissions as $desiredPermission) {
            $desiredRef = $desiredPermission->getProperty('ref');
            if (isset($permissionsToUnlink[$desiredRef])) {
                unset($permissionsToUnlink[$desiredRef]);
            } else {
                $linkResult = static::linkPermissionToRole($desiredPermission, $role);
                if (!$linkResult) {
                    return false;
                }
            }
        }
        foreach ($permissionsToUnlink as $permissionToUnlink) {
            if(Permission::verifyByRef($permissionToUnlink->getProperty('ref'))){
                $unlinkResult = static::unlinkPermissionFromRole($permissionToUnlink, $role);
                if (!$unlinkResult) {
                    return false;
                }
            }
        }
        return true;
    }
    
        /**
     * Returns an array of permissions for a role that the calling user has as well
     */
    public static function getFilteredRolePermissions(AbstractRole $role) {
        $userId = Login::getUserId();
        $rolePermissions = static::getPermissionsByRole($role);
        $filteredPermissions = array();
        foreach ($rolePermissions as $rolePermission) {
            $ref = $rolePermission->getProperty('ref');
            if (Permission::verifyByRef($ref, $userId)) {
                array_push($filteredPermissions, $rolePermission);
            }
        }
        return $filteredPermissions;
    }
    
    /**
     * @return GI_DataSearch
     */
    public static function searchRestricted(){
        $curUser = Login::getUser(true);
        $userId = $curUser->getProperty('id');
        $permissionTableName = dbConfig::getDbPrefix() . 'permission';
        $giDataSearch = static::search()
                ->setAutoStatus(false)
                ->leftJoin('permission_link_to_role', 'permission_id', $permissionTableName, 'id', 'pltr')
                ->leftJoin('role', 'id', 'pltr','role_id', 'pltrr')
                ->leftJoin('user_link_to_role', 'role_id', 'pltrr', 'id', 'ultr')
                ->leftJoin('permission_link_to_user', 'permission_id', $permissionTableName, 'id', 'pltu')
                ->filterGroup()
                    ->filterGroup()
                        ->filter('ultr.user_id', $userId)
                        ->filter('pltr.status', 1)
                        ->filter('pltrr.status', 1)
                        ->filter('ultr.status', 1)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->filter('pltu.user_id', $userId)
                        ->andIf()
                        ->filter('pltu.status', 1)
                    ->closeGroup()
                ->closeGroup()
                ->andIf()
                ->filter('status', 1)
                ->groupBy('id');
        return $giDataSearch;
    }

    public static function linkPermissionToRoleByRoleGroupRef($permissionRef, $roleGroupSystemTitle){
        $permissions = static::search()
                ->filter('ref', $permissionRef)
                ->select();
        
        if(!$permissions){
            return false;
        }
        
        foreach($permissions as $permission){
            $roles = RoleFactory::getByRoleGroupSystemTitle($roleGroupSystemTitle);
            
            foreach($roles as $role){
                static::linkPermissionToRole($permission, $role);
            }
        }
        return true;
    }
    
    /**
     * @param string $ref
     * @return AbstractPermission
     */
    public static function getModelByRef($ref) {
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = static::search()
                ->filter('ref', $ref)
                ->select();
        
        if($result){
            static::$modelsRefKey[$ref] = $result[0];
            return static::$modelsRefKey[$ref];
        }
        return NULL;
    }
    
}
