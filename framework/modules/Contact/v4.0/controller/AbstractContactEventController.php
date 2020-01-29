<?php
/**
 * Description of AbstractContactEventController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.1.2
 */
abstract class AbstractContactEventController extends GI_Controller {
    
    /**
     * View contact events
     * @param type $attributes
     * @return string
     */
    public function actionIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'event';
        }
        
        if (isset($attributes['catType'])) {
            $catType = $attributes['catType'];
        } else {
            $catType = 'client';
        }
        
        $sampleContactCat  = ContactCatFactory::buildNewModel($catType);
        if (!$sampleContactCat->isEventIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
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
        
        $contactEventSearch = ContactEventFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        if (!empty($type)) {
            $contactEventSearch->filterByTypeRef($type);
        }
        $sampleContactEvent = ContactEventFactory::buildNewModel($type);
        if (!empty($catType) && $catType != 'category') {
            $sampleContactEvent->addContactTableToDataSearch($contactEventSearch);
            $contactEventSearch->join('contact_cat', 'contact_id', 'CONTACT', 'id', 'CAT')
                ->join('contact_cat_type', 'id', 'CAT', 'contact_cat_type_id', 'CAT_TYPE')
                ->filter('CAT_TYPE.ref', $catType)
                ->groupBy('id');
        }
        
        $sampleContactEvent->addSortingToDataSearch($contactEventSearch);
        $redirectArray = array();
        $searchView = $sampleContactEvent->getSearchForm($contactEventSearch, $type, $redirectArray, $catType);
        
        $pageBarLinkProps = $attributes;
        
        $sampleContactEvent->addContactCatJoinsToDataSearch($contactEventSearch); //Join Contact Cat table before addCustomFiltersToDataSearch to replace Contact table to Contact Event table
        $sampleContactEvent->addCustomFiltersToDataSearch($contactEventSearch);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContactEvent)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $contactEvents = $contactEventSearch->select();
            $pageBar = $contactEventSearch->getPageBar($pageBarLinkProps);
            
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleContactEvent->getUIRolodexCols();
                $uiTableView = new UIRolodexView($contactEvents, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleContactEvent->getUITableCols();
                $uiTableView = new UITableView($contactEvents, $uiTableCols, $pageBar);
            }
            
            $view = new ContactEventIndexView($contactEvents, $uiTableView, $sampleContactEvent, $searchView);
            $view->setCatType($catType);
            $view->setAddTypeSelector(false);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        $logURLAttributes = array(
            'controller'=>'contactevent',
            'action'=>'index'
        );
        LogService::logActivity(GI_URLUtils::buildURL($logURLAttributes), 'Contact ' . $sampleContactEvent->getViewTitle(true), 'visible', 'view');
        
        if ($targetId == 'list_bar') {
            $returnArray['listBarURL'] = $sampleContactEvent->getListBarURL();
            $returnArray['listBarClass'] = 'loaded';
        }
        
        return $returnArray;
    }
    
    /**
     * Add contact event
     * @param type $attributes
     * @return string
     */
    public function actionAdd($attributes) {
        if (!isset($attributes['pId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'event';
        }
        
        $contactId = $attributes['pId'];
        $contact = ContactFactory::getModelById($contactId);
        if (empty($contact)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
        }
        
        $contactEvent = ContactEventFactory::buildNewModel($type);
        if (empty($contactEvent)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$contactEvent->isAddable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('add_contact_event');
        $view = new ContactEventEditView($form, $contactEvent);
        $success = 0;
        $ajax = false;
        $newUrl = '';
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        if ($form->wasSubmitted()) {
            $targetType = filter_input(INPUT_POST, 'type');
            $contactEvent = ContactEventFactory::buildNewModel($targetType);
            $contactEvent->setProperty('contact_event.contact_id', $contactId);
            
            if ($contactEvent->handleFormSubmission($form)) {
                $viewURLAttributes = $contact->getViewURLAttributes();
                $viewURLAttributes['type'] = $contact->getTypeRef();
                $viewURLAttributes['tab'] = 'events';
                if ($ajax) {
                    $success = 1;
                    $newUrl = GI_URLUtils::buildURL($viewURLAttributes);
                } else {
                    GI_URLUtils::redirect($viewURLAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts() . ' bindContactEventFormElements();';
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    /** 
     * View contact event
     * @param type $attributes
     * @return type
     */ 
    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $contactEvent = ContactEventFactory::getModelById($id);
        if (empty($contactEvent)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
        }
        $view = new ContactEventDetailView($contactEvent);
        if (isset($attributes['refresh'])) {
            $view->setRefresh($attributes['refresh']);
        }
        $success = 1;
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts();
        }
        return $returnArray;
    }
    
    /**
     * Edit contact event
     * @param type $attributes
     * @return string
     */
    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $contactEvent = ContactEventFactory::getModelById($id);
        if (empty($contactEvent)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
        }
        $contactId = $contactEvent->getProperty('contact_id');
        $contact = ContactFactory::getModelById($contactId);
        $form = new GI_Form('edit_contact_event');
        $view = new ContactEventEditView($form, $contactEvent);
        $success = 0;
        $ajax = false;
        $newUrl = '';
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        if ($form->wasSubmitted()) {
            $targetType = filter_input(INPUT_POST, 'type');
            $updatedContactEvent = ContactEventFactory::changeModelType($contactEvent, $targetType);
            if ($updatedContactEvent->handleFormSubmission($form)) {
                $viewURLAttributes = $contact->getViewURLAttributes();
                $viewURLAttributes['type'] = $contact->getTypeRef();
                $viewURLAttributes['tab'] = 'events';
                if ($ajax) {
                    $success = 1;
                    if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                        $newUrl = 'refresh';
                    } else {
                        $newUrl = GI_URLUtils::buildURL($viewURLAttributes);
                    }
                } else {
                    GI_URLUtils::redirect($viewURLAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts() . ' bindContactEventFormElements();';
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    /**
     * Delete contact event
     * @param type $attributes
     * @param type $deleteProperties
     * @return type
     */
    public function actionDelete($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id']) || !isset($attributes['pId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $contactId = $attributes['pId'];
        $redirectProps = array(
            'controller' => 'contact',
            'action' => 'view',
            'id' => $contactId,
            'tab' => 'events',
        );
        
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'ContactEventFactory',
            'redirectOnSuccess' => $redirectProps
        );
        
        if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
            $deleteProperties['refresh'] = 1;
        }
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
}
