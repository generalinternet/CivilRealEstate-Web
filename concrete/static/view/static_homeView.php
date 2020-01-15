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
        $this->addHomeSliderSection();
        $this->addVideoSection();
        $this->addServiceSection();
    }
    
    public function addHeaderBannerSection() {
        $title = "Seeking the <br>Next Big Idea";
        $description = SITE_NAME." is where new business happens in Western Canada. Connecting seasoned investors with visionary entrepreneurs, '.SITE_NAME.' provides access to early stage opportunities.";
        $aboutUsURL = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'about',
        ));
        $signUpURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'signup',
        ));
        $this->addHTML('<section id="header-banner-section" class="section section_type_banner banner banner_size_full-width banner_page_home">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        // $this->addHTML('<video loop muted autoplay preload="auto" poster="resources/media/video/bg_hero_placeholder.jpg" playsinline class="bg-video"><source src="resources/media/video/Slider_video.mp4" type="video/mp4"></video>');
                        $this->addHTML('<div class="banner__content-wrap">');
                            $this->addHTML('<h1 class="banner__title">'.$title.'</h1>');
                            $this->addHTML('<p class="banner__description">'.$description.'</p>');
                            $this->addHTML('<div class="banner__buttons">');
                                $this->addHTML('<a href="'.$aboutUsURL.'" class="button button_theme_primary button_has-icon">LEARN MORE <span class="button__icon button__icon_color_dark"></span></a>');
                                $this->addHTML('<a href="'.$signUpURL.'" class="button button_theme_white">REGISTER</a>');
                            $this->addHTML('</div>'); // banner__buttons
                        $this->addHTML('</div>'); // banner__content-wrap
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }
    
    public function addUnderHeaderBannerSection() {
        $this->addHTML('<section id="under-header-banner-section" class="no-bg-section">');
        $this->addHTML('</section>');
    }
    
    public function addServiceSection() {
        $homeItemList = [
            [
                'ref' => 'start',
                'title' => 'Start',
                'content' => 'startups seeking capital and creating a stronger Western Canadian economy',
                'link' => array(
                    'controller' => 'static',
                    'action' => 'opportunities',
                    'type' => 'category',
                    'ref' => 'start'
                )
            ],
            [
                'ref' => 'opportunities',
                'title' => 'Opportunities',
                'content' => 'private placements, convertible notes and direct investment opportunities',
                'link' => array(
                    'controller' => 'static',
                    'action' => 'opportunities',
                    'type' => 'category',
                    'ref' => 'opportunities'
                )
            ],
            [
                'ref' => 'realestate',
                'title' => 'Real Estate',
                'content' => 'commercial, residential, and diverse real estate investment opportunities',
                'link' => array(
                    'controller' => 'static',
                    'action' => 'opportunities',
                    'type' => 'category',
                    'ref' => 'realestate'
                )
            ],
            [
                'ref' => 'kids',
                'title' => 'Kids',
                'content' => 'youth-focused startup mentoring program',
                'link' => array(
                    'controller' => 'static',
                    'action' => 'opportunities',
                    'type' => 'category',
                    'ref' => 'kids'
                )
            ],
        ];
        
        $this->addHTML('<section class="section section_type_service">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<h2 class="section__title">Our Services</h2>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                $this->addHTML('<div class="row flex_row">');
                    foreach($homeItemList as $key => $item){
                        $opporLink = GI_URLUtils::buildCleanURL($item['link']);
                        $this->addHTML("<div class='col-xs-12 col-sm-6 col-md-3'>");
                            $this->addHTML('<div class="service">');
                                $this->addHTML("<img src='resources/media/img/icons/service_icon_{$item['ref']}.png' alt='{$item['title']}' class='service__icon-image'>");
                                $this->addHTML("<h4 class='service__title service__title_color_dark'>'.SITE_NAME.'</h4>");
                                $this->addHTML("<h4 class='service__title service__title_color_white'>{$item['title']}</h4>");
                                $this->addHTML("<p class='service__description'>{$item['content']}</p>");
                                $this->addHTML("<a href='{$opporLink}' class='button button_theme_no-border button_has-icon service__button'></span>LEARN MORE<span class='button__icon button__icon_color_dark'></a>");
                            $this->addHTML('</div><!--.service-->');
                        $this->addHTML("</div>");
                    }
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }
    
    public function addVideoSection() {
        $signUpURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'signup',
        ));
        $title = 'What is '.SITE_NAME.'?';
        $description = SITE_NAME.' is a Western Canadian Investor Network that connects accredited investors with exclusive investment opportunities.  Categories include Start-Up Angel Investments, Real Estate, Kids Programs and other opportunities.';

        $this->addHTML('<section class="section section_bg_grey section_type_video">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');

                    $this->addHTML('<div class="col-xs-12"><h2 class="section__title">'.$title.'</h2></div>');

                    $this->addHTML('<div class="col-md-6">');
                        $this->addHTML('<div class="content-container">');
                            $this->addHTML('<p class="section__description">'.$description.'</p>');
                            $this->addHTML('<div class="section__buttons">');
                                $this->addHTML('<a href="'.$signUpURL.'" class="button button_theme_dark button_has-icon" id="video_register_btn">JOIN NOW<span class="button__icon button__icon_color_primary"></span></a>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div><!--.content-container-->');
                    $this->addHTML('</div><!--.col-->');

                    $this->addHTML('<div class="col-md-6">');
                        $this->addHTML('<div class="video-container section__embeded-video">');
                            $this->addHTML('<div class="embed-responsive embed-responsive-16by9">');
                                $this->addHTML('<div class="youtube-player" data-id="Qhi6YE5SGqk" data-related="0" data-control="1" data-info="1" data-fullscreen="1">');
                                    $this->addHTML('<div class="place_holder">');
                                    $this->addHTML('<img src="resources/media/video/youtube_placeholder_corporate.jpg" alt="Corporate Video Placeholder" class="placeholder-bg"><div class="video-btn"><img src="resources/media/img/icons/icon_play_red.svg" alt="YouTube Play Icon" title="Play"></div>');
                                    $this->addHTML('</div></div>');
                                $this->addHTML('</div>');
                            $this->addHTML('</div>');
                        $this->addHTML('</div><!--.video-container-->');
                    $this->addHTML('</div><!--.col-->');

                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }

    public function addHomeSliderSection(){
        $opportunitiesURL = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'opportunities',
        ));
        $title = "Current Opportunities";
        $html = '
            <section class="section section_type_home-slider" id="home_slider">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="section__title">
                                '.$title.'
                                <span class="section__slider-buttons">
                                    <span id="home_slider_wrap_prev" class="button button_theme_no-border"><span class="button__icon button__icon_type-prev-primary"></span>PREV</span>
                                    <span id="home_slider_wrap_next" class="button button_theme_no-border">NEXT<span class="button__icon button__icon_type-next-primary"></span></span>
                                </span>
                            </h2>
                            <div class="opportunity-slider" id="home_slider_wrap">'.$this->getSliderItemHTML().'</div>
                            <div class="section__button-wrap">
                                <a href="'.$opportunitiesURL.'" class="button button_has-icon button_theme_no-border">SEE MORE OPPORTUNITIES <span class="button__icon button__icon_color_primary"></span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        ';
        $this->addHTML($html);
        return $this;
    }
    private function getSliderItemHTML(){
        // $typeRef = 'investment';
        // $general = true;
        // $investStatusArray = array();
        // $idsAsKey = false;
        // $isFeatured = true;
        // $itemList = ContentFactory::getInvestmentContents($typeRef, $general, $investStatusArray, $idsAsKey, $isFeatured);

        // $html = '';
        // foreach($itemList as $investmentItem){
        //     $sliderItemView = $investmentItem->getSliderItemDetailView();
        //     $html .= '<div class="investment__wrap">';
        //     $html .= $sliderItemView->getHTMLView();
        //     $html .= '</div>';
        // }

        // return $html;
        return null;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
