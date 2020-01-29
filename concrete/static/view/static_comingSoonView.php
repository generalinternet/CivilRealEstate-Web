<?php

class StaticComingSoonView extends MainWindowView {
    
    protected $comingSoonTitle = 'In Development';
    protected $comingSoonMessage = 'The current page is in development';
    
    public function __construct() {
        parent::__construct();
        $this->setWindowTitle($this->comingSoonTitle);
    }
    
    public function setComingSoonTitle($comingSoonTitle){
        $this->comingSoonTitle = $comingSoonTitle;
        $this->setWindowTitle($this->comingSoonTitle);
        return $this;
    }
    
    public function setComingSoonMessage($comingSoonMessage){
        $this->comingSoonMessage = $comingSoonMessage;
        return $this;
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->addSiteTitle($this->comingSoonTitle);
        $this->addHTML('<div class="gear_wrap">')
               ->addHTML('<div class="gears"></div>')
               ->addHTML('<p>' . $this->comingSoonMessage . '</p>')
               ->addHTML('</div>');
        $this->closePaddingWrap();
    }
    
}
