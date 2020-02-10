<?php

class RECatalogView extends AbstractRECatalogView{
    
    protected function openListingWrap(){
        $this->addHTML('<div class="relisting-item">');
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
                        $this->addHTML('<p>Detached, 4 Bedroom, 3 Bathrooms</p>');
                    $this->addHTML('</div>');
                    // $this->addSummary();
                $this->addHTML('</div>');
                $this->addHTML('<div class="relisting-item__view-button-wrap">');
                    $this->addViewCTA();
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            $this->addHTML('<div class="relisting-item__features-wrap">');
                $this->addHTML('<div class="relisting-item__square-footage">');
                    $this->addHTML('<span class="relisting-item__feature-title">Square Footage</span>');
                    $this->addHTML('<span class="relisting-item__feature-value">6,410 <span class="unit">sqft</span></span>');
                $this->addHTML('</div>');
                $this->addHTML('<div class="relisting-item__price">');
                    $this->addHTML('<span class="relisting-item__feature-title">Price</span>');
                    $this->addHTML('<span class="relisting-item__feature-value">');
                        $this->addListingPrice();
                    $this->addHTML('</span>');
                $this->addHTML('</div>');
                $this->addHTML('<div class="relisting-item__favourite">');
                    $this->addHTML('<p class="relisting-item__favourite-text">Favourite <span class="relisting-item__favourite-icon"></span></p>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
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
    
    protected function addViewCTA(){
        $url = $this->listing->getViewURL();
        $this->addHTML('<a href="' . $url . '" class="button button_theme_primary" title="View Details">View Details</a>');
    }
}