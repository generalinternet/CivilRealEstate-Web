<?php

class File extends AbstractFile {
    
    public function getFeaturedView($width = '386', $height = '275'){
        $view = new AbstractFileSizedView($this);
        $view->setDimensions($width, $height);
        return $view;
    }
    public function getView($type = 'thumbnail', AbstractGI_Uploader $uploader = NULL){
        switch($type){
            case 'featured':
                $view = $this->getFeaturedView();
                break;
            case 'featured_16_9':
                $width = 792;
                $height = 446;
                $view = $this->getFeaturedView($width, $height);
                break;
            default:
                $view = parent::getView($type, $uploader);
                break;
        }
        return $view;
    }
}
