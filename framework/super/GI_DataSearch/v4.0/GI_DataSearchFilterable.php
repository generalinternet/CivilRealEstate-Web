<?php
/**
 * Description of GI_DataSearchFilterable
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class GI_DataSearchFilterable{
    
    /** @var GI_DataSearchGroup */
    protected $curGroup = NULL;
    /** @var GI_DataSearchGroup */
    protected $mainGroup = NULL;
    protected $curConnector = 'AND';
    
    public function __clone() {
        $this->mainGroup = clone $this->mainGroup;
        $this->curGroup = $this->mainGroup;
    }
    
    /**
     * Returns the current group filters are being added to
     * 
     * @return \GI_DataSearchGroup
     */
    protected function getCurrentGroup(){
        if(is_null($this->curGroup)){
            $this->filterGroup();
        }
        
        return $this->curGroup;
    }
    
    /**
     * @return \GI_DataSearchFilterable
     */
    public function filterGroup(){
        $group = new GI_DataSearchGroup($this->getDataSearch());
        $group->setConnector($this->curConnector);
        if(is_null($this->mainGroup)){
            $this->mainGroup = $group;
        } elseif(!is_null($this->curGroup)){
            $this->curGroup->addGroup($group);
            $group->setParentGroup($this->curGroup);
        }
        $this->curGroup = $group;
        return $this;
    }
    
    /**
     * @return \GI_DataSearchFilterable
     */
    public function closeGroup(){
        $curGroup = $this->getCurrentGroup();
        $parentGroup = $curGroup->getParentGroup();
        $this->curGroup = $parentGroup;
        return $this;
    }
    
    /**
     * Adds the provided filter into the current group
     * 
     * @param GI_DataSearchFilter $filter the filter to add to a group
     * @return \GI_DataSearchFilterable
     */
    public function addFilterToGroup(GI_DataSearchFilter $filter){
        $group = $this->getCurrentGroup();
        $group->addFilter($filter);
        return $this;
    }
    
    public function andIf(){
        $this->curConnector = 'AND';
        return $this;
    }
    
    public function orIf(){
        $this->curConnector = 'OR';
        return $this;
    }
    
    public function getConnector(){
        return $this->curConnector;
    }
    
    /**
     * @return \GI_DataSearch
     */
    public abstract function getDataSearch();
    
    /**
     * Adds a new filter to the data search
     * 
     * @param string $column
     * @param mixed $value
     * @param string $comp
     * @param mixed $andValue
     * @return \GI_DataSearchFilter
     */
    public function createFilter($column, $value = NULL, $comp = '=', $andValue = NULL){
        $filter = new GI_DataSearchFilter($this->getDataSearch(), $comp);
        $filter->setConnector($this->curConnector);
        $filter->setColumn($column);
        if(!is_null($value)){
            $filter->setValue($value);
        }
        if(!is_null($andValue)){
            $filter->setAndValue($andValue);
        }
        return $filter;
    }
    
    /**
     * Adds a new filter to the data search
     * 
     * @param string $column
     * @param mixed $value
     * @param string $comp
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filter($column, $value = NULL, $comp = '=', $andValue = NULL){
        $filter = $this->createFilter($column, $value, $comp, $andValue);
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $column = "$value"
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterEqualTo($column, $value){
        return $this->filter($column, $value);
    }
    
    /**
     * $column IS NULL
     * 
     * @param string $column
     * @return \GI_DataSearchFilterable
     */
    public function filterNull($column){
        return $this->filter($column, NULL, 'IS NULL');
    }
    
    /**
     * $column IS NOT NULL
     * 
     * @param string $column
     * @return \GI_DataSearchFilterable
     */
    public function filterNotNull($column){
        return $this->filter($column, NULL, 'IS NOT NULL');
    }
    
    /**
     * @param string $column
     * @param mixed $orValue
     * @return \GI_DataSearchFilterable
     */
    public function filterNullOr($column, $orValue = 0){
        $connector = $this->curConnector;
        $this->filterGroup()
                ->filterNull($column)
                ->orIf()
                ->filter($column, $orValue)
                ->closeGroup();
        $this->curConnector = $connector;
        return $this;
    }
    
    /**
     * @param string $column
     * @param mixed $orValue
     * @return \GI_DataSearchFilterable
     */
    public function filterNotNullOr($column, $orValue = 1){
        $connector = $this->curConnector;
        $this->filterGroup()
                ->filterNotNull($column)
                ->orIf()
                ->filter($column, $orValue)
                ->closeGroup();
        $this->curConnector = $connector;
        return $this;
    }
    
    /**
     * $column < $value
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterLessThan($column, $value){
        return $this->filter($column, $value, '<');
    }
    
    /**
     * $column > $value
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterGreaterThan($column, $value){
        return $this->filter($column, $value, '>');
    }
    
    /**
     * $column <= $value
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterLessOrEqualTo($column, $value){
        return $this->filter($column, $value, '<=');
    }
    
    /**
     * $column >= $value
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterGreaterOrEqualTo($column, $value){
        return $this->filter($column, $value, '>=');
    }
    
    /**
     * $column != $value
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterNotEqualTo($column, $value){
        return $this->filter($column, $value, '!=');
    }
    
    /**
     * $column BETWEEN $value AND $andValue
     * 
     * @param string $column
     * @param mixed $value
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterBetween($column, $value, $andValue){
        return $this->filter($column, $value, 'BETWEEN', $andValue);
    }
    
    /**
     * $column NOT BETWEEN $value AND $andValue
     * 
     * @param string $column
     * @param mixed $value
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetween($column, $value, $andValue){
        return $this->filter($column, $value, 'NOT BETWEEN', $andValue);
    }
    
    /**
     * $column LIKE "$value"
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterLike($column, $value){
        return $this->filter($column, $value, 'LIKE');
    }
    
    /**
     * $column NOT LIKE "$value"
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterNotLike($column, $value){
        return $this->filter($column, $value, 'NOT LIKE');
    }
    
    /**
     * $column SOUNDS LIKE "$value"
     * 
     * @param string $column
     * @param mixed $value
     * @return \GI_DataSearchFilterable
     */
    public function filterSoundsLike($column, $value){
        return $this->filter($column, $value, 'SOUNDS LIKE');
    }
    
    /**
     * $column IN ($values[0], $values[1], $values[2], ...)
     * 
     * @param string $column
     * @param array $values
     * @return \GI_DataSearchFilterable
     */
    public function filterIn($column, $values){
        return $this->filter($column, $values, 'IN');
    }
    
    /**
     * $column NOT IN ($values[0], $values[1], $values[2], ...)
     * 
     * @param string $column
     * @param array $values
     * @return \GI_DataSearchFilterable
     */
    public function filterNotIn($column, $values){
        return $this->filter($column, $values, 'NOT IN');
    }
    
    public function filterTermsLike($columns, $terms, $includeSoundsLike = false){
        if(!is_array($columns)){
            $columns = explode(',',$columns);
        }
        
        if(!is_array($terms)){
            $terms = explode(' ',$terms);
        }
        
        $fullTerm = implode(' ', $terms);
        
        if($includeSoundsLike){
            $this->filterGroup();
        }
        
        $termCount = count($terms);
        if($termCount > 1){
            $this->filterGroup();
        }
        
        foreach($terms as $term){
            $this->filterGroup();
                foreach($columns as $column){
                    $this->filterLike($column, '%' . $term . '%')
                            ->orIf();
                }
            $this->closeGroup()
                    ->andIf();
        }
        
        if($termCount > 1){
            $this->closeGroup();
        }
        
        if($includeSoundsLike){
            $this->orIf();

            foreach($columns as $column){
                $this->filterSoundsLike($column, $fullTerm);
            }

            $this->closeGroup()
                    ->andIf();
        }
        
        return $this;
    }
    
    /**
     * Adds a new filter to the data search
     * 
     * @param string $column
     * @param mixed $column2
     * @param string $comp
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterWithColumn($column, $column2 = NULL, $comp = '=', $andValue = NULL, $andValueIsColumn = false){
        $filter = $this->createFilter($column, $column2, $comp, $andValue);
        $filter->setCleanValue(false);
        $filter->setValueIsColumn(true);
        if($andValueIsColumn){
            $filter->setCleanAndValue(false);
            $filter->setAndValueIsColumn(true);
        }
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $column = $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterEqualToWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2);
    }
    
    /**
     * $column < $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterLessThanWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2, '<');
    }
    
    /**
     * $column > $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterGreaterThanWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2, '>');
    }
    
    /**
     * $column <= $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterLessOrEqualToWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2, '<=');
    }
    
    /**
     * $column >= $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterGreaterOrEqualToWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2, '>=');
    }
    
    /**
     * $column != $column2
     * 
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterNotEqualToWithColumn($column, $column2){
        return $this->filterWithColumn($column, $column2, '!=');
    }
    
    /**
     * $column BETWEEN $column2 AND $andValue
     * 
     * @param string $column
     * @param string $column2
     * @param mixed $andValue
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenWithColumn($column, $column2, $andValue, $andValueIsColumn = false){
        return $this->filterWithColumn($column, $column2, 'BETWEEN', $andValue, $andValueIsColumn);
    }
    
    /**
     * $column NOT BETWEEN $column2 AND $andValue
     * 
     * @param string $column
     * @param string $column2
     * @param mixed $andValue
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenWithColumn($column, $column2, $andValue, $andValueIsColumn = false){
        return $this->filterWithColumn($column, $column2, 'NOT BETWEEN', $andValue, $andValueIsColumn);
    }
    
    /**
     * $column BETWEEN $value AND $andValue
     * 
     * @param string $column
     * @param mixed $value
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenCVV($column, $value, $andValue){
        return $this->filterBetween($column, $value, $andValue);
    }
    
    /**
     * $column NOT BETWEEN $value AND $andValue
     * 
     * @param string $column
     * @param mixed $value
     * @param mixed $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenCVV($column, $value, $andValue){
        return $this->filterBetween($column, $value, $andValue);
    }
    
    /**
     * $column BETWEEN $column2 AND $andValue
     * 
     * @param string $column
     * @param string $column2
     * @param mixed $andValue
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenCCV($column, $column2, $andValue){
        return $this->filterBetweenWithColumn($column, $column2, $andValue);
    }
    
    /**
     * $column NOT BETWEEN $column2 AND $andValue
     * 
     * @param string $column
     * @param string $column2
     * @param mixed $andValue
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenCCV($column, $column2, $andValue){
        return $this->filterNotBetweenWithColumn($column, $column2, $andValue);
    }
    
    /**
     * $column BETWEEN $column2 AND $column3
     * 
     * @param string $column
     * @param string $column2
     * @param string $column3
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenCCC($column, $column2, $column3){
        return $this->filterBetweenWithColumn($column, $column2, $column3, true);
    }
    
    /**
     * $column NOT BETWEEN $column2 AND $column3
     * 
     * @param string $column
     * @param string $column2
     * @param string $column3
     * @param boolean $andValueIsColumn
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenCCC($column, $column2, $column3){
        return $this->filterNotBetweenWithColumn($column, $column2, $column3);
    }
    
    /**
     * $column BETWEEN $value AND $column2
     * 
     * @param mixed $column
     * @param string $value
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenCVC($column, $value, $column2){
        $filter = $this->createFilter($column, $value, 'BETWEEN', $column2);
        $filter->setBetweenOrder(array('C', 'V', 'C'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $column NOT BETWEEN $value AND $column2
     * 
     * @param mixed $column
     * @param string $value
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenCVC($column, $value, $column2){
        $filter = $this->createFilter($column, $value, 'NOT BETWEEN', $column2);
        $filter->setBetweenOrder(array('C', 'V', 'C'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $value BETWEEN $column AND $column2
     * 
     * @param mixed $value
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenVCC($value, $column, $column2){
        $filter = $this->createFilter($value, $column, 'BETWEEN', $column2);
        $filter->setBetweenOrder(array('V', 'C', 'C'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $value NOT BETWEEN $column AND $column2
     * 
     * @param mixed $value
     * @param string $column
     * @param string $column2
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenVCC($value, $column, $column2){
        $filter = $this->createFilter($value, $column, 'NOT BETWEEN', $column2);
        $filter->setBetweenOrder(array('V', 'C', 'C'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $value BETWEEN $column AND $andValue
     * 
     * @param mixed $value
     * @param string $column
     * @param string $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterBetweenVCV($value, $column, $andValue){
        $filter = $this->createFilter($value, $column, 'BETWEEN', $andValue);
        $filter->setBetweenOrder(array('V', 'C', 'V'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
    /**
     * $value NOT BETWEEN $column AND $andValue
     * 
     * @param mixed $value
     * @param string $column
     * @param string $andValue
     * @return \GI_DataSearchFilterable
     */
    public function filterNotBetweenVCV($value, $column, $andValue){
        $filter = $this->createFilter($value, $column, 'NOT BETWEEN', $andValue);
        $filter->setBetweenOrder(array('V', 'C', 'V'));
        $this->addFilterToGroup($filter);
        return $this;
    }
    
}
