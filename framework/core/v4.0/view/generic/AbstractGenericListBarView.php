<?php

class AbstractGenericListBarView extends ListWindowView {
    
    protected $windowBtnHTML = '';
    
    protected function addWindowBtns() {
        if($this->windowBtnHTML){
            $this->addHTML($this->windowBtnHTML);
        }
    }
    
    public function addWindowBtnHTML($windowBtnHTML){
        $this->windowBtnHTML .= $windowBtnHTML;
        return $this;
    }
    
}
