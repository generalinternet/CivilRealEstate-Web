<?php

abstract class AbstractMLSListingItemView extends GI_View {
    
    /**
     * @var AbstractMLSListing 
     */
    protected $mlsListing;
    
    public function __construct($mlsListing) {
        parent::__construct();
        $this->mlsListing = $mlsListing;
    }
    
    public function buildView() {
        $listingURL = $this->mlsListing->getViewURL();
        $this->addHTML('<div class="mls_listing_block">');
            $this->addHTML('<h3><a href="' . $listingURL . '">' . $this->mlsListing->getTitle() . '</a></h3>');
            $image = $this->mlsListing->getCoverImage();
            if($image){
                $this->addHTML('<img src="' . $image->getImageURL() . '" height="100"/><br/>');
            }
            $propTypeTitle = $this->mlsListing->getDwellingTypeTitle();
            if(empty($propTypeTitle)){
                $propTypeTitle = $this->mlsListing->getComTypeTitle();
            }
            $this->addHTML('MLS Number: ' . $this->mlsListing->getMLSNumber() . '<br/>');
            $this->addHTML('Type: ' . $this->mlsListing->getTypeTitle() . '<br/>');
            $this->addHTML('City: ' . $this->mlsListing->getCityTitle() . '<br/>');
            $this->addHTML('Area: ' . $this->mlsListing->getAreaTitle() . '<br/>');
            $this->addHTML('SubArea: ' . $this->mlsListing->getSubAreaTitle() . '<br/>');
            $this->addHTML('Property Type: ' . $propTypeTitle . '<br/>');
            $realtor = $this->mlsListing->getRealtor();
            if($realtor){
                $this->addHTML('Realtor: ' . $realtor->getName() . '<br/>');
            }
            $firm = $this->mlsListing->getFirm();
            if($firm){
                $this->addHTML('Firm: ' . $firm->getName() . '<br/>');
            }
            $this->addHTML('Status: ' . $this->mlsListing->getListingStatusTitle() . '<br/>');
            $this->addHTML('<hr/>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
