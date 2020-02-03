<?php

class MainLayoutView extends AbstractMainLayoutView {
    
    protected function addDefaultCSS() {
        $this->addCSS('https://fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic');
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
            
            $this->addVendorMenu();
            
            $this->addClientMenu();
            
            $this->addOrdersMenu();
            
            $this->addInventoryMenu();
            
            $this->addProjectsMenu();
            
            $this->addAccountingMenu();
            
            $this->addRealEstateMenu();
            
            $this->addChatMenu();
            
            $this->addFormsMenu();

            $this->addContentMenu();
            
            $this->addAdminMenu();
            
            $this->addInternalMenu();
            
            $this->addAccountMenu();
        }
        return $this;
    }
    
    protected function addLogo($fileName = 'full-logo.svg', $path="resources/media/img/logos/"){
        parent::addLogo($fileName, $path);
        return $this;
    }
}
