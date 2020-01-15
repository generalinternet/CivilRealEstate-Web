<?php
/**
 * Description of GI_DataSearchJoin
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
class GI_DataSearchJoin extends GI_DataSearchFilterable{
    
    /**
     * @var GI_DataSearch
     */
    protected $dataSearch;
    protected $groupConnector = 'AND';
    protected $joinType = 'inner';
    protected $joinTable = NULL;
    protected $as = NULL;
    protected $on = NULL;
    protected $withTable = NULL;
    protected $withColumn = NULL;
    protected $dbJoinTypes = array(
        'inner' => 'INNER JOIN',
        'left' => 'LEFT JOIN',
        'right' => 'RIGHT JOIN',
        'outer' => 'FULL OUTER JOIN'
    );
    
    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $joinType (inner, outer, left, right)
     */
    public function __construct(GI_DataSearch $dataSearch, $table = NULL, $as = NULL, $joinType = 'inner') {
        $this->dataSearch = $dataSearch;
        $this->setJoinType($joinType);
        $this->setJoinTable($table, $as);
        $group = new GI_DataSearchGroup($this->dataSearch);
        $this->curConnector = $this->dataSearch->getConnector();
        $this->groupConnector = $this->curConnector;
        $group->setConnector($this->curConnector);
        $this->mainGroup = $group;
        $this->curGroup = $group;
    }
    
    public function getDataSearch() {
        return $this->dataSearch;
    }
    
    public function setJoinType($joinType){
        $this->joinType = $joinType;
    }
    
    public function setAs($as){
        if(!is_null($as) && !empty($as)){
            $this->as = $as;
        }
        return $this;
    }
    
    public function setJoinTable($table, $as = NULL){
        if(!is_null($table)){
            $this->joinTable = $table;
        }
        $this->setAs($as);
        return $this;
    }
    
    public function getJoinTable($realName = false, $withPrefix = true){
        if(!$realName && !is_null($this->as)){
            return $this->as;
        } else {
            $joinTable = $this->joinTable;
            if($withPrefix){
                return $this->dataSearch->prefixTableName($joinTable);
            }
            return $joinTable;
        }
    }
    
    public function setOn($column){
        $this->on = $column;
        return $this;
    }
    
    public function setWithTable($table){
        $this->withTable = $table;
        return $this;
    }
    
    public function setWithColumn($column){
        $this->withColumn = $column;
        return $this;
    }
    
    public function buildJoinString(){
        $joinString = $this->dbJoinTypes[$this->joinType] . ' ';
        $joinString .= '`' . $this->getJoinTable(true) . '` ';
        $as = $this->as;
        if(!is_null($as)){
            $joinString .= 'AS `'.$as.'` ';
        }
        $joinString .= 'ON `' . $this->getJoinTable() . '`.' . $this->on . ' = ';
        $joinString .= '`' . $this->withTable . '`.' . $this->withColumn . ' ';
        $groupString = $this->mainGroup->buildGroupString();
        if(!empty($groupString)){
            $joinString .= ' ' . $this->groupConnector . ' ' . $groupString;
        }
        return $joinString;        
    }
    
}
