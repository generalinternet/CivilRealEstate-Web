<?php
/**
 * Description of AbstractUserActionRequiredFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserActionRequiredFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'user_act_req';
    protected static $models = array();
    protected static $modelsSystemTitleKey = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractUserActionRequired
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'redirect':
                $model = new UserActionRequiredRedirect($map);
                break;
            case 'notify':
            case 'act_req':
            default:
                $model = new UserActionRequired($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractUserActionRequired
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'redirect':
                $typeRefs = array('redirect');
                break;
            case 'notify':
                $typeRefs = array('notify');
                break;
            case 'act_req':
                $typeRefs = array('act_req');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param integer $id
     * @param boolean $force 
     * @return AbstractUserActionRequired
     */
    public static function getModelById($id, $force = false){ 
        return parent::getModelById($id, $force);
    }
    
}