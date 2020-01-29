<?php
/**
 * Description of AbstractContactRelationship
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactRelationship extends GI_FormRowableModel {
    
    /**
     * Form Submit handler
     * @param type $form
     * @return boolean|$this
     */
    public function handleFormSubmission($form) {
        if (!$form->wasSubmitted() || !$form->validate()) {
            return false;
        }
        if (!$this->setPropertiesFromForm()) {
            return false;
        }
        
        if ($this->save()) {
            return $this;
        }
        return NULL;
    }
    
    /**
     * Form Submit handler multiple rows
     * @param type $form
     * @return boolean|$this
     */
    public function handleRowsFormSubmission($form) {
        if (!$form->wasSubmitted() || !$form->validate()) {
            return false;
        }
        if (!$this->setPropertiesFromForm(true)) {
            return false;
        }
        
        if ($this->save()) {
            return $this;
        }
        return NULL;
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    protected function setPropertiesFromForm($hasMultiRows = false){
        if ($hasMultiRows) {
            $pContactId = filter_input(INPUT_POST, $this->getFieldName('p_contact_id'));
            $cContactId = filter_input(INPUT_POST, $this->getFieldName('c_contact_id'));
            $title = filter_input(INPUT_POST, $this->getFieldName('title'));
        } else {
            $pContactId = filter_input(INPUT_POST, 'p_contact_id');
            $cContactId = filter_input(INPUT_POST, 'c_contact_id');
            $title = filter_input(INPUT_POST, 'title');
        }
        
        $this->setProperty('p_contact_id', $pContactId);
        $this->setProperty('c_contact_id', $cContactId);
        $this->setProperty('title', $title);
        
        return true;
    }

    protected function getIsDeleteable() {
        if (!Permission::verifyByRef('unlink_contacts')) {
            return false;
        }
        if (!parent::getIsDeleteable()) {
            return false;
        }
        $childContact = ContactFactory::getModelById($this->getProperty('c_contact_id'));
        $locationContactType = TypeModelFactory::getTypeModelByRef('loc', 'contact_type');
        if ($childContact->getProperty('contact_type_id') == $locationContactType->getProperty('id')) {
            $parentContact = ContactFactory::getModelById($this->getProperty('p_contact_id'));
            if (!empty($parentContact->getProperty('internal'))) {
                return false;
            }
        }
        return true;
    }

}
