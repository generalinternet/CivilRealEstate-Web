<?php
/**
 * Description of AbstractSettings
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettings extends GI_Model {
    
    public function getDetailView() {
        return NULL;
    }
    
    public function getFormView(GI_Form $form) {
        return NULL;
    }
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->setPropertiesFromForm($form)) {
                return false;
            }
            
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
           
            return true;
        }
        return false;
    }
    
}