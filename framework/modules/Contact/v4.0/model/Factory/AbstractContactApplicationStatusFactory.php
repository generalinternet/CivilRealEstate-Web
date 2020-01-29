<?php
/**
 * Description of AbstractContactApplicationStatusFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactApplicationStatusFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_app_status';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContactApplicationStaus
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'client':
            case 'app':
            default:
                $model = new ContactApplicationStatus($map);
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
            case 'app':
                $typeRefs = array('app');
                break;
            case 'client':
                $typeRefs = array('client');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractContactApplicationStatus
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactApplicationStatus
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getNextStatusModelByApplication(AbstractContactApplication $contactApplication) {
        $currentStatus = $contactApplication->getCurrentStatus();
        if (empty($currentStatus)) {
            return NULL;
        }
        $search = static::search();
        $search->filterByTypeRef($currentStatus->getTypeRef())
                ->filterGreaterOrEqualTo('rank', $currentStatus->getProperty('rank'))
                ->filterNotEqualTo('id', $currentStatus->getId());
        $search->setItemsPerPage(1)
                ->setPageNumber(1)
                ->orderBy('rank', 'ASC');
        
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }

    public static function getNextStatusModelByStatusModel(AbstractContactApplicationStatus $status) {
        $search = static::search();
        $search->filterByTypeRef($status->getTypeRef())
                ->filterGreaterOrEqualTo('rank', $status->getProperty('rank'))
                ->filterNotEqualTo('id', $status->getId());
        $search->setItemsPerPage(1)
                ->setPageNumber(1)
                ->orderBy('rank', 'ASC');
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }

    public static function getPreviousStatusModelByStatusModel(AbstractContactApplicationStatus $status) {
        $search = static::search();
        $search->filterByTypeRef($status->getTypeRef())
                ->filterLessOrEqualTo('rank', $status->getProperty('rank'))
                ->filterNotEqualTo('id', $status->getId());
        $search->setItemsPerPage(1)
                ->setPageNumber(1)
                ->orderBy('rank', 'DESC');
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
    public static function getStatusModelByRefAndTypeRef($ref, $typeRef) {
        $search = static::search();
        $search->filterByTypeRef($typeRef)
                ->filter('ref', $ref);
        $search->groupBy('id')
                ->orderBy('id', 'ASC');
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }

}
