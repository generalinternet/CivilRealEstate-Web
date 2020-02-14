<?php
/**
 * Description of GI_ModelFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class GI_ModelFactory extends GI_Object {
    
    protected static $dbType = 'client';
    /**
     * It's important to leave this as NULL so that when "setDBType" is called it can set this value to the original value
     * @var string
     */
    protected static $originalDbType = NULL;
    protected static $deleteFKTableNameExceptions = array();
    
    /**
     * @abstract
     */
    abstract protected static function buildModelByTypeRef($typeRef, $map);
    
    /**
     * @abstract
     */
    abstract protected static function getTypeRefArrayFromTypeRef($typeRef);
    
    public static function getDBType(){
        return static::$dbType;
    }
    
    public static function setDBType($dbType){
        if(is_null(static::$originalDbType)){
            static::$originalDbType = static::$dbType;
        }
        static::$dbType = $dbType;
    }
    
    public static function resetDBType(){
        if(!is_null(static::$originalDbType)){
            static::$dbType = static::$originalDbType;
        }
    }
    
    public static function getDbPrefix(){
        return dbConfig::getDbPrefix(static::getDBType());
    }
    
    public static function getTypeRefArray($typeRef){
        return static::getTypeRefArrayFromTypeRef($typeRef);
    }
    
    /**
     * @return String The name of the root db table
     */
    public static function getTableName(){
        return static::$primaryDAOTableName;
    }
    
    /**
     * @param String $tableName
     * @return String The name of the type table associated with $tableName
     */
    public static function prepareTypeTableName($tableName){
        return $tableName . '_type';
    }
    
    public static function prepareTypeTableIdKey($tableName, $prepareTableName = false){
        if($prepareTableName){
            return static::prepareTypeTableIdKey(static::prepareTypeTableName($tableName));
        } else {
            return $tableName . '_id';
        }
    }
    
    public static function prepareTableLinkToTableName($tableName, $linkToTableName){
        return $tableName . '_link_to_' . $linkToTableName;
    }
    
    /**
     * @deprecated - To be remove in v3.0
     * This method is leftover from an earlier version of the framework, where a primary DAO could exist within multiple models of differing types.
     * This functionality was discarded, and this method therefore no longer serves a purpose.
     * @param Integer $id
     * @param Integer $status
     * @return GI_Model[] An array of models corresponding to the id
     */
    public static function getModelArrayById($id, $status = 1) {
        $primaryDAO = static::getDAOById(static::getTableName(), $id, $status);
        $modelArray = static::buildModelsWithExistingPrimaryDAO($primaryDAO);
        return $modelArray;
    }
    
    /**
     * @param integer $id
     * @param boolean $force
     * @return GI_Model
     */
    public static function getModelById($id, $force = false){
        if(ProjectConfig::storeModels() && isset(static::$models) && isset(static::$models[$id]) && !$force){
            return static::$models[$id];
        }
        $modelArray = static::getModelArrayById($id);
        if(isset($modelArray[0])){
            $model = $modelArray[0];
            if(!static::validateModel($model)){
                return NULL;
            }
            if(ProjectConfig::storeModels() && isset(static::$models)){
                static::$models[$id] = $model;
            }
            return $model;
        }
        return NULL;
    }
    
    public static function validateModel(GI_Model $model){
        if(!static::validateModelFranchise($model)){
            return false;
        }
        //other validation if necessary
        return true;
    }
    
    public static function validateModelFranchise(GI_Model $model){
        if(ProjectConfig::getIsFranchisedSystem()){
            $modelFranchiseId = $model->getProperty('franchise_id');
            $curFranchise = Login::getCurrentFranchise();
            if(!empty($modelFranchiseId) && $curFranchise && $curFranchise->getId() != $modelFranchiseId){
                trigger_error('Access denied to "' . get_class($model) . '" [' . $model->getId() . '].');
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param integer $id
     * @return GI_Model
     */
    public static function getDeletedModelById($id){
        $modelArray = static::getModelArrayById($id, 0);
        if(isset($modelArray[0])){
            $model = $modelArray[0];
            return $model;
        }
        return NULL;
    }
    
    protected static function buildModelArray($daoArray, $idsAsKey = false){
        $modelArray = array();
        if (empty($daoArray)) {
            return $modelArray;
        }
        foreach ($daoArray as $primaryDAO) {
            $modelSubArray = static::buildModelsWithExistingPrimaryDAO($primaryDAO);
            if (is_null($modelSubArray)) {
                return NULL;
            }
            foreach($modelSubArray as $modelSub) {
                if(!$idsAsKey){
                    $modelArray[] = $modelSub;
                } else {
                    $modelArray[$modelSub->getProperty('id')] = $modelSub;
                }
            }
        }
        return $modelArray;
    }
    
    /**
     * @param integer $status
     * @param integer $pageNumber
     * @param integer $itemsPerPage
     * @return GI_Model[]
     */
    public static function getAll($status = 1, $pageNumber = NULL, $itemsPerPage = NULL) {
        $search = static::search()
                ->filter('status', $status);
        if(!empty($pageNumber)){
            $search->setPageNumber($pageNumber);
        }
        if(!empty($itemsPerPage)){
            $search->setItemsPerPage($itemsPerPage);
        }
        $models = $search->select();
        return $models;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param boolean $idsAsKey
     * @return GI_Model[]
     */
    public static function getByDataSearch(GI_DataSearch $dataSearch, $idsAsKey = false){
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $daoArray = $defaultDAOClass::getByDataSearch($dataSearch);
        $modelArray = static::buildModelArray($daoArray, $idsAsKey);
        return $modelArray;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @return integer
     */
    public static function getCountByDataSearch(GI_DataSearch $dataSearch){
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $count = $defaultDAOClass::getCountByDataSearch($dataSearch);
        return $count;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @return array
     */
    public static function getSumByDataSearch(GI_DataSearch $dataSearch){
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $sums = $defaultDAOClass::getSumByDataSearch($dataSearch);
        return $sums;
    }
    
    /**
     * @param String $typeRef
     * @return GI_Model A new model of type $typeRef
     */
    public static function buildNewModel($typeRef = '') {
        $daoArray = static::buildNewDAOArray($typeRef);
        if(!$daoArray){
            return NULL;
        }
        $map = static::buildMap($daoArray, $typeRef);
        $model = static::buildModelByTypeRef($typeRef, $map);
        return $model;
    }

    protected static function buildNewDAOArray($typeRef) {
        $typeRefs = static::getTypeRefArrayFromTypeRef($typeRef);
        $daoArray = array();
        return static::buildNewSingleDAOForDAOArray(static::$primaryDAOTableName, $daoArray, $typeRefs);
    }

    protected static function buildModelsWithExistingPrimaryDAO(GI_DAO $primaryDAO = NULL) {
        $mapArray = static::buildMapsFromExistingDAOs($primaryDAO);
        if (is_null($mapArray)) {
            return NULL;
        }
        $modelArray = array();
        foreach ($mapArray as $map) {
            $typeRef = $map->getTypeRef();
            $model = static::buildModelByTypeRef($typeRef, $map);
            $modelArray[] = $model;
        }
        return $modelArray;
    }

    protected static function buildMapsFromExistingDAOs(GI_DAO $primaryDAO = NULL) {
        $daoArray = array();
        $bucket = array();
        $result = static::buildExistingDAOArray($primaryDAO, $daoArray, $bucket, NULL);
        if (!is_null($result) && !$result) {
            return NULL;
        }
        $mapArray = array();
        foreach ($bucket as $typeRef => $daoArray) {
            $map = static::buildMap($daoArray, $typeRef);
            $mapArray[] = $map;
        }
        return $mapArray;
    }

    /** @return GI_DataMap */
    protected static function buildMap($daoArray, $typeRef = NULL) {
        $defaultMapEntryClass = static::getStaticPropertyValueFromChild('defaultDataMapEntryClass');
        $dataMapEntrySample = new $defaultMapEntryClass();
        $defaultDataMapClass = static::getStaticPropertyValueFromChild('defaultDataMapClass');
        $dataMap = new $defaultDataMapClass($daoArray, $dataMapEntrySample, $typeRef);
        return $dataMap;
    }
    
    //returns 'direct', 'link_table', or 'none'
    protected static function determineDAOTypeTableLinkType(GI_DAO $dao) {
        $tableName = $dao->getTableName();
        $daoTypeTableIdKey = static::prepareTypeTableIdKey($tableName, true);
        $daoCols = $dao->getCols();
        if (isset($daoCols[$daoTypeTableIdKey])) {
            return 'direct';
        }        
        $typeTableLinkTableName = static::prepareTableLinkToTableName($tableName, static::prepareTypeTableName($tableName));
        if (dbConnection::verifyTableExists($typeTableLinkTableName, static::getDBType())) {
            return 'link_table';
        }
        return 'none';
    }

    protected static function buildExistingDAOArray(GI_DAO $dao = NULL, &$array = array(), &$bucket = array(), $lastTypeRef = NULL) {
        if (is_null($dao)) {
            return;
        }
        $key = sizeof($array);
        $array[$key] = $dao;
        $tableName = $dao->getTableName();
        $daoTypeTableName = static::prepareTypeTableName($tableName);
        $daoTypeTableIdKey = static::prepareTypeTableIdKey($daoTypeTableName);
        $daoCols = $dao->getCols();
        if (isset($daoCols[$daoTypeTableIdKey])) {
            $daoTypeId = $dao->getProperty($daoTypeTableIdKey);
            //CASE 1
            //There is a single type (fk type_id on table)
            $typeDAO = static::getDAOById($daoTypeTableName, $daoTypeId);
            if (empty($typeDAO)) {
                $bucket[] = $array;
                return $bucket;
            }
            $dao->setTypeDAO($typeDAO);
            $typeRef = $typeDAO->getProperty('ref');
            $childTableName = $typeDAO->getProperty('table_name');
            if (is_null($childTableName)) {
                $bucket[$typeRef] = $array; //leaf
                return $bucket;
            } else {
                $parentDaoId = $dao->getProperty('id');
                $childDAO = static::getChildDAO($childTableName, $parentDaoId, true, $typeRef);
                if (!is_null($childDAO)) {
                    static::buildExistingDAOArray($childDAO, $array, $bucket, $typeRef);
                } 
            }
        } else {
            $bucket[$lastTypeRef] = $array;
        }
    }

    protected static function getDAOById($tablename, $id = NULL, $status = 1) {
        if (!is_null($id)) {
            $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
            return $defaultDAOClass::getById($tablename, $id, static::getDBType(), $status);
        }
    }

    protected static function getChildDAO($childTableName, $parentDaoId, $createNew = false, $typeRef = NULL) {
        $childDAO = NULL;
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        if (!$createNew && is_null($parentDaoId)) {
            return $childDAO;
        }
        $childSearch = new GI_DataSearch($childTableName);
        $childDAOs = $childSearch->setDBType(static::getDBType())
                ->setAutoStatus(false)
                ->filter('parent_id', $parentDaoId)
                ->orderBy('status', 'DESC')
                ->select();
        $childDAO = NULL;
        if (!empty($childDAOs)) {
            $childDAO = $childDAOs[0];
        }

        if (is_null($childDAO) && $createNew && !empty($typeRef)) {
            $childDAO = new $defaultDAOClass($childTableName, array(
                'dbType' => static::getDBType()
            ));
            $childDAO->setProperty('parent_id', $parentDaoId);
            $childDAO->setProperty('status', 1);
            $typeLinkType = static::determineDAOTypeTableLinkType($childDAO);
            if ($typeLinkType === 'direct') {
                $typeTableName = static::prepareTypeTableName($childTableName);
                $typeSearch = new GI_DataSearch($typeTableName);
                $typeDAOs = $typeSearch->setDBType(static::getDBType())
                        ->filter('ref', $typeRef)
                        ->select();
                if ($typeDAOs) {
                    $typeDAO = $typeDAOs[0];
                    $typeId = $typeDAO->getProperty('id');
                    $tableTypeIdKey = static::prepareTypeTableIdKey($typeTableName);
                    $childDAO->setProperty($tableTypeIdKey, $typeId);
                    $childDAO->setTypeRef($typeRef);
                }
            }
        }
        return $childDAO;
    }

    protected static function buildNewSingleDAOForDAOArray($tableName, &$daoArray, $typeRefsArray) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $dao = new $defaultDAOClass($tableName, array(
            'dbType' => static::getDBType()
        ));
        if (sizeof($typeRefsArray) <= 0) {
            $daoArray[] = $dao;
            return $daoArray;
        }
        $arrayKeys = array_keys($typeRefsArray);
        $firstKey = $arrayKeys[0];
        $typeRef = $typeRefsArray[$firstKey];
        unset($typeRefsArray[$firstKey]);
        $typeTableName = static::prepareTypeTableName($tableName);
        $typeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, static::getDBType());
        if(!$typeDAO){
            return NULL;
        }
        $dao->setTypeDAO($typeDAO);
        $daoHasTypeId = $dao->getHasTypeIdStatus();
        if (!$daoHasTypeId) {
            $typeLinkTableName = static::prepareTableLinkToTableName($tableName, static::prepareTypeTableName($tableName));
            if (dbConnection::verifyTableExists($tableName, static::getDBType())) {
                $dao->setTypeRef($typeRef);
                $dao->setTypeLinkTableName($typeLinkTableName);
            }
        } else {
            $typeId = $typeDAO->getProperty('id');
            $typeTableNameKey = static::prepareTypeTableIdKey($typeTableName);
            $dao->setProperty($typeTableNameKey, $typeId);
        }
        $daoArray[] = $dao;
        $childTableName = $typeDAO->getProperty('table_name');
        if (is_null($childTableName) || empty($childTableName)) {
            return $daoArray;
        } else {
            return static::buildNewSingleDAOForDAOArray($childTableName, $daoArray, $typeRefsArray);
        }
    }
    
    /**
     * @return GI_DataSearch
     */
    public static function search(){
        $giDataSearch = GI_DataSearch::createFromFactory(get_called_class());
        return $giDataSearch;
    }
    
    /**
     * @param string $childTypeRef
     * @return string
     */
    public static function getChildTableName($childTypeRef){
        $curTableName = static::getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $typeRefs = static::getTypeRefArrayFromTypeRef($childTypeRef);
        if(!empty($typeRefs)){
            foreach($typeRefs as $typeRef){
                $typeTableName = static::prepareTypeTableName($curTableName);
                $typeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, static::getDBType());
                $childTableName = $typeDAO->getProperty('table_name');
                if (!is_null($childTableName) && !empty($childTableName)) {
                    $curTableName = $childTableName;
                }
            }
        } else {
            $curTableName = $childTypeRef;
        }
        return $curTableName;
    }
    
    /**
     * @param string $parentTypeRef
     * @param string $typeRef
     * @param string $typeIdColumn
     * @param string $tableLinkedToType
     * @return integer
     */
    public static function getTypeIdByRef($parentTypeRef, $typeRef, &$typeIdColumn = NULL, &$tableLinkedToType = NULL){
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $parentTable = static::getChildTableName($parentTypeRef);
        $getLinkToConv = static::prepareTableLinkToTableName('', '');
        if (strpos($parentTable, $getLinkToConv) !== false) {
            $tableNameArray = explode($getLinkToConv, $parentTable);
            $tableLinkedToType = $tableNameArray[0];
            $parentTypeTable = $tableNameArray[1];
        } else {
            $parentTypeTable = static::prepareTypeTableName($parentTable);
        }
        $typeIdColumn = static::prepareTypeTableIdKey($parentTypeTable);
        $typeDAO = $defaultDAOClass::getTypeDAOByRef($parentTypeTable, $typeRef, static::getDBType());
        if (!empty($typeDAO)) {
            $typeId = $typeDAO->getProperty('id');
            return $typeId;
        }
        return NULL;
    }

    /**
     * @param string $valueColumn
     * @return string[]
     */
    public static function getOptionsArray($valueColumn = 'title') {
        if (empty(static::$optionsArray)) {
            $daoSearch = static::search();
            $daos = $daoSearch->setSortAscending(true)
                    ->select();
            $returnArray = array();
            if (!empty($daos)) {
                foreach ($daos as $dao) {
                    $daoId = $dao->getProperty('id');
                    $title = $dao->getProperty($valueColumn);
                    $returnArray[$daoId] = $title;
                }
            }
            static::$optionsArray = $returnArray;
        }
        return static::$optionsArray;
    }
    
    /**
     * @param string $rootType The type to serve as the root. Default is actual root type
     * @param boolean $topLevelWithIdAsKey Only available for actual root. Makes the type id the key of the array returned
     * @param string $typeProperty The column to use as the value of the array
     * @param boolean $stopAtRoot whether to continue down any branches included
     * @param boolean $excludeBranches whether to include types that branch off
     * @param string[] $includeBranchRefs specific branches to include (by ref)
     * @return string[] An array of all type 'below' $rootType
     */
    public static function getTypesArray($rootType = NULL, $topLevelWithIdAsKey = false, $typeProperty = 'title', $stopAtRoot = false, $excludeBranches = false, $includeBranchRefs = array()) {
        $typesArray = array();
        if (is_null($rootType)) {
            $primaryTableName = static::$primaryDAOTableName;
            $primaryTypeTableName = $primaryTableName . '_type';
            if (dbConnection::verifyTableExists($primaryTypeTableName, static::getDBType())) {
                static::buildTypesArray($typesArray, $primaryTypeTableName, $topLevelWithIdAsKey, $typeProperty, $stopAtRoot, $excludeBranches, $includeBranchRefs);
            } 
        } else {
            $typeTableName = static::getTypeTableNameFromTypeRef($rootType);
            static::buildTypesArray($typesArray, $typeTableName, $topLevelWithIdAsKey, $typeProperty, $stopAtRoot, $excludeBranches, $includeBranchRefs);
        }
        return $typesArray;
    }
    
    protected static function buildTypesArray(&$array, $typeTableName, $topLevelWithIdAsKey = false, $typeProperty = 'title', $stopAtRoot = false, $excludeBranches = false, $includeBranchRefs = array()) {
        $typeSearch = new GI_DataSearch($typeTableName);
        $typeDAOs = $typeSearch->setDBType(static::getDBType())
                ->setSortAscending(true)
                ->orderBy('pos', 'ASC')
                ->select();
        if (!empty($typeDAOs)) {
            foreach ($typeDAOs as $typeDAO) {
                $title = $typeDAO->getProperty($typeProperty);
                $ref = $typeDAO->getProperty('ref');
                $tableName = $typeDAO->getProperty('table_name');
                if(!empty($tableName) && $excludeBranches && !in_array($ref, $includeBranchRefs)){
                    continue;
                }
                if (!$topLevelWithIdAsKey) {
                    if (!isset($array[$ref])) {
                        $array[$ref] = $title;
                    }
                    if (!empty($tableName) && !$stopAtRoot) {
                        static::buildTypesArray($array, $tableName . '_type', $topLevelWithIdAsKey, $typeProperty, $stopAtRoot);
                    }
                } else {
                    $id = $typeDAO->getProperty('id');
                    $array[$id] = $title;
                }
            }
        }
    }

    /**
     * @param string $typeRef
     * @return string
     */
    public static function getTypeTableNameFromTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $primaryDAOTableName = static::$primaryDAOTableName;
        $primaryTypeTableName = $primaryDAOTableName . '_type';
        $typeTableName = static::findTypeTableNameFromTypeRef($typeRefsArray, $primaryTypeTableName);
        return $typeTableName;
    }
    
    protected static function findTypeTableNameFromTypeRef($typeRefsArray, $typeTableName) {
        if (sizeof($typeRefsArray) == 0) {
            return $typeTableName;
        }
        $firstTypeRef = array_shift($typeRefsArray);
        $typeSearch = new GI_DataSearch($typeTableName);
        $typeDAOs = $typeSearch->setDBType(static::getDBType())
                ->filter('ref', $firstTypeRef)
                ->orderBy('pos', 'ASC')
                ->select();
        if($typeDAOs){
            $typeDAO = $typeDAOs[0];
            $childTableName =  $typeDAO->getProperty('table_name');
            if (!empty($childTableName)) {
                return static::findTypeTableNameFromTypeRef($typeRefsArray, $childTableName . '_type');
            }
        }
        return $typeTableName;
    }
    
    /**
     * @param string $typeRef
     * @param string $typeTableName
     * @return GI_Model
     */
    public static function getTypeModelByRef($typeRef, $typeTableName = NULL) {
        if (empty($typeTableName)) {
            //now accounts for SUB types
            $tables = static::getTablesFromTypeRef($typeRef);
            $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
            $requestedTypeDAO = NULL;
            foreach($tables as $tableName => $tableInfo){
                $typeTableName = static::prepareTypeTableName($tableName);
                $requestedTypeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, static::getDBType());
                if($requestedTypeDAO){
                    break;
                }
            }
            if(empty($requestedTypeDAO)){
                //old method, used as fail safe
                $typeTableName = static::$primaryDAOTableName . '_type';
            }
        }
        $defaultTypeFactoryClass = static::getStaticPropertyValueFromChild('defaultTypeModelFactoryClass');
        return $defaultTypeFactoryClass::getTypeModelByRef($typeRef, $typeTableName, static::getDBType());
    }

    protected static function setFactoryClassName(GI_Model $model) {
        $factoryClassName = get_called_class();
        $model->setFactoryClassName($factoryClassName);
        return $model;
    }

    /**
     * @param string $typeRef
     * @return string The direct parent type ref of $typeRef for the model. An empty string if $typeRef is for root table
     * @deprecated
     */
    public static function getPTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $numberOfRefs = sizeof($typeRefsArray);
        if ($numberOfRefs > 1) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            return $pTypeRef;
        } else {
            $pTypeRef = '';
        }
        return $pTypeRef;
    }
    
    /**
     * @param GI_Model $model
     * @return boolean
     */
    public static function isModelDeleteable(GI_Model $model) {
        $modelTableName = $model->getTableName();
        $modelId = $model->getProperty('id');
        $fkColsArray = TableColumnFactory::search()
                ->filter('fk_subject', $modelTableName . '.id')
                ->select();
        if (!empty($fkColsArray)) {
            foreach ($fkColsArray as $fkCol) {
                $tableId = $fkCol->getProperty('table_id');
                $table = TableFactory::getModelById($tableId);
                if (empty($table)) {
                    return false;
                }
                $tableSystemTitle = $table->getProperty('system_title');
                if (!in_array($tableSystemTitle, static::$deleteFKTableNameExceptions)) {
                    $colName = $fkCol->getProperty('column_name');
                    if ($colName !== 'parent_id') {
                        $existingRowSearch = new GI_DataSearch($tableSystemTitle);
                        $existingRows = $existingRowSearch->setDBType(static::getDBType())
                                ->filter($colName, $modelId)
                                ->select();
                        if (!empty($existingRows)) {
                            return false;
                        }
                    }
                }
            }
        }
        $genericTablesToSearch = array(
            'item_link_to_expense',
            'item_link_to_income',
            'item_link_to_expense_item',
            'item_link_to_income_item',
            'item_link_to_file',
            'item_link_to_folder'
        );
        
        $searchArray = array(
            'table_name' => $modelTableName,
            'item_id' => $modelId
        );

        foreach ($genericTablesToSearch as $tableName) {
            if (!in_array($tableName, static::$deleteFKTableNameExceptions)) {
                $existingGenericSearch = new GI_DataSearch($tableName);
                $existingGenericSearch->setDBType(static::getDBType());
                foreach($searchArray as $column => $value){
                    $existingGenericSearch->filter($column, $value);
                }
                $existingGenericRows = $existingGenericSearch->select();
                if (!empty($existingGenericRows)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function changeModelType(GI_Model $model, $targetTypeRef) {
        $currentTypeRef = $model->getTypeRef();
        if (empty($currentTypeRef) || ($targetTypeRef == $currentTypeRef)) {
            return $model;
        }
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $modelTableName = $model->getTableName();
        $modelId = $model->getProperty('id');
        $primaryDAO = $defaultDAOClass::getById($modelTableName, $modelId, static::getDBType());
        $currentTypeRefsArray = static::getTypeRefArrayFromTypeRef($currentTypeRef);
        $existingDAOArray = array();
        $bucket = array();
        static::buildExistingDAOArray($primaryDAO, $existingDAOArray, $bucket, NULL);
        $targetTypeRefsArray = static::getTypeRefArrayFromTypeRef($targetTypeRef);
        if (empty($targetTypeRefsArray)) {
            return NULL;
        }
        $level = 0;
        $newDAOArray = array();
        $toRemoveDAOArray = array();
        $result = static::buildTypeChangedDAOArray($existingDAOArray, $newDAOArray, $toRemoveDAOArray,$level, $currentTypeRefsArray, $targetTypeRefsArray);
        if ($result) {
            foreach($newDAOArray as $newDAO){
                /*@var $newDAO GI_DAO*/
                $newDAO->setUsedState(true);
            }
            $map = static::buildMap($newDAOArray, $targetTypeRef);
            $updatedModel = static::buildNewModel($targetTypeRef);
            if (empty($updatedModel)) {
                return NULL;
            }
            $updatedModel->setMap($map);
            $updatedModel->setDAOArrayToDeleteOnSave($toRemoveDAOArray);
            return $updatedModel;
        }
        return NULL;
    }

    protected static function buildTypeChangedDAOArray($existingDAOArray, &$newDAOArray, &$toRemoveDAOArray, $level, $currentTypeRefsArray, $targetTypeRefsArray) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $currentLevelTypeRef = $currentTypeRefsArray[$level];
        $targetLevelTypeRef = $targetTypeRefsArray[$level];
        if (!empty($currentLevelTypeRef) && ($currentLevelTypeRef == $targetLevelTypeRef)) {
            $newDAOArray[$level] = $existingDAOArray[$level];
        } else {
            $sameTable = false;
            if ($level == 0) {
                $sameTable = true;
            }
            if (!$sameTable && (isset($existingDAOArray[$level-1]) && !empty($existingDAOArray[$level - 1]))) {
                $existingDAOFromLastLevelTypeDAO = $existingDAOArray[$level - 1]->getTypeDAO();
                $existingLastLevelDAOTableName = $existingDAOArray[$level-1]->getTableName();
                $existingLastLevelDAOTypeTableName = $existingLastLevelDAOTableName . '_type';
                $originalTypeId = $existingDAOArray[$level-1]->getProperty($existingLastLevelDAOTypeTableName . '_id', true);
                if ($existingDAOFromLastLevelTypeDAO->getProperty('id') != $originalTypeId) {
                    $originalDAOTypeDAOSearch = new GI_DataSearch($existingLastLevelDAOTypeTableName);
                    $originalDAOTypeDAOSearch->setDBType(static::$dbType);
                    $originalDAOTypeDAOSearch->filter('id', $originalTypeId);
                    $originalTypeDAOArray = $originalDAOTypeDAOSearch->select();
                    if (empty($originalTypeDAOArray)) {
                        return false;
                    }
                    $existingDAOFromLastLevelTypeDAO = $originalTypeDAOArray[0];
                }
                $newDAOFromLastLevelTypeDAO = $newDAOArray[$level - 1]->getTypeDAO();
                if ($existingDAOFromLastLevelTypeDAO->getProperty('table_name') == $newDAOFromLastLevelTypeDAO->getProperty('table_name')) {
                    $sameTable = true;
                }
            }
            if ($sameTable) {
                $sameDAO = $existingDAOArray[$level];
                $sameDAOTableName = $sameDAO->getTableName();
                $sameDAOTypeTableName = $sameDAOTableName . '_type';
                $sameDAONewTypeDAOSearch = new GI_DataSearch($sameDAOTypeTableName);
                $sameDAONewTypeDAOSearch->setDBType(static::$dbType);
                $sameDAONewTypeDAOSearch->filter('ref', $targetLevelTypeRef);
                $sameDAONewTypeDAOArray = $sameDAONewTypeDAOSearch->select();
                if (empty($sameDAONewTypeDAOArray)) {
                    return false;
                }
                $sameDAONewTypeDAO = $sameDAONewTypeDAOArray[0];
                $sameDAO->setProperty($sameDAOTypeTableName . '_id', $sameDAONewTypeDAO->getProperty('id'));
                $sameDAO->setTypeDAO($sameDAONewTypeDAO);
                $newDAOArray[$level] = $sameDAO;
            } else {
                $lastDAO = $newDAOArray[$level - 1];
                $lastDAOTypeDAO = $lastDAO->getTypeDAO();
                $newDAOTableName = $lastDAOTypeDAO->getProperty('table_name');
                $newDAOTypeTableName = $newDAOTableName . '_type';
                $newDAOTypeDAOSearch = new GI_DataSearch($newDAOTypeTableName);
                $newDAOTypeDAOSearch->setDBType(static::$dbType);
                $newDAOTypeDAOSearch->filter('ref', $targetLevelTypeRef);
                $newDAOTypeDAOArray = $newDAOTypeDAOSearch->select();
                if (empty($newDAOTypeDAOArray)) {
                    return false;
                }
                $newDAOTypeDAO = $newDAOTypeDAOArray[0];
                $newDAOTypeId = $newDAOTypeDAO->getProperty('id');
                $newDAOParentId = $lastDAO->getProperty('id');
                $existingDAOSearch = new GI_DataSearch($newDAOTableName);
                $existingDAOSearch->setDBType(static::$dbType);
                $existingDAOSearch->filter('parent_id', $newDAOParentId);
                $existingLevelDAOArray = $existingDAOSearch->select();
                if (!empty($existingLevelDAOArray)) {
                    $newDAO = $existingLevelDAOArray[0];
                } else {
                    $softDeletedDAOSearch = new GI_DataSearch($newDAOTableName);
                    $softDeletedDAOSearch->setDBType(static::$dbType);
                    $softDeletedDAOSearch->filter('parent_id', $newDAOParentId);
                    $softDeletedDAOSearch->filter('status', 0);
                    $softDeletedDAOArray = $softDeletedDAOSearch->select();
                    if (!empty($softDeletedDAOArray)) {
                        $newDAO = $softDeletedDAOArray[0];
                        $newDAO->setProperty('status', 1);
                    } else {
                        $newDAO = new $defaultDAOClass($newDAOTableName, array(
                            'dbType'=> static::getDBType()
                        ));
                        $newDAO->setProperty('parent_id', $newDAOParentId);
                    }
                }
                $newDAO->setProperty($newDAOTypeTableName . '_id', $newDAOTypeId);
                $newDAO->setTypeDAO($newDAOTypeDAO);
                $newDAOArray[$level] = $newDAO;
                $count = count($existingDAOArray);
                for ($i = $level; $i < $count; $i++) {
                    $toRemoveDAOArray[] = $existingDAOArray[$i];
                }
                $existingDAOArray = array();
            }
        }

        if (!isset($targetTypeRefsArray[$level + 1])) {
            $currentDAO = $newDAOArray[$level];
            $currentDAOTableName = $currentDAO->getTableName();
            $currentDAOTypeTableName = $currentDAOTableName . '_type';
            $currentTypeDAOId = $currentDAO->getProperty($currentDAOTypeTableName . '_id');
            $currentDAOTypeDAO = $defaultDAOClass::getById($currentDAOTypeTableName, $currentTypeDAOId, static::getDBType());
            $nextDAO = NULL;
            if (!empty($currentDAOTypeDAO)) {
                $nextDAOTableName = $currentDAOTypeDAO->getProperty('table_name');
                if (!empty($nextDAOTableName)) {
                    $nextDAOSearch = new GI_DataSearch($nextDAOTableName);
                    $nextDAOSearch->setDBType(static::getDBType());
                    $nextDAOSearch->filter('parent_id', $currentDAO->getProperty('id'));
                    $nextDAOArray = $nextDAOSearch->select();
                    if (!empty($nextDAOArray)) {
                        $nextDAO = $nextDAOArray[0];
                    } else {
                        $nextDAO = NULL;
                        $softDeletedNextDAOSearch = new GI_DataSearch($nextDAOTableName);
                        $softDeletedNextDAOSearch->setDBType(static::getDBType());
                        $softDeletedNextDAOSearch->filter('parent_id', $currentDAO->getProperty('id'));
                        $softDeletedNextDAOSearch->filter('status', 0);
                        $softDeletedNextDAOArray = $softDeletedNextDAOSearch->select();
                        if (!empty($softDeletedNextDAOArray)) {
                            $softDeletedNextDAO = $softDeletedNextDAOArray[0];
                            $softDeletedNextDAO->setProperty('status', 1);
//                            if ($softDeletedNextDAO->save()) {
//                                $nextDAO = $softDeletedNextDAO;
//                            }
                        }
                        if (empty($nextDAO)) {
                            $nextDAO = new $defaultDAOClass($nextDAOTableName, array(
                                'dbType' => static::getDBType()
                            ));
                            $nextDAO->setProperty('parent_id', $currentDAO->getProperty('id'));
//                            if (!$nextDAO->save()) {
//                                return false;
//                            }
                        }
                    }
                    $newDAOArray[$level + 1] = $nextDAO;
                }
            }
            $nextDAOId = NULL;
            if (!empty($nextDAO)) {
                $nextDAOId = $nextDAO->getProperty('id');
            }
            $existingDAOArrayCount = count($existingDAOArray);
            for ($i = $level+1; $i<$existingDAOArrayCount;$i++) {
                $existingDAO = $existingDAOArray[$i];
                if (!empty($nextDAOId) && $nextDAOId == $existingDAO->getProperty('id')) {
                    continue;
                }
                $toRemoveDAOArray[] = $existingDAO;
            }
            return true;
        } else {
            $level++;
            return static::buildTypeChangedDAOArray($existingDAOArray, $newDAOArray, $toRemoveDAOArray, $level, $currentTypeRefsArray, $targetTypeRefsArray);
        }
    }
    
    public static function getTablesFromTypeRef($typeRef, $includeAdditionalInfo = true){
        $typeRefs = static::getTypeRefArrayFromTypeRef($typeRef);
        $tables = static::getTablesFromTypeRefArray($typeRefs, array(), $includeAdditionalInfo);
        return $tables;
    }
    
    public static function getTablesFromTypeRefArray($typeRefs, $tables = array(), $includeAdditionalInfo = true){
        if(empty($tables)){
            $tableName = static::$primaryDAOTableName;
            if($includeAdditionalInfo){
                $tables[$tableName] = array(
                    'tableName' => $tableName,
                    'typeDAO' => NULL
                );
            } else {
                $tables[] = $tableName;
            }
        }
        
        if (empty($typeRefs)) {
            return $tables;
        }
        
        $arrayKeys = array_keys($typeRefs);
        $firstKey = $arrayKeys[0];
        $typeRef = $typeRefs[$firstKey];
        
        unset($typeRefs[$firstKey]);
        if($includeAdditionalInfo){
            $tableInfo = end($tables);
            $tableName = $tableInfo['tableName'];
        } else {
            $tableName = end($tables);
        }
        
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        
        $typeTableName = static::prepareTypeTableName($tableName);
        $typeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, static::getDBType());
        
        $childTableName = $typeDAO->getProperty('table_name');
        if (is_null($childTableName) || empty($childTableName)) {
            return $tables;
        } else {
            if($includeAdditionalInfo){
                $tables[$childTableName] = array(
                    'tableName' => $childTableName,
                    'typeDAO' => $typeDAO
                );
            } else {
                $tables[] = $childTableName;
            }
            return static::getTablesFromTypeRefArray($typeRefs, $tables);
        }
    }
    
    /**
     * @param string||array $ids
     * @return GI_Model[]
     */
    public static function getByIds($ids){
        if(empty($ids)){
            return array();
        }
        $search = static::search();
        $search->filterIn('id', $ids);
        $models = $search->select();
        return $models;
    }
    
}
