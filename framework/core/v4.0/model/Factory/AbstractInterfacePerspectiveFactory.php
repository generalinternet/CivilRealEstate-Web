<?php
/**
 * Description of AbstractInterfacePerspectiveFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractInterfacePerspectiveFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'interface_perspective';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return InterfacePerspective
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new InterfacePerspective($map);
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
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return InterfacePerspective
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return InterfacePerspective
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getModelByRef($ref) {
        $search = static::search();
        $search->filter('ref', $ref);
        $search->setPageNumber(1)
                ->setItemsPerPage(1);
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
   
}
