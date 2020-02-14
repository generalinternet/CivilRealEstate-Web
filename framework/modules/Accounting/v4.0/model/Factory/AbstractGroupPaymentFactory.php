<?php
/**
 * Description of AbstractGroupPaymentFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'group_payment';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'credit':
                $model = new GroupPaymentCredit($map);
                break;
            case 'refund':
                $model = new GroupPaymentRefund($map);
                break;
            case 'imported':
                $model = new GroupPaymentImported($map);
                break;
            default:
                $model = new GroupPayment($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'cheque':
                $typeRefs = array('cheque');
                break;
            case 'cash':
                $typeRefs = array('cash');
                break;
            case 'transfer':
                $typeRefs = array('transfer');
                break;
            case 'credit_card':
                $typeRefs = array('credit_card');
                break;
            case 'adjustment':
                $typeRefs = array('adjustment');
                break;
            case 'credit':
                $typeRefs = array('credit');
                break;
            case 'refund':
                $typeRefs = array('refund');
                break;
            case 'imported':
                $typeRefs = array('imported');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param String $typeRef
     * @return AbstractGroupPayment
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param Integer $id
     * @param Boolean $force
     * @return AbstractGroupPayment
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getAppliedPaymentSumByGroupPayment(AbstractGroupPayment $groupPayment) {
        $groupPaymentId = $groupPayment->getProperty('id');
        $search = PaymentFactory::search()
                ->filter('group_payment_id', $groupPaymentId)
                ->filter('void', 0)
                ->filter('cancelled', 0);
        $result = $search->sum(array('amount'));
        if (empty($result)) {
            $sum = 0;
        } else {
            $sum = $result['amount'];
        }
        return $sum;
    }
    
     public static function getTypesArray($rootType = NULL, $topLevelWithIdAsKey = false, $typeProperty = 'title', $stopAtRoot = false, $excludeBranches = false, $includeBranchRefs = array()) {
         $types = parent::getTypesArray($rootType, $topLevelWithIdAsKey, $typeProperty, $stopAtRoot, $excludeBranches, $includeBranchRefs);
         if (isset($types['refund'])) {
             unset($types['refund']);
         }
         return $types;
     }
     
     public static function linkGroupPaymentAndTag(AbstractGroupPayment $groupPayment, AbstractTag $tag) {
         $existingLinkSearch = new GI_DataSearch('group_payment_link_to_tag');
         $existingLinkSearch->filter('group_payment_id', $groupPayment->getProperty('id'))
                 ->filter('tag_id', $tag->getProperty('id'));
         $existingLinkArray = $existingLinkSearch->select();
         if (!empty($existingLinkArray)) {
             return true;
         }
         $softDeletedLinkSearch = new GI_DataSearch('group_payment_link_to_tag');
         $softDeletedLinkSearch->filter('group_payment_id', $groupPayment->getProperty('id'))
                 ->filter('tag_id', $tag->getProperty('id'))
                 ->filter('status', 0);
         $softDeletedLinkArray = $softDeletedLinkSearch->select();
         if (!empty($softDeletedLinkArray)) {
             $softDeletedLink = $softDeletedLinkArray[0];
             $softDeletedLink->setProperty('status', 1);
             if ($softDeletedLink->save()) {
                 return true;
             }
         }
         $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
         $newLink = new $defaultDAOClass('group_payment_link_to_tag');
         $newLink->setProperty('group_payment_id', $groupPayment->getProperty('id'));
         $newLink->setProperty('tag_id', $tag->getProperty('id'));
         return $newLink->save();
     }
     
     public static function unlinkGroupPaymentAndTag(AbstractGroupPayment $groupPayment, AbstractTag $tag) {
         $search = new GI_DataSearch('group_payment_link_to_tag');
         $search->filter('group_payment_id', $groupPayment->getProperty('id'))
                 ->filter('tag_id', $tag->getProperty('id'));
         $linkArray = $search->select();
         if (empty($linkArray)) {
             return true;
         }
         foreach ($linkArray as $linkDAO) {
             $linkDAO->setProperty('status', 0);
             if (!$linkDAO->save()) {
                 return false;
             }
         }
         return true;
     }

}
