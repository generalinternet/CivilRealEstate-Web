<?php

use function Aws\filter;

class REListingRes extends AbstractREListingRes {

    /******SEARCH******/
    /**
     * @param GI_DataSearch $dataSearch
     * @return \GI_DataSearch
     */
    public function addUIFiltersToDataSearch(GI_DataSearch &$dataSearch, $tableName) {
        $searchBarForm = new GI_Form('search_bar');

        if($searchBarForm->wasSubmitted() && $searchBarForm->validate()){
            $keyword = filter_input(INPUT_POST, 'keyword');
            if(!empty($keyword)){
                $dataSearch->filterGroup()
                    ->filterLike($tableName.'.addr', '%'.$keyword.'%')
                    ->orIf()
                    ->filterLike($tableName.'.province', '%'.$keyword.'%')
                    ->orIf()
                    ->filterLike($tableName.'.postal_code', '%'.$keyword.'%')
                    ->closeGroup();
            }
        }
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
                    'controller' => 'relisting',
                    'action' => 'index'
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            $redirectArray['queryId'] = $queryId;
            GI_URLUtils::redirect($redirectArray);

        }
        return $searchView;
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
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
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){

            $priceMin = filter_input(INPUT_POST, 'search_price_min');
            $dataSearch->setSearchValue('price_min', $priceMin);

            $priceMax = filter_input(INPUT_POST, 'search_price_max');
            $dataSearch->setSearchValue('price_max', $priceMax);

            $propertyType = filter_input(INPUT_POST, 'search_property_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $dataSearch->setSearchValue('property_type', $propertyType);

            $searchAreaMin = filter_input(INPUT_POST, 'search_area_min');
            $dataSearch->setSearchValue('floor_area', $searchAreaMin);

            $searchAreaMax = filter_input(INPUT_POST, 'search_area_max');
            $dataSearch->setSearchValue('floor_area', $searchAreaMax);

        }

        return true;
    }
}