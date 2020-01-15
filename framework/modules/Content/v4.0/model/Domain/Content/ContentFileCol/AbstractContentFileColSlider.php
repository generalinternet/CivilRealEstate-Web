<?php
/**
 * Description of AbstractContentFileColSlider
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileColSlider extends AbstractContentFileCol{
    
    /**
     * @return \ContentFileColSliderDetailView
     */
    public function getView() {
        $contentView = new ContentFileColSliderDetailView($this);
        return $contentView;
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Slider';
        if ($plural) {
            $title .= 's';
        }
        return $title;
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
