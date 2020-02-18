<?php
/**
 * Description of AbstractTableDAO
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.3
 */
abstract class AbstractTableDAO extends GI_DAO {

    protected static $specificCols = array(
        'title' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'system_title' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'is_core' => array(
            'type' => 'onoff',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'add_base' => array(
            'type' => 'onoff',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'parent_table' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '1'
        ),
        'filter_franchise'=>array(
            'type'=>'onoff',
            'not_null'=>'0',
            'indexed'=>'1'
        )
    );
    protected $columnModels = NULL;
    protected $commonColsType = 'full';

    public function __construct($tableName, $paramsArray = NULL) {
        parent::__construct('table', $paramsArray);
    }

    protected function setTableId() {
        //No table ID for this table
        //Do Nothing
    }

    protected function setCols() {
        $colsTemp = array();
        $commonCols = dbConfig::getCommonColNames();
        foreach($commonCols as $column => $columnInfo){
            $colsTemp[$column] = $columnInfo;
        }
        $tempArray = array_merge($colsTemp, static::$specificCols);
        $this->cols = $tempArray;
    }

    function getColumnModels() {
        return $this->columnModels;
    }

    function setColumnModels($columnModels) {
        $this->columnModels = $columnModels;
    }

    public static function getById($tableName, $id, $dbType = 'client', $status = 1, $commonColsType = 'full') {
        $status = (string) $status;
        if ($status !== '0') {
            $status = '1';
        }
        $dbConnection = dbConnection::getInstance($dbType);
        if(empty($dbConnection)){
            return false;
        }
        $sql = 'SELECT * FROM ' . dbConfig::getDbPrefix($dbType) . $tableName . ' WHERE id=' . $id . ' AND status = ' . $status;
        try {
            $req = $dbConnection->query($sql);
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
            $dao = new TableDAO('table', $propsArray);
            $dao->setProperties($tempProperties);
            return $dao;
        } catch (Exception $ex) {
            //TODO: Add Logging
            return NULL;
        }
    }
    
    public function save() {
       if (empty($this->getProperty('id'))) {
           if (!Permission::verifyByRef('add_tables')) {
               return false;
           }
       } else {
           if (!Permission::verifyByRef('edit_table_row')) {
               return false;
           }
       }
       return parent::save();
    }

}
