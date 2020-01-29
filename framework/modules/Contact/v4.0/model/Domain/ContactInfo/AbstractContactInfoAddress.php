<?php
/**
 * Description of AbstractContactInfoAddress
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactInfoAddress extends AbstractContactInfo {

    public function __construct($map) {
        parent::__construct($map);
        $this->setFieldPrefix('contact_info_address_');
    }

    public function getFormView(GI_Form $form, $otherData = array()) {
        $formView = new ContactInfoAddressFormView($form, $this, $otherData);
        return $formView;
    }
    
    public function getLocationAddressFormView($form) {
        $formView = new ContactInfoLocationAddressFormView($form, $this);
        $formView->hideTypeField(true);
        return $formView;
    }

    public function validateForm(\GI_Form $form) {
        $valid = parent::validateForm($form);
        $addrCountry = filter_input(INPUT_POST, $this->getFieldName('addr_country'));
        $addrRegion = filter_input(INPUT_POST, $this->getFieldName('addr_region'));
        if(GeoDefinitions::forceRegionForCountryCode($addrCountry) && (empty($addrRegion) || $addrRegion == 'NULL')){
            $form->addFieldError($this->getFieldName('addr_region'), 'required', 'Required field.');
            $valid = false;
        }
        return $valid;
    }
    
    public function setPropertiesFromForm(GI_Form $form) {
        $addrStreet = filter_input(INPUT_POST, $this->getFieldName('addr_street'));
        $addrStreetTwo = filter_input(INPUT_POST, $this->getFieldName('addr_street_two'));
        $addrCity = filter_input(INPUT_POST, $this->getFieldName('addr_city'));
        $addrRegion = filter_input(INPUT_POST, $this->getFieldName('addr_region'));
        $customAddrRegion = filter_input(INPUT_POST, $this->getFieldName('custom_addr_region'));
        $addrCountry = filter_input(INPUT_POST, $this->getFieldName('addr_country'));
        if (empty($addrCountry)) {
            $addrCountry = ProjectConfig::getDefaultCountryCode();
        }
        $addrCode = filter_input(INPUT_POST, $this->getFieldName('addr_code'));
        if(!empty($addrRegion)){
            $addrRegionArray = explode('_',$addrRegion);
            if(isset($addrRegionArray[1])){
                $addrRegion = $addrRegionArray[1];
            }
        } else {
            $addrRegion = ProjectConfig::getDefaultRegionCode();
        }
        $this->setProperty('contact_info_address.addr_street', $addrStreet);
        $this->setProperty('contact_info_address.addr_street_two', $addrStreetTwo);
        $this->setProperty('contact_info_address.addr_city', $addrCity);
        if(GeoDefinitions::forceRegionForCountryCode($addrCountry)){
            $this->setProperty('contact_info_address.addr_region', $addrRegion);
        } else {
            $this->setProperty('contact_info_address.addr_region', $customAddrRegion);
        }
        
        $this->setProperty('contact_info_address.addr_country', $addrCountry);
        $this->setProperty('contact_info_address.addr_code', $addrCode);
        return parent::setPropertiesFromForm($form);
    }

    /**
     * @deprecated - use setPropertiesFromModel instead - remove in V5
     * @param AbstractContactInfoAddress $address
     * @return $this
     */
    public function setPropertiesFromOtherAddress(AbstractContactInfoAddress $address) {
        $this->setProperty('contact_info_address.addr_street', $address->getProperty('contact_info_address.addr_street'));
        $this->setProperty('contact_info_address.addr_street_two', $address->getProperty('contact_info_address.addr_street_two'));
        $this->setProperty('contact_info_address.addr_city', $address->getProperty('contact_info_address.addr_city'));
        $this->setProperty('contact_info_address.addr_region', $address->getProperty('contact_info_address.addr_region'));
        $this->setProperty('contact_info_address.addr_country', $address->getProperty('contact_info_address.addr_country'));
        $this->setProperty('contact_info_address.addr_code', $address->getProperty('contact_info_address.addr_code'));
        return $this;
    }

    public function getAddressString($breaklines = false, $forceIncludeCountry = true) {
        $addrStreet = $this->getProperty('contact_info_address.addr_street');
        $addrStreetTwo = $this->getProperty('contact_info_address.addr_street_two');
        $addrCity = $this->getProperty('contact_info_address.addr_city');
        $addrRegion = $this->getProperty('contact_info_address.addr_region');
        $addrCountry = $this->getProperty('contact_info_address.addr_country');
        $addrCode = $this->getProperty('contact_info_address.addr_code');
        $addressString = GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, $addrCountry, $breaklines, $addrStreetTwo, $forceIncludeCountry);
        return $addressString;
    }
    
    public function getAddressString2Lines(){
        $addrStreet = $this->getProperty('contact_info_address.addr_street');
        $addrCity = $this->getProperty('contact_info_address.addr_city');
        $addrRegion = $this->getProperty('contact_info_address.addr_region');
        $addrCode = $this->getProperty('contact_info_address.addr_code');
        $addressString = GI_StringUtils::buildAddrString2Lines($addrStreet, $addrCity, $addrRegion, $addrCode);
        return $addressString;
    }
    
    public function getRegion($includeCountry = false){
        $regionCode = $this->getProperty('contact_info_address.addr_region');
        $countryCode = $this->getProperty('contact_info_address.addr_country');
        if($includeCountry){
            $addrRegionString = $regionCode;            
            $addrCountryName = GeoDefinitions::getCountryNameFromCode($countryCode);
            $addrRegionString .= ', ' . $addrCountryName;
        } else {
            $addrRegionString = GeoDefinitions::getRegionNameFromCode($countryCode, $regionCode);
        }
        return $addrRegionString;
    }
    
    public function getCountry(){
        $countryCode = $this->getProperty('contact_info_address.addr_country');
        $addrCountryName = GeoDefinitions::getCountryNameFromCode($countryCode);
        return $addrCountryName;
    }
    
    public function getRegionGroupArray() {
        return GeoDefinitions::getRegionGroupOptions();
    }
    
    public function getRegionArray($countryCode = NULL) {
        return GeoDefinitions::getRegionOptions($countryCode);
    }
    
    /** @return AbstractContactInfoAddressDetailView */
    public function getDetailView() {
        $detailView = new ContactInfoAddressDetailView($this);
        return $detailView;
    }
    
    public function isSameAs(ContactInfoAddress $compareAddr){
        
        if($this->getProperty('contact_info_address.addr_code') != $compareAddr->getProperty('contact_info_address.addr_code')){
            return false;
        }
        
        if($this->getProperty('contact_info_address.addr_street') != $compareAddr->getProperty('contact_info_address.addr_street')){
            return false;
        }
        
        if($this->getProperty('contact_info_address.addr_street_two') != $compareAddr->getProperty('contact_info_address.addr_street_two')){
            return false;
        }
        
        if($this->getProperty('contact_info_address.addr_city') != $compareAddr->getProperty('contact_info_address.addr_city')){
            return false;
        }
        
        if($this->getProperty('contact_info_address.addr_region') != $compareAddr->getProperty('contact_info_address.addr_region')){
            return false;
        }
        
        if($this->getProperty('contact_info_address.addr_country') != $compareAddr->getProperty('contact_info_address.addr_country')){
            return false;
        }

        return true;
    }

    public function isMailAcceptable() {
        $street = $this->getProperty('contact_info_address.addr_street');
        $city = $this->getProperty('contact_info_address.addr_city');
        $region = $this->getProperty('contact_info_address.addr_region');
        $country = $this->getProperty('contact_info_address.addr_country');
        $code = $this->getProperty('contact_info_address.addr_code');
        if (empty($street) || empty($city) || empty($region) || empty($country) || empty($code)) {
            return false;
        }
        return true;
    }

    public function getFormColumnClass() {
        return 'full_width_in_modal';
    }

    public function getFormBlockAlignment() {
        return 'multi_column';
    }

    public function setRequiredDefaultProperties() {
        $this->setProperty('contact_info_address.addr_region', DEFAULT_REGION);
        $this->setProperty('contact_info_address.addr_country', DEFAULT_COUNTRY);
        return true;
    }

    public function getAddrProperty($key, $original = false) {
        return $this->getProperty('contact_info_address.' . $key, $original);
    }

    public function getQuickbooksAddressPropertiesArray($contactNameAsFirstLine = false, $contactName = '', $freeForm = false) {
        $properties = array();
        $streetOne = $this->getProperty('contact_info_address.addr_street');
        $streetTwo = $this->getProperty('contact_info_address.addr_street_two');
        $city = $this->getProperty('contact_info_address.addr_city');
        $region = $this->getProperty('contact_info_address.addr_region');
        $countryCode = $this->getProperty('contact_info_address.addr_country');
        $code = $this->getProperty('contact_info_address.addr_code');

        $country = $countryCode;
        if (!empty($country)) {
            $countryName = GeoDefinitions::getCountryNameFromCode($country);
            if (!empty($countryName)) {
                $country = $countryName;
            }
        }
        if ($contactNameAsFirstLine) {
            if (empty($contactName)) {
                $contact = $this->getContact();
                if (!empty($contact)) {
                    $contactName = $contact->getRealName();
                }
            } 
            if (!empty($contactName)) {
                $properties['Line1'] = $contactName;
            }
        }

        if (!empty($streetOne)) {
            if (isset($properties['Line1'])) {
                $properties['Line2'] = $streetOne;
            } else {
                $properties['Line1'] = $streetOne;
            }
        }
        if (!empty($streetTwo)) {
            if (isset($properties['Line2'])) {
                $properties['Line3'] = $streetTwo;
            } else {
                $properties['Line2'] = $streetTwo;
            }
        }
        if (!$freeForm) {
            if (!empty($city)) {
                $properties['City'] = $city;
            }
            if (!empty($region)) {
                $properties['CountrySubDivisionCode'] = $region;
            }
            if (!empty($country)) {
                $properties['Country'] = $country;
            }
            if (!empty($code)) {
                $properties['PostalCode'] = $code;
            }
        } else {
            $nextLineNumber = 1;
            for ($i=2;$i<6;$i++) {
                $nextLineNumber = $i;
                if (!isset($properties['Line' . $nextLineNumber])) {
                    break;
                }
            }
            $string = $city . ', ' . $region . ' ' . $code;
            $properties['Line' . $nextLineNumber] = $string;
            $nextLineNumber += 1;
            if ($nextLineNumber < 6 && !($countryCode == ProjectConfig::getDefaultCountryCode())) {
                $properties['Line' . $nextLineNumber] = $country;
            }
        }
        return $properties;
    }

    public function setPropertiesFromModel(GI_Model $model) {
        $this->setProperty('contact_info_address.addr_street', $model->getProperty('contact_info_address.addr_street'));
        $this->setProperty('contact_info_address.addr_street_two', $model->getProperty('contact_info_address.addr_street_two'));
        $this->setProperty('contact_info_address.addr_city', $model->getProperty('contact_info_address.addr_city'));
        $this->setProperty('contact_info_address.addr_region', $model->getProperty('contact_info_address.addr_region'));
        $this->setProperty('contact_info_address.addr_country', $model->getProperty('contact_info_address.addr_country'));
        $this->setProperty('contact_info_address.addr_code', $model->getProperty('contact_info_address.addr_code'));
        return true;
    }

}
