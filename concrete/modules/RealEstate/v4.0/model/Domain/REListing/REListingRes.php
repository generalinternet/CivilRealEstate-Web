<?php

use function Aws\filter;

class REListingRes extends AbstractREListingRes {

    protected static $resTableAlias = array(
        'client' => 'rlRes',
        'rets' => 'mlRes'
    );

    protected static $resTypeTableAlias = array(
        'client' => 'rlResType',
        'rets' => 'mlResType'
    );

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        // Keywords
        $keyword = $dataSearch->getSearchValue('keyword');
        if(!empty($keyword) && $keyword != 'NULL'){
            static::addKeywordFilterToDataSearch($dataSearch, $keyword);
        }
        // Favourite
        // TODO:
        // Bedrooms
        $bedroomMin = $dataSearch->getSearchValue('bedroom_min');
        $bedroomMax = $dataSearch->getSearchValue('bedroom_max');
        if((!empty($bedroomMin) && $bedroomMin != 'NULL') || (!empty($bedroomMax) && $bedroomMax != 'NULL')){
            static::addBedroomRangeFilterToDataSearch($dataSearch, $bedroomMin, $bedroomMax);
        }
        // Bathrooms
        $bathroomMin = $dataSearch->getSearchValue('bathroom_min');
        $bathroomMax = $dataSearch->getSearchValue('bathroom_max');
        if((!empty($bathroomMin) && $bathroomMin != 'NULL') || (!empty($bathroomMax) && $bathroomMax != 'NULL')){
            static::addBathroomRangeFilterToDataSearch($dataSearch, $bathroomMin, $bathroomMax);
        }
        // Price
        $priceMin = $dataSearch->getSearchValue('price_min');
        $priceMax = $dataSearch->getSearchValue('price_max');
        if((!empty($priceMin) && $priceMin != 'NULL') || (!empty($priceMax) && $priceMax != 'NULL')){
            static::addPriceFilterToDataSearch($dataSearch, $priceMin, $priceMax);
        }
        // Property Type
        $propertyTypes = $dataSearch->getSearchValue('property_type');
        if(!empty($propertyTypes) && $propertyTypes != 'NULL'){
            static::addPropertyTypeFilterToDataSearch($propertyTypes, $dataSearch);
        }
        // Area
        $areaMin = $dataSearch->getSearchValue('area_min');
        $areaMax = $dataSearch->getSearchValue('area_max');
        if((!empty($areaMin) && $areaMin != 'NULL') || (!empty($areaMax) && $areaMax != 'NULL')){
            static::addFloorAreaRangeFilterToDataSearch($dataSearch, $areaMin, $areaMax);
        }
        // Date Posted
        $datePosted = $dataSearch->getSearchValue('date_posted');
        if(!empty($datePosted) && $datePosted != 'NULL'){
            static::addDatePostedFilterToDataSearch($datePosted, $dataSearch);
        }
        
        return true;
    }

    public static function addBedroomRangeFilterToDataSearch(GI_DataSearch $dataSearch, $bedroomMin, $bedroomMax){
        $dbType = $dataSearch->getDBType();
        if(!empty($bedroomMin) && $bedroomMin != 'NULL'){
            $dataSearch->filterGreaterOrEqualTo(self::$resTableAlias[$dbType].'.total_bedrooms', $bedroomMin);
        }
        if(!empty($bedroomMax) && $bedroomMax != 'NULL'){
            $dataSearch->filterGreaterOrEqualTo(self::$resTableAlias[$dbType].'.total_bedrooms', $bedroomMax);
        }
    }

    public static function addBathroomRangeFilterToDataSearch(GI_DataSearch $dataSearch, $bathroomMin, $bathroomMax){
        $dbType = $dataSearch->getDBType();
        $tableName = $dataSearch->getTableName();
        if(!empty($bathroomMin) && $bathroomMin != 'NULL'){
            $dataSearch->filterGreaterOrEqualTo(self::$resTableAlias[$dbType].'.total_baths', $bathroomMin);
        }
        if(!empty($bathroomMax) && $bathroomMax != 'NULL'){
            $dataSearch->filterGreaterOrEqualTo(self::$resTableAlias[$dbType].'.total_baths', $bathroomMax);
        }
    }

    public static function addDatePostedFilterToDataSearch($postedDate, GI_DataSearch $dataSearch){
        $tableName = $dataSearch->getTableName();
        if(empty($postedDate)){
            return;
        }

        $compareDate = NULL;
        $compareChar = '>=';

        switch ($postedDate) {
            case 'older_than_1_month':
                $compareDate = date('Y-m-d H:i:s', strtotime('-4 week'));
                $compareDate = '<';
                break;
            case 'last_four_weeks':
                $compareDate = date('Y-m-d H:i:s', strtotime('-4 week'));
                break;
            case 'last_three_weeks':
                $compareDate = date('Y-m-d H:i:s', strtotime('-3 week'));
                break;
            case 'last_two_weeks':
                $compareDate = date('Y-m-d H:i:s', strtotime('-2 week'));
                break;
            case 'last_week':
                $compareDate = date('Y-m-d H:i:s', strtotime('-1 week'));
                break;
            
            default:
                break;
        }

        if(!empty($compareDate)){
            $dataSearch->filter($tableName.'.inception', $compareDate, $compareChar);
        }
    }

    public static function addFloorAreaRangeFilterToDataSearch(GI_DataSearch $dataSearch, $areaMin, $areaMax){
        $tableName = $dataSearch->getTableName();
        if(!empty($areaMin) && $areaMin != 'NULL'){
            $dataSearch->filter($tableName.'.lot_size_acres', $areaMin, '>=');
        }
        if(!empty($areaMax) && $areaMax != 'NULL'){
            if($areaMax != '5000+'){
                $dataSearch->filterLessOrEqualTo($tableName.'.lot_size_acres', $areaMax);
            }else{
                $dataSearch->filterGreaterThan($tableName.'.lot_size_acres', $areaMax);
            }
        }
    }

    public static function addPriceFilterToDataSearch(GI_DataSearch $dataSearch, $priceMin = NULL, $priceMax = NULL){
        $tableName = $dataSearch->getTableName();
        if(!empty($priceMin) && $priceMin != 'NULL') {
            $dataSearch->filterGreaterOrEqualTo($tableName.'.list_price', $priceMin);
        }
        
        if(!empty($priceMax) && $priceMax != 'NULL') {
            $dataSearch->filterLessOrEqualTo($tableName.'.list_price', $priceMax);
        }
    }

    public static function addPropertyTypeFilterToDataSearch($propertyTypeRefs, GI_DataSearch $dataSearch){
        if(empty($propertyTypeRefs)) {
            return;
        }
        $dbType = $dataSearch->getDBType();
        $dataSearch->filterIn(self::$resTypeTableAlias[$dbType].'.ref', $propertyTypeRefs);
    }
    
    public static function addKeywordFilterToDataSearch(GI_DataSearch $dataSearch, $keyword = NULL){
        $tableName = $dataSearch->getTableName();

        $mlsListing = MLSListingFactory::getModelByMLSNumber($keyword);
        if(!empty($mlsListing)){
            GI_URLUtils::redirect($mlsListing->getViewURLAttrs());
        }

        $dataSearch->filterGroup()
            ->filterLike($tableName.'.addr', '%'.$keyword.'%')
            ->orIf()
            ->filterLike($tableName.'.province', '%'.$keyword.'%')
            ->orIf()
            ->filterLike($tableName.'.postal_code', '%'.$keyword.'%')
            ->orIf()
            ->filterLike($tableName.'.amenities', '%'.$keyword.'%');
        
        if($dataSearch->getDBType() == 'rets'){
            $dataSearch->leftJoin( 'mls_city', 'id', REListingFactory::getDbPrefix().$tableName, 'mls_city_id', 'lct');
            $dataSearch
                ->orIf()
                ->filterLike('lct.title', '%'.$keyword.'%');
            $dataSearch->closeGroup();
            $dataSearch
                ->andIf()
                ->filter('lct.status', 1);
        }else{
            $dataSearch->closeGroup();
        }
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArrayrelistingSearch
     * @return AbstractRESearchFormView
     */
    public function getFullSearchForm(GI_DataSearch $relistingSearch, GI_DataSearch $mlsListingSearch = NULL, $type = NULL, &$redirectArray = array(), GI_Form $filterForm, GI_Form $searchForm){
        $searchView = static::getSearchFormView($filterForm, $relistingSearch);
        
        static::filterSearchForm($relistingSearch);
        static::filterSearchForm($mlsListingSearch);

         $isSubmitted = false;
        
        if(!is_null($filterForm) && $filterForm->wasSubmitted() && $filterForm->validate()){
            $isSubmitted = true;

            $bedroomMin = filter_input(INPUT_POST, 'bedroom_min');
            $relistingSearch->setSearchValue('bedroom_min', $bedroomMin);

            $bedroomMax = filter_input(INPUT_POST, 'bedroom_max');
            $relistingSearch->setSearchValue('bedroom_max', $bedroomMax);

            $bathroomMin = filter_input(INPUT_POST, 'bathroom_min');
            $relistingSearch->setSearchValue('bathroom_min', $bathroomMin);

            $bathroomMax = filter_input(INPUT_POST, 'bathroom_max');
            $relistingSearch->setSearchValue('bathroom_max', $bathroomMax);

            $priceMin = filter_input(INPUT_POST, 'price_min');
            $relistingSearch->setSearchValue('price_min', $priceMin);
            $priceMax = filter_input(INPUT_POST, 'price_max');
            $relistingSearch->setSearchValue('price_max', $priceMax);

            $propertyType = filter_input(INPUT_POST, 'property_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $relistingSearch->setSearchValue('property_type', $propertyType);

            $areaMin = filter_input(INPUT_POST, 'area_min');
            $relistingSearch->setSearchValue('area_min', $areaMin);
            $areaMax = filter_input(INPUT_POST, 'area_max');
            $relistingSearch->setSearchValue('area_max', $areaMax);

            $datePosted = filter_input(INPUT_POST, 'date_posted');
            $relistingSearch->setSearchValue('date_posted', $datePosted);
        }
        if((!is_null($searchForm) && $searchForm->wasSubmitted() && $searchForm->validate())){
            $isSubmitted = true;

            $keyword = filter_input(INPUT_POST, 'keyword');
            $relistingSearch->setSearchValue('keyword', $keyword);
        }
        
        if($isSubmitted){
            $queryId = $relistingSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'relisting',
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

}