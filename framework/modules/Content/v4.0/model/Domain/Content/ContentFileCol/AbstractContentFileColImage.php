<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentFileColImage extends AbstractContentFileCol {
    
    /**
     * Get a detail view
     * @return \ContentFileColImageDetailView
     */
    public function getView() {
        $contentView = new ContentFileColImageDetailView($this);
        return $contentView;
    }
    
    /**
     * Get a form view
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentFileColFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentFileColImageFormView($form, $this, false);
        $uploader = $this->getUploader($form);
        $contentFormView->setUploader($uploader);
        if($buildForm){
            $contentFormView->buildForm();
        }
        return $contentFormView;
    }
    
    /**
     * Override getUploader
     * @param type $form
     * @return type
     */
    protected function getUploader(GI_Form $form = NULL){
        $uploader = parent::getUploader($form);
        if(!$uploader){
            return NULL;
        }
        $uploader->setFilesLabel('Image');
        $uploader->setMimeTypes('imgs');
        $uploader->setBrowseLabel('Upload Image');
        return $uploader;
    }
    
    /** Get a content value **/
    public function getContent() {
        return $this->getProperty('content_file_col.content');
    }
}
