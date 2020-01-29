<?php
/**
 * Description of AbstractContentFileColGallery
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileColGallery extends AbstractContentFileCol{
    
    /**
     * @return \ContentFileColGalleryDetailView
     */
    public function getView() {
        $contentView = new ContentFileColGalleryDetailView($this);
        return $contentView;
    }
    
    protected function getUploader(GI_Form $form = NULL){
        $uploader = parent::getUploader($form);
        if(!$uploader){
            return NULL;
        }
        $uploader->setFilesLabel('Images');
        $uploader->setMimeTypes('imgs');
        $uploader->setBrowseLabel('Upload Images');
        return $uploader;
    }
    
}
