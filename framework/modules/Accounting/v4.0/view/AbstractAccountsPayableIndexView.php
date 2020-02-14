<?php

/**
 * Description of AbstractAccountsPayableIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccountsPayableIndexView extends GI_View {

    protected $currentTabKey = 'bills';
    protected $billsTypeRef = 'bill';
    protected $showBillsTab = true;
    protected $showPOBillsTab = true;
    protected $showSOBillsTab = true;
    protected $showPaymentsTab = true;
    protected $showImportedPaymentsTab = true;

    public function __construct() {
        $this->addSiteTitle('Accounting');
        if (ProjectConfig::getIsQuickbooksIntegrated()) {
            $this->addSiteTitle('Bills');
        } else {
            $this->addSiteTitle('Expenses');
        }
        parent::__construct();
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }
    
    public function setShowBillsTab($showBillsTab = true) {
        $this->showBillsTab = $showBillsTab;
    }
    
    public function setShowPOBillsTab($showPOBillsTab = true) {
        $this->showPOBillsTab = $showPOBillsTab;
    }
    
    public function setShowSOBillsTab($showSOBillsTab = true) {
        $this->showSOBillsTab = $showSOBillsTab;
    }
    
    public function setShowCreditsTab($showCreditsTab = true) {
        $this->showCreditsTab = $showCreditsTab;
    }
    
    public function setShowImportedPaymentsTab($showImportedPaymentsTab = true) {
        $this->showImportedPaymentsTab = $showImportedPaymentsTab;
    }

    protected function addQuickbooksBar(){
        $qbBar = QBConnection::getQuickbooksBarView();
        if($qbBar){
            $this->addHTML($qbBar->getHTMLView());
        }
    }
    
    protected function buildView() {
        $this->addQuickbooksBar();
        $this->openViewWrap();
        if (ProjectConfig::getIsQuickbooksIntegrated()) {
            $this->addHTML('<h1>Bills</h1>');
        } else {
            $this->addHTML('<h1>Expenses</h1>');
        }
        
        $this->buildTabs();
        $this->closeViewWrap();
    }

    protected function buildTabs() {
        $tabs = array();
        if ($this->showBillsTab) {
            $billsTab = $this->buildBillsTab();
            if (!empty($billsTab)) {
                $tabs['bills'] = $billsTab;
            }
        }

        if (dbConnection::isModuleInstalled('order')) {
            if ($this->showPOBillsTab) {
                $poBillsTab = $this->buildPurchaseOrderBillsTab();
                if (!empty($poBillsTab)) {
                    $tabs['po_bills'] = $poBillsTab;
                }
            }
            if ($this->showSOBillsTab) {
                $soBillsTab = $this->buildSalesOrderBillsTab();
                if (!empty($soBillsTab)) {
                    $tabs['so_bills'] = $soBillsTab;
                }
            }
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated() && $this->showPaymentsTab) {
            $paymentsTab = $this->buildPaymentsTab();
            if (!empty($paymentsTab)) {
                $tabs['payments'] = $paymentsTab;
            }
        }
        
        if (!ProjectConfig::getIsQuickbooksIntegrated() && $this->showImportedPaymentsTab) {
            $importedPaymentsTab = $this->buildImportedPaymentsTab();
            if (!empty($importedPaymentsTab)) {
                $tabs['imported_payments'] = $importedPaymentsTab;
            }
        }
        
        if(!empty($tabs[$this->currentTabKey]) && isset($tabs[$this->currentTabKey])){
            $tabs[$this->currentTabKey]->setCurrent(true);
        } else {
            $reversedTabsArray = array_reverse($tabs);
            $curTab = array_pop($reversedTabsArray);
            $curTab->setCurrent(true);
        }
        $tabWrap = new GenericTabWrapView($tabs);
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildBillsTab() {
        $billsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'billing',
                    'action' => 'index',
                    'type' => $this->billsTypeRef,
                    'tabbed' => 1,
                    'hideAddBtn' => 1,
        ));
        $billsTab = new GenericTabView('Bills', $billsURL, true);
        return $billsTab;
    }

    protected function buildPurchaseOrderBillsTab() {
        $billsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'billing',
                    'action' => 'index',
                    'type' => 'order',
                    'tabbed' => 1,
                    'hideAddBtn' => 1,
        ));
        $billsTab = new GenericTabView('Purchase Order Bills', $billsURL, true);
        return $billsTab;
    }

    protected function buildSalesOrderBillsTab() {
        $billsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'billing',
                    'action' => 'index',
                    'type' => 'sales_order',
                    'tabbed' => 1,
                    'hideAddBtn' => 1,
        ));
        $billsTab = new GenericTabView('Sales Order Bills', $billsURL, true);
        return $billsTab;
    }

    protected function buildPaymentsTab() {
        $paymentsExpenseURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'paymentsIndex',
                    'type' => 'expense',
                    'tabbed' => 1
        ));
        $paymentsTab = new GenericTabView('Payments', $paymentsExpenseURL, true);
        return $paymentsTab;
    }
    
    protected function buildImportedPaymentsTab() {
        $importedPaymentsURL = GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'importedPaymentsIndex',
            'type'=>'expense',
            'tabbed'=>1,
        ));
        $importedPaymentsTab = new GenericTabView('Imported Payments', $importedPaymentsURL, true);
        return $importedPaymentsTab;
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
