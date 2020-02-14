<?php

/**
 * Description of AbstractEventFactory
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'event';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'project':
                $model = new EventProject($map);
                break;
            case 'purchase_order':
                $model = new EventPurchaseOrder($map);
                break;
            case 'sales_order':
                $model = new EventSalesOrder($map);
                break;
            case 'qna':
                $model = new EventQnA($map);
                break;
            case 'payment':
                $model = new EventPayment($map);
                break;
            case 'event':
            default:
                $model = new Event($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'project':
                $typeRefs = array('project');
                break;
            case 'purchase_order':
                $typeRefs = array('purchase_order');
                break;
            case 'sales_order':
                $typeRefs = array('sales_order');
                break;
            case 'qna':
                $typeRefs = array('qna');
                break;
            case 'payment':
                $typeRefs = array('payment');
                break;
            case 'event':
                $typeRefs = array('event');
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
     * @return AbstractEvent
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param String $typeRef
     * @return AbstractEvent
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    public static function getModelByRefAndTypeRef($ref, $typeRef) {
        $search = static::search();
        $search->filterByTypeRef($typeRef)
                ->filter('ref', $ref);
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }

    /**
     * 
     * @param String $typeRef
     * @return AbstractEvent[]
     */
    public static function getModelArrayByTypeRef($typeRef) {
        $search = static::search();
        $search->filterByTypeRef($typeRef)
                ->orderBy('pos', 'ASC')
                ->orderBy('id');
        return $search->select();
    }

}
