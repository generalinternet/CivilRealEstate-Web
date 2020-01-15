<?php
/**
 * Description of AbstractRoleFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.2
 */
abstract class AbstractRoleFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'role';
    protected static $models = array();
    protected static $modelsSystemTitleKey = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractRole
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Role($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractRole
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
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
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return Role
     */
    public static function getModelById($id, $force = false){ 
        return parent::getModelById($id, $force);
    }
    
    public static function getRoleBySystemTitle($systemTitle){
        if(!isset(static::$modelsSystemTitleKey[$systemTitle])){
            $roles = static::search()
                    ->filter('system_title', $systemTitle)
                    ->select();
            if($roles){
                static::$modelsSystemTitleKey[$systemTitle] = $roles[0];
            } else {
                static::$modelsSystemTitleKey[$systemTitle] = NULL;
            }
        }
        return static::$modelsSystemTitleKey[$systemTitle];
    }
    
    public static function linkRoleToUser(AbstractRole $role, AbstractUser $user) {
        $defualtDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $roleId = $role->getProperty('id');
        $userId = $user->getProperty('id');
        $existingLinkArray = $defualtDAOClass::getByProperties('user_link_to_role', array(
                    'user_id' => $userId,
                    'role_id' => $roleId
        ));
        if (!empty($existingLinkArray)) {
            return true;
        }
        $softDeletedLinkArray = $defualtDAOClass::getByProperties('user_link_to_role', array(
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'status' => 0,
        ));
        if (!empty($softDeletedLinkArray)) {
            $softDeletedLink = $softDeletedLinkArray[0];
            $softDeletedLink->setProperty('status', 1);
            if ($softDeletedLink->save()) {
                return true;
            }
        }
        $newLink = new $defualtDAOClass('user_link_to_role');
        $newLink->setProperty('user_id', $userId);
        $newLink->setProperty('role_id', $roleId);
        if ($newLink->save()) {
            return true;
        }
        return false;
    }

    public static function unlinkRoleFromUser(AbstractRole $role, AbstractUser $user) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $roleId = $role->getProperty('id');
        $userId = $user->getProperty('id');
        $existingLinkArray = $defaultDAOClass::getByProperties('user_link_to_role', array(
                    'user_id' => $userId,
                    'role_id' => $roleId
        ));
        if (empty($existingLinkArray)) {
            return true;
        }
        $existingLink = $existingLinkArray[0];
        return $existingLink->softDelete();
    }
    
    public static function unlinkRoleFromAllUsers(AbstractRole $role) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $roleId = $role->getProperty('id');
        $existingLinkArray = $defaultDAOClass::getByProperties('user_link_to_role', array(
                    'role_id' => $roleId
        ));
        if (!empty($existingLinkArray)) {
            foreach ($existingLinkArray as $existingLink) {
                if (!$existingLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public static function unlinkUserFromAllRoles(AbstractUser $user) {
        $userId = $user->getProperty('id');
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $existingLinkArray = $defaultDAOClass::getByProperties('user_link_to_role', array(
            'user_id'=>$userId
        ));
        if (!empty($existingLinkArray)) {
            foreach ($existingLinkArray as $existingLink) {
                if (!$existingLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function getRolesByUser(AbstractUser $user) {
        $userId = $user->getProperty('id');
        
        $roleTableName = dbConfig::getDbPrefix() . 'role';
        $roles = RoleFactory::search()
                ->join('user_link_to_role', 'role_id', $roleTableName, 'id', 'ultr')
                ->filter('ultr.user_id', $userId)
                ->select();
        return $roles;
    }
    
    public static function getRolesByRoleRank(AbstractRoleGroup $roleRank) {
        $rankId = $roleRank->getProperty('id');
        $roles = static::search()
                ->filter('role_rank', $rankId)
                ->select();
        return $roles;
    }
    
    public static function getByRoleGroupSystemTitle($systemTitle){
        $roleTable = dbConfig::getDbPrefix() . 'role';
        $roles = static::search()
                ->join('role_rank', 'id', $roleTable, 'role_rank', 'rr')
                ->filter('rr.system_title', $systemTitle)
                ->groupBy('id')
                ->select();
        
        return $roles;
    }
    
    public static function getRolesByPermission(AbstractPermission $permission, AbstractUser $user = NULL) {
        $permissionId = $permission->getProperty('id');
        $roleTableName = dbConfig::getDbPrefix() . 'role';
        $roleSearch = static::search()
                ->join('permission_link_to_role', 'role_id', $roleTableName, 'id', 'pltr')
                ->filter('pltr.permission_id', $permissionId)
                ->groupBy('id');
        if(!empty($user)){
            $userId = $user->getProperty('id');
            $roleSearch->join('user_link_to_role', 'role_id', $roleTableName, 'id', 'ultr')
                    ->filter('ultr.user_id', $userId);
        }
        
        $roles = $roleSearch->select();
        return $roles;
    }
    
    public static function getLimitedRoles(){
        $limitedRoleGroup = RoleGroupFactory::getRoleGroupBySystemTitle('limited_user');
        if($limitedRoleGroup){
            $limitedRoleGroupId = $limitedRoleGroup->getProperty('id');
            $limitedRoles = static::search()
                    ->filter('role_rank', $limitedRoleGroupId)
                    ->select();
            return $limitedRoles;
        }
        return NULL;
    }

    public static function buildRoleOptions($type = 'self'){
        $userId = Login::getUserId();
        if(empty($userId)){
            $roleOptions = array();
            $roles = static::search()
                    ->filter('registerable', 1)
                    ->select();
            if (!empty($roles)) {
                foreach($roles as $role){
                    $roleId = $role->getProperty('id');
                    $title = $role->getProperty('title');
                    $roleOptions[$roleId] = $title;
                }
            } else {
                $limitedRoles = static::getLimitedRoles();
                if($limitedRoles){
                    foreach($limitedRoles as $limitedRole){
                        $roleId = $limitedRole->getProperty('id');
                        $title = $limitedRole->getProperty('title');
                        $roleOptions[$roleId] = $title;
                    }
                }
            }
        } else {
            $userHighestRoleRank = RoleGroup::getUserHighestRoleGroupRank($type);
            $roleGroups = RoleGroupFactory::search()
                    ->filterLessOrEqualTo('rank', $userHighestRoleRank)
                    ->select();

            $roleOptions = array();
            foreach($roleGroups as $roleGroup) {
                $roleGroupId = $roleGroup->getProperty('id');
                $roles = static::search()
                        ->filter('role_rank', $roleGroupId)
                        ->select();
                if (!empty($roles)) {
                    foreach($roles as $role){
                        $roleId = $role->getProperty('id');
                        $title = $role->getProperty('title');
                        $roleOptions[$roleId] = $title;
                    }
                }
            }
        }
        return $roleOptions;
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
}
