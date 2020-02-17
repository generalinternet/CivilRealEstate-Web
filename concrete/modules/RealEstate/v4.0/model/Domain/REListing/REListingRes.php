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

        $sideBarForm = new GI_Form('real_estate_search');
        if($sideBarForm->wasSubmitted() && $sideBarForm->validate()){
            // $favourite = filter_input(INPUT_POST, 'favourites', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    
            $priceRangeMin = filter_input(INPUT_POST, 'price_min');
            if(!empty($priceRangeMin) && $priceRangeMin != 'NULL'){
                $dataSearch->filter($tableName.'.list_price', $priceRangeMin, '>=');
            }

            $priceRangeMax = filter_input(INPUT_POST, 'price_max');
            if(!empty($priceRangeMax) && $priceRangeMax != 'NULL'){
                $dataSearch->filter($tableName.'.list_price', $priceRangeMax, '<=');
            }

            $propertyTypes = filter_input(INPUT_POST, 'property_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if(!empty($propertyTypes)){
                // $newTableName = str_replace('bos_', '', $tableName);
                // $typeCol = $newTableName.'_type_id';
                // $typeTable = $newTableName.'_type';
                // $typeJoin = $dataSearch->createLeftJoin($typeTable, $typeCol, $newTableName , 'id');
                // $typeJoin->filterIn($typeTable.'.ref', $propertyTypes);
            }

            $searchAreaMin = filter_input(INPUT_POST, 'area_min');
            if(!empty($searchAreaMin) && $searchAreaMin != 'NULL'){
                $dataSearch->filter($tableName.'.lot_size_acres', $searchAreaMin, '>=');
                // $dataSearch
                //     ->filterGroup()
                //         ->filter($tableName.'.floor_area_total', 0, '!=')
                //         ->andIf()
                //         ->filter($tableName.'.floor_area_total', $searchAreaMin, '>=')
                //     ->closeGroup()
                //     ->orIf()
                //     ->filterGroup()
                //         ->filter($tableName.'.lot_size_acres', 0, '!=')
                //         ->andIf()
                //         ->filter($tableName.'.lot_size_acres', $searchAreaMin, '>=')
                //     ->closeGroup();
            }

            $searchAreaMax = filter_input(INPUT_POST, 'area_max');
            if(!empty($searchAreaMax) && $searchAreaMax != 'NULL'){
                $dataSearch->filter($tableName.'.lot_size_acres', $searchAreaMax, '<=');
                // $dataSearch
                //     ->filterGroup()
                //         ->filter($tableName.'.floor_area_total', 0, '!=')
                //         ->andIf()
                //         ->filter($tableName.'.floor_area_total', $searchAreaMax, '<=')
                //     ->closeGroup()
                //     ->orIf()
                //     ->filterGroup()
                //         ->filter($tableName.'.lot_size_acres', 0, '!=')
                //         ->andIf()
                //         ->filter($tableName.'.lot_size_acres', $searchAreaMax, '<=')
                //     ->closeGroup();
            }

            $datePosted = filter_input(INPUT_POST, 'date_posted');
            if(!empty($datePosted)){
                $compareDate = NULL;
                switch ($datePosted) {
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
                    $dataSearch->filter($tableName.'.inception', $compareDate, '>=');
                }
            }

            $features = filter_input(INPUT_POST, 'features', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if(!empty($features)){
                $dataSearch->filterGroup();
                foreach($features as $i => $feature){
                    $dataSearch->filterLike($tableName.'.amenities', '%'.$feature.'%');
                    if(isset($features[$i+1])){
                        $dataSearch->orIf();
                    }
                }
                $dataSearch->closeGroup();
            }
        }

        $sortBy = GI_URLUtils::getAttribute('sort');
        if(!empty($sortBy)){
            switch ($sortBy) {
                case 'low_to_high':
                    $dataSearch->orderBy($tableName.'.list_price', 'ASC');
                    break;
                
                case 'high_to_low':
                    $dataSearch->orderBy($tableName.'.list_price', 'DESC');
                    break;
                
                default:
                    break;
            }
        }
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return AbstractRESearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array(), GI_Form $form = null){
        $searchView = static::getSearchFormView($form, $dataSearch);
        return $searchView;
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
        $searchView = new RESearchFormView($form);
        return $searchView;
    }
}