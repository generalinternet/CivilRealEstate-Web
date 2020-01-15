<?php

class AbstractGenericTabView extends GI_View {
    
    protected $tabClass = array();
    protected $tabLabelClass = array();
    protected $tabContentClass = array();
    protected $current = false;
    protected $tabContent = '';
    protected $buildingTab = false;
    protected $tabURL = NULL;
    protected $tabTitle = '';
    protected $tabHoverTitle = '';
    protected $loadWithAjax = false;
    protected $viewBuilt = false;
    protected $addLabel = true;
    protected $disabled = false;
    protected $tabIndex = 0;

    public function __construct($tabTitle = NULL, $tabURL = NULL, $loadWithAjax = false) {
        if(!empty($tabTitle)){
            $this->setTabTitle($tabTitle);
        }
        
        $this->setTabURL($tabURL);
        
        $this->setLoadWithAjax($loadWithAjax);
        
        parent::__construct();
    }
    
    public function setAddLabel($addLabel){
        $this->addLabel = $addLabel;
        return $this;
    }
    
    public function setTabIndex($tabIndex){
        $this->tabIndex = $tabIndex;
        return $this;
    }
    
    public function setDisabled($disabled){
        $this->disabled = $disabled;
    }
    
    public function setTabURL($tabURL){
        $this->tabURL = $tabURL;
        return $this;
    }
    
    public function setLoadWithAjax($loadWithAjax){
        $this->loadWithAjax = $loadWithAjax;
        return $this;
    }
    
    public function setTabTitle($tabTitle, $tabHoverTitle = NULL){
        $this->tabTitle = $tabTitle;
        if(!empty($tabHoverTitle)){
            $this->setTabHoverTitle($tabHoverTitle);
        } elseif(empty($this->tabHoverTitle)){
            $this->setTabHoverTitle($tabTitle);
        }
        return $this;
    }
    
    public function getTabTitle(){
        return $this->tabTitle;
    }
    
    public function setTabHoverTitle($tabHoverTitle){
        $this->tabHoverTitle = $tabHoverTitle;
        return $this;
    }
    
    public function getTabIndex(){
        return $this->tabIndex;
    }
    
    public function getTabLabel(){
        if(!empty($this->tabURL) && !$this->loadWithAjax){
            $tabLabelTag = 'a';
            $tabLabelAttr = 'href="' . $this->tabURL . '"';
        } else {
            $tabLabelTag = 'div';
            $tabLabelAttr = 'data-content-url="' . $this->tabURL . '"';
        }
        $tabLabelClass = $this->getTabLabelClass();
        
        $tabLabel = '<' . $tabLabelTag . ' class="tab_label ' . $tabLabelClass . '" ' . $tabLabelAttr . ' title="' . $this->tabHoverTitle . '" data-tab-index="' . $this->getTabIndex() . '">';
        $tabLabel .= '<span class="tab_label_text">' . $this->getTabTitle() . '</span>';
        $tabLabel .= '</' . $tabLabelTag . '>';
        return $tabLabel;
    }
    
    public function setCurrent($current){
        $this->current = $current;
        return $this;
    }
    
    public function isCurrent(){
        return $this->current;
    }
    
    public function isDisabled(){
        return $this->disabled;
    }
    
    public function addTabClass($class) {
        if (!in_array($class, $this->tabClass)) {
            $this->tabClass[] = $class;
        }
        return $this;
    }
    
    protected function getTabClass(){
        $tabClass = '';
        if($this->isCurrent()){
            $tabClass = 'current ';
        }
        if($this->isDisabled()){
            $tabClass .= ' disabled';
        }
        if (!empty($this->tabClass)) {
            $tabClass .= implode(' ', $this->tabClass);
        }
        return $tabClass;
    }
    
    public function addTabLabelClass($class) {
        if (!in_array($class, $this->tabLabelClass)) {
            $this->tabLabelClass[] = $class;
        }
        return $this;
    }
    
    protected function getTabLabelClass(){
        $tabLabelClass = '';
        if($this->isCurrent()){
            $tabLabelClass = 'current ';
        }
        if($this->isDisabled()){
            $tabLabelClass .= ' disabled';
        }
        if (!empty($this->tabLabelClass)) {
            $tabLabelClass .= implode(' ', $this->tabLabelClass);
        }
        return $tabLabelClass;
    }
    
    public function addTabContentClass($class) {
        if (!in_array($class, $this->tabContentClass)) {
            $this->tabContentClass[] = $class;
        }
        return $this;
    }
    
    protected function getTabContentClass(){
        $tabContentClass = '';
        if(empty($this->tabContent)){
            $tabContentClass = 'empty ';
        }
        if (!empty($this->tabContentClass)) {
            $tabContentClass .= implode(' ', $this->tabContentClass);
        }
        return $tabContentClass;
    }
    
    protected function buildView(){
        if(!$this->viewBuilt){
            $this->buildingTab = true;
            $tabClass = $this->getTabClass();
            $this->addHTML('<div class="tab ' . $tabClass . '" data-tab-index="' . $this->getTabIndex() . '">');
            if($this->addLabel){
                $this->addHTML($this->getTabLabel());
            }
                $tabContentClass = $this->getTabContentClass();
                $this->addHTML('<div class="tab_content ' . $tabContentClass . '">');
                    $this->addHTML($this->tabContent);
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            $this->buildingTab = false;
        }
        $this->viewBuilt = true;
    }
    
    public function addHTML($html){
        if($this->buildingTab){
            return parent::addHTML($html);
        } else {
            $this->tabContent .= $html;
            return $this;
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function resetHTML() {
        $this->viewBuilt = false;
        return parent::resetHTML();
    }
    
}
