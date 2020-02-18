<?php
/**
 * Description of AbstractTagFactory
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.1
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
            case 'feos_option':
                $model = new TagFEOSOption($map);
                break;
            case 'contact_sub_cat':
                $model = new TagContactSubcat($map);
                break;
            case 'content':
                $model = new TagContent($map);
                break;
            case 'qna':
                $model = new TagQnA($map);
                break;
            case 'location':
                $model = new TagLocation($map);
                break;
            case 'contact':
            case 'accounting_loc':
            case 'expense':
            case 'expense_item':
            case 'income':
            case 'income_item':
            case 'payment':
            case 'tag':
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
            case 'contact_sub_cat':
                $typeRefs = array('contact_sub_cat');
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
            case 'feos_option':
                $typeRefs = array('feos_option');
                break;
            case 'tag':
                $typeRefs = array('tag');
                break;
            case 'qna':
                $typeRefs = array('qna');
                break;
            case 'location':
                $typeRefs = array('location');
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
     * @return AbstractTag[]
     */
    public static function getByModel(GI_Model $model, $idsAsKey = false, $dbType = 'client', $typeRef = NULL, $includingSoftDeletedLink = false, $contextRef = NULL){
        $tableName = $model->getTableName();
        $itemId = $model->getId();
        
        $tagTable = dbConfig::getDbPrefix($dbType) . 'tag';
        $tagSearch = static::search();
        $tagSearch->setDBType($dbType);
        $tagSearch->createJoin('item_link_to_tag', 'tag_id', $tagTable, 'id', 'TL')
                ->filter('TL.item_id', $itemId)
                ->filter('TL.table_name', $tableName);
        if (!empty($typeRef)) {
            $tagSearch->filterByTypeRef($typeRef);
        }
        if(!empty($contextRef)){
            $tagSearch->filter('TL.context_ref', $contextRef);
        }
        if ($includingSoftDeletedLink) {
            $tagSearch->filterNotNull('TL.status');
        }
        $tagSearch->filter('system', 0);
        $tags = $tagSearch->select($idsAsKey);
        
        return $tags;
    }

    public static function linkModelAndTag(GI_Model $model, AbstractTag $tag, $dbType = 'client', $contextRef = '') {
        $tableName = $model->getTableName();
        $itemId = $model->getId();
        $tagId = $tag->getId();

        $existingSearch = new GI_DataSearch('item_link_to_tag');
        if(!empty($dbType)){
            $existingSearch->setDBType($dbType);
        }
        $existingSearch->filter('item_id', $itemId)
                ->filter('table_name', $tableName)
                ->filter('tag_id', $tagId)
                ->filterNotNull('status');
        if (!empty($contextRef)) {
            $existingSearch->filter('context_ref', $contextRef);
        }
        $existingResult = $existingSearch->select();

        if ($existingResult) {
            $tagLink = $existingResult[0];
            if (!$tagLink->getProperty('status')) {
                $tagLink->setProperty('status', 1);
                return $tagLink->save();
            }
            return true;
        } else {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $options = array();
            if(!empty($dbType)){
                $options['dbType'] = $dbType;
            }
            $tagLink = new $defaultDAOClass('item_link_to_tag', $options);
            $tagLink->setProperty('table_name', $tableName);
            $tagLink->setProperty('item_id', $itemId);
            $tagLink->setProperty('tag_id', $tagId);
            if (!empty($contextRef)) {
                $tagLink->setProperty('context_ref', $contextRef);
            }
            return $tagLink->save();
        }
        
        return false;
    }

    public static function unlinkModelAndTag(GI_Model $model, AbstractTag $tag, $dbType = 'client', $contextRef = '') {
        $tableName = $model->getTableName();
        $itemId = $model->getId();
        $tagId = $tag->getId();
        $search = new GI_DataSearch('item_link_to_tag');
        if(!empty($dbType)){
            $search->setDBType($dbType);
        }
        $search->filter('table_name', $tableName);
        $search->filter('item_id', $itemId);
        $search->filter('tag_id', $tagId);
        if (!empty($contextRef)) {
            $search->filter('context_ref', $contextRef);
        }
        $linkDAOArray = $search->select();
        if (empty($linkDAOArray)) {
            return true;
        }
        foreach ($linkDAOArray as $linkDAO) {
            if (!$linkDAO->softDelete()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param GI_Model $model
     * @param AbstractTag[] $tags
     * @param string $dbType
     * @param string $contextRef
     * @return boolean
     */
    public static function adjustTagsOnModel(GI_Model $model, $tags = array(), $dbType = 'client', $contextRef = ''){
        $existingTags = static::getByModel($model, true, $dbType, NULL, false, $contextRef);
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
                    $result = static::linkModelAndTag($model, $tag, $dbType, $contextRef);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }
        foreach ($tagsToRemove as $tagToRemove) {
            if (!static::unlinkModelAndTag($model, $tagToRemove, $dbType, $contextRef)) {
                return false;
            }
        }
        
        return true;
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $tagTable = $dataSearch->prefixTableName('tag');
        $dataSearch->setSortAscending(true)
                ->orderBy($tagTable . '.pos', 'ASC', true);
        return $dataSearch;
    }
    
    /**
     * @return string[]
     */
    public static function getTagOptionsArrayByTypeRef($typeRef = NULL) {
     //   if (empty(static::$tagOptionsArray)) {
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
                    $daoId = $dao->getId();
                    $title = $dao->getProperty('title');
                    $returnArray[$daoId] = $title;
                }
            }
            return $returnArray;
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
     * @param GI_Model $model
     * @param string $typeRef
     * @return AbstractTagListFormView
     */
    public static function getTagListFormView(GI_Form $form, GI_Model $model, $typeRef) {
        $tagListFormView = new TagListFormView($form, $model);
        $tagListFormView->setTagType($typeRef);
        return $tagListFormView;
    }
    
    /**
     * @param AbstractTag $tag
     * @param AbstractTag[] $parentTags
     * @return boolean
     */
    public static function adjustParentTags(AbstractTag $tag, $parentTags = array()){
        $existingParents = static::getParentTags($tag);
        if (empty($existingParents)) {
            $existingParents = array();
        }
        $parentsToRemove = array();
        foreach ($existingParents as $parentToRemove) {
            $parentTagId = $parentToRemove->getId();
            $parentsToRemove[$parentTagId] = $parentToRemove;
        }
        if($parentTags){
            foreach ($parentTags as $parentTag) {
                $parentTagId = $parentTag->getId();
                if (isset($parentsToRemove[$parentTagId])) {
                    unset($parentsToRemove[$parentTagId]);
                } else {
                    $result = static::linkChildToParent($tag, $parentTag);
                    if (!$result) {
                        return false;
                    }
                }
            }
        }
        foreach ($parentsToRemove as $parentToRemove) {
            if (!static::unlinkChildFromParent($tag, $parentToRemove)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param AbstractTag $tag
     * @param string $dbType
     * @return GI_DataSearch
     */
    public static function getParentTagSearch($tag, $dbType = 'client'){
        $search = static::search();
        $search->setDBType($dbType);
        $tagTable = $search->prefixTableName('tag');
        $search->createJoin('tag_link_to_tag', 'p_tag_id', $tagTable, 'id', 'TLTT')
                ->filter('TLTT.status', 1);
        $search->filter('TLTT.c_tag_id', $tag->getId());
        return $search;
    }
    
    /**
     * @param AbstractTag $tag
     * @param string $dbType
     * @return AbstractTag[]
     */
    public static function getParentTags(AbstractTag $tag, $dbType = 'client'){
        $tagSearch = static::getParentTagSearch($tag, $dbType);
        $tags = $tagSearch->select();
        
        return $tags;
    }

    /**
     * @param AbstractTag $tag
     * @param AbstractTag $parentTag
     * @param type $dbType
     * @return boolean
     */
    public static function linkChildToParent(AbstractTag $tag, AbstractTag $parentTag, $dbType = 'client') {
        $tagId = $tag->getId();
        $parentTagId = $parentTag->getId();
        
        $existingSearch = new GI_DataSearch('tag_link_to_tag');
        $existingSearch->setDBType($dbType);
        $existingResult = $existingSearch->filter('c_tag_id', $tagId)
                ->filter('p_tag_id', $parentTagId)
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
            $tagLink = new $defaultDAOClass('tag_link_to_tag', array(
                'dbType' => $dbType
            ));
            $tagLink->setProperty('c_tag_id', $tagId);
            $tagLink->setProperty('p_tag_id', $parentTagId);
            return $tagLink->save();
        }
        
        return false;
    }

    /**
     * @param AbstractTag $tag
     * @param AbstractTag $parentTag
     * @param string $dbType
     * @return boolean
     */
    public static function unlinkChildFromParent(AbstractTag $tag, AbstractTag $parentTag, $dbType = 'client') {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $tagId = $tag->getId();
        $parentTagId = $parentTag->getId();
        $linkDAOArray = $defaultDAOClass::getByProperties('tag_link_to_tag', array(
            'c_tag_id' => $tagId,
            'p_tag_id' => $parentTagId
        ), $dbType);
        if (empty($linkDAOArray)) {
            return true;
        }
        $linkDAO = $linkDAOArray[0];
        return $linkDAO->softDelete();
    }
    
    public static function getTagIdChildTree($tagId, &$childTree = array()){
        //QUERY BELOW REQUIRES AT LEAST MYSQL 8.0
        $dbType = static::getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        if(!$dbConnection){
            return false;
        }
        
        $dbPrefix = dbConfig::getDbPrefix($dbType);
        
        $finalQuery = 'WITH RECURSIVE TAG_LINK_CTE (c_tag_id, p_tag_id) AS (';
	$finalQuery .= 'SELECT c_tag_id, p_tag_id ';
	$finalQuery .= 'FROM ' . $dbPrefix . 'tag_link_to_tag ';
	$finalQuery .= 'WHERE p_tag_id = ' . $tagId . ' ';
	$finalQuery .= 'UNION ALL ';
	$finalQuery .= 'SELECT TAG_LINK.c_tag_id, TAG_LINK.p_tag_id ';
	$finalQuery .= 'FROM ' . $dbPrefix . 'tag_link_to_tag TAG_LINK ';
	$finalQuery .= 'INNER JOIN TAG_LINK_CTE ';
	$finalQuery .= 'ON TAG_LINK.p_tag_id = TAG_LINK_CTE.c_tag_id ';
	$finalQuery .= ') ';
	$finalQuery .= 'SELECT * FROM TAG_LINK_CTE;';
        try {
            $req = $dbConnection->query($finalQuery);
            $results = $req->fetch_all(MYSQLI_ASSOC);
            $childTree = array_map('intval', array_column($results, 'c_tag_id'));
        } catch (mysqli_sql_exception $ex) {
            if (DEV_MODE) {
                print_r($ex->getMessage());
                die();
            }
            return false;
        }
        return $childTree;
        
//        $search = new GI_DataSearch('tag_link_to_tag');
//        $search->filter('p_tag_id', $tagId);
//        $search->setSelectColumns(array(
//            'c_tag_id'
//        ));
//        $results = $search->select();
//        $childIds = array_map('intval', array_column($results, 'c_tag_id'));
//        if(!empty($childIds)){
//            $childTree = array_merge($childTree, $childIds);
//            foreach($childIds as $childId){
//                $childTree = array_merge($childTree, static::getTagIdChildTree($childId));
//            }
//        }
//        return $childTree;
    }
    
    public static function getTagIdParentTree($tagId, &$parentTree = array()){
        //QUERY BELOW REQUIRES AT LEAST MYSQL 8.0
        $dbType = static::getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        if(!$dbConnection){
            return false;
        }
        
        $dbPrefix = dbConfig::getDbPrefix($dbType);
        
        $finalQuery = 'WITH RECURSIVE TAG_LINK_CTE (c_tag_id, p_tag_id) AS (';
	$finalQuery .= 'SELECT c_tag_id, p_tag_id ';
	$finalQuery .= 'FROM ' . $dbPrefix . 'tag_link_to_tag ';
	$finalQuery .= 'WHERE p_tag_id = ' . $tagId . ' ';
	$finalQuery .= 'UNION ALL ';
	$finalQuery .= 'SELECT TAG_LINK.c_tag_id, TAG_LINK.p_tag_id ';
	$finalQuery .= 'FROM ' . $dbPrefix . 'tag_link_to_tag TAG_LINK ';
	$finalQuery .= 'INNER JOIN TAG_LINK_CTE ';
	$finalQuery .= 'ON TAG_LINK.c_tag_id = TAG_LINK_CTE.p_tag_id ';
	$finalQuery .= ') ';
	$finalQuery .= 'SELECT * FROM TAG_LINK_CTE;';
        try {
            $req = $dbConnection->query($finalQuery);
            $results = $req->fetch_all(MYSQLI_ASSOC);
            $parentTree = array_map('intval', array_column($results, 'p_tag_id'));
        } catch (mysqli_sql_exception $ex) {
            if (DEV_MODE) {
                print_r($ex->getMessage());
                die();
            }
            return false;
        }
        return $parentTree;
        
//        $search = new GI_DataSearch('tag_link_to_tag');
//        $search->filter('c_tag_id', $tagId);
//        $search->setSelectColumns(array(
//            'p_tag_id'
//        ));
//        $results = $search->select();
//        $parentIds = array_map('intval', array_column($results, 'p_tag_id'));
//        if(!empty($parentIds)){
//            $parentTree = array_merge($parentTree, $parentIds);
//            foreach($parentIds as $parentId){
//                $parentTree = array_merge($parentTree, static::getTagIdParentTree($parentId));
//            }
//        }
//        return $parentTree;
    }
    
}
