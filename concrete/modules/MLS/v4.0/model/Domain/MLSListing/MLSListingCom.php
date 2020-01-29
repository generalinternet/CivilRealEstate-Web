<?php

class MLSListingCom extends AbstractMLSListingCom {
    
//    public function isInTheBooklet(){
//        if(isset($_SESSION['bookletListings'])){
//            $listingId = $this->getProperty('id');
//            if(in_array($listingId, $_SESSION['bookletListings'])){
//                return true;
//            }
//        }
//
//        return false;
//    }
//    
//    public function getPropertySize(){
//        return $this->getProperty('lot_size_sqft');
//    }
//    
//    public function getRealtorName(){
//        $realtor = $this->getRealtor();
//        if(empty($realtor)){
//            return null;
//        }
//        
//        return $realtor->getName();
//    }
//    
//    public function getFirmName(){
//        $firm = $this->getFirm();
//        if(empty($firm)){
//            return null;
//        }
//        
//        return $firm->getName();
//    }
//    
//    public function getTagTypeTitle(){
//        $tags = $this->getTags();
//        if(empty($tags)){
//            return false;
//        }
//        
//        return $tags[0]->getProperty('title');
//    }
//    
//    public function getModifyURL(){
//        $url = GI_URLUtils::buildURL(array(
//            'controller' => 'realty',
//            'action' => 'modifyListing',
//            'id' => $this->getProperty('id'),
//            'type' => $this->getTypeRef()
//        ));
//        return $url;
//    }
//    
//    public function getViewAttrs(){
//        return array(
//            'controller' => 'listing',
//            'action' => 'view',
//            'id' => $this->getProperty('id'),
//        );
//    }
//    
//    public function getViewURL(){
//        $urlAttrs = $this->getViewAttrs();
//        $url = GI_URLUtils::buildURL($urlAttrs);
//        return $url;
//    }
//    
//    /**
//     * 
//     * @param array $options
//     * @return type
//     */
//    public function getAddressWithOptions($options){
//        $addrStreet = $this->getProperty('addr');
//        if (array_key_exists('city', $options) && $options['city']) {
//            $addrCity = $this->getCityTitle();
//        } else {
//            $addrCity = '';
//        }
//        
//        if (array_key_exists('province', $options) && $options['province']) {
//            $addrRegion = $this->getProperty('province');
//        } else {
//            $addrRegion = '';
//        }
//        
//        if (array_key_exists('postal_code', $options) && $options['postal_code']) {
//            $addrCode = $this->getProperty('postal_code');
//        } else {
//            $addrCode = '';
//        }
//        
//        if (array_key_exists('break_lines', $options) && $options['break_lines']) {
//            $breakLines = true;
//        } else {
//            $breakLines = false;
//        }
//        $addr = GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, NULL, $breakLines);
//        return $addr;
//    }
//    
//    public function getDisplayPublicRemarks($limit = 200) {
//        return GI_StringUtils::summarize($this->getProperty('public_remarks'), $limit);
//    }
//    
//    protected static function formatMoney($amount, $withCommas = true, $decimals = 2) {
//        $string = '<span class="unit">$</span><span class="amount">';
//        if ($withCommas) {
//            $string .= number_format($amount, $decimals);
//        } else {
//            $string .= number_format($amount, $decimals, '.', '');
//        }
//        $string .= '</span>';
//        return $string;
//    }
}
