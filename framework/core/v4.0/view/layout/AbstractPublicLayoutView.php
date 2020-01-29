<?php
/**
 * Description of AbstractPublicLayoutView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
abstract class AbstractPublicLayoutView extends AbstractLayoutView {
    
    /** @var GI_MenuView */
    protected $menuView = NULL;
    /** @var User */
    protected $currentUser = NULL;
    /** @var AbstractFileAvatarView */
    protected $userAvatarView = NULL;
    
    protected $addGoogleMap = true;

    public function __construct($layoutArray) {
        parent::__construct($layoutArray);
        
        if(isset($layoutArray['menu']) && is_a($layoutArray['menu'], 'GI_MenuView')){
            $this->setMenuView($layoutArray['menu']);
        }

        if (isset($layoutArray['currentUser']) && is_a($layoutArray['currentUser'], 'AbstractUser')) {
            $this->setCurrentUser($layoutArray['currentUser']);
        }
        
        if (isset($layoutArray['userAvatar']) && is_a($layoutArray['userAvatar'], 'GI_View')) {
            $this->setUserAvatarView($layoutArray['userAvatar']);
        }
        
        $this->addBodyClass('public_layout');
    }
    
    protected function addLayoutCSS(){
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/adv_icons.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/public_layout.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/public_theme.css');
        $this->addCSS('resources/css/public_theme.css');
    }
    
    /**
     * @param GI_MenuView $breadCrumbMenuView
     * @return \AbstractMainLayoutView
     */
    public function setBreadCrumbMenuView(GI_MenuView $breadCrumbMenuView){
        $this->breadCrumbMenuView = $breadCrumbMenuView;
        return $this;
    }
    
    /**
     * @return GI_MenuView
     */
    public function getBreadCrumbMenuView(){
        return $this->breadCrumbMenuView;
    }
    
    /**
     * @param GI_MenuView $menuView
     * @return \AbstractMainLayoutView
     */
    public function setMenuView(GI_MenuView $menuView){
        $this->menuView = $menuView;
        return $this;
    }
    
    /**
     * @return GI_MenuView
     */
    public function getMenuView(){
        return $this->menuView;
    }
    
    /**
     * @param AbstractUser $currentUser
     * @return \AbstractMainLayoutView
     */
    public function setCurrentUser(AbstractUser $currentUser){
        $this->currentUser = $currentUser;
        return $this;
    }
    
    /**
     * @return User
     */
    public function getCurrentuser(){
        return $this->currentUser;
    }
    
    /**
     * @param GI_View $userAvatarView
     * @return \AbstractMainLayoutView
     */
    public function setUserAvatarView(GI_View $userAvatarView){
        $this->userAvatarView = $userAvatarView;
        return $this;
    }
    
    /**
     * @return GI_View
     */
    public function getUserAvatarView(){
        return $this->userAvatarView;
    }
    
    /**
     * @return GI_MenuView
     */
    public function setAddGoogleMap($addGoogleMap){
        $this->addGoogleMap = $addGoogleMap;
        return $this;
    }
    
    protected function addDefaultCSS() {
        parent::addDefaultCSS();
        if (dbConnection::isModuleInstalled('contact')) {
            $this->addCSS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/contacts.css');
        }
    }
    
    protected function addDefaultJS(){
        parent::addDefaultJS();
        
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/forms.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/core.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/public_layout.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_modal.js');
        
        /* Google Map */
        $googleApiKey = ProjectConfig::getGoogleAPIKey();
        if (!empty($googleApiKey) && $this->addGoogleMap) {
            $this->addJS('resources/js/custom_google_map.js');
            $this->addJS('https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey.'&callback=googleMapInit', false);
        }
    }
    
    protected function buildMainNav(){
        return $this;
    }
    
    protected function addMenuContent(){
        $this->buildMainNav();
        $menuView = $this->getMenuView();
        if ($menuView) {
            $this->addHTML($menuView->getHTMLView());
        }
        return $this;
    }
    
    protected function addLogoutBtn($addLoginIfNotLoggedIn = true){
        $userId = Login::getUserId();
        $iconWidth = '14px';
        $iconHeight = '14px';
        if(!empty($userId)){
            $logOutUrl = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'logout'
            ));
            $this->addHTML('<a href="' . $logOutUrl . '" title="Log Out" id="log_out">'.GI_StringUtils::getSVGIcon('logout', $iconWidth, $iconHeight).' Log Out</a>');
        } elseif($addLoginIfNotLoggedIn) {
            $logInUrl = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'index'
            ));
            $this->addHTML('<a href="' . $logInUrl . '" title="Log In" id="log_out">'.GI_StringUtils::getSVGIcon('login', $iconWidth, $iconHeight).' Log In</a>');
        }
        return $this;
    }
    
    protected function addUserBar(){
        $this->addHTML('<div id="user_bar">');
        $this->addAccountMenu();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addAccountMenu() {
        $this->addHTML('<div class="my_account_menu">');
        if (!empty($this->currentUser)) {
            $contactOrg = $this->currentUser->getContactOrg();
            if (!empty($contactOrg)) {
                $userLink = GI_URLUtils::buildURL(array(
                    'controller' => 'contactprofile',
                    'action' => 'view',
                    'id' => $contactOrg->getId(),
                    'tab' => 'my_settings'
                ));
            } else {
                $userLink = GI_URLUtils::buildURL(array(
                    'controller' => 'user',
                    'action' => 'view',
                    'id' => $this->currentUser->getId()
                ));
            }
            $this->addHTML('<a href="' . $userLink . '">');
            if ($this->userAvatarView) {
                $this->userAvatarView->setSize(24, 24);
                $this->addHTML($this->userAvatarView->getHTMLView());
            } else {
                $this->addHTML($this->currentUser->getUserAvatarHTML());
            }
            $this->addHTML('<span class="user_name">' . $this->currentUser->getFullName() . '</span>');
            $this->addHTML('</a>');

            if (Permission::verifyByRef('view_dashboard')) {
                $dashboardLink = GI_URLUtils::buildURL(array(
                    'controller' => 'dashboard',
                    'action' => 'index',
                ));
                $this->addHTML('<a href="' . $dashboardLink . '">Admin</a>');
            }
            
            $logoutURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'logout',
            ));
            
            $this->addHTML('<a href="' . $logoutURL . '">Log Out</a>');
        } else {
            $loginURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'index',
            ));
            $this->addHTML('<a href="' . $loginURL . '">Log In</a>');
            
            $registerURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'register',
            ));
            $this->addHTML('<a href="' . $registerURL . '">Sign Up</a>');
        }
        $this->addHTML('</div>');
    }
    
    public function display() {
        $mainContentHtml = $this->getMainContent();
        $this->addHeader()
            ->openPageDiv()
                ->openHeaderWrapDiv()
                    ->openHeaderDiv()
                        ->addMenuBtn()
                        ->addUserBar()
                        ->addLogo()
                        ->addMenu()
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
            ->addChatBar()
            ->addFooter();
        echo $this->html;
    }
    protected function openPageDiv($class = ''){
        $this->addHTML('<div id="page" class="'.$class.'">');
        return $this;
    }
    
    protected function closePageDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openHeaderWrapDiv($class = ''){
        $this->addHTML('<header id="header_wrap" class="'.$class.'">');
        return $this;
    }
    
    protected function closeHeaderWrapDiv(){
        $this->addHTML('</header>');
        return $this;
    }
    
    protected function openHeaderDiv($class = ''){
        $this->addHTML('<div id="header" class="'.$class.'">');
        return $this;
    }
    
    protected function closeHeaderDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openContentDiv($class = ''){
        $this->addHTML('<div id="content" class="'.$class.'">');
        return $this;
    }
    
    protected function closeContentDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openFooterTag($class = ''){
        $this->addHTML('<footer id="footer_wrap" class="'.$class.'">');
        return $this;
    }
    
    protected function closeFooterTag(){
        $this->addHTML('</footer>');
        return $this;
    }
    
    protected function addFooterContent() {
        $this->addFooterLinkBlock();
        $this->addFooterMapBlock();
        return $this;
    }
    
    protected function addFooterLinkBlock() {
        $this->addHTML('<section id="footer_links" class="container">');
            $this->addHTML('<div id="footer_logo_col">');
                $this->addHTML('<div id="footer_logo">');
                    $this->addFooterLogo();
                $this->addHTML('</div>');
            $this->addHTML('</div>');

            $this->addHTML('<div id="footer_contact_info_col" itemscope itemtype="http://schema.org/Organization">');
                $this->addHTML('<div class="contact_info_block">');
                    $this->addHTML('<h3 class="contact_info_title">Address</h3>
                                    <p itemprop="address">'.SITE_ADDR_STREET.'<br>
                                       '.SITE_ADDR_CITY.', '.SITE_ADDR_REGION.'<br>
                                       '.SITE_ADDR_COUNTRY.' '.SITE_ADDR_CODE.'</p>');
                $this->addHTML('</div>');

                $this->addHTML('<div class="contact_info_block">');
                    $this->addHTML('<h3 class="contact_info_title">Contact</h3>
                                        <p><a href="tel:'.str_replace(" ","", SITE_PHONE).'"  itemprop="telephone">' .SITE_PHONE. '</a><br>');

                                            $arrEmail = explode("@", SITE_EMAIL);
                                            $this->addHTML('<script type="text/javascript">
                                                            <!--
                                                                var string1 = "'.$arrEmail[0].'";
                                                                var string2 = "@";
                                                                var string3 = "'.$arrEmail[1].'";
                                                                var string4 = string1 + string2 + string3;
                                                                document.write("<a href=" + "mail" + "to:" + string1 + string2 + string3 + " itemprop=\'email\'>" + string4 + "</a>");
                                                            //-->
                                                </script></p>');

                $this->addHTML('</div>');
                $this->addHTML('<div class="contact_info_block" id="footet_socialmedia_block">');
                    $this->addHTML('<h3 class="contact_info_title">Join Us</h3>');
                    $this->addSocialMediaBlock();
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
        return $this;
    }
    
    protected function addFooterLogo($fileName = 'logo.svg', $path="resources/media/img/logos/"){
        $this->addHTML('<a href="." title="' . ProjectConfig::getSiteTitle() . '">');
            $logoPath = $path . $fileName;
            if (file_exists($logoPath)){
                $this->addHTML('<img src="'.$logoPath.'" alt="'.ProjectConfig::getSiteTitle().'" title="'.ProjectConfig::getSiteTitle().'">');
            } else {
                $this->addHTML('<span class="text_logo">'.ProjectConfig::getSiteTitle().'</span>');
            }
            $this->addHTML('</a>');
        return $this;
    }
    
    protected function addSocialMediaBlock() {
            $this->addHTML('<a href="" class="socialmedia_link facebook" target="_blank">');
                $this->addHTML(GI_StringUtils::getSVGIcon('facebook'));
            $this->addHTML('</a>');
            $this->addHTML('<a href="" class="socialmedia_link twitter" target="_blank">');
                $this->addHTML(GI_StringUtils::getSVGIcon('twitter'));
            $this->addHTML('</a>');
            $this->addHTML('<a href="" class="socialmedia_link linkedin" target="_blank">');
                $this->addHTML(GI_StringUtils::getSVGIcon('linkedin'));
            $this->addHTML('</a>');
            $this->addHTML('<a href="" class="socialmedia_link insta" target="_blank">');
                $this->addHTML(GI_StringUtils::getSVGIcon('insta'));
            $this->addHTML('</a>');
        return $this;
    }
    
    protected function addFooterMapBlock() {
        if ($this->addGoogleMap) {
            $this->addHTML('<section id="footer_map" class="google_map_wrap">');
                $this->addHTML('<div id="google_map" data-title="'.SITE_ADDR_STREET.' '.SITE_ADDR_CITY.', BC" data-addr="'.SITE_ADDR_STREET.' '.SITE_ADDR_CITY.','.SITE_ADDR_REGION.','.SITE_ADDR_COUNTRY.','.SITE_ADDR_CODE.'"></div>');
            $this->addHTML('</section>');
        }
        
        return $this;
    }
    
    
}
