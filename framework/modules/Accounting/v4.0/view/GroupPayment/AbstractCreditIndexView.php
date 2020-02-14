<?php
/**
 * Description of AbstractAccountingCreditIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccountingCreditIndexView extends GI_View {

    protected $groupPayments;
    protected $uiTableView;
    protected $sampleCredit;
    protected $searchView = NULL;
    protected $tabView = false;

    public function __construct($groupPayments, $uiTableView, AbstractPayment $sampleCredit, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->groupPayments = $groupPayments;
        $this->uiTableView = $uiTableView;
        $this->sampleCredit = $sampleCredit;
        $this->searchView = $searchView;
        $this->addSiteTitle('Credits');
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
        $searchBtnClass = 'open';
        if ($this->searchView) {
            $this->addHTML($this->searchView->getHTMLView());
            $queryId = $this->searchView->getQueryId();
            if (!empty($queryId)) {
                $searchBtnClass = '';
            }
        }
        $this->addHTML('<div class="right_btns">');
        if (Permission::verifyByRef('add_credits')) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'addPayment',
                        'type' => $this->sampleCredit->getTypeRef(),
                        'gp' => 'credit',
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Credit" class="custom_btn" ><span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">Credit</span></a>');
        }
        if ($this->searchView) {
            $this->addHTML('<span title="Search Credits" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
        }
        $this->addHTML('</div>');
        $this->addHTML('<h1>' . $this->sampleCredit->getCreditIndexTitle() . '</h1>');

        if (sizeof($this->groupPayments) > 0) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $this->addHTML('<p class="no_model_message">No credits found.</p>');
        }
        if (!$this->tabView) {
            $this->closeViewWrap();
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
