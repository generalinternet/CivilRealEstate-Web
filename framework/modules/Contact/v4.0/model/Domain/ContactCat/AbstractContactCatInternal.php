<?php
/**
 * Description of AbstractContactCatInternal
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactCatInternal extends AbstractContactCat {
    
    protected $applicationRequired = false;
    protected $profileRequired = false;
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Internal';
        return $title;
    }

    /**
     * @param GI_Form $form
     * @param AbstractContact $contact
     * @return \AbstractContactCat|boolean
     */
    public function handleFormSubmission(GI_Form $form, AbstractContact $contact) {
        if (!($form->wasSubmitted() && $this->validateForm($form))) {
            return false;
        }
        $this->setPropertiesFromForm($form);
        $this->setProperty('contact_id', $contact->getProperty('id'));
        if (!$this->save()) {
            return false;
        }
        if (!$contact->markAsInternal()) {
            return false;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function softDelete() {
        $contact = $this->getContact();
        if (parent::softDelete()) {
            if (!empty($contact) && !$contact->markAsNotInternal()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function getIsEditable() {
        if (Permission::verifyByRef('mark_contact_as_internal')) {
            return true;
        }
        return parent::getIsEditable();
    }

    public function isQuickbooksExportable() {
        return false;
    }

    public function isInternal() {
        return true;
    }

    public function getProfileDetailView() {
        $contact = $this->getContact();
        return new ContactOrgInternalProfileDetailView($contact);
    }

}
