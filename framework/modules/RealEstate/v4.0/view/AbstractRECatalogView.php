<?php
/**
 * Description of AbstractRECatalogView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractRECatalogView extends GI_View {
    
    /** @var AbstractREListing or AbstractMLSListing */
    protected $listing = NULL;
    
    public function __construct($listing) {
        parent::__construct();
        $this->listing = $listing;
    }
    
    protected function openListingWrap(){
        $this->addHTML('<div class="re_listing_wrap">');
        return $this;
    }
    
    protected function closeListingWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addListingContent(){
        $this->addFeaturedImage();
        $this->addListingPrice();
        $this->addListingTitle();
        $this->addSummary();
        $this->addViewCTA();
    }
    
    protected function addFeaturedImage(){
        $this->addHTML($this->listing->getCoverImageHTML());
    }
    
    protected function addListingPrice(){
        $this->addHTML($this->listing->getDisplayListPrice());
    }
    
    protected function addListingTitle(){
        $this->addHTML($this->listing->getAddress());
    }
    
    protected function addSummary(){
        $this->addHTML($this->listing->getDisplayPublicRemarks());
    }
    
    protected function addViewCTA(){
        $url = $this->listing->getViewURL();
        $this->addHTML('<a href="' . $url . '" title="View Complete Listing">View Complete Listing</a>');
    }
    
    protected function buildView(){
        $this->openListingWrap();
            $this->addListingContent();
        $this->closeListingWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        return parent::beforeReturningView();
    }

}
