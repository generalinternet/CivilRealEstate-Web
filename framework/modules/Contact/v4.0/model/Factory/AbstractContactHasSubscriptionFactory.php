<?php
/**
 * Description of AbstractContactHasSubscriptionFactory
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactHasSubscriptionFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_has_subscription';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContactHasSubscription
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new ContactHasSubscription($map);
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
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractContactHasSubscription
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactHasSubscription
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getModelsByContact(AbstractContact $contact, $activeOnDateTime = NULL) {
        if (empty($activeOnDateTime)) {
            $activeOnDateTime = GI_Time::getDateTime();
        }
        $search = static::search();
        $search->filter('contact_id', $contact->getId())
                ->filterLessThan('start_date_time', $activeOnDateTime);
        $search->filterGroup()
                ->filterGroup()
                ->filterNull('end_date_time')
                ->closeGroup()
                ->orIf()
                ->filterGroup()
                ->filterGreaterThan('end_date_time', $activeOnDateTime)
                ->closeGroup()
                ->closeGroup()
                ->andIf();
        $search->orderBy('start_date_time', 'ASC');
        return $search->select();
    }

    public static function getModelsByContactStartAfterDate(AbstractContact $contact, $startAfterDateTime = NULL) {
        if (empty($startAfterDateTime)) {
            $startAfterDateTime = GI_Time::getDateTime();
        }
        $search = static::search();
        $search->filter('contact_id', $contact->getId())
                ->filterGreaterThan('start_date_time', $startAfterDateTime);
        $search->orderBy('start_date_time', 'ASC');
        return $search->select();
    }
    
}