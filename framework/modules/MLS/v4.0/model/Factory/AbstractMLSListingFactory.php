<?php
/**
 * Description of AbstractMLSListingFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_listing';
    protected static $models = array();
    protected static $modelsListingIdKey = array();
    protected static $modelsMLSNumberKey = array();
    protected static $forceUpdating = false;
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSListing
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'res':
            case 'RD_1':
            case 'RA_2':
            case 'MF_3':
            case 'LD_4':
                $model = new MLSListingRes($map);
                break;
            case 'com':
            case 'CM_1':
                $model = new MLSListingCom($map);
                break;
            default:
                $model = new MLSListing($map);
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
            case 'listing':
                $typeRefs = array('listing');
                break;
            case 'res':
                $typeRefs = array('res', 'res');
                break;
            case 'RD_1':
                $typeRefs = array('res', 'RD_1');
                break;
            case 'RA_2':
                $typeRefs = array('res', 'RA_2');
                break;
            case 'MF_3':
                $typeRefs = array('res', 'MF_3');
                break;
            case 'LD_4':
                $typeRefs = array('res', 'LD_4');
                break;
            case 'com':
                $typeRefs = array('com', 'com');
                break;
            case 'CM_1':
                $typeRefs = array('com', 'CM_1');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractFormElement
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    public static function getImportableTypeRefs($retsType = NULL){
        $typeRefs = array();
        
        if(is_null($retsType) || $retsType == 'res'){
            $resTypeDataSearch = new GI_DataSearch('mls_listing_res_type');
            $resTypeDataSearch->setDBType(static::getDBType());
            $resTypes = $resTypeDataSearch->filterNotEqualTo('ref', 'res')
                    ->orderBy('id')
                    ->select();
            foreach($resTypes as $resType){
                $typeRefs[] = $resType->getProperty('ref');
            }
        }
        
        if(is_null($retsType) || $retsType == 'com'){
            $comTypeDataSearch = new GI_DataSearch('mls_listing_com_type');
            $comTypeDataSearch->setDBType(static::getDBType());
            $comTypes = $comTypeDataSearch->filterNotEqualTo('ref', 'com')
                    ->orderBy('id')
                    ->select();
            foreach($comTypes as $comType){
                $typeRefs[] = $comType->getProperty('ref');
            }
        }
        
        return $typeRefs;
    }
    
    public static function getModelByListingId($listingId){
        if(isset(static::$modelsListingIdKey) && isset(static::$modelsListingIdKey[$listingId])){
            return static::$modelsListingIdKey[$listingId];
        }
        $mlsListingResult = static::search()
                ->filter('listing_id', $listingId)
                ->select();
        if($mlsListingResult){
            $mlsListing = $mlsListingResult[0];
            static::$modelsListingIdKey[$listingId] = $mlsListing;
            static::$modelsMLSNumberKey[$mlsListing->getMLSNumber()] = $mlsListing;
            return static::$modelsListingIdKey[$listingId];
        }
        return NULL;
        
    }
    
    public static function getModelByMLSNumber($mlsNumber){
        if(isset(static::$modelsMLSNumberKey) && isset(static::$modelsMLSNumberKey[$mlsNumber])){
            return static::$modelsMLSNumberKey[$mlsNumber];
        }
        $mlsListingResult = static::search()
                ->filter('mls_number', $mlsNumber)
                ->select();
        if($mlsListingResult){
            $mlsListing = $mlsListingResult[0];
            static::$modelsMLSNumberKey[$mlsNumber] = $mlsListing;
            static::$modelsListingIdKey[$mlsListing->getProperty('listing_id')] = $mlsListing;
            return static::$modelsMLSNumberKey[$mlsNumber];
        }
        return NULL;
        
    }
    
    public static function setForceUpdating($forceUpdating){
        static::$forceUpdating = $forceUpdating;
    }
    
    public static function getForceUpdating(){
        return static::$forceUpdating;
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return AbstractMLSListing
     */
    public static function buildFromRecord(PHRETS\Models\Search\Record $record, &$needsUpdating = false, &$needsImporting = false, &$imagesNeedUpdating = false){
        $class = $record->getClass();
        $listingId = $record[GI_RETSField::getFieldId('listing_id', $class)];
        
        $mlsListing = static::getModelByListingId($listingId);
        
        $updateListing = true;
        $imagesNeedUpdating = true;
        
        if(!$mlsListing){
            $needsImporting = true;
            $mlsListing = static::buildNewModel($class);
        } elseif(!static::getForceUpdating()) {
            $lastTrans = new DateTime($record[GI_RETSField::getFieldId('last_trans_date', $class)]);
            $lastUpdate = new DateTime($mlsListing->getProperty('last_trans_date'));
            if($lastTrans == $lastUpdate){
                $updateListing = false;
            }
            
            $lastImgTrans = new DateTime($record[GI_RETSField::getFieldId('last_img_trans_date', $class)]);
            $lastImgUpdate = new DateTime($mlsListing->getProperty('last_img_trans_date'));
            
            if($lastImgTrans == $lastImgUpdate){
                $imagesNeedUpdating = false;
            }
        }
        
        if($updateListing){
            $needsUpdating = true;
            $mlsListing->setPropertiesFromRecord($record);
        }
        
        return $mlsListing;
        
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return AbstractMLSListing
     */
    public static function buildFromRecordAndSave(PHRETS\Models\Search\Record $record, &$needsUpdating = false, &$needsImporting = false, &$imagesNeedUpdating = false){
        $mlsListing = static::buildFromRecord($record, $needsUpdating, $needsImporting, $imagesNeedUpdating);
        if(!$needsUpdating || $mlsListing->save()){
            if($needsUpdating){
                MLSRealtorFactory::linkListingToRealtorsFromRecord($mlsListing, $record);
                MLSFirmFactory::linkListingToFirmsFromRecord($mlsListing, $record);
                static::tagFromRecord($mlsListing, $record);
                if($imagesNeedUpdating){
                    MLSListingImageFactory::updateForMLSListing($mlsListing);
                }
            }
            return $mlsListing;
        }
        return NULL;
    }
    
    public static function tagFromRecord(AbstractMLSListing $mlsListing, PHRETS\Models\Search\Record $record){
        $listingTypeRef = $mlsListing->getTypeRef();
        $tagFields = $mlsListing->getTagFields();
        
        $deleteTags = TagFactory::getByModel($mlsListing, true, static::getDBType());
        
        $tags = array();
        
        foreach($tagFields as $retsField => $tagTypeRef){
            $tagTitles = explode(',', $record[GI_RETSField::getFieldId($retsField, $listingTypeRef)]);
            
            foreach($tagTitles as $tagTitle){
                $ref = GI_Sanitize::ref($tagTitle);
                TagFactory::setDBType(static::getDBType());
                $tag = TagFactory::getModelByRefAndTypeRef($ref, $tagTypeRef, static::getDBType());
                TagFactory::resetDBType();
                if($tag){
                    $tagId = $tag->getProperty('id');
                    $tags[$tagId] = $tag;
                    if(isset($deleteTags[$tagId])){
                        unset($deleteTags[$tagId]);
                    }
                }
            }
            
        }
        
        foreach($tags as $tagId => $tag){
            TagFactory::linkModelAndTag($mlsListing, $tag, static::getDBType());
        }
        
        foreach($deleteTags as $deleteTag){
            TagFactory::unlinkModelAndTag($mlsListing, $deleteTag, static::getDBType());
        }
        
        return true;
    }
    
    public static function getMLSNumbers($retsType = NULL, $activeOnly = true){
        $mlsListingsSearch = static::search();
        if(!empty($retsType)){
            $mlsListingsSearch->filterByTypeRef($retsType);
        }
        if($activeOnly){
            $mlsListingsSearch->filter('active', 1);
        }
        $mlsListings = $mlsListingsSearch->select();
        $mlsNumbers = array();
        foreach($mlsListings as $mlsListing){
            $mlsNumbers[] = $mlsListing->getMLSNumber();
        }
        return $mlsNumbers;
    }
    
    /**
     * @return GI_DataSearch
     */
    public static function searchActive() {
        $dataSearch = parent::search();
        $dataSearch->filter('active', 1);
        return $dataSearch;
    }
    
}
