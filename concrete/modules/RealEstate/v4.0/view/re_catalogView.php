<?php

class RECatalogView extends AbstractRECatalogView{
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
        return $this;
    }

    protected function addListingContent(){
        $this->addHTML('<div class="relisting-item__featured-image">');
        $this->addFeaturedImage();
        $this->addHTML('</div>');

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
                        $this->addOpenHouseSchedules();
                    }
                    $this->addViewCTA();
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            if(!$this->isOpenHouse){
                $this->addFeatures();
            }
        $this->addHTML('</div>');
    }

    protected function addFeatures(){
        $this->addHTML('<div class="relisting-item__features-wrap">');
            $this->addHTML('<div class="relisting-item__square-footage">');
                $this->addHTML('<span class="relisting-item__feature-title">Square Footage</span>');
                $lotSize = $this->listing->getDisplaySquareFootage();
                $this->addHTML('<span class="relisting-item__feature-value">'.$lotSize.'</span>');
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

    protected function addOpenHouseSchedules(){
        $openHouses = $this->listing->getOpenHouses();
        $url = $this->listing->getViewURL();
        foreach($openHouses as $openHouse){
            $startDate = date('l, F j, Y', strtotime($openHouse->getProperty('oh_start_date')));
            $startTime = date('g:i a', strtotime($openHouse->getProperty('oh_start_time')));
            $endTime = date('g:i a', strtotime($openHouse->getProperty('oh_end_time')));
            $this->addHTML('<a href="'.$url.'" class="relisting-item__open-house-btn button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span>'.$startDate.'<br> '.$startTime.' to '.$endTime.'</a>');
        }
        // $this->addHTML('<a href="" class="relisting-item__open-house-btn button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span> Saturday, February 28, 2020<br> 2:00pm to 4:00pm</a>');
    }
    
    protected function addViewCTA(){
        $url = $this->listing->getViewURL();
        $this->addHTML('<a href="' . $url . '" class="button button_theme_primary relisting-item__view-detail" title="View Details">View Details</a>');
    }
}