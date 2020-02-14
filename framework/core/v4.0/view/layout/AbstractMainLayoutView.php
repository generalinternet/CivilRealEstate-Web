<?php
/**
 * Description of AbstractMainLayoutView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.2
 */
abstract class AbstractMainLayoutView extends AbstractLayoutView {
    
    /** @var GI_MenuView */
    protected $menuView = NULL;
    /** @var GI_MenuView */
    protected $breadCrumbMenuView = NULL;
    /** @var User */
    protected $currentUser = NULL;
    /** @var GI_View */
    protected $userAvatarView = NULL;
    
    /** @var GI_View */
    protected $listBarContent = NULL;
    protected $listBarUrl = NULL;
    protected $listBarClass = '';
    
    protected $targetId = 'main_window';
    protected $notificationCountPos = 'avatar';

    public function __construct($layoutArray) {
        parent::__construct($layoutArray);
        
        if(isset($layoutArray['menu']) && is_a($layoutArray['menu'], 'GI_MenuView')){
            $this->setMenuView($layoutArray['menu']);
        }
        
        if(isset($layoutArray['breadcrumbMenu']) && is_a($layoutArray['breadcrumbMenu'], 'GI_MenuView')) {
            $this->setBreadCrumbMenuView($layoutArray['breadcrumbMenu']);
        }
        
        if(isset($layoutArray['currentUser']) && is_a($layoutArray['currentUser'], 'AbstractUser')) {
            $this->setCurrentUser($layoutArray['currentUser']);
        }
        
        if(isset($layoutArray['userAvatar']) && is_a($layoutArray['userAvatar'], 'GI_View')) {
            $this->setUserAvatarView($layoutArray['userAvatar']);
        }
        
        if(isset($layoutArray['listBarContent'])) {
            $this->setListBarContent($layoutArray['listBarContent']);
        }
        
        if(isset($layoutArray['targetId'])) {
            $this->setTargetId($layoutArray['targetId']);
        }
        
        if(isset($layoutArray['curMenuRef']) && !empty($this->menuView)){
            $this->menuView->setCurMenuRef($layoutArray['curMenuRef']);
        }
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
    
    public function setTargetId($targetId){
        $this->targetId = $targetId;
        return $this;
    }
    
    public function setListBarContent($html) {
        $this->listBarContent = $html;
    }

    /**
     * 
     * @return GI_View
     */
    public function getListBarContent() {
        return $this->listBarContent;
    }
    
    public function setListBarURL($url) {
        $this->listBarURL = $url;
    }
    
    public function getListBarURL() {
        if (!empty($this->listBarURL)) {
            if(strpos($this->listBarURL, '?') !== false && strpos($this->listBarURL, 'fullView') == false){
                $this->listBarURL .= '&fullView=1';
            }
            return $this->listBarURL;
        }
        return NULL;
    }
    
    public function setListBarClass($listBarClass) {
        $this->listBarClass = $listBarClass;
    }
    
    protected function addDefaultJS(){
        parent::addDefaultJS();
        
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/forms.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/core.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/layout.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_modal.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/notes.js');
        
        //Detect resizing of element
        $this->addJS('resources/external/js/css-element-queries/ResizeSensor.js');
        $this->addJS('resources/external/js/css-element-queries/ElementQueries.js');
        
        //Module Specific Javascript
        if (dbConnection::isModuleInstalled('inventory')) {
            $this->addJS('framework/modules/Inventory/' . MODULE_INVENTORY_VER . '/resources/inventory.js');
        }
        if (dbConnection::isModuleInstalled('contact')) {
            $this->addJS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/contacts.js');
        }
        if (dbConnection::isModuleInstalled('accounting')) {
            $this->addJS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.js');
            $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/global_accounting.js');
        }
        
        //This calls actions in the accounting controller, which is not present w/o the accounting module
     ///  $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/global_accounting.js');
        
        /* Google Map */
        $googleApiKey = ProjectConfig::getGoogleAPIKey();
        if (!empty($googleApiKey)) {
            $this->addJS('resources/js/custom_google_map.js');
            $this->addJS('https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey.'&callback=googleMapInit', false);
        }
    }
    
    protected function addDefaultCSS() {
        parent::addDefaultCSS();
        if (dbConnection::isModuleInstalled('contact')) {
            $this->addCSS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/contacts.css');
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
    
    protected function addBreadCrumbs(){
        if (!empty($this->breadCrumbMenuView)) {
            $this->addHTML('<div id="breadcrumbs">');
            $this->addHTML($this->breadCrumbMenuView->getHTMLView());
            $this->addHTML('</div>');
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
        if (!empty($this->currentUser)) {
//            $userLink = GI_URLUtils::buildURL(array(
//                'controller' => 'user',
//                'action' => 'view',
//                'id' => Login::getUserId(),
//                'profile' => 1
//            ));
            $userLink = '';
            $user = Login::getUser();
            if (!empty($user)) {
                $contactOrg = $user->getContactOrg();
                if (!empty($contactOrg)) {
                    $userLinkAttrs = $contactOrg->getProfileViewURLAttrs();
                    $userLinkAttrs['tab'] = 'my_settings';
                    $userLink = GI_URLUtils::buildURL($userLinkAttrs);
                }
            }

            $this->addHTML('<div id="user_bar">');
            $this->addHTML('<a href="' . $userLink . '">');
            $this->addHTML('<span class="user_name">' . $this->currentUser->getFullName() . '</span>');
            $this->addHTML('<span class="user_email">' . $this->currentUser->getProperty('email') . '</span>');

            if($this->notificationCountPos == 'user_bar'){
                $this->addNotificationCount();
            }
            
            $this->addUserAvatar();
            
            $this->addHTML('</a>');
            $this->addHTML('</div>');
        }
        
        return $this;
    }
    
    protected function addNotificationCount(){
        $notificationCount = $this->currentUser->getNotificationCount();
        $notifyClass = '';
        if($notificationCount == 0){
            $notifyClass = 'hide_on_load';
        }
        $this->addHTML('<span class="notify_count ' . $notifyClass . '">' . $notificationCount . '</span>');
    }
    
    protected function addUserAvatar(){
        $this->addHTML('<span class="user_avatar_wrap">');
        if($this->notificationCountPos == 'avatar'){
            $this->addNotificationCount();
        }
        if ($this->userAvatarView) {
            $this->addHTML($this->userAvatarView->getHTMLView());
        } elseif($this->currentUser){
            $this->addHTML($this->currentUser->getUserAvatarHTML());
        } else {
            $this->addHTML('<span class="avatar_placeholder"><span class="icon avatar"></span></span>');
        }
        $this->addHTML('</span>');
    }
    
    public function display() {
        $mainContentHtml = $this->getMainContent();
        $this->addHeader()
            ->openPageDiv()
                ->openHeaderWrapDiv()
                    ->openTwoColPanelWrapDiv()
                        ->openLeftPanelWrapDiv()
                            ->addUserBar()
                        ->closeLeftPanelWrapDiv()
                        ->openRightPanelWrapDiv()
                            ->openHeaderDiv()
                                ->addLogo()
                                ->addWarehouseInfo()
                            ->closeHeaderDiv()
                        ->closeRightPanelWrapDiv()
                    ->closeTwoColPanelWrapDiv()
                    ->addMenuBtn()
                ->closeHeaderWrapDiv()
                ->addDevModeBanner()
                ->addBars()
                ->openContentWrapDiv()
                    ->openTwoColPanelWrapDiv()
                        ->openLeftPanelWrapDiv(NULL, 'flex_row')
                            //Menu bar
                            ->openMenuBarDiv()
                                ->addMenu()
                            ->closeMenuBarDiv()
                            //List bar
                            ->openListBarDiv();
                            if ($this->targetId == 'list_bar') {
                                $this->addHTML($mainContentHtml);
                            } else {
                                $this->addHTML($this->getListBarContent());
                            }
                            $this->closeListBarDiv()
                        ->closeLeftPanelWrapDiv()
                        ->openRightPanelWrapDiv()
                            //Main Content
                            ->openContentDiv();
                                if ($this->targetId == 'main_window') {
                                    $this->openMainWindowDiv();
                                    $this->addBreadCrumbs();
                                    $this->addHTML($mainContentHtml);
                                } else {
                                    $this->openMainWindowDiv('empty');
                                }
                                $this->closeMainWindowDiv();
                            $this->closeContentDiv()
                
                        ->closeRightPanelWrapDiv()
                    ->closeTwoColPanelWrapDiv()
                ->closeContentWrapDiv()
                ->openFooterTag()
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
        $this->addHTML('<header id="header_wrap" class="two_col_panel_wrap '.$class.'">');
        return $this;
    }
    
    protected function closeHeaderWrapDiv(){
        $this->addHTML('</header>');
        return $this;
    }
    
    protected function openTwoColPanelWrapDiv($class = ''){
        $this->addHTML('<div class="two_col_panel_wrap '.$class.'">');
        return $this;
    }
    
    protected function closeTwoColPanelWrapDiv(){
        $this->addHTML('</div>');
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
    
    protected function openMainWindowDiv($class = ''){
        $this->addHTML('<div id="main_window" class="'.$class.'">');
        return $this;
    }
    
    protected function closeMainWindowDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openLeftPanelWrapDiv($id = NULL, $class = ''){
        $this->addHTML('<div '.(isset($id) ? 'id="'.$id.'" ':'').'class="left_panel '.$class.'">');
        return $this;
    }
    
    protected function closeLeftPanelWrapDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openRightPanelWrapDiv($id = NULL, $class = ''){
        $this->addHTML('<div '.(isset($id) ? 'id="'.$id.'" ':'').'class="right_panel '.$class.'">');
        return $this;
    }
    
    protected function closeRightPanelWrapDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openMenuBarDiv($class = ''){
        $this->addHTML('<div id="menu_bar" class="'.$class.'">');
        return $this;
    }
    
    protected function closeMenuBarDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openListBarDiv($class = ''){
        $this->addHTML('<div id="list_bar" ');
        
        $listBarURL = $this->getListBarURL();
        $data = '';
        if (!empty($listBarURL)) {
            $class .= ' ajaxed_contents auto_load';
            if (!empty($this->listBarClass)) {
                $class .= ' '.$this->listBarClass;
            }
            $data = ' data-url="'.$listBarURL.'"';
        }
        
        $this->addHTML('class="'.$class.'"'.$data.'>');
        
        return $this;
    }
    
    protected function closeListBarDiv(){
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
    
    protected function addAdminMenu($hasAtLeastOneAdminPermission = false){
        $adminPermissions = array(
            'view_users',
            'view_role_ranks',
            'view_permissions',
            'view_pricing_regions',
            'view_eco_fees',
            'super_admin',
            'all_warehouses',
            'import_contacts_from_quickbooks',
            'view_qb_settings',
            'view_other_user_activity',
            'view_settings',
        );
        if (!$hasAtLeastOneAdminPermission) {
            foreach ($adminPermissions as $adminPermission) {
                if (Permission::verifyByRef($adminPermission)) {
                    $hasAtLeastOneAdminPermission = true;
                    break;
                }
            }
        }

        $assignedToMultipleWarehouses = AssignedToContactFactory::userAssignedToMultipleWarehouses();

        if($hasAtLeastOneAdminPermission || (Permission::verifyByRef('view_contacts') && $assignedToMultipleWarehouses)){
            $this->menuView->addSubMenu('main', 'admin', $this->getMenuTextWithSVGIcon('admin', 'Admin'));
            
//            $myProfileURL = GI_URLUtils::buildURL(array(
//                'controller' => 'user',
//                'action' => 'view',
//                'id' => Login::getUserId(),
//                'profile' => 1
//            ));
//        
//            $this->menuView->addMenuItem('admin', 'My Profile', $myProfileURL);
            
            if(Permission::verifyByRef('view_contacts') && $assignedToMultipleWarehouses){
                $warehouseURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'warehouseIndex'
                ));
                $this->menuView->addMenuItem('admin', 'Warehouses', $warehouseURL, array(
                    'linkClass' => 'admin'
                ));
            }
            
            if(Permission::verifyByRef('view_users')){
                $usersURL = GI_URLUtils::buildURL(array(
                    'controller' => 'user',
                    'action' => 'index'
                ));
                $this->menuView->addMenuItem('admin', 'Users', $usersURL, array(
                    'linkClass' => 'admin'
                ));
            }

            if(Permission::verifyByRef('view_role_ranks')){
                $rolesURL = GI_URLUtils::buildURL(array(
                    'controller' => 'role',
                    'action' => 'index'
                ));
                $this->menuView->addMenuItem('admin', 'Role Groups', $rolesURL, array(
                    'linkClass' => 'admin'
                ));
            }

            if(Permission::verifyByRef('view_permissions')){
                $permissionsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'permission',
                    'action' => 'index'
                ));
                $this->menuView->addMenuItem('admin', 'Permissions', $permissionsURL, array(
                    'linkClass' => 'admin'
                ));
            }

            if(Permission::verifyByRef('view_pricing_regions')) {
                 $pricingRegionsURL = GI_URLUtils::buildURL(array(
                     'controller'=>'admin',
                     'action'=>'pricingRegionIndex',
                 ));
                 $this->menuView->addMenuItem('admin', 'Pricing Regions', $pricingRegionsURL, array(
                    'linkClass' => 'admin'
                ));
            }

            if(dbConnection::isModuleInstalled('accounting') && !QBTaxCodeFactory::getTaxingUsesQBAst() && Permission::verifyByRef('view_eco_fees')) {
                $ecoFeesURL = GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'ecoFeeIndex'
                ));
                $sampleRegion = RegionFactory::buildNewModel();
                $this->menuView->addMenuItem('admin', $sampleRegion->getEcoFeeIndexTitle(), $ecoFeesURL, array(
                    'linkClass' => 'admin'
                ));
            }
            
            if(Permission::verifyByRef('super_admin')){
                $logsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'admin',
                    'action' => 'viewLogs'
                ));
                $this->menuView->addMenuItem('admin', 'View Logs', $logsURL, array(
                    'linkClass' => 'admin'
                ));
            }
            if (Permission::verifyByRef('view_other_user_activity')) {
                $userActivityURL = GI_URLUtils::buildURL(array(
                            'controller' => 'user',
                            'action' => 'activityIndex',
                            'id' => Login::getUserId(),
                ));
                $this->menuView->addMenuItem('admin', 'Recent Activity', $userActivityURL, array(
                    'linkClass' => 'admin'
                ));
            }
            if (Permission::verifyByRef('view_settings')) {
                $viewSettingsURL = GI_URLUtils::buildURL(array(
                            'controller' => 'admin',
                            'action' => 'settingsIndex',
                ));
                $this->menuView->addMenuItem('admin', 'Settings', $viewSettingsURL, array(
                    'linkClass' => 'admin'
                ));
            }
            if (Permission::verifyByRef('import_contacts_from_quickbooks') || Permission::verifyByRef('view_qb_settings')) {
                $this->menuView->addSubMenu('admin', 'qb', 'Quickbooks');
                if (Permission::verifyByRef('view_qb_settings')) {
                    $qbSettingsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'admin',
                                'action' => 'qbSettingsIndex',
                                'tab' => 'general',
                    ));
                    $this->menuView->addMenuItem('qb', 'Settings', $qbSettingsURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                if (Permission::verifyByRef('import_contacts_from_quickbooks')) {
                    $importQBContactsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'qbImportIndex'
                    ));
                    $this->menuView->addMenuItem('qb', 'Unlinked Contacts', $importQBContactsURL, array(
                        'linkClass' => 'admin'
                    ));
                }
            }
        }
    }

    protected function addFranchiseHeadOfficeMenu() {
        $this->menuView->addSubMenu('main', 'head_office', $this->getMenuTextWithSVGIcon('head_office', 'Head Office'));
        $actAsURL = GI_URLUtils::buildURL(array(
            'controller' => 'franchise',
            'action' => 'changeCurrentFranchise'
        ));
        $this->menuView->addMenuItem('head_office', 'Act As', $actAsURL, array(
            'anchorClass' => 'open_modal_form'
        ));
        if (Permission::verifyByRef('view_franchise_index')) {
            $franchiseContactsURL = GI_URLUtils::buildURL(array(
                'controller' => 'contact',
                'action' => 'index',
                'type' => 'franchise',
            ));
            $this->menuView->addMenuItem('head_office', 'Franchises', $franchiseContactsURL);
        }
    }

    protected function addContentMenu() {
        if (dbConnection::isModuleInstalled('content')){
            $permissions = array(
                'view_content_index'
            );
            $hasAtLeastOnePermission = false;
            foreach ($permissions as $permission) {
                if (Permission::verifyByRef($permission)) {
                    $hasAtLeastOnePermission = true;
                    break;
                }
            }
            if ($hasAtLeastOnePermission) {
                $this->menuView->addSubMenu('main', 'content', $this->getMenuTextWithSVGIcon('content', 'Content'));

                $contentURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index'
                ));

                $this->menuView->addMenuItem('content', 'Content', $contentURL);
                if (dbConnection::isModuleInstalled('blog')){
                    $blogURL = GI_URLUtils::buildURL(array(
                        'controller' => 'content',
                        'action' => 'index',
                        'type' => 'page_post'
                    ));

                    $this->menuView->addMenuItem('content', 'Blog', $blogURL);
                }
                
                $contentAdminPermissions = array(
                    'view_content_tag_index'
                );
                $hasAtLeastOneContentAdminPermission = false;
                foreach ($contentAdminPermissions as $contentAdminPermission) {
                    if (Permission::verifyByRef($contentAdminPermission)) {
                        $hasAtLeastOneContentAdminPermission = true;
                        break;
                    }
                }

                if($hasAtLeastOneContentAdminPermission){
                    $this->menuView->addSubMenu('content', 'content_admin', 'Admin', '', array(
                        'linkClass' => 'admin'
                    ));

                    if(Permission::verifyByRef('view_content_tag_index')){
                        $tagsURL = GI_URLUtils::buildURL(array(
                            'controller' => 'tag',
                            'action' => 'index',
                            'type' => 'content'
                        ));
                        $this->menuView->addMenuItem('content_admin', 'Tags', $tagsURL, array(
                            'linkClass' => 'admin'
                        ));
                    }
                }
            }
        }
    }
    
    protected function addTimesheetMenu(){
        if(dbConnection::isModuleInstalled('timesheet')){
            $timesheetURL = GI_URLUtils::buildURL(array(
                'controller' => 'timesheet',
                'action' => 'index'
            ));
            
            $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('timesheet', 'Timesheet'), $timesheetURL);
        }
    }
    
    protected function addInventoryMenu(){
        if(dbConnection::isModuleInstalled('inventory')){
            if (!Permission::verifyByRef('view_inventory_index')) {
                return;
            }
            $this->menuView->addSubMenu('main', 'inventory', $this->getMenuTextWithSVGIcon('inventory', 'Inventory'));

            $invItemListURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'index',
                'itemIndex' => 1,
            ));
            $this->menuView->addMenuItem('inventory', 'List', $invItemListURL);
            
            $invItemURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'index',
            ));
            $this->menuView->addMenuItem('inventory', 'Stock', $invItemURL);
            
            $invAdminPermissions = array(
                'view_stock',
                'view_forecast_index',
                'convert_stock',
                'manage_inv_item_cats',
                'view_inventory_tag_index',
                'export_sold_stock_report',
                'export_unsold_stock_report',
                'view_inventory_transfers_list',
                'view_container_index',
                'count_inventory'
            );
            $hasAtLeastOneInvAdminPermission = false;
            foreach ($invAdminPermissions as $invAdminPermission) {
                if (Permission::verifyByRef($invAdminPermission)) {
                    $hasAtLeastOneInvAdminPermission = true;
                    break;
                }
            }
            
            if($hasAtLeastOneInvAdminPermission){
                $this->menuView->addSubMenu('inventory', 'inv_admin', 'Admin', '', array(
                    'linkClass' => 'admin'
                ));
                
                if(Permission::verifyByRef('view_stock') || Permission::verifyByRef('view_forecast_index')){
                    $invItemURL = GI_URLUtils::buildURL(array(
                        'controller' => 'inventory',
                        'action' => 'forecastIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Stock Forecast', $invItemURL, array(
                        'linkClass' => 'admin'
                    ));
                }

                if(Permission::verifyByRef('count_inventory')){
                    $invCountURL = GI_URLUtils::buildURL(array(
                        'controller' => 'inventory',
                        'action' => 'countIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Count', $invCountURL, array(
                        'linkClass' => 'admin'
                    ));
                }

                if (Permission::verifyByRef('convert_stock')) {
                    $inventoryConversionURL = GI_URLUtils::buildURL(array(
                        'controller' => 'inventory',
                        'action' => 'conversionIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Conversions', $inventoryConversionURL, array(
                        'linkClass' => 'admin'
                    ));
                }

                if(Permission::verifyByRef('manage_inv_item_cats')){
                    $invItemCatIndexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'inventory',
                        'action' => 'categoryIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Categories', $invItemCatIndexURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                if (Permission::verifyByRef('view_inventory_tag_index')) {
                    $invTagsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'tag',
                                'action' => 'index',
                                'type' => 'inventory',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Tags', $invTagsURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                if (Permission::verifyByRef('export_sold_stock_report') || Permission::verifyByRef('export_unsold_stock_report')) {
                    $exportStockURL = GI_URLUtils::buildURL(array(
                                'controller' => 'inventory',
                                'action'=>'exportStock',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Exports', $exportStockURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                if (Permission::verifyByRef('view_inventory_transfers_list')) {
                    $inventoryTransfersURL = GI_URLUtils::buildURL(array(
                                'controller' => 'inventory',
                                'action' => 'transferIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Transfers', $inventoryTransfersURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                if (Permission::verifyByRef('view_container_index')) {
                    $containerSearchURL = GI_URLUtils::buildURL(array(
                        'controller' => 'inventory',
                        'action' => 'containerIndex',
                    ));
                    $this->menuView->addMenuItem('inv_admin', 'Container Search', $containerSearchURL, array(
                        'linkClass' => 'admin'
                    ));
                }
            }
        }
    }

    /**
     * @deprecated - use separate menu items for vendor, client, etc.
     */
    protected function addContactMenu() {
        if (dbConnection::isModuleInstalled('contact')) {
            $permissions = array(
                'view_contacts_index'
            );
            $hasAtLeastOnePermission = false;
            foreach ($permissions as $permission) {
                if (Permission::verifyByRef($permission)) {
                    $hasAtLeastOnePermission = true;
                    break;
                }
            }
            if ($hasAtLeastOnePermission) {
                $this->menuView->addSubMenu('main', 'contacts', $this->getMenuTextWithSVGIcon('contacts', 'Contacts'));
                $contactCatTypeRefs = ContactCatFactory::getTypesArray(NULL, false, 'title', true);
                if (isset($contactCatTypeRefs['category'])) {
                    unset($contactCatTypeRefs['category']);
                }
                if (ProjectConfig::useContactCatIndex()) {
                    if (!empty($contactCatTypeRefs)) {
                        foreach ($contactCatTypeRefs as $typeRef => $typeTitle) {
                            $contactCat = ContactCatFactory::buildNewModel($typeRef);
                            if ($contactCat->isIndexViewable()) {
                                $catIndexURL = GI_URLUtils::buildURL(array(
                                            'controller' => 'contact',
                                            'action' => 'catIndex',
                                            'type' => $typeRef,
                                ));
                                $this->menuView->addMenuItem('contacts', $typeTitle, $catIndexURL);
                            }
                        }
                    }
                } else {
                    $indContactsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'index',
                                'type' => 'ind',
                    ));
                    $orgContactsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'index',
                                'type' => 'org',
                    ));
                    $locContactsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contact',
                                'action' => 'index',
                                'type' => 'loc',
                    ));
                    $this->menuView->addMenuItem('contacts', 'Individuals', $indContactsURL);

                    $this->menuView->addMenuItem('contacts', 'Organizations', $orgContactsURL);

                    $this->menuView->addMenuItem('contacts', 'Locations', $locContactsURL);
                }
                $hasEventPermission = false;
                if (!empty($contactCatTypeRefs)) {
                    foreach ($contactCatTypeRefs as $typeRef => $typeTitle) {
                        $contactCat = ContactCatFactory::buildNewModel($typeRef);
                        if ($contactCat->isEventIndexViewable()) {
                            $hasEventPermission = true;
                            break;
                        }
                    }
                }
                if ($hasEventPermission) {
                    $contactEvetnsURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contactevent',
                                'action' => 'index',
                    ));
                    $this->menuView->addMenuItem('contacts', 'Contact History', $contactEvetnsURL);
                }
                $hasAtLeastOneAdminPermission = false;
                $adminPermissions = array(
                    'view_contact_subcats',
                );
                foreach ($adminPermissions as $adminPermission) {
                    if (Permission::verifyByRef($adminPermission)) {
                        $hasAtLeastOneAdminPermission = true;
                        break;
                    }
                }
                if ($hasAtLeastOneAdminPermission) {
                    $this->menuView->addSubMenu('contacts', 'contacts_admin', 'Admin');
                    if (Permission::verifyByRef('view_contact_subcats')) {
                        $contactSubCatTagURL = GI_URLUtils::buildURL(array(
                                    'controller' => 'tag',
                                    'action' => 'index',
                                    'type' => 'contact_sub_cat'
                        ));
                        $this->menuView->addMenuItem('contacts_admin', Lang::getString('contact_sub_category_pl'), $contactSubCatTagURL);
                    }
                }
            }
        }
    }

    protected function addClientMenu() {
        if (dbConnection::isModuleInstalled('contact') && Permission::verifyByRef('view_contact_client_index')) {
            $this->menuView->addSubMenu('main', 'clients', $this->getMenuTextWithSVGIcon('contacts', 'Clients'));
            $clientsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'index',
                        'type' => 'client'
            ));
            $this->menuView->addMenuItem('clients', 'Clients', $clientsURL);

            if (Permission::verifyByRef('view_c_events_index') || Permission::verifyByRef('view_c_client_events_index')) {

                $contactEventsURL = GI_URLUtils::buildURL(array(
                            'controller' => 'contactevent',
                            'action' => 'index',
                            'catType' => 'client',
                ));
                $this->menuView->addMenuItem('clients', 'Contact History', $contactEventsURL);
            }
        }
    }

    protected function addVendorMenu() {
        if (dbConnection::isModuleInstalled('contact') && Permission::verifyByRef('view_contact_vendor_index')) {
            $vendorURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'index',
                        'type' => 'vendor',
            ));
            $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('vendor', 'Vendors'), $vendorURL);
        }
    }

    protected function addInternalMenu() {
        if (dbConnection::isModuleInstalled('contact') && Permission::verifyByRef('view_contact_internal_index')) {
                        $myCompanyURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'index',
                        'type' => 'internal',
            ));
            
            $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('building', 'My Company'), $myCompanyURL);
        }
    }

    protected function addAccountingMenu() {
        if (dbConnection::isModuleInstalled('accounting')) {
            $accountingViewPermissions = array(
                'view_bills',
                'view_payments',
                'view_invoices_index',
                'view_invoice_statements_index',
                'view_invoice_quotes_index',
                'view_invoice_payments_index',
                'view_credits_index',
                'view_reports',
            );
            $hasAtLeastOneAccountingPermission = false;
            foreach ($accountingViewPermissions as $accountingViewPermission) {
                if (Permission::verifyByRef($accountingViewPermission)) {
                    $accountingViewPermissions[$accountingViewPermission] = true;
                    $hasAtLeastOneAccountingPermission = true;
                    break;
                }
            }
            if ($hasAtLeastOneAccountingPermission) {
                if (ProjectConfig::getIsQuickbooksIntegrated()) {
                    $this->addQuickbooksIntegratedAccountingMenu();
                } else {
                    $this->addNoQuickbooksAccountingMenu();
                }
            }
        }
    }

    protected function addNoQuickbooksAccountingMenu() {
        $this->menuView->addSubMenu('main', 'accounting', $this->getMenuTextWithSVGIcon('accounting', 'Accounting'));


        if (Permission::verifyByRef('view_invoices_index') || Permission::verifyByRef('view_invoice_statements_index') || Permission::verifyByRef('view_invoice_quotes_index') || Permission::verifyByRef('view_invoice_payments_index') || Permission::verifyByRef('view_credits_index')) {
            $this->menuView->addSubMenu('accounting', 'ar', 'Sales');
        }
        if (Permission::verifyByRef('view_payments') || Permission::verifyByRef('view_bills')) {
            $this->menuView->addSubMenu('accounting', 'ap', 'Expenses');
        }


        if (Permission::verifyByRef('view_bills')) {
            $billsURL = GI_URLUtils::buildURL(array(
                'controller' => 'billing',
                'action' => 'index',
            ));
            $this->menuView->addMenuItem('accounting', 'Bills', $billsURL);
//            $apBillsURL = GI_URLUtils::buildURL(array(
//                        'controller' => 'accounting',
//                        'action' => 'accountsPayable',
//                        'tab' => 'bills'
//            ));
//            $this->menuView->addMenuItem('ap', 'Bills', $apBillsURL);
//
//            if (dbConnection::isModuleInstalled('order')) {
//                $apPOBillsURL = GI_URLUtils::buildURL(array(
//                            'controller' => 'accounting',
//                            'action' => 'accountsPayable',
//                            'tab' => 'po_bills'
//                ));
//                $this->menuView->addMenuItem('ap', 'Purchase Order Bills', $apPOBillsURL);
//                $apSOBillsURL = GI_URLUtils::buildURL(array(
//                            'controller' => 'accounting',
//                            'action' => 'accountsPayable',
//                            'tab' => 'so_bills'
//                ));
//                $this->menuView->addMenuItem('ap', 'Sales Order Bills', $apSOBillsURL);
//            }
        }
        if (Permission::verifyByRef('view_payments')) {
            $apPaymentsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsPayable',
                        'tab' => 'payments',
            ));
            $this->menuView->addMenuItem('ap', 'Payments', $apPaymentsURL);
            $apImportedPaymentsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsPayable',
                        'tab' => 'imported_payments',
            ));
            $this->menuView->addMenuItem('ap', 'Imported Payments', $apImportedPaymentsURL);
        }

        //AR
        if (Permission::verifyByRef('view_invoices_index')) {
            $arInvoicesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsReceivable',
                        'tab' => 'invoices',
            ));
            $this->menuView->addMenuItem('ar', 'Invoices', $arInvoicesURL);
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && Permission::verifyByRef('view_invoice_quotes_index')) {
            $arInvoiceQuotesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsReceivable',
                        'tab' => 'quotes',
            ));
            $this->menuView->addMenuItem('ar', 'Quotes', $arInvoiceQuotesURL);
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && Permission::verifyByRef('view_invoice_statements_index')) {
            $arInvoiceQuotesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsReceivable',
                        'tab' => 'statements',
            ));
            $this->menuView->addMenuItem('ar', 'Statements', $arInvoiceQuotesURL);
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && Permission::verifyByRef('view_invoice_payments_index')) {
            $arPaymentsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsReceivable',
                        'tab' => 'payments',
            ));
            $this->menuView->addMenuItem('ar', 'Payments', $arPaymentsURL);
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && Permission::verifyByRef('view_credits_index')) {
            $arCreditsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountsReceivable',
                        'tab' => 'credits',
            ));
            $this->menuView->addMenuItem('ar', 'Credits', $arCreditsURL);
        }


        if (Permission::verifyByRef('view_reports')) {
            $reportsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'accountingReportsLive',
            ));
            $this->menuView->addMenuItem('accounting', 'Reports', $reportsURL);
        }

        if (Permission::verifyByRef('export_incomes') || Permission::verifyByRef('export_income_payments') || Permission::verifyByRef('export_expenses') || Permission::verifyByRef('export_expense_payments') || Permission::verifyByRef('export_ar_invoices')) {
            $exportURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'export',
            ));
            $this->menuView->addMenuItem('accounting', 'Exports', $exportURL);
        }
    }

    protected function addQuickbooksIntegratedAccountingMenu() {
        $this->menuView->addSubMenu('main', 'accounting', $this->getMenuTextWithSVGIcon('accounting', 'Accounting'));
        
        if (Permission::verifyByRef('view_invoices')) {
            $invoicesURL = GI_URLUtils::buildURL(array(
                'controller'=>'invoice',
                'action'=>'index',
            ));
            $this->menuView->addMenuItem('accounting', 'Invoices', $invoicesURL);
        }

        
//        $orderBills = false;
//        if (dbConnection::isModuleInstalled('order')) {
//            $orderBills = true;
//        }
//        
//        if (!$orderBills) {
        if (Permission::verifyByRef('view_bills')) {
            $billsURL = GI_URLUtils::buildURL(array(
                'controller' => 'billing',
                'action' => 'index',
            ));
            $this->menuView->addMenuItem('accounting', 'Bills', $billsURL);
        }
//        } else {
//            if (Permission::verifyByRef('view_bills')) {
//                $this->menuView->addSubMenu('accounting', 'ap', 'Bills');
//                $apBillsURL = GI_URLUtils::buildURL(array(
//                            'controller' => 'accounting',
//                            'action' => 'accountsPayable',
//                            'tab' => 'po_bills'
//                ));
//                $this->menuView->addMenuItem('ap', 'Purchase Order Bills', $apBillsURL);
//                $apSOBillsURL = GI_URLUtils::buildURL(array(
//                            'controller' => 'accounting',
//                            'action' => 'accountsPayable',
//                            'tab' => 'so_bills'
//                ));
//                $this->menuView->addMenuItem('ap', 'Sales Order Bills', $apSOBillsURL);
//            }
//        }

        if (Permission::verifyByRef('view_reports')) {
            $reportsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'reports',
            ));
            $this->menuView->addMenuItem('accounting', 'Reports', $reportsURL);
        }

        if (Permission::verifyByRef('export_incomes') || Permission::verifyByRef('export_income_payments') || Permission::verifyByRef('export_expenses') || Permission::verifyByRef('export_expense_payments') || Permission::verifyByRef('export_ar_invoices')) {
            $exportURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'export',
            ));
            $this->menuView->addMenuItem('accounting', 'Exports', $exportURL);
        }
        if (Permission::verifyByRef('export_adjustments_to_quickbooks')) {
            $exportAdjustmentsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'exportAdjustmentsIndex',
            ));
            $this->menuView->addMenuItem('accounting', 'Adjustments', $exportAdjustmentsURL);
        }
    }

    protected function addOrdersMenu() {
        if (dbConnection::isModuleInstalled('order')) {
            $orderPermissions = array(
                'view_purchase_order_index',
                'view_sales_order_index',
                'view_shipping_index',
                'view_receiving_index',
                'view_returns_index',
                'view_sales_order_line_index',
                'view_inventory_transfers_list'
            );
            $hasAtLeastOneOrderPermission = false;
            foreach ($orderPermissions as $orderPermission) {
                if (Permission::verifyByRef($orderPermission)) {
                    $orderPermissions[$orderPermission] = true;
                    $hasAtLeastOneOrderPermission = true;
                    break;
                }
            }
            if ($hasAtLeastOneOrderPermission) {
                //Purchase order
                if (Permission::verifyByRef('view_purchase_order_index')) {
                    $purchaseOrdersURL = GI_URLUtils::buildURL(array(
                                'controller' => 'order',
                                'action' => 'index',
                                'type' => 'purchase',
                    ));
                    $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('purchase_order', 'Purchase Orders'), $purchaseOrdersURL, array(
                        'itemRef' => 'purchase_orders'
                    ));
                }
                //Receiving
                if (Permission::verifyByRef('view_receiving_index') || Permission::verifyByRef('view_returns_index')) {
                    $this->menuView->addSubMenu('main', 'receiving', $this->getMenuTextWithSVGIcon('receiving', 'Receiving'));
                    if (Permission::verifyByRef('view_receiving_index')) {
                        $poShipmentReceivingURL = GI_URLUtils::buildURL(array(
                                    'controller' => 'order',
                                    'action' => 'receiving',
                                    'tab' => 'po_shipments',
                        ));
                        $this->menuView->addMenuItem('receiving', 'PO Shipments', $poShipmentReceivingURL);
                    }
                    if (Permission::verifyByRef('view_returns_index')) {
                        $returnsReceivingURL = GI_URLUtils::buildURL(array(
                                    'controller' => 'order',
                                    'action' => 'receiving',
                                    'tab' => 'returns',
                        ));
                        $this->menuView->addMenuItem('receiving', 'Returns', $returnsReceivingURL);
                    }
                    if (Permission::verifyByRef('view_inventory_transfers_list')) {
                        $incomingTransfersURL = GI_URLUtils::buildURL(array(
                            'controller'=>'inventory',
                            'action'=>'transferIndex',
                            'inbound'=>1,
                        ));
                        $this->menuView->addMenuItem('receiving', 'In Transit Transfers', $incomingTransfersURL);
                    }
                }
                
                //Sales Orders
                if (Permission::verifyByRef('view_sales_order_index') || Permission::verifyByRef('view_sales_order_line_index')) {
                    $this->menuView->addSubMenu('main', 'sales_orders', $this->getMenuTextWithSVGIcon('sales_order', 'Sales Orders'));
                    if (Permission::verifyByRef('view_sales_order_index')) {
                        $salesOrdersURL = GI_URLUtils::buildURL(array(
                                    'controller' => 'order',
                                    'action' => 'index', 
                                    'type' => 'sales',
                        ));

                        $this->menuView->addMenuItem('sales_orders', 'Sales Order List', $salesOrdersURL);
                    }
                    if (Permission::verifyByRef('view_sales_order_line_index')) {
                        $backOrdersURL = GI_URLUtils::buildURL(array(
                            'controller' => 'order',
                            'action' => 'lineIndex',
                            'type' => 'sales',
                            'backOrdered' => 1
                        ));
                        $this->menuView->addMenuItem('sales_orders', 'Back Order', $backOrdersURL);
                    }
                    
                    $salesOrderAdminPermissions = array(
                        'export_sales_order_reports'
                        );
                    $hasAtLeastOneSalesOrderAdminPermission = false;
                    foreach ($salesOrderAdminPermissions as $salesOrderAdminPermission) {
                        if (Permission::verifyByRef($salesOrderAdminPermission)) {
                            $hasAtLeastOneSalesOrderAdminPermission = true;
                            break;
                        }
                    }
                    if ($hasAtLeastOneSalesOrderAdminPermission) {
                        $this->menuView->addSubMenu('sales_orders', 'so_admin', 'Admin');
                        if (Permission::verifyByRef('export_sales_order_reports')) {
                            $salesOrderExportURL = GI_URLUtils::buildURL(array(
                                        'controller' => 'order',
                                        'action' => 'exportSalesOrderReports',
                            ));
                            $this->menuView->addMenuItem('so_admin', 'Exports', $salesOrderExportURL);
                        }
                    }
                }
                //Shipping
                if (Permission::verifyByRef('view_shipping_index')) {
                    $shippingURL = GI_URLUtils::buildURL(array(
                                'controller' => 'order',
                                'action' => 'shipmentIndex',
                                'type' => 'sales',
                                'general' => '1'
                    ));
                    $this->menuView->addSubMenu('main', 'shipping', $this->getMenuTextWithSVGIcon('shipping', 'Shipping'));
                    $this->menuView->addMenuItem('shipping', 'Outgoing Shipments', $shippingURL);
                    $containersToReturnURL = GI_URLUtils::buildURL(array(
                        'controller'=>'order',
                        'action'=>'containersToReturnIndex',
                    ));
                    $this->menuView->addMenuItem('shipping', 'Containers to Return', $containersToReturnURL);
                }
            }
        }
    }

    protected function addProjectsMenu() {
        if (dbConnection::isModuleInstalled('project')) {
            $permissions = array(
                'view_projects_index',
                'view_project_templates_index',
                'view_price_sheet_index',
                'view_timesheets_index',
            );
            $hasAtLeastOnePermission = false;
            foreach ($permissions as $permission) {
                if (Permission::verifyByRef($permission)) {
                    $hasAtLeastOnePermission = true;
                    break;
                }
            }
            if ($hasAtLeastOnePermission) {
                $this->menuView->addSubMenu('main', 'projects',$this->getMenuTextWithSVGIcon('project', 'Projects'));
                //TODO - temp - organized like this for dev.
                if (Permission::verifyByRef('view_projects_index')) {
                    
                    $projectsIndexURL = GI_URLUtils::buildURL(array(
                                'controller' => 'project',
                                'action' => 'index',
                                'type' => 'project'
                    ));
                    $this->menuView->addMenuItem('projects', 'Projects List', $projectsIndexURL);
                    $workOrdersIndexURL = GI_URLUtils::buildURL(array(
                                'controller' => 'project',
                                'action' => 'index',
                                'type' => 'work_order'
                    ));
                    $this->menuView->addMenuItem('projects', 'Work Orders List', $workOrdersIndexURL);
                }
                if (Permission::verifyByRef('view_project_templates_index')) {
                    $projectTemplatesIndexURL = GI_URLUtils::buildURL(array(
                        'controller'=>'project',
                        'action'=>'templateIndex',
                    ));
                    $this->menuView->addMenuItem('projects', 'Templates', $projectTemplatesIndexURL);
                }
                if (Permission::verifyByRef('view_price_sheet_index')) {
                    $priceSheetIndexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'project',
                                'action' => 'priceSheetIndex',
                    ));
                    $this->menuView->addMenuItem('projects', 'Price Sheets', $priceSheetIndexURL);
                }
                if (Permission::verifyByRef('view_timesheets_index')) {
                    $timesheetsIndexURL = GI_URLUtils::buildURL(array(
                                'controller' => 'timesheet',
                                'action' => 'index'
                    ));
                    $this->menuView->addMenuItem('projects', 'Timesheets', $timesheetsIndexURL);
                }
            }
        }
    }
    
    protected function addPageDFTypeMenus(){
        $pageDFTypes = ContentFactory::getTypesArray('page_df');
        foreach($pageDFTypes as $pageDFTypeRef => $pageDFTypeTitle){
            $pageDFTmp = ContentFactory::buildNewModel($pageDFTypeRef);
            if(!$pageDFTmp || !$pageDFTmp->showMenuItem()){
                continue;
            }
            /*@var $pageDFTmp AbstractContentPageDF*/
            $pageDFTmp->setProperty('content_page_df.is_template', 1);
            $subMenuRef = 'forms_' . $pageDFTypeRef;
            $this->menuView->addSubMenu('main', $subMenuRef, $this->getMenuTextWithSVGIcon($pageDFTmp->getMenuItemIcon(), $pageDFTmp->getMenuItemLabel()));
            $pageDFTmp->setUpSubMenuItems($subMenuRef, $this->menuView);
        }
    }

    protected function addFormsMenu() {
        if (dbConnection::isModuleInstalled('forms')) {
            $this->addPageDFTypeMenus();
            
            $formsPermissions = array(
                'view_forms_index',
//                'view_assigned_forms_list'
            );
            $hasAtLeastOnePermission = false;
            foreach ($formsPermissions as $formsPermission) {
                if (Permission::verifyByRef($formsPermission)) {
                    $formsPermissions[$formsPermission] = true;
                    $hasAtLeastOnePermission = true;
                    break;
                }
            }
            if (!$hasAtLeastOnePermission) {
                return;
            }
//            $this->menuView->addSubMenu('main', 'forms', 'Forms');
            $this->menuView->addSubMenu('main', 'forms', $this->getMenuTextWithSVGIcon('forms', 'Forms'));
            
            if(Permission::verifyByRef('view_assigned_forms_list')){
//                $myAssDFIndexURL = GI_URLUtils::buildURL(array(
//                    'controller' => 'forms',
//                    'action' => 'assignedIndex',
//                    'userId' => Login::getUserId()
//                ));
//                $this->menuView->addMenuItem('forms', 'My Forms', $myAssDFIndexURL);
                if(Permission::verifyByRef('assign_form')){
                    $assDFIndexURL = GI_URLUtils::buildURL(array(
                        'controller' => 'forms',
                        'action' => 'assignedIndex'
                    ));
                    $this->menuView->addMenuItem('forms', 'Assigned Forms', $assDFIndexURL);
                }
            }
            
            if(Permission::verifyByRef('view_forms_index')){
                $formIndexURL = GI_URLUtils::buildURL(array(
                    'controller' => 'forms',
                    'action' => 'index'
                ));
                $this->menuView->addMenuItem('forms', 'Form Manager', $formIndexURL, array(
                    'linkClass' => 'admin'
                ));
            }
            
            if(Permission::verifyByRef('view_feos_index')){
                $feosIndexURL = GI_URLUtils::buildURL(array(
                    'controller' => 'forms',
                    'action' => 'feosIndex',
                    'type' => 'list'
                ));
                $this->menuView->addMenuItem('forms', 'Option Lists', $feosIndexURL, array(
                    'linkClass' => 'admin'
                ));
                
                $feosTagURL = GI_URLUtils::buildURL(array(
                    'controller' => 'tag',
                    'action' => 'index',
                    'type' => 'feos_option'
                ));
                $this->menuView->addMenuItem('forms', 'Option Groups', $feosTagURL, array(
                    'linkClass' => 'admin'
                ));
            }
        }
    }
    
    protected function addChatMenu() {
        if (dbConnection::isModuleInstalled('chat')) {
            $chatPermissions = array(
                'view_active_chat_client_index'
            );
            $hasAtLeastOnePermission = false;
            foreach ($chatPermissions as $chatPermission) {
                if (Permission::verifyByRef($chatPermission)) {
                    $chatPermissions[$chatPermission] = true;
                    $hasAtLeastOnePermission = true;
                    break;
                }
            }
            if (!$hasAtLeastOnePermission) {
                return;
            }
            
            //@todo until we have more menu items, no sub menu is needed
//            $this->menuView->addSubMenu('main', 'chat', $this->getMenuTextWithSVGIcon('chat', 'Chat'));
            
            if(Permission::verifyByRef('view_active_chat_client_index')){
                $indexURL = GI_URLUtils::buildURL(array(
                    'controller' => 'chat',
                    'action' => 'activeClientIndex'
                ));
                $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('chat', 'Chat'), $indexURL);
                //@todo until we have more menu items, no sub menu is needed
                $this->menuView->addMenuItem('chat', 'Active Clients', $indexURL);
            }
        }
    }
    
    protected function addDevModeBanner(){
        if(DEV_MODE){
            $this->addHTML('<div id="dev_mode"></div>');
        }
        return $this;
    }
    
    protected function addBars(){
        $this->addHTML('<div class="footer_bars">');
        $this->addManualCountBar();
        $this->addFranchiseBar();
        //$this->addWarehouseBar();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addManualCountBar(){
        if(dbConnection::isModuleInstalled('inventory')){
            $invManCount = InvManCountFactory::getCurrentCount();
            if($invManCount){
                $invManCountBtnBar = $invManCount->getBtnBarView();
                $this->addHTML($invManCountBtnBar->getHTMLView());
            }
        }
    }
    
    protected function addWarehouseInfo(){
        if(dbConnection::isModuleInstalled('inventory')){
            $warehouse = InventoryController::getCurInvLoc();
            $this->addHTML('<div id="warehouse_info">');
                $filterBtn = new InvFilterWarehouseBtnView();
                $filterBtn->setAddPaddingIfNoBtn(false);
                $this->addHTML($filterBtn->getHTMLView());
                if($warehouse){
                    $this->addHTML('<span class="info_wrap">');
                    $this->addHTML('<span class="label">' . $warehouse->getName() . '</span>');
                    $this->addHTML('<span class="addr">' . $warehouse->getAddress() . '</span>');
                }
                $this->addHTML('</span>');
            $this->addHTML('</div>');
        }
        return $this;
    }

//    protected function addWarehouseBar(){
//        if(dbConnection::isModuleInstalled('inventory')){
//            $warehouse = InventoryController::getCurInvLoc();
//            if($warehouse){
//                $this->addHTML('<div class="warehouse_bar footer_bar">');
//                $this->addHTML('<div class="flex_row vert_center no_pad">');
//                    $this->addHTML('<span class="flex_col">');
//                        $this->addWarehouseBarLabel($warehouse);
//                    $this->addHTML('</span>');
//                    $this->addHTML('<span class="flex_col right_align">');
//                        $this->addWarehouseBarBtns();
//                    $this->addHTML('</span>');
//                $this->addHTML('</div>');
//                $this->addHTML('</div>');
//            }
//        }
//        return $this;
//    }
//    
//    protected function addWarehouseBarLabel(AbstractContactLoc $warehouse){
//        $this->addHTML('<span class="label_wrap">');
//        $this->addHTML('<span class="label">Warehouse</span>');
//        $this->addHTML('<span class="title">' . $warehouse->getName() . '</span>');
//        $this->addHTML('</span>');
//    }
    
//    protected function addWarehouseBarBtns(){
    protected function addWarehouseFilterBtn(){
        $filterBtn = new InvFilterWarehouseBtnView();
        $this->addHTML('<span class="filter_wrap">');
        $this->addHTML($filterBtn->getHTMLView());
        $this->addHTML('</span>');
        return $this;
    }
    
    protected function addFranchiseBar(){
        if (ProjectConfig::getIsFranchisedSystem() && Permission::verifyByRef('franchise_head_office')) {
            $curFranchise = Login::getCurrentFranchise();
            $user = Login::getUser();
            if ((!empty($curFranchise) && !empty($user)) && ($curFranchise->getProperty('id') != $user->getProperty('franchise_id')) ) {
                $styleString = '';
                $colour = $curFranchise->getColour();
                $barClass = 'light_font';
                if($colour){
                    $styleString .= 'style="background: #' . $colour . ';"';
                    if(!GI_Colour::useLightFont($colour)){
                        $barClass = 'dark_font';
                    }
                }
                $this->addHTML('<div class="franchise_bar footer_bar ' . $barClass . '" ' . $styleString . '>');
                $this->addHTML('<span class="acting_as_wrap label_wrap">');
                $this->addHTML('<span class="acting_as_label label">Acting As</span>');
                $this->addHTML('<span class="acting_as_title title">' . $curFranchise->getTitle() . '</span>');
                $this->addHTML('</span>');
                $this->addHTML('</div>');
            }
        }
        return $this;
    }
    
    protected function addAccountMenu() {
//        $myProfileURL = GI_URLUtils::buildURL(array(
//                'controller' => 'user',
//                'action' => 'view',
//                'id' => Login::getUserId(),
//                'profile' => 1
//            ));
        $myProfileURL = '';
        $user = Login::getUser();
        if (!empty($user)) {
            $contactOrg = $user->getContactOrg();
            if (!empty($contactOrg)) {
                $userLinkAttrs = $contactOrg->getProfileViewURLAttrs();
                $userLinkAttrs['tab'] = 'my_settings';
                $myProfileURL = GI_URLUtils::buildURL($userLinkAttrs);
            }
        }

        $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('account', 'My Account'), $myProfileURL, array('linkClass' => 'login_bar_menu login_bar_menu_start'));

        $logoutURL = GI_URLUtils::buildURL(array(
                    'controller' => 'login',
                    'action' => 'logout',
        ));
        $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('logout', 'Log Out'), $logoutURL, array('linkClass' => 'login_bar_menu login_bar_menu_end'));
    }
    
    protected function addRealEstateMenu() {
        if (dbConnection::isModuleInstalled('realEstate')) {
            if(Permission::verifyByRef('view_re_listing_index') || Permission::verifyByRef('view_modified_mls_listing_index')){
                $this->menuView->addSubMenu('main', 'realEstate', $this->getMenuTextWithSVGIcon('real_estate', 'Real Estate'));
                
                if(Permission::verifyByRef('view_re_listing_index')) {
                    $reIndexURL = GI_URLUtils::buildURL(array(
                        'controller' => 're',
                        'action' => 'index',
                        'type' => 'res'
                    ));
                    $this->menuView->addMenuItem('realEstate', 'Real Estate', $reIndexURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                
                if(Permission::verifyByRef('view_modified_mls_listing_index')) {
                    $mlsIndexURL = GI_URLUtils::buildURL(array(
                        'controller' => 're',
                        'action' => 'index',
                        'type' => 'res_mod'
                    ));
                    $this->menuView->addMenuItem('realEstate', 'Modified MLS', $mlsIndexURL, array(
                        'linkClass' => 'admin'
                    ));
                }
                
            }
        }
    }
}
