<?php
/**
 * Description of AbstractMLSListing
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListing extends GI_Model {
    
    public function save() {
        if(RETS_MODIFY_ROWS){
            return parent::save();
        }
        return false;
    }
    
    public function softDelete(){
        if(RETS_MODIFY_ROWS){
            return parent::softDelete();
        }
        return false;
    }
    
    public function markInactive(){
        $this->setProperty('active', 0);
        return $this->save();
    }
    
    protected $retsType = NULL;
    /**
     * [DB Column] => [RETS Field]
     * @var array 
     */
    protected static $importCols = array(
        'mls_number' => 'mls_number',
        'listing_id' => 'listing_id',
        'listing_status' => 'status',
        'list_price' => 'list_price',
        'mls_area_id' => 'area',
        'mls_city_id' => 'city',
        'mls_sub_area_id' => 'sub_area',
        'lot_size_acres' => 'lot_size_acres',
        'lot_size_hectares' => 'lot_size_hectares',
        'lot_size_sqft' => 'lot_size_sqft',
        'lot_size_sqm' => 'lot_size_sqm',
        'year' => 'year',
        'storeys_in_building' => 'storeys_in_building',
        'public_remarks' => 'public_remarks',
        'amenities' => 'amenities',
        'display_addr' => 'display_addr',
        'addr' => 'addr',
        'house_number' => 'house_number',
        'street_dir' => 'street_dir',
        'street_name' => 'street_name',
        'street_desig_id' => 'street_desig_id',
        'postal_code' => 'postal_code',
        'province' => 'province',
        'unit_number' => 'unit_number',
        'num_imgs' => 'num_imgs',
        'last_img_trans_date' => 'last_img_trans_date',
        'last_trans_date' => 'last_trans_date',
        'virtual_tour_url' => 'virtual_tour_url',
        'geo_lat' => 'geo_lat',
        'geo_lon' => 'geo_lon'
    );
    /**
     * [DB Column] => [RETS Field]
     * @var array 
     */
    protected static $addImportCols = array();
    /**
     * [RETS Field] => [Tag TypeRef]
     * @var array 
     */
    protected static $tagFields = array();
    
    protected static $storeImages = false;
    
    protected static $importOpenHouses = false;
    
    /**
     * @var AbstractMLSRealtor[] 
     */
    protected $realtors = NULL;
    
    /**
     * @var AbstractMLSFirm[] 
     */
    protected $firms = NULL;
    
    /**
     * @var AbstractMLSListingImage[]
     */
    protected $images = NULL;
    
    /**
     * @var AbstractMLSOpenHouse[]
     */
    protected $openHouses = NULL;
    
    /**
     * @var AbstractMLSCity
     */
    protected $mlsCity = NULL;
    
    /**
     * @var AbstractMLSArea
     */
    protected $mlsArea = NULL;
    
    /**
     * @var AbstractMLSSubArea
     */
    protected $mlsSubArea = NULL;
    
    /**
     * @var iTag[]
     */
    protected $tags = NULL;
    
    /**
     * @var AbstractREListing 
     */
    protected $reListing = NULL;
    
    protected static $unmodifyableColumns = array(
        'id',
        'inception',
        'status',
        'uid',
        'last_mod',
        'last_mod_by',
        'mls_listing_type_id',
        'mls_number',
        'listing_id',
        'last_img_trans_date',
        'last_trans_date'
    );
    
    public function getREListing(){
        if(is_null($this->reListing)){
            REListingFactory::resetDBType();
            $this->reListing = REListingFactory::getModelByMLSListingId($this->getProperty('id'));
        }
        return $this->reListing;
    }
    
    public function getRetsType(){
        return $this->retsType;
    }
    
    public function getMLSNumber(){
        return $this->getProperty('mls_number');
    }
    
    public static function getImportCols(){
        $importCols = array_merge(static::$importCols, static::$addImportCols);
        return $importCols;
    }
    
    public static function getTagFields(){
        return static::$tagFields;
    }
    
    public function storeImages(){
        return static::$storeImages;
    }
    
    public function importOpenHouses(){
        return static::$importOpenHouses;
    }
    
    /**
     * @param string $column
     * @param string $retsField
     * @param PHRETS\Models\Search\Record $record
     * @return \AbstractMLSListing
     */
    public function setPropertyFromRecord($column, $retsField, PHRETS\Models\Search\Record $record){
        switch($retsField){
            case 'area':
                $areaTitle = $record[GI_RETSField::getFieldId($retsField, $this->getTypeRef())];
                $areaRef = GI_Sanitize::ref($areaTitle);
                $mlsArea = MLSAreaFactory::getModelByRefOrCreate($areaRef, $areaTitle);
                if($mlsArea){
                    $this->setProperty($column, $mlsArea->getProperty('id'));
                } else {
                    $this->setProperty($column, NULL);
                }
                break;
            case 'city':
                $cityTitle = $record[GI_RETSField::getFieldId($retsField, $this->getTypeRef())];
                $cityRef = GI_Sanitize::ref($cityTitle);
                $mlsCity = MLSCityFactory::getModelByRefOrCreate($cityRef, $cityTitle);
                if($mlsCity){
                    $this->setProperty($column, $mlsCity->getProperty('id'));
                } else {
                    $this->setProperty($column, NULL);
                }
                break;
            case 'sub_area':
                $subAreaTitle = $record[GI_RETSField::getFieldId($retsField, $this->getTypeRef())];
                $subAreaRef = GI_Sanitize::ref($subAreaTitle);
                $mlsSubArea = MLSSubAreaFactory::getModelByRef($subAreaRef);
                if($mlsSubArea){
                    $this->setProperty($column, $mlsSubArea->getProperty('id'));
                } else {
                    $this->setProperty($column, NULL);
                }
                break;
            default:
                $this->setProperty($column, $record[GI_RETSField::getFieldId($retsField, $this->getTypeRef())]);
                break;
        }
        return $this;
    }
    
    /**
     * @param PHRETS\Models\Search\Record $record
     * @return \AbstractMLSListing
     */
    public function setPropertiesFromRecord(PHRETS\Models\Search\Record $record){
        //set active to 1 because if we have a record we know it's active
        $this->setProperty('active', 1);
        
        $importCols = static::getImportCols();
        
        foreach($importCols as $column => $retsField){
            $this->setPropertyFromRecord($column, $retsField, $record);
        }
        
        return $this;
    }
    
    /**
     * @return AbstractMLSListingImage[]
     */
    public function getImages(){
        if(is_null($this->images)){
            $this->images = MLSListingImageFactory::search()
                    ->filter('mls_listing_id', $this->getProperty('id'))
                    ->orderBy('pos', 'ASC')
                    ->select();
        }
        return $this->images;
    }
    
    public function getModifyImages(){
        $reListing = $this->getREListing();
        if(!empty($reListing)){
            $reImages = $reListing->getImages();
            
            return $reImages;
        }
        
        return null;
    }
    
    /**
     * @return AbstractMLSOpenHouse[]
     */
    public function getOpenHouses($pastOpenHouses = false){
        if(is_null($this->openHouses)){
            $openHouseSearch = MLSOpenHouseFactory::search()
                    ->filter('mls_listing_id', $this->getProperty('id'));
            
            if(!$pastOpenHouses){
                $date = new DateTime();
                //$date->sub(new DateInterval('P14D'));
                $ohExpiryDate = GI_Time::formatDateTime($date, 'datetime');
                $openHouseSearch->filterGreaterOrEqualTo('oh_start_date', $ohExpiryDate);
            }
            
            $this->openHouses = $openHouseSearch->select();
        }
        return $this->openHouses;
    }
    
    public function getTitle() {
        $title = $this->getProperty('addr');
        return $title;
    }
    
    /**
     * @return AbstractMLSCity
     */
    public function getCity(){
        if(is_null($this->mlsCity)){
            $this->mlsCity = MLSCityFactory::getModelById($this->getProperty('mls_city_id'));
        }
        
        return $this->mlsCity;
    }
    
    public function getCityTitle(){
        $city = $this->getCity();
        if($city){
            return $city->getTitle();
        }
        return NULL;
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
    
    public function getAreaTitle() {
        $area = $this->getArea();
        if($area){
            return $area->getTitle();
        }
        return NULL;
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
    
    public function getSubAreaTitle() {
        $subArea = $this->getSubArea();
        if($subArea){
            return $subArea->getTitle();
        }
        return NULL;
    }
    
    public function getAddress($breakLines = true){
        $addrStreet = $this->getProperty('addr');
        $addrCity = $this->getCityTitle();
        $addrRegion = $this->getProperty('province');
        $addrCode = $this->getProperty('postal_code');
        $addr = GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, NULL, $breakLines);
        return $addr;
    }
    
     /** @return AbstractTag[] */
    public function getTags($typeRef = NULL, $contextRef = NULL){
        $dbType = MLSListingFactory::getDBType();
        if(empty($typeRef) && empty($contextRef)){
            if(is_null($this->tags)){
                $this->tags = TagFactory::getByModel($this, false, $dbType);
            }
            return $this->tags;
        }
        return TagFactory::getByModel($this, false, $dbType, $typeRef, false, $contextRef);
    }
    
    /**
     * @return \MLSListingDetailView
     */
    public function getView() {
        $detailView = new MLSListingDetailView($this);
        return $detailView;
    }
    
    /**
     * @return \MLSListingItemView
     */
    public function getItemView() {
        $itemView = new MLSListingItemView($this);
        return $itemView;
    }
    
    public function getViewURLAttrs(){
        $urlAttributes = array(
            'controller' => 'mls',
            'action' => 'view',
            'id' => $this->getId(),
        );
        return $urlAttributes;
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Listing';
        if($plural){
            $title .= 's';
        }
        return $title;
    }
    
    public function getModifyURLAttrs(){
        $urlAttributes = array(
            'controller' => 're',
            'action' => 'add',
            'type' => MLSListingFactory::getPTypeRef($this->getTypeRef()).'_mod',
            'mlsId' => $this->getId(),
        );
        return $urlAttributes;
    }
    
    public function getModifyURL(){
        return GI_URLUtils::buildURL($this->getModifyURLAttrs());
    }
    
    public function getBreadcrumbs($listingTypeRef = NULL) {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'mls',
            'action' => 'index'
        ));
        $breadcrumbs[] = array(
            'label' => 'All Listings',
            'link' => $bcIndexLink
        );
        if (empty($listingTypeRef)) {
            $listingTypeRef = $this->getRetsType();
        }
        if(!empty($listingTypeRef)){
            $bcLink = GI_URLUtils::buildURL(array(
                'controller' => 'mls',
                'action' => 'index',
                'type' => $listingTypeRef
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $bcLink
            );
        }
        $listingId = $this->getProperty('id');
        if (!is_null($listingId)) {
            $breadcrumbs[] = array(
                'label' => $this->getMLSNumber(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Image',
                'method_name' => 'getCoverImageHTML',
                'css_class'=> 'thumb_img',
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
    
    public static function getUITableColsToModify() {
        $tableColArrays = array(
            array(
                'header_title' => 'Image',
                'method_name' => 'getCoverImageHTML',
                'css_class'=> 'img_thumb',
                'cell_url_method_name' => 'getModifyURL',
            ),
            array(
                'header_title' => 'Address',
                'method_attributes'=>'addr',
                'cell_url_method_name' => 'getModifyURL',
            ),
            array(
                'header_title' => 'List Price',
                'method_name' => 'getDisplayListPrice',
                'cell_url_method_name' => 'getModifyURL',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
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
     * @return \MLSSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchView = new MLSSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        $form = new GI_Form('mls_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'mls',
                    'action' => 'index',
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                } else {
                    $redirectArray['type'] = 'res';
                }
                if(GI_URLUtils::getAttribute('refs')){
                    $redirectArray['refs'] = GI_URLUtils::getAttribute('refs');
                } else {
                    $redirectArray['refs'] = 'active';
                }
                
                if(GI_URLUtils::getAttribute('targetId')){
                    $redirectArray['targetId'] = GI_URLUtils::getAttribute('targetId');
                } else {
                    $redirectArray['targetId'] = 'list_bar';
                }
                if(GI_URLUtils::getAttribute('modify')){
                    $redirectArray['modify'] = GI_URLUtils::getAttribute('modify');
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['newUrlRedirect'] = 1;
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
        $dataSearch->filterGroup();
        //Address
        static::addAddressFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->orIf();
        
        //MLS Number
        static::addMLSNumberToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->orIf();

        $dataSearch->closeGroup();
        
        $dataSearch->andIf();
        $dataSearch->groupBy('id');
    }
    
    public static function addLocationFilterToDataSearch($location, GI_DataSearch $dataSearch){
        $dataSearch->filterGroup();
        //Address
        static::addAddressFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //Area
        static::addAreaFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //City
        static::addCityFilterToDataSearch($location, $dataSearch);
        $dataSearch->orIf();
        
        //MLS Number
        static::addMLSNumberToDataSearch($location, $dataSearch);
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
            $dataSearch->filterIn('id', $mlsListingIds);
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
    
    public function getCoverImage(){
        $reImages = $this->getModifyImages();
        if(!empty($reImages)){
            return $reImages[0];
        }
        else{
            if(is_null($this->images)){
                $imageResult = MLSListingImageFactory::search()
                        ->filter('mls_listing_id', $this->getProperty('id'))
                        ->orderBy('pos', 'ASC')
                        ->setItemsPerPage(1)
                        ->select();
            } else {
                $imageResult = $this->images;
            }
            if(isset($imageResult[0])){
                return $imageResult[0];
            }
        }
    }
    
    public function getCoverImageHTML($width = 320, $height = 214, $keepRatio = true, $startWrapHTML= '', $endWrapHTML = ''){
        $image = $this->getCoverImage();
        if (!empty($image)) {
            return $this->getImageHTML($image, $width, $height, $keepRatio, $startWrapHTML, $endWrapHTML);
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
            return $startWrapHTML.'<div class="img_wrap"><img src="'.$imageURL.'" alt="'.$imageTitle.'" title="'.$imageTitle.'" /></div>'.$endWrapHTML;
        }
    }
    
    /**
     * @return AbstractREListingStatus
     */
    public function getListingStatus(){
        $reListing = $this->getREListing();
        if($reListing){
            return $reListing->getListingStatus();
        }
        
        return NULL;
    }
    
    public function getListingStatusTitle(){
        $listingStatus = $this->getListingStatus();
        if($listingStatus){
            return $listingStatus->getTitle();
        }
        
        if($this->getRealProperty('active')){
            return 'Active';
        }
        
        return 'Inactive';
    }
    
     public function getListingStatusRef(){
        $listingStatus = $this->getListingStatus();
        if($listingStatus){
            return $listingStatus->getRef();
        }
        
        if($this->getRealProperty('active')){
            return 'active';
        }
        
        return 'inactive';
    }
    
    /**
     * @param string $typeRef
     * @return iTag[]
     */
    public function getTagsByType($typeRef){
        $dbType = MLSListingFactory::getDBType();
        $tagTable = dbConfig::getDbPrefix($dbType) . 'tag';
        
        TagFactory::setDBType($dbType);
        $tags = TagFactory::search()
                ->join('item_link_to_tag', 'tag_id', $tagTable, 'id', 'TL')
                ->filterByTypeRef($typeRef)
                ->filter('TL.item_id', $this->getProperty('id'))
                ->filter('TL.table_name', $this->getTableName())
                ->select();
        TagFactory::resetDBType();
        return $tags;
    }
    
//    public function getDwellingTypeTitle(){
//        $dwellingTags = $this->getTagsByType('mls_dwelling');
//        
//        if($dwellingTags){
//            $dwellingTag = $dwellingTags[0];
//            return $dwellingTag->getProperty('title');
//        }
//        
//        return NULL;
//    }
//    
//    public function getComTypeTitle() {
//        $comTypeTags = $this->getTagsByType('mls_com_prop_type');
//        
//        if($comTypeTags){
//            $comTypeTag = $comTypeTags[0];
//            return $comTypeTag->getProperty('title');
//        }
//        
//        return NULL;
//    }
    
    /**
     * @return AbstractMLSRealtor[]
     */
    public function getRealtors(){
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
    
    public static function columnIsModifyable($column){
        if(in_array($column, static::$unmodifyableColumns)){
            return false;
        }
        return true;
    }
    
    public function getRealProperty($key){
        return parent::getProperty($key);
    }
    
    public function getProperty($key, $original = false) {
        $property = NULL;
        $reListing = NULL;
        if(static::columnIsModifyable($key)){
            $reListing = $this->getREListing();
            if($reListing){
                $property = $reListing->getProperty($key);
            }
        }
        if(!$reListing || empty($property)){
            $property = parent::getProperty($key);
        }
        return $property;
    }
    
    public function getPublicRemarks(){
        return $this->getProperty('public_remarks');
    }
    
    /**
     * Get limited remarks and if edited MLS listings have no edited price, show MLS listing remarks.
     * @todo: move this into AbstractREListing
     * @return string
     */
    public function getDisplayPublicRemarks($limit = 200) {
        $publicRemarks = $this->getPublicRemarks();
        return GI_StringUtils::summarize($publicRemarks, $limit);
    }
    
    public function getDisplayPriceHTML($price) {
        $string = '<span class="unit">$</span><span class="amount">';
        $string .= GI_StringUtils::formatMoney($price, true, 0);
        $string .= '</span>';
        return $string;
    }
    
    public function getListPrice(){
        return $this->getProperty('list_price');
    }
    
    public function getDisplayListPrice() {
        return $this->getDisplayPriceHTML($this->getProperty('list_price'));
    }
    
    public function getSoldPrice(){
        return $this->getProperty('sold_price');
    }
    
    public function getDisplaySoldPrice() {
        return $this->getDisplayPriceHTML($this->getProperty('sold_price'));
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
