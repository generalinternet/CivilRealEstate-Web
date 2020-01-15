<?php

class GI_Uploader extends AbstractGI_Uploader{

    protected $description = NULL;
    
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }

    protected function buildView(){
        $formElementClass = 'form_element';

        $this->addHTML('<div id="' . $this->getContainerId() . '" class="' . $formElementClass . ' uploader_container" data-upload-url="' . $this->getUploadURL() . '" data-upload-type="' . $this->getUploadType() . '" data-target-folder-id="' . $this->getTargetFolderId() . '" data-mime-types="' . $this->getMimeTypes() . '" data-uploader-name="' . $this->getUploaderName() . '" data-file-limit="' . $this->fileLimit . '">');
        
        $this->addLabel();

        $this->addDescriptionMessage();
        
        $this->addBtns();

        $this->addFilesArea();

        $this->addVerifyMessage();

        $this->addHTML('</div>');
    }

    protected function addDescriptionMessage(){
        if(empty($this->description)){
            return;
        }

        $this->addHTML('<div class="form_"><i>'.$this->description.'</i></div>');
    }

}
