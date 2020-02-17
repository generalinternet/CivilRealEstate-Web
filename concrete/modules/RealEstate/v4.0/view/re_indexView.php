<?php

class REIndexView extends AbstractREIndexView{
    protected $mainContentClass = "relisting__main-content";
    
    public function __construct($listings, AbstractUITableView $uiTableView, AbstractREListing $sampleListing, GI_View $searchView = NULL)
    {
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
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_modal.js');
    }

    protected function buildViewHeader(){
        return $this;
    }

    protected function addViewBodyContent() {
        $this->addHTML('<section class="section section_type_relisting">');
            $this->addHTML('<div class="container">');

                $this->addHTML('<div class="row">');
                $this->addTopTitleBar();
                $this->addHTML('</div>');
                
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-4">');
                    $this->addSearchBox();
                    $this->addHTML('</div>');

                    $this->addHTML('<div class="col-xs-12 col-md-8">');
                    $this->addTable();
                    $this->addHTML('</div>');
                $this->addHTML('</div>');

            $this->addHTML('</div>');
        $this->addHTML('</section>');

        $this->addLinkSection();
    }

    protected function addTable($class = ''){
        $this->addHTML('<div class="list_table_wrap '.$class.' relisting__list-wrap">');
        $this->addHTML($this->uiTableView->getHTMLView());
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
            $this->addHTML('<p class="relisting__sortby-list">');
                $this->addHTML('<span class="relisting__sortby-title">Sort by</span>');
                $sortArr = array(
                    'low_to_high' => 'Price · Low to High',
                    'high_to_low' => 'Price · High to Low',
                );
                $sortBy = GI_URLUtils::getAttribute('sort');
                $controller = GI_URLUtils::getController();
                $action = GI_URLUtils::getAction();
                foreach($sortArr as $ref => $title){
                    $selected = '';
                    if($ref === $sortBy){
                        $selected = 'relisting__sortby-item_selected';
                    }
                    $url = GI_URLUtils::buildURL(array(
                        'controller' => $controller,
                        'action' => $action,
                        'sort' => $ref
                    ));
                    $this->addHTML('<a href="'.$url.'" class="relisting__sortby-item '.$selected.'">'.$title.'</a>');
                }
            $this->addHTML('</p>');
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
