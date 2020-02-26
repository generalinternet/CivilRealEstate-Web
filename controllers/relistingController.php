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
        
        $pageBarLinkArray = array(
            'controller' => 'relisting',
            'action' => 'index'
        );

        if(!empty($type)){
            $mlsSearch->filterByTypeRef($type);
            $search->filterByTypeRef($type);
            $pageBarLinkArray['type'] = $type;
        }

        $sampleListing  = REListingFactory::buildNewModel($type);
        
        $sampleListing->addCustomFiltersToDataSearch($mlsSearch);        
        $sampleListing->addCustomFiltersToDataSearch($search);

        $pageBarLinkProps = $attributes;

        $redirectArray = array();

        $filterForm = new GI_Form('real_estate_search');
        $searchForm = new GI_Form('search_bar');
        $sortByForm = new GI_Form('sort_by_form');

        $searchView = $sampleListing->getFullSearchForm($search, $mlsSearch, $type, $redirectArray, $filterForm, $searchForm, $sortByForm);
        $sampleListing->addSortingToDataSearch($search);

        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult
                ->setSampleModel($sampleListing)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);

        if(!GI_URLUtils::getAttribute('search')){
            $mlsListings = $mlsSearch->select();
            $reListings = $search->select();
            $listings = array_merge($reListings, $mlsListings);
            
            $pageBar = $search->getPageBar($pageBarLinkProps);
            $uiTableCols = $sampleListing->getUIRolodexCols();
            
            $catalogView = new UICatalogView($listings, $uiTableCols, $pageBar);
            $catalogView->setLoadLinksWithAJAX(false);
            
            $view = new REIndexView($listings, $catalogView, $sampleListing, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    //->setUITableView($uiTableView);
                    ->setUITableView($catalogView);
        }

        $returnArray = $actionResult->getIndexReturnArray();
        $interfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        return $returnArray;
    }

    public function actionOpenHouse($attributes){
        $attributes['openHouse'] = 1;
        return $this->actionIndex($attributes);
        $queryId = 'gi_1';

        /*items displayed per page*/
        $itemPerPage = 5;

        /*starting page number*/
        if(isset($attributes['pageNumber'])){
            $pageNumber = $attributes['pageNumber'];
        }else{
            $pageNumber = 1;
        }

        $type = 'res';
        $mlsListingTable = dbConfig::getDbPrefix() . 'mls_listing';
        $date = new DateTime();
        $ohExpiryDate = GI_Time::formatDateTime($date, 'datetime');

        $listingSearch = MLSListingFactory::search()
            ->filter('active', 1)
            ->filterTypeByRef('listing', $type)
            ->innerJoin('mls_open_house', 'mls_listing_id', $mlsListingTable, 'id', 'moh')
            ->filterGreaterOrEqualTo('moh.oh_end_date_time', $ohExpiryDate)
            ->groupBy('moh.mls_listing_id')
        ;

        $mlltrJoin = $listingSearch->createLeftJoin('mls_listing_link_to_realtor', 'mls_listing_id', $mlsListingTable, 'id', 'mlltr');
        $mlltrJoin->filter('mlltr.status', 1);

        $mrJoin = $listingSearch->createLeftJoin('mls_realtor', 'id', 'mlltr', 'mls_realtor_id', 'mr');
        $mrJoin->filterIn('mr.login', unserialize(RETS_REALTOR_IDS));

        $listingSearch->setAutoStatus(false);
        $listingSearch
            ->filter('status', 1)
            ->filter('moh.status', 1);

        $ourListingCase = $listingSearch->newCase();
        $ourListingCase->filterNotNull('MAX(mr.login)')
            ->setThen(1)
            ->setElse(0);
        
        $listingSearch->orderByCase($ourListingCase, 'DESC');
        
        $listingSearch
            ->orderBy('last_trans_date', 'DESC')
            ->setPageNumber($pageNumber)
            ->setItemsPerPage($itemPerPage)
            ->setQueryId($queryId)
        ;

        $sort = 'last_trans_date';
        $order = 'desc';
        $listingSearch->orderBy($sort, $order);
        $listings = $listingSearch->select();

        $pageBarLinkArray = array(
            'controller' => 'listing',
            'action' => 'openHouse',
            'type' => $type
        );
                
        $pageBar = $listingSearch->getPageBar($pageBarLinkArray);
        $sampleListing = MLSListingFactory::buildNewModel($type);
        $view = new MLSOpenHouseView($listings, $pageBar, $sampleListing);
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
}
