<?php
/**
 * Description of AbstractMLSListingDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingDetailView extends MainWindowView {
    
    /** @var AbstractMLSListing */
    protected $listing;

    public function __construct(AbstractMLSListing $listing) {
        parent::__construct();
        $this->listing = $listing;
        
        //Set titles
        $title = $listing->getViewTitle(false);
        $title .= ' - ' . $listing->getTitle();
        $this->addSiteTitle($title);
        $this->setWindowTitle('<span class="inline_block">' . $title . '</span>');
        
        //Set primary view model
        $this->setPrimaryViewModel($this->listing);
    }
    
    protected function addViewBodyContent(){
        $this->openViewBodyContent();
        $this->addUploadedImageSection();
        $this->addGeneralInfoSection();
        $this->addLocationSection();
        $this->addDetailInfoSection();
        $this->closeViewBodyContent();//left_label_content
    }
    
    protected function openViewBodyContent() {
        $this->addHTML('<div class="re_listing_wrap">');
    }
    
    protected function closeViewBodyContent() {
        $this->addHTML('</div>');
    }
    
    protected function addWindowBtns() {
        $this->addEditBtn();
    }
    
    protected function addEditBtn() {
//        if ($this->listing->isEditable()) {
//            $editURL = $this->listing->getEditURL();
//            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
//        }
    }

    protected function addGeneralInfoSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Information</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->listing->getListingStatusTitle(), 'Listing Status');
                $this->addContentBlockWithWrap($this->listing->getLinkedPropertyTypeTagTitle(), 'Property Type');
                $this->addContentBlockWithWrap($this->listing->getDisplayListPrice($showMLSData), 'List Price');
                $this->addContentBlockWithWrap($this->listing->getDisplayLotSizeSqft(), 'Lot Size');
                $this->addContentBlockWithWrap($this->listing->getYearBuilt(), 'Year Built');
                if ($this->listing->getSoldPrice() > 0) {
                    $this->addContentBlockWithWrap($this->listing->getDisplaySoldPrice(), 'Sold Price');
                }
                $this->addContentBlockWithWrap($this->listing->getDisplaySoldDate(), 'Sold Date');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addLocationSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Location</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->listing->getAddress($showMLSData), 'Address');
                $this->addContentBlockWithWrap($this->listing->getStreetName($showMLSData), 'Street Name');
                $this->addContentBlockWithWrap($this->listing->getPostalCode($showMLSData), 'Postal Code');

                $this->addContentBlockWithWrap($this->listing->getCityTitle($showMLSData), 'City');
                $this->addContentBlockWithWrap($this->listing->getSubAreaTitle($showMLSData), 'Sub Area');
                $this->addContentBlockWithWrap($this->listing->getAreaTitle($showMLSData), 'Area');
                $this->addContentBlockWithWrap($this->listing->getProvince($showMLSData), 'Province');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addDetailInfoSection(){
        $this->addHTML('<div class="auto_column">');
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Public Remarks</h2>');
                $showMLSData = true;
                $this->addContentBlockWithWrap($this->listing->getPublicRemarks($showMLSData));
            $this->addHTML('</div>');
            
            
            $virtualTourURL = $this->listing->getProperty('virtual_tour_url');
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
                    $this->addHTML($this->listing->getImagesHTML($width, $height));
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            
        $this->addHTML('</div>');
    }

}
