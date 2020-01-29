<?php
/**
 * Description of AbstractContactEventFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactEventFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_event';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'event':
            case 'email':
            case 'meeting':
            case 'phone_call':
            default:
                $model = new ContactEvent($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'event':
                $typeRefs = array('event');
                break;
            case 'email':
                $typeRefs = array('email');
                break;
            case 'meeting':
                $typeRefs = array('meeting');
                break;
            case 'phone_call':
                $typeRefs = array('phone_call');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    public static function getMulpleDatesTypeString() {
        return 'event';
    }
    
    public static function getMulpleTimesTypeString() {
        return 'event,meeting';
    }
}
