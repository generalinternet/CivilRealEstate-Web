<?php

abstract class AbstractREListingFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 're_listing';
    protected static $models = array();
    protected static $modelsMLSListingIdKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractREListing
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'res':
            case 'RD_1':
            case 'RA_2':
            case 'MF_3':
            case 'LD_4':
                $model = new REListingRes($map);
                break;
            case 'res_mod':
                $model = new REListingResMod($map);
                break;
            case 'com':
            case 'CM_1':
                $model = new REListingCom($map);
                break;
            case 'com_mod':
                $model = new REListingComMod($map);
                break;
            default:
                $model = new REListing($map);
                break;
        }
        return self::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
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
            case 'res_mod':
                $typeRefs = array('res', 'res_mod');
                break;
            case 'com':
                $typeRefs = array('com', 'com');
                break;
            case 'CM_1':
                $typeRefs = array('com', 'CM_1');
                break;
            case 'com_mod':
                $typeRefs = array('com', 'com_mod');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractREListing
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param int $mlsListingId
     * @return AbstractREListing
     */
    public static function getModelByMLSListingId($mlsListingId){
        if(isset(static::$modelsMLSListingIdKey) && isset(static::$modelsMLSListingIdKey[$mlsListingId])){
            return static::$modelsMLSListingIdKey[$mlsListingId];
        }
        $mlsListingResult = static::search()
                ->filter('mls_listing_id', $mlsListingId)
                ->select();
        if($mlsListingResult){
            static::$modelsMLSListingIdKey[$mlsListingId] = $mlsListingResult[0];
            return static::$modelsMLSListingIdKey[$mlsListingId];
        }
        return NULL;
        
    }
    
    public static function tagPropertyType($reListing, $tagIds){
        $deleteTags = TagFactory::getByModel($reListing, true, static::getDBType());
        
        $tags = array();
        
        foreach($tagIds as $tagId){
            $tag = TagFactory::getModelById($tagId);
            if($tag){
                $tags[$tagId] = $tag;
                if(isset($deleteTags[$tagId])){
                    unset($deleteTags[$tagId]);
                }
            }
        }
        
        foreach($tags as $tagId => $tag){
            TagFactory::linkModelAndTag($reListing, $tag, static::getDBType());
        }
        
        foreach($deleteTags as $deleteTag){
            TagFactory::unlinkModelAndTag($reListing, $deleteTag, static::getDBType());
        }
        
        return true;
    }
    
}
