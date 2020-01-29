<?php

abstract class AbstractContactInfoAddressDetailView extends GI_View {
    
    protected $contactInfo;
    protected $title = 'Address';
    protected $name = '';
    
    public function __construct(AbstractContactInfoAddress $contactInfoAddress) {
        parent::__construct();
        $this->contactInfo = $contactInfoAddress;
        $this->title = $contactInfoAddress->getTypeTitle();
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function setName($name){
        $this->name = $name;
    }
    
    protected function buildView() {
        $addrStreet = $this->contactInfo->getProperty('contact_info_address.addr_street');
        $addrStreetTwo = $this->contactInfo->getProperty('contact_info_address.addr_street_two');
        $addrCity = $this->contactInfo->getProperty('contact_info_address.addr_city');
        $addrRegion = $this->contactInfo->getProperty('contact_info_address.addr_region');
        $addrCountry = $this->contactInfo->getProperty('contact_info_address.addr_country');
        $addrCode = $this->contactInfo->getProperty('contact_info_address.addr_code');
        
        $dataAttrString = 'data-id="' . $this->contactInfo->getId() . '"';
        $dataAttrString .= 'data-addr-street="' . $addrStreet . '" ';
        $dataAttrString .= 'data-addr-street-two="' . $addrStreetTwo . '" ';
        $dataAttrString .= 'data-addr-city="' . $addrCity . '" ';
        $dataAttrString .= 'data-addr-region="' . $addrRegion . '" ';
        $dataAttrString .= 'data-addr-country="' . $addrCountry . '" ';
        $dataAttrString .= 'data-addr-code="' . $addrCode . '" ';
        
        $classNames = '';
        $qbLinked = $this->contactInfo->getProperty('qb_linked');
        if (!empty($qbLinked)) {
            $classNames .= ' qb_linked';
        }
        
        $this->addHTML('<div class="contact_info_view addr_view'.$classNames.'" ' . $dataAttrString . '>');
        $addressString = '';
        if(!empty($this->name)){
            $addressString = $this->name . '<br/>';
        }
        $addressString .= GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, $addrCountry, true, $addrStreetTwo);
        $title = $this->title;
        if(empty($title)){
            $title = NULL;
        }
        $this->addContentBlock($addressString, $this->title, true);
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
