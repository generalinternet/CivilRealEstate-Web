<?php
/**
 * Description of AbstractAssignedToContactFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractAssignedToContactFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'assigned_to_contact';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractAssignedToContact
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'assigned_to':
            default:
                $model = new AssignedToContact($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'assigned_to':
                $typeRefs = array('assigned_to');
                break;
            case 'assigned_to_warehouse':
                $typeRefs = array('assigned_to_warehouse');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractAssignedToContact
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractAssignedToContact
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param interger $userId
     * @param string $typeRef
     * @return AbstractAssignedToContact[]
     */
    public static function getByUserId($userId = NULL, $typeRef = NULL){
        if(empty($userId)){
            $userId = Login::getUserId();
        }
        $search = static::search()
                ->filter('user_id', $userId);
        
        if(!empty($typeRef)){
            $search->filterByTypeRef($typeRef);
        }
        
        return $search->select();
    }
    
    public static function userAssignedToMultipleWarehouses($userId = NULL){
        if(dbConnection::isModuleInstalled('contact')){
            if(empty($userId)){
                $userId = Login::getUserId();
            }
            if(Permission::verifyByRef('all_warehouses', $userId)){
                return true;
            }
            $search = static::search()
                    ->filter('user_id', $userId)
                    ->filterByTypeRef('assigned_to_warehouse');
            $count = $search->count();
            if($count > 1){
                return true;
            }
        }
        return false;
    }
    
}
