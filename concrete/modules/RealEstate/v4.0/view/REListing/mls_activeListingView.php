<?php

class MLSActiveListingView extends AbstractMLSIndexView {
    
    protected $form = NULL;
    protected $reListings;
    protected $searchContent;
    protected $queryId = NULL;
    
    public function __construct($mlsListings, $reListings, \GI_PageBarView $pageBar, \iMLSListing $sampleListing = null, \GI_SearchView $searchView = NULL) {
        parent::__construct($mlsListings, $pageBar, $sampleListing, $searchView);
        $this->reListings = $reListings;

        $this->addCSS('resources/css/search.css');
        $this->addCSS('resources/css/pagination.css');
        $this->addCSS('resources/css/open_house.css');
        $this->addCSS('resources/css/google_map.css');
        $this->addJS('resources/js/google_map.js');
        $this->addFinalContent('<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDIk7n8goeIhOiJivU42YCZ9M2xdxC5OSQ&callback=initActiveMap" async defer></script>');
        
        //Add metatag
        $this->addSiteTitle('Active Listings');
        $this->setDescription('Search Active Listings in the Tri-Cities(Coquitlam, Port Coquitlam, Port Moody, including Anmore and Belcarra), North Burnaby and Pitt Meadows/Maple Ridge areas of Greater Vancouver\'s Lower Mainland');

    }
    
    public function setSearchForm(GI_Form $form){
        $this->form = $form;
        $this->buildSearchForm($this->searchArray);
    }
    
    public function setMLSListings($mlsListings){
        $this->mlsListings = $mlsListings;
    }
    
    public function setPageBar($pageBar){
        $this->pageBar = $pageBar;
    }
    
    public function setSearchArray($searchArray){
        $this->searchArray = $searchArray;
    }
    
    public function setQueryId($queryId){
        $this->queryId = $queryId;
        return $this;
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function buildView(){
        $this->addContent('<div class="mobile_banner">
            
                <h1>Active Listings</h1>

                </div>');
        
        $this->addContent('<div class="realty_wrapper">');

        
        
        $this->addContent('<div id="realty_content" class="container">
                            <table class="realty_content">'); 
        
        foreach($this->reListings as $listing){
            $itemView = new MLSActiveListingItemView($listing, $this->queryId);
            $this->addHTML($itemView->getHTMLView());
        }
        
        foreach($this->mlsListings as $listing){
            $itemView = new MLSActiveListingItemView($listing, $this->queryId);
            $this->addHTML($itemView->getHTMLView());
        }
        $this->addContent('</table>');
        $this->addContent('<div class="pagination_wrapper">');          

        $this->addContent($this->pageBar->getHTMLView());

        $this->addContent('</div>


                <div class="container mls_statement justified">
                    <img style="margin:auto;" src="resources/css/images/mls_logo_large.png" alt="MLS logo">
                    <p>This representation is based in whole or in part on data generated by the Chilliwack & District Real Estate Board, Fraser Valley Real Estate Board or Real Estate Board of Greater Vancouver which assumes no responsibility for its accuracy</p>
                </div>


        </div>');
        
        $this->addContent('
                <div id="map_wrap">
                    <div id="map">
                    </div>
                </div>
            ');
    }
    
    public function getHTMLView($buildView = true){
        if($buildView){
            $this->buildView();
        }
        else{
            $this->addContent($this->form->getForm());
        }
        return $this->html;
    }
    
}
