<?php

require_once 'framework/modules/Content/' . MODULE_CONTENT_VER . '/controller/AbstractContentController.php';

class ContentController extends AbstractContentController {
    
    public function actionIndexInvestmentHistory($attributes) {
        $type = 'content';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        }
        
        $general = false;
        if (isset($attributes['general']) && $attributes['general'] == 1) {
            $general = true;
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
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }
        
        $search = ContentFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        $pageBarLinkProps = $attributes;
        
        if(!empty($type)){
            $search->filterByTypeRef($type, $general);
        }
        
        $curId = NULL;
        if(isset($attributes['curId']) && $attributes['curId'] != ''){
            $curId = $attributes['curId'];
        }
//        
        $sampleContent  = ContentFactory::buildNewModel($type);
        
        $searchView = $sampleContent->getSearchForm($search, $type);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContent)
                ->setUseAjax(true);
        if(!GI_URLUtils::getAttribute('search')){
            $content = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols =  $sampleContent->getUIRolodexCols();
                $uiTableView = new UIRolodexView($content, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(!empty($curId)){
                    $uiTableView->setCurId($curId);
                }
            } else {
                $uiTableCols = $sampleContent->getUITableCols();
                $uiTableView = new UITableView($content, $uiTableCols, $pageBar);
            }
            $view = new ContentIndexView($content, $uiTableView, $sampleContent, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    /**
     * Add investment History page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionAddInvestmentHistory($attributes) {
        // Login check
        $curUserId = Login::getUserId();

        if (empty($curUserId)) {
            // Set the current attributes to forward after login
            GI_URLUtils::setLastAttributes($attributes);
            
            // Redirect
            GI_URLUtils::redirect(array(
                'controller' => 'login',
                'action' => 'index'
            ));
        }
        
        //Permission: add_content
        if (!Permission::verifyByRef('add_content')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $user = NULL;
        if (isset($attributes['userId'])) {
            $user = UserFactory::getModelById($attributes['userId']);
        }
        
        $listing = NULL;
        if (isset($attributes['listingId'])) {
            $listing = ContentFactory::getModelById($attributes['listingId']);
        }
        
        
        $form = new GI_Form('add_investment_history');
        
        $investmentHistory = InvestmentHistoryFactory::buildNewModel('history');

        if (is_null($investmentHistory)) {
            GI_URLUtils::redirectToError(3000);
        }
        
        $view = $investmentHistory->getFormView($form, $listing, $user);
        $success = 0;
        if ($investmentHistory->handleFormSubmission($form)) {
            $success = 1;
            $newURL = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($success) {
            $returnArray['newUrl'] = $newURL;
        }

        return $returnArray;
    }
    
    /**
     * Edit investment History page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionEditInvestmentHistory($attributes) {
        // Login check
        $curUserId = Login::getUserId();

        if (empty($curUserId)) {
            // Set the current attributes to forward after login
            GI_URLUtils::setLastAttributes($attributes);
            
            // Redirect
            GI_URLUtils::redirect(array(
                'controller' => 'login',
                'action' => 'index'
            ));
        }
        
        //Permission: add_content
        if (!Permission::verifyByRef('edit_content')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $form = new GI_Form('edit_investment_history');
        
        $investmentHistory = InvestmentHistoryFactory::getModelById($id);

        if (is_null($investmentHistory)) {
            GI_URLUtils::redirectToError(3000);
        }
        
        $user = $investmentHistory->getUser();
        $listing = $investmentHistory->getContent();
        
        $view = $investmentHistory->getFormView($form, $listing, $user);
        $success = 0;
        if ($investmentHistory->handleFormSubmission($form)) {
            $success = 1;
            $newURL = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($success) {
            $returnArray['newUrl'] = $newURL;
        }

        return $returnArray;
    }
    
    /**
     * Delete investment History
     * 
     * @param type $attributes
     * @param type $deleteProperties
     * @return type
     */
    public function actionDeleteInvestmentHistory($attributes, $deleteProperties = array()) {
        // Login check
        $curUserId = Login::getUserId();

        if (empty($curUserId)) {
            // Set the current attributes to forward after login
            GI_URLUtils::setLastAttributes($attributes);
            
            // Redirect
            GI_URLUtils::redirect(array(
                'controller' => 'login',
                'action' => 'index'
            ));
        }
        
        //Permission: add_content
        if (!Permission::verifyByRef('edit_content')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $model = InvestmentHistoryFactory::getModelById($id);

        if (is_null($model)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $redirectProps = array(
            'controller' => 'listing',
            'action' => 'index'
        );
        
        if (isset($attributes['redirectPage']) && isset($attributes['redirectId'])) {
            $redirectProps = array(
                'controller' => $attributes['redirectPage'],
                'action' => 'view',
                'id' => $attributes['redirectId'],
            );
        }
        
        $form = new GI_Form('delete_model');
        $view = new GenericDeleteFormView($form, $model);
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        if ($form->wasSubmitted() && $form->validate()) {
            if ($model->getIsDeleteable() && $model->softDelete()) {
                $newUrlArray = $redirectProps;
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlArray);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
            }
            $view->setDeleteError('You cannot delete this ' . $model->getTypeTitle() . '.');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        return $returnArray;
    }
    
    /**
     * Sanitize Ref by content title
     * @param type $attributes
     * @return type
     */
    public function actionSanitizeRef($attributes) {
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1){
            GI_URLUtils::redirectToError(2000);
        }
        $titleStr = filter_input(INPUT_POST, 'titleStr');

        //Sanitize
        $cleanRef = GI_Sanitize::ref(trim($titleStr));
        
        //Check if the ref exists in the content table
        $uniqueRef = ContentFactory::getUniqueRef($cleanRef);
        
        return array(
            'success' => 1,
            'unique_ref' => $uniqueRef
            );
    }
    
}
