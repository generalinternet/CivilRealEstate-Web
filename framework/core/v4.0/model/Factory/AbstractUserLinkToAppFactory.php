<?php
/**
 * Description of AbstractUserLinkToAppFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserLinkToAppFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'user_link_to_app';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return UserLinkToApp
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new UserLinkToApp($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return UserLinkToApp
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return UserLinkToApp
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
}
