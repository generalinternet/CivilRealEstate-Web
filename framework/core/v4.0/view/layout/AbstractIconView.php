<?php

abstract class AbstractIconView {
    
    protected $icon = '';
    protected $iconColour = 'primary';
    protected $iconClass = '';
    protected $iconWrapClass = 'circle border';
    protected $addIconWrap = true;
    protected $html = '';
    
    public function __construct($icon) {
        $this->setIcon($icon);
    }
    
    public function getIcon() {
        return $this->icon;
    }

    public function getIconColour() {
        return $this->iconColour;
    }

    public function getIconClass() {
        return $this->iconClass;
    }

    public function getIconWrapClass() {
        return $this->iconWrapClass;
    }

    public function getAddIconWrap() {
        return $this->addIconWrap;
    }

    public function setIcon($icon) {
        $this->icon = $icon;
        return $this;
    }

    public function setIconColour($iconColour) {
        $this->iconColour = $iconColour;
        return $this;
    }

    public function setIconClass($iconClass) {
        $this->iconClass = $iconClass;
        return $this;
    }

    public function setIconWrapClass($iconWrapClass) {
        $this->iconWrapClass = $iconWrapClass;
        return $this;
    }

    public function setAddIconWrap($addIconWrap) {
        $this->addIconWrap = $addIconWrap;
        return $this;
    }
    
    /**
     * @param string $html
     * @return $this
     */
    protected function addHTML($html){
        $this->html .= $html;
        return $this;
    }
    
    protected function buildHTML(){
        $this->openWrap();
            $this->openIcon();
            $this->closeIcon();
        $this->closeWrap();
    }
    
    protected function openWrap(){
        if($this->addIconWrap){
            $this->addHTML('<span class="icon_wrap ' . $this->getIconWrapClass() . '" >');
        }
    }
    
    protected function closeWrap(){
        if($this->addIconWrap){
            $this->addHTML('</span>');
        }
    }
    
    protected function openIcon(){
        $this->addHTML('<span class="icon ' . $this->getIcon() . ' ' . $this->getIconColour() . ' ' . $this->getIconClass() . '" >');
    }
    
    protected function closeIcon(){
        $this->addHTML('</span>');
    }

    public function getHTMLView(){
        $this->buildHTML();
        return $this->html;
    }
    
}
