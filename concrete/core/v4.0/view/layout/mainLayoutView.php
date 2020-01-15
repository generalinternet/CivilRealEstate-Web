<?php

class MainLayoutView extends AbstractMainLayoutView {
    
    protected function addDefaultCSS() {
        $this->addCSS('http://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic&PT+Serif:700i');
        parent::addDefaultCSS();
    }
    
    protected function addDefaultJS() {
        parent::addDefaultJS();
        if(Permission::verifyByRef('super_admin')){
            $dateObj = new DateTime();
            $aprilFools = new DateTime('2019-04-01');
            $afterAprilFools = new DateTime('2019-04-02');
            if($dateObj > $aprilFools && $dateObj < $afterAprilFools){
                $this->addCSS('resources/external/js/boxy/boxy.css');
                $this->addCSS('resources/external/js/boxy/skins/classic/classic.css');
                $this->addJS('resources/external/js/boxy/boxy.js');
            }
        }
        $this->addJS('resources/js/custom.js');
    }
    
    protected function buildMainNav() {
        if (Permission::verifyByRef('view_dashboard')) {
            $dashboardURL = GI_URLUtils::buildURL(array(
                    'controller' => 'dashboard',
                    'action' => 'index',
                ));
            $this->menuView->addMenuItem('main', $this->getMenuTextWithSVGIcon('dashboard', 'Dashboard'), $dashboardURL);
        }
        $userId = Login::getUserId();
        if(!empty($userId)){
            $this->addContactMenu();
            
//            $this->addOrdersMenu();
//            
//            $this->addInventoryMenu();
//            
//            $this->addProjectsMenu();
//            
//            $this->addAccountingMenu();
            
            //set to true to force show the admin menu (for profile link)
            $this->addAdminMenu(true);
            
            //$this->addFormsMenu();

            $this->addContentMenu();
            
            $this->addAgreementMenu();
            
            $this->addAccountMenu();
        }
        return $this;
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
                $this->menuView->addSubMenu('main', 'content', $this->getMenuTextWithSVGIcon('dollars', 'Investment'));

                $startInvestmentURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index',
                    'type' => 'start'
                ));
                $this->menuView->addMenuItem('content', 'Start', $startInvestmentURL);

                $opporInvestmentURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index',
                    'type' => 'opportunities'
                ));
                $this->menuView->addMenuItem('content', 'Opportunities', $opporInvestmentURL);

                $realestateURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index',
                    'type' => 'realestate'
                ));

                $this->menuView->addMenuItem('content', 'Real Estate', $realestateURL);

                $realestateInvestmentURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index',
                    'type' => 'realestate_investment_opportunities'
                ));

                $this->menuView->addMenuItem('content', 'Real Estate Investment Opportunities', $realestateInvestmentURL);

                $kidsFinanceInvestURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'index',
                    'type' => 'kids'
                ));
                $this->menuView->addMenuItem('content', 'Kids', $kidsFinanceInvestURL);
                
            }
        }
    }
    
    protected function addAgreementMenu() {
        //@todo: permission
        if (Permission::verifyByRef('view_content_index')) {
            $this->menuView->addSubMenu('main', 'agreement', $this->getMenuTextWithSVGIcon('clipboard_text', 'Agreement Form'));

            $agreementFormURL = GI_URLUtils::buildURL(array(
                'controller' => 'agreement',
                'action' => 'indexForm',
            ));

            $this->menuView->addMenuItem('agreement', 'Agreement Form', $agreementFormURL);
            
            $agreementItemURL = GI_URLUtils::buildURL(array(
                'controller' => 'agreement',
                'action' => 'indexFormItem',
            ));

            $this->menuView->addMenuItem('agreement', 'Agreement Form Items', $agreementItemURL);
        }
    }
    
    protected function addLogo($fileName = 'logo-header.png', $path="resources/media/img/logos/"){
        parent::addLogo($fileName, $path);
        return $this;
    }
}
