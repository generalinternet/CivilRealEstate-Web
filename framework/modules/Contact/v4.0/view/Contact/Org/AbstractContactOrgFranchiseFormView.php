<?php

/**
 * Description of AbstractContactOrgFranchiseFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactOrgFranchiseFormView extends AbstractContactOrgFormView {

    /** @var AbstractContactInd */
    protected $franchisePrimaryOwnerContact;

    /** @var AbstractUser */
    protected $franchisePrimaryOwnerUser;
    
    protected $addFranchiseOwnerSection = true;

    public function __construct(GI_Form $form, AbstractContact $contact) {
        parent::__construct($form, $contact);
        $franchiseOwner = $this->contact->getFranchisePrimaryOwner();
        if (empty($franchiseOwner)) {
            $franchiseOwner = ContactFactory::buildNewModel('ind');
        }
        $user = $franchiseOwner->getUser();
        if (empty($user)) {
            $user = UserFactory::buildNewModel('user');
        }
        $this->franchisePrimaryOwnerContact = $franchiseOwner;
        $this->franchisePrimaryOwnerUser = $user;
    }
    
    public function setAddFranchiseOwnerSection($addFranchiseOwnerSection) {
        $this->addFranchiseOwnerSection = $addFranchiseOwnerSection;
    }

    public function buildFormHeader() {
        AbstractContactFormView::buildFormHeader();
        if ($this->ajax) {
            $this->buildAjaxForm();
            return;
        }
        $this->form->addHTML('<div class="flex_row">');
        $this->addNameFields();
        $this->addDefaultCurrencyField();
        $this->addInterestRatesField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<br />');
    }

    protected function addFormFields() {
        if (!$this->ajax) {
            $this->addContactInfoForms();

            $this->addUploaders();
            if (Permission::verifyByRef('add_franchises') || Permission::verifyByRef('edit_franchises')) {
                $this->addFranchiseOwnerSection();
            }
            
        }
        $this->addSubmitBtn();
    }

    protected function addNameFields() {
        $this->form->addHTML('<div class="flex_col">');
                $this->form->addField('title', 'text', array(
            'displayName' => 'Title',
            'placeHolder' => 'Title',
            'value' => $this->contact->getProperty('contact_org.title'),
            'required' => true
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
                $this->form->addField('doing_bus_as', 'text', array(
            'displayName' => 'Doing Business As',
            'placeHolder' => 'Doing Business As',
            'value' => $this->contact->getProperty('contact_org.doing_bus_as'),
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col sml">');
        $this->addColourField();
        
        $this->form->addHTML('</div>');
    }

    protected function addDefaultCurrencyField() {
        if (empty($this->contact->getProperty('id'))) {
            $value = ProjectConfig::getDefaultCurrencyId();
        } else {
            $value = $this->contact->getProperty('default_currency_id');
        }
        $this->form->addHTML('<div class="flex_col med">');
        $overwriteSettings = array();
        if ($this->contact->hasBeenExportedToQuickbooks()) {
            $overwriteSettings['readOnly'] = true;
        }
        $this->form->addDefaultCurrencyDropdownOrField($value, 'default_currency_id', $overwriteSettings);
        $this->form->addHTML('</div>');
    }

    protected function addInterestRatesField() {
        $this->form->addField('use_default_rate', 'hidden', array(
            'value' => 1,
        ));
    }

    protected function addFranchiseOwnerSection() {
        if ($this->addFranchiseOwnerSection) {
            $this->form->addField('add_owner_section', 'hidden', array(
                'value'=>1,
            ));
            $this->form->addHTML('<hr />');
            $this->form->addHTML('<h3>Owner Details</h3>');
            $contactId = $this->contact->getId();
            if (empty($contactId)) {
                $this->form->addHTML('<div class="auto_columns quarters">');
                $this->form->addField('existing_owner', 'radio', array(
                    'displayName' => 'Select Existing Owner?',
                    'required' => true,
                    'options' => array(
                        1 => 'Yes',
                        0 => 'No'
                    ),
                    'value' => 0,
                    'fieldClass' => 'stay_on radio_toggler'
                ));
                $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="existing_owner" data-element="1">');
                $autocompURL = GI_URLUtils::buildURL(array(
                            'controller' => 'user',
                            'action' => 'autocompUser',
                            'ajax' => 1
                ));
                $this->form->addField('existing_owner_id', 'autocomplete', array(
                    'displayName' => 'Existing Owner',
                    'placeHolder' => 'Start typing owner name...',
                    'formElementClass' => 'fake_required',
                    'autocompURL' => $autocompURL
                ));
                $this->form->addHTML('</div>');
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="existing_owner" data-element="0">');
            }
            $this->form->addHTML('<div class="columns thirds">')
                    ->addHTML('<div class="column">');
            $this->addOwnerFirstNameField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addOwnerLastNameField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addOwnerPhoneNumberField();
            $this->form->addHTML('</div>')
                    ->addHTML('</div>');

            if (empty($contactId)) {
                $this->form->addField('edit_login_credentials', 'hidden', array(
                    'value' => 1
                ));
                $this->addFranchiseOwnerLoginFields();
                $this->form->addHTML('</div>');
            } else {
                $this->form->addField('edit_login_credentials', 'radio', array(
                    'displayName' => 'Overwrite Login Credentials?',
                    'options' => array(
                        1 => 'Yes',
                        2 => 'No'
                    ),
                    'value' => 2,
                    'stayOn' => true,
                    'fieldClass' => 'radio_toggler'));
                $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="edit_login_credentials" data-element=1>');
                $this->addFranchiseOwnerLoginFields();
                $this->form->addHTML('</div>');
            }
        } else {
            $this->form->addField('add_owner_section', 'hidden', array(
                'value' => 0,
            ));
        }
    }

    protected function addFranchiseOwnerLoginFields() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column">');
        $this->addOwnerLoginEmailField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addOwnerPasswordField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addOwnerConfirmPasswordField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addOwnerFirstNameField() {
        $this->form->addField('owner_first_name', 'text', array(
            'displayName' => 'First Name',
            'placeHolder' => 'First Name',
            'value' => $this->franchisePrimaryOwnerContact->getProperty('contact_ind.first_name'),
            'formElementClass' => 'fake_required'
        ));
    }

    protected function addOwnerLastNameField() {
        $this->form->addField('owner_last_name', 'text', array(
            'displayName' => 'Last Name',
            'placeHolder' => 'Last Name',
            'value' => $this->franchisePrimaryOwnerContact->getProperty('contact_ind.last_name'),
            'formElementClass' => 'fake_required'
        ));
    }

    protected function addOwnerPhoneNumberField() {
        $value = NULL;
        $phoneNumber = $this->franchisePrimaryOwnerContact->getContactInfo('phone_num');
        if (!empty($phoneNumber)) {
            $value = $phoneNumber->getProperty('contact_info_phone_num.phone');
        }
        $this->form->addField('owner_phone_number', 'phone', array(
            'displayName' => 'Phone Number',
            'placeHolder' => 'Phone Number',
            'value' => $value,
        ));
    }

    protected function addOwnerLoginEmailField() {
        $this->form->addField('owner_login_email', 'email', array(
            'displayName' => 'Login Email',
            'placeHolder' => 'Login Email',
            'value' => $this->franchisePrimaryOwnerUser->getProperty('email')
        ));
    }

    protected function addOwnerPasswordField() {
        $this->form->addField('owner_password', 'password', array(
            'displayName' => 'Password',
            'placeHolder' => 'Password',
            'value' => '',
        ));
    }

    protected function addOwnerConfirmPasswordField() {
        $this->form->addField('owner_confirm_password', 'password', array(
            'displayName' => 'Confirm Password',
            'placeHolder' => 'Confirm Password',
            'value' => '',
        ));
    }

}
