<?php

/**
 * Description of AbstractContextRoleFactory
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContextRoleFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'context_role';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new ContextRole($map);
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
     * @return AbstractContextRole
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getContextRoleCount(GI_Model $subjectModel) {
        $search = static::search();
        $search->filter('table_name', $subjectModel->getTableName());
        $itemId = $subjectModel->getItemId();
        if (empty($itemId)) {
            $search->filterNull('item_id');
        } else {
            $search->filter('item_id', $itemId);
        }
        $count = $search->count();
        if (empty($count)) {
            $count = 0;
        }
        return $count;
    }
    
    public static function getHighestPOS(GI_Model $subjectModel) {
        $search = static::search();
        $search->filter('table_name', $subjectModel->getTableName());
        $itemId = $subjectModel->getId();
        if (!empty($itemId)) {
            $search->filter('item_id', $itemId);
        } else {
            $search->filterNULL('item_id');
        }
        $search->setPageNumber(1)
                ->setItemsPerPage(1)
                ->orderBy('pos', 'DESC');
        $results = $search->select();
        if (!empty($results)) {
            $model = $results[0];
            return $model->getProperty('pos');
        }
        return 0;
    }

}
