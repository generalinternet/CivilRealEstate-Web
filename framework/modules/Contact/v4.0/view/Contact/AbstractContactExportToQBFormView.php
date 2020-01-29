<?php
/**
 * Description of AbstractContactExportToQBFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactExportToQBFormView extends GI_View {
    
    protected $form;
    protected $contact;
    protected $contactQB;
    protected $formBuilt = false;
    protected $addButtons = true;

    
    public function __construct(GI_Form $form, AbstractContact $contact, AbstractContactQB $contactQB) {
        parent::__construct();
        $this->form = $form;
        $this->contact = $contact;
        $this->contactQB = $contactQB;
    }

    public function setAddButtons($addButtons) {
        $this->addButtons = $addButtons;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildFormHeader() {
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col">'); //Left Col
        $this->form->addHTML('<h2>' . $this->contactQB->getViewTitle(false) . ' Info</h2>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Space in lieu of arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $siteTitle = ProjectConfig::getSiteTitle();
        $contactTypeTitle = $this->contact->getTypeTitle();
        $this->form->addHTML('<h2>' . $siteTitle . ' Contact - ' . $contactTypeTitle . '</h2>');
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row
    }

    protected function buildFormBody($classNames = '') {
        $this->form->addHTML('<div class="form_body give_me_space '.$classNames.'">');
        if ($this->contact->isIndividual()) {
            $this->addIndBasicInfoSection();
        } else if ($this->contact->isOrganization()) {
            $this->addOrgBasicInfoSection();
        }
        $this->addBillingAddressSection();
        if ($this->contactQB->isCustomer()) {
            $this->addShippingAddressSection();
        }
        $this->addEmailAddressSection();
        $this->addPhoneNumberSections();
        $this->form->addHTML('</div>');
    }

    protected function addOrgBasicInfoSection() {
        $this->form->addHTML('<div class="bg_section_box">'); //Lighter Green Box
        //Name
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col

        $this->form->addHTML('<h3>Name</h3>');
        $name = $this->contactQB->getIndividualName();
        if (empty($name)) {
            $name = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $name . '</p>');
        
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row
        
        //Company
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
        $this->form->addHTML('<h3>Company</h3>');
        $currencyRef = $this->contactQB->getProperty('company');
        if (empty($currencyRef)) {
            $currencyRef = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $currencyRef . '</p>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text has_arrow left_arrow"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Right Col
        $this->form->addHTML('<h3>Organization Name</h3>');
        $this->form->addHTML('<p class="content_block">' . $this->contact->getProperty('contact_org.title') . '</p>');
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row
        
        //Display Name
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
        $this->form->addHTML('<h3>Display Name as</h3>');
        $displayName = $this->contactQB->getProperty('display_name');
        if (empty($displayName)) {
            $displayName = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $displayName . '</p>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row

        if (empty($this->contactQB) || empty($this->contactQB->getId())) {
            $this->form->addHTML('<div class="flex_row">'); //Row
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
            $this->form->addHTML('<h3>Currency</h3>');
            $currencyRef = $this->contactQB->getProperty('company');
            if (empty($currencyRef)) {
                $currencyRef = '--';
            }
            $this->form->addHTML('<p class="content_block">' . $currencyRef . '</p>');
            $this->form->addHTML('</div>'); //End Left Col
            $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text has_arrow left_arrow"></span></div>'); //Arrow
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Right Col
            $this->form->addHTML('<h3>Default Currency</h3>');
            $defaultCurrency = $this->contact->getDefaultCurrency();
            if (empty($defaultCurrency)) {
                $defaultCurrencyName = '--';
            } else {
                $defaultCurrencyName = $defaultCurrency->getProperty('name');
            }
            $this->form->addHTML('<p class="content_block">' . $defaultCurrencyName . '</p>');
            $this->form->addHTML('</div>'); //End Right Col
            $this->form->addHTML('</div>'); //End Row
        }

        $this->form->addHTML('</div>'); //End Lighter Green Box
    }

    protected function addIndBasicInfoSection() {
        $this->form->addHTML('<div class="bg_section_box">'); //Lighter Green Box
        //Name
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
        $this->form->addHTML('<h3>Name</h3>');
        $name = $this->contactQB->getIndividualName();
        if (empty($name)) {
            $name = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $name . '</p>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text has_arrow left_arrow"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h3>First Name</h3>');
        $this->form->addHTML('<p class="content_block">' . $this->contact->getProperty('contact_ind.first_name') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<h3>Last Name</h3>');
        $this->form->addHTML('<p class="content_block">' . $this->contact->getProperty('contact_ind.last_name') . '</p>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row
        //Company
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
        $this->form->addHTML('<h3>Company</h3>');
        $companyName = $this->contactQB->getProperty('company');
        if (empty($companyName)) {
            $companyName = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $companyName . '</p>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row
        //Display Name
        $this->form->addHTML('<div class="flex_row">'); //Row
        $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
        $this->form->addHTML('<h3>Display Name as</h3>');
        $displayName = $this->contactQB->getProperty('display_name');
        if (empty($displayName)) {
            $displayName = '--';
        }
        $this->form->addHTML('<p class="content_block">' . $displayName . '</p>');
        $this->form->addHTML('</div>'); //End Left Col
        $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
        $this->form->addHTML('<div class="flex_col">'); //Right Col
        $this->form->addHTML('</div>'); //End Right Col
        $this->form->addHTML('</div>'); //End Row

        if (empty($this->contactQB) || empty($this->contactQB->getId())) {
            $this->form->addHTML('<div class="flex_row">'); //Row
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
            $this->form->addHTML('<h3>Currency</h3>');
            $currencyRef = $this->contactQB->getProperty('company');
            if (empty($currencyRef)) {
                $currencyRef = '--';
            }
            $this->form->addHTML('<p class="content_block">' . $currencyRef . '</p>');
            $this->form->addHTML('</div>'); //End Left Col
            $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text has_arrow left_arrow"></span></div>'); //Arrow
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Right Col
            $this->form->addHTML('<h3>Default Currency</h3>');
            $defaultCurrency = $this->contact->getDefaultCurrency();
            if (empty($defaultCurrency)) {
                $defaultCurrencyName = '--';
            } else {
                $defaultCurrencyName = $defaultCurrency->getProperty('name');
            }
            $this->form->addHTML('<p class="content_block">' . $defaultCurrencyName . '</p>');
            $this->form->addHTML('</div>'); //End Right Col
            $this->form->addHTML('</div>'); //End Row
        }
        $this->form->addHTML('</div>'); //End Lighter Green Box
    }

    protected function addBillingAddressSection() {
        $qbBillingAddressString = $this->contactQB->getBillingAddress();
        $billingAddress = $this->contact->getQBBillingAddress();
        $this->addAddressSection($qbBillingAddressString, 'bill_address_id', 'Billing Address', 'Billing', $billingAddress);
    }

    protected function addShippingAddressSection() {
        $qbShippingAddressString = $this->contactQB->getShippingAddress();
        $shippingAddress = $this->contact->getQBShippingAddress();
        $this->addAddressSection($qbShippingAddressString, 'ship_address_id', 'Shipping Address', 'Shipping', $shippingAddress);
    }

    protected function addAddressSection($addressString, $fieldName, $qbTitle, $title, $contactInfo = NULL) {
        $value = NULL;
        if (empty($addressString)) {
            $value = 'dne';
        }
        $contactInfoArray = $this->contact->getContactInfoArray('address');
        $options = array();

        if (!empty($contactInfoArray)) {
            $contactInfoAddressArray = $contactInfoArray['address'];
            foreach ($contactInfoAddressArray as $contactInfoAddress) {
                $address = $contactInfoAddress->getAddressString(true);
                if (!empty($address)) {
                    $options[$contactInfoAddress->getId()] = '<div class="qb_contact_option_header">' . strtoupper($contactInfoAddress->getTypeTitle()) . '</div>' . $address;
                }
            }
        }
        if (!empty($contactInfo)) {
            $value = $contactInfo->getId();
            if (isset($options[$value])) {
                unset($options[$value]);
                $tempArray = array(
                    $value => '<div class="qb_contact_option_header">' . strtoupper($contactInfo->getTypeTitle()) . '</div>' . $contactInfo->getAddressString(true),
                );
                $options = $tempArray + $options;
            }
        }
        if (!empty($options)) {
            $this->form->addHTML('<div class="bg_section_box">'); //Lighter Green Box
            $this->form->addHTML('<div class="flex_row">'); //Row
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
            $this->form->addHTML('<h3>' . $qbTitle . '</h3>');
            if (empty($addressString)) {
                $addressString = '--';
            }
            $this->form->addHTML('<p class="content_block">' . $addressString . '</p>');
            $this->form->addHTML('</div>'); //End Left Col
            $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
            $this->form->addHTML('<div class="flex_col">'); //Right Col


            if ($fieldName == 'ship_address_id') {
                $options['sab'] = 'Same as Billing';
            }
            $options['dne'] = 'Do Not Export';

            $this->form->addField($fieldName, 'radio', array(
                'displayName' => '',
             //   'stayOn' => true,
                'options' => $options,
                'value' => $value,
                'formElementClass' => 'v_aligned_element',
                'fieldClass' => 'qb_contact_info with_left_arrow',
                'required'=>true,
            ));
            $this->form->addHTML('</div>'); //End Right Col
            $this->form->addHTML('</div>'); //End Row
            $this->form->addHTML('</div>'); //End Lighter Green Box
        }
    }

    protected function addEmailAddressSection() {
        $email = $this->contactQB->getProperty('email');
        $value = NULL;
        if (empty($email)) {
            $value = 'dne';
        }
        $contactInfoArray = $this->contact->getContactInfoArray('email_address');
        $options = array();

        if (!empty($contactInfoArray)) {
            $contactInfoEmailArray = $contactInfoArray['email_address'];
            foreach ($contactInfoEmailArray as $contactInfoEmail) {
                $email = $contactInfoEmail->getProperty('contact_info_email_addr.email_address');
                if (!empty($email)) {
                    $options[$contactInfoEmail->getId()] = '<div class="qb_contact_option_header">' . strtoupper($contactInfoEmail->getTypeTitle()) . '</div>' . $email;
                    if ($contactInfoEmail->isQuickbooksLinked()) {
                        $value = $contactInfoEmail->getId();
                    }
                }
            }
        }
        if (!empty($options)) {
            $this->form->addHTML('<div class="bg_section_box">'); //Lighter Green Box
            $this->form->addHTML('<div class="flex_row">'); //Row
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
            $this->form->addHTML('<h3>Email</h3>');
            if (empty($email)) {
                $email = '--';
            }
            $this->form->addHTML('<p class="content_block">' . $email . '</p>');
            $this->form->addHTML('</div>'); //End Left Col
            $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
            $this->form->addHTML('<div class="flex_col">'); //Right Col
            $options['dne'] = 'Do Not Export';
            $this->form->addField('email_id', 'radio', array(
                'displayName' => '',
             //   'stayOn' => true,
                'options' => $options,
                'value' => $value,
                'formElementClass' => 'v_aligned_element',
                'fieldClass' => 'qb_contact_info with_left_arrow',
                'required'=>true,
            ));
            $this->form->addHTML('</div>'); //End Right Col
            $this->form->addHTML('</div>'); //End Row
            $this->form->addHTML('</div>'); //End Lighter Green Box
        }
    }

    protected function addPhoneNumberSections() {
        $qbPhoneNum = $this->contact->getQBPhoneNumber();
        $this->addPhoneNumberSection('primary_phone', 'phone_id', 'Phone', 'Phone Number', $qbPhoneNum);
        $qbMobilePhoneNum = $this->contact->getQBMobileNumber();
        $this->addPhoneNumberSection('mobile', 'mobile_phone_id', 'Mobile', 'Mobile', $qbMobilePhoneNum);
        $qbFaxPhoneNum = $this->contact->getQBFaxNumber();
        $this->addPhoneNumberSection('fax', 'fax_phone_id', 'Fax', 'Fax', $qbFaxPhoneNum);
        $qbOtherPhoneNum = $this->contact->getQBOtherNumber();
        $this->addPhoneNumberSection('alternate_phone', 'other_phone_id', 'Other', 'Other', $qbOtherPhoneNum);
    }

    protected function addPhoneNumberSection($qbContactPropertyName, $fieldName, $qbTitle, $title, $contactInfo = NULL) {
        $phone = $this->contactQB->getProperty($qbContactPropertyName);
        $value = NULL;
        if (empty($phone)) {
            $value = 'dne';
        }
        $contactInfoArray = $this->contact->getContactInfoArray('phone_num');
        $options = array();

        if (!empty($contactInfoArray)) {
            $contactInfoPhoneNumArray = $contactInfoArray['phone_num'];
            foreach ($contactInfoPhoneNumArray as $contactInfoPhoneNum) {
                $phoneNum = $contactInfoPhoneNum->getProperty('contact_info_phone_num.phone');
                if (!empty($phoneNum)) {
                    $options[$contactInfoPhoneNum->getId()] = '<div class="qb_contact_option_header">' . strtoupper($contactInfoPhoneNum->getTypeTitle()) . '</div>' . $phoneNum;
                }
            }
        }
        if (!empty($contactInfo)) {
            $value = $contactInfo->getId();
            if (isset($options[$value])) {
                unset($options[$value]);
                $tempArray = array(
                    $value => '<div class="qb_contact_option_header">' . strtoupper($contactInfo->getTypeTitle()) . '</div>' . $contactInfo->getProperty('contact_info_phone_num.phone')
                );
                $options = $tempArray + $options;
            }
        }
        if (!empty($options)) {
            $this->form->addHTML('<div class="bg_section_box">'); //Lighter Green Box
            $this->form->addHTML('<div class="flex_row">'); //Row
            $this->form->addHTML('<div class="flex_col h_content_wrap">'); //Left Col
            $this->form->addHTML('<h3>' . $qbTitle . '</h3>');
            if (empty($phone)) {
                $phone = '--';
            }
            $this->form->addHTML('<p class="content_block">' . $phone . '</p>');
            $this->form->addHTML('</div>'); //End Left Col
            $this->form->addHTML('<div class="flex_col sml vert_center center_align"><span class="lrg_text"></span></div>'); //Arrow
            $this->form->addHTML('<div class="flex_col">'); //Right Col

            $options['dne'] = 'Do Not Export';
            $this->form->addField($fieldName, 'radio', array(
                'displayName' => '',
            //    'stayOn' => true,
                'options' => $options,
                'value' => $value,
                'formElementClass' => 'v_aligned_element',
                'fieldClass' => 'qb_contact_info with_left_arrow',
                'required'=>true,
            ));
            $this->form->addHTML('</div>'); //End Right Col
            $this->form->addHTML('</div>'); //End Row
            $this->form->addHTML('</div>'); //End Lighter Green Box
        }
    }

    protected function buildFormFooter() {
        if ($this->addButtons) {
            $this->form->addHTML('<div class="center_btns wrap_btns">');
            $this->addSubmitButton();
            $this->addCancelButton();
            $this->form->addHTML('</div>');
        }
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Export</span>');
    }
    
    protected function addCancelButton() {
        $viewQBURL = GI_URLUtils::buildURL(array(
                'controller' => 'contact',
                'action' => 'viewQB',
                'id' => $this->contact->getId(),
                'callback' => 'closeQBSection',
            ));
        $this->form->addHTML('<a href="' . $viewQBURL . '" class="other_btn load_in_element" title="Cancel" data-load-in-id="qb_info_section">Cancel</a>');
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHeaderIcons();
        $this->addHTML('<div id="qb_info_content">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function addHeaderIcons() {
        $this->addHTML('<span id="qb_info_section_expand" class="icon_wrap"><span class="icon arrow_down border"></span></span>');
        $viewQBURL = GI_URLUtils::buildURL(array(
                'controller' => 'contact',
                'action' => 'viewQB',
                'id' => $this->contact->getId(),
                'callback' => 'closeQBSection',
            ));
        $this->addHTML('<a href="' . $viewQBURL . '" class="load_in_element close_icon_link" data-load-in-id="qb_info_section"><span id="qb_info_section_close" class="icon_wrap circle"><span class="icon eks"></span></span></a>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}