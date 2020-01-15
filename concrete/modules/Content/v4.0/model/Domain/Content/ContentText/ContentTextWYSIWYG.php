<?php

class ContentTextWYSIWYG extends AbstractContentTextWYSIWYG {
    public function getTitle(){
        $title = $this->getProperty('title');
        if(!$title){
            return NULL;
        }
        return $title;
    }
}
