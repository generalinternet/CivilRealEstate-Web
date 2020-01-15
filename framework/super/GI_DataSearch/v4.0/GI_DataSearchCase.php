<?php
/**
 * Description of GI_DataSearchCase
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.2
 */
class GI_DataSearchCase extends GI_DataSearchFilterable{
    
    /**
     * @var GI_DataSearch
     */
    protected $dataSearch;
    protected $then = '';
    protected $else = '';
    
    public function __construct(GI_DataSearch $dataSearch) {
        //@todo create parent abstract class that both case + datasearch can use for the grouping/filtering management
        $this->dataSearch = $dataSearch;
        $group = new GI_DataSearchGroup($this->dataSearch);
        $this->curConnector = $this->dataSearch->getConnector();
        $group->setConnector($this->curConnector);
        $this->mainGroup = $group;
        $this->curGroup = $group;
    }
    
    public function getDataSearch() {
        return $this->dataSearch;
    }
    
    public function setThen($then){
        $this->then = $then;
        return $this;
    }
    
    public function setElse($else){
        $this->else = $else;
        return $this;
    }
    
    public function buildCaseString(){
        $caseString = 'CASE WHEN ';
        $caseString .= $this->mainGroup->buildGroupString();
        $caseString .= 'THEN ' . $this->then . ' ';
        $caseString .= 'ELSE ' . $this->else . ' ';
        $caseString .= 'END ';
        return $caseString;
    }
    
}
