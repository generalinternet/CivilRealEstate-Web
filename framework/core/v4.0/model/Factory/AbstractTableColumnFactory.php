<?php
/**
 * Description of AbstractTableColumnFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractTableColumnFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'table_column';
    protected static $models = array();
    protected static $defaultDAOClass = 'TableColumnDAO';

    public static function validateModelFranchise(\GI_Model $model) {
        return true;
    }

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new TableColumn($map);
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
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    /**
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return TableColumn
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
}
