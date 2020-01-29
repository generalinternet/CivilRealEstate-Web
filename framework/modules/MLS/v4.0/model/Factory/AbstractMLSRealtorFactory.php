<?php
/**
 * Description of AbstractMLSRealtorFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSRealtorFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_realtor';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSRealtor
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSRealtor($map);
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
     * @return AbstractMLSRealtor
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractMLSListing $mlsListing
     * @param AbstractMLSRealtor[] $mlsRealtors
     * @param boolean $deleteExisting
     * @return boolean
     */
    public static function linkListingToRealtors(AbstractMLSListing $mlsListing, $mlsRealtors = array(), $deleteExisting = true){
        $mlsListingId = $mlsListing->getProperty('id');
        
        $existingSearch = new GI_DataSearch('mls_listing_link_to_realtor');
        $existingSearch->setDBType(static::getDBType());
        $existingResult = $existingSearch->filter('mls_listing_id', $mlsListingId)
                ->filterNotNull('status')
                ->select();
        
        $deleteLinks = array();
        foreach($existingResult as $existingLink){
            $deleteLinks[$existingLink->getProperty('mls_realtor_id')] = $existingLink;
        }
        
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        
        if($mlsRealtors){
            $pos = 0;
            foreach($mlsRealtors as $mlsRealtor){
                $mlsRealtorId = $mlsRealtor->getProperty('id');
                if(isset($deleteLinks[$mlsRealtorId])){
                    $mlsRealtorLink = $deleteLinks[$mlsRealtorId];
                    unset($deleteLinks[$mlsRealtorId]);
                } else {
                    $mlsRealtorLink = new $defaultDAOClass('mls_listing_link_to_realtor', array(
                        'dbType' => static::getDBType()
                    ));
                    $mlsRealtorLink->setProperty('mls_listing_id', $mlsListingId);
                    $mlsRealtorLink->setProperty('mls_realtor_id', $mlsRealtorId);
                }
                $mlsRealtorLink->setProperty('status', 1);
                $mlsRealtorLink->setProperty('pos', $pos);
                $mlsRealtorLink->save();
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
     * @param AbstractMLSListing $mlsLising
     * @param PHRETS\Models\Search\Record $record
     * @return boolean
     */
    public static function linkListingToRealtorsFromRecord(AbstractMLSListing $mlsListing, PHRETS\Models\Search\Record $record){
        $mlsRealtors = array();
        $mlsTypeRef = $mlsListing->getTypeRef();
        
        for($i=1; $i<=3; $i++){
            $emptyRealtor = true;
            $realtorSearch = static::search();
            $name = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_name', $mlsTypeRef)];
            $login = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_login', $mlsTypeRef)];
            $phone = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_phone', $mlsTypeRef)];
            $email = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_email', $mlsTypeRef)];
            $url = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_url', $mlsTypeRef)];
            $officeId = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_office_id', $mlsTypeRef)];
            $realtorId = $record[GI_RETSField::getFieldId('list_realtor_' . $i . '_id', $mlsTypeRef)];
            if($name){
                $emptyRealtor = false;
                $realtorSearch->filter('name', $name);
            }
            if($login){
                $emptyRealtor = false;
                $realtorSearch->filter('login', $login);
            }
            if($phone){
                $emptyRealtor = false;
                $realtorSearch->filter('phone', $phone);
            }
            if($email){
                $emptyRealtor = false;
                $realtorSearch->filter('email', $email);
            }
            if($url){
                $emptyRealtor = false;
                $realtorSearch->filter('url', $url);
            }
            if($officeId){
                $emptyRealtor = false;
                $realtorSearch->filter('office_id', $officeId);
            }
            if($realtorId){
                $emptyRealtor = false;
                $realtorSearch->filter('realtor_id', $realtorId);
            }
            if(!$emptyRealtor){
                $realtorResult = $realtorSearch->select();
                if($realtorResult){
                    $mlsRealtor = $realtorResult[0];
                } else {
                    $mlsRealtor = static::buildNewModel();
                    $mlsRealtor->setProperty('name', $name);
                    $mlsRealtor->setProperty('login', $login);
                    $mlsRealtor->setProperty('phone', $phone);
                    $mlsRealtor->setProperty('email', $email);
                    $mlsRealtor->setProperty('url', $url);
                    $mlsRealtor->setProperty('office_id', $officeId);
                    $mlsRealtor->setProperty('realtor_id', $realtorId);
                    $mlsRealtor->save();
                }
                $mlsRealtors[] = $mlsRealtor;
            }
        }
        return static::linkListingToRealtors($mlsListing, $mlsRealtors);
    }
    
}
