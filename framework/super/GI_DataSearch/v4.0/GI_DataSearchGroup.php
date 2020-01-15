<?php
/**
 * Description of GI_DataSearchGroup
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
class GI_DataSearchGroup{
    
    /** @var GI_DataSearch */
    protected $dataSearch;
    protected $connector;
    
    /** @var GI_DataSearchFilter[] */
    protected $filters = array();
    
    /** @var GI_DataSearchGroup[] */
    protected $groups = array();
    
    /** @var GI_DataSearchGroup */
    protected $parentGroup = NULL;
    
    public function __construct(GI_DataSearch $dataSearch, $connector = 'AND') {
        $this->dataSearch = $dataSearch;
        $this->setConnector($connector);
    }
    
    public function __clone() {
        $filters = $this->filters;
        $this->filters = array();
        foreach($filters as $filter){
            $this->filters[] = clone $filter;
        }
        
        $groups = $this->groups;
        $this->groups = array();
        foreach($groups as $group){
            $this->groups[] = clone $group;
        }
    }
    
    public function setConnector($connector){
        $this->connector = $connector;
    }
    
    public function getConnector(){
        return $this->connector;
    }
    
    public function addFilter(GI_DataSearchFilter $filter){
        $this->filters[] = $filter;
    }
    
    public function addGroup(GI_DataSearchGroup $group){
        $this->groups[] = $group;
    }
    
    public function setParentGroup($parentGroup){
        $this->parentGroup = $parentGroup;
    }
    
    public function getParentGroup(){
        return $this->parentGroup;
    }
    
    public function buildGroupString(){
        $groupString = '';
        foreach($this->groups as $group){
            if(!empty($groupString)){
                $groupString .= ' '.$group->getConnector().' ';
            }
            $groupString .= '(' . $group->buildGroupString() . ') ';
        }
        foreach($this->filters as $filter){
            if(!empty($groupString)){
                $groupString .= ' '.$filter->getConnector().' ';
            }
            $groupString .= $filter->buildFilterString().' ';
        }
        return $groupString;
    }
    
}
