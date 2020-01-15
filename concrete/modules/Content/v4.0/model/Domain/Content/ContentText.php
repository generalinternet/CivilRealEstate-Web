<?php

class ContentText extends AbstractContentText {
    
    public function getTitle(){
        $title = $this->getProperty('title');
        if(!$title){
            return '';
        }
        return $title;
    }

}
