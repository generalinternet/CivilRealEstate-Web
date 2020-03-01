<?php

class REIndexView extends AbstractREIndexView{
    protected $mainContentClass = "relisting__main-content";
    protected $pageBar;
    
    public function __construct($listings, REUICatalogView $uiTableView, AbstractREListing $sampleListing, GI_View $searchView = NULL, GI_PageBarView $pageBar)
    {
        $this->listings = $listings;
        $this->uiTableView = $uiTableView;
        $this->sampleListing = $sampleListing;
        $this->searchView = $searchView;
        $this->pageBar = $pageBar;
        $siteTitle = $sampleListing->getViewTitle();
        $this->addSiteTitle($siteTitle);
        $this->setWindowTitle($siteTitle);
        $this->setWindowIcon('real_estate');
        $this->setUseAJAXLoading(false);
        $this->addCSS('framework/modules/RealEstate/' . MODULE_REALESTATE_VER . '/resources/real_estate.css');
        $this->addJS('framework/modules/RealEstate/' . MODULE_REALESTATE_VER . '/resources/real_estate.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_modal.js');
    }

    protected function buildViewHeader(){
        return $this;
    }

    protected $isOpenHouse = false;
    public function setIsOpenHouse(bool $isOpenHouse){
        $this->isOpenHouse = $isOpenHouse;
    }

    protected function addViewBodyContent() {
        $this->addHTML('<section class="section section_type_relisting">');
            $this->addHTML('<div class="container">');

                $this->addHTML('<div class="row">');
                $this->addTopTitleBar();
                $this->addHTML('</div>');
                
                $this->addHTML('<div class="row">');
                
                $tableCol = "col-md-12";
                if(!$this->isOpenHouse){
                    $this->addHTML('<div class="col-xs-12 col-md-4">');
                    $this->addSearchBox();
                    $this->addHTML('</div>');

                    $tableCol = "col-md-8";
                }

                    $this->addHTML('<div class="col-xs-12 '.$tableCol.'">');
                    $this->addTable();
                    $this->addHTML('</div>');
                $this->addHTML('</div>');

            $this->addHTML('</div>');
        $this->addHTML('</section>');

        $this->addLinkSection();
    }

    protected function addTable($class = ''){
        $this->addHTML('<div class="list_table_wrap '.$class.' relisting__list-wrap">');
        $this->uiTableView->setLoadMore(false);
        $this->uiTableView->setLoadPrev(false);
        $this->addHTML($this->uiTableView->getHTMLView());
        $this->addHTML($this->pageBar->getHTMLView());
        $this->addHTML('</div>');
    }

    protected function addTopTitleBar(){
        $this->addHTML('<div class="col-xs-12 col-md-6">');
            $keyword = filter_input(INPUT_POST, 'keyword');
            if(!empty($keyword)){
                $this->addHTML('<h4 class="relisting__result-title">Showing results for "<b>'.$keyword.'</b>"</h4>');
            }
        $this->addHTML('</div>');
        $this->addHTML('<div class="col-xs-12 col-md-6">');
            $this->addHTML('<div class="relisting__sortby-list">');
                $sortArr = array(
                    'relevance' => 'Relevance',
                    'price_low_to_high' => 'Price · Low to High',
                    'price_high_to_low' => 'Price · High to Low',
                );
                
                $sortByVal = REListingFactory::getSearchValue('sort_by');
                if(empty($sortByVal)){
                    $sortByVal = 'relevance';
                }
                $sortByForm = new GI_Form('sort_by_form');
                $sortByForm->addField('sort_by', 'radio', array(
                    'class' => 'form__input form__input_type_text',
                    'options' => $sortArr,
                    'value' => $sortByVal,
                ));

                $this->addHTML($sortByForm->getForm());
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addLinkSection(){
        $this->addHTML('<section class="section section_type_linking">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-8 col-md-push-2">');
                        $this->addHTML('<h3 class="linking__title">Looking to List or Lease your property?</h3>');
                        $this->addHTML('<div class="linking__button-wrap"><a href="" class="button button_theme_primary">Post Your Property</a></div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }

    protected function addSearchBox()
    {
        $this->addHTML($this->searchView->getHTMLView());
    }
}
