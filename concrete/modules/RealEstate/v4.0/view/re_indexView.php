<?php

class REIndexView extends AbstractREIndexView{
    protected $mainContentClass = "relisting__main-content";
    
    public function __construct($listings, AbstractUITableView $uiTableView, AbstractREListing $sampleListing, GI_SearchView $searchView = NULL)
    {
        parent::__construct($listings, $uiTableView, $sampleListing, $searchView);
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
            $this->addHTML('<h4 class="relisting__result-title">Showing 47 results for "<b>Port Moody, British Columbia</b>"</h4>');
        $this->addHTML('</div>');
        $this->addHTML('<div class="col-xs-12 col-md-6">');
            $this->addHTML('<p class="relisting__sortby-list">');
                $this->addHTML('<span class="relisting__sortby-title">Sort by</span>');
                $this->addHTML('<a href="" class="relisting__sortby-item relisting__sortby-item_selected">Relevance</a>');
                $this->addHTML('<a href="" class="relisting__sortby-item">Price · Low to High</a>');
                $this->addHTML('<a href="" class="relisting__sortby-item">Price · High to Low</a>');
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
}
