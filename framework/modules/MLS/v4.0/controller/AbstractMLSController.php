<?php
/**
 * Description of AbstractMLSController
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSController extends GI_Controller {
        
    public function actionIndex($attributes){
        if (!isset($attributes['type'])) {
            $type = 'res';
        } else {
            $type = $attributes['type'];
        }
        $sampleModel = MLSListingFactory::buildNewModel($type);
        if (!$sampleModel->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }

        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }
        $dataSearch = MLSListingFactory::search()
                ->filterByTypeRef($type)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        if (isset($attributes['active'])) {
            $dataSearch->filter('active', 1);
        }
        $sampleModel->addCustomFiltersToDataSearch($dataSearch);
        $pageBarLinkProps = $attributes;
        $redirectArray = array();
        $searchView = $sampleModel->getSearchForm($dataSearch, $type, $redirectArray);
        $sampleModel->addSortingToDataSearch($dataSearch);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleModel)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $models = $dataSearch->select();
            $pageBar = $dataSearch->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols =  $sampleModel->getUIRolodexCols();
                $uiTableView = new UIRolodexView($models, $uiTableCols, $pageBar);
                if(GI_URLUtils::getAttribute('modify')){
                    $uiTableView->setGetURLMethod('getUITableColsToModify');
                } else {
                    $uiTableView->setGetURLMethod('getViewURL');

                }
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                if(GI_URLUtils::getAttribute('modify')){
                    $uiTableCols = $sampleModel->getUITableColsToModify();
                } else {
                    $uiTableCols = $sampleModel->getUITableCols();
                }
                $uiTableView = new UITableView($models, $uiTableCols, $pageBar);
            }
            $view = new MLSIndexView($models, $uiTableView, $sampleModel, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        $returnArray = $actionResult->getIndexReturnArray();
        return $returnArray;
    }
    
    public function actionAutocompMLSCity($attributes){
        $attributes['type'] = 'city';
        return $this->actionAutocompMLS($attributes);
    }
    
    public function actionAutocompMLSArea($attributes){
        $attributes['type'] = 'area';
        return $this->actionAutocompMLS($attributes);
    }
    
    public function actionAutocompMLSSubArea($attributes){
        $attributes['type'] = 'subarea';
        return $this->actionAutocompMLS($attributes);
    }
    
    public function actionAutocompMLS($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        
        if (!isset($attributes['type'])){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        
        $factoryClassName = '';
        switch ($attributes['type']) {
            case 'city':
                $factoryClassName = 'MLSCityFactory';
                break;
            case 'area':
                $factoryClassName = 'MLSAreaFactory';
                break;
            case 'subarea':
                $factoryClassName = 'MLSSubAreaFactory';
                break;
            default:
                $factoryClassName = 'MLSCityFactory';
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $itemId){
                $item = $factoryClassName::getModelById($itemId);
                if($item){
                    $name = $item->getTitle();

                    $finalLabel[] = $name;
                    $finalValue[] = $itemId;
                    $finalResult[] = '<span class="result_text">'.$name.'</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
      
            $dataSearch = $factoryClassName::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $dataSearch->setPageNumber($pageNumber);
            }
            
            if(!empty($term)){
                $dataSearch->filterTermsLike('title', $term)
                        ->orderByLikeScore('title', $term);
            }
            
            $items = $dataSearch->select();
            $results = array();

            foreach($items as $item){
                $name = $item->getTitle();

                $itemInfo = array(
                    'label' => $name,
                    'value' => $item->getId(),
                    'autoResult' => '<span class="result_text">'.$this->markTerm($term, $name).'</span>'
                );

                $results[] = $itemInfo;
            }

            $itemsPerPage = $dataSearch->getItemsPerPage();
            $count = $dataSearch->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            return $results;
        }
    }
    
    protected function addMoreResult(GI_DataSearch $dataSearch, &$results){
        $itemsPerPage = $dataSearch->getItemsPerPage();
        $count = $dataSearch->getCount();
        if (!empty($itemsPerPage) && $count > $itemsPerPage) {
            $results[] = array(
                'preventDefault' => 1,
                'liClass' => 'more_results',
                'autoResult' => '&hellip;'
            );
        }
    }
    
    protected function markTerm($term, $result) {
        if (!empty($term)) {
            return preg_replace('/' . $term . '/i', "<mark>\$0</mark>", $result);
        }
        return $result;
    }
}
