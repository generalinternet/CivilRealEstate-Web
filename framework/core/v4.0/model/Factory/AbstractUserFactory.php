<?php
/**
 * Description of AbstractUserFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.1
 */
class AbstractUserFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'user';
    protected static $models = array();

    public static function validateModelFranchise(\GI_Model $model) {
        return true;
    }

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractUser
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'user':
            default:
                $model = new User($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'user':
                $typeRefs = array('user');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractUser
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id - the id of the model
     * @param boolean $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractUser
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @return AbstractUser
     */
    public static function getRootUser() {
        $rootUser = static::getByEmail(ROOT_USER_EMAIL);
        return $rootUser;
    }
    
    /**
     * @return AbstractUser
     */
    public static function getByEmail($email){
        $users = static::search()
                ->setAutoFranchise(false)
                ->filter('email', $email)
                ->select();
        if (!empty($users)) {
            return $users[0];
        }
        return NULL;
    }
    
    /**
     * @param integer $contactId
     * @return AbstractUser
     */
    public static function getByContactId($contactId){
        $userTable = static::getDbPrefix() . 'user';
        $userResult = static::search()
                ->join('contact', 'source_user_id', $userTable, 'id', 'C')
                ->filter('C.id', $contactId)
                ->select();
        
        if($userResult){
            return $userResult[0];
        }
        return NULL;
    }
    
    /**
     * @return GI_DataSearch
     */
    public static function searchRestricted(){
        $curUser = Login::getUser();
        $userMaxRank = $curUser->getMaxRefRoleRank();
        $userTable = dbConfig::getDbPrefix() . 'user';
        $giDataSearch = parent::search()
                ->join('user_link_to_role', 'user_id', $userTable, 'id', 'ultr')
                ->join('role', 'id', 'ultr', 'role_id', 'r')
                ->join('role_rank', 'id', 'r', 'role_rank', 'rr')
                ->filterLessOrEqualTo('rr.rank', $userMaxRank)
                ->groupBy('id');
        return $giDataSearch;
    }
    
    /**
     * @param string $email email to check
     * @param integer $ignoreUserId user id to ignore
     * @return boolean
     */
    public static function existingEmail($email, $ignoreUserId = NULL){
        if(!empty($email)){
            $existingUserSearch = UserFactory::search()
                    ->filter('email', $email);
            
            if(!empty($ignoreUserId)){
                $existingUserSearch->filterNotEqualTo('id', $ignoreUserId);
            }
            
            $existingUser = $existingUserSearch->select();
            if($existingUser){
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param AbstractPermission $permission
     * @return AbstractUser[]
     */
    public static function getUsersByPermission(AbstractPermission $permission) {
        $userTableName = static::getDbPrefix() . 'user';
        $permissionId = $permission->getProperty('id');
        $userSearch = static::search();
        $userSearch->join('permission_link_to_user', 'user_id', $userTableName, 'id', 'PLTU', 'left')
                ->join('user_link_to_role', 'user_id', $userTableName, 'id', 'ULTR', 'left')
                ->join('role', 'id', 'ULTR', 'role_id', 'ROLE')
                ->join('permission_link_to_role', 'role_id', 'ROLE', 'id', 'PLTR');
        $userSearch->filterGroup()
                ->filterGroup()
                ->filter('PLTU.permission_id', $permission->getProperty('id'))
                ->filter('PLTU.status', 1)
                ->closeGroup()
                ->orIf()
                ->filterGroup()
                ->andIf()
               ->filterNULL('PLTU.status')
                ->filter('PLTR.permission_id', $permissionId)
                ->filter('ULTR.status',1)
                ->filter('ROLE.status', 1)
                ->filter('PLTR.status', 1)
                ->closeGroup()
                ->closeGroup()
                ->andIf()
                ->groupBy('id');
        return $userSearch->select();
    }
    
}
