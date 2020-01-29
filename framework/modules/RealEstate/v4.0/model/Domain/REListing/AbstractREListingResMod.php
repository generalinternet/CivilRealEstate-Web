<?php

abstract class AbstractREListingResMod extends AbstractREListingRes {
    
    protected $fieldArray = array(
                're_listing_status_id',
                'sold_price',
                'sold_date',
                'list_price',
                'public_remarks',
                'virtual_tour_url'
            );
    
    public function getViewTitle($plural = true) {
        $title = 'Mod. MLS ';
        $title .= parent::getViewTitle($plural);
        return $title;
    }
    
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new REModFormView($form, $this);
        $uploader = $this->getUploader($form);
        $formView->setUploader($uploader);
        //MLS Listing sample model to search
        $mlsListing = $this->getMLSListing();
        if (empty($mlsListing)) {
            $mlsListing = MLSListingFactory::buildNewModel('res');
        }
        $formView->setMLSListing($mlsListing);
        
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
    
    public function handleFormSubmission(GI_Form $form){
        if($form->wasSubmitted() && $form->validate()){
            foreach($this->fieldArray as $field){
                $value = filter_input(INPUT_POST, $field);
                $this->setProperty($field, $value);
            }
            
            $uploader = $this->getUploader($form);

            if($this->save()){
                $uploader->setTargetFolder($this->getFolder());
                FolderFactory::putUploadedFilesInTargetFolder($uploader);

                return true;
            }
            
            return false;
        }
        
        return false;
    }
    
    /**
     * Merge RE listing images and MLS listing images
     * @return AbstractFile[]
     */
    public function getImages(){
        if(is_null($this->images)){
            //Get re_listing images
            $reImages = parent::getImages();
            //Get mls_listing images
            $mlsImages = $this->getMLSListingImages();
            $this->images = array_merge($reImages, $mlsImages);
        }
        return $this->images;
    }
    
    /**
     * Should get MLS listing's value
     * @return AbstractMLSCity
     */
    public function getCity(){
        if(is_null($this->mlsCity)){
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $cityId = $mlsListing->getProperty('mls_city_id');
            }
            $this->mlsCity = MLSCityFactory::getModelById($cityId);
        }
        return $this->mlsCity;
    }
    
    public function getCityTitle($showMLSData = false){
        $cityTitle = '';
        $city = $this->getCity();
        if($city){
            $cityTitle = $city->getTitle();
            if ($showMLSData && !empty($cityTitle)) {
                $cityTitle = '<span class="mls_data" title="MLS Data">'.$cityTitle.'</span>';
            }
        }
        return $cityTitle;
    }
    
    /**
     * Should get MLS listing's value
     * @return AbstractMLSArea
     */
    public function getArea(){
        if(is_null($this->mlsArea)){
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $areaId = $mlsListing->getProperty('mls_area_id');
            }
            $this->mlsArea = MLSAreaFactory::getModelById($areaId);
        }
        return $this->mlsArea;
    }
    
    public function getAreaTitle($showMLSData = false){
        $areaTitle = '';
        $area = $this->getArea();
        if($area){
            $areaTitle = $area->getTitle();
            if ($showMLSData && !empty($areaTitle)) {
                $areaTitle = '<span class="mls_data" title="MLS Data">'.$areaTitle.'</span>';
            }
        }
        return $areaTitle;
    }
    
    /**
     * Should get MLS listing's value
     * @return AbstractMLSArea
     */
    public function getSubArea(){
        if(is_null($this->mlsSubArea)){
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $subAreaId = $mlsListing->getProperty('mls_sub_area_id');
            }
            $this->mlsSubArea = MLSSubAreaFactory::getModelById($subAreaId);
        }
        return $this->mlsSubArea;
    }
    
    public function getSubAreaTitle($showMLSData = false){
        $subAreaTitle = '';
        $subArea = $this->getSubArea();
        if($subArea){
            $subAreaTitle = $subArea->getTitle();
            if ($showMLSData && !empty($subAreaTitle)) {
                $subAreaTitle = '<span class="mls_data" title="MLS Data">'.$subAreaTitle.'</span>';
            }
        }
        return $subAreaTitle;
    }
    
    /**
     * Should get MLS listing's value
     * @return string
     */
    public function getAddress($showMLSData = false){
        $addr = '';
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $addr = $mlsListing->getProperty('addr');
            if ($showMLSData && !empty($addr)) {
                $addr = '<span class="mls_data" title="MLS Data">'.$addr.'</span>';
            }
        }
        return $addr;
    }
    
    /**
     * Should get MLS listing's value
     * @return string
     */
    public function getStreetName($showMLSData = false){
        $streetName = '';
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $streetName = $mlsListing->getProperty('street_name');
            if ($showMLSData && !empty($streetName)) {
                $streetName = '<span class="mls_data" title="MLS Data">'.$streetName.'</span>';
            }
        }
        return $streetName;
    }
    
    /**
     * Should get MLS listing's value
     * @return string
     */
    public function getProvince($showMLSData = false){
        $province = '';
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $province = $mlsListing->getProperty('province');
            if ($showMLSData && !empty($province)) {
                $province = '<span class="mls_data" title="MLS Data">'.$province.'</span>';
            }
        }
        return $province;
    }
    
    /**
     * Should get MLS listing's value
     * @return string
     */
    public function getPostalCode($showMLSData = false){
        $postalCode = '';
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $postalCode = $mlsListing->getProperty('postal_code');
            if ($showMLSData && !empty($postalCode)) {
                $postalCode = '<span class="mls_data" title="MLS Data">'.$postalCode.'</span>';
            }
        }
        return $postalCode;
    }
    
    /**
     * Should get MLS listing's value
     * @return string
     */
    public function getYearBuilt($showMLSData = false){
        $year = '';
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $year = $mlsListing->getProperty('year');
            if ($showMLSData && !empty($year)) {
                $year = '<span class="mls_data" title="MLS Data">'.$year.'</span>';
            }
        }
        return $year;
    }
    
    /**
     * Should get MLS listing's value
     * @param string $typeRef
     * @return Tag[]
     */
    public function getLinkedTagArrayByTypeRef($typeRef, $idsAsKey = false){
        $tags = NULL;
        $mlsListing = $this->getMLSListing();
        if (!empty($mlsListing)) {
            $dbType = MLSListingFactory::getDBType();
            $tags = TagFactory::getByModel($mlsListing, $idsAsKey, $dbType, $typeRef);
        }
        return $tags;
    }
    
    public function getDisplayListPrice($showMLSData = false) {
        $listPrice = $this->getProperty('list_price');
        if (empty($listPrice)) {
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $listPrice = $mlsListing->getProperty('list_price');
            }
        }
        $priceHTML = $this->getDisplayPriceHTML($listPrice);
        if ($showMLSData) {
            $priceHTML = '<span class="mls_data" title="MLS Data">'.$priceHTML.'</span>';
        }
        return $priceHTML;
    }
    
    public function getDisplaySoldPrice($showMLSData = false) {
        $soldPrice = $this->getProperty('sold_price');
        if (empty($soldPrice)) {
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $soldPrice = $mlsListing->getProperty('sold_price');
            }
        }
        $soldPriceHTML = $this->getDisplayPriceHTML($soldPrice);
        if ($showMLSData) {
            $soldPriceHTML = '<span class="mls_data" title="MLS Data">'.$soldPriceHTML.'</span>';
        }
        return $soldPriceHTML;
    }
    
    public function getDisplaySoldDate($showMLSData = false) {
        $soldDate = $this->getProperty('sold_date');
        if (empty($soldDate)) {
            $mlsListing = $this->getMLSListing();
            if (!empty($mlsListing)) {
                $soldDate = $mlsListing->getProperty('sold_date');
            }
            if ($showMLSData && !empty($soldDate)) {
                $soldDate = '<span class="mls_data" title="MLS Data">'.$soldDate.'</span>';
            }
        }
        return $soldDate;
    }
    
    
}
