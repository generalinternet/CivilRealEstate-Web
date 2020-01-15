<?php

class ContentFileColImage extends AbstractContentFileColImage {
    public function getTitle(){
        $title = $this->getProperty('title');
        if(!$title){
            return NULL;
        }
        return $title;
    }
}
