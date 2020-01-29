<?php
/**
 * Description of AbstractMLSOpenHouse
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSOpenHouse extends GI_Model {
    
    public function save() {
        if(RETS_MODIFY_ROWS){
            return parent::save();
        }
        return false;
    }
    
    public function softDelete(){
        if(RETS_MODIFY_ROWS){
            return parent::softDelete();
        }
        return false;
    }
    
    /**
     * [DB Column] => [RETS Field]
     * @var array 
     */
    protected static $importCols = array(
        'oh_unique_id' => 'oh_unique_id',
        'oh_open_house_id' => 'oh_open_house_id',
        'oh_start_date_time' => 'oh_start_date_time',
        'oh_end_date_time' => 'oh_end_date_time',
        'oh_start_date' => 'oh_start_date',
        'oh_end_date' => 'oh_end_date',
        'oh_comments' => 'oh_comments',
        'oh_create_date_time' => 'oh_create_date_time',
        'oh_update_date_time' => 'oh_update_date_time',
        'oh_start_time' => 'oh_start_time',
        'oh_end_time' => 'oh_end_time',
        'update_date' => 'update_date',
        'status_date' => 'status_date'
    );
    
    /**
     * @var AbstractMLSListing 
     */
    protected $mlsListing = NULL;
    
    /**
     * @return AbstractMLSListing
     */
    public function getMLSListing(){
        if(is_null($this->mlsListing)){
            $this->mlsListing = MLSListingFactory::getModelById($this->getProperty('mls_listing_id'));
        }
        
        return $this->mlsListing;
    }
    
    public static function getImportCols(){
        $importCols = static::$importCols;
        return $importCols;
    }
    
    /**
     * @param string $column
     * @param string $retsField
     * @param PHRETS\Models\Search\Record $record
     * @return \AbstractMLSOpenHouse
     */
    public function setPropertyFromRecord($column, $retsField, PHRETS\Models\Search\Record $record){
        $mlsListing = $this->getMLSListing();
        if($mlsListing){
            $this->setProperty($column, $record[GI_RETSField::getFieldId($retsField, $mlsListing->getTypeRef())]);
        }
        return $this;
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return \AbstractMLSOpenHouse
     */
    public function setPropertiesFromRecord(PHRETS\Models\Search\Record $record){
        $class = $record->getClass();
        $listingId = $record[GI_RETSField::getFieldId('listing_id', $class)];
        $mlsListing = MLSListingFactory::getModelByListingId($listingId);
        
        if(!$mlsListing){
            return NULL;
        }
        
        $this->setProperty('mls_listing_id', $mlsListing->getProperty('id'));
        
        $importCols = static::getImportCols();
        
        foreach($importCols as $column => $retsField){
            $this->setPropertyFromRecord($column, $retsField, $record);
        }
        
        return $this;
    }
    
}
