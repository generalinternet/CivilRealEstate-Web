<?php
/**
 * Description of AbstractContentTextVideo
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextVideo extends AbstractContentText{
    
    /**
     * @return \ContentTextVideoDetailView
     */
    public function getView() {
        $contentView = new ContentTextVideoDetailView($this);
        return $contentView;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentTextVideoFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentTextVideoFormView($form, $this, $buildForm);
        return $contentFormView;
    }
    
}
