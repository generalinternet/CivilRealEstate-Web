<?php

/**
 * Description of GI_DataSearch
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.12
 */
class GI_DataSearch extends GI_DataSearchFilterable{
    
    protected $dbType = 'client';
    protected $pageNumber = 1;
    protected $itemsPerPage = 0;
    protected $limit = 0;
    protected $offset = NULL;
    protected $offsetRowCount = 0;
    protected $tableName = '';
    protected $as = NULL;
    protected $factoryClass = NULL;
    protected $orderBys = array();
    protected $finalOrderBys = array();
    /** @var GI_DataSearchJoin[] */
    protected $joins = array();
    protected $groupBys = array();
    /** @todo make having use filterables instead of just string setting */
    protected $havingString = NULL;
    protected $searchString = '';
    protected $countString = '';
    protected $count = 0;
    protected $sumString = '';
    protected $autoStatus = true;
    protected $statusFilters = array();
    protected $autoFranchise = true;
    protected $franchiseFilters = array();
    protected $queryId = NULL;
    protected $lastQuery = '';
    protected $escapeStrings = true;
    /**
     * An array of statuses for all tables that have been checked for the franchise_id column
     * @var array [tableName] => boolean
     */
    protected static $isFranchisedStatus = array();
    protected $autoSortDirection = 'DESC';
    
    public function __construct($tableName = NULL) {
        if($tableName){
            $this->setTableName($tableName);
        }
        $this->filterGroup();
    }
    
    public function getDataSearch() {
        return $this;
    }
    
    public function getSearchString($forDisplay = false){
        $string = $this->searchString;
        if($forDisplay){
            return GI_StringUtils::formatQuery($string);
        }
        return $string;
    }
    
    public function getCountString($forDisplay = false){
        $string = $this->countString;
        if($forDisplay){
            return GI_StringUtils::formatQuery($string);
        }
        return $string;
    }
    
    public function getCount(){
        if(!is_null($this->offset)){
            return $this->count - $this->offset;
        }
        if(!empty($this->offsetRowCount)){
            return $this->count + $this->offsetRowCount;
        }
        return $this->count;
    }
    
    public function getSumString($forDisplay = false){
        $string = $this->sumString;
        if($forDisplay){
            return GI_StringUtils::formatQuery($string);
        }
        return $string;
    }
    
    public function getQueryId(){
        if(is_null($this->queryId)){
            $this->queryId = static::genUniqueQueryId();
        }
        return $this->queryId;
    }
    
    public function setQueryId($queryId){
        $this->queryId = $queryId;
        return $this;
    }
    
    protected function makeSessionQuery(){
        $queryId = $this->getQueryId();
        if(!isset($_SESSION['queryIds'][$queryId]['searchValues'])){
            $_SESSION['queryIds'][$queryId]['searchValues'] = array(
                'queryId' => $queryId
            );
        }
        return $this;
    }
    
    public function setSearchValue($key, $value){
        $queryId = $this->getQueryId();
        $this->makeSessionQuery();
        $_SESSION['queryIds'][$queryId]['searchValues'][$key] = $value;
        return $this;
    }
    
    public function getSearchValue($key){
        $queryId = $this->getQueryId();
        if(isset($_SESSION['queryIds'][$queryId]['searchValues'][$key])){
            return $_SESSION['queryIds'][$queryId]['searchValues'][$key];
        }
        return NULL;
    }
    
    public function getSearchValues(){
        $queryId = $this->getQueryId();
        if(isset($_SESSION['queryIds'][$queryId]['searchValues'])){
            return $_SESSION['queryIds'][$queryId]['searchValues'];
        }
        return array();
    }
    
    public function clearSearchValues(){
        $queryId = $this->getQueryId();
        if(isset($_SESSION['queryIds'][$queryId]['searchValues'])){
            unset($_SESSION['queryIds'][$queryId]['searchValues']);
        }
    }
    
    public static function genUniqueQueryId($attemptCount = 0){
        $newQueryId = 'gi_' . mt_rand(5000, 100000);
        $maxAttempts = 10;
        if(isset($_SESSION['queryIds'][$newQueryId])){
            if($attemptCount < $maxAttempts){
                $attemptCount++;
                $newQueryId = static::genUniqueQueryId($attemptCount);
            } else {
                unset($_SESSION['queryIds']);
                $_SESSION['queryIds'] = array();
            }
        }
        $_SESSION['queryIds'][$newQueryId] = array();
        return $newQueryId;
    }
    
    /**
     * Sets the primary table for the final search
     * 
     * @param string $tableName a table in the database
     * @return \GI_DataSearch
     */
    public function setTableName($tableName){
        $this->tableName = $tableName;
        return $this;
    }
    
    public function setAs($as){
        $this->as = $as;
        return $this;
    }
    
    /**
     * Returns the primary table name
     * 
     * @return string
     */
    public function getTableName(){
        return $this->tableName;
    }
    
    /**
     * Appends a prefix on the provided table name
     * 
     * @param string $tableName table name to prefix
     * @return string
     */
    public function prefixTableName($tableName){
        $prefixedTableName = dbConfig::getDbPrefix($this->getDBType()).$tableName;
        return $prefixedTableName;
    }
    
    /**
     * Sets the factory class name to be used to execute the search
     * 
     * @param string $factoryClass the name of the GI_ModelFactory class
     * @return \GI_DataSearch
     */
    public function setFactoryClass($factoryClass){
        $this->factoryClass = $factoryClass;
        return $this;
    }
    
    /**
     * Sets which database we're going to be accessing
     * 
     * @param string $dbType default "client"
     * @return \GI_DataSearch
     */
    public function setDBType($dbType){
        $this->dbType = $dbType;
        return $this;
    }
    
    /**
     * Returns the currently set database type
     * 
     * @return string
     */
    public function getDBType(){
        return $this->dbType;
    }
    
    /**
     * Sets the current "page" for the search
     * 
     * @param integer $pageNumber the current page
     * @return \GI_DataSearch
     */
    public function setPageNumber($pageNumber){        
        if($pageNumber < 1){
            $pageNumber = 1;
        }
        $this->pageNumber = (int) $pageNumber;
        return $this;
    }
    
    /**
     * Returns the currently set page number
     * 
     * @return integer
     */
    public function getPageNumber(){
        return $this->pageNumber;
    }
    
    /**
     * Sets the "limit" per page
     * 
     * @param integer $itemsPerPage how many items per page to search for
     * @return \GI_DataSearch
     */
    public function setItemsPerPage($itemsPerPage){
        $this->itemsPerPage = (int) $itemsPerPage;
        $this->limit = $this->itemsPerPage;
        return $this;
    }
    
    /**
     * Returns the currently set "limit" per page
     * 
     * @return integer
     */
    public function getItemsPerPage(){
        return $this->itemsPerPage;
    }
    
    /**
     * Returns the currently set mysql "limit"
     * 
     * @return integer
     */
    public function getLimit(){
        return $this->limit;
    }
    
    public function newCase(){
        $case = new GI_DataSearchCase($this);
        return $case;
    }
    
    /**
     * @return \GI_DataSearch
     */
    public function filter($column, $value = NULL, $comp = '=', $andValue = NULL){
        $this->checkIfColumnIsAutoFiltered($column);
        return parent::filter($column, $value, $comp, $andValue);
    }
    
    protected function checkIfColumnIsAutoFiltered($column){
        if (strpos($column, '.') !== false) {
            $columnTable = static::getTableNameFromColumn($column);
            $columnName = static::getColumnNameFromColumn($column);
        } else {
            $columnTable = $this->getTableName();
            $columnName = $column;
        }
        if(strtolower($columnName) === 'status'){
            $this->ignoreStatus($columnTable);
        }
        if(strtolower($columnName) === 'franchise_id'){
            $this->ignoreFranchise($columnTable);
        }
    }
    
    /**
     * @param type $tableName
     * @return \GI_DataSearch
     */
    public function ignoreStatus($tableName){
        $this->statusFilters[] = $tableName;
        return $this;
    }
    
    /**
     * Sets whether or not to add status filters automatically
     * 
     * @param boolean $autoStatus
     * @return \GI_DataSearch
     */
    public function setAutoStatus($autoStatus){
        $this->autoStatus = $autoStatus;
        return $this;
    }
    
    protected function addStatusFilters(){
        if($this->autoStatus){
            $this->andIf();

            $this->curGroup = $this->mainGroup;

            if(!in_array($this->getTableName(), $this->statusFilters)){
                $this->filter('status', 1);
            }

            foreach($this->joins as $joinTable => $join){
                if (!in_array($joinTable, $this->statusFilters)) {
                    $this->filter($joinTable . '.status', 1);
                }
            }
        }
    }
    
    /**
     * @param type $tableName
     * @return \GI_DataSearch
     */
    public function ignoreFranchise($tableName){
        $this->franchiseFilters[] = $tableName;
        return $this;
    }
    
    /**
     * Sets whether or not to add franchise filters automatically
     * 
     * @param boolean $autoFranchise
     * @return \GI_DataSearch
     */
    public function setAutoFranchise($autoFranchise){
        $this->autoFranchise = $autoFranchise;
        return $this;
    }
    
    protected function addFranchiseFilters(){
        if(ProjectConfig::getIsFranchisedSystem() && $this->autoFranchise){
            $franchisedTables = array();
            $dbType = $this->getDBType();
            $tableName = $this->getTableName();
            if(!in_array($this->getTableName(), $this->franchiseFilters) && static::isTableFranchised($tableName, $dbType)){
                $franchisedTables[] = $tableName;
            }
            
            foreach($this->joins as $joinTable => $join){
                if (!in_array($joinTable, $this->franchiseFilters)) {
                    $joinTableName = $join->getJoinTable(true, false);
                    if(static::isTableFranchised($joinTableName, $dbType)){
                        $franchisedTables[] = $joinTable;
                    }
                }
            }
            
            if(empty($franchisedTables)){
                //if no tables have a franchise id kill it
                return true;
            }
            $this->andIf();
            $this->curGroup = $this->mainGroup;

            $curFranchise = Login::getCurrentFranchise();
            $franchiseId = NULL;
            if($curFranchise){
                $franchiseId = $curFranchise->getId();
            } elseif(Permission::verifyByRef('franchise_head_office')) {
                //no current franchise set, but head office so no filter needed
                return true;
            }
            
            foreach($franchisedTables as $franchisedTable){
                $franchiseFullColumnName = $franchisedTable . '.franchise_id';
                if($franchisedTable == $tableName){
                    $franchiseFullColumnName = 'franchise_id';
                }
                if(!empty($franchiseId)){
                    $this->filterNullOr($franchiseFullColumnName, $franchiseId);
                } else {
                    $this->filterNull($franchiseFullColumnName);
                }
            }
        }
    }
    
    public static function isTableFranchised($tableName, $dbType = 'client'){
        if(!isset(static::$isFranchisedStatus[$tableName])){
            $isFranchised = dbConnection::verifyColumnExists($tableName, 'franchise_id', $dbType);
            static::$isFranchisedStatus[$tableName] = $isFranchised;
        }
        return static::$isFranchisedStatus[$tableName];
    }
    
    /** 
     * @param boolean $sortAsc
     * @return \GI_DataSearch
     */
    public function setSortAscending($sortAsc){
        if($sortAsc){
            $this->autoSortDirection = 'ASC';
        } else {
            $this->autoSortDirection = 'DESC';
        }
        return $this;
    }
    
    /** 
     * @param boolean $sortDesc
     * @return \GI_DataSearch
     */
    public function setSortDescending($sortDesc){
        if($sortDesc){
            $this->autoSortDirection = 'DESC';
        } else {
            $this->autoSortDirection = 'ASC';
        }
        return $this;
    }
    
    protected function addAutoFilters(){
        $this->addFranchiseFilters();
        $this->addStatusFilters();
        $this->orderBy('id', $this->autoSortDirection, true);
    }

    /**
     * @param string $parentTypeRef
     * @param string $typeRef
     * @param string $comp
     * @return \GI_DataSearch
     * @deprecated since version 2.0.1 use instead filterByTypeRef()
     */
    public function filterTypeByRef($parentTypeRef, $typeRef = '', $comp = '='){
        $general = true;
        if($parentTypeRef == $typeRef){
            $general = false;
        }
        if(empty($typeRef)){
            $typeRef = $parentTypeRef;
            $general = true;
        }
        return $this->filterByTypeRef($typeRef, $general, 'inner', $comp);
        /*
        if($this->factoryCreated()){
            $factoryClass = $this->factoryClass;
            $typeIdColumn = NULL;
            $tableLinkedToType = NULL;
            $typeId = $factoryClass::getTypeIdByRef($parentTypeRef, $typeRef, $typeIdColumn, $tableLinkedToType);
            if(!is_null($tableLinkedToType)){
                $withTable = $this->prefixTableName($this->getTableName());
                $this->join($parentTypeRef, $tableLinkedToType.'_id', $withTable, 'id', $parentTypeRef);
            }
            $this->filter($parentTypeRef.'.'.$typeIdColumn, $typeId, $comp);
        }
        return $this;
         */        
    }
    
    /**
     * @param string $typeRef
     * @param boolean $general
     * @return \GI_DataSearch
     */
    public function filterByTypeRef($typeRef, $general = true, $joinType = 'inner', $comp = '='){
        if($this->factoryCreated()){
            /* @var $factoryClass GI_ModelFactory */
            $factoryClass = $this->factoryClass;
            $defaultDAOClass = $factoryClass::getStaticPropertyValueFromChild('defaultDAOClass');
            
            $tables = $factoryClass::getTablesFromTypeRef($typeRef);
            $joinToTable = $this->prefixTableName($this->getTableName());
            $tableAlias = NULL;
            $typeColumnName = NULL;
            foreach($tables as $tableName => $tableInfo){
                $typeTableName = $factoryClass::prepareTypeTableName($tableName);
                $typeColumnName = $factoryClass::prepareTypeTableIdKey($typeTableName);
                $requestedTypeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, $factoryClass::getDBType());
                if($tableName != $this->getTableName()){
                    $typeDAO = $tableInfo['typeDAO'];
                    $tableAlias = $typeDAO->getProperty('ref');
                    $this->join($tableName, 'parent_id', $joinToTable, 'id', $tableAlias, $joinType);
                    $joinToTable = $tableAlias;
                    $typeColumnName = '`' . $tableAlias . '`.' . $typeColumnName;
                } elseif($general && $requestedTypeDAO){
                    $baseTypeSearch = new static($typeTableName);
                    $baseTypes = $baseTypeSearch->orderBy('id', 'ASC')
                            ->setDBType($factoryClass::getDBType())
                            ->setItemsPerPage(1)
                            ->select();
                    if($baseTypes){
                        $baseType = $baseTypes[0];
                        if($baseType->getProperty('ref') == $typeRef){
                            $requestedTypeDAO = NULL;
                            break;
                        }
                    }
                }
                
                if($general && $requestedTypeDAO){
                    break;
                }
            }
            
            if($requestedTypeDAO){
                $this->filter($typeColumnName, $requestedTypeDAO->getProperty('id'), $comp);
            }
        }
        return $this;
    }
    
    public function orderBy($column, $direction = 'ASC', $finalSorting = false){
        $this->autoJoinWithColumn($column);
        //@todo create GI_DataSearchOrderBy
        $orderByString = $this->prepareColumnName($column) . ' ' . $direction;
        if($finalSorting){
            $this->finalOrderBys[] = $orderByString;
        } else {
            $this->orderBys[] = $orderByString;
        }
        return $this;
    }
    
    public function clearOrderBys(){
        $this->orderBys = array();
        return $this;
    }
    
    public function clearFinalOrderBys(){
        $this->finalOrderBys = array();
        return $this;
    }
    
    public function orderByCase($case, $direction = 'ASC', $finalSorting = false){
        $orderByCase = new GI_DataSearchOrderByCase($this);
        if(is_array($case)){
            $orderByCase->addCases($case);
        } else {
            $orderByCase->addCase($case);
        }
        $orderByString = $orderByCase->buildCaseString() . ' ' . $direction;
        if($finalSorting){
            $this->finalOrderBys[] = $orderByString;
        } else {
            $this->orderBys[] = $orderByString;
        }
        return $this;
    }
    
    public function orderByLikeScore($columns, $terms, $direction = 'DESC', $finalSorting = false){
        if(!is_array($columns)){
            $columns = explode(',',$columns);
        }
        foreach($columns as $column){
            $this->autoJoinWithColumn($column);
        }
        $likeScoreString = '';
        if(is_array($terms)){
            foreach($terms as $term){
                foreach($columns as $column){
                    $likeScoreString .= $this->buildLikeScoreString($column, $term);
                    $likeScoreString .= ' + ';
                }
            }
        } else {
            $term = $terms;
            foreach($columns as $column){
                $likeScoreString .= $this->buildLikeScoreString($column, $term);
                $likeScoreString .= ' + ';
            }
        }
        $trimmedLikeScoreString = substr($likeScoreString, 0, -3);
        
        $orderByString = $trimmedLikeScoreString . ' ' . $direction;
        if($finalSorting){
            $this->finalOrderBys[] = $orderByString;
        } else {
            $this->orderBys[] = $orderByString;
        }
        return $this;
    }
    
    protected function buildLikeScoreString($column, $term){
        $preparedTerm = $this->escapeString($term);
        
        $likeScoreString = '(CASE WHEN '.$this->prepareColumnName($column).' LIKE "' . $preparedTerm . '%" THEN 1 ELSE 0 END) + (CASE WHEN '.$this->prepareColumnName($column).' LIKE "' . $preparedTerm . '" THEN 3 ELSE 0 END)';
        
        return $likeScoreString;
    }
    
    public function groupBy($column){
        $this->autoJoinWithColumn($column);
        $this->groupBys[] = $this->prepareColumnName($column);
        return $this;
    }
    
    public function clearGroupBys(){
        $this->groupBys = array();
        return $this;
    }
    
    /**
     * Checks if there is currently a join with the given alias
     * 
     * @param string $alias
     * @return boolean
     */
    public function isJoinedWithTable($alias){
        $joinTableClean = GI_StringUtils::removeTicks($alias);
        if(isset($this->joins[$joinTableClean]) || isset($this->joins[strtolower($joinTableClean)]) || isset($this->joins[strtoupper($joinTableClean)])){
            return true;
        }
        return false;
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @param string $type
     * @return \GI_DataSearchJoin
     */
    public function createJoin($joinTable, $on, $withTable, $withColumn, $as = NULL, $type = 'inner'){
        $join = new GI_DataSearchJoin($this, $joinTable, $as, $type);
        $join->setOn($on)
                ->setWithTable($withTable)
                ->setWithColumn($withColumn);
        $joinTable = $join->getJoinTable();
        $joinTableClean = GI_StringUtils::removeTicks($joinTable);
        $this->joins[$joinTableClean] = $join;
        return $join;
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearchJoin
     */
    public function createInnerJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        return $this->createJoin($joinTable, $on, $withTable, $withColumn, $as);
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearchJoin
     */
    public function createLeftJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        return $this->createJoin($joinTable, $on, $withTable, $withColumn, $as, 'left');
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearchJoin
     */
    public function createRightJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        return $this->createJoin($joinTable, $on, $withTable, $withColumn, $as, 'right');
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @param string $type
     * @return \GI_DataSearch
     */
    public function join($joinTable, $on, $withTable, $withColumn, $as = NULL, $type = 'inner'){
        $this->createJoin($joinTable, $on, $withTable, $withColumn, $as, $type);
        return $this;
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearch
     */
    public function innerJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        $this->createInnerJoin($joinTable, $on, $withTable, $withColumn, $as);
        return $this;
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearch
     */
    public function leftJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        $this->createLeftJoin($joinTable, $on, $withTable, $withColumn, $as);
        return $this;
    }
    
    /**
     * @param string $joinTable
     * @param string $on
     * @param string $withTable
     * @param string $withColumn
     * @param string $as
     * @return \GI_DataSearch
     */
    public function rightJoin($joinTable, $on, $withTable, $withColumn, $as = NULL){
        $this->createRightJoin($joinTable, $on, $withTable, $withColumn, $as);
        return $this;
    }
    
    /**
     * Sets whether or not to escape strings
     * 
     * @param boolean $escapeStrings
     * @return \GI_DataSearch
     */
    public function setEscapeStrings($escapeStrings){
        $this->escapeStrings = $escapeStrings;
        return $this;
    }
    
    /**
     * @param string $string
     * @return string
     */
    public function escapeString($string){
        $cleanString = $string;
        if($this->escapeStrings){
            $dbConnect = dbConnection::getInstance($this->getDBType());
            $cleanString = $dbConnect->real_escape_string($string);
        }
        return $cleanString;
    }
    
    public function prepareColumnName($column){
        $prefixedTableName = $this->prefixTableName($this->getTableName());
        $leaveRaw = false;
        if(strpos($column, '(') !== false){
            $leaveRaw = true;
        }
        if (!$leaveRaw && strpos($column, '.') !== false) {
            $tableName = static::getTableNameFromColumn($column);
            $columnName = static::getColumnNameFromColumn($column);
            if($tableName == $prefixedTableName){
                $columnName = $column;
                $leaveRaw = true;
            } elseif(!is_null($this->as) && $tableName === $this->as){                
                $tableName = $prefixedTableName;
            }
            
        } else {
            $tableName = $prefixedTableName;
            $columnName = $column;
        }
        
        $cleanColumnName = $this->escapeString($columnName);
        
        if($leaveRaw || !count($this->joins)){
            return $cleanColumnName;
        } else {
            return '`' . $tableName . '`.' . $cleanColumnName;
        }
    }

    public function prepareValue($value, $column) {
        if (strpos($column, '.') !== false) {
            $tableAlias = static::getTableNameFromColumn($column);
            $columnName = static::getColumnNameFromColumn($column);
            if (isset($this->joins[$tableAlias])) {
                $tableJoin = $this->joins[$tableAlias];
                $tableName = $tableJoin->getJoinTable(true, false);
            } else {
                $tableName = $tableAlias;
            }
        } else {
            $tableName = $this->getTableName();
            $columnName = $column;
        }

        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        if ($this->factoryCreated()) {
            $factoryClass = $this->factoryClass;
            $defaultDAOClass = $factoryClass::getStaticPropertyValueFromChild('defaultDAOClass');
        }
        $dbType = $this->getDBType();
        /* @var $tableDAO GI_DAO */
        $tableDAO = new $defaultDAOClass($tableName, $dbType);
        $preparedValue = $tableDAO->prepareValue($value, $columnName);
        return $preparedValue;
    }
    
    protected function setLastQuery($query, $echoQuery = false){
        $this->lastQuery = $query;
        if($echoQuery){
            $this->echoLastQuery();
        }
        return $this;
    }
    
    public function echoLastQuery(){
        if(!empty($this->lastQuery)){
            echo GI_StringUtils::formatQuery($this->lastQuery);
            echo '<br/>';
        } else {
            echo '<i>No query found</i><br/>';
        }
        return $this;
    }
    
    public function getLastQuery(){
        if(!empty($this->lastQuery)){
            return $this->lastQuery;
        }
        return 'NO QUERY';
    }
    
    public function select($idsAsKey = false, $echoQuery = false){
        $this->addAutoFilters();
        
        $selectString = $this->getSelectString();
        $this->searchString = $selectString;
        
        $this->setLastQuery($selectString, $echoQuery);
        
        $this->count();
        
        if($this->factoryCreated()){
            $factoryClass = $this->factoryClass;
            return $factoryClass::getByDataSearch($this, $idsAsKey);
        } else {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $daoArray = $defaultDAOClass::getByDataSearch($this);
            return $daoArray;
        }
    }
    
    public function getSelectString(){
        $prefixedTableName = $this->prefixTableName($this->getTableName());
        $selectString = 'SELECT `' . $prefixedTableName . '`.* FROM `' . $prefixedTableName . '` ';
        $selectString .= $this->buildJoinString();
        $selectString .= $this->buildWhereString();
        $selectString .= $this->buildGroupByString();
        $selectString .= $this->buildHavingString();
        $selectString .= $this->buildOrderByString();
        $selectString .= $this->buildLimitString();
        return $selectString;
    }
    
    public function count(){
        $this->addAutoFilters();
        
        $prefixedTableName = $this->prefixTableName($this->getTableName());
        $countString = 'SELECT COUNT(`' . $prefixedTableName . '`.id) AS row_count FROM `' . $prefixedTableName . '` ';
        $countString .= $this->buildJoinString();
        $countString .= $this->buildWhereString();
        $countString .= $this->buildGroupByString();
        $countString .= $this->buildHavingString();
        
        if(!empty($this->groupBys)){
            $countString = 'SELECT COUNT(*) AS row_count FROM (' . $countString . ') AS A';
        }
        
        $this->countString = $countString;
        
        $this->setLastQuery($countString);
        
        if($this->factoryCreated()){
            $factoryClass = $this->factoryClass;
            $this->count = $factoryClass::getCountByDataSearch($this);
            return $this->getCount();
        } else {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $count = $defaultDAOClass::getCountByDataSearch($this);
            return $count;
        }
    }
    
    public function sum($columns = array()){
        if(!is_array($columns)){
            $columns = explode(',', $columns);
        }
        $this->addAutoFilters();
        
        $prefixedTableName = $this->prefixTableName($this->getTableName());
        $sumColumnString = '';
        foreach($columns as $sumAs => $column){
            if(!empty($sumColumnString)){
                $sumColumnString .= ', ';
            }
            if(is_numeric($sumAs)){
                $sumAs = $column;
            }
            
            $sumColumn = $this->prepareColumnName($column);
            $sumColumnString .= 'SUM(' . $sumColumn . ') AS ' . $sumAs;
        }
        
        $sumString = 'SELECT ' . $sumColumnString . ' FROM `' . $prefixedTableName . '` ';
        $sumString .= $this->buildJoinString();
        $sumString .= $this->buildWhereString();
        $sumString .= $this->buildGroupByString();
        $sumString .= $this->buildHavingString();
        $this->sumString = $sumString;
        
        $this->setLastQuery($sumString);
        
        if($this->factoryCreated()){
            $factoryClass = $this->factoryClass;
            return $factoryClass::getSumByDataSearch($this);
        } else {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $sums = $defaultDAOClass::getSumByDataSearch($this);
            return $sums;
        }
    }
    
    protected function buildJoinString(){
        $joinString = '';
        foreach ($this->joins as $join){
            $joinString .= $join->buildJoinString();
        }
        return $joinString;
    }
    
    protected function buildWhereString(){
        $whereString = 'WHERE ';
        $whereString .= $this->mainGroup->buildGroupString();
        return $whereString;
    }
    
    //@todo this is just a temp solution to add HAVING to querys
    public function having($having){
        if(!empty($this->havingString)){
            $this->havingString .= ' ' . $this->getConnector() . ' ';
        }
        $this->havingString .= $having;
        return $this;
    }
    
    protected function buildHavingString(){
        if(!empty($this->havingString)){
            $havingString = 'HAVING ' . $this->havingString . ' ';
        } else {
            $havingString = '';
        }
        return $havingString;
    }
    
    protected function buildGroupByString(){
        if(!empty($this->groupBys)){
            $groupByString = 'GROUP BY '.implode(', ', $this->groupBys).' ';
        } else {
            $groupByString = '';
        }
        return $groupByString;
    }
    
    protected function buildOrderByString(){
        $orderByString = '';
        if(!empty($this->orderBys)){
            $orderByString = 'ORDER BY ' . implode(', ', $this->orderBys) . ' ';
        }
        if(!empty($this->finalOrderBys)){
            if(empty($orderByString)){
                $orderByString = 'ORDER BY ';
            } else {
                $orderByString .= ', ';
            }
            $orderByString .= implode(', ', $this->finalOrderBys) . ' ';
        }
        return $orderByString;
    }
    
    public function setOffset($offset){
        $this->offset = $offset;
        return $this;
    }
    
    public function setOffsetRowCount($offsetRowCount){
        $this->offsetRowCount = $offsetRowCount;
        return $this;
    }
    
    public function getOffset(){
        $pageNumber = $this->getPageNumber();
        $itemsPerPage = $this->getItemsPerPage();
        $defaultOffset = ($pageNumber - 1) * $itemsPerPage;
        if(!is_null($this->offset)){
            $offset = $this->offset + $defaultOffset;
        } elseif(!empty($this->offsetRowCount)){
            $offset = $defaultOffset - $this->offsetRowCount;
            if($offset < 0){
                $adjustLimit = $offset;
                $offset = 0;
                $this->limit = $itemsPerPage + $adjustLimit;
            }
        } else {
            $offset = $defaultOffset;
        }
        return $offset;
    }
    
    protected function buildLimitString(){
        $limitString = '';
        $offset = $this->getOffset();
        $itemsPerPage = $this->getItemsPerPage();
        if(!empty($itemsPerPage)){
            $limit = $this->getLimit();
            $limitString = 'LIMIT ' . $offset . ',' . $limit.' ';
        }
        return $limitString;
    }
    
    public function getPageBar($linkArray, $pageLinks = 3){
        if(!isset($linkArray['queryId'])){
            $linkArray['queryId'] = $this->getQueryId();
        }
        $pageBar = new PageBarView($linkArray, $this->getItemsPerPage(), $this->getCount(), $this->getPageNumber(), $pageLinks);
        return $pageBar;
    }
    
    public static function createFromFactory($factoryClass){
        $giDataSearch = new self();
        $giDataSearch->setTableName($factoryClass::getTableName());
        $giDataSearch->setDBType($factoryClass::getDBType());
        $giDataSearch->setFactoryClass($factoryClass);
        return $giDataSearch;
    }
    
    public function factoryCreated(){
        $factoryClass = $this->factoryClass;
        if(!is_null($factoryClass) && is_subclass_of($factoryClass, 'GI_ModelFactory')){
            return true;
        } else {
            return false;
        }
    }
    
    public function autoJoin($autoJoinTable){
        if(!isset($this->joins[$autoJoinTable])){
            if($this->factoryCreated()){
                $factoryClass = $this->factoryClass;
                $thisTable = $this->getTableName();
                $withTable = $this->prefixTableName($thisTable);
                $autoJoinTable = GI_StringUtils::removeTicks($autoJoinTable);
                if($autoJoinTable == $withTable){
                    return NULL;
                }
                /*
                 * THIS IS THE OLD WAY
                $joinTable = $factoryClass::getChildTableName($autoJoinTable);
                
                if($joinTable !== $thisTable){
                    $this->join($joinTable, 'parent_id', $withTable, 'id', $autoJoinTable, 'left');
                } else {
                    $this->setAs($autoJoinTable);
                }
                */
                $curTableName = $factoryClass::getTableName();
                $defaultDAOClass = $factoryClass::getStaticPropertyValueFromChild('defaultDAOClass');
                $typeRefs = $factoryClass::getTypeRefArray($autoJoinTable);
                if(!empty($typeRefs)){
                    foreach($typeRefs as $typeRef){
                        $typeTableName = $factoryClass::prepareTypeTableName($curTableName);
                        $typeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, $this->getDBType());
                        $childTableName = $typeDAO->getProperty('table_name');
                        if (!is_null($childTableName) && !empty($childTableName)) {
                            $curTableName = $childTableName;
                            $this->join($childTableName, 'parent_id', $withTable, 'id', $typeRef, 'left');
                            $withTable = $typeRef;
                        }
                    }
                    $checkTable = $this->prefixTableName($thisTable);
                    if($withTable == $checkTable){
                        $this->setAs($autoJoinTable);
                    }
                } else {
                    if($autoJoinTable !== $thisTable){
                        //this is assuming $autoJoinTable can join directly with this table
                        $this->join($autoJoinTable, 'parent_id', $withTable, 'id', $autoJoinTable, 'left');
                    } else {
                        $this->setAs($autoJoinTable);
                    }
                }
            } else {
                //@todo report error
            }
        }
    }
    
    public function autoJoinWithColumn($autoJoinColumn){
        if (strpos($autoJoinColumn, '(') === false && strpos($autoJoinColumn, '.') !== false) {
//            $autoJoinTable = substr($autoJoinColumn, 0, strpos($autoJoinColumn, '.'));
            $autoJoinTable = static::getTableNameFromColumn($autoJoinColumn);
            $this->autoJoin($autoJoinTable);            
        }
    }
    
    public static function getTableNameFromColumn($column, $removeTicks = true){
        $tableName = substr($column, 0, strpos($column, '.'));
        if($removeTicks){
            return GI_StringUtils::removeTicks($tableName);
        }
        return $tableName;
    }
    
    public static function getColumnNameFromColumn($column){
        $columnName = substr($column, strpos($column, '.') + 1);
        return $columnName;
    }
    
    public function getTypeIdByRefAndTableName($typeRef, $tableName){
        if($this->factoryCreated()){
            $factoryClass = $this->factoryClass;
            $defaultDAOClass = $factoryClass::getStaticPropertyValueFromChild('defaultDAOClass');
            $typeTableName = $factoryClass::prepareTypeTableName($tableName);
            $typeDAO = $defaultDAOClass::getTypeDAOByRef($typeTableName, $typeRef, $factoryClass::getDBType());
            if (!empty($typeDAO)) {
                $typeId = $typeDAO->getProperty('id');
                return $typeId;
            }
        }
        return NULL;
    }
    
}
