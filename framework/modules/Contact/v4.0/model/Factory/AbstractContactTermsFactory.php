<?php

abstract class AbstractContactTermsFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_terms';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        $model = new ContactTerms($map);
        return static::setFactoryClassName($model);
    }
    
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        return array();
    }

}
