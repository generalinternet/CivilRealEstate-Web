<?php

class PublicLayoutView extends AbstractPublicLayoutView {
    
    public static $socialItems = [
        ['type' => 'facebook', 'icon' => 'facebook', 'link' => 'https://www.facebook.com'],
        ['type' => 'twitter', 'icon' => 'twitter', 'link' => 'https://www.twitter.com'],
        ['type' => 'pinterest', 'icon' => 'pinterest', 'link' => 'https://www.pinterest.com'],
        ['type' => 'linkedin', 'icon' => 'linkedin', 'link' => 'https://www.linkedin.com'],
    ];
    
    protected function addDefaultCSS() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Merriweather:700,900|Montserrat:400,500,700,900&display=swap');
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
        // set up user menu
        $userMenu = [
            ['ref' => 'main',
            'label' => 'Sign Up',
            'sub' => 'my_menu',
            'subItems' => [
                    ['label' => 'Sign Up',
                    'link' => GI_URLUtils::buildURL(array(
                                'controller' => 'user',
                                'action' => 'signup',
                            )),
                    ],
                    ['label' => 'Log In',
                    'link' => GI_URLUtils::buildURL(array(
                                'controller' => 'login',
                                'action' => 'index',
                            )),
                    ],
                ]
            ],
        ];
        if(!empty($this->currentUser)) {
            $myAccountURL = GI_URLUtils::buildURL(array(
                'controller' => 'user',
                'action' => 'weAccountDetail',
            ));
            if(Permission::verifyByRef('super_admin')){
                $myAccountURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'catIndex',
                    'type' => 'client',
                ));
            }

            $userMenu = [
                ['ref' => 'main',
                'label' => 'My Menu',
                'sub' => 'my_menu',
                'subItems' => [
                        ['label' => 'My Account',
                        'link' => $myAccountURL,
                        ],
                        ['label' => 'Log Out',
                        'link' => GI_URLUtils::buildURL(array(
                                    'controller' => 'login',
                                    'action' => 'logout',
                                )),
                        ],
                    ]
                ],
            ];
            
        }

        // main menu
        $mainMenu = [
            [
                'ref' => 'main',
                'label' => 'Opportunities',
                'sub' => 'opportunities',
                'subItems' => [
                    [
                        'label' => 'Start',
                        'link' => GI_URLUtils::buildURL(array(
                            'controller' => 'static',
                            'action' => 'opportunities',
                            'type' => 'category',
                            'type_ref' => 'start'
                        )),
                    ],
                    [
                        'label' => 'Opportunities',
                        'link' => GI_URLUtils::buildURL(array(
                            'controller' => 'static',
                            'action' => 'opportunities',
                            'type' => 'category',
                            'type_ref' => 'opportunities'
                        )),
                    ],
                    [
                        'label' => 'Real Estate',
                        'link' => GI_URLUtils::buildURL(array(
                            'controller' => 'static',
                            'action' => 'opportunities',
                            'type' => 'category',
                            'type_ref' => 'realestate'
                        )),
                    ],
                    [
                        'label' => 'Kids',
                        'link' => GI_URLUtils::buildURL(array(
                            'controller' => 'static',
                            'action' => 'opportunities',
                            'type' => 'category',
                            'type_ref' => 'kids'
                        )),
                    ],
                ]
            ],
            // [
            //     'ref' => 'main',
            //     'label' => 'History',
            //     'link' => GI_URLUtils::buildURL(array(
            //         'controller' => 'static',
            //         'action' => 'history',
            //     )),
            // ],
            [
                'ref' => 'main',
                'label' => 'About',
                'link' => GI_URLUtils::buildURL(array(
                     'controller' => 'static',
                     'action' => 'about',
                 )),
            ],
            [
                'ref' => 'main',
                'label' => 'Contact',
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => 'contact',
                )),
            ],
        ];

        $menuItems = array_merge($userMenu, $mainMenu);
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
        $this->addHTML('<div class="col-xs-12">');
        return $this;
    }

    public function closeHeaderContainer(){
        $this->addHTML('</div>');
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
                            ->addLogo()
                            ->addMenuBtn()
                            ->addMenu()
                        ->closeHeaderContainer()
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
        $this->addHTML('<div class="' . $class . '">');
        $this->addHTML('<nav>');

        $contactPhone = SITE_PHONE;
        $this->addHTML('<a href="tel:'.$contactPhone.'" class="nav__contact-button button button_theme_primary">CALL '.$contactPhone.'</a>');

        return $this;
    }
    
    protected function closeMenuDiv(){
        $this->addHTML('</nav>');
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addLogo($fileName = 'logo-header.png', $path="resources/media/img/logos/"){
        parent::addLogo($fileName, $path);
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
        $this->addFooterTestimonialsBlock();
        $this->addFooterLinkBlock();
        $this->addStickyMenu();
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
    
    protected function addFooterLinkBlock() {
        $this->addHTML('<section id="footer_links" class="section footer">');
            $this->addHTML('<div class="container">');
                // row
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-lg-12">');
                        $this->addHTML('<div class="logo footer__logo">');
                            $this->addFooterLogo('logo-footer.png');
                        $this->addHTML('</div>');
                    $this->addHTML('</div><!--.col-->');
                $this->addHTML('</div><!--.row-->');
                // end row
                // row
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xl-12">');
                        $this->addHTML('<div class="nav footer__nav nav_type_footer">');
                        $this->addHTML($this->menuView->getHTMLView());
                        $this->addHTML('</div><!--.col-->');
                    $this->addHTML('</div><!--.col-->');
                $this->addHTML('</div><!--.row-->');
                // end row
            $this->addHTML('</div><!--.container-->');
        $this->addHTML('</section>');

        // footer bottom
        $this->addHTML('<div class="section section_bg_primary footer">');
            $this->addHTML('<div class="container">');
            // row
            $this->addHTML('<div class="row">');
                $this->addHTML('<div class="col-lg-12">');
                    $this->addHTML('<div class="footer__social-list">');
                        $this->addHTML(static::getSocialMediaLinkHTML());
                    $this->addHTML('</div>');
                $this->addHTML('</div><!--.col-->');
            $this->addHTML('</div><!--.row-->');
            // end row
            // row
            $currentYear = date('Y');
            $this->addHTML('<div class="row">');
                $this->addHTML('<div class="col-lg-12">');
                    $this->addHTML('<p class="footer__bottom-text">© '.$currentYear. SITE_TITLE . '. All Rights reserved.</p>');
                $this->addHTML('</div><!--.col-->');
            $this->addHTML('</div><!--.row-->');
            // end row
            $this->addHTML('</div><!--.container-->');
        $this->addHTML('</div>');
        return $this;
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

        $this->addHTML('<div class="sticky-menu">');
        foreach($items as $menuItem){
            $this->addHTML('<div class="sticky-menu__item '.$menuItem['classes'].'">');
                $this->addHTML('<a class="sticky-menu__item-link" href="'.$menuItem['ref'].'" '.$menuItem['attribute'].'>');
                    $this->addHTML('<span class="sticky-menu__item-icon"><img src="resources/media/img/icons/'.$menuItem['icon'].'.svg" alt="'.SITE_NAME.'"></span>');
                    $this->addHTML('<span class="sticky-menu__item-text">'.$menuItem['title'].'</span>');
                $this->addHTML('</a>');
            $this->addHTML('</div>');
        }
        $this->addHTML('</div>');

        $this->addMapModal();
        $this->addOfficeHoursModal();
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

}
