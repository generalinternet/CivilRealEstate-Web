<?php

class CharityFactory extends GI_ModelFactory{

    protected static $primaryDAOTableName = 'charity';
    protected static $models = array();
    protected static $optionsArray = NULL;

    
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Content($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
}