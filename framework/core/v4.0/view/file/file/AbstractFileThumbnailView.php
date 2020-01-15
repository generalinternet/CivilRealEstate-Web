<?php

class AbstractFileThumbnailView extends GI_View{
    
    /**
     * @var File
     */
    protected $file;
    
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    protected $displayPreview = true;
    protected $isDeleteable = true;
    protected $isRenamable = true;
    protected $displayIframe = false;
    protected $fileMissing = false;
    protected $s3URL = NULL;
    protected $linkToFile = true;
    protected $predefinedResizeType = 'thumbnail';

    public function __construct(File $file) {
        $this->file = $file;
        $this->s3URL = $this->file->getFileS3URL();
        if(empty($this->s3URL)){
            $this->fileMissing = true;
        }
        parent::__construct();
    }
    
    public function setUploader(AbstractGI_Uploader $uploader = NULL){
        $this->uploader = $uploader;
        return $this;
    }

    public function setIsDeleteable($isDeleteable) {
        $this->isDeleteable = $isDeleteable;
        return $this;
    }
    
    public function setIsRenamable($isRenamable) {
        $this->isRenamable = $isRenamable;
        return $this;
    }

    public function setDisplayPreview($displayPreview) {
        $this->displayPreview = $displayPreview;
        return $this;
    }

    public function setDisplayIframe($displayIframe) {
        $this->displayIframe = $displayIframe;
        return $this;
    }
    
    public function setLinkToFile($linkToFile){
        $this->linkToFile = $linkToFile;
        return $this;
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function openFileWrap(){
        $fileId = $this->file->getProperty('id');
        $this->addHTML('<div class="file_wrap" id="file_' . $fileId . '" data-id="' . $fileId . '">');
    }
    
    protected function closeFileWrap(){
        $this->addHTML('</div>');
    }
    
    protected function getThumbnailClass(){
        $ext = $this->file->getExtension();
        
        $thumbnailClass = File::getFileTypeClass($ext);
        
        if ($this->fileMissing) {
            $thumbnailClass = 'missing';
        }

        if ($this->displayIframe) {
            $thumbnailClass .= ' iframed';
        }
        
        if(!$this->file->getProperty('attached')){
            $thumbnailClass .= ' temp';
        }
        
        return $thumbnailClass;
    }
    
    protected function openThumbnailWrap(){
        $fileBasename = $this->file->getProperty('file.display_name');
        $fileParts = pathinfo($fileBasename);
        $fileName = $fileParts['filename'];

        $thumbnailClass = $this->getThumbnailClass();
        
        if ($this->linkToFile) {
            $this->addHTML('<a href="' . $this->s3URL . '" class="file_thumb ' . $thumbnailClass . '" target="_blank" title="' . GI_Sanitize::htmlAttribute($fileBasename) . '" >');
        } else {
            $this->addHTML('<span class="file_thumb ' . $thumbnailClass . '" title="' . GI_Sanitize::htmlAttribute($fileName) . '">');
        }
    }
    
    protected function closeThumbnailWrap(){
        if ($this->linkToFile) {
            $this->addHTML('</a>');
        } else {
            $this->addHTML('</span>');
        }
    }
    
    protected function addThumbnail(){
        $fileBasename = $this->file->getProperty('file.display_name');
        $fileParts = pathinfo($fileBasename);
        $fileName = $fileParts['filename'];
        
        if ($this->displayPreview) {
            $thumbnail = $this->file->getPredefinedResizedImage($this->predefinedResizeType);
            if($thumbnail){
                $this->addHTML('<span class="img_thumb"><img src="' . $thumbnail . '" alt="' . GI_Sanitize::htmlAttribute($fileName) . '" /></span>');
            }
        }

        if ($this->displayIframe) {
            $this->addHTML('<span class="overlay"></span>');
            $this->addHTML('<iframe src="' . $this->s3URL . '" ></iframe>');
        }
    }

    protected function buildView() {
        if($this->fileMissing){
            $this->setDisplayIframe(false);
            $this->setDisplayPreview(false);
            $this->setLinkToFile(false);
        }
        
        $this->openFileWrap();
        
        $fileBasename = $this->file->getProperty('file.display_name');
        $fileParts = pathinfo($fileBasename);
        $ext = $fileParts['extension'];
        $fileName = $fileParts['filename'];
        $shortName = GI_StringUtils::summarize($fileName, 14);

        $this->openThumbnailWrap();
        
        $this->addBtns();
        
        $this->addHTML('<span class="ext"><span class="ext_icon"></span><span class="ext_title">' . $ext . '</span></span>');
        $this->addHTML('<span class="filename">' . $shortName . '</span>');
        $this->addHTML('<span class="corner"></span>');
        
        $this->addThumbnail();
        
        $this->addField();
        
        $this->closeThumbnailWrap();
        
        $this->closeFileWrap();
    }
    
    protected function addBtns(){
        $fileId = $this->file->getProperty('id');
        
        if ($this->isDeleteable) {
            $this->addHTML('<span class="remove_file" data-file-id="' . $fileId . '"></span>');
        }
        
        if($this->isRenamable){
            $editFileURLProps = array(
                'controller' => 'file',
                'action' => 'edit',
                'id' => $fileId
            );
            if($this->uploader){
                $editFileURLProps['uploader'] = $this->uploader->getUploaderName();
            }
            $editFileURL = GI_URLUtils::buildURL($editFileURLProps);
            $this->addHTML('<span data-url="' . $editFileURL . '" class="edit_file" title="Edit File" ></span>');
        }
    }
    
    protected function addField(){
        $fileId = $this->file->getProperty('id');
        if($this->uploader){
            $this->addHTML('<input type="hidden" value="' . $fileId .'" name="' . $this->uploader->getUploaderName() . '_files[]" />');
        }
    }
    
}
