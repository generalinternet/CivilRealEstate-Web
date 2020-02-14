<?php

/**
 * Description of AbstractPaymentAccountIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentAccountIndexView extends GI_View {

    protected $paymentAccounts;
    protected $uiTableView;
    protected $samplePaymentAccount;
    protected $searchView = NULL;
    protected $tabView = false;

    public function __construct($paymentAccounts, $uiTableView, AbstractPaymentAccount $samplePaymentAccount, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->paymentAccounts = $paymentAccounts;
        $this->uiTableView = $uiTableView;
        $this->samplePaymentAccount = $samplePaymentAccount;
        $this->searchView = $searchView;
        $this->addSiteTitle('Accounts');
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    /**
     * @param Boolean $isTabView
     */
    public function setTabView($isTabView) {
        $this->tabView = $isTabView;
    }

    protected function buildView() {
        if (!$this->tabView) {
            $this->openViewWrap();
        }
        $this->addViewHeader();
        $this->addViewBody();
        $this->addViewFooter();
        if (!$this->tabView) {
            $this->closeViewWrap();
        }
    }

    protected function addViewHeader() {
        $this->addButtonsAndSearchView();
        $this->addHTML('<h1>' . $this->samplePaymentAccount->getIndexTitle() . '</h1>');
    }

    protected function addViewBody() {
        if (sizeof($this->paymentAccounts) > 0) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $this->addHTML('<p class="no_model_message">No accounts found.</p>');
        }
    }

    protected function addViewFooter() {
        
    }

    protected function addButtonsAndSearchView() {
        $searchBtnClass = 'open';
        if ($this->searchView) {
            $this->addHTML($this->searchView->getHTMLView());
            $queryId = $this->searchView->getQueryId();
            if (!empty($queryId)) {
                $searchBtnClass = '';
            }
        }
        $this->addButtons($searchBtnClass);
    }

    protected function addButtons($searchBtnClass = 'open') {
        $this->addHTML('<div class="right_btns">');
        if ($this->samplePaymentAccount->isAddable()) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'addPaymentAccount',
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Account" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">Account</span></a>');
        }
        if ($this->searchView) {
            $this->addHTML('<span title="Search Accounts" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
        }
        $this->addHTML('</div>');
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
