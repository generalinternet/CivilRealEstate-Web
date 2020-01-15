<?php

abstract class GI_AuditModel {

    protected $properties;
    protected $dbType;
    protected static $auditPostfix = '_audit';

    public function __construct($paramsArray = NULL) {
        if (isset($paramsArray['dbType'])) {
            $this->dbType = $paramsArray['dbType'];
        } else {
            $this->dbType = 'client';
        }
        $this->setCols();
        $this->initProperties();
        if (isset($paramsArray['id'])) {
            $this->properties['id'] = $paramsArray['id'];
        }
        if (isset($paramsArray['uid'])) {
            $this->properties['uid'] = $paramsArray['uid'];
        }
        if (isset($paramsArray['inception'])) {
            $this->properties['inception'] = $paramsArray['inception'];
        }
    }

    public function setCols() {
        $targetDAO = new GenericDAO(static::$targetTableName);
        $targetDAOCols = $targetDAO->getCols();
        $targetDAOCols['target_id'] = array(
            'type'=>'id',
            'not_null'=>1,
            'indexed'=>1
        );
        static::$cols = $targetDAOCols;
    }

    public function getCols() {
        return static::$cols;
    }

    protected function initProperties() {
        $properties = array();
        $colsKeys = array_keys(static::getCols());
        for ($i = 0; $i < sizeof($colsKeys); $i++) {
            $properties[$colsKeys[$i]] = NULL;
        }
        $this->properties = $properties;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function getProperty($key) {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        }
        return NULL;
    }

    public static function getById($id, $dbType = 'client', $status = 1) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        $dbConnect = dbConnection::getInstance($dbType);
        $sql = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . static::$targetTableName . static::$auditPostfix . ' WHERE id=' . $id . ' AND status = ' . $status;
        try {
            $req = $dbConnect->query($sql);
            $modelName = static::$targetTableName . static::$auditPostfix;
            $tempModel = new $modelName();
            $cols = $tempModel->getCols();
            $result = $req->fetch_array(MYSQLI_ASSOC);
            if (!$result) {
                return NULL;
            }
            $tempProperties = array();
            $tempPropertiesKeys = array_keys($result);
            for ($i = 0; $i < sizeof($tempPropertiesKeys); $i++) {
                $key = $tempPropertiesKeys[$i];
                $columnType = $cols[$key]['type'];
                if ($columnType === 'datetime' || $columnType === 'time' || $columnType === 'date') {
                    $convertedTime = GI_Time::formatToUserTime($result[$key], $columnType);
                    $tempProperties[$key] = $convertedTime;
                } else {
                    $tempProperties[$key] = $result[$key];
                }
            }
            $inception = $tempProperties['inception'];
            $uid = $tempProperties['uid'];
            $model = new $modelName(
                    array(
                'id' => $id,
                'uid' => $uid,
                'inception' => $inception
                    )
            );
            $model->setProperties($tempProperties);
            //  $props = $model->getProperties();
            return $model;
        } catch (Exception $ex) {
            //TODO: Add Logging
            $ex->getMessage();
            return NULL;
        }
    }

    public static function getAllByTargetId($targetId, $dbType = 'client', $status = 1) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        $dbConnection = dbConnection::getInstance($dbType);
        $sql = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . static::$targetTableName . static::$auditPostfix . ' WHERE target_id=' . $targetId . ' AND status = ' . $status;
        try {
            $req = $dbConnection->query($sql);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $modelsArray = array();
            for ($i = 0; $i < sizeof($result); $i++) {
                $id = $result[$i]['id'];
                $uid = $result[$i]['uid'];
                $inception = $result[$i]['inception'];
                $modelName = static::$targetTableName . static::$auditPostfix;
                $model = new $modelName(array(
                    'id' => $id,
                    'uid' => $uid,
                    'inception' => $inception
                ));
                $cols = $model->getCols();
                foreach ($result[$i] as $key => $value2) {
                    $columnType = $cols[$key]['type'];
                    if (($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time')) {
                        $convertedTime = GI_Time::formatToUserTime($value2, $columnType);
                        $model->setProperty($key, $convertedTime);
                    } else {
                        $model->setProperty($key, $value2);
                    }
                }
                array_push($modelsArray, $model);
            }
            return $modelsArray;
        } catch (Exception $ex) {
            //TODO: Add Logging
        }
    }

    protected function setProperty($key, $value) {
        if (array_key_exists($key, $this->properties)) {
            if (!($key == 'id' || $key == 'uid' || $key == 'inception')) {
                $this->properties[$key] = $value;
                return true;
            }
        }
        return false;
    }

    protected function setProperties($properties) {
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

    public static function getByProperties($properties = array(), $dbType = 'client', $status = 1) {
        if (!array_filter($properties)) {
            return static::getAll($dbType, $status);
        }
        if (!isset($properties['status'])) {
            $properties = static::setStatusInPropertiesArray($properties, $status);
        } else {
            $properties = static::setStatusInPropertiesArray($properties, $properties['status']);
        }

        $dbConnection = dbConnection::getInstance($dbType);
        $cols = '';
        $colsAndValues = '';
        foreach ($properties as $column => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $cols .= ', ' . $column;
            if ($value != 'NULL' && $value != '') {
                $colsAndValues .= ' AND ' . $column . '="' . $dbConnection->real_escape_string($value) . '"';
            }
        }
        $colsAndValues = substr($colsAndValues, 4);
        $cols = substr($cols, 1);

        $queryString = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . static::$targetTableName . static::$auditPostfix . ' WHERE ' . $colsAndValues;

        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $modelName = static::$targetTableName . static::$auditPostfix;
            $modelsArray = array();

            for ($i = 0; $i < sizeof($result); $i++) {
                $id = $result[$i]['id'];
                $uid = $result[$i]['uid'];
                $inception = $result[$i]['inception'];
                $model = new $modelName(array(
                    'id' => $id,
                    'uid' => $uid,
                    'inception' => $inception
                ));
                $cols = $model->getCols();
                foreach ($result[$i] as $key => $value2) {
                    $columnType = $cols[$key]['type'];
                    if (($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time')) {
                        $convertedTime = GI_Time::formatToUserTime($value2, $columnType);
                        $model->setProperty($key, $convertedTime);
                    } else {
                        $model->setProperty($key, $value2);
                    }
                }
                array_push($modelsArray, $model);
            }
            return $modelsArray;
        } catch (mysqli_sql_exception $e) {
            //TODO: Add Logging
            return NULL;
        }
    }

    protected static function getAll($dbType = 'client', $status = 1) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . static::$targetTableName . static::$auditPostfix . ' WHERE status = ' . $status;
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $modelsArray = array();
            $modelName = static::$targetTableName . static::$auditPostfix;
            for ($i = 0; $i < sizeof($result); $i++) {
                $id = $result[$i]['id'];
                $uid = $result[$i]['uid'];
                $inception = GI_Time::formatToUserTime($result[$i]['inception']);
                $model = new $modelName(array(
                    'id' => $id,
                    'uid' => $uid,
                    'inception' => $inception
                ));
                $cols = $model->getCols();
                foreach ($result[$i] as $key => $value2) {
                    $columnType = $cols[$key]['type'];
                    if ($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time') {
                        $convertedTime = GI_Time::formatToUserTime($value2, $columnType);
                        $model->setProperty($key, $convertedTime);
                    } else {
                        $model->setProperty($key, $value2);
                    }
                }
                array_push($modelsArray, $model);
            }
            return $modelsArray;
        } catch (Exception $ex) {
            $ex->getMessage();
            //TODO: Add Logging
            return NULL;
        }
    }

    protected static function setStatusInPropertiesArray($properties, $status) {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        } else {
            $status = '0';
        }
        $properties['status'] = $status;
        return $properties;
    }

    /**
     * 
     * @param array() $searchParams - array(
     *                                      'property_name'=>array(
     *                                                      'comp'=>'comparison_operator',
     *                                                      'val'=>'value_to_be_compared_to'
     *                                                      )
     *                                      ... n
     *                                      )
     * 
     * Optional: @param  $orderByString Format: 'col_to_order by ORDER' Example: 'id ASC'
     */
    public static function getModelsBySearchParams($searchParams, $orderByString = NULL, $dbType = 'client') {
        $dbConnection = dbConnection::getInstance($dbType);
        $queryString = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . static::$targetTableName . static::$auditPostfix;

        if (sizeof($searchParams) == 1) {
            $searchArray = reset($searchParams);
            $singleComp = $searchArray['comp'];
            $singleVal = $searchArray['val'];
            if ($singleComp !== 'NULL' && $singleVal !== 'NULL') {
                $queryString .= ' WHERE ';
            }
        } else {
            $queryString .= ' WHERE ';
        }
        $i = 0;
        $len = count($searchParams);
        foreach ($searchParams as $key => $value) {
            $comp = $value['comp'];
            $val = $value['val'];
            if ($val === 'NULL' || $comp === 'NULL') {
                $i++;
            } else {
                if ($val === 'true' || $val === 'false') {
                    $string = $key . ' ' . $comp . ' ' . $dbConnection->real_escape_string($val);
                } else {
                    $string = $key . ' ' . $comp . ' ' . '"' . $dbConnection->real_escape_string($val) . '"';
                }
                if ($i != $len - 1) {
                    $string .= ' AND ';
                }
                $queryString .= $string;
                $i++;
            }
        }
        if ($orderByString) {
            $queryString .= ' ORDER BY ' . $orderByString;
        }
        try {
            $req = $dbConnection->query($queryString);
            $result = $req->fetch_all(MYSQLI_ASSOC);
            $modelsArray = array();
            for ($i = 0; $i < sizeof($result); $i++) {
                $id = $result[$i]['id'];
                $uid = $result[$i]['uid'];
                $inception = $result[$i]['inception'];
                $modelName = static::$targetTableName . static::$auditPostfix;
                $model = new $modelName(array(
                    'id' => $id,
                    'uid' => $uid,
                    'inception' => $inception
                ));
                $cols = $model->getCols();
                foreach ($result[$i] as $key => $value2) {
                    if(!isset($cols[$key])){
                        continue;
                    }
                    $columnType = $cols[$key]['type'];
                    if ($columnType === 'date' || $columnType === 'datetime' || $columnType === 'time') {
                        $convertedTime = GI_Time::formatToUserTime($value2, $columnType);
                        $model->setProperty($key, $convertedTime);
                    } else {
                        $model->setProperty($key, $value2);
                    }
                }

                array_push($modelsArray, $model);
            }
            return $modelsArray;
        } catch (mysqli_sql_exception $e) {
            //TODO: Add Logging
            return NULL;
        }
    }

}
