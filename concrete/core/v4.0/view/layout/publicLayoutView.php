<?php

class PublicLayoutView extends AbstractPublicLayoutView {
    
    public static $socialItems = [
        ['type' => 'facebook', 'icon' => 'facebook', 'link' => 'https://www.facebook.com'],
        ['type' => 'twitter', 'icon' => 'twitter', 'link' => 'https://www.twitter.com'],
        ['type' => 'pinterest', 'icon' => 'pinterest', 'link' => 'https://www.pinterest.com'],
        ['type' => 'linkedin', 'icon' => 'linkedin', 'link' => 'https://www.linkedin.com'],
    ];
    
    protected function addDefaultCSS() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900&display=swap');
        parent::addDefaultCSS();
    }
    
    protected function addDefaultJS() {
        parent::addDefaultJS();
        $this->addJS('resources/js/custom.js');
        // $this->addJS('resources/js/custom_google_map.js');
        $this->addJS('resources/js/civil.js');
    }
    
    protected function buildMainNav() {
        $linkClassArr = array('linkClass' => "nav__li");

        // main menu
        $menuItems = [
            [
                'ref' => 'main',
                'label' => 'buy',
                'link' => GI_URLUtils::buildURL(array(
                     'controller' => 'static',
                     'action' => 'buy',
                 )),
            ],
            [
                'ref' => 'main',
                'label' => 'sell',
                'link' => GI_URLUtils::buildURL(array(
                     'controller' => 'static',
                     'action' => 'sell',
                 )),
            ],
            [
                'ref' => 'main',
                'label' => 'listings',
                'link' => GI_URLUtils::buildURL(array(
                     'controller' => 'relisting',
                     'action' => 'index'
                 )),
            ],
            [
                'ref' => 'main',
                'label' => 'open houses',
                'link' => GI_URLUtils::buildURL(array(
                     'controller' => 'relisting',
                     'action' => 'openHouse',
                 ), false, true),
            ],
            [
                'ref' => 'main',
                'label' => 'about us',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'about-us',
                 )),
            ],
            [
                'ref' => 'main',
                'label' => 'referrals',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'referrals',
                )),
            ],
            [
                'ref' => 'main',
                'label' => 'contact us',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'contact',
                )),
            ],
        ];

        foreach ($menuItems as $menuItem) {
            if (isset($menuItem['sub'])) {
                $sub = $menuItem['sub'];
                $this->menuView->addSubMenu($menuItem['ref'], $menuItem['sub'], $menuItem['label'], '', $linkClassArr);
                if (isset($menuItem['subItems'])) {
                    $subItems = $menuItem['subItems'];
                    foreach ($subItems as $subItem) {
                        $this->menuView->addMenuItem($sub, $subItem['label'], $subItem['link'], $linkClassArr);
                    }
                }
            } else {
                $this->menuView->addMenuItem($menuItem['ref'], $menuItem['label'], $menuItem['link'], $linkClassArr);
            }
        }
        return $this;
    }

    public function openHeaderContainer(){
        $this->addHTML('<div class="section section_type_header">');
        $this->addHTML('<div class="container">');
        $this->addHTML('<div class="row">');
        return $this;
    }

    public function closeHeaderContainer(){
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    public function openMenuContainer(){
        $this->addHTML('<div class="section section_type_menu">');
        $this->addHTML('<div class="container">');
        $this->addHTML('<div class="row">');
        return $this;
    }

    public function closeMenuContainer(){
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }
    
    public function display() {
        $mainContentHtml = $this->getMainContent();
        $this->addHeader()
            ->openPageDiv()
                ->openHeaderWrapDiv()
                    ->openHeaderDiv()
                        ->openHeaderContainer()
                            ->addOpenLogoWrap()
                                ->addLogo()
                            ->addCloseLogoWrap()
                        ->closeHeaderContainer()
                        ->openMenuContainer()
                            ->addMenuBtn()
                            ->addMenu()
                        ->closeMenuContainer()
                    ->closeHeaderDiv()
                ->closeHeaderWrapDiv()
                ->openContentWrapDiv()
                    //Main Content
                    ->openContentDiv()
                        ->addHTML($mainContentHtml)
                    ->closeContentDiv()
                ->closeContentWrapDiv()
                ->openFooterTag()
                ->addFooterContent()
                ->closeFooterTag()
            ->closePageDiv()   
            ->addFooter();
        echo $this->html;
    }
    
    protected function openMenuDiv($class = ''){
        $class ='nav';
        $this->addHTML('<div class="col-xs-12">');
        $this->addHTML('<div class="' . $class . '">');
        $this->addHTML('<nav>');
        return $this;
    }
    
    protected function closeMenuDiv(){
        $this->addHTML('</nav>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }

    protected function addOpenLogoWrap(){
        $this->addHTML('<div class="col-xs-12 logo-bar">');
        return $this;
    }
    
    protected function addLogo($fileName = 'header_logo.png', $path="resources/media/img/logo/"){
        parent::addLogo($fileName, $path);
        return $this;
    }

    protected function addCloseLogoWrap(){
        $this->addOtherWidget();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openHeaderWrapDiv($class = ''){
        parent::openHeaderWrapDiv('dark_bg '.$class);
        return $this;
    }
    
    protected function openFooterTag($class = ''){
        $this->addHTML('<footer class="'.$class.'">');
        return $this;
    }
    
    protected function addFooterContent() {
        $this->addHTML('<section id="footer_links" class="section footer">');
            $this->addHTML('<div class="container">');
            // row
            $currentYear = date('Y');
            $this->addHTML('<div class="row">');
                $this->addHTML('<div class="col-xs-12 col-sm-5 col-lg-6 text-center">');
                self::addLogo();
                $this->addHTML('</div><!--.col-->');
                $this->addHTML('<div class="col-xs-12 col-sm-7 col-lg-6">');
                    $this->addHTML('<p class="footer__bottom-text">© Copyright '. SITE_TITLE . ' 2004 - '.$currentYear.'. All Rights Reserved.</p>');
                $this->addHTML('</div><!--.col-->');
            $this->addHTML('</div><!--.row-->');
            // end row
            $this->addHTML('</div><!--.container-->');
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addFooterTestimonialsBlock() {
        $opportunitiesURL = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'opportunities',
        ));
        $registerURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'signup',
        ));
        $title = "We provide accredited investor members to access exclusive investment opportunities.";
        $this->addHTML('<section id="footer_testimonials" class="section section_type_testimonial testimonial section_bg_dark">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-md-8 col-lg-6">');
                        $this->addHTML('<p class="testimonial__text">'.$title.'</p>');
                    $this->addHTML('</div><!--.col-->');
                    
                    $this->addHTML('<div class="col-md-4 col-lg-6">');
                        $this->addHTML('<div class="testimonial__buttons">');
                            $this->addHTML('<a href="'.$opportunitiesURL.'" class="button button_bg_hot button_has-icon button_theme_primary" id="bottom_learnmore_btn">SEE OPPORTUNITIES <span class="button__icon button__icon_color_dark"></span></a>');
                            $this->addHTML('<a href="'.$registerURL.'" class="button button_theme_white" id="bottom_register_btn">REGISTER</a>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div><!--.col-->');
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }
    
    public static function getSocialMediaLinkHTML() {
        // TODO: leave social empty temporarily
        return '';
        
        $html = '<div class="social-list">';
        foreach (static::$socialItems as $socialItem) {
            $html .= '<a href="'.$socialItem['link'].'" class="social-list__link social-list__link_type_'.$socialItem['type'].'" target="_blank"><span class="social-list__icon '.$socialItem['icon'].'"></span></a>';
        }
        $html .= '</div>';
        return $html;
    }
    
    protected function addMenuBtn(){
        $this->addHTML('<div id="menu_btn" title="Open Navigation Menu"><span class="top"></span><span class="middle"></span><span class="bottom"></span></div>');
        return $this;
    }

    public static function getBannerSectionContainerOpen($isFullSize = false, $pageName ="", $id = "", $isFluidContainer = false){
        $idAttr = "";
        if(!empty($id)){
            $idAttr = $id;
        }

        $classes = "section_type_banner banner";
        if($isFullSize){
            $classes .= " banner_size_full-width";
        }else{
            $classes .= " banner_size_normal";
        }

        if(!empty($pageName)){
            $classes .= ' banner_page_'.$pageName;
        }

        $containerClass = "container";
        if($isFluidContainer){
            $containerClass = 'container-fluid';
        }

        $html = '';
        $html.='<section id="'.$idAttr.'" class="'.$classes.'">';
        $html.='<div class="'.$containerClass.'">';
        return $html;
    }
    public static function getBannerSectionContainerClose(){
        $html = '';
        $html.='</div>';
        $html.='</section>';
        return $html;
    }

    public static function getEmailAddress(){
        // hide email address from bots
        $emailAddress = SITE_EMAIL;
        $mailToAddress = str_replace('.', '@@', $emailAddress);
        $mailTextAddress = str_replace('.', '<span class="hide"></span>.', $emailAddress);
        return [
            'anchorAttrs' => '
                href="mailto:'.$mailToAddress.'"
                onmouseover="this.href=this.href.replace(\'@@\',\'.\')"
                onclick="this.href=this.href.replace(\'@@\',\'.\')"
            ',
            'mailTo' => $mailToAddress,
            'mailText' => $mailTextAddress
        ];
    }
    public static function getEmailLink(){
        $emailItem = self::getEmailAddress();
        return '
            <a
                href="mailto:'.$emailItem['mailTo'].'"
                onmouseover="this.href=this.href.replace(\'@@\',\'.\')"
                onclick="this.href=this.href.replace(\'@@\',\'.\')"
            >'.$emailItem['mailText'].'
            </a>
        ';
    }

    public function addStickyMenu(){
        $mailToAddress = str_replace('.', '@@', SITE_EMAIL);
        $items = [
            [
                'icon' => 'icon_telephone',
                'title' => 'CALL',
                'ref' => 'tel:'.SITE_PHONE,
                'classes' => '',
                'attribute' => ''
            ],
            [
                'icon' => 'icon_email',
                'title' => 'EMAIL',
                'ref' => 'mailto:'.$mailToAddress.'"
                onmouseover="this.href=this.href.replace(\'@@\',\'.\')"
                onclick="this.href=this.href.replace(\'@@\',\'.\')',
                'classes' => '',
                'attribute' => ''
            ],
            [
                'icon' => 'icon_location',
                'title' => 'MAP',
                'ref' => '',
                'classes' => '',
                'attribute' => 'data-toggle="modal" data-target="#location_modal"'
            ],
            [
                'icon' => 'icon_office_hours',
                'title' => 'HOURS',
                'ref' => '',
                'classes' => '',
                'attribute' => 'data-toggle="modal" data-target="#office_hour_modal"'
            ],
        ];
    }

    protected function addMapModal(){
        $firstAddress = SITE_ADDR_STREET. ', ' .SITE_ADDR_CITY. ', ' .SITE_ADDR_REGION.' '.SITE_ADDR_CODE.', '.SITE_ADDR_COUNTRY;
        $firstName =  SITE_ADDR_STREET. ', ' .SITE_ADDR_CITY;

        $contentHtml = '<div class="google_map"><div 
            id="google_map" class="org_map"
            data-title="'.$firstName.'"
            data-addr="'.$firstAddress.'"
        ></div></div>';
        $this->addModal('location_modal', 'modal_type_location', 'Location', $contentHtml);
    }

    protected function addOfficeHoursModal(){
        $contentHtml = '<div class="office-hour">
            <p><b>Monday</b> 8:00am to 4:30pm</p>
            <p><b>Tuesday</b> 8:00am to 4:30pm</p>
            <p><b>Wednesday</b> 8:00am to 4:30pm</p>
            <p><b>Thursday</b> 8:00am to 4:30pm</p>
            <p><b>Friday</b> 8:00am to 4:30pm</p>
            <p><b>Saturday</b> Closed</p>
            <p><b>Sunday</b> Closed</p>
        </div>';
        $this->addModal('office_hour_modal', 'modal_type_hours', 'Office hours', $contentHtml);
    }

    protected function addModal($id, $classes, $title, $content){
        $this->addHTML('<div id="'.$id.'" class="modal fade '.$classes.'" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">'.$title.'</h4>
                    </div>
                    <div class="modal-body">'.$content.'</div>
                </div>
            </div>
        </div>');
    }

    protected $headerWidgetConfig = array(
        'relisting' => array(
            'index' => ['search_bar', 'user'],
            'openHouse' => ['search_bar', 'user'],
        ),
        'mls' => array(
            'view' => ['call', 'search_icon', 'user']
        ),
    );
    protected function addOtherWidget(){
        $controller = GI_URLUtils::getController();
        $action = GI_URLUtils::getAction();

        if(!isset($this->headerWidgetConfig[$controller])){
            return;
        }

        if(!isset($this->headerWidgetConfig[$controller][$action])){
            return;
        }
        $this->addHTML('<div class="header-widget">');
        $widgets = $this->headerWidgetConfig[$controller][$action];
        foreach($widgets as $widget){
            $this->addHeaderWidget($widget);
        }
        $this->addHTML('</div>');
    }
    protected function addHeaderWidget($widget){
        switch($widget){
            case 'search_bar':
                $searchForm = new GI_Form('search_bar');
                $this->addHTML('<div class="header-widget__item header-widget__item_type_search-bar">');
                    $searchForm->addHTML('<div class="header-widget__search-bar-wrap">');
                        $searchForm->addField('keyword', 'text', array(
                            'class' => 'form__input form__input_type_text',
                            'placeHolder' => 'Port Moody, British Columbia',
                            'displayName' => ''
                        ));
                        $searchForm->addHTML('<a href="" class="submit_btn button button_theme_primary">Search</a>');
                    $searchForm->addHTML('</div>');
                    $this->addHTML($searchForm->getForm());
                $this->addHTML('</div>');
                break;

            case 'search_icon':
                $this->addHTML('<div class="header-widget__item header-widget__item_type_search-icon">');
                    $relistingIndexURL = GI_URLUtils::buildCleanURL(array(
                        'controller' => 'relisting',
                        'action' => 'index'
                    ));
                    $this->addHTML('<div class="header-widget__search-link-wrap">');
                        $this->addHTML('<a href="'.$relistingIndexURL.'" class="header-widget__search-link"><i class="header-widget__search-icon"></i> Search</a>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                break;

            case 'call':
                $this->addHTML('<div class="header-widget__item header-widget__item_type_call">');
                    $this->addHTML('<div class="header-widget__call-link-wrap">');
                        $this->addHTML('<a href="'.SITE_TITLE.'" class="header-widget__call-link">Call Us '.SITE_PHONE.'</a>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
                break;

            case 'user':
                $this->addHTML('<div class="header-widget__item header-widget__item_type_user">');
                    if(!Login::isLoggedIn()){
                        $this->addHTML('<span class="header-widget__login-link-wrap">');
                            $loginURL = GI_URLUtils::buildCleanURL(array(
                                'controller' => 'user',
                                'action' => 'login'
                            ));
                            $this->addHTML('<a href="'.$loginURL.'" class="header-widget__login-link">Log In</a>');
                        $this->addHTML('</span>');
                    }else{
                        $this->addHTML('<span class="header-widget__user-wrap">');
                        $img = '<img src="resources/media/img/art/avatar.png" alt="'.SITE_TITLE.'" class="header-widget__user-avatar-img">';
                        $user = Login::getUser();
                        $avtView = $user->getUserAvatarView();
                        if(!empty($avtView)){
                            $avtView->setSize(50, 50);
                            $img = $avtView->getAvatarImg();
                        }
                        $this->addHTML('<span class="header-widget__user-avatar">');
                            $this->addHTML($img);
                        $this->addHTML('</span>');
                        $this->addHTML('<span class="header-widget__user-name">'.$user->getFullName().'<span class="header-widget__avatar-dropdown-icon"></span></span>');
                        $this->addHTML('</span>');
                    }
                $this->addHTML('</div>');
                break;

            default:
                break;
        }
    }
}
