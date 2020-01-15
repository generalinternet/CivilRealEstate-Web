<?php
/**
 * Description of AbstractTagFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
abstract class AbstractTagFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'tag';
    protected static $models = array();
    protected static $modelsRefKeyByType = array();
    protected static $tagOptionsArray = NULL;

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'inventory':
                $model = new TagInventory($map);
                break;
            case 'contact':
            case 'accounting_loc':
            case 'expense':
            case 'expense_item':
            case 'income':
            case 'income_item':
            case 'payment':
            case 'tag':
            case 'content':
            case 'invoice_line':
            default:
                $model = new Taggi($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'content':
                $typeRefs = array('content');
                break;
            case 'contact':
                $typeRefs = array('contact', 'contact');
                break;
            case 'vendor':
                $typeRefs = array('contact', 'vendor');
                break;
            case 'client':
                $typeRefs = array('contact', 'client');
                break;
            case 'internal':
                $typeRefs = array('contact', 'internal');
                break;
            case 'accounting_loc':
                $typeRefs = array('accounting_loc');
                break;
            case 'expense':
                $typeRefs = array('expense');
                break;
            case 'expense_item':
                $typeRefs = array('expense_item');
                break;
            case 'income':
                $typeRefs = array('income');
                break;
            case 'income_item':
                $typeRefs = array('income_item');
                break;
            case 'payment':
                $typeRefs = array('payment');
                break;
            case 'time_interval':
                $typeRefs = array('time_interval');
                break;
            case 'inventory':
                $typeRefs = array('inventory');
                break;
            case 'invoice_line':
                $typeRefs = array('invoice_line');
                break;
            case 'tag':
                $typeRefs = array('tag');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    public static function getPTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $numberOfRefs = count($typeRefsArray);
        if ($numberOfRefs > 1) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            return $pTypeRef;
        } else {
            $pTypeRef = 'tag';
        }
        return $pTypeRef;
    }
    
    /**
     * @param string $ref
     * @param string $typeRef
     * @return AbstractTag
     */
    public static function getModelByRefAndTypeRef($ref, $typeRef) {
        if(isset(static::$modelsRefKeyByType[$typeRef][$ref])){
            return static::$modelsRefKeyByType[$typeRef][$ref];
        }
        $search = static::search()
                ->filterByTypeRef($typeRef)
                ->filter('ref', $ref)
                ->orderBy('id');
        $result = $search->select();
        if (!empty($result)) {
            $status = $result[0];
            static::$modelsRefKeyByType[$typeRef][$ref] = $status;
            return $status;
        }
        return NULL;
    }

    /**
     * @deprecated - use getModelByRefAndTypeRef instead
     * @param String $tagRef
     * @param String $typeRef
     * @return AbstractTag
     */
    public static function getModelByTagRefAndTypeRef($tagRef, $typeRef) {
        return static::getModelByRefAndTypeRef($tagRef, $typeRef);
    }

    /**
     * @param GI_Model $model
     * @return AbstractTag
     */
    public static function getByModel(GI_Model $model, $idsAsKey = false, $dbType = 'client', $typeRef = NULL, $includingSoftDeletedLink = false){
        $tableName = $model->getTableName();
        $itemId = $model->getProperty('id');
        
        $tagTable = dbConfig::getDbPrefix($dbType) . 'tag';
        $tagSearch = static::search();
        $tagSearch->setDBType($dbType);
        $tagSearch->createJoin('item_link_to_tag', 'tag_id', $tagTable, 'id', 'TL')
                ->filter('TL.item_id', $itemId)
                ->filter('TL.table_name', $tableName);
        if (!empty($typeRef)) {
            $tagSearch->filterByTypeRef($typeRef);
        }
        if ($includingSoftDeletedLink) {
            $tagSearch->filterNotNull('TL.status');
        }
        $tags = $tagSearch->select($idsAsKey);
        
        return $tags;
    }

    public static function linkModelAndTag(GI_Model $model, AbstractTag $tag, $dbType = 'client') {
        $tableName = $model->getTableName();
        $itemId = $model->getProperty('id');
        $tagId = $tag->getProperty('id');
        
        $existingSearch = new GI_DataSearch('item_link_to_tag');
        $existingSearch->setDBType($dbType);
        $existingResult = $existingSearch->filter('item_id', $itemId)
                ->filter('table_name', $tableName)
                ->filter('tag_id', $tagId)
                ->filterNotNull('status')
                ->select();
        
        if($existingResult){
            $tagLink = $existingResult[0];
            if(!$tagLink->getProperty('status')){
                $tagLink->setProperty('status', 1);
                return $tagLink->save();
            }
            return true;
        } else {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $tagLink = new $defaultDAOClass('item_link_to_tag', array(
                'dbType' => $dbType
            ));
            $tagLink->setProperty('table_name', $tableName);
            $tagLink->setProperty('item_id', $itemId);
            $tagLink->setProperty('tag_id', $tagId);
            return $tagLink->save();
        }
        
        return false;
        /*
        $searchArray = array(
            'table_name' => $tableName,
            'item_id' => $itemId,
            'tag_id' => $tagId
        );
        $existingDAOArray = $defaultDAOClass::getByProperties('item_link_to_tag', $searchArray);
        if (!empty($existingDAOArray)) {
            return true;
        }
        $searchArray['status'] = 0;
        $softDeletedDAOArray = $defaultDAOClass::getByProperties('item_link_to_tag', $searchArray);
        if (!empty($softDeletedDAOArray)) {
            $softDeletedDAO = $softDeletedDAOArray[0];
            $softDeletedDAO->setProperty('status', 1);
            if ($softDeletedDAO->save()) {
                return true;
            }
        }
        $newLinkDAO = new $defaultDAOClass('item_link_to_tag');
        $newLinkDAO->setProperty('table_name', $tableName);
        $newLinkDAO->setProperty('item_id', $itemId);
        $newLinkDAO->setProperty('tag_id', $tagId);
        return $newLinkDAO->save();
        */
    }

    public static function unlinkModelAndTag(GI_Model $model, AbstractTag $tag, $dbType = 'client') {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $tableName = $model->getTableName();
        $itemId = $model->getProperty('id');
        $tagId = $tag->getProperty('id');
        $linkDAOArray = $defaultDAOClass::getByProperties('item_link_to_tag', array(
                    'table_name' => $tableName,
                    'item_id' => $itemId,
                    'tag_id' => $tagId
        ), $dbType);
        if (empty($linkDAOArray)) {
            return true;
        }
        $linkDAO = $linkDAOArray[0];
        return $linkDAO->softDelete();
    }
    
    /**
     * @param GI_Model $model
     * @param AbstractTag[] $tags
     * @return boolean
     */
    public static function adjustTagsOnModel(GI_Model $model, $tags = array()){
        $existingTags = static::getByModel($model);
        if (empty($existingTags)) {
            $existingTags = array();
        }
        $tagsToRemove = array();
        foreach ($existingTags as $tagToRemove) {
            $tagId = $tagToRemove->getId();
            $tagsToRemove[$tagId] = $tagToRemove;
        }
        if($tags){
            foreach ($tags as $tag) {
                $tagId = $tag->getId();
                if (isset($tagsToRemove[$tagId])) {
                    unset($tagsToRemove[$tagId]);
                } else {
                    $result = static::linkModelAndTag($model, $tag);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }
        foreach ($tagsToRemove as $tagToRemove) {
            if (!static::unlinkModelAndTag($model, $tagToRemove)) {
                return false;
            }
        }
        
        return true;
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true)
                ->orderBy('pos', 'ASC', true);
        return $dataSearch;
    }
    
    /**
     * @param string $valueColumn
     * @return string[]
     */
    public static function getTagOptionsArrayByTypeRef($typeRef = NULL) {
        if (empty(static::$tagOptionsArray)) {
            $returnArray = array();
            if (empty(TagFactory::getTypeRefArray($typeRef))) {
                //If there is no available type, return empty
                return $returnArray;
            }
            $daoSearch = static::search()
                    ->setSortAscending(true);
   
            if (!empty($typeRef)) {
                $daoSearch->filterByTypeRef($typeRef);
            }
            $daos = $daoSearch->select();
            if (!empty($daos)) {
                foreach ($daos as $dao) {
                    $daoId = $dao->getProperty('id');
                    $title = $dao->getProperty('title');
                    $returnArray[$daoId] = $title;
                }
            }
            static::$tagOptionsArray = $returnArray;
        }
        return static::$tagOptionsArray;
    }
    
    public static function getByRef($typeRef, $idsAsKey = false, $dbType = 'client'){
        $tagSearch = static::search();
        $tagSearch->setDBType($dbType)
                ->filterByTypeRef($typeRef);
        $tags = $tagSearch->select($idsAsKey);
        return $tags;
    }
    
    /** @return TagListView */
    public static function getTagListView(GI_Model $model) {
        $tags = static::getByModel($model);
        $tagListView = new TagListView($tags);
        return $tagListView;
    }
    
    /**
     * @param GI_Form $form
     * @return TagListFormView
     */
    public static function getTagListFormView($form, GI_Model $model, $typeRef) {
        $existingTags = static::getByModel($model, true);
        $allTags = static::getByRef($typeRef);
        $tagListFormView = new TagListFormView($form, $allTags, $existingTags);
        return $tagListFormView;
    }
}
