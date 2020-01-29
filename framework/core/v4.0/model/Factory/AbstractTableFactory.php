<?php
/**
 * Description of AbstractTableFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.1
 */
class AbstractTableFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'table';
    protected static $models = array();
    protected static $defaultDAOClass = 'TableDAO';
    protected static $tablesByTableTitle = array();
    protected static $tablesByTableName = array();

    public static function validateModelFranchise(\GI_Model $model) {
        //this is world ending if removed
        return true;
    }

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Table($map);
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
     * @return Table
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $tableTitle
     * @param boolean $force
     * @return Table
     */
    public static function getModelByTableTitle($tableTitle, $force = false) {
        if (!$force && isset(static::$tablesByTableTitle[$tableTitle])) {
            return static::$tablesByTableTitle[$tableTitle];
        }
        $search = static::search();
        $search->filter('title', $tableTitle);
        $results = $search->select();
        if (!empty($results)) {
            $model = $results[0];
            static::$tablesByTableTitle[$tableTitle] = $model;
            return $model;
        }
        return NULL;
    }
    
    /**
     * @param string $tableName
     * @param boolean $force
     * @return Table
     */
    public static function getModelByTableName($tableName, $force = false){
        if (!$force && isset(static::$tablesByTableName[$tableName])) {
            return static::$tablesByTableName[$tableName];
        }
        $search = static::search();
        $search->filter('system_title', $tableName);
        $results = $search->select();
        if(!empty($results)){
            $table = $results[0];
            static::$tablesByTableTitle[$tableName] = $table;
            return $table;
        }
        return NULL;            
    }
    
}
