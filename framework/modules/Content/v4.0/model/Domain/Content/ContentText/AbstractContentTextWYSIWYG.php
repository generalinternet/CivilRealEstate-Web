<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextWYSIWYG extends AbstractContentText {
    /**
     * Get a detail view
     * @return \ContentTextWYSIWYGDetailView
     */
    public function getView() {
        $contentView = new ContentTextWYSIWYGDetailView($this);
        return $contentView;
    }
    
    /**
     * Get a form view
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentTextWYSIWYGFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentTextWYSIWYGFormView($form, $this, $buildForm);
        return $contentFormView;
    }
    
}
