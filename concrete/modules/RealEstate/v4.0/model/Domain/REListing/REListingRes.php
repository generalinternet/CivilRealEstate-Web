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

        $reSearchForm = new GI_Form('real_estate_search');
        if($reSearchForm->wasSubmitted() && $reSearchForm->validate()){
            $propertyTypes = filter_input(INPUT_POST, 'property_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if(!empty($propertyTypes)){
                $reListingResTypeName = REListingFactory::getDbPrefix().'re_listing_res_type';
                $dataSearch->join($reListingResTypeName, $tableName.'.re_listing_type_id', $reListingResTypeName, 'id', 'left');
                $dataSearch->filterIn($reListingResTypeName.'.title', $propertyTypes);
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

}