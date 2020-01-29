<?php

/**
 * Description of AbstractREDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractREDetailView extends MainWindowView {
    
    /** @var AbstractREListing */
    protected $reListing;

    public function __construct(AbstractREListing $reListing) {
        parent::__construct();
        $this->reListing = $reListing;
        
        
        //Set list URL
        $this->setListBarURL($this->reListing->getListBarURL());
        
        //Set titles
        $title = $reListing->getViewTitle(false);
        $title .= ' - ' . $reListing->getTitle();
        $this->addSiteTitle($title);
        $this->setWindowTitle('<span class="inline_block">' . $title . '</span>');
        
        //Set primary view model
        $this->setPrimaryViewModel($this->reListing);
    }
    
    protected function addViewBodyContent(){
        $this->openViewBodyContent();
        $this->addGeneralInfoSection();
        $this->addLocationSection();
        $this->addDetailInfoSection();
        $this->addUploadedImageSection();
        $this->closeViewBodyContent();
    }
    
    protected function openViewBodyContent() {
        $this->addHTML('<div class="auto_columns halves left_label_content">');
    }
    
    protected function closeViewBodyContent() {
        $this->addHTML('</div>');
    }
    
    protected function addWindowBtns() {
        $this->addEditBtn();
        $this->addDeleteBtn();
    }
    
    protected function addEditBtn() {
        if ($this->reListing->isEditable()) {
            $editURL = $this->reListing->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
    }
    
    protected function addDeleteBtn() {
        if ($this->reListing->isDeleteable()) {
            $deleteURL = $this->reListing->getDeleteURL();
            $this->addHTML('<a href="' . $deleteURL . '" title="Delete" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon primary trash"></span></span><span class="btn_text">Delete</span></a>');
        }
    }

    protected function addGeneralInfoSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Information</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->reListing->getListingStatusTitle(), 'Listing Status');
                $this->addContentBlockWithWrap($this->reListing->getLinkedPropertyTypeTagTitle(), 'Property Type');
                $this->addContentBlockWithWrap($this->reListing->getDisplayListPrice($showMLSData), 'List Price');
                $this->addContentBlockWithWrap($this->reListing->getDisplayLotSizeSqft(), 'Lot Size');
                $this->addContentBlockWithWrap($this->reListing->getYearBuilt(), 'Year Built');
                if ($this->reListing->getSoldPrice() > 0) {
                    $this->addContentBlockWithWrap($this->reListing->getDisplaySoldPrice(), 'Sold Price');
                }
                $this->addContentBlockWithWrap($this->reListing->getDisplaySoldDate(), 'Sold Date');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addLocationSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Location</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->reListing->getAddress($showMLSData), 'Address');
                $this->addContentBlockWithWrap($this->reListing->getStreetName($showMLSData), 'Street Name');
                $this->addContentBlockWithWrap($this->reListing->getPostalCode($showMLSData), 'Postal Code');

                $this->addContentBlockWithWrap($this->reListing->getCityTitle($showMLSData), 'City');
                $this->addContentBlockWithWrap($this->reListing->getSubAreaTitle($showMLSData), 'Sub Area');
                $this->addContentBlockWithWrap($this->reListing->getAreaTitle($showMLSData), 'Area');
                $this->addContentBlockWithWrap($this->reListing->getProvince($showMLSData), 'Province');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addDetailInfoSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Public Remarks</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->reListing->getPublicRemarks($showMLSData));
            $this->addHTML('</div>');
            
            
            $virtualTourURL = $this->reListing->getProperty('virtual_tour_url');
            if (!empty($virtualTourURL)) {
                $this->addHTML('<div class="content_group">');
                    $this->addHTML('<h2 class="content_group_title">Virtual Tour URL</h2>');
                    $this->addHTML('<a href="'.$virtualTourURL.'" class="other_btn virtual_tour_btn" target="_blank">Virtual Tour URL</a>');
                $this->addHTML('</div>');
            }
        $this->addHTML('</div>');
    }
    
    protected function addUploadedImageSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Uploaded Images</h2>');
                $this->addHTML('<div class="img_thumb_list">');
                    $width = 120;
                    $height = 80;
                    $this->addHTML($this->reListing->getImagesHTML($width, $height));
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            
        $this->addHTML('</div>');
    }

}
