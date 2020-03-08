<?php

class MLSListingDetailView extends AbstractMLSListingDetailView{
    protected $mainContentClass = "relisting__main-content";
    protected $openHouses = array();

    public function __construct(AbstractMLSListing $listing)
    {
        parent::__construct($listing);
        
        $this->addCSS('resources/external/slick-1.6.0/slick/slick.css');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick-theme.css');
        $this->addJS('resources/external/slick-1.6.0/slick/slick.js');

        $this->openHouses = $this->listing->getOpenHouses();
    }

    protected $isSent = false;
    public function setSent($isSent){
        $this->isSent = $isSent;
    }

    protected $form = NULL;
    public function setForm($form){
        $this->form = $form;
        $this->buildForm();
    }

    protected function buildForm(){
        if(empty($this->form)){
            $this->form = new GI_Form('detail_contact');
        }

        $addClasses = '';
        if($this->isSent){
            $addClasses = 'contact__form-wrap_type_thankyou';
        }

        $this->form->addHTML('<div class="contact__form-wrap '.$addClasses.'">');
            if(!$this->isSent){
                $this->form->addHTML('<h3 class="relisting-detail__contact-form-title">Please enter your contact info</h3>');
                $this->form->addHTML('<div class="relisting-detail__contact-form-fields">');
                    $this->form->addField('first_name', 'text', array(
                        'required' => true,
                        'placeHolder' => 'First Name*'
                    ));
                    $this->form->addField('last_name', 'text', array(
                        'required' => true,
                        'placeHolder' => 'Last Name*'
                    ));
                    $this->form->addField('r_email', 'email', array(
                        'required' => true,
                        'placeHolder' => 'Email*'
                    ));
                    $this->form->addField('phone', 'phone', array(
                        'required' => true,
                        'placeHolder' => 'Phone Number*'
                    ));
                    $this->form->addField('mls_number', 'hidden', array(
                        'value' => $this->listing->getMLSNumber()
                    ));
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="relisting-detail__contact-form-buttons">');
                    $this->form->addHTML('<span class="submit_btn button button_theme_secondary button_has_icon">Submit <span class="button__icon"></span></span>');
                $this->form->addHTML('</div>');
            }else{
                $this->form->addHTML('<h3 class="relisting-detail__contact-form-title"><b>Thank you,</b><br><br> Civil Real Estate Local Expert will be in contact with you promtly</h3>');
            }
        $this->form->addHTML('</div>');
    }

    protected function buildViewHeader(){
        return $this;
    }

    protected function openViewBodyContent() {
        $this->addHTML('<div class="re_listing_wrap relisting-detail">');
    }

    protected function addUploadedImageSection(){
        $width = 120;
        $height = 80;

        $this->addHTML('<div class="section section_type_listing-detail-slider">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');

                        $this->addHTML('<div class="relisting-detail__slider-wrap">');
                            $this->addHTML('<div class="relisting-detail__slider">');
                                $this->addHTML($this->listing->getImagesHTML($width, $height));
                            $this->addHTML('</div>');
                            $this->addHTML('<span class="relisting-detail__slider-next"></span>');
                            $this->addHTML('<span class="relisting-detail__slider-prev"></span>');
                        $this->addHTML('</div>');

                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addViewBodyContent(){
        $this->openViewBodyContent();
        $this->addUploadedImageSection();
        $this->addHighlightInfoSection();
        $this->addCTASection();
        $this->addDetailInfoSection();
        $this->addMapSection();
        $this->addOpenHouseHoursSection();
        $this->addContactFormSection();
    }

    protected function addHighlightInfoSection(){
        $this->addHTML('<div class="section section_type_listing-detail-highlight">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-left">');
                        $this->addHTML('<div class="relisting-detail__back-link">');
                            
                            $lastURL = SessionService::getValue('last_listing_list_url');
                            if(!empty($lastURL)){
                                $url = GI_URLUtils::buildURL($lastURL);
                            }else{
                                $url = GI_URLUtils::buildURL(array(
                                    'controller' => 'relisting',
                                    'action' => 'index'
                                ));
                                if(($this->openHouses)){
                                    $url = GI_URLUtils::buildURL(array(
                                        'controller' => 'relisting',
                                        'action' => 'openHouse'
                                    ));
                                }
                            }

                            $this->addHTML('<a href="'.$url.'" class="relisting-detail__back-link-text"><span class="relisting-detail__back-link-icon"></span> Back to List</a>');

                        $this->addHTML('</div>');
                        $this->addHTML('<div class="relisting-detail__favourite">');
                            $this->addHTML('<p class="relisting-detail__favourite-text"><span class="relisting-detail__favourite-icon"></span> Favourite</p>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-9">');
                        $this->addHTML('<div class="relisting-detail__highlight-info">');
                            $this->addHTML('<div class="relisting-detail__highlight-row">');
                                $this->addHTML('<div class="relisting-detail__address-wrap">');
                                    $this->addHTML('<h3 class="relisting-detail__address">');
                                        // $this->addHTML('<b>47 Newton Crescent</b>');
                                        // $this->addHTML('Port Moody, British Columbia');
                                        $address = $this->listing->getAddress();
                                        if(strpos($address, '<br/>') !== false){
                                            $addressArr = explode("<br/>", $address);
                                            $address = "<b>{$addressArr[0]}</b>";
                                            $address .= $addressArr[1];
                                        }
                                        $this->addHTML($address);
                                    $this->addHTML('</h3>');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="relisting-detail__price">');
                                    $price = $this->listing->getDisplayListPrice(true);
                                    $this->addHTML($price);
                                $this->addHTML('</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="relisting-detail__highlight-row relisting-detail__highlight-row_type_features">');
                                $featureInfo = array(
                                    [
                                        'title' => 'Square Footage',
                                        'value' => $this->listing->getDisplaySquareFootage(),
                                        'replacement' => array(
                                            'title' => 'Acreage',
                                            'value' => $this->listing->getDisplayAcreage()
                                        )
                                    ],
                                    [
                                        'title' => 'Property Type',
                                        'value' =>  $this->listing->getLinkedPropertyTypeTagTitle()
                                    ],
                                    [
                                        'title' => 'Bathrooms',
                                        'value' => $this->listing->getProperty('mls_listing_res.total_bathrooms')
                                    ],
                                    [
                                        'title' => 'Bedrooms',
                                        'value' => $this->listing->getProperty('mls_listing_res.total_bedrooms')
                                    ],
                                    [
                                        'title' => 'Type of Dwelling',
                                        'value' => $this->listing->getTagTypeTitle()
                                    ],
                                    [
                                        'title' => 'Area',
                                        'value' => $this->listing->getAreaTitle()
                                    ],
                                    [
                                        'title' => 'Sub Area',
                                        'value' => $this->listing->getSubAreaTitle()
                                    ],
                                    [
                                        'title' => 'MLS® Number',
                                        'value' => $this->listing->getMLSNumber()
                                    ],
                                    [
                                        'title' => 'Listing Brokerage',
                                        'value' => $this->listing->getFirmName()
                                    ],
                                    [
                                        'title' => 'Year Built',
                                        'value' => $this->listing->getYearBuilt()
                                    ],
                                );
                                foreach($featureInfo as $feature){
                                    $title = $feature['title'];
                                    $value = $feature['value'];
                                    if(empty($value)){
                                        if(isset($feature['replacement'])){
                                            $title = $feature['replacement']['title'];
                                            $value = $feature['replacement']['value'];
                                        }
                                        if(empty($value)){
                                            continue;
                                        }
                                    }
                                    $this->addHTML('<div class="relisting-detail__feature-item">');
                                        $this->addHTML('<span class="relisting-detail__feature-title">'.$title.'</span>');
                                        $this->addHTML('<span class="relisting-detail__feature-value">');
                                            $this->addHTML($value);
                                        $this->addHTML('</span>');
                                    $this->addHTML('</div>');
                                }
                            $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addCTASection(){
        $contactURL = GI_URLUtils::buildCleanURL(array(
            'controller' => 'static',
            'action' => 'contact'
        ));
        $this->addHTML('<div class="section section_type_listing-detail-cta section_bg_primary">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-8 col-md-push-2">');
                        $this->addHTML('<h3 class="relisting-detail__cta-title">Want to know more?</h3> ');
                        $this->addHTML('<div class="relisting-detail__cta-button-wrap">');
                            $this->addHTML('<a href="'.$contactURL.'" class="button button_theme_thirdary">Contact Us Now</a>');
                            $this->addHTML('<a href="tel:'.SITE_PHONE.'" class="button button_theme_outline_white">Call Us '.SITE_PHONE.'</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addDetailInfoSection(){
        $this->addHTML('<div class="section section_type_listing-detail-full section_bg_dark">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row relisting-detail__info-row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                        $this->addHTML('<h3 class="relisting-detail__info-title">Description</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-9">');
                        $this->addHTML('<p class="relisting-detail__info-content">');
                            $listingPublicRemark = $this->listing->getPublicRemarks(true);
                            $this->addHTML(GI_StringUtils::nl2brHTML(GI_StringUtils::convertURLs($listingPublicRemark)));
                        $this->addHTML('</p>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                // $this->addHTML('<div class="row relisting-detail__info-row">');
                //     $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                //         $this->addHTML('<h3 class="relisting-detail__info-title">Strata Info</h3>');
                //     $this->addHTML('</div>');
                //     $this->addHTML('<div class="col-xs-12 col-md-9">');
                //         $this->addHTML('<p class="relisting-detail__info-content">');
                //             $this->addHTML('Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam.');
                //         $this->addHTML('</p>');
                //     $this->addHTML('</div>');
                // $this->addHTML('</div>');
                $listingFeatures = $this->listing->getFeatureArr();
                if(!empty($listingFeatures)){
                    $this->addHTML('<div class="row relisting-detail__info-row">');
                        $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                            $this->addHTML('<h3 class="relisting-detail__info-title">Listing Features</h3>');
                        $this->addHTML('</div>');
                        $this->addHTML('<div class="col-xs-12 col-md-9">');
                            $this->addHTML('<p class="relisting-detail__info-content">');
                                // $listingFeatures = array(
                                //     'Bathrooms',
                                //     'Parking',
                                //     'Visitor Parking',
                                //     'Storage',
                                //     'Bike Storage',
                                //     'Private Outdoor Space',
                                //     'Fireplace',
                                //     'In-suite Laundry',
                                //     'Common Outdoor Space',
                                //     'Cleaning Included',
                                //     'Concièrge',
                                //     'Pet Friendly'
                                // );
                                foreach($listingFeatures as $feature) {
                                    $this->addHTML('<span class="relisting-detail__info-content-tag">'.$feature.'</span>');
                                }
                            $this->addHTML('</p>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                }
                $amenities = $this->listing->getProperty('amenities');
                if(!empty($amenities)){
                    $this->addHTML('<div class="row relisting-detail__info-row">');
                        $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                            $this->addHTML('<h3 class="relisting-detail__info-title">Amenities</h3>');
                        $this->addHTML('</div>');
                        $this->addHTML('<div class="col-xs-12 col-md-9">');
                            $this->addHTML('<p class="relisting-detail__info-content">');
                                $listingFeatures = explode(',', $amenities);
                                foreach($listingFeatures as $feature) {
                                    $this->addHTML('<span class="relisting-detail__info-content-tag">'.$feature.'</span>');
                                }
                            $this->addHTML('</p>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                }
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addOpenHouseHoursSection(){
        if(empty($this->openHouses)){
            return;
        }
        $this->addHTML('<div class="section section_type_open-house-hours open-house-hours">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6 col-md-push-3">');
                        $this->addHTML('<h3 class="open-house-hours__title">Open House for this Property</h3>');
                        $this->addHTML('<div class="open-house-hours__schedule-wrap">');
                            foreach($this->openHouses as $openHouse){
                                $startDate = date('l, F j, Y', strtotime($openHouse->getProperty('oh_start_date')));
                                $startTime = date('g:i a', strtotime($openHouse->getProperty('oh_start_time')));
                                $endTime = date('g:i a', strtotime($openHouse->getProperty('oh_end_time')));
                                $this->addHTML('<a href="" class="button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span> '.$startDate.' <span class="hour">'.$startTime.' to '.$endTime.'</span></a>');
                            }
                            // $this->addHTML('<a href="" class="button button_theme_secondary button_has-icon"> <span class="button__icon button__icon_type_clock"></span> Saturday, February 28, 2020 <span class="hour">2:00pm to 4:00pm</span></a>');
                        $this->addHTML('</div>');
                        $this->addHTML('<div class="open-house-hours__buttons">');
                            $this->addHTML('<a href="'.GI_URLUtils::buildURL(array(
                                'controller' => 'relisting',
                                'action' => 'openHouse'
                            )).'" class="button button_theme_outline">View All Open Houses</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addMapSection(){
        $addressString = $this->listing->getAddress(false);
        $this->addHTML('<div class="section section_type_listing-detail-map">');
            $this->addHTML('<div class="embeded-map"></div>');
                $this->addHTMl('<div id="embedded_map" class="embedded-map__map" data-address="'.$addressString.'">');
                $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addContactFormSection(){
        $this->addHTML('<div class="section section_type_listing-detail-form section_bg_primary">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-5 col-md-push-1 relisting-detail__contact-left-col">');
                        $this->addHTML('<h3 class="relisting-detail__contact-title">Want to know more?</h3>');
                        $this->addHTML('<div class="relisting-detail__contact-button-wrap">');
                            $this->addHTML('<a href="tel:'.SITE_PHONE.'" class="button button_theme_outline_white">Call Us '.SITE_PHONE.'</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-5 col-md-push-1">');
                        $this->addHTML('<div class="relisting-detail__contact-form-wrap">');
                            $this->addHTML($this->form->getForm());
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

}
