<?php

/**
 * Description of AbstractREListingDetailView
 * Front face page: show Real Estate listings and MLS listings details
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    1.0.0
 */

abstract class AbstractREListingDetailView extends GI_View {
    
    /**
     * @var AbstractMLSListing 
     */
    protected $mlsListing;
    
    public function __construct($mlsListing) {
        parent::__construct();
        $this->mlsListing = $mlsListing;
        $this->addSiteTitle('MLS Listing');
        $typeTitle = $this->mlsListing->getViewTitle();
        if($typeTitle != 'Listing'){
            $this->addSiteTitle($typeTitle);
        }
        $this->addSiteTitle($this->mlsListing->getMLSNumber());
    }
    
    public function buildView() {
        $this->addHTML('<div class="content_padding">');
            $this->addHTML('<h1>' . $this->mlsListing->getTitle() . '</h1>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
