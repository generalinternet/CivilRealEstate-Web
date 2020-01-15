<?php

/**
 * Description of AbstractDocumentFactory
 * 
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.1
 */
abstract class AbstractDocumentFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'document';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'document':
            default:
                $model = new Document($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'document':
                $typeRefs = array('document');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

     /**
     * 
     * @param Integer $id
     * @param Boolean $force
     * @return AbstractDocument
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

}
