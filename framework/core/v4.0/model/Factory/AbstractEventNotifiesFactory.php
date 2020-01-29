<?php

/**
 * Description of AbstractEventNotifiesFactory
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventNotifiesFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'event_notifies';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new EventNotifies($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
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
     * @return AbstractEventNotifies
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getDefaultEventNotifies(GI_Model $model, AbstractEvent $event, $returnNewIfEmpty = true) {
        $search = static::search();
        $search->filter('event_id', $event->getId())
                ->filterNull('role_id')
                ->filterNull('context_role_id')
                ->filterNull('user_id')
                ->filterNotNull('no_roles')
                ->filterNotNull('no_context_roles')
                ->filterNotNull('no_users')
                ->filter('table_name', $model->getTableName())
                ->filter('item_id', $model->getId());
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        if ($returnNewIfEmpty) {
            $newModel = static::buildNewModel();
            $newModel->setProperty('table_name', $model->getTableName());
            $newModel->setProperty('item_id', $model->getId());
            $newModel->setProperty('event_id', $event->getId());
            return $newModel;
        }
        return NULL;
    }

}
