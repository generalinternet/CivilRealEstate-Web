<?php

class MLSListing extends AbstractMLSListing {
    
    public function isInTheBooklet(){
        $listingId = $this->getProperty('id');
        if (!empty($listingId) && !empty(SessionService::getValue('bookletListings'))) {
            return true;
        }

        return false;
    }
    
    /**
     * 
     * @param array $options
     * @return type
     */
    public function getAddressWithOptions($options){
        $addrStreet = $this->getProperty('addr');
        if (array_key_exists('city', $options) && $options['city']) {
            $addrCity = $this->getCityTitle();
        } else {
            $addrCity = '';
        }
        
        if (array_key_exists('province', $options) && $options['province']) {
            $addrRegion = $this->getProperty('province');
        } else {
            $addrRegion = '';
        }
        
        if (array_key_exists('postal_code', $options) && $options['postal_code']) {
            $addrCode = $this->getProperty('postal_code');
        } else {
            $addrCode = '';
        }
        
        if (array_key_exists('break_lines', $options) && $options['break_lines']) {
            $breakLines = true;
        } else {
            $breakLines = false;
        }
        $addr = GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, NULL, $breakLines);
        return $addr;
    }
    
    public function getDisplayPublicRemarks($limit = 200) {
        return GI_StringUtils::summarize($this->getProperty('public_remarks'), $limit);
    }
    
    protected static function formatMoney($amount, $withCommas = true, $decimals = 2) {
        $string = '<span class="unit">$</span><span class="amount">';
        if ($withCommas) {
            $string .= number_format($amount, $decimals);
        } else {
            $string .= number_format($amount, $decimals, '.', '');
        }
        $string .= '</span>';
        return $string;
    }
}
