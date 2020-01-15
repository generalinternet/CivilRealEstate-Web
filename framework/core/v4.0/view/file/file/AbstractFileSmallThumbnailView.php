<?php

class AbstractFileSmallThumbnailView extends AbstractFileThumbnailView{
    
    protected $predefinedResizeType = 'small';
    
    protected function getThumbnailClass(){
        $thumbnailClass = parent::getThumbnailClass();
        $thumbnailClass .= ' small';
        return $thumbnailClass;
    }
    
}
