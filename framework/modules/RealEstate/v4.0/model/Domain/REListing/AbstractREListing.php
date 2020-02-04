<?php

abstract class AbstractREListing extends GI_Model {
    
    /** @var AbstractMLSRealtor[] */
    protected $realtors = NULL;
    
    /** @var AbstractMLSFirm[] */
    protected $firms = NULL;
    
    /** @var AbstractMLSCity */
    protected $mlsCity = NULL;
    
    /** @var AbstractMLSArea */
    protected $mlsArea = NULL;
    
    /** @var AbstractMLSSubArea */
    protected $mlsSubArea = NULL;
    
    /** @var AbstractFile[] */
    protected $files = NULL;
    
    /** @var AbstractFile[] */
    protected $images = NULL;
    
    /** @var AbstractREListingStatus */
    protected $listingStatus = NULL;
    
    /** @var AbstractMLSListing */
    protected $mlsListing = NULL;
    
    /** Default : residential fields */
    protected $fieldArray = array(
        're_listing_status_id',
        'addr',
        'mls_area_id',
        'mls_city_id',
        'mls_sub_area_id',
        'sold_price',
        'sold_date',
        'list_price',
        'lot_size_acres',
        'lot_size_hectares',
        'lot_size_sqft', 
        'lot_size_sqm',
        'year',
        'storeys_in_building',
        'public_remarks',
        'amenities',
        'house_number',
        'street_name',
        'postal_code',
        'province',
        'unit_number',
        'virtual_tour_url'
    );
    /** Default : residential type ref */
    protected $propertyTypeTagTypeRef = 're_dwelling';
    
    /** @return AbstractFile[] */
    public function getImages(){
        if(is_null($this->images)){
            $folder = $this->getFolder();
            $this->files = $folder->getFiles();
            $images = array();
            foreach($this->files as $file){
                if($file->isImage()){
                    $images[] = $file;
                }
            }
            $this->images = $images;
        }
        return $this->images;
    }
    
    /** @return AbstractMLSCity */
    public function getCity(){
        if(is_null($this->mlsCity)){
            $this->mlsCity = MLSCityFactory::getModelById($this->getProperty('mls_city_id'));
        }
        
        return $this->mlsCity;
    }
    
    public function getCityTitle($showMLSData = false){
        $cityTitle = '';
        $city = $this->getCity();
        if($city){
            $cityTitle = $city->getTitle();
        }
        return $cityTitle;
    }
    
    /**
     * @return AbstractMLSArea
     */
    public function getArea(){
        if(is_null($this->mlsArea)){
            $this->mlsArea = MLSAreaFactory::getModelById($this->getProperty('mls_area_id'));
        }
        return $this->mlsArea;
    }
    
    public function getAreaTitle($showMLSData = false) {
        $areaTitle = '';
        $area = $this->getArea();
        if($area){
            $areaTitle = $area->getTitle();
        }
        return $areaTitle;
    }
    
    /**
     * @return AbstractMLSSubArea
     */
    public function getSubArea(){
        if(is_null($this->mlsSubArea)){
            $this->mlsSubArea = MLSSubAreaFactory::getModelById($this->getProperty('mls_sub_area_id'));
        }
        
        return $this->mlsSubArea;
    }
    
    public function getSubAreaTitle($showMLSData = false) {
        $subAreaTitle = '';
        $subArea = $this->getSubArea();
        if($subArea){
            $subAreaTitle = $subArea->getTitle();
        }
        return $subAreaTitle;
    }
    
    public function getAddress($showMLSData = false){
        return $this->getProperty('addr');
    }
    
    public function getProvince($showMLSData = false){
        return $this->getProperty('province');
    }
    
    public function getPostalCode($showMLSData = false){
        return $this->getProperty('postal_code');
    }
    
    public function getStreetName($showMLSData = false){
        return $this->getProperty('street_name');
    }
    
    public function getYearBuilt($showMLSData = false){
        return $this->getProperty('year');
    }
    
    /**
     * @param array $options
     * @return type
     */
    public function buildAddrStringWithOptions($options){
        $addrStreet = $this->getAddress('addr');
        if (array_key_exists('city', $options) && $options['city']) {
            $addrCity = $this->getCityTitle();
        } else {
            $addrCity = '';
        }
        
        if (array_key_exists('province', $options) && $options['province']) {
            $addrRegion = $this->getProvince();
        } else {
            $addrRegion = '';
        }
        
        if (array_key_exists('postal_code', $options) && $options['postal_code']) {
            $addrCode = $this->getPostalCode();
        } else {
            $addrCode = '';
        }
        
        if (array_key_exists('break_lines', $options) && $options['break_lines']) {
            $breakLines = true;
        } else {
            $breakLines = false;
        }
        $addrCountry = '';
        $addr = GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, $addrCountry, $breakLines);
        return $addr;
    }

//@todo: delete because don't need to get all linked tags to get only first tag's title later at getTagTypeTitle  
    /**
     * @return Taggi[]
     */
//    public function getTags(){
//        if(is_null($this->tags)){
//            $this->tags = TagFactory::getByModel($this);
//        }
//        
//        return $this->tags;
//    }
    
    public function getCoverImage(){
        $images = $this->getImages();
        if(isset($images[0])){
            return $images[0];
        }
        return NULL;
    }
    
    public function getCoverImageHTML($width = 320, $height = 214, $keepRatio = true, $startWrapHTML = '', $endWrapHTML = ''){
        $image = $this->getCoverImage();
        if (!empty($image)) {
            return $this->getImageHTML($image, $width, $height, $keepRatio, $startWrapHTML, $endWrapHTML);
        }
        return '<div class="no_img no_cover_image"></div>';
    }
    
    public function getImagesHTML($width = 320, $height = 214, $keepRatio = true, $startWrapHTML = '', $endWrapHTML = ''){
        $images = $this->getImages();
        if (!empty($images)) {
            $html = '';
            foreach($images as $image) {
                $html .= $this->getImageHTML($image, $width, $height, $keepRatio, $startWrapHTML, $endWrapHTML);
            }
            return $html;
        }
        return '<div class="no_img no_cover_image"></div>';
    }
    
    public function getImageHTML($image, $width = 320, $height = 214, $keepRatio = true, $startWrapHTML = '', $endWrapHTML = '') {
        if ($keepRatio && method_exists($image, 'getSizedViewKeepRatio')) {
            $view = $image->getSizedViewKeepRatio($width, $height);
            return $startWrapHTML.$view->getHTMLView().$endWrapHTML;
        } else if (!$keepRatio && method_exists($image, 'getSizedView')) {
            $view = $image->getSizedView($width, $height);
            return $startWrapHTML.$view->getHTMLView().$endWrapHTML;
        } else if (method_exists($image, 'getImageURL')) {
            $imageURL = $image->getImageURL();
            $imageTitle = 'Cover image';
            if (method_exists($image, 'getTitle')) {
               $imageTitle = $image->getTitle();
            }
            return $startWrapHTML.'<div class="img_wrap"><img src="'.$imageURL.'" alt="'.$imageTitle.'" title="'.$imageTitle.'"/></div>'.$endWrapHTML;
        }
    }
    
    public function getListingStatus(){
        if(is_null($this->listingStatus)){
            $this->listingStatus = REListingStatusFactory::getModelById($this->getProperty('re_listing_status_id'));
        }
        return $this->listingStatus;
    }
    
    public function getListingStatusTitle(){
        $listingStatus = $this->getListingStatus();
        if($listingStatus){
            return $listingStatus->getTitle();
        }
        return NULL;
    }
    
    public function getListingStatusRef(){
        $listingStatus = $this->getListingStatus();
        if($listingStatus){
            return $listingStatus->getRef();
        }
        return NULL;
    }
    
    /**
     * @param string $typeRef
     * @return Tag[]
     */
    public function getLinkedTagArrayByTypeRef($typeRef, $idsAsKey = false){
        $dbType = REListingFactory::getDBType();
        $tags = TagFactory::getByModel($this, $idsAsKey, $dbType, $typeRef);
        return $tags;
    }
    
    public function getLinkedTagIdByTypeRef($typeRef){
        $tags = $this->getLinkedTagArrayByTypeRef($typeRef);
        if (!empty($tags)) {
            return $tags[0]->getProperty('id');
        }
        return NULL;
    }
    
    public function getLinkedTagTitleByTypeRef($typeRef){
        $tags = $this->getLinkedTagArrayByTypeRef($typeRef);
        if (!empty($tags)) {
            return $tags[0]->getProperty('title');
        }
        return NULL;
    }
    
    public function getPropertyTypeTagsOptionArray(){
        $tags = TagFactory::getByRef($this->propertyTypeTagTypeRef);
        $options = array();
        foreach($tags as $tag){
            $options[$tag->getId()] = $tag->getProperty('title');
        }
        return $options;
    }
    
    public function getLinkedPropertyTypeTagId(){
        return $this->getLinkedTagIdByTypeRef($this->propertyTypeTagTypeRef);
    }
    
    public function getLinkedPropertyTypeTagTitle(){
        return $this->getLinkedTagTitleByTypeRef($this->propertyTypeTagTypeRef);
    }
    
    /**
     * @return AbstractMLSRealtor[]
     */
    public function getRealtors(){
        //@todo need to get links first, then realtors
        /*
        if(is_null($this->realtors)){
            $dbType = MLSListingFactory::getDBType();
            $realtorTable = dbConfig::getDbPrefix($dbType) . 'mls_realtor';

            $this->realtors = MLSRealtorFactory::search()
                    ->join('mls_listing_link_to_realtor', 'mls_realtor_id', $realtorTable, 'id', 'R')
                    ->filter('R.mls_listing_id', $this->getProperty('id'))
                    ->orderBy('R.pos', 'ASC')
                    ->orderBy('R.id', 'ASC')
                    ->select();
        }
        return $this->realtors;
        */
    }
    
    /**
     * @return AbstractMLSRealtor
     */
    public function getRealtor(){
        $realtors = $this->getRealtors();
        if($realtors){
            return $realtors[0];
        }
        return NULL;
    }
    
    /**
     * @return AbstractMLSFirm[]
     */
    public function getFirms(){
        //@todo need to get links first, then firms
        /*
        if(is_null($this->firms)){
            $dbType = MLSListingFactory::getDBType();
            $firmTable = dbConfig::getDbPrefix($dbType) . 'mls_firm';

            $this->firms = MLSFirmFactory::search()
                    ->join('mls_listing_link_to_firm', 'mls_firm_id', $firmTable, 'id', 'F')
                    ->filter('F.mls_listing_id', $this->getProperty('id'))
                    ->orderBy('F.pos', 'ASC')
                    ->orderBy('F.id', 'ASC')
                    ->select();
        }
        return $this->firms;
         */
    }
    
    /**
     * @return AbstractMLSFirm
     */
    public function getFirm(){
        $firms = $this->getFirms();
        if($firms){
            return $firms[0];
        }
        return NULL;
    }
    
    /**
     * @return AbstractMLSListing
     */
    public function getMLSListing(){
        if(is_null($this->mlsListing)){
            $this->mlsListing = MLSListingFactory::getModelById($this->getProperty('mls_listing_id'));
        }
        return $this->mlsListing;
    }
    
    /**
     * @return AbstractMLSListing
     */
    public function setMLSListing($mlsListing){
        $this->mlsListing = $mlsListing;
        $this->setProperty('mls_listing_id', $mlsListing->getId());
    }
    
    /**
     * @return AbstractMLSListingImage[]
     */
    public function getMLSListingImages(){
        $mlsListing = $this->getMLSListing();
        if(!empty($mlsListing)){
            $mlsImages = $mlsListing->getImages();
            return $mlsImages;
        }
        return NULL;
    }
    
    
    public function handleFormSubmission(GI_Form $form){
        if($this->validateForm($form)){
//@todo:delete it because it's set when $this->fieldArray initialized.
//            $fieldArray = array(
//                're_listing_status_id',
//                'addr',
//                'mls_area_id',
//                'mls_city_id',
//                'mls_sub_area_id',
//                'sold_price',
//                'sold_date',
//                'list_price',
//                'lot_size_acres',
//                'lot_size_hectares',
//                'lot_size_sqft', 
//                'lot_size_sqm',
//                'year',
//                'storeys_in_building',
//                'public_remarks',
//                'amenities',
//                'house_number',
//                'street_name',
//                'postal_code',
//                'province',
//                'unit_number',
//                'virtual_tour_url'
//            );
            
            $propertyTypeId = filter_input(INPUT_POST, 'property_type');
            
            foreach($this->fieldArray as $field){
                $value = filter_input(INPUT_POST, $field);
                $this->setProperty($field, $value);
            }
            
            $uploader = $this->getUploader($form);

            if($this->save()){
                $uploader->setTargetFolder($this->getFolder());
                FolderFactory::putUploadedFilesInTargetFolder($uploader);
                
                REListingFactory::tagPropertyType($this, [$propertyTypeId]);
                return true;
            }
            return false;
        }
        return false;
    }
//@todo:delete it because it's moved to AbstractREListingMod class    
//    public function handleModifyFormSubmission(GI_Form $form){
//        if($form->wasSubmitted() && $form->validate()){
//            $fieldArray = array(
//                're_listing_status_id',
//                'sold_price',
//                'sold_date',
//                'list_price',
//                'public_remarks',
//                'virtual_tour_url'
//            );
//            
//            foreach($fieldArray as $field){
//                $value = filter_input(INPUT_POST, $field);
//                $this->setProperty($field, $value);
//            }
//            
//            $uploader = $this->getUploader($form);
//
//            if($this->save()){
//                $uploader->setTargetFolder($this->getFolder());
//                FolderFactory::putUploadedFilesInTargetFolder($uploader);
//
//                return true;
//            }
//            
//            return false;
//        }
//        
//        return false;
//    }
    
//    protected function getUploader($form){
//        if($this->getProperty('id')){
//            $appendName = 'edit_' . $this->getProperty('id');
//        } else {
//            $appendName = 'add';
////            $contentNumber = $this->getContentNumber();
////            if(!empty($contentNumber)){
////                $appendName .= '_' . $contentNumber;
////            }
//        }
//        
//        $uploader = GI_UploaderFactory::buildImageUploader('listing_' . $appendName);
//        $folder = $this->getFolder();
//        
//        $uploader->setTargetFolder($folder);
//        $uploader->setForm($form);
//
//        return $uploader;
//    }
    
    protected function getUploader(GI_Form $form = NULL){
        if($this->getProperty('id')){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        
        $uploader = GI_UploaderFactory::buildImageUploader('re_listing_' . $appendName);
        $uploader->setFilesLabel('Images');
        $uploader->setBrowseLabel('Upload Images');
        
        $folder = $this->getFolder();
        
        $uploader->setTargetFolder($folder);
        if (!empty($form)) {
            $uploader->setForm($form);
        }
        
        return $uploader;
    }
    
    public function getFolderProperties() {
        $folderProperties = parent::getFolderProperties();
        $folderProperties['title'] = 'Real Estate ' . $this->getProperty('id');
        return $folderProperties;
    }
  
//@todo: delete old code    
//    public function getFormView(\GI_Form $form) {
//        $RealtyAddListingView = new RealtyAddListingView($form, $this);
//        $uploader = $this->getUploader($form);
//        $RealtyAddListingView->setUploader($uploader);
//        $RealtyAddListingView->buildForm();
////        $RealtyAddListingView->setShowRef(true);
////        if($buildForm){
////            $RealtyAddListingView->buildForm();
////        }
//        return $RealtyAddListingView;
//    }
    
    /**
     * Get a form view
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \REFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $formView = new REFormView($form, $this);
        $uploader = $this->getUploader($form);
        $formView->setUploader($uploader);
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }
// @todo: delete it because it becomes REListingResMod's getFormView    
//    public function getModifyFormView(\GI_Form $form) {
//        $RealtyModifyListingView = new RealtyModifyListingView($form, $this);
//        $uploader = $this->getUploader($form);
//        $RealtyModifyListingView->setUploader($uploader);
//        $RealtyModifyListingView->buildForm();
////        $RealtyAddListingView->setShowRef(true);
////        if($buildForm){
////            $RealtyAddListingView->buildForm();
////        }
//        return $RealtyModifyListingView;
//    }
    
//    public function getFirstImageHTML(){
//        $images = $this->getImages();
//        if(isset($images[0]) && (method_exists($images[0],'getFileS3URL'))){
//            return '<img src="' . $images[0]->getFileS3URL() . '">';
//        }
//        return '';
//    }
    
    public static function addTagFilterToDataSearch($tag, GI_DataSearch $dataSearch){
        $tagIds = explode(',', $tag);
        $mlsListingTable = MLSListingFactory::getDbPrefix() . 'mls_listing';
        $dataSearch->join('item_link_to_tag', 'item_id', $mlsListingTable, 'id', 'TL')
                ->filter('TL.table_name', 'mls_listing')
                ->filterIn('TL.tag_id', $tagIds);
    }
    
//    public function getTagTypeTitle(){
//        $tags = $this->getTags();
//        if(empty($tags)){
//            return false;
//        }
//        
//        return $tags[0]->getProperty('title');
//    }
    
    public function getMLSListingAddress(){
        $mlsListing = $this->getMLSListing();
        if(!empty($mlsListing)){
            return $mlsListing->getProperty('addr');
        }
        
        return null;
    }
    
    /******@todo: delete this comment 4.0 code start******/
    public function getTitle() {
        $mlsListing = $this->getMLSListing();
        if(!empty($mlsListing)){
            return $mlsListing->getProperty('addr');
        } else {
            return $this->getProperty('addr');
        }
    }
    public function getViewTitle($plural = true) {
        $title = 'Listing';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    /** @return \AbstractREDetailView */
    public function getDetailView() {
        $view = new REDetailView($this);
        return $view;
    }
    
    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Image',
                'method_name' => 'getCoverImageHTML',
                'css_class'=> 'img_thumb',
                'method_attributes' => array(90, 60, true),
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => 'Status',
                'method_name' => 'getListingStatusTitle',
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => 'Address',
                'method_attributes'=>'addr',
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => 'List Price',
                'method_name' => 'getDisplayListPrice',
                'cell_url_method_name' => 'getViewURL',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    /**
     * Gets breadcrumbs
     * 
     * @return array[key=>value]
     */
    public function getBreadcrumbs() {
        $breadcrumbs = array();
        if($this->isIndexViewable()) {
            $indexUrl = GI_URLUtils::buildURL(array(
                'controller' => 're',
                'action' => 'index',
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $indexUrl
            );
        } else {
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle()
            );
        }
        $userId = $this->getProperty('id');
        if (!is_null($userId)) {
            $breadcrumbs[] = array(
                'label' => $this->getTitle(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    public function getViewURLAttrs(){
        $urlAttributes = array(
            'controller' => 'relisting',
            'action' => 'view',
            'id' => $this->getId(),
        );
        return $urlAttributes;
    }
    
    public function getAddURLAttributes(){
        $urlAttributes = array(
            'controller' => 're',
            'action' => 'add',
            'type' => $this->getTypeRef(),
        );
        return $urlAttributes;
    }
    
    public function getAddURL(){
        $urlAttributes = $this->getAddURLAttributes();
        return GI_URLUtils::buildURL($urlAttributes);
    }
    
    public function getEditURLAttributes(){
        $urlAttributes = array(
            'controller' => 're',
            'action' => 'edit',
            'id' => $this->getId(),
        );
        return $urlAttributes;
    }
    
    public function getEditURL(){
        $urlAttributes = $this->getEditURLAttributes();
        return GI_URLUtils::buildURL($urlAttributes);
    }
    
    public function getDeleteURLAttributes(){
        $urlAttributes = array(
            'controller' => 're',
            'action' => 'delete',
            'id' => $this->getId(),
        );
        return $urlAttributes;
    }
    
    public function getDeleteURL(){
        $urlAttributes = $this->getDeleteURLAttributes();
        return GI_URLUtils::buildURL($urlAttributes);
    }
    
    public function getIndexURLAttrs($withPageNumber = false){
        $indexURLAttributes = array(
            'controller' => 're',
            'action' => 'index',
        );
        $attributes = GI_URLUtils::getAttributes();
        if (isset($attributes['queryId'])) {
            $indexURLAttributes['queryId'] = $attributes['queryId'];
        }
        if ($withPageNumber && isset($attributes['pageNumber'])) {
            $indexURLAttributes['pageNumber'] = $attributes['pageNumber'];
        }
        return $indexURLAttributes;
    }
    
    public function getListBarURL($otherAttributes = NULL) {
        if (!$this->isIndexViewable()) {
            return NULL;
        }
        
        $listURLAttributes = $this->getIndexURLAttrs();
        $listURLAttributes['targetId'] = 'list_bar';
        $listURLAttributes['curId'] = $this->getId();
        $attributes = GI_URLUtils::getAttributes();
        if (isset($attributes['type'])) {
            $listURLAttributes['type'] = $attributes['type'];
        }
        if (isset($otherAttributes['type'])) {
            //overrite type
            $listURLAttributes['type'] = $otherAttributes['type'];
        }
        if (isset($otherAttributes['fullView'])) {
            $listURLAttributes['fullView'] = $otherAttributes['fullView'];
        } else {
            $listURLAttributes['fullView'] = 1;
        }
        
        return GI_URLUtils::buildURL($listURLAttributes);
    }
    
    /******PERMISSIONS******/
    public function getIsIndexViewable() {
        return Permission::verifyByRef('view_re_listing_index');
    }
    
    public function getIsViewable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('view_re_listings')){
            return true;
        }
        return false;
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_re_listings')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_re_listings')){
            return true;
        }
        return false;
    }
    
    public function getIsDeleteable(){
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_re_listings')){
            return true;
        }
        return false;
    }
    /******PERMISSIONS:end******/
    
    /******SEARCH******/
    /**
     * @param GI_DataSearch $dataSearch
     * @return \GI_DataSearch
     */
    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        return $dataSearch;
    }
    
    /** @param GI_DataSearch $dataSearch */
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $dataSearch->setSortDescending(true);
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \AbstractRESearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new RESearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return AbstractRESearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        $form = new GI_Form('real_estate_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 're',
                    'action' => 'index'
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addBasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            //Advanced Search
            $mlsNumber = $dataSearch->getSearchValue('mls_number');
            if(!empty($mlsNumber)){
                static::addMLSNumberToDataSearch($mlsNumber, $dataSearch);
            }
            
            $address = $dataSearch->getSearchValue('address');
            if(!empty($address)){
                static::addAddressFilterToDataSearch($address, $dataSearch);
            }
            
            $cityIds = $dataSearch->getSearchValue('city_id');
            if(!empty($cityIds)){
                static::addCityIdsFilterToDataSearch(explode(',', $cityIds), $dataSearch);
            }
            
            $location = $dataSearch->getSearchValue('location');
            if(!empty($location)){
                static::addLocationFilterToDataSearch($location, $dataSearch);
            }

            $priceMin = $dataSearch->getSearchValue('price_min');
            $priceMax = $dataSearch->getSearchValue('price_max');
            if(!empty($priceMin) || !empty($priceMax)){
                static::addPriceFilterToDataSearch($dataSearch, $priceMin, $priceMax);
            }

            $propertyType = $dataSearch->getSearchValue('property_type');
            if(!empty($propertyType) && $propertyType != 'NULL'){
                static::addPropertyTypeFilterToDataSearch($propertyType, $dataSearch);
            }

            $floorArea = $dataSearch->getSearchValue('floor_area');
            if(!empty($floorArea) && $floorArea != 'NULL'){
                static::addFloorAreaFilterToDataSearch($floorArea, $dataSearch);
            }

            $bedrooms = $dataSearch->getSearchValue('bedrooms');
            if(!empty($bedrooms) && $bedrooms != 'NULL'){
                static::addBedroomFilterToDataSearch($bedrooms, $dataSearch);
            }

            $bathrooms = $dataSearch->getSearchValue('bathrooms');
            if(!empty($bathrooms) && $bathrooms != 'NULL'){
                static::addBathroomFilterToDataSearch($bathrooms, $dataSearch);
            }

            $year = $dataSearch->getSearchValue('year');
            if(!empty($year) && $year != 'NULL'){
                static::addYearFilterToDataSearch($year, $dataSearch);
            }
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $dataSearch->clearSearchValues();
            $searchType = filter_input(INPUT_POST, 'search_type');
            if (empty($searchType) || $searchType === 'basic') {
                $dataSearch->setSearchValue('search_type', 'basic');
                $basicSearchField = filter_input(INPUT_POST, 'basic_search_field');
                $dataSearch->setSearchValue('basic_search_field', $basicSearchField);
            } else {
                $dataSearch->setSearchValue('search_type', 'advanced');
                
                $mlsNumber = filter_input(INPUT_POST, 'search_mls_number');
                $dataSearch->setSearchValue('mls_number', $mlsNumber);
                $address = filter_input(INPUT_POST, 'search_address');
                $dataSearch->setSearchValue('address', $address);
                $cityIds = filter_input(INPUT_POST, 'search_city_id');
                $dataSearch->setSearchValue('city_id', $cityIds);
                $location = filter_input(INPUT_POST, 'search_location');
                $dataSearch->setSearchValue('location', $location);
                $priceMin = filter_input(INPUT_POST, 'search_price_min');
                $dataSearch->setSearchValue('price_min', $priceMin);
                $priceMax = filter_input(INPUT_POST, 'search_price_max');
                $dataSearch->setSearchValue('price_max', $priceMax);
                $propertyType = filter_input(INPUT_POST, 'search_property_type');
                $dataSearch->setSearchValue('property_type', $propertyType);
                $floorArea = filter_input(INPUT_POST, 'search_floor_area');
                $dataSearch->setSearchValue('floor_area', $floorArea);
                $bedrooms = filter_input(INPUT_POST, 'search_bedrooms');
                $dataSearch->setSearchValue('bedrooms', $bedrooms);
                $bathrooms = filter_input(INPUT_POST, 'search_bathrooms');
                $dataSearch->setSearchValue('bathrooms', $bathrooms);
                $year = filter_input(INPUT_POST, 'search_year');
                $dataSearch->setSearchValue('year', $year);
            }
        }
        
        return true;
    }
    
    /**
     * @param type $basicSearchField
     * @param GI_DataSearch $dataSearch
     */
    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        static::addLocationFilterToDataSearch($basicSearchField, $dataSearch);
    }
    
    public static function addLocationFilterToDataSearch($location, GI_DataSearch $dataSearch){
        $dataSearch->filterGroup();
        //Address
        $this->addAddressFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //Area
        $this->addAreaFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //City
        $this->addCityFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //MLS Number
        $this->addMLSNumberToDataSearch($location, $dataSearch);
        $dataSearch->orIf();

        $dataSearch->closeGroup();
        
        $dataSearch->andIf();
        $dataSearch->groupBy('id');
    }
    
    public static function addAddressFilterToDataSearch($address, GI_DataSearch $dataSearch){
        //@todo: search modified MLS listings by MLS address
        $dataSearch->filterTermsLike('addr', $address)
                ->orderByLikeScore('addr', $address);
        
    }

    public static function addAreaFilterToDataSearch($area, GI_DataSearch $dataSearch){
        $areas = MLSAreaFactory::search()
                ->filterTermsLike('title', $area)
                ->orderByLikeScore('title', $area)
                ->select(true);
        if (!empty($areas)) {
            $areaIds = array_keys($areas);
            $dataSearch->filterIn('mls_area_id', $areaIds);
        }
    }
    
    public static function addCityFilterToDataSearch($city, GI_DataSearch $dataSearch){
        $cities = MLSCityFactory::search()
                ->filterTermsLike('title', $city)
                ->orderByLikeScore('title', $city)
                ->select(true);
        if (!empty($cities)) {
            $cityIds = array_keys($cities);
            $this->addCityIdsFilterToDataSearch($cityIds, $dataSearch);
        }
    }
    
    public static function addCityIdsFilterToDataSearch($cityIds, GI_DataSearch $dataSearch){
        $dataSearch->filterIn('mls_city_id', $cityIds);
    }
    
    public static function addMLSNumberToDataSearch($mlsNumber, GI_DataSearch $dataSearch){
        $mlsListings = MLSListingFactory::search()
                ->filterLike('mls_number', $mlsNumber)
                ->orderByLikeScore('mls_number', $mlsNumber)
                ->select(true);
        if (!empty($mlsListings)) {
            $mlsListingIds = array_keys($mlsListings);
            $dataSearch->filterIn('mls_listing_id', $mlsListingIds);
        }
    }
    
    public static function addPriceFilterToDataSearch(GI_DataSearch $dataSearch, $priceMin = NULL, $priceMax = NULL){
        if(!empty($priceMin)) {
            $dataSearch->filterGreaterOrEqualTo('list_price', $priceMin);
        }
        
        if(!empty($priceMax)) {
            $dataSearch->filterLessOrEqualTo('list_price', $priceMax);
        }
        
        $dataSearch->orderBy('list_price');
    }
    
    public static function addPropertyTypeFilterToDataSearch($propertyType, GI_DataSearch $dataSearch){
        $tagIds = explode(',', $propertyType);
        $reListingTable = REListingFactory::getDbPrefix() . 're_listing';
        $dataSearch->join('item_link_to_tag', 'item_id', $reListingTable, 'id', 'TL')
                ->filter('TL.table_name', 're_listing')
                ->filterIn('TL.tag_id', $tagIds);
    }
    
    public static function addFloorAreaFilterToDataSearch($floorArea, GI_DataSearch $dataSearch){
        $floorAreaArray = explode(',', $floorArea);
        if(!empty($floorAreaArray[0])){
            //start floor area
            $dataSearch->filterGreaterOrEqualTo('re_listing_res.floor_area_total', $floorAreaArray[0]);
        }
        if(!empty($floorAreaArray[1])){
            //end floor area
            $dataSearch->filterLessOrEqualTo('re_listing_res.floor_area_total', $floorAreaArray[1]);
        }
        $dataSearch->orderBy('re_listing_res.floor_area_total');
    }
    
    public static function addBedroomFilterToDataSearch($bedrooms, GI_DataSearch $dataSearch){
        $bedroomArray = explode(',', $bedrooms);
        if(count($bedroomArray) > 1){
            //number +
            $dataSearch->filterGreaterOrEqualTo('re_listing_res.total_bedrooms', $bedroomArray[0]);
        } else {
            //number
            $dataSearch->filterEqualTo('re_listing_res.total_bedrooms', $bedroomArray[0]);
        }
        $dataSearch->orderBy('re_listing_res.total_bedrooms');
    }
    
    public static function addBathroomFilterToDataSearch($bathrooms, GI_DataSearch $dataSearch){
        $bathroomArray = explode(',', $bathrooms);
        if(count($bathroomArray) > 1){
            //number +
            $dataSearch->filterGreaterOrEqualTo('re_listing_res.total_baths', $bathroomArray[0]);
        } else {
            //number
            $dataSearch->filterEqualTo('re_listing_res.total_baths', $bathroomArray[0]);
        }
        $dataSearch->orderBy('re_listing_res.total_baths');
    }
    
    public static function addYearFilterToDataSearch($year, GI_DataSearch $dataSearch){
        $yearArray = explode(',', $year);
        if(!empty($yearArray[0])){
            //start year
            $dataSearch->filterGreaterOrEqualTo('year', $yearArray[0]);
        }
        if(!empty($yearArray[1])){
            //end year
            $dataSearch->filterLessOrEqualTo('year', $yearArray[1]);
        }
        $dataSearch->orderBy('year');
    }
    /******SEARCH :end******/
    public function getPublicRemarks($checkMLSData = false) {
        $remarks = $this->getProperty('public_remarks');
        if(empty($remarks) && $checkMLSData){
            $mlsListing = $this->getMLSListing();
            if($mlsListing){
                return $mlsListing->getPublicRemarks();
            }
        }
        return $remarks;
    }
    
    public function getDisplayPublicRemarks($limit = 200) {
        $publicRemarks = $this->getPublicRemarks(true);
        return GI_StringUtils::summarize($publicRemarks, $limit);
    }
    
    public function getDisplayPriceHTML($price) {
        $string = '<span class="unit">$</span><span class="amount">';
        $string .= GI_StringUtils::formatMoney($price, true, 0);
        $string .= '</span>';
        return $string;
    }
    
    public function getListPrice($checkMLSData = false) {
        $listPrice = $this->getProperty('list_price');
        if(empty($listPrice) && $checkMLSData){
            $mlsListing = $this->getMLSListing();
            if($mlsListing){
                return $mlsListing->getListPrice();
            }
        }
        return $listPrice;
    }
    
    public function getDisplayListPrice($checkMLSData = false) {
        return $this->getDisplayPriceHTML($this->getListPrice($checkMLSData));
    }
    
    public function getSoldPrice($checkMLSData = false) {
        $soldPrice = $this->getProperty('sold_price');
        if(empty($soldPrice) && $checkMLSData){
            $mlsListing = $this->getMLSListing();
            if($mlsListing){
                return $mlsListing->getSoldPrice();
            }
        }
        return $soldPrice;
    }
    
    public function getDisplaySoldPrice($checkMLSData = false) {
        return $this->getDisplayPriceHTML($this->getSoldPrice($checkMLSData));
    }
    
    public function getDisplaySoldDate() {
        return $this->getProperty('sold_date');
    }
    
    public function getDisplayLotSizeSqft() {
        $string = '<span class="amount">';
        $string .= GI_StringUtils::formatFloat($this->getProperty('lot_size_sqft'));
        $string .= ' <span class="unit right_unit">sq ft</span></span>';
        return $string;
    }
    
    /** @return \AbstractRECatalogView */
    public function getCatalogItemView() {
        $view = new RECatalogView($this);
        return $view;
    }
    
}
