<?php

class AgreementController extends GI_Controller {
    
    public function actionIndex($attributes) {
        $type = 'agreement';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
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
        
        $search = AgreementFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->filterByTypeRef($type)
                ->setQueryId($queryId);
        
        $pageBarLinkProps = $attributes;
        
        $curId = NULL;
        if(isset($attributes['curId']) && $attributes['curId'] != ''){
            $curId = $attributes['curId'];
        }
//        
        $sampleAgreement  = AgreementFactory::buildNewModel($type);
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSampleModel($sampleAgreement)
                ->setUseAjax(true);
        $agreements = $search->select();
        $pageBar = $search->getPageBar($pageBarLinkProps);
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols =  $sampleAgreement->getUIRolodexCols();
            $uiTableView = new UIRolodexView($agreements, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(!empty($curId)){
                $uiTableView->setCurId($curId);
            }
        } else {
            $uiTableCols = $sampleAgreement->getUITableCols();
            $uiTableView = new UITableView($agreements, $uiTableCols, $pageBar);
        }
        $view = new AgreementIndexView($agreements, $uiTableView, $sampleAgreement);
        $actionResult->setView($view)
                ->setPageBar($pageBar)
                ->setUITableView($uiTableView);
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    public function actionIndexForm($attributes) {
        $type = 'agreement_form';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
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
        
        $search = AgreementFormFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->filterByTypeRef($type)
                ->setQueryId($queryId);
        
        $pageBarLinkProps = $attributes;
        
        $curId = NULL;
        if(isset($attributes['curId']) && $attributes['curId'] != ''){
            $curId = $attributes['curId'];
        }
        $sampleAgreementForm  = AgreementFormFactory::buildNewModel($type);
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSampleModel($sampleAgreementForm)
                ->setUseAjax(true);
        $agreementForms = $search->select();
        $pageBar = $search->getPageBar($pageBarLinkProps);
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols =  $sampleAgreementForm->getUIRolodexCols();
            $uiTableView = new UIRolodexView($agreementForms, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(!empty($curId)){
                $uiTableView->setCurId($curId);
            }
        } else {
            $uiTableCols = $sampleAgreementForm->getUITableCols();
            $uiTableView = new UITableView($agreementForms, $uiTableCols, $pageBar);
        }
        $view = new AgreementFormIndexView($agreementForms, $uiTableView, $sampleAgreementForm);
        $actionResult->setView($view)
                ->setPageBar($pageBar)
                ->setUITableView($uiTableView);
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    public function actionIndexFormItem($attributes) {
        $type = 'agreement_form_item';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
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
        
        $search = AgreementFormItemFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->filterByTypeRef($type)
                ->setQueryId($queryId);
        
        $pageBarLinkProps = $attributes;
        
        $curId = NULL;
        if(isset($attributes['curId']) && $attributes['curId'] != ''){
            $curId = $attributes['curId'];
        }
        $sampleAgreementFormItem  = AgreementFormItemFactory::buildNewModel($type);
        
        $redirectArray = array();
        $searchView = $sampleAgreementFormItem->getSearchForm($search, $type, $redirectArray);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleAgreementFormItem)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $agreementFormItems = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols =  $sampleAgreementFormItem->getUIRolodexCols();
                $uiTableView = new UIRolodexView($agreementFormItems, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(!empty($curId)){
                    $uiTableView->setCurId($curId);
                }
            } else {
                $uiTableCols = $sampleAgreementFormItem->getUITableCols();
                $uiTableView = new UITableView($agreementFormItems, $uiTableCols, $pageBar);
                $searchView->setTargetElementId($sampleAgreementFormItem->getTableWrapId());
            }
            $view = new AgreementFormItemIndexView($agreementFormItems, $uiTableView, $sampleAgreementFormItem, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
         
        return $returnArray;
    }
    
    /**
     * Add agreement form page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionAddForm($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'agreement_form';
        }
        
        //@todo: permission
        $agreementForms = AgreementFormFactory::getAgreementFormByTypeRef();
        if(!Permission::verifyByRef('add_content') || count($agreementForms) != 0){
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('add_agreement_form');
        
        $agreementForm = AgreementFormFactory::buildNewModel($type);
        if (empty($agreementForm)) {
            GI_URLUtils::redirectToError(4000);
        }
        if (!$agreementForm->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $agreementForm->getFormView($form);
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        $view->buildForm();
        $success = 0;
        if ($agreementForm->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $agreementForm->getViewURLAttributes();
            $agreementFormId = $agreementForm->getId();
            if($ajax){
                //Change the view to a detail view
                $view = $agreementForm->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $agreementForm->getBreadcrumbs();
        $addLink = $agreementForm->getAddURL();
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$agreementFormId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    /**
     * Add agreement form item page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionAddFormItem($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'onoff';
        }
        
        //@todo: permission
        if(!Permission::verifyByRef('add_content')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('add_agreement_form_item');
        if ($form->wasSubmitted()) {
            $submittedType = filter_input(INPUT_POST, 'agreement_form_item_type');
            if (!empty($submittedType)) {
                $type = $submittedType;
            }
        }
        $agreementFormItem = AgreementFormItemFactory::buildNewModel($type);
        if (empty($agreementFormItem)) {
            GI_URLUtils::redirectToError(4000);
        }
        if (!$agreementFormItem->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $agreementFormItem->getFormView($form);
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        $view->buildForm();
        $success = 0;
        if ($agreementFormItem->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $agreementFormItem->getViewURLAttributes();
            $agreementFormItemId = $agreementFormItem->getId();
            if($ajax){
                //Change the view to a detail view
                $view = $agreementFormItem->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $agreementFormItem->getBreadcrumbs();
        $addLink = $agreementFormItem->getAddURL();
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$agreementFormItemId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    public function actionViewForm($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $agreementForm = AgreementFormFactory::getModelById($id);
        if (empty($agreementForm)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        
        if(!$agreementForm->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $agreementForm->getDetailView();

        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $agreementForm->getBreadcrumbs();
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = 'setCurrentOnListBar('.$id.');';
        }
        
        return $returnArray;
    }
    
    public function actionViewFormItem($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $agreementFormItem = AgreementFormItemFactory::getModelById($id);
        if (empty($agreementFormItem)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        
        if(!$agreementFormItem->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $agreementFormItem->getDetailView();

        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $agreementFormItem->getBreadcrumbs();
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = 'setCurrentOnListBar('.$id.');';
        }
        
        return $returnArray;
    }
    
    
    /**
     * Add investment History page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionAdd($attributes) {
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
    public function actionEdit($attributes) {
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
     * Edit agreement form page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionEditForm($attributes) {
        //@todo: permission
        if(!Permission::verifyByRef('edit_content')){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $form = new GI_Form('edit_agreement_form');
        
        $agreementForm = AgreementFormFactory::getModelById($id);
        if (empty($agreementForm)) {
            GI_URLUtils::redirectToError(4000);
        }
        if (!$agreementForm->isEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $agreementForm->getFormView($form);
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        $view->buildForm();
        $success = 0;
        if ($agreementForm->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $agreementForm->getViewURLAttributes();
            $agreementFormId = $agreementForm->getId();
            if($ajax){
                //Change the view to a detail view
                $view = $agreementForm->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $agreementForm->getBreadcrumbs();
        $editLink = $agreementForm->getEditURL();
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$agreementFormId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    /**
     * Edit agreement form item page
     * 
     * @param type $attributes
     * @return string
     */
    public function actionEditFormItem($attributes) {
        //@todo: permission
        if(!Permission::verifyByRef('edit_content')){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $pId = NULL;
        if (isset($attributes['pId']) && !empty($attributes['pId'])) {
            $pId = $attributes['pId'];
            $agreementForm = AgreementFormFactory::getModelById($pId);
        }
        $form = new GI_Form('edit_agreement_form_item');
        
        $agreementFormItem = AgreementFormItemFactory::getModelById($id);
        if (empty($agreementFormItem)) {
            GI_URLUtils::redirectToError(4000);
        }
        if (!$agreementFormItem->isEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if ($form->wasSubmitted()) {
            $submittedType = filter_input(INPUT_POST, 'agreement_form_item_type');
            $curType = $agreementFormItem->getTypeRef();
            if (!empty($submittedType) && $curType != $submittedType) {
                $agreementFormItem = AgreementFormItemFactory::changeModelType($agreementFormItem, $submittedType);
            }
        }
        $view = $agreementFormItem->getFormView($form);
        $ajax = false;
        if (GI_URLUtils::isAJAX()) {
            $ajax = true;
        }
        $view->buildForm();
        $success = 0;
        if ($agreementFormItem->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $agreementFormItem->getViewURLAttributes();
            $agreementFormItemId = $agreementFormItem->getId();
            if($ajax){
                if (!empty($agreementForm)) {
                    //If it's from the agreement form, redirect to the form's view
                    $view = $agreementForm->getDetailView();
                    $redirectURLAttributes = $agreementForm->getViewURLAttributes();
                    $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
                } else {
                    //Change the view to a detail view
                    $view = $agreementFormItem->getDetailView();
                    $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
                }
                
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $agreementFormItem->getBreadcrumbs();
        if (!$success) {
            if (!empty($agreementForm)) {
                $viewFormLink = $agreementForm->getViewURL();
                $breadcrumbs[] = array(
                    'label' => 'Agreement Form',
                    'link' => $viewFormLink
                );
            }
        
            $editLink = $agreementFormItem->getEditURL();
            $breadcrumbs[] = array(
                'label' => 'Edit',
                'link' => $editLink
            );
        }
        
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$agreementFormItemId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
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
    public function actionDelete($attributes, $deleteProperties = array()) {
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
    
    public function actionDeleteForm($attributes, $deleteProperties = array()) {
        //Permission: delete_content @todo
        if (!Permission::verifyByRef('delete_content')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $agreementForm = AgreementFormFactory::getModelById($id);
        if (empty($agreementForm)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$agreementForm->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $redirectProps = array(
            'controller' => 'agreement',
            'action' => 'indexForm'
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
            'factoryClassName' => 'AgreementFormFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionDeleteFormItem($attributes, $deleteProperties = array()) {
        //Permission: delete_content @todo
        if (!Permission::verifyByRef('delete_content')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $agreementFormItem = AgreementFormItemFactory::getModelById($id);
        if (empty($agreementFormItem)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$agreementFormItem->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $redirectProps = array(
            'controller' => 'agreement',
            'action' => 'indexFormItem'
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
            'factoryClassName' => 'AgreementFormItemFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionAutocompFormItem($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);

            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();

            foreach($curVals as $formItemId){
                $formItem = AgreementFormItemFactory::getModelById($formItemId);
                if($formItem){
                    $blurb = $formItem->getBlurb();

                    $finalLabel[] = $blurb;
                    $finalValue[] = $formItemId;
                    $finalResult[] = '<span class="result_text">'.$blurb.'</span>';
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

            $formItemSearch = AgreementFormItemFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->filterLike('agreement_form_item_content.content', '%' . $term . '%')
                    ->orderByLikeScore('agreement_form_item_content.content', $term);

            $formItems = $formItemSearch->select();

            $results = array();
            
            $addURLAttr = array(
                    'controller' => 'agreement',
                    'action' => 'addFormItemRow',
                );

            foreach($formItems as $formItem){
                /* @var $formItem AgreementFormItem */
                $blurb = $formItem->getBlurb();
                $content = $formItem->getContent();
                $addURLAttr['id'] = $formItem->getId();
                $formItemInfo = array(
                    'label' => $blurb,
                    'value' => $formItem->getId(),
                    'autoResult' => '<span class="result_text">'.$blurb.'</span>',
                    'content' => $content,
                    'addBtn'=> '<div class="add_row_wrap"><div class="form_element"><label="main">Position</label><input type="text" name="position" value="1" class="position_input"></div>'.$formItem->getSortableLinFormView(true).'<span class="custom_btn add_form_item_to_sortable_list" title="Add" data-url="'.GI_URLUtils::buildURL($addURLAttr).'">'.GI_StringUtils::getIcon('arrow_left').' Add Item</span></div>',
                );
                $results[] = $formItemInfo;
            }

            $this->addMoreResult($formItemSearch, $results);

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

//    protected function markTerm($term, $result) {
//        if (!empty($term)) {
//            return preg_replace('/' . $term . '/i', "<mark>\$0</mark>", $result);
//        }
//        return $result;
//    }
    
}
