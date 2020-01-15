<?php

class UserFormView extends AbstractUserFormView {
    
    protected $showContactCatField = false;
    
    protected function openViewBody($class = ''){
        $this->addHTML('<div class="main_body'.$class.' user_edit">');
        return $this;
    }
}
