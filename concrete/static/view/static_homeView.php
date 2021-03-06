<?php

class StaticHomeView extends GI_View{

    /**
     * @var CharityFormView
     */
    protected $charityForm;
    protected $form;

    public function __construct() {
        parent::__construct();
        $this->addJS('resources/js/youtube-player.js');
        $this->addSiteTitle('Homepage');
        $this->setDescription(SITE_TITLE.' - Seeking the Next Big Idea');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick.css');
        $this->addCSS('resources/external/slick-1.6.0/slick/slick-theme.css');
        $this->addJS('resources/external/slick-1.6.0/slick/slick.js');

        $isSent = false;
        $this->charityForm = new CharityFormView($this->form, $isSent);
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
                                    $homeSearchForm = new GI_Form('home_search_form');
                                    $homeSearchForm->addField('home_search', 'text', array(
                                        'class' => 'form__input form__input_type_text',
                                        'placeHolder' => 'Enter Subdivision, Address, City, Postal Code, School District or MLS® Number ...'
                                    ));
                                    $homeSearchForm->addHTML('<a class="button button_has-icon submit_btn"><span class="button__icon button__icon_type_search"></span></a>');
                                    $this->addHTML($homeSearchForm->getForm());
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
                            $this->addHTML('<div class="col-xs-12 col-lg-4">');
                                $this->addHTML('<div class="home-process__item home-process__item_first">');
                                    $this->addHTML('<h3 class="home-process__item-title">Step 1</h3>');
                                    $this->addHTML('<img src="resources/media/img/icon/glass-big.png" alt="'.SITE_TITLE.'" class="home-process__item-image"> ');
                                    $this->addHTML('<h4 class="home-process__item-subtitle">Find a Home or List Yours For Sale</h4>');
                                    $this->addHTML('<p class="home-process__item-description">Browse our easy-to-navigate platform listings. Or request a consultation to list your home for sale.</p>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-lg-4">');
                                $this->addHTML('<div class="home-process__item home-process__item_second">');
                                    $this->addHTML('<h3 class="home-process__item-title">Step 2</h3>');
                                    $this->addHTML('<img src="resources/media/img/icon/people-big.png" alt="'.SITE_TITLE.'" class="home-process__item-image"> ');
                                    $this->addHTML('<h4 class="home-process__item-subtitle">Connect with Top Local Realtors</h4>');
                                    $this->addHTML('<p class="home-process__item-description">Top MLS Realtors will connect with you and provide industry-leading realty services.</p>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="col-xs-12 col-lg-4">');
                                $this->addHTML('<div class="home-process__item home-process__item_third">');
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
                            $this->addHTML('<div class="community__item community__item_first community__item_bg_thirdary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/glass.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">Find a Home or List Yours For Sale</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="community__item community__item_second community__item_bg_primary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/people.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">Connect with Top Local Realtors</p>');
                            $this->addHTML('</div>');
                            $this->addHTML('<div class="community__item community__item_third community__item_bg_secondary">');
                                $this->addHTML('<div class="community__item-image-wrap">');
                                    $this->addHTML('<img class="community__item-image" src="resources/media/img/icon/heart.png" alt="'.SITE_TITLE.'">');
                                $this->addHTML('</div>');
                                $this->addHTML('<p class="community__item-description">Raise Money for Your Charity</p>');
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
                        $this->addHTML('<h3 class="section__title section__title_theme_overline section__title_size_small">Newest Listings</h3>');
                    $this->addHTML('</div>');
                    $this->addHTML('<br>');
                    $this->addHTML('<br>');
                    $this->addHTML('<br>');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<div class="flex_row row">');
                            $latestListings = MLSListingFactory::getLatestListing();
                            foreach($latestListings as $listing){
                                $this->addHTML('<div class="col-xs-12 col-md-4 listing__col">');
                                    $this->addHTML('<div class="listing">');
                                        $this->addHTML('<div class="listing__image-wrap">');
                                            $this->addHTML($listing->getCoverImageHTML());
                                            $this->addHTML('<span class="listing__price">'.$listing->getDisplayListPrice().'</span>');
                                        $this->addHTML('</div>');
                                        $this->addHTML('<div class="listing__content">');
                                            $this->addHTML('<h3 class="listing__title">'.$listing->getAddress().'</h3>');
                                            $this->addHTML('<p class="listing__description">'.$listing->getProperty('public_remarks').'</p>');
                                        $this->addHTML('</div>');
                                        $this->addHTML('<div class="listing__button-wrap">');
                                            $this->addHTML('<a href="'.$listing->getViewURL().'" class="button button_theme_primary">View Complete Listing</a>');
                                        $this->addHTML('</div>');
                                    $this->addHTML('</div>');
                                $this->addHTML('</div>');
                            }
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    $this->addHTML('</section>');        
    }

    public function addCharitySection(){
        $this->addHTML('<div class="section section_type_charity charity charity_type_home charity_step_1" data-step="1">');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__banner">');
                            $this->addHTML('<img src="resources/media/img/banner/footer-small.jpg" alt="'.SITE_TITLE.'" class="charity__banner-img">');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__content">');
                            $this->addHTML($this->charityForm->getStepSelector());
                            $this->addHTML($this->charityForm->getHTMLView());
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
