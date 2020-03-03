<?php

require_once 'framework/modules/RealEstate/' . MODULE_REALESTATE_VER . '/controller/AbstractREListingController.php';

class REListingController extends AbstractREListingController {

    public function actionIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'res';
        }
        
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        
        if(isset($attributes['queryId'])){
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        $itemPerPage = ProjectConfig::getUITableItemsPerPage();
        
        $search = REListingFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage($itemPerPage)
                ->setQueryId($queryId);
        
        $mlsSearch = MLSListingFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage($itemPerPage)
                ->setQueryId($queryId);
        
        if(isset($attributes['openHouse']) && $attributes['openHouse'] == 1){
            $search->setSearchValue('openHouse', true, true);
            $mlsSearch->setSearchValue('openHouse', true, true);
        }
        
        $search->filterNull('mls_listing_id');
        $reListingTable = REListingFactory::getDbPrefix() . 're_listing';
        $search->leftJoin('re_listing_res', 'parent_id', $reListingTable, 'id', 'rlRes');
        $search->leftJoin('re_listing_res_type', 'id', 'rlRes', 're_listing_res_type_id', 'rlResType');
        $search->innerJoin('re_listing_status', 'id', $reListingTable, 're_listing_status_id', 'rls')
                ->filter('rls.active', 1);
        
        $reCount = $search->count();
        
        $mlsListingTable = MLSListingFactory::getDbPrefix() . 'mls_listing';
        $mlsSearch->leftJoin('mls_listing_res', 'parent_id', $mlsListingTable, 'id', 'mlRes');
        $mlsSearch->leftJoin('mls_listing_res_type', 'id', 'mlRes', 'mls_listing_res_type_id', 'mlResType');
        $mlsSearch->filter('active', 1);
        $mlsSearch->setOffsetRowCount($reCount);
        
        if(!empty(RETS_REALTOR_IDS)){
            $realtorIds = unserialize(RETS_REALTOR_IDS);
            if(!empty($realtorIds)){
                //Show our listings first
                $mlltrJoin = $mlsSearch->createLeftJoin('mls_listing_link_to_realtor', 'mls_listing_id', $mlsListingTable, 'id', 'mlltr');
                $mlltrJoin->filter('mlltr.status', 1);

                $mrJoin = $mlsSearch->createLeftJoin('mls_realtor', 'id', 'mlltr', 'mls_realtor_id', 'mr');
                $mrJoin->filterIn('mr.login', unserialize(RETS_REALTOR_IDS));
                $mlsSearch->ignoreStatus('mr');

                $ourListingCase = $mlsSearch->newCase();
                $ourListingCase->filterNotNull('MAX(mr.login)')
                        ->setThen(1)
                        ->setElse(0);
                $mlsSearch->orderByCase($ourListingCase, 'DESC');
            }
        }
        
        $pageBarLinkProps = array(
            'controller' => 'relisting',
            'action' => $attributes['action'],
        );

        if(!empty($type)){
            $mlsSearch->filterByTypeRef($type);
            $search->filterByTypeRef($type);
            $pageBarLinkProps['type'] = $type;
        }

        $sampleListing  = REListingFactory::buildNewModel($type);
        
        $sampleListing->addCustomFiltersToDataSearch($mlsSearch);        
        $sampleListing->addCustomFiltersToDataSearch($search);

        $filterForm = new GI_Form('real_estate_search');
        $searchForm = new GI_Form('search_bar');
        $sortByForm = new GI_Form('sort_by_form');

        $searchView = $sampleListing->getFullSearchForm($search, $mlsSearch, $type, $pageBarLinkProps, $filterForm, $searchForm, $sortByForm);
        $sampleListing->addSortingToDataSearch($search);

        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult
                ->setSampleModel($sampleListing)
                ->setUseAjax(true)
                ->setRedirectArray($pageBarLinkProps);

        if(!GI_URLUtils::getAttribute('search')){
            $mlsListings = $mlsSearch->select();
            $reListings = $search->select();
            $listings = array_merge($reListings, $mlsListings);

            $isOpenHouse = (isset($attributes['openHouse']) && $attributes['openHouse'] == 1);
            
            $pageBar = REListingFactory::getUnionPageBar($search, $mlsSearch, $pageBarLinkProps);
            $uiTableCols = $sampleListing->getUIRolodexCols();
            
            $catalogView = new REUICatalogView($listings, $uiTableCols, $pageBar, $isOpenHouse);
            $catalogView->setLoadLinksWithAJAX(false);
            
            // TODO: is openhouse
            $view = new REIndexView($listings, $catalogView, $sampleListing, $searchView, $pageBar);
            $view->setIsOpenHouse($isOpenHouse);
            
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($catalogView);
        }

        $returnArray = $actionResult->getIndexReturnArray();
        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();

        // set last listing list
        SessionService::setValue('last_listing_list_url', GI_URLUtils::getAttributes());

        return $returnArray;
    }

    public function actionOpenHouse($attributes){
        $attributes['openHouse'] = 1;
        return $this->actionIndex($attributes);
    }
}
