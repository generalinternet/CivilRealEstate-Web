<?php
/**
 * Description of AbstractContactQBFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactQBFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_qb';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'supplier':
                $model = new ContactQBSupplier($map);
                break;
            case 'customer':
                $model = new ContactQBCustomer($map);
                break;
            case 'qb':
            default:
                return NULL;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'qb':
                $typeRefs = array('qb');
                break;
            case 'customer':
                $typeRefs = array('customer');
                break;
            case 'supplier':
                $typeRefs = array('supplier');
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
     * @return AbstractContactQB
     */
    public static function getModelById($id, $force = false) {
         return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractContactQB
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $typeRef
     * @param string[] $qbDataArray
     * @return AbstractContactQB[]
     */
    public static function importNewModelsFromQB($typeRef, $qbDataArray) {
        $models = array();
        if (!empty($qbDataArray)) {
            foreach ($qbDataArray as $qbObject) {
                $qbId = $qbObject->Id;
                if (!empty($qbId)) {
                    $search = static::search()
                            ->filter('qb_id', $qbId);
                    $existingModel = $search->select();
                    if (!empty($existingModel)) {
                        continue;
                    }
                    $model = static::buildNewModel($typeRef);
                    if ($model->updateFromQB($qbObject)) {
                        $models[] = $model;
                    }
                }
            }
        }
        
        return $models;
    }
    
    /**
     * @param Integer $qbId
     */
    public static function getModelByQBId($qbId) {
        $search = static::search()
                ->filter('qb_id', $qbId);
        $array = $search->select();
        if (!empty($array)) {
            return $array[0];
        }
        return NULL;
    }

}
