<?php

class MLSActiveListingItemView extends AbstractMLSListingItemView {
    
    public function buildView(){
        
        $listing = $this->mlsListing;
        
        $images = $listing->getImages();
        $default_image = 'resources/css/images/no_image_sign.png';
        
        $image = $listing->getCoverImage();
        if(empty($image)){
            $imageURL = $default_image;
        }
        else{
            $imageURL = $image->getImageURL();
        }
//        $images[1] = isset($images[1]) ? $images[1] : $default_image;
//        $images[2] = isset($images[2]) ? $images[2] : $default_image;
//                                        <div class="image_wrapper_sm"><img src="' . $images[1] . '"></div>
//                                        <div class="image_wrapper_sm" style="float: right;"><img src="' . $images[2] . '"></div>
        
        $detailUrl = $listing->getViewURL();
        
        
        $this->addContent('<tr class="section_2" style="margin:0">
                                <td class="col-sm-4 no_left_padding out_border" rowspan=2>
                                    <a href="' . $detailUrl . '">');
        
                    if($listing->getOpenHouses()){
                        $this->addContent('<div class="open_house_icon"><img src="resources/css/images/open_house_icon.png" alt="open house icon"></div>');
                    }
                    else if($listing->getListingStatusTitle() == 'Sold'){
                        $this->addContent('<div class="open_house_icon"><img src="resources/css/images/sold_icon.png" alt="open house icon"></div>');
                    }
        


                    $this->addContent('<div class="image_wrapper"><img src="' . $imageURL . '" alt="Listing image"></div>
                                    </a>
                                </td>
                                <td class="col-sm-4 header_td">
                                    <a href="' . $detailUrl . '">
                                        <div class="header address" data-city="' . $listing->getCityTitle() . '">' . $listing->getProperty('addr') . ', ' . $listing->getCityTitle() . '</div>
                                    </a>
                                    
                                </td>
                                <td class="col-sm-4 hidden-xs">

                                </td>
                            </tr>
                            <tr class="out_border">
                                <td class="border">
                                    <div class="info">
                                    <table style="width: 100%">');
                    
                    if($listing->getListingStatusTitle() != 'Sold'){
                        $this->addHTML('<tr>
                                            <td class="label">Price</td>
                                            <td>$' . $this->checkValue(number_format($listing->getProperty('list_price'))) . '</td>
                                        </tr>');
                    }
                    $this->addContent('<tr>
                                            <td class="label">City</td>
                                            <td class="city">' . $listing->getCityTitle() . '</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Bedrooms</td>
                                            <td>' . $this->checkValue($listing->getProperty('mls_listing_res.total_bedrooms')) . '</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Bathrooms</td>
                                            <td>' . $this->checkValue($listing->getProperty('mls_listing_res.total_baths')) . '</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Square Feet</td>
                                            <td>' . $this->checkValue($listing->getPropertySize()) . ' sqft</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Lot Size</td>
                                            <td>' . $this->checkValue($listing->getProperty('lot_size_sqft')) . ' sqft</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Year Built</td>
                                            <td>' . $this->checkValue($listing->getProperty('year')) . '</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Dwelling Type</td>
                                            <td>' . $listing->getTagTypeTitle() . '</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Brokerages</td>
                                            <td>' . $this->checkValue($listing->getFirmName()) . '</td>
                                        </tr>
                                    </table>
                                    </div>
                                </td>
                                <td class="border">
                                    <div class="info">' . GI_StringUtils::summarize($listing->getProperty('internet_remarks'), 420) . '<br/>
                                    <a href="' . $detailUrl . '" class="read_more">read more <span class="lrg_char">&raquo;</span></a>
                                    </div>
                                </td>
                                
                            </tr>');
    }
    
    public function checkImage($image){
        $defaultImage = 'resources/css/images/no_image_sign.png';
        return isset($image) ? $image->getImageURL() : $defaultImage;
    }

    public function checkValue($value){
        $result = empty($value) ? '' : $value;
        return $result;
    }
    
    public function checkTitle($value){
        $result = empty($value) ? '' : $value->getProperty('title');
        return $result;
    }
}
