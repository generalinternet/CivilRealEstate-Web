<?php

class MLSListingDetailView extends AbstractMLSListingDetailView{
    protected $form = NULL;
    protected $mainContentClass = "relisting__main-content";

    public function __construct(AbstractMLSListing $listing, GI_Form $form = NULL)
    {
        parent::__construct($listing);

        if(empty($form)){
            $form = new GI_Form('contact');
        }

        $this->form = $form;
        $this->buildForm();
        
        $this->addCSS('resources/external/slick-1.6.0/slick/slick.css');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick-theme.css');
        $this->addJS('resources/external/slick-1.6.0/slick/slick.js');
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="contact__form-wrap">');
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
                $this->form->addField('email', 'email', array(
                    'required' => true,
                    'placeHolder' => 'Email*'
                ));
                $this->form->addField('phone', 'phone', array(
                    'required' => true,
                    'placeHolder' => 'Phone Number*'
                ));
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="relisting-detail__contact-form-buttons">');
                $this->form->addHTML('<a href="" class="button button_theme_secondary button_has_icon">Submit <span class="button__icon"></span></a>');
            $this->form->addHTML('</div>');
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
            $this->addHTML('<div class="relisting-detail__slider-wrap">');
                $this->addHTML('<div class="relisting-detail__slider">');
                    $this->addHTML($this->listing->getImagesHTML($width, $height));
                $this->addHTML('</div>');
                $this->addHTML('<span class="relisting-detail__slider-next"></span>');
                $this->addHTML('<span class="relisting-detail__slider-prev"></span>');
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
        $this->addContactFormSection();
    }

    protected function addHighlightInfoSection(){
        $this->addHTML('<div class="section section_type_listing-detail-highlight">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                        $this->addHTML('<div class="relisting-detail__favourite">');
                            $this->addHTML('<p class="relisting-detail__favourite-text">Favourite <span class="relisting-item__favourite-icon"></span></p>');
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
                                        'value' => $this->listing->getDisplayLotSizeSqft()
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
                                    if(empty($feature['value'])){
                                        continue;
                                    }
                                    $this->addHTML('<div class="relisting-detail__feature-item">');
                                        $this->addHTML('<span class="relisting-detail__feature-title">'.$feature['title'].'</span>');
                                        $this->addHTML('<span class="relisting-detail__feature-value">');
                                            $this->addHTML($feature['value']);
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
                $this->addHTML('<div class="row relisting-detail__info-row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                        $this->addHTML('<h3 class="relisting-detail__info-title">Strata Info</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-9">');
                        $this->addHTML('<p class="relisting-detail__info-content">');
                            $this->addHTML('Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam.');
                        $this->addHTML('</p>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                $this->addHTML('<div class="row relisting-detail__info-row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                        $this->addHTML('<h3 class="relisting-detail__info-title">Listing Features</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-9">');
                        $this->addHTML('<p class="relisting-detail__info-content">');
                            $listingFeatures = array(
                                'Bathrooms',
                                'Parking',
                                'Visitor Parking',
                                'Storage',
                                'Bike Storage',
                                'Private Outdoor Space',
                                'Fireplace',
                                'In-suite Laundry',
                                'Common Outdoor Space',
                                'Cleaning Included',
                                'Concièrge',
                                'Pet Friendly'
                            );
                            foreach($listingFeatures as $feature) {
                                $this->addHTML('<span class="relisting-detail__info-content-tag">'.$feature.'</span>');
                            }
                        $this->addHTML('</p>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                $this->addHTML('<div class="row relisting-detail__info-row">');
                    $this->addHTML('<div class="col-xs-12 col-md-3 relisting-detail__col-pad-right">');
                        $this->addHTML('<h3 class="relisting-detail__info-title">Amenities</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-9">');
                        $this->addHTML('<p class="relisting-detail__info-content">');
                            $listingFeatures = array(
                                'Pool',
                                'Steam',
                                'Common Room',
                                'Guest Suite',
                                'Sauna',
                            );
                            foreach($listingFeatures as $feature) {
                                $this->addHTML('<span class="relisting-detail__info-content-tag">'.$feature.'</span>');
                            }
                        $this->addHTML('</p>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
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
                            $this->addHTML('<a href="" class="button button_theme_outline_white">Call Us  888.333.7777</a>');
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
