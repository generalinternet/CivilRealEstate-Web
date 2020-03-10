<?php
/**
 * Description of AbstractDBDirectUpdater
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractDBDirectUpdater {
    
    protected $tableName = '';
    protected $columnTypes = array();
    protected $dbType = 'client';
    protected $commonColsType = 'full';
    
    public function __construct($tableName, $commonColsType = 'full', $columnTypes = array()) {
        $this->tableName = $tableName;
        $this->setCommonColsType($commonColsType);
        $this->columnTypes = $columnTypes;
    }
    
    public function setColumnType($column, $type){
        $this->columnTypes[$column] = $type;
        return $this;
    }
    
    public function setDBType($dbType){
        $this->dbType = $dbType;
        return $this;
    }
    
    public function setCommonColsType($commonColsType){
        $this->commonColsType = $commonColsType;
        if($commonColsType == 'full'){
            $this->setColumnType('id', 'id');
            $this->setColumnType('uid', 'id');
            $this->setColumnType('inception', 'datetime');
            $this->setColumnType('last_mod', 'datetime');
            $this->setColumnType('last_mod_by', 'id');
        }
        return $this;
    }
    
    public function getTableName(){
        return $this->tableName;
    }
    
    public function getDBType(){
        return $this->dbType;
    }
    
    public function getColumnType($column){
        if(isset($this->columnTypes[$column])){
            return $this->columnTypes[$column];
        }
        return 'text';
    }
    
    protected function prepareValue($value, $column, $ignoreBaseDateTimes = false){
        $dbConnection = dbConnection::getInstance($this->getDBType());
        if(empty($dbConnection)){
            return false;
        }
        $columnType = $this->getColumnType($column);
        
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
        } else {
            if (is_numeric($value)) {
                $finalValue = $dbConnection->real_escape_string($value);
            } else {
                $finalValue = 'NULL';
            }
        }
        
        return $finalValue;
    }
    
    public function save($properties, $primaryValue = NULL, $primaryColumn = 'id'){
        $dbType = $this->getDBType();
        $dbConnection = dbConnection::getInstance($dbType);
        if(empty($dbConnection)){
            return false;
        }
        UserFactory::setDBType($dbType);
        $uid = Login::getUserId(true);
        UserFactory::resetDBType();
        
        $insert = false;
        
        $tableName = $this->getTableName();
        if(isset($properties[$primaryColumn])){
            if(empty($primaryValue)){
                $primaryValue = $properties[$primaryValue];
            } elseif($primaryValue !== $properties[$primaryColumn]){
                trigger_error('Provided $primaryValue does not match primary value within properties');
                return false;
            }
        } else {
            $properties[$primaryColumn] = $primaryValue;
        }
        $saveId = NULL;
        if(empty($primaryValue)){
            $insert = true;
        } else {
            $existSearch = new GI_DataSearch($tableName);
            $existSearch->filter($primaryColumn, $primaryValue);
            $existCount = $existSearch->count();
            if(empty($existCount)){
                $insert = true;
            } elseif($existCount > 1){
                trigger_error('More than one row matches with the provided $primaryValue');
                return false;
            } else {
                $existSearch->setSelectColumns(array(
                    'id' => 'id'
                ));
                $existResults = $existSearch->select();
                $existIds = array_column($existResults, 'id');
                $saveId = $existIds[0];
            }
        }
        
        $ignoreBaseDateTimes = false;
        if ($this->commonColsType === 'full') {
            $rejectColumns = array(
                'id',
                'uid',
                'inception',
                'last_mod',
                'last_mod_by'
            );
            $ignoreBaseDateTimes = true;
        } else {
            $rejectColumns = array(
                'id'
            );
        }
        
        foreach ($rejectColumns as $column) {
            if (isset($properties[$column])) {
                unset($properties[$column]);
            }
        }
        $currentTime = GI_Time::getDateTime();
        $lastModTime = GI_Time::formatToGMT($currentTime);
        if($insert){
            $properties['status'] = 1;
            if($ignoreBaseDateTimes){
                //if $ignoreBaseDateTimes is true, then that means this table contains the base columns
                $properties['uid'] = $uid;
                $properties['inception'] = $lastModTime;
            }
        }
        if($ignoreBaseDateTimes){
            $properties['last_mod'] = $lastModTime;
            $properties['last_mod_by'] = $uid;
        }

        $queryString = NULL;
        if($insert){
            foreach ($properties as $column => $value) {
                $columns .= ', `' . $column . '`';
                $values .= ', ' . $this->prepareValue($value, $column, $ignoreBaseDateTimes);
            }
            $columns = substr($columns, 1);
            $values = substr($values, 1);
            $queryString = 'INSERT INTO `' . dbConfig::getDbPrefix($dbType) . $tableName . '` (' . $columns . ')VALUES (' . $values . ')';
        } else {
            $updateString = '';
            foreach ($properties as $column => $value) {
                $updateString .= ', `' . $column . '` = ' . $this->prepareValue($value, $column, $ignoreBaseDateTimes);
            }
            $finalUpdateString = substr($updateString, 1);
            $queryString = 'UPDATE `' . dbConfig::getDbPrefix($dbType) . $tableName . '` SET ' . $finalUpdateString . ' WHERE `' . $primaryColumn . '`=' . $this->prepareValue($primaryValue, $primaryColumn, $ignoreBaseDateTimes);
        }
        
        try {
            $dbConnection->query($queryString);
            if($insert){
                $saveId = $dbConnection->insert_id;
            }
            return $saveId;
        } catch (mysqli_sql_exception $ex) {
            if (DEV_MODE) {
                print_r($ex->getMessage());
                die();
            }
            trigger_error('Could not save.');
            return false;
        }
        
    }
    
}
