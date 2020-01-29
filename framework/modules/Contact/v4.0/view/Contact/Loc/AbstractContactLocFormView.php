<?php
/**
 * Description of AbstractContactLocFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactLocFormView extends AbstractContactFormView {

    /**
     * @var ContactLoc
     */
    protected $contact;
    protected $showTypeField = true;
    protected $addressCountryReadOnly = false;
    
    public function setAddressCountryReadOnly($addressCountryReadOnly) {
        $this->addressCountryReadOnly = $addressCountryReadOnly;
    }

    public function setShowTypeField($showTypeField = true) {
        $this->showTypeField = $showTypeField;
    }

    public function buildFormBody() {
        $this->addContactField();
        $this->addTitleField();
        $this->addTypeField();
    }

    protected function addFormFields() {
        $this->addFormBody();
        $this->addSubmitBtn();
    }

    protected function addFormBody() {
        $this->addAddressFields();
        $internal = NULL;
        if (!empty($this->pId)) {
            $parentContactOrg = ContactFactory::getModelById($this->pId);
            if (!empty($parentContactOrg)) {
                $internal = $parentContactOrg->getProperty('internal');
            }
        } else {
            if (!empty($this->pInternal) && $this->pInternal == 1) {
                $internal = $this->pInternal;
            }
        }
        //Only show Accounting location tag when Contact org is internal
//        if ($internal)  {
//            $this->addAccountingLocationTagField();
//        }
    }

    protected function addAddressFields() {
        $address = $this->contact->getAddressModel($this->form);
        if (!empty($address)) {
            $formView = $address->getLocationAddressFormView($this->form);
            if ($this->addressCountryReadOnly) {
                $formView->setCountryFieldReadOnly(true);
            }
            
            $formView->buildForm();
        }
    }

    protected function addAccountingLocationTagField() {
        if (ProjectConfig::getMultiAccoutingLocations()) {
            $accountingLocTagSearch = TagFactory::search()
                    ->filterByTypeRef('accounting_loc')
                    ->orderBy('id');
            $accountingLocTags = $accountingLocTagSearch->select();
            $options = array();
            if (!empty($accountingLocTags)) {
                foreach ($accountingLocTags as $accountingLocTag) {
                    $options[$accountingLocTag->getProperty('ref')] = $accountingLocTag->getProperty('title');
                }
            }
            $value = NULL;
            $tag = $this->contact->getAccountingLocationTag();
            if (!empty($tag)) {
                $value = $tag->getProperty('ref');
            }
            $this->form->addField('accounting_loc_tag_ref', 'radio', array(
                'options' => $options,
                'value' => $value,
                'displayName' => 'Accounting Territory',
                'required' => true
            ));
        } else {
            $defaultAccountingLocTag = ProjectConfig::getDefaultAccoutingLocationTag();
            $this->form->addField('accounting_loc_tag_ref', 'hidden', array(
                'value'=>$defaultAccountingLocTag->getProperty('ref')
            ));
        }
    }

    protected function addTitleField(){
        $title = $this->contact->getName();
        if(empty($title)){
            $title = $this->startTitle;
        }
        $this->form->addField('loc_name', 'text', array(
            'displayName' => 'Location Title',
            'placeHolder' => 'Title',
            'value' => $title,
            'required' => true
        ));
    }

    protected function addContactField() {
        $internal = false;
        if (!empty($this->pId)) {
            $this->form->addField('p_contact_id', 'hidden', array(
                'value'=>  $this->pId
            ));
            //Get internal value from pId
            $parentContactOrg = ContactFactory::getModelById($this->pId);
            if (!empty($parentContactOrg)) {
                $this->setPInternal($parentContactOrg->getProperty('internal'));
            }
        } else {
            $value = NULL;
            $parentContactOrg = $this->contact->getParentContactOrg();
            if (!empty($parentContactOrg)) {
                $this->pId = $parentContactOrg->getId();
                $this->setPInternal($parentContactOrg->getProperty('internal'));
                $value = $this->pId;
            }

            $contactAutoCompURLParam = array(
                'controller' => 'contact',
                'action' => 'autocompContact',
                'type' => 'org',
                'ajax' => 1,
            );
            if (!empty($this->pInternal)) {
                $contactAutoCompURLParam['internal'] = $this->pInternal;
            }
            $contactAutoCompURL = GI_URLUtils::buildURL($contactAutoCompURLParam);

            $this->form->addField('p_contact_id', 'autocomplete', array(
                'displayName' => 'Contact Organization',
                'placeHolder' => 'start typing...',
                'autocompURL' => $contactAutoCompURL,
                'value' => $value,
                'hideDescOnError' => false,
                'required' => true,
                'autocompMinLength' => 2,
            ));
        }
        if (!$this->contact->getId()) {
            $internal = $this->pInternal;
        } else {
            $internal = $this->contact->getProperty('internal');
        }
        $this->form->addField('internal', 'hidden', array(
            'value' => $internal
        ));
    }

    protected function addTypeField() {
        $addHidden = true;
        if ($this->showTypeField) {
            $options = ContactFactory::getTypesArray('loc');
            if (isset($options['warehouse'])) {
                unset($options['warehouse']);
            }
            if (count($options) > 1) {
                $this->form->addField('type_ref', 'dropdown', array(
                    'displayName' => 'Location Type',
                    'options' => $options,
                    'value' => $this->contact->getTypeRef(),
                    'required' => true,
                    'hideNull' => true
                ));
            }
        }
        if ($addHidden) {
            $this->form->addField('type_ref', 'hidden', array(
                'value' => $this->contact->getTypeRef(),
            ));
        }
    }

}
