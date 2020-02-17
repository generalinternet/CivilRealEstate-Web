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
        
        $search->filterNull('mls_listing_id');
        $reListingTable = REListingFactory::getDbPrefix() . 're_listing';
        $search->innerJoin('re_listing_status', 'id', $reListingTable, 're_listing_status_id', 'rls')
                ->filter('rls.active', 1);
        
        $reCount = $search->count();
        
        $mlsListingTable = MLSListingFactory::getDbPrefix() . 'mls_listing';
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
        $sampleListing->addUIFiltersToDataSearch($mlsSearch, $mlsListingTable);

        $sampleListing->addCustomFiltersToDataSearch($search);
        $sampleListing->addUIFiltersToDataSearch($search, $reListingTable);

        $pageBarLinkProps = $attributes;

        $redirectArray = array();

        $filterForm = new GI_Form('real_estate_search');
        $searchView = $sampleListing->getSearchForm($search, $type, $redirectArray, $filterForm);
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

}
