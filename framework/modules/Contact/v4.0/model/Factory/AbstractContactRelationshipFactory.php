<?php
/**
 * Description of AbstractContactRelationshipFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactRelationshipFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_relationship';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'franchise_owner':
            case 'relationship':
            default:
                $model = new ContactRelationship($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'relationship':
                $typeRefs = array('relationship');
                break;
            case 'franchise_owner':
                $typeRefs = array('franchise_owner');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    public static function getTypesArray($rootType = NULL, $topLevelWithIdAsKey = false, $typeProperty = 'title', $stopAtRoot = false, $excludeBranches = false, $includeBranchRefs = array()) {
        $types = parent::getTypesArray($rootType, $topLevelWithIdAsKey, $typeProperty, $stopAtRoot, $excludeBranches, $includeBranchRefs);
        if (!ProjectConfig::getIsFranchisedSystem() || !Permission::verifyByRef('edit_franchises')) {
            if (isset($types['franchise_owner'])) {
                unset($types['franchise_owner']);
            }
        }
        return $types;
    }
    
    public static function establishRelationship(AbstractContact $parent, AbstractContact $child, $typeRef = 'relationship', $saveNewModel = true) {
        $search = static::search();
        $search->filter('p_contact_id', $parent->getProperty('id'))
                ->filter('c_contact_id', $child->getProperty('id'))
                ->filterByTypeRef($typeRef);
        $array = $search->select();
        if (!empty($array)) {
            return $array[0];
        }
        $softDeletedSearch = static::search();
        $softDeletedSearch->filter('p_contact_id', $parent->getProperty('id'))
                ->filter('c_contact_id', $child->getProperty('id'))
                ->filter('status', 0)
                ->filterByTypeRef($typeRef);
        $softDeletedArray = $softDeletedSearch->select();
        if (!empty($softDeletedArray)) {
           $softDeletedModel = $softDeletedArray[0];
           if ($softDeletedModel->unSoftDelete()) {
               return $softDeletedModel;
            }
        }
        $newModel = static::buildNewModel($typeRef);
        $newModel->setProperty('p_contact_id', $parent->getProperty('id'));
        $newModel->setProperty('c_contact_id', $child->getProperty('id'));
        if ($saveNewModel) {
            if ($newModel->save()) {
                return $newModel;
            }
            return NULL;
        } else {
            return $newModel;
        }
    }

}
