<?php

class REListingItemView extends AbstractREListingItemView {
    
    public function buildView(){
        $listing = $this->mlsListing;
        $viewURL =  $listing->getViewURL();
        $this->addHTML('<div class="ui_grid flex-cell staus_'.$listing->getListingStatusRef().'">');
            $this->addHTML('<div class="grid_header">');
                $this->addHTML('<a href="'.$viewURL.'">');
                    $this->addImageViews();
                    if($listing->getListingStatusRef() != 'sold'){
                        //$this->addHTML('<p class="bottom_bar price">'.$listing->getDisplayPrice().'</p>');
//                    } else {
//                        $this->addHTML('<p class="bottom_bar sold">Sold</p>');
                    }
                $this->addHTML('</a>');
            $this->addHTML('</div>');
            
            $this->addHTML('<div class="grid_body">');
                $this->addHTML('<a href="'.$viewURL.'"><h4 class="title">'.$listing->getAddressWithOptions(
                        array('city' => true)
                        ).'</h4></a>');
                $this->addHTML('<p class="remarks">'.$listing->getDisplayPublicRemarks().'</p>');
            $this->addHTML('</div>');    
            
            $this->addHTML('<div class="grid_bottom">');  
                $this->addHTML('<a href="'.$viewURL.'" class="primary_btn">View Listing</a>');
            $this->addHTML('</div>');  
        $this->addHTML('</div><!--.ui_grid-->');
    }
    
    /**
     * Add Listing thumbnail image
     */
    protected function addImageViews(){
        $this->addHTML('<div class="grid_img">');
        $image = $this->mlsListing->getCoverImage();
        if (!empty($image)) {
            if (method_exists($image, 'getView')) {
                $imageView = $image->getView('grid');
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
                $this->addHTML('<div class="no_img no_grid_img"></div>');
            }
        } else {
            $this->addHTML('<div class="no_img no_grid_img"></div>');
        }
        $this->addHTML('</div>');
    }
}
