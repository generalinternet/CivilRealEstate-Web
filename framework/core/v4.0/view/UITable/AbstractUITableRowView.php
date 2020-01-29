<?php
/**
 * Description of AbstractUITableRowView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractUITableRowView extends GI_View{
    
    protected $tableCols = array();
    
    public function buildHeader($includeTHead = true){
        if($includeTHead){
            $this->addHTML('<thead>');
        }
        $this->addHTML('<tr>');
                foreach($this->tableCols as $tableCol){
                    $this->buildHeaderCell($tableCol);
                }
        $this->addHTML('</tr>');
        if($includeTHead){
            $this->addHTML('</thead>');
        }
    }
    
    protected function buildHeaderCell($tableCol) {
        $cssHeaderClass = $tableCol['css_header_class'];
        $headerHoverTitle = $tableCol['header_hover_title'];
        $headerAttrString = '';
        if(isset($tableCol['header_other_attr'])){
            $headerOtherAttr = $tableCol['header_other_attr'];
            foreach($headerOtherAttr as $attrName => $attrVal){
                $headerAttrString .= $attrName .'="' . $attrVal . '" ';
            }
        }
        $this->addHTML('<th class="' . $cssHeaderClass . '" title="' . $headerHoverTitle . '" ' . $headerAttrString . '>');
        $headerTitle = $tableCol['header_title'];
        $this->addHTML($headerTitle);
        $this->addHTML('</th>');
    }
    
    protected function buildCell($tableCol, $addReqContent = false) {
        $cssClass = $tableCol['css_class'];
        $cellHoverTitleMethodName = $tableCol['cell_hover_title_method_name'];
        $cellHoverTitle = '';
        if(!empty($cellHoverTitleMethodName)){
            $cellHoverTitle = $this->$cellHoverTitleMethodName();
        }        
        
        $headerTitle = GI_Sanitize::htmlAttribute($tableCol->getHeaderTitle());
        $this->addHTML('<td class="' . $cssClass . '" title="' . $cellHoverTitle . '" data-label="' . $headerTitle . '">');
        if($addReqContent){
            $this->addRequiredContent();
        }
        $methodName = $tableCol['method_name'];
        if (!empty($methodName)) {
            $this->$methodName();
        }
        
        $this->addHTML('</td>');
    }
    
    protected function addRequiredContent(){
        $this->addHTML('');
    }
    
    protected function addRowTag(){
        $this->addHTML('<tr>');
    }
    
    protected function addCloseRowTag(){
        $this->addHTML('</tr>');
    }
    
    public function buildRow(){
        $this->addRowTag();
        $reqContentAdded = false;
        foreach ($this->tableCols as $tableCol) {
            $addReqContent = false;
            if(!$reqContentAdded){
                $addReqContent = true;
                $reqContentAdded = true;
            }
            $this->buildCell($tableCol, $addReqContent);
            
        }
        $this->addCloseRowTag();        
    }
    
}
