<?php
/**
 * Description of AbstractGroupPaymentIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentIndexView extends GI_View {
    
    protected $groupPayments;
    protected $uiTableView;
    protected $samplePayment;
    protected $searchView = NULL;
    protected $tabView = false;

    public function __construct($groupPayments, $uiTableView, AbstractPayment $samplePayment, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->groupPayments = $groupPayments;
        $this->uiTableView = $uiTableView;
        $this->samplePayment = $samplePayment;
        $this->searchView = $searchView;
        $this->addSiteTitle('Payments');
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
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
        $this->addHTML('<h1>' . $this->samplePayment->getIndexTitle() . '</h1>');
    }

    protected function addViewBody() {
        if (sizeof($this->groupPayments) > 0) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $this->addHTML('<p class="no_model_message">No payments found.</p>');
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
        if ($this->samplePayment->isAddable()) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'addPayment',
                        'type' => $this->samplePayment->getTypeRef(),
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Payment" class="custom_btn" ><span class="icon_wrap"><span class="icon primary add"></span></span><span class="btn_text">Payment</span></a>');
        }
        if ($this->searchView) {
            $this->addHTML('<span title="Search Payments" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon dark_gray search"></span></span><span class="btn_text">Search</span></span>');
        }
        $this->addHTML('</div>');
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
