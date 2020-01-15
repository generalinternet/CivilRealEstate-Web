<?php
/**
 * Description of AbstractRecentActivityFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    2.0.0
 */
class AbstractRecentActivityFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'recent_activity';
    protected static $models = array();

    
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'activity':
            case 'add':
            case 'view':
            case 'edit':
            default:
                $model = new RecentActivity($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    /**
     * 
     * @param String $typeRef
     * @return String[]
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'activity':
                $typeRefs = array('activity');
                break;
            case 'add':
                $typeRefs = array('add');
                break;
            case 'view':
                $typeRefs = array('view');
                break;
            case 'edit':
                $typeRefs = array('edit');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param Integer $id - the id of the model
     * @param Boolean $force 
     * @return AbstractRecentActivity
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getNextAvailableModel(AbstractUser $user, $typeRef) {
        $model = NULL;
        $maxNumOfRecentActivityEntriesPerUser = LogService::getMaxNumOfRecentActivityEntriesPerUser();
        $search = static::search();
        $search->filter('uid', $user->getId())
                ->orderBy('last_mod', 'ASC');
        $count = $search->count();
        if (empty($count) || $count < $maxNumOfRecentActivityEntriesPerUser) {
            $model = static::buildNewModel($typeRef);
        } else {
            $models = $search->select();
            if (!empty($models)) {
                $model = $models[0];
                $model = static::changeModelType($model, $typeRef);
            }
        }
        return $model;
    }
    
    public static function getMostRecentModel(AbstractUser $user) {
        $search = static::search();
        $search->filter('uid', $user->getId())
                ->setItemsPerPage(1)
                ->setPageNumber(1)
                ->orderBy('last_mod', 'DESC');
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
}