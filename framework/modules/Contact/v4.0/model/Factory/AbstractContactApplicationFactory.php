<?php
/**
 * Description of AbstractContactApplicationFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactApplicationFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_app';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContactApplication
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'client':
                $model = new ContactApplicationClient($map);
                break;
            case 'app':
            default:
                $model = new ContactApplication($map);
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
     * @return AbstractContactApplication
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactApplication
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    

    /**
     * @param AbstractContactOrg $contactOrg
     * @return AbstractContactApplication
     */
    public static function getModelByContactOrg(AbstractContactOrg $contactOrg) {
        $search = static::search();
        $search->filter('contact_org_id', $contactOrg->getId());
        $search->orderBy('id', 'ASC')
                ->setItemsPerPage(1)
                ->setPageNumber(1);
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
}