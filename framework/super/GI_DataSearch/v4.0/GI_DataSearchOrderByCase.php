<?php
/**
 * Description of GI_DataSearchOrderByCase
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class GI_DataSearchOrderByCase{
    
    protected $dataSearch;
    
    /**
     *
     * @var GI_DataSearchCase[]
     */
    protected $cases = array();
    
    public function __construct(GI_DataSearch $dataSearch) {
        $this->dataSearch = $dataSearch;
    }
    
    public function addCase(GI_DataSearchCase $case){
        $this->cases[] = $case;
        return $this;
    }
    
    public function addCases($cases){
        foreach($cases as $case){
            $this->addCase($case);
        }
        return $this;
    }
    
    public function buildCaseString(){
        $caseString = '';
        foreach($this->cases as $case){
            if(!empty($caseString)){
                $caseString .= ' + ';
            }
            $caseString .= '(' . $case->buildCaseString() . ') ';
        }
        return $caseString;
    }
    
}
