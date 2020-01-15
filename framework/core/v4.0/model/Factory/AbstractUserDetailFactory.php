<?php
/**
 * Description of AbstractUserDetailFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractUserDetailFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'user_detail';
    protected static $models = array();
    protected static $modelsUserIdKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractUserDetail
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'detail':
            default:
                $model = new UserDetail($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'detail':
                $typeRefs = array('detail');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractUserDetail
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractUserDetail
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param AbstractUser $user
     * @param string $typeRef
     * @param boolean $general
     * @return AbstractUserDetail[]
     */
    public static function getByUser(AbstractUser $user, $typeRef = NULL, $general = true){
        $userId = $user->getId();
        $search = static::search()
                ->filter('user_id', $userId);
        
        if(!empty($typeRef)){
            $search->filterByTypeRef($typeRef, $general);
        }
        
        $result = $search->select();
        
        return $result;
    }
    
    /**
     * @param AbstractUser $user
     * @param string $typeRef
     * @param boolean $general
     * @return AbstractUserDetail
     */
    public static function getModelByUser(AbstractUser $user, $typeRef = NULL, $general = true){
        $userId = $user->getId();
        if(empty($typeRef) && isset(static::$modelsUserIdKey[$userId])){
            return static::$modelsUserIdKey[$userId];
        }
        
        $result = static::getByUser($user, $typeRef, $general);
        
        if($result){
            $userDetail = $result[0];
            if(empty($typeRef)){
                static::$modelsUserIdKey[$userId] = $userDetail;
                return static::$modelsUserIdKey[$userId];
            } else {
                return $userDetail;
            }
        }
        return NULL;
    }
    
    /**
     * @param AbstractUser $user
     * @param string $typeRef
     * @param boolean $general
     * @param boolean $forceNew
     * @return AbstractUserDetail
     */
    public static function buildModelByUser(AbstractUser $user, $typeRef = 'detail', $general = true, $forceNew = false){
        if(!$forceNew){
            $userDetail = static::getModelByUser($user, $typeRef, $general);
            if($userDetail){
                return $userDetail;
            }
        }
        $userDetail = static::buildNewModel($typeRef);
        $userDetail->setProperty('user_id', $user->getId());
        return $userDetail;
    }
    
}
