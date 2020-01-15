<?php
/**
 * Description of AbstractContentController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.2
 */
class AbstractContentController extends GI_Controller {
    
    public function actionIndex($attributes) {
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

        $sampleContent  = ContentFactory::buildNewModel($type);
        $redirectArray = array();
        $searchView = $sampleContent->getSearchForm($search, $type, $redirectArray);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContent)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
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
    
    public function actionView($attributes) {
        if (!isset($attributes['id']) && !isset($attributes['ref'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if(isset($attributes['id'])){
            $id = $attributes['id'];
            $content = ContentFactory::getModelById($id);
            if (empty($content)) {
                $deletedModel = ContentFactory::getDeletedModelById($id);
                if($deletedModel){
                    return $this->actionDeletedView($attributes, $deletedModel);
                }
                GI_URLUtils::redirectToError(4001);
            }
        } else { 
            $ref = $attributes['ref'];
            $content = ContentFactory::getModelByRef($ref);
        }
        if (empty($content)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$content->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $content->getView();
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $content->getBreadcrumbs();
        if(GI_URLUtils::isAJAX()){
            $returnArray['jqueryAction'] = $view->getUploaderScripts();
        }
        return $returnArray;
    }
    
    public function actionAdd($attributes) {
        if(!Permission::verifyByRef('add_content')){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'content';
        }
        
        $form = new GI_Form('add_content');
        if($form->wasSubmitted()){
            $type = filter_input(INPUT_POST, 'type_ref_0');
        }
        
        $content = ContentFactory::buildNewModel($type);
        if (is_null($content)) {
            GI_URLUtils::redirectToError(4000);
        }
        
        if(!$content->isAddable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $content->getFormView($form);
        
        $success = 0;
        if ($content->handleFormSubmission($form)) {
            $success = 1;
            $loadContent = $content;
            if($content->redirectToParent()){
                $parentContent = $content->getParentContent();
                if($parentContent){
                    $loadContent = $parentContent;
                }
            }
            $newURLProps = $loadContent->getViewURLAttrs();
            if(GI_URLUtils::isAJAX()){
                //Change the view to a detail view
                $view = $loadContent->getView();
                $redirectURL = GI_URLUtils::buildURL($newURLProps);
            } else {
                GI_URLUtils::redirect($newURLProps);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $content->getBreadcrumbs();
        if(!$success){
            $addLink = GI_URLUtils::buildURL(array(
                'controller' => 'content',
                'action' => 'add',
                'type' => $type
            ));
            $breadcrumbs[] = array(
                'label' => 'Add',
                'link' => $addLink
            );
        }
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if(GI_URLUtils::isAJAX()){
            $contentId = $content->getId();
            $returnArray['success'] = $success;
            $returnArray['autocompId'] = $contentId;
            if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                $returnArray['newUrl'] = 'refresh';
            }
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$contentId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        return $returnArray;
    }
    
    public function actionAddInContent($attributes){
        if(!isset($attributes['ajax']) || !$attributes['ajax']){
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'content';
        }
        
        $content = ContentFactory::buildNewModel($type);
        if (isset($attributes['contentNumber'])) {
            $contentNumber = $attributes['contentNumber'];
            $content->setContentNumber($contentNumber);
        }
        if (isset($attributes['parentNumber'])) {
            $parentNumber = $attributes['parentNumber'];
            $content->setParentNumber($parentNumber);
        }
        $tmpForm = new GI_Form('tmp_form');
        $view = $content->getFormView($tmpForm, false);
        $view->setDisplayAsChild(true);
        if(isset($attributes['inContent']) && $attributes['inContent']){
            $view->buildForm(true);
        } else {
            $view->buildForm(false);
        }
        
        $returnArray = array(
            'content' => $view->getHTMLView(),
            'jqueryAction' => $view->getUploaderScripts()
        );
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'content';
        }
        $id = $attributes['id'];
        $content = ContentFactory::getModelById($id);
        if(isset($attributes['id'])){
            $id = $attributes['id'];
            $content = ContentFactory::getModelById($id);
            if (empty($content)) {
                $deletedModel = ContentFactory::getDeletedModelById($id);
                if($deletedModel){
                    return $this->actionDeletedView($attributes, $deletedModel);
                }
                GI_URLUtils::redirectToError(4001);
            }
        }
        
        if(!$content->isEditable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('edit_content');
        $view = $content->getFormView($form);
        
        $onlyBodyContent = false;
        if(isset($attributes['onlyBodyContent']) && $attributes['onlyBodyContent'] == 1){
            $onlyBodyContent = true;
        }
        
        $success = 0;
        if ($content->handleFormSubmission($form)) {
            $success = 1;
            $loadContent = $content;
            if($content->redirectToParent()){
                $parentContent = $content->getParentContent();
                if($parentContent){
                    $loadContent = $parentContent;
                }
            }
            $newURLProps = $loadContent->getViewURLAttrs();
            if(GI_URLUtils::isAJAX()){
                //Change the view to a detail view
                if($onlyBodyContent){
                    $view = $content->getView();
                } else {
                    $view = $loadContent->getView();
                }
                $redirectURL = GI_URLUtils::buildURL($newURLProps);
            } else {
                GI_URLUtils::redirect($newURLProps);
            }
        }
        
        if($onlyBodyContent){
            $view->setOnlyBodyContent(true);
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $content->getBreadcrumbs();
        if(!$success){
            $editLink = GI_URLUtils::buildURL(array(
                'controller' => 'content',
                'action' => 'edit',
                'type' => $type
            ));
            $breadcrumbs[] = array(
                'label' => 'Edit',
                'link' => $editLink
            );
        }
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if(GI_URLUtils::isAJAX()){
            $contentId = $content->getId();
            $returnArray['success'] = $success;
            $returnArray['autocompId'] = $contentId;
            if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                $returnArray['newUrl'] = 'refresh';
            }
            if ($success) {
                //Set the list bar with index view to update new contact
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$contentId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        }
        
        return $returnArray;
    }
    
    public function actionDelete($attributes, $deleteProperties = array()) {
        $id = $attributes['id'];
        $content = ContentFactory::getModelById($id);
        if(isset($attributes['id'])){
            $id = $attributes['id'];
            $content = ContentFactory::getModelById($id);
            if (empty($content)) {
                    $deletedModel = ContentFactory::getDeletedModelById($id);
                    if($deletedModel){
                        return $this->actionDeletedView($attributes, $deletedModel);
                    }
                GI_URLUtils::redirectToError(4001);
            }
        }
        
        if(!$content->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $parentContent = $content->getParentContent();
        if($parentContent){
            $redirectProps = $parentContent->getViewURLAttrs();
        } else {
            $redirectProps = array(
                'controller' => 'content',
                'action' => 'index'
            );

            if(isset($attributes['type'])){
                $redirectProps['type'] = $attributes['type'];
            }
        }
        
        if(isset($attributes['targetId'])){
            $redirectProps['targetId'] = $attributes['targetId'];
        } else {
            $redirectProps['targetId'] = 'list_bar';
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'ContentFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
}
