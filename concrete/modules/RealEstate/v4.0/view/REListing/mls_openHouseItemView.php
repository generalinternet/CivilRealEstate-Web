<?php

class MLSOpenHouseItemView extends AbstractMLSListingItemView {
    
    protected $queryId;
    
    public function __construct($mlsListing, $queryId = null) {
        parent::__construct($mlsListing);
        $this->queryId = $queryId;
        
        $open_houses = $mlsListing->getOpenHouses();
        if (!is_null($mlsListing->getAddress())) {
            $this->addSiteTitle('Open House-'.$mlsListing->getAddress());
            if(!empty($open_houses)){
                $this->setDescription('Open House:'.$mlsListing->getAddress()
                        .', Date:'.date('D M j', strtotime($open_houses[0]->getProperty('oh_start_date_time')))
                        .' time:'.date('g:i A', strtotime($open_houses[0]->getProperty('oh_start_time'))) . ' - ' . date('g:i A', strtotime($open_houses[0]->getProperty('oh_end_time')))
                        .', Price:'.$this->checkValue(number_format($mlsListing->getProperty('list_price')))
                        .', Beds:'.$this->checkValue($mlsListing->getProperty('total_bedrooms'))
                        .', Baths:'.$this->checkValue($mlsListing->getProperty('total_baths'))
                        .', Sqft:'.$this->checkValue($mlsListing->getPropertySize())
                        );
            }
        }
    }
    
    public function buildView(){
        $listing = $this->mlsListing;
        $detailUrl = $listing->getViewURL();
        $this->addHTML('<div class="ui_list staus_'.$listing->getListingStatusRef().'">');
            $this->addHTML('<div class="row flex-row sm-flex-disable">');
                $this->addHTML('<div class="col-sm-12 col-md-4 ui_list_col media-col">');
                    $this->addHTML('<a href="' . $detailUrl . '">');
                        $this->addImageViews();
                    $this->addHTML('</a>');
                $this->addHTML('</div>');
                
                $this->addHTML('<div class="col-sm-12 col-md-4 col-md-push-4 ui_list_col openhouse-col">');
                    $this->addOpenhouseDetails();
                $this->addHTML('</div>');
                
                $this->addHTML('<div class="col-sm-12 col-md-4 col-md-pull-4 ui_list_col detail-col">');
                    $this->addListingDetails();
                $this->addHTML('</div>');

                
            $this->addHTML('</div>');
        $this->addHTML('</div><!--.ui_list-->');
    }
        
    protected function addListingDetails(){
        $listing = $this->mlsListing;
        $detailUrl = $listing->getViewURL();
        $this->addHTML('<div class="detail-box flex-cell">');
            $this->addHTML('<h4 class="title"><a href="' . $detailUrl . '">'.$listing->getAddressWithOptions(
                            array('city' => true)
                            ).'</a><h4>');
            $this->addHTML('<ul class="info">');
                $this->addHTML('<li><span class="label">Price</span><span class="value">'.$listing->getDisplayPrice().'</span></li>');
                $this->addHTML('<li><span class="label">City</span><span class="value">'.$listing->getCityTitle().'</span></li>');
                $totalBedrooms = $this->checkValue($listing->getProperty('total_bedrooms'));
                if (!empty($totalBedrooms)) {
                    $this->addContent('<li><span class="label">Bedrooms</span><span class="value">'.$totalBedrooms.'</span></li>');
                }
                $totalBaths = $this->checkValue($listing->getProperty('total_baths'));
                if (!empty($totalBaths)) {
                    $this->addContent('<li><span class="label">Bathrooms</span><span class="value">'.$totalBaths.'</span></li>');
                }
                $propertySize = $this->checkValue($listing->getPropertySize());
                if (!empty($propertySize)) {
                    $this->addContent('<li><span class="label">Square Feet</span><span class="value">'.$propertySize.'</span></li>');
                }
                $year = $this->checkValue($listing->getProperty('year'));
                if (!empty($year)) {
                    $this->addContent('<li><span class="label">Year Built</span><span class="value">'.$year.'</span></li>');
                }
                $dwellingType = $this->checkValue($listing->getTagTypeTitle());
                if (!empty($dwellingType)) {
                    $this->addContent('<li><span class="label">Dwelling Type</span><span class="value">'.$dwellingType.'</span></li>');
                }
                $brokerages = $this->checkValue($listing->getFirmName());
                if (!empty($brokerages)) {
                    $this->addContent('<li><span class="label">Brokerages</span><span class="value">'.$brokerages.'</span></li>');
                }
            $this->addHTML('</ul>');

            $this->addHTML('<a href="' . $detailUrl . '" class="read_more hidden-sm hidden-xs">read more <span class="lrg_char">&raquo;</span></a>');
            $this->addHTML('<div class="list_bottom visible-sm visible-xs"><a href="' . $detailUrl . '" class="primary_btn read_more">Read More</a></div>');
        $this->addHTML('</div>');
    }
    
    protected function addOpenhouseDetails(){
        $listing = $this->mlsListing;
        $detailUrl = $listing->getViewURL();
        $openHouses = $listing->getOpenHouses();
        if(!empty($openHouses)){
            $this->addHTML('<div class="open_house_list">');
            foreach($openHouses as $openHouse ) {
                $this->addHTML('<a href="' . $detailUrl . '" class="open_house_link">');
                    $this->addHTML('<div class="open_house_info">
                                        <div class="clock"><img src="resources/css/images/clock_icon.svg" alt="Clock icon"></div>
                                        <div class="open_house_content uppercased">' . date('D M j', strtotime($openHouse->getProperty('oh_start_date'))) . 
                                                                            '<br>' . date('g:i A', strtotime($openHouse->getProperty('oh_start_time'))) . ' - ' . date('g:i A', strtotime($openHouse->getProperty('oh_end_time'))) . '</div>
                                    </div><!--.open_house_info-->');
                $this->addHTML('</a>');    
            }
            $this->addHTML('</div>');
            $internetRemarks = $listing->getProperty('internet_remarks');
            if (!empty($internetRemarks)) {
                $this->addContent('<p class="internet_remarks">'.GI_StringUtils::summarize($internetRemarks, 200).'</p>');
            }
        }
    }

    /**
     * Add Listing thumbnail image
     */
    protected function addImageViews(){
        $this->addHTML('<div class="list_img">');
        $image = $this->mlsListing->getCoverImage();
        if (!empty($image)) {
            if (method_exists($image, 'getView')) {
                $imageView = $image->getView('list');
                $this->addHTML($imageView->getHTMLView());
            } else if (method_exists($image, 'getImageURL')) {
                $imageURL = $image->getImageURL();
                if (!empty($imageURL)) {
                    $imageTitle = 'Listing image';
                    if (method_exists($image, 'getTitle')) {
                       $imageTitle = $image->getTitle();
                    }
                    $this->addHTML('<div class="img_wrap"><img src="'.$imageURL.'" alt="'.$imageTitle.'" title="'.$imageTitle.'"/></div>');
                }
            } else {
                $this->addHTML('<div class="no_img no_list_img"></div>');
            }
        } else {
            $this->addHTML('<div class="no_img no_list_img"></div>');
        }
        $this->addHTML('</div>');
    }
    
    protected function checkValue($value){
        $result = empty($value) ? '' : $value;
        return $result;
    }
}
