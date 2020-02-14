<?php
/**
 * Description of AbstractAccountsReceivableIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccountsReceivableIndexView extends GI_View {

    protected $currentTabKey = 'invoices';
    protected $invoicesTypeRef = 'inv';

    public function __construct() {
        $this->addSiteTitle('Accounting');
        $this->addSiteTitle('Sales');
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
    
    protected function buildView() {
        $this->addQuickbooksBar();
        $this->openViewWrap();
        $this->addHTML('<h1>Sales</h1>');
        $this->buildTabs();
        $this->closeViewWrap();
    }

    protected function buildTabs() {
        $tabs = array();
        $invoicesTab = $this->buildInvoicesTab();
        if (!empty($invoicesTab)) {
            $tabs['invoices'] = $invoicesTab;
        }
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            $statementsTab = $this->buildStatementsTab();
            if (!empty($statementsTab)) {
                $tabs['statements'] = $statementsTab;
            }
            $paymentsTab = $this->buildPaymentsTab();
            if (!empty($paymentsTab)) {
                $tabs['payments'] = $paymentsTab;
            }
            $creditsTab = $this->buildCreditsTab();
            if (!empty($creditsTab)) {
                $tabs['credits'] = $creditsTab;
            }
            $quotesTab = $this->buildQuotesTab();
            if (!empty($quotesTab)) {
                $tabs['quotes'] = $quotesTab;
            }
        }

        $tabs[$this->currentTabKey]->setCurrent(true);
        $tabWrap = new GenericTabWrapView($tabs);
        $this->addHTML($tabWrap->getHTMLView());
    }

    protected function buildInvoicesTab() {
        if (Permission::verifyByRef('view_invoices_index')) {
            $invoicesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'invoice',
                        'action' => 'index',
                        'type' => $this->invoicesTypeRef,
                        'tabbed' => 1
            ));
            $invoicesTab = new GenericTabView('Invoices', $invoicesURL, true);
            return $invoicesTab;
        }
        return NULL;
    }

    protected function buildQuotesTab() {
        if (Permission::verifyByRef('view_invoice_quotes_index')) {
            $quotesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'invoice',
                        'action' => 'index',
                        'type' => 'inv_quote',
                        'tabbed' => 1
            ));
            $quotesTab = new GenericTabView('Quotes', $quotesURL, true);
            return $quotesTab;
        }
        return NULL;
    }

    protected function buildPaymentsTab() {
        if (Permission::verifyByRef('view_invoice_payments_index')) {
            $paymentsIncomeURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'paymentsIndex',
                        'type' => 'income',
                        'tabbed' => 1
            ));
            $paymentsTab = new GenericTabView('Payments', $paymentsIncomeURL, true);
            return $paymentsTab;
        }
        return NULL;
    }

    protected function buildCreditsTab() {
        if (Permission::verifyByRef('view_credits_index')) {
            $creditsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'creditsIndex',
                        'type' => 'income',
                        'tabbed' => 1
            ));
            $creditsTab = new GenericTabView('Credits', $creditsURL, true);
            return $creditsTab;
        }
        return NULL;
    }

    protected function buildStatementsTab() {
        if (Permission::verifyByRef('view_invoice_statements_index')) {
            $statementsURL = GI_URLUtils::buildURL(array(
                        'controller' => 'invoice',
                        'action' => 'statementIndex',
                        'tabbed' => 1
            ));
            $statementsTab = new GenericTabView('Statements', $statementsURL, true);
            return $statementsTab;
        }
        return NULL;
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
