<?php

class AbstractGenericOverlayGridView extends GI_View {
    
    
    protected $gridContent = '';
    protected $targetRef = '';
    protected $gridIcon = 'info';
    protected $gridIconWidth = '50px';
    protected $gridIconHeight = '50px';
    protected $gridTitle = '';
    protected $gridHoverTitle = '';
    protected $gridIndex = 0;
    protected $gridIconClass = array();
    protected $gridContentClass = array();

    public function __construct($gridTitle = NULL, $targetRef = NULL, $gridIcon = NULL, $gridIconWidth = NULL, $gridIconHeight = NULL) {
        if(!empty($gridTitle)){
            $this->setGridTitle($gridTitle);
        }
        if(!empty($targetRef)){
            $this->setTargetRef($targetRef);
        }
        
        if(!empty($gridIcon)){
            $this->setGridIcon($gridIcon);
        }
        
        if(!empty($gridIconWidth) && !empty($gridIconHeight)){
            $this->setGridIconSize($gridIconWidth, $gridIconHeight);
        }
         
        parent::__construct();
    }
    
    public function setGridIndex($gridIndex){
        $this->gridIndex = $gridIndex;
        return $this;
    }
    
    public function setTargetRef($targetRef){
        $this->targetRef = $targetRef;
        return $this;
    }
    
    public function setGridIcon($gridIcon){
        $this->gridIcon = $gridIcon;
        return $this;
    }
    
    public function setGridIconSize($gridIconWidth, $gridIconHeight){
        $this->gridIconWidth = $gridIconWidth;
        $this->gridIconHeight = $gridIconHeight;
        return $this;
    }
    
    public function addGridIconClass($class) {
        if (!in_array($class, $this->gridIconClass)) {
            $this->gridIconClass[] = $class;
        }
        return $this;
    }

    public function setGridTitle($gridTitle, $gridHoverTitle = NULL){
        $this->gridTitle = $gridTitle;
        if(!empty($gridHoverTitle)){
            $this->setGridHoverTitle($gridHoverTitle);
        } else {
            $this->setGridHoverTitle($gridTitle);
        }
        return $this;
    }
    
    public function getGridTitle(){
        return $this->gridTitle;
    }
    
    public function setGridHoverTitle($gridHoverTitle){
        $this->gridHoverTitle = $gridHoverTitle;
        return $this;
    }
    
    public function getGridIndex(){
        return $this->gridIndex;
    }
    
    public function addGridContentClass($class) {
        if (!in_array($class, $this->gridContentClass)) {
            $this->gridContentClass[] = $class;
        }
        return $this;
    }
    
    protected function getGridContentClass(){
        $gridContentClass = '';
        if (!empty($this->gridContentClass)) {
            $gridContentClass .= implode(' ', $this->gridContentClass);
        }
        return $gridContentClass;
    }
    
    protected function getGridIconClass(){
        $gridIconClass = '';
        if (!empty($this->gridIconClass)) {
            $gridIconClass .= implode(' ', $this->gridIconClass);
        }
        return $gridIconClass;
    }
    
    protected function buildView(){
        $gridContentClass = $this->getGridContentClass();
        $gridIconClass = $this->getGridIconClass();
        $gridTitle = $this->gridTitle;
        $this->addHTML('<a class="overlay_grid '.$gridContentClass.'" data-ref="'.$this->targetRef.'">');
            $this->addHTML('<span class="overlay_grid_box">');
                $this->addHTML(GI_StringUtils::getSVGIcon($this->gridIcon, $this->gridIconWidth, $this->gridIconHeight, $gridIconClass));
                $this->addHTML('<span class="btn_text">'.$gridTitle.'</span>');
            $this->addHTML('</span>');
        $this->addHTML('</a>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
