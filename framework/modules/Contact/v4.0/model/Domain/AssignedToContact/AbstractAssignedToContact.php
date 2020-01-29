<?php
/**
 * Description of AbstractAssignedToContact
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractAssignedToContact extends GI_Model {
    
    /** @var AbstractContact */
    protected $contact = NULL;
    /** @var AbstractUser */
    protected $user = NULL;
    
    /**
     * Form Submit handler
     * @param type $form
     * @return boolean|$this
     */
    public function handleFormSubmission($form) {
        if (!$form->wasSubmitted() || !$form->validate()) {
            return false;
        }
        $contactId = filter_input(INPUT_POST, 'contact_id');
        $userId = filter_input(INPUT_POST, 'user_id');
        $this->setProperty('contact_id', $contactId);
        $this->setProperty('user_id', $userId);
        
        if ($this->save()) {
            return $this;
        }
        return NULL;
    }
    
    protected function getIsDeleteable() {
        if (!Permission::verifyByRef('unassign_contacts') || $this->getProperty('user_id') == Login::getUserId()) {
            return false;
        }
        $factoryClassName = $this->factoryClassName;
        $deleteable = $factoryClassName::isModelDeleteable($this);
        return $deleteable;
    }
    
    protected function getIsEditable() {
        if (!(Permission::verifyByRef('assign_contacts') && Permission::verifyByRef('unassign_contacts'))) {
            return false;
        }
        return true;
    }
    
    /** @return AbstractContact */
    public function getContact(){
        if(is_null($this->contact)){
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }
    
    /** @return AbstractUser */
    public function getUser(){
        if(is_null($this->user)){
            $this->user = UserFactory::getModelById($this->getProperty('user_id'));
        }
        return $this->user;
    }
    
    public function getContactName(){
        $contact = $this->getContact();
        if($contact){
            return $contact->getName();
        }
        return NULL;
    }
    
    public function getViewTitle($plural = true) {
        $title = '';
        if($this->getId()){
            $title .= $this->getContactName() . ' ';
        }
        $title .= 'Assignment';
        if($plural){
            $title .= 's';
        }
        
        return $title;
    }
    
}
