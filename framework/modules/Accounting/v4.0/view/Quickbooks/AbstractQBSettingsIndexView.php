<?php
/**
 * Description of AbstractQBSettingsIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractQBSettingsIndexView extends MainWindowView {

    protected $currentTabKey = '';
    protected $title = 'Quickbooks Settings';
    protected $showRegionalTab = true;

    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Quickbooks Settings');
        $this->setWindowTitle('Quickbooks Settings');
        
    }
    
    public function setShowRegionalTab($showRegionalTab) {
        $this->showRegionalTab = $showRegionalTab;
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }

    public function buildView() {
        $this->addQBBar();
        parent::buildView();
    }
    
    protected function addViewBodyContent(){
        $this->buildTabs();
    }
    
    protected function addQBBar() {
        $qbBarView = QBConnection::getQuickbooksBarView();
        if (!empty($qbBarView)) {
            $this->addHTML($qbBarView->getHTMLView());
        }
    }

    protected function buildTabs() {
        $tabs = array();
        $tabs['general'] = $this->buildGeneralSettingsTab();
        if ($this->showRegionalTab) {
            $tabs['regional'] = $this->buildRegionalSettingsTab();
        }
        $tabs['accounts'] = $this->buildAccountsTab();
        $tabs['products'] = $this->buildProductsAndServicesTab();
        if (isset($tabs[$this->currentTabKey])) {
            $tabs[$this->currentTabKey]->setCurrent(true);
        } else {
            $tabs['general']->setCurrent(true);
        }
        $tabWrap = new GenericTabWrapView($tabs);
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildGeneralSettingsTab() {
        $generalSettingsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'viewQBSettings',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('General', $generalSettingsURL, true);
        return $tab;
    }
    
    protected function buildRegionalSettingsTab() {
                $regionalSettingsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'regionalQBSettingsIndex',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('Regional', $regionalSettingsURL, true);
        return $tab;
    }

    protected function buildAccountsTab() {
        $accountsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'QBAccountsIndex',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('Accounts', $accountsURL, true);
        return $tab;
    }

    protected function buildProductsAndServicesTab() {
        $productsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'QBProductsIndex',
                    'tabbed' => 1,
        ));
        $tab = new GenericTabView('Products and Services', $productsURL, true);
        return $tab;
    }
}
