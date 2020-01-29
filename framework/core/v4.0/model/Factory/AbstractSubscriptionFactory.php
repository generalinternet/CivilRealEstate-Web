<?php
/**
 * Description of AbstractSubscriptionFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractSubscriptionFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'subscription';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractSubscription
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'stripe':
                $model = new SubscriptionStripe($map);
                break;
            case 'subscription':
            default:
                $model = new Subscription($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'subscription':
                $typeRefs = array('subscription');
                break;
            case 'stripe':
                $typeRefs = array('stripe');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractSubscription
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractSubscription
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param AbstractContact $contact
     * @return AbstractSubscription[]
     */
    public static function getModelsByContact(AbstractContact $contact) {
        $search = static::search();
        $tableName = $search->prefixTableName('subscription');
        $search->join('contact_has_subscription', 'subscription_id', $tableName, 'id', 'CHS');
        $search->filter('CHS.contact_id', $contact->getId());
        $search->groupBy('id')
                ->orderBy('id', 'ASC');
        return $search->select();
    }
   
    
}