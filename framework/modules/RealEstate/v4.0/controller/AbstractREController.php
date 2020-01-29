<?php
/**
 * Description of AbstractREController
 * Back-end controller
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractREController extends GI_Controller {
        
    public function actionIndex($attributes){
        if (!isset($attributes['type'])) {
            $type = 'res';
        } else {
            $type = $attributes['type'];
        }
        $sampleModel = REListingFactory::buildNewModel($type);
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
        $dataSearch = REListingFactory::search()
                ->filterByTypeRef($type, false)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
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
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                $uiTableCols = $sampleModel->getUITableCols();
                $uiTableView = new UITableView($models, $uiTableCols, $pageBar);
            }
            
            $view = new REIndexView($models, $uiTableView, $sampleModel, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        $returnArray = $actionResult->getIndexReturnArray();
        return $returnArray;
    }
    public function actionAdd($attributes){
        if (!isset($attributes['type'])) {
            $type = 'res';
        } else {
            $type = $attributes['type'];
        }

        $reListing = REListingFactory::buildNewModel($type);
        if (!$reListing->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (isset($attributes['mlsId'])) {
            $mlsId = $attributes['mlsId'];
           
            $mlsListing = MLSListingFactory::getModelById($mlsId);
            if (!empty($mlsListing)) {
                $reListing->setMLSListing($mlsListing);
            }
        }
        $form = new GI_Form('add_form');
        $buildForm = true;
        $view = $reListing->getFormView($form, $buildForm);
        if(GI_URLUtils::isAJAX()){
            $view->setAddWrap(false);
        }
        $success = 0;
        if ($reListing->handleFormSubmission($form)) {
            $success = 1;
            $reListingId = $reListing->getId();
            $viewURLAttrs = $reListing->getViewURLAttrs();
            LogService::logAdd($reListing, $reListing->getViewTitle(false) . ' - ' . $reListing->getTitle());
            LogService::setIgnoreNextLogView(true);
            if(GI_URLUtils::isAJAX()){
                //Change the view to a detail view
                $view = $reListing->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($viewURLAttrs);
            } else {
                GI_URLUtils::redirect($viewURLAttrs);
            }
        }
        $returnArray = static::getReturnArray($view);
        $returnArray['breadcrumbs'] = $reListing->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Add',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        if(GI_URLUtils::isAJAX()){
            $returnArray['success'] = $success;
            if ($success) {
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$reListingId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    public function actionEdit($attributes){
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $reListing = REListingFactory::getModelById($id);
        if (empty($reListing)) {
            GI_URLUtils::redirectToError(4001);
        }
        if(!$reListing->isEditable()){
            GI_URLUtils::redirectToAccessDenied();
        }

        $form = new GI_Form('edit_form');
        $buildForm = true;
        $view = $reListing->getFormView($form, $buildForm);
        if(GI_URLUtils::isAJAX()){
            $view->setAddWrap(false);
        }
        $success = 0;
        if ($reListing->handleFormSubmission($form)) {
            $success = 1;
            $viewURLAttrs = $reListing->getViewURLAttrs();
            //@toto test log service
            LogService::logEdit($reListing, $reListing->getViewTitle(false) . ' - ' . $reListing->getTitle());
            LogService::setIgnoreNextLogView(true);
            if(GI_URLUtils::isAJAX()){
                //Change the view to a detail view
                $view = $reListing->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($viewURLAttrs);
            } else {
                GI_URLUtils::redirect($viewURLAttrs);
            }
        }
        $returnArray = static::getReturnArray($view);
        $returnArray['breadcrumbs'] = $reListing->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Edit',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        if(GI_URLUtils::isAJAX()){
            $returnArray['success'] = $success;
            if ($success) {
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$reListing->getId().');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $reListing = REListingFactory::getModelById($id);
        if (empty($reListing)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$reListing->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = $reListing->getDetailView();
        
        //@toto Log Service
        LogService::logView($reListing, $reListing->getViewTitle(false) . ': ' . $reListing->getTitle());
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $reListing->getBreadcrumbs();
        return $returnArray;
    }
    
    public function actionDelete($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $reListing = REListingFactory::getModelById($id);
        if (empty($reListing)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$reListing->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $redirectProps = array(
            'controller' => 're',
            'action' => 'index'
        );
        
        if(isset($attributes['targetId'])){
            $redirectProps['targetId'] = $attributes['targetId'];
        } else {
            $redirectProps['targetId'] = 'list_bar';
        }
        
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'REListingFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
}
