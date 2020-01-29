<?php
/**
 * Description of AbstractMLSOpenHouseFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSOpenHouseFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_open_house';
    protected static $models = array();
    protected static $modelsOpenHouseIdKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSOpenHouse
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSOpenHouse($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param type $typeRef
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
     * @return AbstractMLSOpenHouse
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    public static function getModelByOpenHouseId($ohId){
        if(isset(static::$modelsOpenHouseIdKey) && isset(static::$modelsOpenHouseIdKey[$ohId])){
            return static::$modelsOpenHouseIdKey[$ohId];
        }
        $mlsOHResult = MLSOpenHouseFactory::search()
                ->filter('oh_unique_id', $ohId)
                ->select();
        if($mlsOHResult){
            static::$modelsOpenHouseIdKey[$ohId] = $mlsOHResult[0];
            return static::$modelsOpenHouseIdKey[$ohId];
        }
        return NULL;
        
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return AbstractMLSOpenHouse
     */
    public static function buildFromRecord(PHRETS\Models\Search\Record $record, &$needsUpdating = false, &$needsImporting = false){
        $class = $record->getClass();
        $ohId = $record[GI_RETSField::getFieldId('oh_unique_id', $class)];
        
        $mlsOpenHouse = static::getModelByOpenHouseId($ohId);
        
        $updateOpenHouse = true;
        
        if(!$mlsOpenHouse){
            $needsImporting = true;
            $mlsOpenHouse = static::buildNewModel();
        } elseif(!MLSListingFactory::getForceUpdating()) {
            $lastTrans = new DateTime($record[GI_RETSField::getFieldId('oh_update_date_time', $class)]);
            $lastUpdate = new DateTime($mlsOpenHouse->getProperty('oh_update_date_time'));
            if($lastTrans == $lastUpdate){
                $updateOpenHouse = false;
            }
        }
        
        if($updateOpenHouse){
            $needsUpdating = true;
            if(!$mlsOpenHouse->setPropertiesFromRecord($record)){
                //can't find the MLS Listing
                return NULL;
            }
        }
        
        return $mlsOpenHouse;
        
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return AbstractMLSOpenHouse
     */
    public static function buildFromRecordAndSave(PHRETS\Models\Search\Record $record, &$needsUpdating = false, &$needsImporting = false){
        $mlsOpenHouse = static::buildFromRecord($record, $needsUpdating, $needsImporting);
        if($mlsOpenHouse && (!$needsUpdating || $mlsOpenHouse->save())){
            return $mlsOpenHouse;
        }
        return NULL;
    }
    
}
