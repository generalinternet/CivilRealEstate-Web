<?php
/**
 * Description of GI_DAO
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class GI_DAO extends GI_Object {

    protected $cols = array();
    protected $tableName = '';
    protected $properties;
    protected $originalProperties = array();
    protected $tableId;
    protected $tableModel;
    protected $dbType;
    protected $commonColsType = '';
    protected $typeLinkTableName = NULL;
    protected $typeRef = NULL;
    protected $hasTypeId = false;
    protected $pendingLinkDAO = NULL;
    protected $typeDAO = NULL;
    protected $tableHasBaseColumns = NULL;
    protected $used = false;
    protected static $colsResults = array();
    protected $baseColInfo = array(
        'id' => array(
            'type' => 'id',
            'indexed' => 1
        ),
        'inception' => array(
            'type' => 'datetime',
            'indexed' => 0
        ),
        'status' => array(
            'type' => 'onoff',
            'indexed' => 0
        ),
        'uid' => array(
            'type' => 'id',
            'indexed' => 1
        ),
        'last_mod' => array(
            'type' => 'datetime',
            'indexed' => 0
        ),
        'last_mod_by' => array(
            'type' => 'id',
            'indexed' => 1
        ),
        'franchise_id'=>array(
            'type'=>'id',
            'indexed'=>1
        ),
    );
    protected static $doNotCloneColumns = array(
        'id',
        'inception',
        'uid',
        'last_mod',
        'last_mod_by',
        'parent_id'
    );

    public function __construct($tableName, $paramsArray = NULL) {
        if (isset($paramsArray['dbType'])) {
            $this->dbType = $paramsArray['dbType'];
        } else {
            $this->dbType = 'client';
        }
        $this->tableName = $tableName;
        $this->setTableId();
        $this->setCols();
        $this->initProperties();
        if (isset($paramsArray['id'])) {
            $this->properties['id'] = $this->filterProperty('id', $paramsArray['id']);
        }
        if (isset($paramsArray['uid'])) {
            $this->properties['uid'] = $this->filterProperty('uid', $paramsArray['uid']);
        }
        if (isset($paramsArray['inception'])) {
            $this->properties['inception'] = $this->filterProperty('inception', $paramsArray['inception']);
        }
    }
    
    public function __clone(){
        $doNotCloneColumns = static::$doNotCloneColumns;
        foreach($doNotCloneColumns as $property){
            if(isset($this->properties[$property])){
                $this->properties[$property] = NULL;
            }
        }
        $this->originalProperties = $this->properties;
    }
    
    public static function isColumnCloneable($column){
        if(in_array($column, static::$doNotCloneColumns)){
            return false;
        }
        return true;
    }
    
    /**
     * @return Boolean
     */
    public function getUsedState() {
        return $this->used;
    }
    
    /**
     * @param Boolean $used
     */
    public function setUsedState($used) {
        $this->used = $used;
    }
    
    /**
     * Initializes $this->properties array with the correct keys
     */
    protected function initProperties() {
        $properties = array();
        $colsKeys = array_keys($this->getCols());
        for ($i = 0; $i < count($colsKeys); $i++) {
            $properties[$colsKeys[$i]] = NULL;
            $this->originalProperties[$colsKeys[$i]] = NULL;
            $typeTableIdColName = $this->tableName . '_type_id';
            if ($colsKeys[$i] === $typeTableIdColName) {
                $this->hasTypeId = true;
            }
        }
        $this->properties = $properties;
    }

    /**
     * 
     * @return array - an array representing the columns in the db table corresponding to this model
     *                 of the form:  array( 'col_name' => array('type'=>string, 'not_null'=> binary, 'indexed'=>binary),
     *                                      etc..
     *                                    )
     */
    public function getCols() {
        return $this->cols;
    }

    public function getCommonColsType() {
        return $this->commonColsType;
    }

    public function setTypeDAO(GI_DAO $typeDAO) {
        $this->typeDAO = $typeDAO;
    }

    public function getTypeDAO() {
        return $this->typeDAO;
    }

    public function getColType($colKey) {
        if (isset($this->cols[$colKey])) {
            if (isset($this->cols[$colKey]['type'])) {
                return $this->cols[$colKey]['type'];
            }
        }
        return NULL;
    }

    public function getColNotNullStatus($colKey) {
        if (isset($this->cols[$colKey])) {
            if (isset($this->cols[$colKey]['not_null'])) {
                return $this->cols[$colKey]['not_null'];
            }
        }
        return NULL;
    }

    public function getTableName() {
        return strtolower($this->tableName);
    }

    public function setTypeRef($typeRef) {
        $this->typeRef = $typeRef;
    }

    public function setTypeLinkTableName($typeLinkTableName) {
        $this->typeLinkTableName = $typeLinkTableName;
    }

    public function getHasTypeIdStatus() {
        return $this->hasTypeId;
    }

    public function setPendingLinkDAO($pendingLinkDAO) {
        $this->pendingLinkDAO = $pendingLinkDAO;
    }

    public function getPendingLinkDAO() {
        return $this->pendingLinkDAO;
    }

    public function getIsFilteredByFranchise() {
        if (ProjectConfig::getIsFranchisedSystem()) {
            $tableModel = $this->tableModel;
            if (!empty($tableModel)) {
                if (!empty($tableModel->getProperty('filter_franchise'))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Sets the columns corresponding to the db table
     */
    protected function setCols() {
        $dbConnect = dbConnection::getInstance($this->dbType);
        try {
            $this->commonColsType = 'full';
            if (!$this->tableHasBaseColumns()) {
                $this->commonColsType = 'basic';
            }
            $colsTemp = array();

            //Add the columns common to *almost* all tables
            $table = NULL;
            if (ProjectConfig::getIsFranchisedSystem()) {
                $table = TableFactory::getModelById($this->tableId);
            }
            
            $commonCols = dbConfig::getCommonColNames($this->commonColsType, $table);
            foreach($commonCols as $column => $columnInfo){
                $colsTemp[$column] = $columnInfo;
            }

            if (isset(static::$colsResults[$this->tableName])) {
                $result = static::$colsResults[$this->tableName];
            } else {
                $sql = 'SELECT column_name, type, not_null, indexed FROM ' . dbConfig::getDbPrefix($this->dbType) . 'table_column WHERE table_id="' . $this->tableId . '"';
                $req = $dbConnect->query($sql);
                $result = $req->fetch_all(MYSQLI_ASSOC);
                static::$colsResults[$this->tableName] = $result;
            }

            //Add the columns specific to this model's table
            for ($i = 0; $i < sizeof($result); $i++) {
                $array2 = array(
                    'type' => $result[$i]['type'],
                    'not_null' => $result[$i]['not_null'],
                    'indexed' => $result[$i]['indexed']);
                $colsTemp[$result[$i]['column_name']] = $array2;
            }
            $this->cols = $colsTemp;
        } catch (mysqli_sql_exception $e) {
            trigger_error($e->getMessage());
        }
    }

//    protected function setTableId() {
//        $dbConnect = dbConnection::getInstance($this->dbType);
//        $sql = 'SELECT * FROM ' . dbConfig::getDbPrefix($this->dbType) . 'table WHERE system_title="' . strtolower($this->tableName) . '"';
//        try{
//            $req = $dbConnect->query($sql);
//            $result = $req->fetch_array(MYSQLI_ASSOC);
//            $this->tableId = $result['id'];
//            return true;
//        } catch (mysqli_sql_exception $e) {
//            trigger_error($e->getMessage());
//        }
//        return false;
//    }

    protected function setTableId() {
        $dbConnect = dbConnection::getInstance($this->dbType);
        $sql = 'SELECT * FROM `' . dbConfig::getDbPrefix($this->dbType) . 'table` WHERE `system_title`="' . strtolower($this->tableName) . '"';
        try {
            $req = $dbConnect->query($sql);
            $result = $req->fetch_array(MYSQLI_ASSOC);
            $this->tableId = $result['id'];
            $this->tableModel = TableFactory::getModelById($this->tableId);
            return true;
        } catch (mysqli_sql_exception $e) {
            trigger_error($e->getMessage());
        }
        return false;
    }

    protected function tableHasBaseColumns(){
        if(is_null($this->tableHasBaseColumns)){
            $this->tableHasBaseColumns = true;
            $dbConnect = dbConnection::getInstance($this->dbType);
            if ($this->dbType !== 'master') {
                $tableQuery = 'SELECT * FROM `' . dbConfig::getDbPrefix($this->dbType) . 'table` WHERE `id`="' . $this->tableId . '"';
                $tableReq = $dbConnect->query($tableQuery);
                $tableResult = $tableReq->fetch_all(MYSQLI_ASSOC);
                if ($tableResult) {
                    $tableInfo = $tableResult[0];
                    $addBase = 1;
                    if(isset($tableInfo['add_base'])){
                        $addBase = (int) $tableInfo['add_base'];
                    }

                    if (!$addBase) {
                        $this->tableHasBaseColumns = false;
                    }
                }
            }
        }
        return $this->tableHasBaseColumns;
    }

    /**
     * 
     * @return array - an array containing the properties for the DAO, in the
     *                      form propertyName => value
     */
    public function getProperties() {
        return $this->properties;
    }

    public function getClassProperties(){
        $dao = new GenericDAO($this->tableName, array(
            'dbType' => $this->dbType
        ));
        return $dao->properties;
    }

    /**
     * 
     * @param string $key
     * @return varied - the property associated with $key, if it exists,
     *                  NULL otherwise
     */
    public function getProperty($key, $original = false) {
        if (!$original) {
            if (array_key_exists($key, $this->properties)) {
                return $this->properties[$key];
            }
        } else {
            if (array_key_exists($key, $this->originalProperties)) {
                return $this->originalProperties[$key];
            }
        }
        return NULL;
    }
    
    public function getId(){
        return $this->getProperty('id');
    }

    /**
     * 
     * @param array $properties
     * @return boolean - true if $properties set for model, false if otherwise
     */
    public function setProperties($properties) {
        $intersection = array_intersect_key($this->properties, $properties);
        $size1 = sizeof(array_keys($intersection));
        $size2 = sizeof(array_keys($this->properties));
        if ($size1 == $size2) {
            foreach ($properties as $key => $value) {
                $this->setProperty($key, $value);
            }
            return true;
        }
        return false;
    }

    public function filterProperty($column, $value) {
        if ($value === 'NULL') {
            $value = NULL;
        }
        $notNull = $this->cols[$column]['not_null'];
        if (!empty($this->cols) && ($notNull || !is_null($value) )) {
            $columnType = $this->cols[$column]['type'];
            if (!$notNull && $value === '') {
                $finalValue = NULL;
            } else {
                if ($notNull && (is_null($value) || $value === '')) {
                    if (isset($this->cols[$column]['default_val'])) {
                        $finalValue = $this->cols[$column['default_val']];
                    } else {
                        $finalValue = 0;
                    }
                } else {
                    switch ($columnType) {
                        case 'onoff':
                        case 'id':
                        case 'integer':
                        case 'integer_large':
                            $finalValue = (int) $value;
                            break;
                        case 'money':
                        case 'decimal':
                            $finalValue = (float) $value;
                            break;
                        default:
                            $finalValue = $value;
                            break;
                    }
                }
            }
            return $finalValue;
        } else {
            return $value;
        }
    }

    /**
     * 
     * @param string $key
     * @param string $value
     * @return boolean - true if $value inserted into properties at $key,
     *                   false if either: $key doesn't exist in properties,
     *                                    or $key is one of: 'id','uid','inception'
     */
    public function setProperty($key, $value) {
        if (array_key_exists($key, $this->properties)) {
            if (!($key == 'id' || $key == 'uid' || $key == 'inception')) {
                $filteredValue = $this->filterProperty($key, $value);
                $originalProperty = $this->getProperty($key, true);
                if (is_null($this->originalProperties[$key])) {
                    $this->originalProperties[$key] = $filteredValue;
                }
                $this->properties[$key] = $filteredValue;
                if (empty($originalProperty) || ($filteredValue != $originalProperty)) {
                    $this->setUsedState(true);
                }
                $this->setUsedState(true);
                return true;
            }
        }
        return false;
    }


    public static function getById($tableName, $id, $dbType = 'client', $status = 1, $commonColsType = 'full') {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        $dbConnect = dbConnection::getInstance($dbType);
        $sql = 'SELECT * FROM `' . dbConfig::getDbPrefix($dbType) . $tableName . '` WHERE `id`=' . $id . ' AND `status` = ' . $status;
        try {
            $req = $dbConnect->query($sql);
            $daoClass = get_called_class();
            $tempModel = new $daoClass($tableName, array(
                'dbType' => $dbType
            ));
            $colInfo = $tempModel->getCols();
            $result = $req->fetch_array(MYSQLI_ASSOC);
            if (!$result) {
                return NULL;
            }
            $tempProperties = array();
            $tempPropertiesKeys = array_keys($result);
            
            for ($i = 0; $i < sizeof($tempPropertiesKeys); $i++) {
                $key = $tempPropertiesKeys[$i];
                if(!isset($colInfo[$key])){
                    continue;
                }
                $columnType = $colInfo[$key]['type'];
                if ($columnType === 'datetime' || $columnType === 'time' || $columnType === 'date') {
                    $convertedTime = GI_Time::formatToUserTime($result[$key], $columnType);
                    $tempProperties[$key] = $convertedTime;
                } else {
                    $tempProperties[$key] = $result[$key];
                }
            }
            $propsArray = array(
                'id' => $id,
                'dbType' => $dbType
            );
            if ($dbType !== 'master') {
                if ($commonColsType === 'full') {
                    $inception = $tempProperties['inception'];
                    $uid = $tempProperties['uid'];
                    $propsArray['uid'] = $uid;
                    $propsArray['inception'] = $inception;
                }
            }
            $dao = new GenericDAO($tableName, $propsArray);
            $dao->setProperties($tempProperties);
            return $dao;
        } catch (Exception $ex) {
            //TODO: Add Logging
            return NULL;
        }
    }

    /**
     * 
     * @param array $properties
     * @return array - An array of models with matching properties in the db
     */
    public static function getByProperties($tableName, $properties = array(), $dbType = 'client', $status = 1, $pageNumber = NULL, $itemsPerPage = 100) {
        
        if (!array_filter($properties)) {
            return static::getAll($dbType, $status, $pageNumber, $itemsPerPage);
        }
        
        if (!isset($properties['status'])) {
            $properties = static::setStatusInPropertiesArray($properties, $status);
        } else {
            $properties = static::setStatusInPropertiesArray($properties, $properties['status']);
        }
        
        if (!$pageNumber) {
            $sqlLimit = ' LIMIT ' . static::buildSQLLimit(1, $itemsPerPage);
        } else {
            $sqlLimit = ' LIMIT ' . static::buildSQLLimit($pageNumber, $itemsPerPage);
        }

        $dbConnection = dbConnection::getInstance($dbType);
        //check if the table exists before continuing
        if (!dbConnection::verifyTableExists($tableName, $dbType)) {
            return NULL;
        }

        $cols = '';
        $colsAndValues = '';
        $daoClass = get_called_class();
        $tempModel = new $daoClass($tableName, array(
            'dbType' => $dbType
        ));

        $colInfo = $tempModel->getCols();

        foreach ($properties as $column => $value) {
            if(!isset($colInfo[$column])){
                continue;
            }
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $cols .= ', ' . $column;
            if ($value != 'NULL' && $value != '') {
                $columnType = $colInfo[$column]['type'];
                if ($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time') {
                    $value = GI_Time::formatToGMT($value, $columnType);
                }

                $colsAndValues .= ' AND `' . $column . '`="' . $dbConnection->real_escape_string($value) . '"';
            }
        }
        $colsAndValues = substr($colsAndValues, 4);
        $cols = substr($cols, 1);

        $queryString = 'SELECT * FROM `' . dbConfig::getDbPrefix($dbType) . $tableName . '` WHERE ' . $colsAndValues . $sqlLimit;

        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);

            $modelsArray = array();

            for ($i = 0; $i < sizeof($result); $i++) {
                $model = static::buildDaoFromResult($tableName, $result[$i], $dbType);
                array_push($modelsArray, $model);
            }
            return $modelsArray;
        } catch (mysqli_sql_exception $e) {
            print_r($e->getMessage());
            die();
            return NULL;
        }
    }

    public static function buildSQLLimit($pageNumber, $itemsPerPage = 100) {
        $pageNumber = (int) $pageNumber;
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }
        $sqlLimit = (($pageNumber - 1) * $itemsPerPage ) . ',' . $itemsPerPage;
        return (string) $sqlLimit;
    }

    //v1.5 OK
    public static function getAll($tableName, $dbType = 'client', $status = 1, $pageNumber = NULL, $itemsPerPage = 100) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        if (!$pageNumber) {
            $sqlLimit = ' LIMIT ' . static::buildSQLLimit(1, $itemsPerPage);
        } else {
            $sqlLimit = ' LIMIT ' . static::buildSQLLimit($pageNumber, $itemsPerPage);
        }
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = 'SELECT * FROM `' . dbConfig::getDbPrefix($dbType) . $tableName . '` WHERE `status` = ' . $status . $sqlLimit;
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $daoArray = array();
            for ($i = 0; $i < sizeof($result); $i++) {
                $dao = static::buildDaoFromResult($tableName, $result[$i], $dbType);
                $daoArray[] = $dao;
            }
            return $daoArray;
        } catch (Exception $ex) {
            $ex->getMessage();
            //TODO: Add Logging
            return NULL;
        }
    }
    
    public static function getByDataSearch(GI_DataSearch $dataSearch){
        $tableName = $dataSearch->getTableName();
        $dbType = $dataSearch->getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = $dataSearch->getSearchString();
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $daoArray = array();
            for ($i = 0; $i < sizeof($result); $i++) {
                $dao = static::buildDaoFromResult($tableName, $result[$i], $dbType);
                $daoArray[] = $dao;
            }
            return $daoArray;
        } catch (Exception $ex) {
            $ex->getMessage();
            //TODO: Add Logging
            return NULL;
        }
    }
    
    public static function getCountByDataSearch(GI_DataSearch $dataSearch){
        $dbType = $dataSearch->getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = $dataSearch->getCountString();
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_array(MYSQLI_ASSOC);
            return (int) $result['row_count'];
        } catch (Exception $ex) {
            //TODO: Add Logging
        }
    }
    
    public static function getSumByDataSearch(GI_DataSearch $dataSearch){
        $dbType = $dataSearch->getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = $dataSearch->getSumString();
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_array(MYSQLI_ASSOC);
            return $result;
        } catch (Exception $ex) {
            //TODO: Add Logging
        }
    }

    public function delete() {
        $id = $this->properties['id'];
        $dbConnect = dbConnection::getInstance($this->dbType);
        if ($this->tableName != 'login') {
            $auditSql = 'DELETE FROM `' . dbConfig::getDbPrefix($this->dbType) . static::getTableName() . '_audit` WHERE `target_id`=' . $id;
        } else {
            $auditSql = NULL;
        }
        $sql = 'DELETE FROM `' . dbConfig::getDbPrefix($this->dbType) . static::getTableName() . '` WHERE id=' . $id;
        try {
            if ($auditSql) {
                $dbConnect->query($auditSql);
            }
            $dbConnect->query($sql);
            $this->clearProperties();
            return true;
        } catch (Exception $ex) {
            //TODO: Add Logging
            return false;
        }
    }

    public function softDelete() {
        /*
        $this->setProperty('status', 0);
        return $this->save();
         */
        $id = $this->getProperty('id');
        $dbConnect = dbConnection::getInstance($this->dbType);
        $uid = Login::getUserId(true);
        $tempInception = GI_Time::getDateTime();
        $lastModTime = GI_Time::formatToGMT($tempInception);
        $sql = 'UPDATE `' . dbConfig::getDbPrefix($this->dbType) . static::getTableName() . '` ';
        $sql .= 'SET `status` = 0 ';
        if($this->tableHasBaseColumns()){
            $sql .= ', `last_mod` = "' . $lastModTime . '" ';
            $sql .= ', `last_mod_by` = ' . $uid . ' ';
        }
        $sql .= 'WHERE `id`=' . $id;
        try {
            $dbConnect->query($sql);
            $this->setProperty('status', 0);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /*
     * Saves the data from the dao in the db;
     */

    public function save() {
        if (ProjectConfig::getIsFranchisedSystem() && $this->getIsFilteredByFranchise()) {
            if (empty($this->getProperty('franchise_id'))) {
                $currentFranchise = Login::getCurrentFranchise();
                if (!empty($currentFranchise)) {
                    $franchiseId = $currentFranchise->getProperty('id');
                    $this->setProperty('franchise_id', $franchiseId);
                }
            }
        }
        if ($this->properties['id']) {
            $result = $this->update();
        } else {
            $result = $this->insert();
        }
        if ($result) {
            $this->originalProperties = $this->getProperties();
            return true;
        } else {
            return false;
        }
    }
    
    public function getBaseColInfo($column){
        if(isset($this->baseColInfo[$column])){
            return $this->baseColInfo[$column];
        }
        return NULL;
    }

    public function prepareValue($value, $column, $ignoreBaseDateTimes = false){
        $dbConnection = dbConnection::getInstance($this->dbType);
        $columnType = 'text';
        $columnNotNull = false;
        if(isset($this->cols[$column])){
            $columnType = $this->cols[$column]['type'];
            $columnNotNull = $this->cols[$column]['not_null'];
        } else {
            $baseColInfo = $this->getBaseColInfo($column);
            if($baseColInfo){
                $columnType = $baseColInfo['type'];
                $columnNotNull = true;
            }
        }
        
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        if ($value != 'NULL' && $value != '') {
            if ($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time') {
                if(!$ignoreBaseDateTimes || ($column != 'inception' && $column != 'last_mod')){
                    $value = GI_Time::formatToGMT($value, $columnType);
                }
            }
            $finalValue = '"' . $dbConnection->real_escape_string($value) . '"';
        } elseif ($columnNotNull && $value == 0) {
            $finalValue = 0;
        } else {
            if (is_numeric($value)) {
                $finalValue = $dbConnection->real_escape_string($value);
            } else {
                $finalValue = 'NULL';
            }
        }
        
        return $finalValue;
    }
    /*
     * Inserts the dao as a new row in the db;
     */

    protected function insert() {
        $dbConnection = dbConnection::getInstance($this->dbType);
        $columns = '';
        $values = '';
        $tempInception = NULL;
        if ($this->dbType !== 'master') {
            UserFactory::setDBType($this->dbType);
            $uid = Login::getUserId(true);
            UserFactory::resetDBType();
            $tempInception = GI_Time::getDateTime();
            $inception = GI_Time::formatToGMT($tempInception);
            if ($this->commonColsType === 'full') {
                $rejectColumns = array(
                    'id',
                    'inception',
                    'status',
                    'uid',
                    'last_mod',
                    'last_mod_by'
                );
            } else {
                $rejectColumns = array(
                    'id',
                    'status'
                );
            }
            foreach ($rejectColumns as $column) {
                if (isset($this->properties[$column])) {
                    unset($this->properties[$column]);
                }
            }
            if ($this->commonColsType == 'full') {
                $this->properties['inception'] = $inception;
                $this->properties['status'] = 1;
                $this->properties['uid'] = $uid;
                $this->properties['last_mod'] = $inception;
                $this->properties['last_mod_by'] = $uid;
            } else {
                $this->properties['status'] = 1;
            }
        } else {
            $rejectColumns = array(
                'id'
            );
            foreach ($rejectColumns as $column) {
                if (isset($this->properties[$column])) {
                    unset($this->properties[$column]);
                }
            }
        }
        //$this->properties['id'] = NULL;
        $this->properties['id'] = '0';

        foreach ($this->properties as $column => $value) {
            $columns .= ', `' . $column . '`';
            $values .= ', ' . $this->prepareValue($value, $column, true);
        }
        $columns = substr($columns, 1);
        $values = substr($values, 1);
        $queryString = 'INSERT INTO `' . dbConfig::getDbPrefix($this->dbType) . $this->tableName . '` (' . $columns . ')VALUES (' . $values . ')';
        try {
            $dbConnection->query($queryString);
            $lastId = $dbConnection->insert_id;
            $this->properties['id'] = $lastId;
            if (!is_null($this->typeLinkTableName) && !is_null($this->typeRef)) {
                $typeTableName = $this->tableName . '_type';
                $typeDAO = static::getTypeDAOByRef($typeTableName, $this->typeRef, $this->dbType);
                if (!is_null($typeDAO)) {
                    $typeId = $typeDAO->getProperty('id');
                    $linkTableDAO = new GenericDAO($this->typeLinkTableName, array(
                        'dbType' => $this->dbType
                    ));
                    $linkTableDAO->setProperty($this->tableName . '_id', $lastId);
                    $linkTableDAO->setProperty($typeTableName . '_id', $typeId);
                    if (!$linkTableDAO->save()) {
                        return false;
                    }
                }
            }
            if (!empty($tempInception)) {
                if (isset($this->properties['inception'])) {
                    $this->properties['inception'] = $tempInception;
                }
                if (isset($this->properties['last_mod'])) {
                    $this->properties['last_mod'] = $tempInception;
                }
            }
            return true;
        } catch (mysqli_sql_exception $e) {
            $this->clearProperties();
            if (DEV_MODE) {
                print_r($e->getMessage());
                echo '<br/>';
                echo '<pre><i>' . $queryString . '</i></pre>';
                die();
            }

            //TODO: Add Logging
            return false;
        }
    }

    protected function clearProperties() {
        $this->properties = array_fill_keys(array_keys($this->properties), null);
    }

    /*
     * Updates an existing row in the db;
     */

    protected function update() {
        $dbConnection = dbConnection::getInstance($this->dbType);
        UserFactory::setDBType($this->dbType);
        $uid = Login::getUserId(true);
        UserFactory::resetDBType();
        $currentTime = NULL;
        $oldColumnValues = array();
        $rowId = $this->properties['id'];
        if ($this->dbType !== 'master') {
            if ($this->commonColsType === 'full') {
                $rejectColumns = array(
                    'id',
                    'inception',
                    'last_mod',
                    'last_mod_by'
                );
                foreach ($rejectColumns as $column) {
                    if (isset($this->properties[$column])) {
                        $oldColumnValues[$column] = $this->properties[$column];
                        unset($this->properties[$column]);
                    }
                }
                $currentTime = GI_Time::getDateTime();
                $lastModTime = GI_Time::formatToGMT($currentTime);
                $this->properties['last_mod'] = $lastModTime;
                $this->properties['last_mod_by'] = $uid;
            }
        }

        $updates = '';
        foreach ($this->properties as $column => $value) {
            $updates .= ', `' . $column . '` = ' . $this->prepareValue($value, $column, true);
        }
        $updates = substr($updates, 1);
        $queryString = 'UPDATE `' . dbConfig::getDbPrefix($this->dbType) . $this->tableName . '` SET ' . $updates . ' WHERE `id`=' . '"' . $rowId . '"';
        foreach ($oldColumnValues as $rejectColumn => $rejectedValue) {
            if (!isset($this->properties[$rejectColumn])) {
                $this->properties[$rejectColumn] = $rejectedValue;
            }
        }
        try {
            $dbConnection->query($queryString);
            if (!empty($currentTime)) {
                $this->properties['last_mod'] = $currentTime;
            }
            return true;
        } catch (mysqli_sql_exception $ex) {
            if (DEV_MODE) {
                print_r($ex->getMessage());
                die();
            }

            //TODO: Add Logging
            return false;
        }
    }
    
    protected static function setStatusInPropertiesArray(&$properties, $status) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        } else {
            $status = '0';
        }
        $properties['status'] = $status;
        return $properties;
    }

    public static function buildSystemTitle($title) {
        $lcTitle = strtolower($title);
        $pieces = explode(' ', $lcTitle);
        $returnString = $pieces[0];
        if (sizeof($pieces) > 1) {
            for ($i = 1; $i < sizeof($pieces); $i++) {
                $returnString .= '_' . $pieces[$i];
            }
        }
        return $returnString;
    }

    public static function buildTitleFromSystemTitle($systemTitle) {
        $pieces = explode('_', $systemTitle);
        $returnString = ucfirst($pieces[0]);
        if (sizeof($pieces) > 1) {
            for ($i = 1; $i < sizeof($pieces); $i++) {
                $returnString .= ' ' . ucfirst($pieces[$i]);
            }
        }
        return $returnString;
    }

    protected static function buildDaoFromResult($tableName, $result, $dbType = 'client') {
        $id = $result['id'];
        $daoClass = get_called_class();
        $paramsArray = array();
        $paramsArray['id'] = $id;
        $paramsArray['dbType'] = $dbType;
        if ($dbType !== 'master') {
            if (isset($result['uid'])) {
                $paramsArray['uid'] = $result['uid'];
            }
            if (isset($result['inception'])) {
                $paramsArray['inception'] = GI_Time::formatToUserTime($result['inception'], 'datetime');
            }
        }
        $dao = new $daoClass($tableName, $paramsArray);
        $colInfo = $dao->getCols();
        foreach ($result as $key => $value) {
            if(!isset($colInfo[$key])){
                continue;
            }
            $columnType = $colInfo[$key]['type'];
            if ($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time') {
                if(!empty(strtotime($value))){
                    $convertedTime = GI_Time::formatToUserTime($value, $columnType);
                    $dao->setProperty($key, $convertedTime);
                }
            } else {
                $dao->setProperty($key, $value);
            }
        }

        return $dao;
    }
    
    public static function genUniqueQueryId() {
        $newQueryId = 'gi_' . mt_rand(5000, 100000);
        $keys = array(
            'queryIds',
            $newQueryId
        );
        while(!empty(SessionService::getValue($keys))) {
            $newQueryId = static::genUniqueQueryId();
        }

        return $newQueryId;
    }

    public static function saveSessionQuery($searchParams = NULL, $groupByOrderBy = NULL, $queryValues = NULL) {
        $newQueryId = static::genUniqueQueryId();
        $value = array(
            'searchParams' => $searchParams,
            'groupByOrderBy' => $groupByOrderBy,
            'queryValues' => $queryValues
        );
        $keys = array(
            'queryIds',
            $newQueryId,
        );
        SessionService::setValue($keys, $value);
        return $newQueryId;
    }

    public static function updateSessionQuery($queryId, $searchParams = NULL, $groupByOrderBy = NULL, $queryValues = NULL) {
        $keys = array(
            'queryIds',
            $queryId,
        );
        $value = array(
            'searchParams' => $searchParams,
            'groupByOrderBy' => $groupByOrderBy,
            'queryValues' => $queryValues
        );
        SessionService::setValue($keys, $value);
    }

    public static function getSessionQuery($queryId, &$searchParams = NULL, &$groupByOrderBy = NULL, &$queryValues = NULL) {
        $sessionQuery = SessionService::getValue(array(
                    'queryIds',
                    $queryId,
        ));
        if (empty($sessionQuery)) {
            return false;
        }
        $searchParams = $sessionQuery['searchParams'];
        $groupByOrderBy = $sessionQuery['groupByOrderBy'];
        $queryValues = $sessionQuery['queryValues'];

        return true;
    }

    public static function getSessionQueryValue($queryId, $value) {
        $queryValues = SessionService::getValue(array(
            'queryIds',
            $queryId,
            'queryValues'
        ));
        if (empty($queryValues) || !isset($queryValues[$value])) {
            return NULL;
        }
        return $queryValues[$value];
    }

    public static function buildPhoneTerms($phoneNumber) {
        $phoneTerms = array(
            $phoneNumber
        );

        $phoneStripOldFormat = str_replace(array('(', ')', '-'), array('', '', ' '), $phoneNumber);
        if (!empty($phoneStripOldFormat) && !in_array($phoneStripOldFormat, $phoneTerms)) {
            $phoneTerms[] = $phoneStripOldFormat;
        }

        $phoneDashes = str_replace(array('.', ' '), '-', $phoneStripOldFormat);
        if (!empty($phoneDashes) && !in_array($phoneDashes, $phoneTerms)) {
            $phoneTerms[] = $phoneDashes;
        }

        $phoneDots = str_replace(array('-', ' '), '.', $phoneStripOldFormat);
        if (!empty($phoneDots) && !in_array($phoneDots, $phoneTerms)) {
            $phoneTerms[] = $phoneDots;
        }

        $phoneSpaces = str_replace(array('-', '.'), ' ', $phoneStripOldFormat);
        if (!empty($phoneSpaces) && !in_array($phoneSpaces, $phoneTerms)) {
            $phoneTerms[] = $phoneSpaces;
        }

        $phoneNumeric = preg_replace("/[^0-9,.]/", "", $phoneNumber);
        if (!empty($phoneNumeric) && !in_array($phoneNumeric, $phoneTerms)) {
            $phoneTerms[] = $phoneNumeric;
        }

        return $phoneTerms;
    }
    
    public static function getTypeDAOByRef($typeTableName, $typeRef, $dbType = 'client') {
        $typeDAOArray = static::getByProperties($typeTableName, array(
            'ref'=>$typeRef
        ), $dbType);
        if ($typeDAOArray) {
            $typeDAO = $typeDAOArray[0];
            return $typeDAO;
        }
        return NULL;
    }
    
}
