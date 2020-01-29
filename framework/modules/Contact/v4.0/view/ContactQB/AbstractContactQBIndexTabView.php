<?php

/**
 * Description of AbstractContactQBIndexTabView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactQBIndexTabView extends MainWindowView {

    protected $currentTabKey = 'supplier';


    public function __construct() {
        $this->addSiteTitle('Quickbooks Contacts');
        $this->setWindowTitle('Quickbooks Contacts');
        parent::__construct();
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }
    
    protected function addQuickbooksBar(){
        $qbBar = QBConnection::getQuickbooksBarView();
        if($qbBar){
            $this->addHTML($qbBar->getHTMLView());
        }
    }
    
    public function buildView() {
        $this->addQuickbooksBar();
        parent::buildView();
    }
    
    protected function addViewBodyContent(){
        $this->addHTML('<h1>Unlinked Quickbooks Contacts</h1>');
        $this->addHTML('<p>Suppliers and Customers created in QuickBooks must be imported and linked to (an) existing contact(s), or used to create (a) new contact(s).</p>');
        $this->addHTML("<p>Use the 'Import New' function to import any Suppliers or Customers from QuickBooks that aren't already linked to a contact in the system. For each imported Supplier and Customer, use the 'Link/Create Contact(s)' function to associate it with a contact in the system. Once a Supplier or Customer has been associated with a contact, it will be automatically removed from this list, and its information can be viewed and updated directly via the contact.</p>");
        $this->buildTabs();
    }

    protected function buildTabs() {
        $tabs = array();

        $suppliersTab = $this->buildSuppliersTab();
        if (!empty($suppliersTab)) {
            $tabs['supplier'] = $suppliersTab;
        }

        $customersTab = $this->buildCustomersTab();
        if (!empty($customersTab)) {
            $tabs['customer'] = $customersTab;
        }

        if (!empty($tabs[$this->currentTabKey]) && isset($tabs[$this->currentTabKey])) {
            $tabs[$this->currentTabKey]->setCurrent(true);
        } else {
            $reversedTabsArray = array_reverse($tabs);
            $curTab = array_pop($reversedTabsArray);
            $curTab->setCurrent(true);
        }
        $tabWrap = new GenericTabWrapView($tabs);
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildSuppliersTab() {
        $suppliersURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'qbImportIndexContent',
                    'type' => 'supplier',
                    'tabbed' => 1
        ));
        $billsTab = new GenericTabView('Suppliers', $suppliersURL, true);
        return $billsTab;
    }

    protected function buildCustomersTab() {
        $customersURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'qbImportIndexContent',
                    'type' => 'customer',
                    'tabbed' => 1
        ));
        $billsTab = new GenericTabView('Customers', $customersURL, true);
        return $billsTab;
    }
}