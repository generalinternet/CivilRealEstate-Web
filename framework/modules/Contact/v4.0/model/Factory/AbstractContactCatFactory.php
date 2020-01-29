<?php
/**
 * Description of AbstractContactCatFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
abstract class AbstractContactCatFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'contact_cat';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractContactCat
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'vendor':
                $model = new ContactCatVendor($map);
                break;
            case 'client':
                $model = new ContactCatClient($map);
                break;
            case 'internal':
                $model = new ContactCatInternal($map);
                break;
            case 'qna_buyer':
                $model = new ContactCatQnABuyer($map);
                break;
            case 'qna_vendor':
                $model = new ContactCatQnAVendor($map);
                break;
            case 'category':
            default:
                $model = new ContactCat($map);
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
            case 'category':
                $typeRefs = array('category');
                break;
            case 'vendor':
                $typeRefs = array('vendor', 'vendor');
                break;
            case 'client':
                $typeRefs = array('client', 'client');
                break;
            case 'internal':
                $typeRefs = array('internal');
                break;
            case 'qna_buyer':
                $typeRefs = array('qna_buyer');
                break;
            case 'qna_vendor':
                $typeRefs = array('client', 'qna_vendor');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractContactCat
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return AbstractContactCat
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    public static function getModelsByContact(AbstractContact $contact, $typeRefs = array()) {
        $search = static::search();
        $search->filter('contact_id', $contact->getProperty('id'));
        if (!empty($typeRefs)) {
            $count = count($typeRefs);
            $search->filterGroup();
            for ($i=0;$i<$count;$i++) {
                $search->filterByTypeRef($typeRefs[$i]);
                if ($i < $count - 1) {
                    $search->orIf();
                }
            }
            $search->closeGroup()
                    ->andIf();
        }
        $search->orderBy('id');
        return $search->select();
    }

    public static function getModelByContactAndTypeRef(AbstractContact $contact, $typeRef) {
        $array = static::getModelsByContact($contact, array($typeRef));
        if (!empty($array)) {
            return $array[0];
        }
        return NULL;
    }
    
    public static function getTypeRefArray($typeRef) {
        return static::getTypeRefArrayFromTypeRef($typeRef);
    }
    
}
