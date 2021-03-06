<?php

class RECatalogView extends AbstractRECatalogView{
    protected $listingOpenHouses = NULL;

    public function __construct($listing, bool $isOpenHouse) {
        parent::__construct($listing);
        $this->isOpenHouse = $isOpenHouse;
    }

    protected $isOpenHouse = false;
    public function setIsOpenHouse(bool $isOpenHouse){
        $this->isOpenHouse = $isOpenHouse;
    }

    function checkValue($val, $prefix = null, $surfix = null){
        if(!empty($val)){
            return $prefix . $val . $surfix;
        }
        
        return null;
    }
    
    protected function openListingWrap(){
        $addClass = '';
        if($this->isOpenHouse){
            $addClass = 'relisting-item_type_open-house';
        }
        $this->addHTML('<div class="relisting-item '.$addClass.'" onclick="location.href=\''.$this->listing->getViewURL().'\'">');

        if($this->isOpenHouse){
            return $this;
        }

        $listingOpenHouses = $this->getListingOpenHouses();
        if(!empty($listingOpenHouses)){
            $this->addOpenHouseTag();
        }
        return $this;
    }

    protected function addListingContent(){
        $this->addHTML('<div class="relisting-item__featured-image">');
        $this->addFeaturedImage();
        $this->addHTML('</div>');

        $openHourHTML = '';
        if($this->isOpenHouse){
            $openHourHTML = $this->getOpenHouseSchedulesHTML();
        }

        $this->addHTML('<div class="relisting-item__main-content-wrap">');
            $this->addHTML('<div class="relisting-item__overview-wrap">');
                $this->addHTML('<div class="relisting-item__title-description">');
                    $this->addHTML('<div class="relisting-item__title">');
                        $this->addListingTitle();
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="relisting-item__other-fields">');
                        $otherField = [];
                        $dwellingTypeTitle = $this->listing->getTagTypeTitle();
                        if(!empty($dwellingTypeTitle)){
                            $otherField[] = $dwellingTypeTitle;
                        }
                        $totalBedrooms = $this->listing->getProperty('mls_listing_res.total_bedrooms');
                        if(!empty($totalBedrooms)){
                            $otherField[] = $totalBedrooms.' Bedroom';
                        }
                        $totalBaths = $this->listing->getProperty('mls_listing_res.total_baths');
                        if(!empty($totalBedrooms)){
                            $otherField[] = $totalBaths.' Bathrooms';
                        }
                        $otherField = implode(', ', $otherField);
                        $this->addHTML('<p>'.$otherField.'</p>');
                    $this->addHTML('</div>');
                    if($this->isOpenHouse){
                        $this->addFeatures();
                    }
                    // $this->addSummary();
                $this->addHTML('</div>');
                $this->addHTML('<div class="relisting-item__view-button-wrap">');
                    if($this->isOpenHouse){
                        $this->addHTML($openHourHTML);
                    }
                    $this->addViewCTA();
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            if(!$this->isOpenHouse){
                $this->addFeatures();
            }
            
            if($this->isOpenHouse){
                $this->addHTML('<div class="relisting-item__view-button-wrap relisting-item__view-button-wrap_type_mobile">');
                    $this->addHTML($openHourHTML);
                    $this->addViewCTA();
                $this->addHTML('</div>');
            }
        $this->addHTML('</div>');
    }

    protected function addFeatures(){
        $this->addHTML('<div class="relisting-item__features-wrap">');
            $this->addHTML('<div class="relisting-item__square-footage">');
                $sqft = $this->listing->getDisplaySquareFootage();
                if(!empty($sqft)){
                    $this->addHTML('<span class="relisting-item__feature-title">Square Footage</span>');

                    $this->addHTML('<span class="relisting-item__feature-value">'.$sqft.'</span>');
                } else {
                    $acreage = $this->listing->getDisplayAcreage();
                    if(!empty($acreage)){
                        $this->addHTML('<span class="relisting-item__feature-title">Acreage</span>');

                        $this->addHTML('<span class="relisting-item__feature-value">'.$acreage.'</span>');
                    }
                }
            $this->addHTML('</div>');
            $this->addHTML('<div class="relisting-item__price">');
                $this->addHTML('<span class="relisting-item__feature-title">Price</span>');
                $this->addHTML('<span class="relisting-item__feature-value">');
                    $this->addListingPrice();
                $this->addHTML('</span>');
            $this->addHTML('</div>');
            // $this->addHTML('<div class="relisting-item__favourite">');
            //     $this->addHTML('<p class="relisting-item__favourite-text">Favourite <span class="relisting-item__favourite-icon"></span></p>');
            // $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addListingTitle(){
        $address = $this->listing->getAddress();
        if(strpos($address, '<br/>') !== false){
            $addressArr = explode("<br/>", $address);
            $address = "<b>{$addressArr[0]}</b>";
            $address .= $addressArr[1];
        }
        $this->addHTML($address);
    }

    protected function getOpenHouseSchedulesHTML(){
        $openHouses = $this->getListingOpenHouses();
        $url = $this->listing->getViewURL();
        $html = '';
        foreach($openHouses as $openHouse){
            $startDate = date('l, F j, Y', strtotime($openHouse->getProperty('oh_start_date')));
            $startTime = date('g:i a', strtotime($openHouse->getProperty('oh_start_time')));
            $endTime = date('g:i a', strtotime($openHouse->getProperty('oh_end_time')));
            $html .= '<a href="'.$url.'" class="relisting-item__open-house-btn button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span>'.$startDate.'<br> '.$startTime.' to '.$endTime.'</a>';
        }
        return $html;
        // $this->addHTML('<a href="" class="relisting-item__open-house-btn button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span> Saturday, February 28, 2020<br> 2:00pm to 4:00pm</a>');
    }
    
    protected function addViewCTA(){
        $url = $this->listing->getViewURL();
        $this->addHTML('<a href="' . $url . '" class="button button_theme_primary relisting-item__view-detail" title="View Details">View Details</a>');
    }

    protected function getListingOpenHouses(){
        if(is_null($this->listingOpenHouses)){
            $this->listingOpenHouses = $this->listing->getOpenHouses();
        }
        return $this->listingOpenHouses;
    }

    protected function addOpenHouseTag(){
        $this->addHTML('<div class="relisting-item__oh-tag"><span class="relisting-item__oh-tag-text">OPEN HOUSE</span></div>');
    }
}