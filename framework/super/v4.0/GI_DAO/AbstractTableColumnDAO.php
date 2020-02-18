<?php
/**
 * Description of AbstractTableColumnDAO
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractTableColumnDAO extends GI_DAO {

    /** @var AbstractTable */
    protected $table = NULL;
    protected $commonColsType = 'full';
    
    protected static $specificCols = array(
        'column_name' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'table_id' => array(
            'type' => 'id',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'type' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'not_null' => array(
            'type' => 'onoff',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'indexed' => array(
            'type' => 'onoff',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'default_val' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0'
        ),
        'fk_subject' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'fk_on_delete' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'fk_on_update' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'fk_display_columns' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
        'label' => array(
            'type' => 'text',
            'not_null' => '0',
            'indexed' => '0',
        ),
    );

    public function __construct($tableName, $paramsArray = NULL) {
        parent::__construct('table_column', $paramsArray);
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

    public function save() {
        if (empty($this->getProperty('id'))) {
            if (!Permission::verifyByRef('add_columns')) {
                return false;
            }
        }
        return parent::save();
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
            $dao = new TableColumnDAO('table_column', $propsArray);
            $dao->setProperties($tempProperties);
            return $dao;
        } catch (Exception $ex) {
            //TODO: Add Logging
            return NULL;
        }
    }
    
    /** @return AbstractTable */
    public function getTable(){
        if(is_null($this->table)){
            $this->table = TableFactory::getModelById($this->getProperty('table_id'));
        }
        return $this->table;
    }
    
    public function alterTable(){
        if(!Permission::verifyByRef('alter_tables')){
            trigger_error('Access denied.');
            return false;
        }
        $columnName = $this->getProperty('column_name');
        $columnType = $this->getProperty('type');
        $defaultVal = $this->getProperty('default_val');
        $notNull = $this->getProperty('not_null');
        $indexed = $this->getProperty('indexed');
        $fkSubject = $this->getProperty('fk_subject');
        
        //get MySQL column type from BOS column type
        $dbColType = dbConfig::getBOSToCol($columnType);
        
        $table = $this->getTable();
        if(!$table){
            trigger_error('Could not find table.');
            return false;
        }
        $tableName = $table->getProperty('system_title');
        $fullTableName = dbConfig::getDbPrefix() . $tableName;
        
        $sql = 'ALTER TABLE ' . $fullTableName . ' ';
        
        $otherTableColumns = TableColumnFactory::search()
                ->filter('column_name', $columnName)
                ->filter('table_id', $table->getId())
                ->filterNotEqualTo('id', $this->getId())
                ->select();
        
        if($otherTableColumns){
            trigger_error('Column [' . $columnName . '] already exists on table [' . $tableName .'].');
            return false;
        }
        
        $realColumnExists = dbConnection::verifyColumnExists($tableName, $columnName);
        
        if($realColumnExists){
            //@todo drop fks
            $sql .= 'CHANGE COLUMN '. $columnName . ' ';
        } else {
            $sql .= 'ADD ';
        }
        
        $sql .= $columnName . ' ' . $dbColType . ' ';
        
        if ($notNull) {
            $sql .= 'NOT ';
        }
        $sql .= 'NULL ';
        
        if (!is_null($defaultVal)) {
            $sql .= 'DEFAULT ' . $defaultVal . ' ';
        }
       
        $dbConnection = dbConnection::getInstance();
        if(empty($dbConnection)){
            return false;
        }

        try {
            $dbConnection->query($sql);
            
            if(!$realColumnExists && empty($fkSubject) && $defaultVal && $notNull){
                $updateSQL = 'UPDATE ' . $fullTableName . ' SET ' . $columnName . ' = ' . $defaultVal;
                $dbConnection->query($updateSQL);
            }
            
            //@todo create fks
            
            if ($indexed) {
                //@todo add index
            } else {
                //@todo drop index if exsits
            }
            
            return true;
        } catch (Exception $ex) {
            trigger_error($ex->getMessage());
            return false;
        }
    }

}
