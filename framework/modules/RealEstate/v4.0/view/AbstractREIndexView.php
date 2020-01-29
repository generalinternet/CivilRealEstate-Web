<?php

/**
 * Description of AbstractREIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractREIndexView extends MainWindowView {
    
    /** @var AbstractREListing[] */
    protected $listings;
    protected $uiTableView;
    protected $sampleQuestion;
    /** @var GI_SearchView */
    protected $searchView = NULL;
    protected $addAddBtn = true;

    public function __construct($listings, AbstractUITableView $uiTableView, AbstractREListing $sampleListing, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->listings = $listings;
        $this->uiTableView = $uiTableView;
        $this->sampleListing = $sampleListing;
        $this->searchView = $searchView;
        $siteTitle = $sampleListing->getViewTitle();
        $this->addSiteTitle($siteTitle);
        $this->setWindowTitle($siteTitle);
        $this->setWindowIcon('real_estate');
        $this->setUseAJAXLoading(false);
        $this->addCSS('framework/modules/RealEstate/' . MODULE_REALESTATE_VER . '/resources/real_estate.css');
        $this->addJS('framework/modules/RealEstate/' . MODULE_REALESTATE_VER . '/resources/real_estate.js');
    }
    
    /**
     * @param boolean $addAddBtn
     * @return \AbstractREIndexView
     */
    public function setAddAddBtn($addAddBtn){
        $this->addAddBtn = $addAddBtn;
        return $this;
    }

    protected function addAddBtn(){
        if ($this->sampleListing->isAddable() && $this->addAddBtn) {
            $addTitle = $this->sampleListing->getViewTitle(false);
            $addURL = $this->sampleListing->getAddURL();
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" ><span class="icon_wrap"><span class="icon primary add"></span></span><span class="btn_text">' . $addTitle . '</span></a>');
        }
    }
    
    protected function addSearchBtn(){
        $title = $this->sampleListing->getViewTitle();
        if($this->searchView){
            if ($this->searchView->getUseShadowBox()) {
                $searchURL = $this->searchView->getShadowBoxURL();
                $this->addHTML('<a href="' . $searchURL . '" title="Search ' . $title . '" class="custom_btn open_modal_form" data-modal-class="medium_sized shadow_box_modal"><span class="icon_wrap"><span class="icon primary search"></span></span><span class="btn_text">Search</span></a>');
            } else {
                $searchBtnClass = 'open';
                $queryId = $this->searchView->getQueryId();
                if(!empty($queryId)){
                    $searchBtnClass = '';
                }

                $this->addHTML('<span title="Search ' . $title . '" class="custom_btn open_search_box ' . $searchBtnClass . '" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon primary search"></span></span><span class="btn_text">Search</span></span>');
            }

        }
    }
    
    protected function addWindowBtns(){
        $this->addSearchBtn();
        $this->addAddBtn();
    }
    
    protected function addViewBodyContent() {
        $this->addSearchBox();
        $this->addTable();
    }
    
    protected function addSearchBox(){
        if($this->searchView && !$this->searchView->getUseShadowBox()){
            $this->addHTML($this->searchView->getHTMLView());
        }
    }
    
    protected function addTable($class = ''){
        $this->addHTML('<div class="list_table_wrap '.$class.'">');
        $this->addHTML($this->uiTableView->getHTMLView());
        $this->addHTML('</div>');
    }
}
