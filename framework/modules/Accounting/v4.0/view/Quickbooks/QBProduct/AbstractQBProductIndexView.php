<?php
/**
 * Description of AbstractQBProductIndexView
 
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractQBProductIndexView extends GI_View {

    protected $qbProducts;
    protected $uiTableView;
    protected $sampleQBProduct;
    protected $searchView = NULL;
    protected $tabView = false;
    protected $addWrap = true;
    protected $addTitle = true;

    public function __construct($qbProducts, $uiTableView, AbstractQBProduct $sampleQBProduct, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->qbProducts = $qbProducts;
        $this->uiTableView = $uiTableView;
        $this->sampleQBProduct = $sampleQBProduct;
        $this->searchView = $searchView;
        $this->addSiteTitle($this->sampleQBProduct->getViewTitle());
    }
    /**
     * @param Boolean $isTabView
     */
    public function setTabView($isTabView) {
        $this->tabView = $isTabView;
    }
    
    /**
     * @param boolean $addWrap
     * @return \AbstractPriceSheetIndexView
     */
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }
    
    
    /**
     * @param boolean $addTitle
     * @return \AbstractPriceSheetIndexView
     */
    public function setAddTitle($addTitle){
        $this->addTitle = $addTitle;
        return $this;
    }
    


    protected function openViewWrap() {
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }

    protected function closeViewWrap() {
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function addSearchBtn(){
        $title = $this->sampleQBProduct->getViewTitle();
        if($this->searchView){
            if ($this->searchView->getUseShadowBox()) {
                $searchURL = $this->searchView->getShadowBoxURL();
                $this->addHTML('<a href="' . $searchURL . '" title="Search  ' . $title . '" class="custom_btn gray open_modal_form" data-modal-class="large_sized shadow_box_modal">' . GI_StringUtils::getIcon('search', true, 'white') . '<span class="btn_text">Search</span></a>');
            } else {
                $searchBtnClass = 'open';
                $queryId = $this->searchView->getQueryId();
                if(!empty($queryId)){
                    $searchBtnClass = '';
                }
                
                $this->addHTML('<span title="Search ' . $title . '" class="custom_btn gray open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" >' . GI_StringUtils::getIcon('search', true, 'white') . '<span class="btn_text">Search</span></span>');
            }
        }
    }
    
    protected function addImportButton() {
        $url = GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'importQBProducts',
        ));
        $this->addHTML('<a href="' . $url . '" title="Import List of Products/Services from Quickbooks" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('import') . '<span class="btn_text">Import</span></a>');
    }
    
    protected function addBtns(){
        $rightBtnClass = '';
        if(!$this->addTitle){
            $rightBtnClass = 'absolute';
        }
        $this->addHTML('<div class="right_btns ' . $rightBtnClass . '">');
        $this->addSearchBtn();
        $this->addImportButton();
        $this->addHTML('</div>');
    }
    
    protected function addHeaderTitle(){
        if ($this->tabView) {
            $this->addHTML('<h2>Products/Services</h2>');
        } elseif($this->addTitle){
            $this->addMainTitle('Quickbooks Products/Services');
        }
    }
    
    protected function buildView() {
        if (!$this->tabView) {
            $this->openViewWrap();
        }
        
        if($this->searchView && !$this->searchView->getUseShadowBox()){
            $this->addHTML($this->searchView->getHTMLView());
        }
        
        $this->addBtns();
        
        $this->addHeaderTitle();
        
        if ($this->uiTableView) {
            $this->addHTML($this->uiTableView->getHTMLView());
        } else {
            $this->addHTML('<p>No ' . strtolower($this->sampleQBProduct->getViewTitle()) . ' found.</p>');
        }

        if (!$this->tabView) {
            $this->closeViewWrap();
        }
    }

}
