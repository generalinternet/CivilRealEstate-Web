<?php
/**
 * Description of AbstractPaymentAccountFactory  
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractPaymentAccountFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'payment_acc';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'account':
            case 'credit':
            case 'chequing':
            default:
                $model = new PaymentAccount($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'account':
                $typeRefs = array('account');
                break;
            case 'credit':
                $typeRefs = array('credit');
                break;
            case 'chequing':
                $typeRefs = array('chequing');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param string $typeRef
     * @return AbstractPaymentAccount
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractPaymentAccount
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
}