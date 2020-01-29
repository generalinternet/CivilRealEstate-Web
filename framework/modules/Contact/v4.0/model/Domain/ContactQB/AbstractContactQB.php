<?php
/**
 * Description of AbstractContactQB
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactQB extends GI_Model {

    protected $contactOrg;
    protected $contactInd;
    protected $quickbooksObject = NULL;
    protected $tableWrapId = 'contact_qb_table';
    protected static $searchFormId = 'contact_qb_search';
    protected static $compatibleContactCatTypeRefs = array();

    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    public function getContactOrg() {
        if (empty($this->contactOrg)) {
            $contactOrgArray = ContactFactory::getModelArrayByContactQB($this, 'org');
            if (!empty($contactOrgArray)) {
                $this->contactOrg = $contactOrgArray[0];
            }
        }
        return $this->contactOrg;
    }

    public function getContactInd() {
        if (empty($this->contactInd)) {
            $contactIndArray = ContactFactory::getModelArrayByContactQB($this, 'ind');
            if (!empty($contactIndArray)) {
                $this->contactInd = $contactIndArray[0];
            }
        }
        return $this->contactInd;
    }

    public function getViewTitle($plural = true) {
        $title = 'Quickbooks Contact';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getAPITableName() {
        return NULL;
    }

    public function getCompatibleContactCatTypeRefs() {
        return static::$compatibleContactCatTypeRefs;
    }

    /**
     * @param type $qbObject
     * @return boolean
     */
    public function updateFromQB($qbObject) {
        if (!$this->setPropertiesFromQBObject($qbObject)) {
            return false;
        }
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    protected function setPropertiesFromQBObject($qbObject) {
        if (empty($qbObject)) {
            return false;
        }
        $qbId = $qbObject->Id;
        if (empty($this->getProperty('qb_id'))) {
            $this->setProperty('qb_id', $qbId);
        }
        if (empty($this->getProperty('qb_import_date'))) {
            $this->setProperty('qb_import_date', Date('Y-m-d'));
        }
        $this->setProperty('title', $qbObject->Title);
        $this->setProperty('first_name', $qbObject->GivenName);
        $this->setProperty('middle_name', $qbObject->MiddleName);
        $this->setProperty('last_name', $qbObject->FamilyName);
        $this->setProperty('suffix', $qbObject->Suffix);
        $this->setProperty('company', $qbObject->CompanyName);
        $this->setProperty('display_name', $qbObject->DisplayName);
        $this->setProperty('company_dba', $qbObject->DisplayName);
        $this->setProperty('fully_qualified_name', $qbObject->FullyQualifiedName);
        $this->setProperty('print_on_cheque_name', $qbObject->PrintOnCheckName);
        $this->setProperty('currency_ref', $qbObject->CurrencyRef);

        $parentRef = $qbObject->ParentRef;
        if (!empty($parentRef)) {
            $this->setProperty('parent_qb_id', $parentRef);
        } else {
            $this->setProperty('parent_qb_id', NULL);
        }

        $emailObject = $qbObject->PrimaryEmailAddr;
        if (!empty($emailObject)) {
            $this->setProperty('email', $emailObject->Address);
        }
        $primaryPhoneObject = $qbObject->PrimaryPhone;
        if (!empty($primaryPhoneObject)) {
            $this->setProperty('primary_phone', $primaryPhoneObject->FreeFormNumber);
        }
        $alternatePhoneObject = $qbObject->AlternatePhone;
        if (!empty($alternatePhoneObject)) {
            $this->setProperty('alternate_phone', $alternatePhoneObject->FreeFormNumber);
        }
        $mobilePhoneObject = $qbObject->Mobile;
        if (!empty($mobilePhoneObject)) {
            $this->setProperty('mobile', $mobilePhoneObject->FreeFormNumber);
        }
        $faxObject = $qbObject->Fax;
        if (!empty($faxObject)) {
            $this->setProperty('fax', $faxObject->FreeFormNumber);
        }
        $billAddrObject = $qbObject->BillAddr;
        if (!empty($billAddrObject)) {
            $this->setProperty('bill_addr_id', $billAddrObject->Id);
            $this->setProperty('bill_addr_line_1', $billAddrObject->Line1);
            $this->setProperty('bill_addr_line_2', $billAddrObject->Line2);
            $this->setProperty('bill_addr_line_3', $billAddrObject->Line3);
            $this->setProperty('bill_addr_line_4', $billAddrObject->Line4);
            $this->setProperty('bill_addr_line_5', $billAddrObject->Line5);
            $this->setProperty('bill_addr_city', $billAddrObject->City);
            $this->setProperty('bill_addr_country', $billAddrObject->Country);
            $this->setProperty('bill_addr_region', $billAddrObject->CountrySubDivisionCode);
            $this->setProperty('bill_addr_postal_code', $billAddrObject->PostalCode);
        }
        return true;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            //Display Name
            array(
                'header_title' => 'Display Name',
                'method_attributes' => 'display_name',
            ),
            //Company
            array(
                'header_title' => 'Company',
                'method_attributes' => 'company',
            ),
            //Indvidual Name
             array(
                'header_title' => 'Name',
                'method_name' => 'getIndividualName',
            ),
            //Import Date
            array(
                'header_title' => 'Import Date',
                'method_name' => 'getImportDate',
                'method_attributes' => array(true)
            ),
            //Functions
            array(
                'header_title' => '',
                'method_name' => 'getRowDropdownMenu',
                'css_header_class' => 'col_xsmall',
                'css_class' => 'col_xsmall'
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getIndividualName() {
        $name = '';
        if (!empty($this->getProperty('title'))) {
            $name .= $this->getProperty('title') . ' ';
        }
        if (!empty($this->getProperty('first_name'))) {
            $name .= $this->getProperty('first_name') . ' ';
        }
        if (!empty($this->getProperty('middle_name'))) {
            $name .= $this->getProperty('middle_name') . ' ';
        }
        if (!empty($this->getProperty('last_name'))) {
            $name .= $this->getProperty('last_name') . ' ';
        }
        if (!empty($this->getProperty('suffix'))) {
            $name .= $this->getProperty('suffix');
        }
        return $name;
    }

    public function getImportDate($formatForDisplay = true) {
        $importDate = $this->getProperty('qb_import_date');
        if (empty($importDate)) {
            return NULL;
        }
        if ($formatForDisplay) {
            return GI_Time::formatDateForDisplay($importDate);
        }
        return $importDate;
    }

    public function getRowDropdownMenu() {
        $importURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'importQBContact',
            'id' => $this->getProperty('id'),
        ));
        $linkOrCreateURL = GI_URLUtils::buildURL(array(
            'controller'=>'contact',
            'action'=>'createOrLinkFromQBContact',
            'id'=>$this->getProperty('id')
        ));
        $html = '<div class="dropdown_tooltip_menu">';
        $html .= '<span class="icon tooltip_arrow"></span>';
        $html .= '<ul class="tooltip_menu">';
        //    $html .= '<li><a href="'.$this->getViewURL().'"><span class="icon search"></span> View</a></li>';
        $html .= '<li><a href="'.$importURL.'" class="custom_btn open_modal_form"><span class="icon primary import"></span>Re-Import</a></li>';
        $html .= '<li><a href="'.$linkOrCreateURL.'" class="custom_btn"><span class="icon primary add_multiple"></span>Link/Create Contact(s)</a></li>';
        $html .= '</ul>';
        $html .= '</div><!--.dropdown_tooltip_menu-->';

        return $html;
    }

    public function getDetailView() {
        return new ContactQBDetailView($this);
    }

    public function getBillingAddress($addHTMLLineBreaks = true) {
        $lines = array();
        $line1 = $this->getProperty('bill_addr_line_1');
        if (!empty($line1)) {
            $lines[] =  $line1 . ' ';
        }
        $line2 = $this->getProperty('bill_addr_line_2');
        if (!empty($line2)) {
            $lines[] = $line2 . ' ';
        }
        $line3 = $this->getProperty('bill_addr_line_3');
        if (!empty($line3)) {
            $lines[] = $line3 . ' ';
        }
        $line4 = $this->getProperty('bill_addr_line_4');
        if (!empty($line4)) {
            $lines[] = $line4 . ' ';
        }
        $line5 = $this->getProperty('bill_addr_line_5');
        if (!empty($line5)) {
            $lines[] = $line5 . ' ';
        }
        $line6 = '';
        $city = $this->getProperty('bill_addr_city');
        if (!empty($city)) {
            $line6 .= $city . ', ';
        }
        $region = $this->getProperty('bill_addr_region');
        if (!empty($region)) {
            $line6 .= $region . ' ';
        }
        $postalCode = $this->getProperty('bill_addr_postal_code');
        if (!empty($postalCode)) {
            $line6 .= $postalCode;
        }
        if (!empty($line6)) {
            $lines[] = $line6;
        }
        $country = $this->getProperty('bill_addr_country');
        if (!empty($country)) {
            $lines[] = $country;
        }
        if ($addHTMLLineBreaks) {
            $address = '';
            foreach ($lines as $line) {
                $address .= $line . '<br />';
            }
        } else {
            $address = implode(' ', $lines);
        }
        return $address;
    }

    public function getShippingAddress($addHTMLLineBreaks = true) {
        return NULL;
    }

    public function getLinkedOrgViewURLAttributes() {
        $contactOrg = $this->getContactOrg();
        if (!empty($contactOrg)) {
            return $contactOrg->getViewURLAttributes();
        }
        return NULL;
    }

    public function getLinkedINdViewURLAttributes() {
        $contactInd = $this->getContactInd();
        if (!empty($contactInd)) {
            return $contactInd->getViewURLAttributes();
        }
        return NULL;
    }

    public function handleLinkOrCreateContactFormSubmission(GI_Form $form) {
        if (!($form->wasSubmitted() && $this->validateLinkOrCreateContactForm($form))) {
            return false;
        }
        $contactOrg = $this->handleLinkOrCreateContactFormOrgFields($form);

        $contactInd = $this->handleLinkOrCreateContactFormIndFields($form);

        if (!empty($contactOrg) && !empty($contactInd)) {
            if (!ContactFactory::linkContactAndContact($contactOrg, $contactInd)) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param GI_Form $form
     * @return AbstractContactOrg
     */
    protected function handleLinkOrCreateContactFormOrgFields(GI_Form $form) {
        if (!$form->wasSubmitted() && $this->validateLinkOrCreateContactForm($form)) {
            return NULL;
        }
        $contactOrg = NULL;
        $selectedOption = filter_input(INPUT_POST, 'org_options');
        if ($selectedOption == 'new') {
            $contactCatTypeRef = filter_input(INPUT_POST, 'org_contact_cat');
            $contactOrg = $this->createAndSaveContactFromData('org', $contactCatTypeRef);
        } else if ($selectedOption == 'existing') {
            $orgContactId = filter_input(INPUT_POST, 'org_contact_id');
            $contactOrg = ContactFactory::getModelById($orgContactId);
            if (empty($contactOrg)) {
                return NULL;
            }
            $contactOrg->setProperty('fully_qualified_name', $this->getProperty('fully_qualified_name'));
            $contactOrg->setProperty('contact_qb_id', $this->getProperty('id'));
            if (!$contactOrg->save()) {
                return NULL;
            }
            $this->setProperty('export_required', 1);
            $this->setProperty('import_required', 1);
            if (!$this->save()) {
                return NULL;
            }
        }

        return $contactOrg;
    }

    /**
     *
     * @param GI_Form $form
     * @return AbstractContactInd
     */
    protected function handleLinkOrCreateContactFormIndFields(GI_Form $form) {
        if (!$form->wasSubmitted() && $this->validateLinkOrCreateContactForm($form)) {
            return NULL;
        }
        $contactInd = NULL;
        $selectedOption = filter_input(INPUT_POST, 'ind_options');
        if ($selectedOption == 'new') {
            $contactCatTypeRef = filter_input(INPUT_POST, 'ind_contact_cat');
            $contactInd = $this->createAndSaveContactFromData('ind', $contactCatTypeRef);
        } else if ($selectedOption == 'existing') {
            $indContactId = filter_input(INPUT_POST, 'ind_contact_id');
            $contactInd = ContactFactory::getModelById($indContactId);
            if (empty($contactInd)) {
                return NULL;
            }
            $contactInd->setProperty('fully_qualified_name', $this->getProperty('fully_qualified_name'));
            $contactInd->setProperty('contact_qb_id', $this->getProperty('id'));
            if (!$contactInd->save()) {
                return NULL;
            }
            $this->setProperty('export_required', 1);
            $this->setProperty('import_required', 1);
            if (!$this->save()) {
                return NULL;
            }
        }

        return $contactInd;
    }

    public function validateLinkOrCreateContactForm(GI_Form $form) {
        if (!$form->wasSubmitted() && $form->validate()) {
            return false;
        }
        //TODO
        return true;
    }

    public function createAndSaveContactFromData($typeRef, $contactCatTypeRef) {
        $contact = ContactFactory::buildNewModel($typeRef);
        $contact->setProperty('fully_qualified_name', $this->getProperty('fully_qualified_name'));
        $contact->setProperty('contact_qb_id', $this->getProperty('id'));
        $currencyId = NULL;
        $currencyRef = strtolower($this->getProperty('currency_ref'));
        if (!empty($currencyRef)) {
            $currency = CurrencyFactory::getModelByRef($currencyRef);
            if (!empty($currency)) {
                $currencyId = $currency->getId();
                $contact->setProperty('default_currency_id', $currencyId);
            }
        }
         $contact->setProperty('internal', 0);
        if ($contact->isIndividual()) {
            if (!empty($this->getContactInd())) {
                return $this->getContactInd();
            }
            $firstName = $this->getProperty('first_name');
            if (empty($firstName)) {
                return NULL;
            }
            $contact->setProperty('contact_ind.first_name', $firstName);
            $contact->setProperty('contact_ind.last_name', $this->getProperty('last_name'));


        } else if ($contact->isOrganization()) {
            if (!empty($this->getContactOrg())) {
                return $this->getContactOrg();
            }
            $companyName = $this->getProperty('company');
            if (empty($companyName)) {
                return NULL;
            }
            $companyDBAName = $this->getProperty('company_dba');
            $contact->setProperty('contact_org.title', $companyName);
            $contact->setProperty('contact_org.doing_bus_as', $companyDBAName);


        } else {
            return NULL;
        }
        if (empty($contactCatTypeRef)) {
            return NULL;
        }
        if (!$contact->save()) {
            return NULL;
        }
        $contactCat = ContactCatFactory::buildNewModel($contactCatTypeRef);
        $contactCat->setProperty('contact_id', $contact->getProperty('id'));
        if (!$contactCat->save()) {
            return NULL;
        }
        if (!$this->createContactInfosFromData($contact)) {
            return NULL;
        }
        
        $notDefinedSubCatTag = TagFactory::getModelByRefAndTypeRef('not_defined', 'contact_sub_cat');
        if (!empty($notDefinedSubCatTag)) {
            ContactFactory::linkContactAndTag($contact, $notDefinedSubCatTag);
        }

        return $contact;
    }

    protected function createContactInfosFromData(AbstractContact $contact) {
        $billingAddress = $this->createBillingAddressContactInfoFromData();
        if (!empty($billingAddress)) {
            $billingAddress->setProperty('contact_id', $contact->getProperty('id'));
            $billingAddress->setProperty('qb_linked', 1);
            if (!$billingAddress->save()) {
                return false;
            }
        }
        $phoneNumberContactInfos = $this->createPhoneNumberContactInfosFromData();
        if (!empty($phoneNumberContactInfos)) {
            foreach ($phoneNumberContactInfos as $phoneNumberContactInfo) {
                $phoneNumberContactInfo->setProperty('contact_id', $contact->getProperty('id'));
                $phoneNumberContactInfo->setProperty('qb_linked', 1);
                if (!$phoneNumberContactInfo->save()) {
                    return false;
                }
            }
        }
        $emailAddressContactInfos = $this->createEmailAddressContactInfosFromData();
        if (!empty($emailAddressContactInfos)) {
            foreach ($emailAddressContactInfos as $emailAddressContactInfo) {
                $emailAddressContactInfo->setProperty('contact_id', $contact->getProperty('id'));
                $emailAddressContactInfo->setProperty('qb_linked', 1);
                if (!$emailAddressContactInfo->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *
     * @param type $typeRef
     * @return AbstractContactInfoAddress
     */
    public function createBillingAddressContactInfoFromData($typeRef = 'billing_address', AbstractContactInfoAddress $contactInfo = NULL) {
        if (empty($contactInfo)) {
            $contactInfo = ContactInfoFactory::buildNewModel($typeRef);
        } else {
            $contactInfo = ContactInfoFactory::changeModelType($contactInfo, $typeRef);
        }
        if (empty($contactInfo)) {
            return NULL;
        }
        $countryCode = 'CAN';
        $regionCode = GeoDefinitions::determineDefaultRegionCode($countryCode);

        $countryString = $this->getProperty('bill_addr_country');
        $countryName = GeoDefinitions::getCountryNameFromCode(strtoupper($countryString));
        if (!empty($countryName)) {
            $countryCode = strtoupper($countryString);
        } else {
            $countryCodeFromName = GeoDefinitions::getCountryCodeFromName($countryString);
            if (!empty($countryCodeFromName)) {
                $countryCode = $countryCodeFromName;
            }
        }
        $regionString = $this->getProperty('bill_addr_region');
        $regionName = GeoDefinitions::getRegionNameFromCode($countryCode, $regionString, true);
        if (!empty($regionName)) {
            $regionCode = $regionString;
        } else {
            $regionCodeFromName = GeoDefinitions::getRegionCodeFromName($countryCode, $regionString, true);
            if (!empty($regionCodeFromName)) {
                $regionCode = $regionCodeFromName;
            } else {
                $regionCode = $regionString;
            }
        }
//        if (empty($countryCode) || empty($regionCode)) {
//            return NULL;
//        }
        if (!empty($countryCode) && ($countryCode === 'CAN' || $countryCode === 'USA') && empty($regionCode)) {
            return NULL;
        }
        $contactInfo->setProperty('contact_info_address.addr_country', $countryCode);
        $contactInfo->setProperty('contact_info_address.addr_region', $regionCode);

        $line1 = $this->getProperty('bill_addr_line_1');
    //    if (!empty($line1)) {
            $contactInfo->setProperty('contact_info_address.addr_street', $line1);
    //    }
        $streetTwo = '';
        $line2 = $this->getProperty('bill_addr_line_2');
        if (!empty($line2)) {
            $streetTwo .= $line2 . ' ';
        }
        $line3 = $this->getProperty('bill_addr_line_3');
        if (!empty($line3)) {
            $streetTwo .= $line3 . ' ';
        }
        $line4 = $this->getProperty('bill_addr_line_4');
        if (!empty($line4)) {
            $streetTwo .= $line4 . ' ';
        }
        $line5 = $this->getProperty('bill_addr_line_5');
        if (!empty($line5)) {
            $streetTwo .= $line5 . ' ';
        }
     //   if (!empty($streetTwo)) {
            $contactInfo->setProperty('contact_info_address.addr_street_two', $streetTwo);
    //    }
        $city = $this->getProperty('bill_addr_city');
        $contactInfo->setProperty('contact_info_address.addr_city', $city);

        $postalCode = $this->getProperty('bill_addr_postal_code');
        $contactInfo->setProperty('contact_info_address.addr_code', $postalCode);
        return $contactInfo;
    }

    public function createShippingAddressContactInfoFromData($typeRef = 'shipping_address', AbstractContactInfoAddress $contactInfo = NULL) {
        return NULL;
    }

    protected function createPhoneNumberContactInfosFromData() {
        $contactInfos = array();
        $primaryPhone = $this->getProperty('primary_phone');
        if (!empty($primaryPhone)) {
            $phone = ContactInfoFactory::buildNewModel('phone_num');
            $phone->setProperty('contact_info_phone_num.phone', $primaryPhone);
            $contactInfos[] = $phone;
        }
        $altPhone = $this->getProperty('alternate_phone');
        if (!empty($altPhone)) {
            $alt = ContactInfoFactory::buildNewModel('other_phone_num');
            $alt->setProperty('contact_info_phone_num.phone', $altPhone);
            $contactInfos[] = $alt;
        }
        $mobilePhone = $this->getProperty('mobile');
        if (!empty($mobilePhone)) {
            $mobile = ContactInfoFactory::buildNewModel('mobile_phone_num');
            $mobile->setProperty('contact_info_phone_num.phone', $mobilePhone);
            $contactInfos[] = $mobile;
        }
        $faxPhone = $this->getProperty('fax');
        if (!empty($faxPhone)) {
            $fax = ContactInfoFactory::buildNewModel('fax_num');
            $fax->setProperty('contact_info_phone_num.phone', $faxPhone);
            $contactInfos[] = $fax;
        }
        return $contactInfos;
    }

    protected function createEmailAddressContactInfosFromData() {
        $contactInfos = array();
        $primaryEmail = $this->getProperty('email');
        if (!empty($primaryEmail)) {
            $contactInfoEmailAddress = ContactInfoFactory::buildNewModel('email_address');
            $contactInfoEmailAddress->setProperty('contact_info_email_addr.email_address', $primaryEmail);
            $contactInfos[] = $contactInfoEmailAddress;
        }
        return $contactInfos;
    }

    public function isSupplier() {
        return false;
    }

    public function isCustomer() {
        return false;
    }

    public function getQuickbooksId() {
        return $this->getProperty('qb_id');
    }

    public function exportToQB() {
        return false;
    }

    public function importFromQB() {
        $qbConnection = QBConnection::getInstance();
        if (!empty($qbConnection)) {
            try {
                $query = "SELECT * FROM " . $this->getAPITableName() . " WHERE Id='" . $this->getProperty('qb_id') . "'";
                $result = $qbConnection->Query($query);
                $error = $qbConnection->getLastError();
                if (!empty($error)) {
                    GI_URLUtils::redirectToQBError($error);
                }
                if (!empty($result) && $this->updateFromQB($result[0]) && $this->updateContactsAfterImportOrExport()) {
                    return true;
                }
            } catch (Exception $ex) {
                GI_URLUtils::redirectToError(6000, $ex->getMessage());
            }
        }
        return false;
    }

    public function getQuickbooksExportPropertiesArray() {
        $properties = array(
            'CompanyName' => $this->getProperty('company'),
          //  'FullyQualifiedName' => $this->getProperty(''),
            'DisplayName' => $this->getProperty('display_name'),
            'Title'=>$this->getProperty('title'),
            'GivenName' => $this->getProperty('first_name'),
            'MiddleName'=>$this->getProperty('middle_name'),
            'FamilyName' => $this->getProperty('last_name'),
            'Suffix'=>$this->getProperty('suffix'),
            'Active' => true,
            'PrimaryPhone' => array(
                'FreeFormNumber' => $this->getProperty('primary_phone'),
            ),
            'PrimaryEmailAddr' => array(
                'Address' => $this->getProperty('email'),
            ),
            'domain' => 'QBO',
            'Mobile' => array(
                'FreeFormNumber' => $this->getProperty('mobile'),
            ),
            'Fax' => array(
                'FreeFormNumber' => $this->getProperty('fax'),
            ),
            'AlternatePhone'=>array(
                'FreeFormNumber'=>$this->getProperty('alternate_phone'),
            ),
            'CurrencyRef' => array(
                'value' => $this->getProperty('currency_ref'),
            ),
            'BillAddr' => array(
                'Line1' => $this->getProperty('bill_addr_line_1'),
                'Line2' => $this->getProperty('bill_addr_line_2'),
                'City' => $this->getProperty('bill_addr_city'),
                'CountrySubDivisionCode' => $this->getProperty('bill_addr_region'),
                'Country' => $this->getProperty('bill_addr_country'),
                'PostalCode' => $this->getProperty('bill_addr_postal_code'),
            ),
        );
        return $properties;
    }

    public function getQuickbooksObject() {
        return NULL;
    }

    public function getQuickbooksBillAddressObject() {
        $quickbooksObject = $this->getQuickbooksObject();
        if (!empty($quickbooksObject)) {
            $billAddr = $quickbooksObject->BillAddr;
            return $billAddr;
        }
        return NULL;
    }

    public function getQuickbooksDefaultTaxCodeRef() {
//        $quickbooksObject = $this->getQuickbooksObject();
//        if (!empty($quickbooksObject)) {
//            return $quickbooksObject->DefaultTaxCodeRef;
//        }
        return NULL;
    }

    public function getQuickbooksDisplayName() {
        $quickbooksObject = $this->getQuickbooksObject();
        if (!empty($quickbooksObject)) {
            return $quickbooksObject->DisplayName;
        }
        return $this->getName();
    }

    public function getOutstandingInvoiceBalance() {
        return NULL;
    }

    public function requiresBalanceUpdate() {
        return false;
    }

        /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return AbstractContactQBSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, $redirectArray = array()) {
        $form = new GI_Form(static::getSearchFormId());
        $searchView = static::getSearchFormView($form, $dataSearch, $type);

        static::filterSearchForm($dataSearch, $form);

        if ($form->wasSubmitted() && $form->validate()) {
            $queryId = $dataSearch->getQueryId();

            if (empty($redirectArray)) {
                $redirectArray = array(
                    'controller' => 'contact',
                    'action'=>'QBImportIndexContent',
                );

                if (!empty($type)) {
                    $redirectArray['type'] = $type;
                }
            }

            $redirectArray['queryId'] = $queryId;
            if (GI_URLUtils::getAttribute('ajax')) {
                $redirectArray['ajax'] = 1;
            }

            GI_URLUtils::redirect($redirectArray);
        }
        return $searchView;
    }

    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \AbstractContactQBSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL, $type = NULL) {
        $searchValues = array();
        if ($dataSearch) {
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = static::buildSearchFormView($form, $searchValues, $type);
        return $searchView;
    }

    /**
     * @param GI_Form $form
     * @param array $searchValues
     * @param string $type
     * @return \AbstractContactQBSearchFormView
     */
    protected static function buildSearchFormView(GI_Form $form, $searchValues = NULL, $type = NULL) {
        $searchFormView = new ContactQBSearchView($form, $searchValues, $type);
        $searchFormView->setModelClass(get_called_class());
        return $searchFormView;
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $contactQBName = $dataSearch->getSearchValue('contact_qb_name');
        if (!empty($contactQBName)) {
            static::addNameFilterToDataSearch($contactQBName, $dataSearch);
        }

        if (!is_null($form) && $form->wasSubmitted() && $form->validate()) {
            $contactQBName = filter_input(INPUT_POST, static::getSearchFieldName('search_contact_qb_name'));
            $dataSearch->setSearchValue('contact_qb_name', $contactQBName);
        }
        return true;
    }

    public static function addNameFilterToDataSearch($nameTerm, GI_DataSearch $dataSearch) {
        $nameTerms = explode(' ', $nameTerm);
        $columns = array(
            'display_name',
            'company',
            'first_name',
            'last_name'
        );
        $dataSearch->filterTermsLike($columns, $nameTerms);
        $dataSearch->orderByLikeScore($columns, $nameTerms);
    }

//    public function softDelete() {
//        $contactInd = $this->getContactInd();
//        if (!empty($contactInd)) {
//            $contactInd->setProperty('contact_qb_id', '');
//            if (!$contactInd->save()) {
//                return false;
//            }
//        }
//        $contactOrg = $this->getContactOrg();
//        if (!empty($contactOrg)) {
//            $contactOrg->setProperty('contact_qb_id', '');
//            if (!$contactOrg->save()) {
//                return false;
//            }
//        }
//        return parent::softDelete();
//    }

    public function softDelete() {
        $search = ContactFactory::search();
        $search->filter('contact_qb_id', $this->getId());
        $results = $search->select();
        if (!empty($results)) {
            foreach ($results as $contact) {
                $contact->setProperty('contact_qb_id', NULL);
                if (!$contact->save()) {
                    return false;
                }
            }
        }
        return parent::softDelete();
    }

    public function getIsTaxExempt() {
        return false;
    }

    protected function updateContactsAfterImportOrExport() {
        $fullyQualifiedName = $this->getProperty('fully_qualified_name');
        $search = ContactFactory::search();
        $search->filter('contact_qb_id', $this->getId());
        $contacts = $search->select();
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                $contact->setProperty('fully_qualified_name', $fullyQualifiedName);
                if (!$contact->save()) {
                    return false;
                }
            }
        }
        return true;
    }

}
