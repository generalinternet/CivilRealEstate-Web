<?php

class LoginLayoutView extends AbstractLoginLayoutView {
    
    protected function addDefaultCSS() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic');
        parent::addDefaultCSS();
    }
    
}
