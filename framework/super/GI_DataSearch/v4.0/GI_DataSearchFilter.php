<?php
/**
 * Description of GI_DataSearchFilter
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
class GI_DataSearchFilter{
    
    /** @var GI_DataSearch */
    protected $dataSearch;
    protected $column;
    protected $comparisonOperator;
    protected $value;
    protected $andValue;
    protected $andValueOperators = array(
        'BETWEEN',
        'NOT BETWEEN'
    );
    protected $connector = 'AND';
    protected $cleanValue = true;
    protected $valueIsColumn = false;
    protected $cleanAndValue = true;
    protected $andValueIsColumn = false;
    protected $betweenOrder = array(
        'C',
        'V',
        'V'
    );
    
    public function __construct(GI_DataSearch $dataSearch, $comparisonOperator = '=') {
        $this->dataSearch = $dataSearch;
        $this->comparisonOperator = $comparisonOperator;
    }
    
    /**
     * @param $column
     * @return \GI_DataSearchFilter
     */
    public function setColumn($column){
        $this->column = $column;
        $this->dataSearch->autoJoinWithColumn($column);        
        return $this;
    }
    
    /**
     * @param $value
     * @return \GI_DataSearchFilter
     */
    public function setValue($value){
        $this->value = $value;
        return $this;
    }
    
    /**
     * @param $andValue
     * @return \GI_DataSearchFilter
     */
    public function setAndValue($andValue){
        if($this->hasAndValue()){
            $this->andValue = $andValue;
        }
        return $this;
    }
    
    /**
     * @param boolean $cleanValue
     * @return \GI_DataSearchFilter
     */
    public function setCleanValue($cleanValue){
        $this->cleanValue = $cleanValue;
        return $this;
    }
    
    /**
     * @param boolean $valueIsColumn
     * @return \GI_DataSearchFilter
     */
    public function setValueIsColumn($valueIsColumn){
        $this->valueIsColumn = $valueIsColumn;
        $this->betweenOrder[1] = 'C';
        return $this;
    }
    
    /**
     * @param boolean $cleanAndValue
     * @return \GI_DataSearchFilter
     */
    public function setCleanAndValue($cleanAndValue){
        $this->cleanAndValue = $cleanAndValue;
        return $this;
    }
    
    /**
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilter
     */
    public function setAndValueIsColumn($andValueIsColumn){
        $this->andValueIsColumn = $andValueIsColumn;
        $this->betweenOrder[2] = 'C';
        return $this;
    }
    
    /**
     * @param array $betweenOrder
     * @return \GI_DataSearchFilter
     */
    public function setBetweenOrder($betweenOrder){
        $this->betweenOrder = $betweenOrder;
        return $this;
    }
    
    protected function hasAndValue(){
        if(in_array($this->comparisonOperator, $this->andValueOperators)){
            return true;
        } else {
            return false;
        }
    }
    
    protected function getColumn(){
        $preparedColumn = $this->dataSearch->prepareColumnName($this->column);
        return $preparedColumn;
    }
    
    public function setConnector($connector){
        $this->connector = $connector;
        return $this;
    }
    
    public function getConnector(){
        return $this->connector;
    }
    
    protected function prepareValue($value, $cleanValue = true){
        if(is_array($value)){
            foreach($value as $key => $val){
                $value[$key] = $this->prepareValue($val);
            }
            $preparedValue = '(' . implode(',', $value) . ')';
        } elseif($cleanValue) {
            $preparedValue = $this->dataSearch->prepareValue($value, $this->column);
        } else {
            $preparedValue = $this->dataSearch->escapeString($value);
        }
        return $preparedValue;
    }
    
    public function buildFilterString(){
        if($this->hasAndValue()){
            return $this->buildBetweenFilterString();
        }
        $filterString = $this->getColumn() . ' ' . $this->comparisonOperator;
        if(!is_null($this->value)){
            $value = $this->value;
            if($this->valueIsColumn){
                $value = $this->dataSearch->prepareColumnName($value);
            }
            $filterString .= ' ' . $this->prepareValue($value, $this->cleanValue);
        }
        if(!is_null($this->andValue)){
            $andValue = $this->andValue;
            if($this->andValueIsColumn){
                $andValue = $this->dataSearch->prepareColumnName($andValue);
            }
            $filterString .= ' AND ' . $this->prepareValue($andValue, $this->cleanAndValue);
        }
        return $filterString;
    }
    
    protected function buildBetweenFilterString(){
        $first = $this->column;
        $second = $this->value;
        $third = $this->andValue;
        
        $filterString = $this->prepareBetweenString($this->betweenOrder[0], $first, $this->cleanValue) . ' ' . $this->comparisonOperator;
        $filterString .= ' ' . $this->prepareBetweenString($this->betweenOrder[1], $second, $this->cleanValue);
        $filterString .= ' AND ' . $this->prepareBetweenString($this->betweenOrder[2], $third, $this->cleanAndValue);
        return $filterString;
    }
    
    protected function prepareBetweenString($type, $string, $cleanValue = true){
        if($type == 'C'){
            return $this->dataSearch->prepareColumnName($string);
        } elseif ($type == 'V'){
            return $this->dataSearch->prepareValue($string, $cleanValue);
        }
    }
    
}
