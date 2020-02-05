<?php

class StaticHomeView extends GI_View{

    public function __construct() {
        parent::__construct();
        $this->addJS('resources/js/youtube-player.js');
        $this->addSiteTitle('Homepage');
        $this->setDescription(SITE_TITLE.' - Seeking the Next Big Idea');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick.css');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick-theme.css');
        $this->addJS('resources/external/slick-1.6.0/slick/slick.js');
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addViewBody();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="view_wrap">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    public function addViewBody() {
        $this->addHeaderBannerSection();
        $this->addHomeProcessSection();
        $this->addCommunitySection();
        $this->addFeaturedListingSection();
        $this->addCharitySection();
    }
    
    public function addHeaderBannerSection() {
        $title = "When Buying or Selling You get to Choose a Charity that Beneﬁts";
        $this->addHTML('<section class="section section_type_banner banner banner_size_normal banner_page_home">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="banner__content-wrap text-center">');
                            $this->addHTML('<h1 class="banner__title">'.$title.'</h1>');
                        $this->addHTML('</div>');
                        $this->addHTML('<div class="col-xs-12 col-md-6 col-md-push-3">');
                            $this->addHTML('<div class="home-search">');
                                $this->addHTML('<div class="home-search__tabs">');
                                    $this->addHTML('<a href="" class="home-search__tab-item">ﬁnd a home</a> ');
                                    $this->addHTML('<a href="" class="home-search__tab-item">sell a home</a> ');
                                $this->addHTML('</div>'); // home-search
                                $this->addHTML('<div class="home-search__input-wrap">');
                                    $this->addHTML('<input type="text" class="home-search__input">');
                                $this->addHTML('</div>'); // home-search__input-wrap
                            $this->addHTML('</div>'); // home-search
                        $this->addHTML('</div>'); // banner__content-wrap
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }

    public function addHomeProcessSection(){
        $this->addHTML('<section class="section section_type_home-process home-process">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 text-center">');
                        $this->addHTML('<h3 class="section__title section__title_theme_overline section__title_size_small">The Civil Process</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<div class="row home-process__item-wrap">');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="home-process__item">');
                                    $this->addHTML('<h3 class="home-process__item-title">Step 1</h3>');
                                    $this->addHTML('<img src="resources/media/img/icon/glass-big.png" alt="'.SITE_TITLE.'" class="home-process__item-image"> ');
                                    $this->addHTML('<h4 class="home-process__item-subtitle">Find a Home or List Yours For Sale</h4>');
                                    $this->addHTML('<p class="home-process__item-description">Browse our easy-to-navigate platform listings. Or request a consultation to list your home for sale.</p>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="home-process__item">');
                                    $this->addHTML('<h3 class="home-process__item-title">Step 2</h3>');
                                    $this->addHTML('<img src="resources/media/img/icon/people-big.png" alt="'.SITE_TITLE.'" class="home-process__item-image"> ');
                                    $this->addHTML('<h4 class="home-process__item-subtitle">Connect with Top Local Realtors</h4>');
                                    $this->addHTML('<p class="home-process__item-description">Top MLS Realtors will connect with you and provide industry-leading realty services.</p>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="home-process__item">');
                                    $this->addHTML('<h3 class="home-process__item-title">Step 3</h3>');
                                    $this->addHTML('<img src="resources/media/img/icon/heart-big.png" alt="'.SITE_TITLE.'" class="home-process__item-image"> ');
                                    $this->addHTML('<h4 class="home-process__item-subtitle">Raise Money for Your Charity</h4>');
                                    $this->addHTML('<p class="home-process__item-description">Once a home is purchased or sold, choose a charity to which Civil will make a donation in your name!</p>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="button-wrap text-center">');
                            $this->addHTML('<a href="" class="button button_theme_primary">Start Now</a>');
                            $this->addHTML('<a href="" class="button button_theme_outline">Contact Civil</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
        
    }

    public function addCommunitySection(){
        $this->addHTML('<section class="section section_type_home-process banner banner_size_normal banner_page_community">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<h3 class="banner__title">Caring & Community</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-8 col-md-push-2">');
                        $this->addHTML('<div class="community__item-wrap">');
                            $this->addHTML('<div class="community__item community__item_bg_thirdary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/glass.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">1. Find a Home or List Yours For Sale</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="community__item community__item_bg_primary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/people.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">2. Connect with Top Local Realtors</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="community__item community__item_bg_secondary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/heart.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">3. Raise Money for Your Charity</p>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="button-wrap text-center">');
                            $this->addHTML('<a href="" class="button button_theme_primary">Start Now</a>');
                            $this->addHTML('<a href="" class="button button_theme_outline_white">Contact Civil</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');        
    }

    public function addFeaturedListingSection(){
        $this->addHTML('<section class="section section_type_home-listing home-listing">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 text-center">');
                        $this->addHTML('<h3 class="section__title section__title_theme_overline section__title_size_small">featured listings</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<br>');
                    $this->addHTML('<br>');
                    $this->addHTML('<br>');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<div class="row">');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="listing">');
                                    $this->addHTML('<div class="listing__image-wrap">');
                                        $this->addHTML('<img src="resources/media/img/house-1.jpg" alt="Listing" class="listing__image">');
                                        $this->addHTML('<span class="listing__price"><em>$</em> 4,324,119</span>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing__content">');
                                        $this->addHTML('<h3 class="listing__title">47 Newton Crescent, Port Coquitlam</h3>');
                                        $this->addHTML('<p class="listing__description">Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat eum iriure dolor hendrerit.</p>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing_button-wrap">');
                                        $this->addHTML('<a href="" class="button button_theme_primary">View Complete Listing</a>');
                                    $this->addHTML('</div>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="listing">');
                                    $this->addHTML('<div class="listing__image-wrap">');
                                        $this->addHTML('<img src="resources/media/img/house-2.jpg" alt="Listing" class="listing__image">');
                                        $this->addHTML('<span class="listing__price"><em>$</em> 4,324,119</span>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing__content">');
                                        $this->addHTML('<h3 class="listing__title">47 Newton Crescent, Port Coquitlam</h3>');
                                        $this->addHTML('<p class="listing__description">Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat eum iriure dolor hendrerit.</p>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing_button-wrap">');
                                        $this->addHTML('<a href="" class="button button_theme_primary">View Complete Listing</a>');
                                    $this->addHTML('</div>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-md-4">');
                                $this->addHTML('<div class="listing">');
                                    $this->addHTML('<div class="listing__image-wrap">');
                                        $this->addHTML('<img src="resources/media/img/house-3.jpg" alt="Listing" class="listing__image">');
                                        $this->addHTML('<span class="listing__price"><em>$</em> 4,324,119</span>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing__content">');
                                        $this->addHTML('<h3 class="listing__title">47 Newton Crescent, Port Coquitlam</h3>');
                                        $this->addHTML('<p class="listing__description">Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat eum iriure dolor hendrerit.</p>');
                                    $this->addHTML('</div>');
                                    $this->addHTML('<div class="listing_button-wrap">');
                                        $this->addHTML('<a href="" class="button button_theme_primary">View Complete Listing</a>');
                                    $this->addHTML('</div>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    $this->addHTML('</section>');        
    }

    public function addCharitySection(){
        $this->addHTML('<div class="section section_type_charity charity">');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__banner">');
                            $this->addHTML('<img src="resources/media/img/banner/footer-small.jpg" alt="'.SITE_TITLE.'" class="charity__banner-img">');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__content">');
                            $this->addHTML('<div class="charity__steps">');
                                $this->addHTML('<img src="resources/media/img/icon/process.png" class="charity__steps-image" alt="'.SITE_TITLE.'">');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="charity__content-wrap">');
                                $this->addHTML('<h3 class="charity__title"><b>STEP 1</b> Select your Charity</h3>');
                                $this->addHTML('<p class="charity__description">Choose the charity you would like funds from your real estate transaction to be directed to</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="charity__content-wrap charity__content-wrap_bg_secondary">');
                                $this->addHTML('<div class="charity__input-wrap">');
                                    $this->addHTML('<label class="charity__input-label" for="">Choose your Charity</label>');
                                    $this->addHTML('<input type="text" class="charity__input" placeholder="Start Typing the Name of your Charity">');
                                $this->addHTML('</div>');
                                $this->addHTML('<a href="" class="charity__form-link">see all charities »</a>');
                                $this->addHTML('<span class="charity__checkbox-wrap">');
                                    $this->addHTML('<input type="checkbox" class="charity__checkbox-input">');
                                    $this->addHTML('<label for="" class="charity__checkbox-label">pick later</label>');
                                $this->addHTML('</span>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="charity__content-wrap">');
                                $this->addHTML('<p class="charity__description">Some of Civil’s featured Charities</p>');
                                $this->addHTML('<img src="resources/media/img/logo/logo_bar.png" alt="'.SITE_TITLE.'" class="charity__logos"> ');
                                $this->addHTML('<a href="" class="button button_theme_primary button_has-icon">Go To Next Step <span class="button__icon"></span></a>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
