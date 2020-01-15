<?php
/**
 * Description of AbstractFileAvatarView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class AbstractFileAvatarView extends GI_View{
    
    /**
     * @var File
     */
    protected $file;
    protected $fileMissing = false;
    protected $s3URL = NULL;
    protected $definedWidth = NULL;
    protected $definedHeight = NULL;
    
    public function __construct(File $file) {
        $this->file = $file;
        $this->s3URL = $this->file->getFileS3URL();
        if(empty($this->s3URL)){
            $this->fileMissing = true;
        }
        parent::__construct();
    }
    
    public function setSize($width, $height){
        $this->definedWidth = $width;
        $this->definedHeight = $height;
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function getAvatarImg(){
        if(!empty($this->definedWidth && !empty($this->definedHeight))){
            return $this->file->getResizedImage($this->definedWidth, $this->definedHeight);
        }
        return $this->file->getPredefinedResizedImage('avatar');
    }
    
    protected function buildView(){
        if ($this->fileMissing) {
            $this->addContent('<span class="img_thumb missing"></span>');
        } else {
            $avatarImg = $this->getAvatarImg();
            
            $this->addContent('<span class="img_thumb"><img src="' . $avatarImg . '" alt="' . $this->file->getAltTag() . '" title="' . $this->file->getTitleTag() . '"/></span>');
        }
    }
    
}
