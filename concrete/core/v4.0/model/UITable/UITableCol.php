<?php

class UITableCol extends GI_Object {
    
    protected $headerTitle = '';
    protected $methodName = 'getProperty';
    protected $methodAttributes = array();
    protected $cellURLMethodName = '';
    protected $cssClass = '';
    protected $cellHoverTitleMethodName = '';
    protected $headerHoverTitle = '';
    protected $cssHeaderClass = '';
    
    public function __construct() {

    }
    
    function getHeaderTitle() {
        return $this->headerTitle;
    }

    function getMethodName() {
        return $this->methodName;
    }

    function getMethodAttributes() {
        return $this->methodAttributes;
    }

    function getCellURLMethodName() {
        return $this->cellURLMethodName;
    }

    function getCSSClass() {
        return $this->cssClass;
    }
    
    function getCellHoverTitleMethodName(){
        return $this->cellHoverTitleMethodName;
    }
    
    function getHeaderHoverTitle(){
        return $this->headerHoverTitle;
    }
    
    function getCSSHeaderClass(){
        return $this->cssHeaderClass;
    }

    function setHeaderTitle($headerTitle) {
        $this->headerTitle = $headerTitle;
        return $this;
    }

    function setMethodName($methodName) {
        $this->methodName = $methodName;
        return $this;
    }

    function setMethodAttributes($methodAttributes) {
        if(!is_array($methodAttributes)){
            $methodAttributes = array($methodAttributes);
        }
        $this->methodAttributes = $methodAttributes;
        return $this;
    }

    function setCellURLMethodName($cellURLMethodName) {
        $this->cellURLMethodName = $cellURLMethodName;
        return $this;
    }

    function setCSSClass($cssClass) {
        $this->cssClass = $cssClass;
        return $this;
    }
    
    function setCellHoverTitleMethodName($cellHoverTitleMethodName){
        $this->cellHoverTitleMethodName = $cellHoverTitleMethodName;
        return $this;
    }
    
    function setHeaderHoverTitle($headerHoverTitle){
        $this->headerHoverTitle = $headerHoverTitle;
        return $this;
    }
    
    function setHeaderClass($cssHeaderClass){
        $this->cssHeaderClass = $cssHeaderClass;
        return $this;
    }
    
    protected static function buildUITableCol($headerTitle = '', $methodAttributes = '', $methodName = '', $cellURLMethodName = '', $cssClass = '') {
        $uiTableCol = new UITableCol();
        $uiTableCol->setHeaderTitle($headerTitle);
        if (!empty($methodName)) {
            $uiTableCol->setMethodName($methodName);
        }
        if (!empty($methodAttributes)) {
            $uiTableCol->setMethodAttributes($methodAttributes);
        }
        
        $uiTableCol->setCellURLMethodName($cellURLMethodName);
        $uiTableCol->setCssClass($cssClass);
        return $uiTableCol;
    }
    
    public static function buildUITableColFromArray($array) {
        $headerTitle = '';
        $methodAttributes = '';
        $methodName = '';
        $cellURLMethodName = '';
        $cssClass = '';
        $cellHoverTitleMethodName = '';
        $headerHoverTitle = '';
        $cssHeaderClass = '';
        
        if (isset($array['header_title'])) {
            $headerTitle = $array['header_title'];
        }
        if (isset($array['method_attributes'])) {
            $methodAttributes = $array['method_attributes'];
        }
        if (isset($array['method_name'])) {
            $methodName  = $array['method_name'];
        }
        if (isset($array['cell_url_method_name'])) {
            $cellURLMethodName = $array['cell_url_method_name'];
        }
        if (isset($array['css_class'])) {
            $cssClass = $array['css_class'];
        }
        if (isset($array['cell_hover_title_method_name'])) {
            $cellHoverTitleMethodName = $array['cell_hover_title_method_name'];
        }
        if (isset($array['header_hover_title'])) {
            $headerHoverTitle = $array['header_hover_title'];            
        }
        if (isset($array['css_header_class'])) {
            $cssHeaderClass = $array['css_header_class'];
        }
        $uiTableCol = static::buildUITableCol($headerTitle, $methodAttributes, $methodName, $cellURLMethodName, $cssClass)
                ->setCellHoverTitleMethodName($cellHoverTitleMethodName)
                ->setHeaderHoverTitle($headerHoverTitle)
                ->setHeaderClass($cssHeaderClass);
        return $uiTableCol;
    }

}
