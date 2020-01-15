<?php

class AbstractFileSizedView extends GI_View{
    
    /**
     * @var File
     */
    protected $file;
    protected $fileMissing = false;
    protected $s3URL = NULL;
    protected $width = 640;
    protected $height = 360;
    
    public function __construct(File $file) {
        $this->file = $file;
        $this->s3URL = $this->file->getFileS3URL();
        if(empty($this->s3URL)){
            $this->fileMissing = true;
        }
        parent::__construct();
    }
    
    public function setDimensions($width, $height){
        $this->width = $width;
        $this->height = $height;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function buildView(){
        $wrapClass = '';
        if ($this->fileMissing) {
            $wrapClass = ' missing';
        }
        
        $this->addContent('<span class="sized_img_wrap ' . $wrapClass . '" style="width:' . $this->width . 'px; height:' . $this->height . 'px;">');
        
        if (!$this->fileMissing) {
            $img = $this->file->getResizedImage($this->width, $this->height);
            $this->addContent('<img src="' . $img . '"  alt="' . $this->file->getAltTag() . '" title="' . $this->file->getTitleTag() . '" />');
        }
        
        $this->addContent('</span>');
        
    }
    
}
