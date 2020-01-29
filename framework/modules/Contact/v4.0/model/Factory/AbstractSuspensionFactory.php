<?php
/**
 * Description of AbstractSuspensionFactory
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractSuspensionFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'suspension';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractSuspension
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'non_payment':
            case 'suspension':
            default:
                $model = new Suspension($map);
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
            case 'non_payment':
                $typeRefs = array('non_payment');
                break;
            case 'suspension':
                $typeRefs = array('suspension');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractSuspension
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractSuspension
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    /**
     * 
     * @param AbstractContact $contact
     * @param string $typeRef
     * @param string $startDateTimeString
     * @param sttring $endDateTimeString
     * @param boolean $activeOnly
     * @return AbstractSuspension[]
     */
    public static function getSuspensionsByContact(AbstractContact $contact, $typeRef = '', $appliesToDateTime = NULL, $activeOnly = true) {
        $search = static::getSuspensionsByContactDataSearch($contact, $typeRef, $appliesToDateTime, $activeOnly);
        return $search->select();
    }

    /**
     * 
     * @param AbstractContact $contact
     * @param string $typeRef
     * @param string $startDateTimeString
     * @param sttring $endDateTimeString
     * @param boolean $activeOnly
     * @return integer
     */
    public static function getSuspensionCountByContact(AbstractContact $contact, $typeRef = '', $appliesToDateTime = NULL, $activeOnly = true) {
        $search = static::getSuspensionsByContactDataSearch($contact, $typeRef, $appliesToDateTime, $activeOnly);
        return $search->count();
    }

    protected static function getSuspensionsByContactDataSearch(AbstractContact $contact, $typeRef = '', $appliesToDateTime = NULL, $activeOnly = true) {
        $search = static::search();
        $tableName = $search->prefixTableName('suspension');

        $directJoin = $search->createLeftJoin('contact', 'id', $tableName, 'contact_id', 'DIR_CONTACT');
        $directJoin->filter('DIR_CONTACT.status', 1);
        $search->ignoreStatus('DIR_CONTACT');

        $viaParentJoin = $search->createLeftJoin('contact', 'id', $tableName, 'contact_id', 'PARENT_CONTACT');
        $viaParentJoin->filter('PARENT_CONTACT.status', 1);
        $search->ignoreStatus('PARENT_CONTACT');
        $parentRelJoin = $search->createLeftJoin('contact_relationship', 'p_contact_id', 'PARENT_CONTACT', 'id', 'CONREL');
        $parentRelJoin->filter('CONREL.status', 1);
        $search->ignoreStatus('CONREL');
        
        $search->filterGroup()
                ->filterGroup()
                ->filter('DIR_CONTACT.id', $contact->getId())
                ->closeGroup()
                ->orIf()
                ->filterGroup()
                ->filter('CONREL.c_contact_id', $contact->getId())
                ->closeGroup()
                ->closeGroup()
                ->andIf();
        
        if (!empty($typeRef)) {
            $search->filterByTypeRef($typeRef);
        }
        if (!empty($appliesToDateTime)) {
            $search->filterLessThan('start_date_time', $appliesToDateTime);
            $search->filterGroup()
                    ->filterGroup()
                    ->filterGreaterThan('end_date_time', $appliesToDateTime)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->filterNull('end_date_time')
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
        }
        
        if ($activeOnly) {
            $search->filter('active', 1);
        }
        return $search;
    }

       
}