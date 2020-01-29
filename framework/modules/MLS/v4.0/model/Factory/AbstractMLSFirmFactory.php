<?php
/**
 * Description of AbstractMLSFirmFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSFirmFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_firm';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSFirm
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSFirm($map);
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
     * @return AbstractMLSFirm
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractMLSListing $mlsListing
     * @param AbstractMLSFirm[] $mlsFirms
     * @param boolean $deleteExisting
     * @return boolean
     */
    public static function linkListingToFirms(AbstractMLSListing $mlsListing, $mlsFirms = array(), $deleteExisting = true){
        $mlsListingId = $mlsListing->getProperty('id');
        
        $existingSearch = new GI_DataSearch('mls_listing_link_to_firm');
        $existingSearch->setDBType(static::getDBType());
        $existingResult = $existingSearch->filter('mls_listing_id', $mlsListingId)
                ->filterNotNull('status')
                ->select();
        
        $deleteLinks = array();
        foreach($existingResult as $existingLink){
            $deleteLinks[$existingLink->getProperty('mls_firm_id')] = $existingLink;
        }
        
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        
        if($mlsFirms){
            $pos = 0;
            foreach($mlsFirms as $mlsFirm){
                $mlsFirmId = $mlsFirm->getProperty('id');
                if(isset($deleteLinks[$mlsFirmId])){
                    $mlsFirmLink = $deleteLinks[$mlsFirmId];
                    unset($deleteLinks[$mlsFirmId]);
                } else {
                    $mlsFirmLink = new $defaultDAOClass('mls_listing_link_to_firm', array(
                        'dbType' => static::getDBType()
                    ));
                    $mlsFirmLink->setProperty('mls_listing_id', $mlsListingId);
                    $mlsFirmLink->setProperty('mls_firm_id', $mlsFirmId);
                }
                $mlsFirmLink->setProperty('status', 1);
                $mlsFirmLink->setProperty('pos', $pos);
                $mlsFirmLink->save();
                $pos++;
            }
        }
        
        if($deleteExisting){
            foreach($deleteLinks as $deleteLink){
                $deleteLink->softDelete();
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractMLSListing $mlsListing
     * @param PHRETS\Models\Search\Record $record
     * @return boolean
     */
    public static function linkListingToFirmsFromRecord(AbstractMLSListing $mlsListing, PHRETS\Models\Search\Record $record){
        $mlsFirms = array();
        $mlsTypeRef = $mlsListing->getTypeRef();
        
        for($i=1; $i<=3; $i++){
            $emptyFirm = true;
            $firmSearch = static::search();
            $name = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_name', $mlsTypeRef)];
            $shortName = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_short_name', $mlsTypeRef)];
            $phone = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_phone', $mlsTypeRef)];
            $email = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_email', $mlsTypeRef)];
            $url = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_url', $mlsTypeRef)];
            $code = $record[GI_RETSField::getFieldId('list_firm_' . $i . '_code', $mlsTypeRef)];
            if($name){
                $emptyFirm = false;
                $firmSearch->filter('name', $name);
            }
            if($shortName){
                $emptyFirm = false;
                $firmSearch->filter('short_name', $shortName);
            }
            if($phone){
                $emptyFirm = false;
                $firmSearch->filter('phone', $phone);
            }
            if($email){
                $emptyFirm = false;
                $firmSearch->filter('email', $email);
            }
            if($url){
                $emptyFirm = false;
                $firmSearch->filter('url', $url);
            }
            if($code){
                $emptyFirm = false;
                $firmSearch->filter('code', $code);
            }
            if(!$emptyFirm){
                $firmResult = $firmSearch->select();
                if($firmResult){
                    $mlsFirm = $firmResult[0];
                } else {
                    $mlsFirm = static::buildNewModel();
                    $mlsFirm->setProperty('name', $name);
                    $mlsFirm->setProperty('short_name', $shortName);
                    $mlsFirm->setProperty('phone', $phone);
                    $mlsFirm->setProperty('email', $email);
                    $mlsFirm->setProperty('url', $url);
                    $mlsFirm->setProperty('code', $code);
                    $mlsFirm->save();
                }
                $mlsFirms[] = $mlsFirm;
            }
        }
        return static::linkListingToFirms($mlsListing, $mlsFirms);
    }
    
}
