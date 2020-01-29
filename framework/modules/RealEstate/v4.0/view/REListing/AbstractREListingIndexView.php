<?php

/**
 * Description of AbstractREListingIndexView
 * Front face page: show Real Estate listings and MLS listings list
 *                  show Real Estate listings first
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    1.0.0
 */

abstract class AbstractREListingIndexView extends GI_View {
    
    /**
     * @var AbstractMLSListing[]
     */
    protected $mlsListings;
    /**
     * @var GI_PageBarView
     */
    protected $pageBar = NULL;
    protected $sampleListing;
    protected $searchView = NULL;

    public function __construct($mlsListings, GI_PageBarView $pageBar, AbstractMLSListing $sampleListing, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->mlsListings = $mlsListings;
        $this->pageBar = $pageBar;
        $this->sampleListing = $sampleListing;
        $this->searchView = $searchView;
        $this->addSiteTitle('MLS Listings');
        $typeTitle = $this->sampleListing->getViewTitle();
        if(!empty($this->sampleListing->getTypeRef()) && $typeTitle != 'Listing'){
            $this->addSiteTitle($typeTitle);
        }
    }

    protected function buildView() {
        $this->addHTML('<div class="content_padding">');
        
        $title = $this->sampleListing->getViewTitle();
        $typeRef = $this->sampleListing->getTypeRef();
        
        if($this->searchView){
            $this->addHTML($this->searchView->getHTMLView());
        }
        
        $this->addHTML('<div class="right_btns">')
                ->addHTML('<span title="Search ' . $title . '" class="custom_btn gray open_search_box" data-box="mls_search_box" ><span class="icon_wrap"><span class="icon search"></span></span> <span class="btn_text">Search</span></span>');
        $this->addHTML('</div>');
        
        if(!empty($typeRef)){
            $this->addHTML('<h1>Listings - ' . $title . '</h1>');
        } else {
            $this->addHTML('<h1>' . $title . '</h1>');
        }
        
        if (count($this->mlsListings) > 0) {
            if($this->pageBar){
                $this->addHTML($this->pageBar->getHTMLView());
            }
            foreach($this->mlsListings as $mlsListing){
                $listingItemView = $mlsListing->getItemView();
                $this->addHTML($listingItemView->getHTMLView());
            }
        } else {
            $this->addHTML('<p>No ' . strtolower($this->sampleListing->getViewTitle()) . ' found.</p>');
        }

        $this->addHTML('</div>');
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}
