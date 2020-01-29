<?php

class REListingDetailView extends AbstractREListingDetailView {
    
    protected $listingImages;

    /**
     * 
     * @param MLSListing or REListing $mlsListing
     */
    public function __construct($mlsListing) {
        parent::__construct($mlsListing);

        $this->addCSS('resources/css/search_detail.css');
        $this->addJS('resources/js/search_detail.js');
        $this->addCSS('resources/css/lightbox.css');
        $this->addJS('resources/js/lightbox.js');
        $this->addCSS('resources/css/scroll.css');
        $this->addJS('resources/js/scroll.js');
        $this->addCSS('resources/external/js/featherlight-1.7.13/featherlight.min.css'); //Mortgage calculator popup
        $this->addJS('resources/external/js/featherlight-1.7.13/featherlight.min.js');
        $this->addFinalContent('<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDIk7n8goeIhOiJivU42YCZ9M2xdxC5OSQ&callback=initMap" async defer></script>');
    
        if (!is_null($mlsListing->getProperty('addr'))) {
            $this->addSiteTitle('Listing-'.$mlsListing->getProperty('addr'));
            $this->setDescription('Listing:'.$mlsListing->getProperty('addr')
                    .', Price:'.$this->checkValue(number_format($mlsListing->getProperty('list_price')))
                    .', Beds:'.$this->checkValue($mlsListing->getProperty('mls_listing_res.total_bedrooms'))
                    .', Baths:'.$this->checkValue($mlsListing->getProperty('mls_listing_res.total_baths'))
                    .', Sqft:'.$this->checkValue($mlsListing->getPropertySize()));
        }
        
        //Set listing images
        $images = $this->mlsListing->getModifyImages();
        if(!empty($images)){
            $this->listingImages = array_merge($images, $this->mlsListing->getImages());
        }
        else{
            $this->listingImages = $this->mlsListing->getImages();
        }
    }
    
    public function buildView(){
//        if($this->mlsListing->getTypeRef() == 'CM_1'){
//            $this->buildComView();
//        }
//        else{
//            $this->buildResView();
//        }
        $this->addHeaderBannerSection();
        $this->addMainContentSection();
    }
    
    protected function addHeaderBannerSection() {
        $images = $this->listingImages;
        $displayAddr = $this->mlsListing->getAddressWithOptions(array(
            'city' => true,
            'province' => true,
            'break_lines' => true,
        ));
        $this->addHTML('<section class="header-banner-section listing-banner-section">');
            $this->addHTML('<div class="header-banner"');
                if (!empty($images)) {
                    $bannerImage = $images[0];
                    $this->addHTML(' style="background-image: url('.$bannerImage->getImageURL().');"');
                }
                
            $this->addHTML('>');
                $this->addHTML('<div class="container">');
                    $this->addHTML('<div class="banner-caption">');
                        $this->addHTML('<h1 class="listing-address">' . $displayAddr . '</h1>');
                    $this->addHTML('</div><!--.banner-caption-->');
                $this->addHTML('</div><!--.container-->');
            $this->addHTML('</div><!--.header-banner-->');
        $this->addHTML('</section>');
    }
    
    function checkValue($val, $prefix = null, $surfix = null){
        if(!empty($val)){
            return $prefix . $val . $surfix;
        }
        
        return null;
    }
    
    protected function addMainContentSection() {
        $this->addListingDetailSection();
        $this->addMapSection();
        $this->addMortgageCalculatorSection();
    }
    
    protected function addListingDetailSection() {
        $listing = $this->mlsListing;
        $images = $this->listingImages;
        $this->addContent('<section class="main-content-section listing_detail staus_'.$listing->getListingStatusRef().'">
                                <div class="container">
                                    <div class="top_btn_group">');
            $urlAttrs = array(
                'controller' => 'listing',
                'action' => 'index'
            );
            
            if (!empty(GI_URLUtils::getAttribute('type'))) {
                $urlAttrs['type'] = GI_URLUtils::getAttribute('type');
            }
            if (!empty(GI_URLUtils::getAttribute('queryId'))) {
                $urlAttrs['queryId'] = GI_URLUtils::getAttribute('queryId');
            }
            if (!empty(GI_URLUtils::getAttribute('pageNumber'))) {
                $urlAttrs['pageNumber'] = GI_URLUtils::getAttribute('pageNumber');
            }

            $this->addContent('<div class="back">&#10094;&#10094; Back to Listings</div>');

            if(!empty($listing->getProperty('virtual_tour_url'))){
                        $this->addContent('<a href="' . $listing->getProperty('virtual_tour_url') . '" target="_blank" class="video_tour">
                                            <img src="resources/css/images/play_icon.png" class="inline-block">
                                            <div class="inline-block">Virtual Tour</div>
                                            </a>');
            }

                $this->addContent('</div><!--.top_btn_group-->');

                $this->addContent('<div class="detail_content">');
                    $this->addContent('<div class="header">');
                        $this->addContent('<h2 class="title">' . $listing->getProperty('addr') . '</h2>');
                        if($listing->getListingStatusRef() != 'sold'){
                            $this->addContent('<div class="price">$' . number_format($listing->getProperty('list_price')) . '</div>');
                        }
                    $this->addContent('</div><!--.header-->');

                    $this->addContent('<div class="detail_images">
                                            <div class="image_view" data-count="' . sizeof($images) . '">
                                                <div class="prev" data-scroll="myScroll2"><img src="resources/media/icons/prev_arrow.svg" alt="left icon"></div>');
                            if (!empty($images) && isset($images[0])) {
                                $imageURL = $images[0]->getImageURL();
                                $this->addContent('<a href="' . $imageURL . '" data-lightbox="' . $listing->getProperty('id') . '" class="image_wrapper"><img src="' . $imageURL . '" alt="Listing image" class="main_img"></a>');
                            } else {
                                $this->addContent('<div class="no_listing_img main_img"></div>');
                            }    
                                $this->addContent('<div class="next" data-scroll="myScroll2"><img src="resources/media/icons/next_arrow.svg" alt="right icon"></div>
                                            </div><!--.image_view-->

                                            <div id="iscroll_wrapper">
                                                <ul id="scroller">');
                                                if (!empty($images)) {
                                                    $isFirst = true;
                                                    foreach ($images as $image) {
                                                        $imageURL = $image->getImageURL();
                                                        $this->addContent('<li '.(($isFirst)? 'class="current"':'').'><a class="image_wrapper"><img src="' . $imageURL . '" alt="Listing thumbnail image"></a></li>');
                                                        $isFirst = false;
                                                    }
                                                }
                                $this->addContent('</ul>
                                                <div class="prev" data-scroll="myScroll2"><img src="resources/media/icons/prev_arrow.svg" alt="left icon"></div>
                                                <div class="next" data-scroll="myScroll2"><img src="resources/media/icons/next_arrow.svg" alt="right icon"></div>
                                            </div><!--#iscroll_wrapper-->
                                        </div><!--.detail_images-->');

                    $this->addContent('<div class="detail_description">
                                            <div class="row flex-row sm-flex-disable">
                                                <div class="col-sm-12 col-md-6 col-md-push-6 border_left list_col">
                                                    <div class="info left_title_list">');
                                        $this->addListingDetailList();
                                    $this->addContent('</div><!--.left_title_list-->');
                            $this->addContent('</div><!--.col-->');
                            $this->addContent('<div class="col-sm-12 col-md-6 col-md-pull-6 remarks_col">
                                                <p>' . GI_StringUtils::nl2brHTML(GI_StringUtils::convertURLs($listing->getProperty('public_remarks'), true)) . '</p>');
                            $this->addContent('</div><!--.col-->');
                        $this->addContent('</div><!--.row-->');

                        $this->addOpenHouseList();

                    $this->addContent('</div><!--.detail_description-->');
                $this->addContent('</div><!--.detail_content-->');
            $this->addContent('</div><!--.container-->');
        $this->addContent('</section>');
    }
    
    protected function addListingDetailList() {
        $listing = $this->mlsListing;
        $this->addContent('<ul>');
        $propertyTypeTitle = $listing->getTypeTitle();
        if (!empty($propertyTypeTitle)) {
            $this->addContent('<li>
                                    <span class="label">Property Type</span>
                                    <span class="value">' . $propertyTypeTitle . '</span>
                                </li>');
        }
        $dwellingTypeTitle = $listing->getTagTypeTitle();
        if (!empty($dwellingTypeTitle)) {
            $this->addContent('<li>
                                    <span class="label">Type of Dwelling</span>
                                    <span class="value">' . $dwellingTypeTitle . '</span>
                                </li>');
        }
        $areaTitle = $listing->getAreaTitle();
        if (!empty($areaTitle)) {   
            $this->addContent('<li>
                                    <span class="label">Area</span>
                                    <span class="value">' . $areaTitle . '</span>
                                </li>');
        }             
        $subAreaTitle = $listing->getSubAreaTitle();        
        if (!empty($subAreaTitle)) {
            $this->addContent('<li>
                                    <span class="label">Sub Area</span>
                                    <span class="value">' . $subAreaTitle . '</span>
                                </li>');
        }
        if ($listing->getListingStatusRef() != 'sold') {
            //Details if listing is not sold
           if($listing->getProperty('exclusive') != 1 && !empty($this->checkValue($listing->getMLSNumber()))){
                $this->addContent('<li>
                                        <span class="label">MLS&reg; Number</span>
                                        <span class="value">' . $this->checkValue($listing->getMLSNumber()) . '</span>
                                    </li>');
            } 
        }
        $brokerage = $this->checkValue($listing->getFirmName());
        if (!empty($brokerage)) {
            $this->addContent('<li>
                                    <span class="label">Listing Brokerage</span>
                                    <span class="value">' . $brokerage . '</span>
                                </li>');
        }
        $propertySize = $this->checkValue($listing->getPropertySize());
        if (!empty($propertySize)) {
            $this->addContent('<li>
                                    <span class="label">Floor Space</span>
                                    <span class="value">' . $propertySize . ' sqft</span>
                                </li>');
        }
        $totalBedrooms = $this->checkValue($listing->getProperty('mls_listing_res.total_bedrooms'));
        if (!empty($totalBedrooms)) {
            $this->addContent('<li>
                                    <span class="label">Bedrooms</span>
                                    <span class="value">' . $totalBedrooms . '</span>
                                </li>');
        }
        $totalBaths = $this->checkValue($listing->getProperty('mls_listing_res.total_baths'));
        if (!empty($totalBaths)) {
            $this->addContent('<li>
                                    <span class="label">Bathrooms</span>
                                    <span class="value">' . $totalBaths . '</span>
                                </li>');
        }
        $year = $this->checkValue($listing->getProperty('year'));
        if (!empty($year)) {
            $this->addContent('<li>
                                    <span class="label">Year Built</span>
                                    <span class="value">' . $year . '</span>
                                </li>');
        }
        $lotSize = $this->checkValue($listing->getProperty('lot_size_sqft'));
        if (!empty($lotSize)) {
            $this->addContent('<li>
                                    <span class="label">Lot Size</span>
                                    <span class="value">' . $lotSize . ' sqft</span>
                                </li>');
        }
        
//        if (!empty($this->checkValue($listing->getProperty('postal_code')))) {
//            $this->addContent('<li>
//                                    <span class="label">Postal Code</span>
//                                    <span class="value postal_code">' . $this->checkValue($listing->getProperty('postal_code')) . '</span>
//                                </li>');
//        }
        
        $grossTaxes = $this->checkValue(number_format($listing->getProperty('gross_taxes')));
        if (!empty($grossTaxes)) {
            $this->addContent('<li>
                                    <span class="label">Tax Amount</span>
                                    <span class="value">$' . $grossTaxes . '</span>
                                </li>');
        }
        $taxYear = $this->checkValue($listing->getProperty('tax_year'));
        if (!empty($taxYear)) {
            $this->addContent('<li>
                                    <span class="label">Tax Year</span>
                                    <span class="value">' . $taxYear . '</span>
                                </li>');
        }
        $siteInfluences = $this->checkValue($listing->getProperty('site_influences'));
        if (!empty($siteInfluences)) {
            $this->addContent('<li>
                                    <span class="label">Site Influences</span>
                                    <span class="value">' . $siteInfluences . '</span>
                                </li>');
        }
        $features = $this->checkValue($listing->getProperty('features'));
        if (!empty($features)) {
            $this->addContent('<li>
                                    <span class="label">Features</span>
                                    <span class="value">' . $features . '</span>
                                </li>');
        }
        $this->addContent('</ul>');
    }
    
    protected function addOpenHouseList() {
        $listing = $this->mlsListing;
        /*open house info*/
        if($listing->getOpenHouses()){
            $oh_listings = $listing->getOpenHouses();
            if($oh_listings){
                $this->addContent('<div class="open_house_wrap">');
                    $this->addContent('<h4>Open Houses</h4>');
                    $this->addContent('<div class="open_house_list">');
                    foreach($oh_listings as $oh_listing){
                        $this->addContent(' <div class="open_house_info"><div class="clock"><img src="resources/css/images/clock_icon.svg" alt="clock icon"></div>
                                <div class="open_house_content uppercased">' . date('D M j', strtotime($oh_listing->getProperty('oh_start_date'))) . 
                                                                    '<br>' . date('g:i A', strtotime($oh_listing->getProperty('oh_start_time'))) . ' - ' . date('g:i A', strtotime($oh_listing->getProperty('oh_end_time'))) . '</div>
                                    </div>');
                    }
                    $this->addContent('</div>');
                $this->addContent('</div>');
            }
        }
    }
    protected function addMapSection() {
        $listing = $this->mlsListing;
        $this->addContent('<section class="map-section">
                                <div class="container">');
                $this->addContent('<div class="tab_wrap">');
                    $this->addContent('<div class="button_group">
                                            <a class="map_btn open">Map</a>
                                            <a class="street_btn">Street View</a>');
                        if(!empty($listing->getProperty('virtual_tour_url'))){
                            $this->addContent('<a class="video_btn" href="' . $listing->getProperty('virtual_tour_url') . '" style="margin-left: 3px;" target="_blank">Virtual Tour</a>');
                        }

                    $this->addContent('</div>');

                    $this->addContent('<div id="map_wrapper" data-type="'.$listing->getTypeRef().'" data-address="'.$listing->getAddress(false).'">
                                            <div id="map"></div>');
                    $this->addContent('</div>');
                $this->addContent('</div>');
            $this->addContent('</div>');
        $this->addContent('</section>');
    }
    
    protected function addMortgageCalculatorSection() {
        $this->addContent('<section class="mortgage_calculator_wrap">');
            $this->addContent('<div class="container">
                                        <a href="'.ProjectConfig::getProjectBase().'/mortgage_calculator_widget.html" class="footer_search_btn_link mortgage_calculator iframe_modal_btn" data-featherlight="iframe" data-featherlight-iframe-allowfullscreen="true" data-featherlight-iframe-width="1000" data-featherlight-iframe-height="600"><div class="footer_search_btn">
                                            <div class="col-xs-8">MORTGAGE<br> CALCULATOR</div>
                                            <img class="col-xs-3" src="resources/css/images/calculator_btn.png" alt="Caculator button">
                                        </div></a>
                                    </div>');
//        $this->addContent('<div class="mls_statement_wrap">
//                                <div class="container">
//                                    <div class="mls_statement justified">
//                                        <img src="resources/css/images/mls_logo_large.png" alt="MLS logo">
//                                        <p>This representation is based in whole or in part on data generated by the Chilliwack & District Real Estate Board, Fraser Valley Real Estate Board or Real Estate Board of Greater Vancouver which assumes no responsibility for its accuracy</p>
//                                    </div>
//                                </div>
//                            </div>');
        $this->addContent('</section>');
    }
}
