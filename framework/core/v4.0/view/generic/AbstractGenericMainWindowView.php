<?php

class AbstractGenericMainWIndowView extends MainWindowView {
    
    protected $viewBodyHTML = '';
    
    protected function addViewBodyContent(){
        parent::addViewBodyContent();
        $this->openPaddingWrap();
        $this->addHTML($this->viewBodyHTML);
        $this->closePaddingWrap();
        return $this;
    }
    
    public function addViewBodyHTML($html){
        $this->viewBodyHTML .= $html;
        return $this;
    }
    
}
