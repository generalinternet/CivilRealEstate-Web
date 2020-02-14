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
    
    protected $isLinkedToBOSAdmin = NULL;
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'My Company';
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

    protected function getIsViewable() {
        if (!(Permission::verifyByRef('view_contacts') || Permission::verifyByRef('view_contact_internals'))) {
            return false;
        }
        if (!empty($this->getId()) && $this->isLinkedToBOSAdmin() && !Permission::verifyByRef('view_bos_admin_contacts')) {
            return false;
        }

        return true;
    }
    
    public function isLinkedToBOSAdmin() {
        if (is_null($this->isLinkedToBOSAdmin)) {
            $search = ContactFactory::search();
            $tableName = $search->prefixTableName('contact');
            
            $search->join('contact_relationship', 'c_contact_id', $tableName, 'id', 'CR');
            $search->join('user', 'id', $tableName, 'source_user_id', 'USER');
            
            $search->filter('CR.p_contact_id', $this->getProperty('contact_id'))
                    ->filter('USER.bos_admin', 1);
            
            $search->filterByTypeRef('ind');
            
            $count = $search->count();
            if (!empty($count)) {
                $this->isLinkedToBOSAdmin = true;
            } else {
                $this->isLinkedToBOSAdmin = false;
            }
        }

        return $this->isLinkedToBOSAdmin;
    }

    public function addCustomFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        parent::addCustomFiltersToDataSearch($dataSearch);

        if (!Permission::verifyByRef('view_bos_admin_contacts')) {
            //This hides the super admin's contact org from the 'internal' index
            $contactTableName = $dataSearch->prefixTableName('contact');
            $dataSearch->join('contact_relationship', 'p_contact_id', $contactTableName, 'id', 'CONRELA');
            $dataSearch->join('contact', 'id', 'CONRELA', 'c_contact_id', 'CONT');
            $dataSearch->join('contact_type', 'id', 'CONT', 'contact_type_id', 'CTYPE');
            $dataSearch->join('user', 'id', 'CONT', 'source_user_id', 'USERTABLE');
            $dataSearch->filter('CTYPE.ref', 'ind')
                    ->filterNotEqualTo('USERTABLE.bos_admin', 1);
        }
    }

}
