<?php
/**
 * Description of AbstractContactController
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactController extends GI_Controller {
    
    public function actionIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = NULL;
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
        
        $contactSearch = ContactFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        if(isset($attributes['internalOnly'])){
            $contactSearch->setSearchValue('internal_only', $attributes['internalOnly']);
        }
        
        $pageBarLinkProps = $attributes;

        if (!empty($type)) {
            $contactSearch->filterByTypeRef($type);
            if ($type == 'franchise') {
                ContactFactory::addFranchiseFiltersForFranchiseList($contactSearch);
            }
        }

        $sampleContact = ContactFactory::buildNewModel($type);
        $sampleContact->addCustomFiltersToDataSearch($contactSearch);
        $redirectArray = array();
        $searchView = $sampleContact->getSearchForm($contactSearch, $type, $redirectArray);
        $sampleContact->addSortingToDataSearch($contactSearch);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContact)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $contacts = $contactSearch->select();
            $pageBar = $contactSearch->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleContact->getUIRolodexCols();
                $uiTableView = new UIRolodexView($contacts, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleContact->getUITableCols();
                $uiTableView = new UITableView($contacts, $uiTableCols, $pageBar);
            }
            $view = new ContactIndexView($contacts, $uiTableView, $sampleContact, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        return $returnArray;
    }

    public function actionWarehouseIndex($attributes) {   
        if(!Permission::verifyByRef('view_contacts') || !AssignedToContactFactory::userAssignedToMultipleWarehouses()){
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
        
        $contactSearch = ContactFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId)
                ->filterByTypeRef('warehouse');
        
        if(!Permission::verifyByRef('all_warehouses')){
            $contactTableName = $contactSearch->prefixTableName('contact');
            $userId = Login::getUserId();
            $contactSearch->join('assigned_to_contact', 'contact_id', $contactTableName, 'id', 'ASS')
                    ->filter('ASS.user_id', $userId)
                    ->join('assigned_to_contact_type', 'id', 'ASS', 'assigned_to_contact_type_id', 'ASSTYPE')
                    ->filter('ASSTYPE.ref', 'assigned_to_warehouse');
        }
        
        $pageBarLinkArray = array(
            'controller' => 'contact',
            'action' => 'index'
        );
        
        $sampleContact  = ContactFactory::buildNewModel('warehouse');
        $sampleContact->addCustomFiltersToDataSearch($contactSearch);
        $contactClass = get_class($sampleContact);
        
        $contacts = $contactSearch->select();
        $pageBar = $contactSearch->getPageBar($pageBarLinkArray);
        
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols = $sampleContact->getUIRolodexCols();
            $uiTableView = new UIRolodexView($contacts, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(isset($attributes['curId']) && $attributes['curId'] != ''){
                $uiTableView->setCurId($attributes['curId']);
            }
        } else {
            //List style view
            $uiTableCols = $contactClass::getUITableCols();
            $uiTableView = new UITableView($contacts, $uiTableCols, $pageBar);
        }
        
        $view = new ContactIndexView($contacts, $uiTableView, $sampleContact);
        $view->setAddListBtns(false);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $sampleContact->getBreadcrumbs();
        return $returnArray;
    }

    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        
        if(!$contact->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $contactQB = $contact->getContactQB();
        if (!empty($contactQB) && $contactQB->requiresBalanceUpdate()) {
            $qbConnection = QBConnection::getInstance();
            if (!empty($qbConnection)) {
                $qbObject = $contactQB->getQuickbooksObject();
                $contactQB->updateFromQB($qbObject);
            }
        }
        $view = $contact->getDetailView();

        if (isset($attributes['tab'])) {
            $curTab = $attributes['tab'];
            $view->setCurTab($curTab);
        }
        $contactCat = $contact->getContactCat();
        $contactCatTypeTitle = '';
        if (!empty($contactCat)) {
            $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
        }
        LogService::logView($contact, $contactCatTypeTitle . $contact->getName());
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $contact->getBreadcrumbs();
        if ($ajax) {
            $jqueryCallbackAction = 'setCurrentOnListBar('.$id.');';
            if (!empty(ProjectConfig::getGoogleAPIKey())) {
                $jqueryCallbackAction .= 'googleMapInit();';
            }
            $returnArray['jqueryCallbackAction'] = $jqueryCallbackAction;
        }
        
        return $returnArray;
    }

    public function actionAdd($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'contact';
        }
        
        if($type == 'warehouse' && !Permission::verifyByRef('all_warehouses')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        
        $form = new GI_Form('add_contact');
        
        $contact = ContactFactory::buildNewModel($type);
        
        if (!$contact->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $catTypeRef = NULL;
        if(isset($attributes['catType'])){
            $catTypeRef = $attributes['catType'];
            $contact->setDefaultContactCatTypeRef($catTypeRef);
        } elseif(isset($attributes['catTypeRefs'])){
            $catTypeRefArray = explode(',', $attributes['catTypeRefs']);
            if (!empty($catTypeRefArray)) {
                foreach($catTypeRefArray as $catTypeRef){
                    $cat = ContactCatFactory::buildNewModel($catTypeRef);
                    if($cat){
                        $contact->setDefaultContactCatTypeRef($catTypeRef);
                        break;
                    }
                }
            }
        }
        
        if (is_null($contact)) {
            GI_URLUtils::redirectToError(4000);
        }

        if (!$contact->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $pId = NULL;
        if (isset($attributes['pId'])) {
            $pId = $attributes['pId'];
        }

        $pInternal = NULL;
        if (isset($attributes['pInternal'])) {
            $pInternal = $attributes['pInternal'];
        }
        
        $view = $contact->getFormView($form);
        
        if(isset($attributes['title'])){
            $view->setStartTitle($attributes['title']);
        }
        
        if(!empty($catTypeRef)){
            $view->setCatTypeRefArray(explode(',', $catTypeRef));
        } elseif(isset($attributes['catTypeRefs'])){
            $view->setCatTypeRefArray(explode(',', $attributes['catTypeRefs']));
        }
        
        if (!isset($attributes['targetId']) || $attributes['targetId'] != 'main_window') {
            $view->setAjax($ajax);
        }
        $view->setPid($pId);
        $view->setPInternal($pInternal);
        $view->buildForm();

        $success = 0;
        $contactId = '';

        if ($form->wasSubmitted()) {
            $targetTypeRef = filter_input(INPUT_POST, 'type_ref');
            if (!empty($targetTypeRef)) {
                $contact = ContactFactory::buildNewModel($targetTypeRef);
            }
        }
        
        if ($contact->handleFormSubmission($form, $pId)) {
            $contactCat = $contact->getContactCat();
            $contactCatTypeTitle = '';
            if (!empty($contactCat)) {
                $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
            }
            LogService::logAdd($contact, $contactCatTypeTitle . $contact->getName());
            LogService::setIgnoreNextLogView(true);
            $success = 1;
            $contactId = $contact->getId();
            $redirectURLAttributes = $contact->getViewURLAttributes();
            if($ajax){
                //Change the view to a detail view
                $view = $contact->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $contact->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'add',
            'type' => $type
        ));
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            $returnArray['autocompId'] = $contactId;
            if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                $returnArray['newUrl'] = 'refresh';
            }
            if ($success) {
                //Set the list bar with index view to update new contact
                $jqueryCallbackAction = 'reloadInElementByTargetId("list_bar", '.$contactId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
                if (!empty(ProjectConfig::getGoogleAPIKey())) {
                    $jqueryCallbackAction .= 'googleMapInit();';
                }
                $returnArray['jqueryCallbackAction'] = $jqueryCallbackAction;
            }
        }
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$contact->isEditable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        
        $form = new GI_Form('edit_contact');

        $view = $contact->getFormView($form);
        $view->buildForm();
        if ($form->wasSubmitted()) {
            $targetTypeRef = filter_input(INPUT_POST, 'type_ref');
            if (!empty($targetTypeRef)) {
                $contact = ContactFactory::changeModelType($contact, $targetTypeRef);
            }
        }
        $success = 0;
        if ($contact->handleFormSubmission($form)) {
            $success = 1;
            $contactCat = $contact->getContactCat();
            $contactCatTypeTitle = '';
            if (!empty($contactCat)) {
                $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
            }
            LogService::logEdit($contact, $contactCatTypeTitle . $contact->getName());
            LogService::setIgnoreNextLogView(true);
            $redirectURLAttributes = $contact->getViewURLAttributes();
            if($ajax){
                //Change the view to a detail view
                $view = $contact->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $contact->getBreadcrumbs();
        $editLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'edit',
            'id' => $id
        ));
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if ($ajax) {
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new contact
                $jqueryCallbackAction = 'reloadInElementByTargetId("list_bar", '.$contact->getId().');historyPushState("reload", "'.$redirectURL.'", "main_window");';
                if (!empty(ProjectConfig::getGoogleAPIKey())) {
                    $jqueryCallbackAction .= 'googleMapInit();';
                }
                $returnArray['jqueryCallbackAction'] = $jqueryCallbackAction;
            }
            
        }
        
        return $returnArray;
    }
    
    public function actionDeleteRelationship($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id']) || !isset($attributes['pId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $contactId = $attributes['pId'];
        $redirectProps = array(
            'controller' => 'contact',
            'action' => 'view',
            'id' => $contactId, 
            'tab' => 'info',
        );
        
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'ContactRelationshipFactory',
            'redirectOnSuccess' => $redirectProps
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }

    public function actionAddContactInfo($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1  || !isset($attributes['type']) || !isset($attributes['seq'])) {
            GI_URLUtils::redirectToError(2000);
            return $returnArray;
        }
        $type = $attributes['type'];
        $seq = $attributes['seq'];
        $contactInfo = ContactInfoFactory::buildNewModel($type);
        if (empty($contactInfo)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
            return $returnArray;
        }
        $contactInfo->setFieldSuffix($seq);
        $typeRefsArray = ContactInfoFactory::getTypeRefArray($type);
        $pType = $typeRefsArray[0];
        $tempForm = new GI_Form('temp_form');
        $formView = $contactInfo->getFormView($tempForm);
        $formView->setPType($pType);
        $formView->buildForm();
        $formView->buildView(false);
        return array(
            'contactInfo' => $formView->getHTMLView(),
            'align' => $contactInfo->getFormBlockAlignment(),
        );
    }
    
    public function actionDelete($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$contact->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $redirectProps = array(
            'controller' => 'contact',
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
            'factoryClassName' => 'ContactFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionCatIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'client';
        }
        
        $sampleContactCat  = ContactCatFactory::buildNewModel($type);
        if (!$sampleContactCat->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $contactTypeRefs = array(
            'ind',
            'org'
        );
        $contactType = NULL;
        if (isset($attributes['contactTypes'])) {
            $contactTypeRefString = $attributes['contactTypes'];
            $contactTypeRefs = explode(',', $contactTypeRefString);
        } elseif(isset($attributes['contactType'])) {
            $contactType = $attributes['contactType'];
            $contactTypeRefs = NULL;
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
        
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $contactSearch = ContactFactory::search()
                ->join('contact_type', 'id', $contactTableName, 'contact_type_id', 'contact_type');
        
        if($contactTypeRefs){
            $contactSearch->filterIn('contact_type.ref', $contactTypeRefs);
        } elseif($contactType){
            $contactSearch->filterByTypeRef($contactType);
        }
        
        if ($type != 'category') {
            $contactSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                ->join('contact_cat_type', 'id', 'cat', 'contact_cat_type_id', 'cat_type')
                ->filter('cat_type.ref', $type)
                ->groupBy('id');
        }
                
        $contactSearch->setPageNumber($pageNumber)
            ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
            ->setQueryId($queryId);
        
        if(isset($attributes['internalOnly'])){
            $contactSearch->setSearchValue('internal_only', $attributes['internalOnly']);
        }
        
        $redirectArray = array();
        $pageBarLinkProps = $attributes;
        $searchView = $sampleContactCat->getSearchForm($contactSearch, $type, $redirectArray);
       
        $sampleContact = ContactFactory::buildNewModel('contact');
        $sampleContact->setDefaultContactCatTypeRef($type);
        $sampleContact->addCustomFiltersToDataSearch($contactSearch);
        $sampleContact->addSortingToDataSearch($contactSearch);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContactCat)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $contacts = $contactSearch->select();
            $pageBar = $contactSearch->getPageBar($pageBarLinkProps);
            $listTitle = '';
            if($contactType){
                $sampleContact = ContactFactory::buildNewModel($contactType);
                $listTitle = $sampleContact->getViewTitle(false);
            }
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleContactCat->getUIRolodexCols();
                $uiTableView = new UIRolodexView($contacts, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleContactCat->getUITableCols();
                $uiTableView = new UITableView($contacts, $uiTableCols, $pageBar);
            }
            
            $view = new ContactCatIndexView($contacts, $uiTableView, $sampleContactCat, $searchView);
            if (!empty($listTitle)) {
                $view->setListTitle($listTitle);
            }
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        if ($targetId == 'list_bar') {
            $returnArray['listBarURL'] = $sampleContactCat->getListBarURL();
            $returnArray['listBarClass'] = 'loaded';
        }
        return $returnArray;
    }
    
    /**
     * Add Contact category form
     * @param type $attributes
     * @return type
     */
    public function actionAddContactCat($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1  || !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
            return $returnArray;
        }
        $type = $attributes['type'];
        $contactCat = NULL;
        if (isset($attributes['contactId']) && $attributes['contactId'] != '') {
            $contactId = $attributes['contactId'];
            $contact = ContactFactory::getModelById($contactId);
            $contactCat = $contact->getContactCatModelByType($type, true);
        }
        if (empty($contactCat)) {
            $contactCat = ContactCatFactory::buildNewModel($type);
        }
        
        if (empty($contactCat)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
            return $returnArray;
        }
        $tempForm = new GI_Form('temp_form');
        $formView = $contactCat->getFormView($tempForm);
        $formView->buildForm();
        $formView->buildView(false);
        return array(
            'contactCat' => $formView->getHTMLView(),
        );
    }
    
    /**
     * Add contact event
     * @param type $attributes
     * @return string
     * @deprecated use ContactEventController add function
     */
    public function actionAddContactEvent($attributes) {
        if (!isset($attributes['pId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'event';
        }
        
        $contactId = $attributes['pId'];
        $contactEvent = ContactEventFactory::buildNewModel($type);
        if (empty($contactEvent)) {
            $returnArray = GI_Controller::getReturnArray();
            GI_URLUtils::redirectToError(4001);
        }
        $contact = ContactFactory::getModelById($contactId);
        $form = new GI_Form('add_contact_event');
        $view = new ContactEventEditView($form, $contactEvent);
        $returnArray = GI_Controller::getReturnArray($view);
        $success = 0;
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        if ($form->wasSubmitted()) {
            $targetType = filter_input(INPUT_POST, 'type');
            $contactEvent = ContactEventFactory::buildNewModel($targetType);
            $contactEvent->setProperty('contact_event.contact_id', $contactId);
            
            if ($contactEvent->handleFormSubmission($form)) {
                $urlProps = array(
                    'controller' => 'contact',
                    'action' => 'view',
                    'id' => $contactId,
                    'tab' => 'events'
                );
                if ($ajax) {
                    $success = 1;
                    $returnArray['newUrl'] = GI_URLUtils::buildURL($urlProps);
                } else {
                    GI_URLUtils::redirect($urlProps);
                }
            }
        }
        $returnArray['success'] = $success;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts() . ' bindContactEventFormElements();';
        }
        return $returnArray;
    }
    
    /** 
     * View contact event
     * @param type $attributes
     * @return type
     * @deprecated use ContactEventController view function
     */ 
    public function actionViewContactEvent($attributes) {
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
        $view = new ContactEventDetailView($contactEvent);
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
     * @deprecated use ContactEventController edit function
     */
    public function actionEditContactEvent($attributes) {
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
        $returnArray = GI_Controller::getReturnArray($view);
        $success = 0;
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        if ($form->wasSubmitted()) {
            $targetType = filter_input(INPUT_POST, 'type');
            $updatedContactEvent = ContactEventFactory::changeModelType($contactEvent, $targetType);
            if ($updatedContactEvent->handleFormSubmission($form)) {
                if ($ajax) {
                    $success = 1;
                    $returnArray['newUrl'] = 'refresh';
                } else {
                    GI_URLUtils::redirect(array(
                        'controller' => 'contact',
                        'action' => 'view',
                        'id' => $contactId,
                        'type' => $contact->getTypeRef()
                    ));
                }
            }
        }
        $returnArray['success'] = $success;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts() . ' bindContactEventFormElements();';
        }
        return $returnArray;
    }
    
    /**
     * Delete contact event
     * @param type $attributes
     * @param type $deleteProperties
     * @return type
     * @deprecated use ContactEventController delete function
     */
    public function actionDeleteContactEvent($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id']) || !isset($attributes['pId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $contactId = $attributes['pId'];
        $redirectProps = array(
            'controller' => 'contact',
            'action' => 'view',
            'id' => $contactId
        );
        
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'ContactEventFactory',
            'redirectOnSuccess' => $redirectProps
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionAddAssignedToContact($attributes) {
        if (!isset($attributes['contactId']) && !isset($attributes['userId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $type = 'assigned_to';
        if(isset($attributes['type'])){
            $type = $attributes['type'];
        }
        
        $user = NULL;
        $contact = NULL;
        if(isset($attributes['contactId'])){
            $contactId = $attributes['contactId'];
            $contact = ContactFactory::getModelById($contactId);
            if (empty($contact)) {
                GI_URLUtils::redirectToError(4001);
            }
            $redirectProps = array(
                'controller' => 'contact',
                'action' => 'view',
                'id' => $contactId
            );
        } else {
            $userId = $attributes['userId'];
            if($userId == Login::getUserId()){
                GI_URLUtils::redirectToAccessDenied();
            }
            $user = UserFactory::getModelById($userId);
            if (empty($user)) {
                GI_URLUtils::redirectToError(4001);
            }
            $redirectProps = array(
                'controller' => 'user',
                'action' => 'view',
                'id' => $userId
            );
        }
        
        if(empty($contact) && isset($attributes['contactType'])){
            $contact = ContactFactory::buildNewModel($attributes['contactType']);
        }
        
        $form = new GI_Form('add_assignedToContact');
        $assignedToContact = AssignedToContactFactory::buildNewModel($type);
        $view = new AssignedToContactFormView($form, $assignedToContact, $contact, $user);
        $success = 0;
        $newURL = '';
        //Check if a record with the same ids exists
        if ($form->wasSubmitted()) {
            $contactId = filter_input(INPUT_POST, 'contact_id');
            $userId = filter_input(INPUT_POST, 'user_id');
            
            if (!empty($contactId) && !empty($userId)) {
                $searchedModels = AssignedToContactFactory::search()
                        ->setAutoStatus(false)
                        ->filter('contact_id', $contactId)
                        ->filter('user_id', $userId)
                        ->select();
                if (!empty($searchedModels)) {
                    //If there is a record with the same ids, replace it
                    $assignedToContact = $searchedModels[0];
                    $assignedToContact->setProperty('status', 1);
                }
            }
        }
        
        if ($assignedToContact->handleFormSubmission($form)) {
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newURL = 'refresh';
                $success = 1; 
            } else {
                GI_URLUtils::redirect($redirectProps);
            }
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newURL;
        $returnArray['mainContent'] = $view->getHTMLView();
        return $returnArray;
    }
    
    public function actionEditAssignedToContact($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $assignedToContact = AssignedToContactFactory::getModelById($id);
        if (empty($assignedToContact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $form = new GI_Form('edit_assignedToContact');
        $contactId = $assignedToContact->getProperty('contact_id');
        $contact = ContactFactory::getModelById($contactId);
        $type = $contact->getTypeRef();
        $userId = $assignedToContact->getProperty('user_id');
        $assignedUser = UserFactory::getModelById($userId);
        $view = new AssignedToContactFormView($form, $assignedToContact, $contact, $assignedUser);
        $success = 0;
        $newURL = '';
        if ($assignedToContact->handleFormSubmission($form)) {
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newURL = 'refresh';
                $success = 1;                        
            } else {
                GI_URLUtils::redirect(array(
                    'controller'=>'contact',
                    'action'=>'view',
                    'id'=>$contactId,
                    'type'=>$type
                ));
            }
        }
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newURL;
        $returnArray['mainContent'] = $view->getHTMLView();
        return $returnArray;
    }
    
    public function actionDeleteAssignedToContact($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id']) || (!isset($attributes['contactId']) && !isset($attributes['userId']))) {
            GI_URLUtils::redirectToError(2000);
        }
        
        if(isset($attributes['contactId'])){
            $redirectProps = array(
                'controller' => 'contact',
                'action' => 'view',
                'id' => $attributes['contactId'],
                'tab' => 'info',
            );
        } else {
            $redirectProps = array(
                'controller' => 'user',
                'action' => 'view',
                'id' => $attributes['userId']
            );
        }
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'AssignedToContactFactory',
            'redirectOnSuccess' => $redirectProps
        );
        
        return parent::actionDelete($attributes, $deleteProperties);
    }
    
    public function actionManageRelationship($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!($contact->isViewable() && (Permission::verifyByRef('link_contacts') || Permission::verifyByRef('unlink_contacts')))){
            GI_URLUtils::redirectToAccessDenied();
        }
        $contactTypeRef = NULL;
        if (isset($attributes['type'])) {
            $contactTypeRef = $attributes['type'];
        }
        $relation = NULL;
        if (isset($attributes['relation'])) {
            $relation = $attributes['relation'];
        }
        if (empty($contactTypeRef) || empty($relation)) {
            //Set default type and relation
            $contactTypeRef = $contact->getTypeRef();
            switch ($contactTypeRef) {
                case 'org':
                    $contactTypeRef = 'ind';
                    $relation = 'child';
                    break;
                case 'ind':
                case 'loc':
                default:
                    $contactTypeRef = 'org';
                    $relation = 'parent';
                    break;
            }
        }
        $form = new GI_Form('manage_relationship');
        $view = $contact->getManageRelationshipFormView($form, $contactTypeRef, $relation);
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        $success = 0;
        if ($contact->handleManageRelationshipFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = array(
                    'controller' => 'contact',
                    'action' => 'view',
                    'id' => $id,
                    'tab' => 'info',
                );
            if($ajax){
                //Change the view to a detail view
                $view = $contact->getDetailView();
                $view->setCurTab('info');
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $contact->getBreadcrumbs();
        $sampleContactModel = ContactFactory::buildNewModel($contactTypeRef);
        if (!empty($sampleContactModel)) {
            $typeTitle = $sampleContactModel->getViewTitle();
        } else {
            $typeTitle = 'Contacts';
        }
        $linkURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'manageRelationship',
            'id' => $id,
            'type' => $contactTypeRef,
            'relation' => $relation,
        ));
        $breadcrumbs[] = array(
            'label' => 'Manage '.$typeTitle,
            'link' => $linkURL,
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        
        if ($ajax) {
            if ($success) {
                $jqueryCallbackAction = 'historyPushState("reload", "'.$redirectURL.'", "main_window");';
                if (!empty(ProjectConfig::getGoogleAPIKey())) {
                    $jqueryCallbackAction .= 'googleMapInit();';
                }
                $returnArray['jqueryCallbackAction'] = $jqueryCallbackAction;
            }
        }
        
        return $returnArray;
    }
    
    public function actionAddRelationshipRow($attributes){
        if (!isset($attributes['pId']) || !isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['seq']) || !isset($attributes['relation']) ) {
            GI_URLUtils::redirectToError(2000);
        }
        $contactId = $attributes['pId'];
        $contact = ContactFactory::getModelById($contactId);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        $seq = $attributes['seq'];
        $relation = $attributes['relation'];
        $typeRef = NULL;
        if(isset($attributes['typeRef']) && $attributes['typeRef']){
            $typeRef = $attributes['typeRef'];
        }
        
        //$returnArray = GI_Controller::getReturnArray();
        $contactRelationship = ContactRelationshipFactory::buildNewModel();
        $contactRelationship->setSeqNumber($seq);
        if ($relation == 'parent') {
            $contactRelationship->setProperty('c_contact_id', $contactId);
        } else if ($relation == 'child') {
            $contactRelationship->setProperty('p_contact_id', $contactId);
        }
                
        $tempForm = new GI_Form('temp_form');
        $formView = new ContactRelationshipFormView($tempForm, $contact, $contactRelationship, $typeRef);
        $formView->setFullView(false);
        $formView->buildForm();
        return array(
            'formRow' => $formView->getHTMLView()
        );
    }
    
    public function actionGetCurrencyId($attributes) {
        if (!isset($attributes['id']) || !(isset($attributes['ajax']) && $attributes['ajax'] == '1')) {
            return array();
        }
        $contactId = $attributes['id'];
        $contact = ContactFactory::getModelById($contactId);
        if (empty($contact)) {
            return array();
        }
        $currency = $contact->getDefaultCurrency();
        if(empty($currency)){
            $currencyId = ProjectConfig::getDefaultCurrencyId();
            $currency = CurrencyFactory::getModelById($currencyId);
        }
        $currencyId = NULL;
        $currencyCode = NULL;
        if (!empty($currency)) {
            $currencyId = $currency->getId();
            $currencyCode = $currency->getProperty('name');
        }
        return array(
            'currencyId' => $currencyId,
            'currencyCode' => $currencyCode
        );
    }
    
    public function actionAutocompContact($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        $addrFieldPrefix = '';
        $addrFieldSuffix = '';
        $addrTypeRef = 'address';
        
        if(isset($attributes['useAddrBtn']) && $attributes['useAddrBtn']){
            $useAddrBtn = true;
            if(isset($attributes['addrFieldPrefix'])){
                $addrFieldPrefix = $attributes['addrFieldPrefix'];
            }
            if(isset($attributes['addrFieldSuffix'])){
                $addrFieldSuffix = $attributes['addrFieldSuffix'];
            }
            if(isset($attributes['addrTypeRef'])){
                $addrTypeRef = $attributes['addrTypeRef'];
            }
        } else {
            $useAddrBtn = false;
        }
        
        $addrInfo = array(
            'addrTypeRef' => $addrTypeRef,
            'addrFieldPrefix' => $addrFieldPrefix,
            'addrFieldSuffix' => $addrFieldSuffix,
        );

        if(isset($attributes['curVal'])){

            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            
            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach($curVals as $contactId){
                $contact = ContactFactory::getModelById($contactId);
                if($contact){
                    $acResult = $contact->getAutocompResult(NULL, $useAddrBtn, $addrInfo);

                    foreach($acResult as $key => $val){
                        if($key == 'addrBtn' || $key == 'addrView'){
                            $results[$key] = $val;
                            continue;
                        } elseif(!isset($results[$key])){
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            
            return $results;
        } else {
            if (isset($_REQUEST['term'])) {
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $contactSearch = ContactFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $contactSearch->setPageNumber($pageNumber);
            }
            $contactTableName = ContactFactory::getDbPrefix() . 'contact';
            if (isset($attributes['pId'])) {
                $pId = $attributes['pId'];
                $contactSearch->join('contact_relationship', 'c_contact_id', $contactTableName, 'id', 'CR')
                        ->filter('CR.p_contact_id', $pId);
            }
            $usedTypeRefs = array(
                'ind',
                'org',
                'loc'
            );
            
            $fieldMap = $this->getContactFieldMap();
            $addWarehouseFilter = false;
            if(isset($attributes['type']) && !empty($attributes['type'])){
                $usedTypeRefs = array();
                $typeRefs = explode(',',$attributes['type']);
                $contactSearch->filterGroup();
                foreach($typeRefs as $typeRef){
                    $usedTypeRefs[] = $fieldMap[$typeRef];
                    $contactSearch->filterByTypeRef($typeRef);
                    $contactSearch->orIf();
                    if($typeRef == 'warehouse'){
                        $addWarehouseFilter = true;
                    }
                }
                $contactSearch->closeGroup();
                $contactSearch->andIf();
            }
            if(isset($attributes['internal'])){
                $contactSearch->filter('internal', $attributes['internal']);
            }
            
            if(!Permission::verifyByRef('all_warehouses') && $addWarehouseFilter){
                $contactSearch->join('assigned_to_contact', 'contact_id', $contactTableName, 'id', 'ASS')
                        ->filter('ASS.user_id', Login::getUserId())
                        ->join('assigned_to_contact_type', 'id', 'ASS', 'assigned_to_contact_type_id', 'ASSTYPE')
                        ->filter('ASSTYPE.ref', 'assigned_to_warehouse');
            }
            
            if(!empty($term)){
                $likeScoreColumns = array();

                $contactSearch->filterGroup();
                    if(in_array('org', $usedTypeRefs)){
                        $orgCols = array(
                            'org.title',
                            'org.doing_bus_as'
                        );
                        $likeScoreColumns = array_merge($likeScoreColumns, $orgCols);
                        if (ProjectConfig::getContactUseFullyQualifiedName()) {
                            $orgCols[] = 'fully_qualified_name';
                        }
                        $contactSearch->filterGroup()
                                ->filterTermsLike($orgCols, $term)
                                ->andIf()
                                ->filter('org.status',1)
                            ->closeGroup();

                        $contactSearch->orIf();
                    }

                    if(in_array('ind', $usedTypeRefs)){
                        $indCols = array(
                            'ind.first_name',
                            'ind.last_name'
                        );
                        $likeScoreColumns = array_merge($likeScoreColumns, $indCols);
                        if (ProjectConfig::getContactUseFullyQualifiedName()) {
                            $indCols[] = 'fully_qualified_name';
                        }
                        $contactSearch->filterGroup()
                                ->filterTermsLike($indCols, $term)
                                ->andIf()
                                ->filter('ind.status',1)
                            ->closeGroup();

                        $contactSearch->orIf();
                }

                if (in_array('loc', $usedTypeRefs)) {
                    $locCols = array(
                        'loc.name'
                    );
                    $likeScoreColumns = array_merge($likeScoreColumns, $locCols);
                    if (ProjectConfig::getContactUseFullyQualifiedName()) {
                        $locCols[] = 'fully_qualified_name';
                    }
                    $contactSearch->filterGroup()
                            ->filterTermsLike($locCols, $term)
                            ->andIf()
                            ->filter('loc.status',1)
                            ->closeGroup();
                }

                if (ProjectConfig::getContactUseFullyQualifiedName()) {
                    $likeScoreColumns[] = 'fully_qualified_name';
                }

                $contactSearch->closeGroup();
                $contactSearch->andIf();

                $contactSearch->orderByLikeScore($likeScoreColumns, $term);
            }
            
            
            //If there is category typeRef parameter, add category conditions
            if (isset($attributes['catTypeRefs'])) {
                $catTypeRefs = $attributes['catTypeRefs'];
                $catTypeRefArray = explode(',', $catTypeRefs);
                $contactTableName = ContactFactory::getDbPrefix() . 'contact';
                $contactSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                        ->join('contact_cat_type', 'id', 'cat', 'contact_cat_type_id', 'cat_type')
                        ->filterIn('cat_type.ref', $catTypeRefArray)
                        ->groupBy('id');
            }

            $sampleContact = ContactFactory::buildNewModel('contact');
            $sampleContact->addCustomFiltersToDataSearch($contactSearch);

            $contacts = $contactSearch->select();

            $results = array();

            foreach ($contacts as $contact) {
                /* @var $item AbstractContact */
                $itemInfo = $contact->getAutocompResult($term, $useAddrBtn, $addrInfo);
                $results[] = $itemInfo;
            }
            
            $itemsPerPage = $contactSearch->getItemsPerPage();
            $count = $contactSearch->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            if (isset($attributes['autocompField'])) {
                $autocompField = $attributes['autocompField'];
                if (isset($attributes['addOrgType']) && !empty($attributes['addOrgType'])) {
                    $addOrgType = $attributes['addOrgType'];
                    $addOrgURLProps = array(
                        'controller' => 'contact',
                        'action' => 'add',
                        'type' => $addOrgType,
                        'ajax' => 1
                    );
                    $sampleOrg = ContactFactory::buildNewModel($addOrgType);
                    $orgTypeTitle = $sampleOrg->getTypeTitle();
                    $addOrgTitle = 'Add ' . $orgTypeTitle;
                    $addOrgHoverTitle = $addOrgTitle;
                    if(!empty($term)){
                        $addOrgTitle = 'Add ' . $term . ' <span class="sml_text">[' . $orgTypeTitle . ']</span>';
                        $addOrgURLProps['title'] = $term;
                    }
                    if (isset($catTypeRefs)) {
                        $addOrgURLProps['catTypeRefs'] = $catTypeRefs;
                    }
                    $addOrgURL = GI_URLUtils::buildURL($addOrgURLProps, false, true);
                    // $autocompField = $attributes['autocompField'];
                    $results[] = array(
                        'preventDefault' => 1,
                        'jqueryAction' => 'giModalOpenAjaxContent("' . $addOrgURL . '","medium_sized",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                        'liClass' => 'custom_btn',
                        'hoverTitle' => $addOrgHoverTitle,
                        'autoResult' => GI_StringUtils::getIcon('add').'<span class="btn_text">' . $addOrgTitle . '</span>'
                    );
                }
                if (isset($attributes['addIndType']) && !empty($attributes['addIndType'])) {
                    $addIndType = $attributes['addIndType'];
                    $addIndURLProps = array(
                        'controller' => 'contact',
                        'action' => 'add',
                        'type' => $addIndType,
                        'ajax' => 1,
                    );
                    $sampleInd = ContactFactory::buildNewModel($addIndType);
                    $indTypeTitle = $sampleInd->getTypeTitle();
                    $addIndTitle = 'Add ' . $indTypeTitle;
                    $addInvHoverTitle = $addIndTitle;
                    if(!empty($term)){
                        $addIndTitle = 'Add ' . $term . ' <span class="sml_text">[' . $indTypeTitle . ']</span>';
                        $addIndURLProps['title'] = $term;
                    }
                    if (isset($catTypeRefs)) {
                        $addIndURLProps['catTypeRefs'] = $catTypeRefs;
                    }
                    $addIndURL = GI_URLUtils::buildURL($addIndURLProps, false, true);
                    $results[] = array(
                        'preventDefault' => 1,
                        'jqueryAction' => 'giModalOpenAjaxContent("' . $addIndURL . '","medium_sized",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                        'liClass' => 'custom_btn',
                        'hoverTitle' => $addInvHoverTitle,
                        'autoResult' => GI_StringUtils::getIcon('add').'<span class="btn_text">' . $addIndTitle . '</span>'
                    );
                }
                if (isset($attributes['addLocType']) && !empty($attributes['addLocType'])) {
                    $addLocType = $attributes['addLocType'];
                    $addLocURLProps = array(
                        'controller' => 'contact',
                        'action' => 'add',
                        'type' => $addLocType,
                        'ajax' => 1,
                    );
                    if(isset($attributes['internal'])){
                        $addLocURLProps['pInternal'] = $attributes['internal'];
                    }
                    $sampleLoc = ContactFactory::buildNewModel($addLocType);
                    $locTypeTitle = $sampleLoc->getTypeTitle();
                    $addLocTitle = 'Add ' . $locTypeTitle;
                    $addLocHoverTitle = $addLocTitle;
                    if(!empty($term)){
                        $addLocTitle = 'Add ' . $term . ' <span class="sml_text">[' . $locTypeTitle . ']</span>';
                        $addLocURLProps['title'] = $term;
                    }
                    
                    $addLocURL = GI_URLUtils::buildURL($addLocURLProps, false, true);
                    $results[] = array(
                        'preventDefault' => 1,
                        'jqueryAction' => 'giModalOpenAjaxContent("' . $addLocURL . '","medium_sized",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                        'liClass' => 'custom_btn',
                        'hoverTitle' => $addLocHoverTitle,
                        'autoResult' => GI_StringUtils::getIcon('add').'<span class="btn_text">' . $addLocTitle . '</span>'
                    );
                }
            }
            
            return $results;
        }
    }

    protected function getContactFieldMap() {
        return array(
            'ind' => 'ind',
            'org' => 'org',
            'loc' => 'loc',
            'warehouse' => 'loc'
        );
    }

    public function actionQBImportIndex($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = new ContactQBIndexTabView();
        if (isset($attributes['tab'])) {
            $currentTab = $attributes['tab'];
            $view->setCurrentTab($currentTab);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label' => 'Admin',
            'link' => '',
        );
        $breadcrumbs[] = array(
            'label' => 'Quickbooks',
            'link' => '',
        );
        $breadcrumbs[] = array(
            'label' => 'Unlinked QuickBooks Contacts',
            'link' => GI_URLUtils::buildURL($attributes),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionQBImportIndexContent($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $type = 'supplier';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
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
        $sampleContactQB = ContactQBFactory::buildNewModel($type);
        $contactQBTableName = ContactQBFactory::getDbPrefix() . 'contact_qb';
        $search = ContactQBFactory::search();
        $join = $search->createJoin('contact', 'contact_qb_id', $contactQBTableName, 'id', 'CONTACT', 'left');
        $join->filterGroup()
                ->filter('CONTACT.status', 1)
                ->orIf()
                ->filterNULL('CONTACT.status')
                ->closeGroup()
                ->andIf();

        $search->filterNull('CONTACT.status')
                ->filterByTypeRef($type)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId)
                ->groupBy('id');

        $searchView = $sampleContactQB->getSearchForm($search, $type);
        if ($type == 'supplier') {
            $searchView->setBoxId('qb_supplier_search');
        } else {
            $searchView->setBoxId('qb_customer_search');
        }
        
        $pageBarLinkProps = array(
            'controller' => 'contact',
            'action' => 'qbImportIndexContent',
            'type' => $type
        );
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContactQB)
                ->setUseAjax(true);
        if(!GI_URLUtils::getAttribute('search')){
            $contactQBs = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            $uiTableCols = $sampleContactQB->getUITableCols();
            $uiTableView = new UITableView($contactQBs, $uiTableCols, $pageBar);
            $view = new ContactQBIndexView($contactQBs, $uiTableView, $sampleContactQB, $searchView);
            if (isset($attributes['tabbed']) && $attributes['tabbed'] == 1) {
                $view->setTabView(true);
            }
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }
    
    public function actionImportQBContacts($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $type = 'supplier';
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        }
        $sampleContactQB = ContactQBFactory::buildNewModel($type);
        if (empty($sampleContactQB)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('import_contacts');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Import New Contacts from Quickbooks');
        $view->setMessageText('Are you sure you wish to import new '.$sampleContactQB->getViewTitle().'? This may take some time.');
        $view->setSubmitButtonLabel('Import');
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        if ($form->wasSubmitted() && $form->validate()) {
            $importer = new QBContactImporter();
            if (!$importer->importFromQB($type)) {
                GI_URLUtils::redirectToError();
            }

            $newUrlAttributes = array(
                'controller' => 'contact',
                'action' => 'qbImportIndex',
                'tab'=>$type
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        return $returnArray;
    }
    
    public function actionImportQBContact($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $qbConnection = QBConnection::getInstance();
        if (!isset($attributes['id']) || empty($qbConnection)) {
            GI_URLUtils::redirectToError();
        }
        $contactQB = ContactQBFactory::getModelById($attributes['id']);
        if (empty($contactQB)) {
            GI_URLUtils::redirectToError();
        }
        $apiTableName = $contactQB->getAPITableName();
        if (empty($apiTableName)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('import_contact_qb');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Import Contact Data from Quickbooks');
        $view->setMessageText('Are you sure you wish to import data from Quickbooks?');
        $view->setSubmitButtonLabel('Import');
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        if ($form->wasSubmitted() && $form->validate()) {
            if ($contactQB->importFromQB()) {
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = 'refresh';
                    $success = 1;
                } else {
                    GI_URLUtils::redirect(array(
                        'controller' => 'contact',
                        'action' => 'qbImportIndex',
                    ));
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        return $returnArray;
    }
    
    public function actionCreateOrLinkFromQBContact($attributes) {
        if (!Permission::verifyByRef('add_contacts')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $contactQB = ContactQBFactory::getModelById($attributes['id']);
        if (empty($contactQB)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('create_link_contact');
        $view = new ContactQBCreateOrLinkFormView($form, $contactQB);
        $view->buildForm();
        if ($contactQB->handleLinkOrCreateContactFormSubmission($form)) {
            $orgViewURLAttributes = $contactQB->getLinkedOrgViewURLAttributes();
            if (!empty($orgViewURLAttributes)) {
                GI_URLUtils::redirect($orgViewURLAttributes);
            }
            $indViewURLAttributes = $contactQB->getLinkedINdViewURLAttributes();
            if (!empty($indViewURLAttributes)) {
                GI_URLUtils::redirect($indViewURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => 'Admin',
                'link' => '',
            ),
            array(
                'label' => 'Unlinked QuickBooks Contacts',
                'link' => GI_URLUtils::buildURL(array(
                    'controller'=>'contact',
                    'action'=>'qbImportIndex'
                )),
            ),
            array(
                'label' => 'Link/Create Contact(s)',
                'link' => GI_URLUtils::buildURL($attributes),
            ),
        );

        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionImportFromQB($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $contact = ContactFactory::getModelById($attributes['id']);
        if (empty($contact)) {
            GI_URLUtils::redirectToError();
        }
        $contactQB = $contact->getContactQB();
        if (empty($contactQB)) {
            GI_URLUtils::redirectToError();
        }
        $qbConnection = QBConnection::getInstance();
        if (empty($qbConnection)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('import_from_qb');
        
        //TODO - check if this is required first?
        if (!$form->wasSubmitted()) {
            if (!$contactQB->importFromQB()) {
                GI_URLUtils::redirectToError();
            }
        }

        $view = new ContactImportFromQBFormView($form, $contact, $contactQB);
        $success = 0;
        $newUrl = '';
        $updatedContact = $contact->handleImportFromQBFormSubmission($form);
        if (!empty($updatedContact)) {
            $newUrlAttributes = array(
                'controller' => 'contact',
                'action' => 'view',
                'id'=>$updatedContact->getId(),
            );
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                //$view = $contactQB->getDetailView();
                $success = 1;
                $newUrl = 'refresh';
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        $returnArray['jqueryCallbackAction'] = 'refreshAllContactInfos();';
        return $returnArray;
    }

    public function actionExportToQB($attributes) {
        if (!Permission::verifyByRef('export_contacts_to_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $contact = ContactFactory::getModelById($attributes['id']);
        if (empty($contact)) {
            GI_URLUtils::redirectToError();
        }
        $contactQB = $contact->getContactQB();
        if (empty($contactQB)) {
            if ($contact->isClient()) {
                $contactQB = ContactQBFactory::buildNewModel('customer');
            } else if ($contact->isVendor() || $contact->isShipper()) {
                $contactQB = ContactQBFactory::buildNewModel('supplier');
            } 
        }
        if (empty($contactQB)) {
            GI_URLUtils::redirectToError();
        }
        $form = new GI_Form('export_to_qb');
        $view = new ContactExportToQBFormView($form, $contact, $contactQB);
        $view->buildForm();
        if (!empty($contactQB->getId()) && !$form->wasSubmitted()) {
            if (!$contactQB->importFromQB()) {
                GI_URLUtils::redirectToError();
            }
        }
        $success = 0;
        $newUrl = '';
        if ($contact->handleExportToQBFormSubmission($form, $contactQB)) {
            if ($contactQB->exportToQB()) {
                $newUrlAttributes = array(
                    'controller' => 'contact',
                    'action' => 'view',
                    'id' => $contact->getId(),
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['newUrl'] = $newUrl;
        $returnArray['jqueryCallbackAction'] = 'refreshAllContactInfos();';
        return $returnArray;
    }

    public function actionViewQB($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$contact->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $contactQB = $contact->getContactQB();
        $returnArray = array();
        if (!empty($contactQB)) {
            $view = $contactQB->getDetailView();
            $returnArray = GI_Controller::getReturnArray($view);
        } else {
            $returnArray['mainContent'] = '';
        }
        if (isset($attributes['callback'])) {
            $returnArray['jqueryCallbackAction'] = $attributes['callback'].'();';
        }
        
       return $returnArray;
    }

    public function actionAutoImportQBCustomers($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $importer = new QBContactImporter();
        if (!($importer->importFromQB('customer') && $importer->createContacts('client'))) {
            GI_URLUtils::redirectToError();
        }
        GI_URLUtils::redirect(array(
            'controller' => 'contact',
            'action' => 'catIndex',
            'type' => 'client'
        ));
    }

    public function actionAutoImportQBVendors($attributes) {
        if (!Permission::verifyByRef('import_contacts_from_quickbooks')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $importer = new QBContactImporter();
        if (!($importer->importFromQB('supplier') && $importer->createContacts('vendor'))) {
            GI_URLUtils::redirectToError();
        }
        GI_URLUtils::redirect(array(
            'controller' => 'contact',
            'action' => 'catIndex',
            'type' => 'vendor'
        ));
    }
    
    public function actionUnlinkQBContact($attributes) {
        if (!Permission::verifyByRef('unlink_contacts_from_qb_contacts')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $contactId = $attributes['id'];
        $contact = ContactFactory::getModelById($contactId);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('unlink_contact');
        $view = new GenericAcceptCancelFormView($form);
        $contactName = $contact->getName();
        $view->setHeaderText('Unlink ' . $contactName .' from Quickbooks?');
        $messageText = 'Are you sure you want to unlink ' . $contactName . ' from Quickbooks?';
        $view->setMessageText($messageText);
        $view->setSubmitButtonLabel('Yes');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $contact->setProperty('contact_qb_id', NULL);
            if ($contact->save()) {
                $newUrlAttributes = array(
                    'controller' => 'contact',
                    'action' => 'view',
                    'id' => $contactId
                );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionLinkQBContact($attributes) {
        if (!Permission::verifyByRef('link_contacts_to_qb_contacts')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id']) || !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $type = $attributes['type'];
        $contactId = $attributes['id'];
        $contact = ContactFactory::getModelById($contactId);
        if (empty($contact) || !empty($contact->getProperty('contact_qb_id'))) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $form = new GI_Form('link_qb_contact');
        if (!$form->wasSubmitted()) {
            $key = 'qb_data_imported_' . $type;
            if (!apcu_exists($key) || !apcu_fetch($key)) {
                $importer = new QBContactImporter();
                if ($importer->importFromQB($type)) {
                    apcu_store($key, '1', 900);
                }
            }
        }
        $view = new ContactQBLinkFormView($form, $contact, $type);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {

            $contactQBId = filter_input(INPUT_POST, 'contact_qb_id');
            if (!empty($contactQBId)) {
                $contactQB = ContactQBFactory::getModelById($contactQBId);
                if (!empty($contactQB)) {
                    $contact->setProperty('contact_qb_id', $contactQBId);
                    $contactQB->setProperty('import_required', 1);
                    $contactQB->setProperty('export_required', 1);
                    if ($contact->save() && $contactQB->save()) {
                        $newUrlAttributes = array(
                            'controller' => 'contact',
                            'action' => 'view',
                            'id' => $contactId
                        );
                        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                            $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                            $success = 1;
                        } else {
                            GI_URLUtils::redirect($newUrlAttributes);
                        }
                    }
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionAutocompContactQB($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)) {
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        if (isset($attributes['curVal'])) {
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            $finalLabel = array();
            $finalValue = array();
            $finalResult = array();
            $finalTotal = array();
            $finalBalance = array();
            foreach ($curVals as $contactQBId) {
                $contactQB = ContactQBFactory::getModelById($contactQBId);
                if ($contactQB) {
                    $displayName = $contactQB->getProperty('display_name');
                    $individualName = $contactQB->getIndividualName();
                    $companyName = $contactQB->getProperty('company');
                    $finalLabel = $displayName;
//                    if (!empty($companyName)) {
//                        $finalLabel .= ' / ' . $companyName;
//                    }
//                    if (!empty($individualName)) {
//                        $finalLabel .= ' / ' . $individualName;
//                    }
                    $finalLabel[] = $finalLabel;
                    $finalValue[] = $contactQBId;
                    $finalResult[] = '<span class="result_text">' . $displayName . '</span>';
                }
            }
            $results = array(
                'label' => $finalLabel,
                'value' => $finalValue,
                'autoResult' => $finalResult,
                'total'=>$finalTotal,
                'balance'=>$finalBalance
            );
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            $type = 'supplier';
            if (isset($attributes['type'])) {
                $type = $attributes['type'];
            }
            $search = ContactQBFactory::search()
                    ->filterByTypeRef($type)
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit())
                    ->andIf()
                    ->filterGroup()
                    ->filterLike('display_name', '%' . $term . '%')
                    ->orIf()
                    ->filterLike('company', '%' . $term . '%')
                    ->orIf()
                    ->filterLike('last_name', '%' . $term . '%')
                    ->orIf()
                    ->filterLike('first_name', '%' . $term . '%')
                    ->closeGroup()
                    ->andIf();
            
            $search->orderByLikeScore('display_name', $term)
                    ->orderByLikeScore('company', $term)
                    ->orderByLikeScore('last_name', $term)
                    ->orderByLikeScore('first_name', $term);
            $contactQBs = $search->select();
            $results = array();
            foreach ($contactQBs as $contactQB) {
                $displayName = $contactQB->getProperty('display_name');
                $individualName = $contactQB->getIndividualName();
                $companyName = $contactQB->getProperty('company');
                $finalLabel = $displayName;
//                if (!empty($companyName)) {
//                    $finalLabel .= ' / ' . $companyName;
//                }
//                if (!empty($individualName)) {
//                    $finalLabel .= ' / ' . $individualName;
//                }
                $contactQBInfo = array(
                    'label' => $finalLabel,
                    'value' => $contactQB->getId(),
                    'autoResult' => '<span class="result_text">' . $displayName . '</span>',
                );
                $results[] = $contactQBInfo;
            }
            return $results;
        }
    }

    public function actionAddWarehouse($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'warehouse';
        }
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('add_warehouse');
  
        $contact = ContactFactory::buildNewModel($type);
        
        if (!$contact->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }

        $catTypeRef = 'internal';
        $contact->setDefaultContactCatTypeRef($catTypeRef);

        $pId = NULL;
        if (isset($attributes['pId'])) {
            $pId = $attributes['pId'];
        }
        if (is_null($contact) || empty($pId)) {
            GI_URLUtils::redirectToError(4000);
        }

        $addressModel = $contact->getAddressModel();
        if (empty($addressModel)) {
            GI_URLUtils::redirectToError(4000);
        }
        $parentContact = ContactFactory::getModelById($pId);
        if (empty($parentContact)) {
            GI_URLUtils::redirectToError(4000);
        }
        
        $parentAddress = $parentContact->getContactInfo('address');
        if (empty($parentAddress)) {
            GI_URLUtils::redirectToError(4000);
        }
        $addressModel->setProperty('contact_info_address.addr_country', $parentAddress->getProperty('contact_info_address.addr_country'));
        $contact->setAddressModel($addressModel);
        $pInternal = NULL;
        if (isset($attributes['pInternal'])) {
            $pInternal = $attributes['pInternal'];
        }
        
        $view = $contact->getFormView($form);
        $view->setAddressCountryReadOnly(true);
        if(isset($attributes['title'])){
            $view->setStartTitle($attributes['title']);
        }
        
        if(!empty($catTypeRef)){
            $view->setCatTypeRefArray(explode(',', $catTypeRef));
        } 
        
        $view->setAjax($ajax);
        $view->setPid($pId);
        $view->setPInternal(1);
        $view->buildForm();

        $success = 0;
        $contactId = '';

        if ($form->wasSubmitted()) {
            $targetTypeRef = filter_input(INPUT_POST, 'type_ref');
            if (!empty($targetTypeRef)) {
                $contact = ContactFactory::buildNewModel($targetTypeRef);
            }
        }
        
        if ($contact->handleFormSubmission($form, $pId)) {
            $contactCat = $contact->getContactCat();
            $contactCatTypeTitle = '';
            if (!empty($contactCat)) {
                $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
            }
            LogService::logAdd($contact, $contactCatTypeTitle . $contact->getName());
            LogService::setIgnoreNextLogView(true);
            $success = 1;
            $contactId = $contact->getId();
            if(!$ajax){
                GI_URLUtils::redirect(array(
                    'controller' => 'contact',
                    'action' => 'view',
                    'id' => $contactId
                ));
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $contact->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'add',
            'type' => $type
        ));
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['autocompId'] = $contactId;
            if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                $returnArray['newUrl'] = 'refresh';
            }
        }
        return $returnArray;
    }

    public function actionEditWarehouse($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }

        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }

        if (!$contact->isEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }

        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }

        $form = new GI_Form('edit_warehouse');

        $view = $contact->getFormView($form);
        $view->setAddressCountryReadOnly(true);
        $view->buildForm();
        if ($form->wasSubmitted()) {
            $targetTypeRef = filter_input(INPUT_POST, 'type_ref');
            if (!empty($targetTypeRef)) {
                $contact = ContactFactory::changeModelType($contact, $targetTypeRef);
            }
        }
        if ($contact->handleFormSubmission($form)) {
            $contactCat = $contact->getContactCat();
            $contactCatTypeTitle = '';
            if (!empty($contactCat)) {
                $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
            }
            LogService::logEdit($contact, $contactCatTypeTitle . $contact->getName());
            LogService::setIgnoreNextLogView(true);
            GI_URLUtils::redirect(array(
                'controller' => 'contact',
                'action' => 'view',
                'id' => $id
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $contact->getBreadcrumbs();
        $editLink = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'edit',
                    'id' => $id
        ));
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;

        return $returnArray;
    }
    
    /**
     * Contact detail view for public pages
     * @param type $attributes
     * @return type
     */
    public function actionViewPublicInfo($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $view = $contact->getPublicDetailView();
        if (GI_URLUtils::isAJAX()) {
            $view->setAddWrap(false);
        }
        $contactCat = $contact->getContactCat();
        $contactCatTypeTitle = '';
        if (!empty($contactCat)) {
            $contactCatTypeTitle = $contactCat->getTypeTitle(false) . ': ';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $contact->getBreadcrumbs();
        return $returnArray;
    }
    
    /**
     * Autocomplete for public pages
     * @param type $attributes
     * @return type
     */
    public function actionAutocompPublicContact($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        //Only search vendor category type 
        $attributes['catTypeRefs'] = 'vendor';
        $useAddrBtn = false;
        $addrInfo = array();

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            
            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach($curVals as $contactId){
                $contact = ContactFactory::getModelById($contactId);
                if($contact){
                    $acResult = $contact->getAutocompResult(NULL, $useAddrBtn, $addrInfo);

                    foreach($acResult as $key => $val){
                        if($key == 'addrBtn' || $key == 'addrView'){
                            $results[$key] = $val;
                            continue;
                        } elseif(!isset($results[$key])){
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            
            return $results;
        } else {
            if (isset($_REQUEST['term'])) {
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $contactSearch = ContactFactory::search()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            $pageNumber = 1;
            if (isset($attributes['pageNumber'])) {
                $pageNumber = (int) $attributes['pageNumber'];
                $contactSearch->setPageNumber($pageNumber);
            }
            $usedTypeRefs = array(
                'ind',
                'org',
            );

            $fieldMap = $this->getContactFieldMap();
            if (isset($attributes['type']) && !empty($attributes['type'])) {
                $usedTypeRefs = array();
                $typeRefs = explode(',', $attributes['type']);
                $contactSearch->filterGroup();
                foreach ($typeRefs as $typeRef) {
                    $usedTypeRefs[] = $fieldMap[$typeRef];
                    $contactSearch->filterByTypeRef($typeRef);
                    $contactSearch->orIf();
                }
                $contactSearch->closeGroup();
                $contactSearch->andIf();
            }

            if (!empty($term)) {
                $likeScoreColumns = array();

                $contactSearch->filterGroup();
                if (in_array('org', $usedTypeRefs)) {
                    $orgCols = array(
                        'org.title',
                        'org.doing_bus_as'
                    );
                    $likeScoreColumns = array_merge($likeScoreColumns, $orgCols);
                    $contactSearch->filterGroup()
                            ->filterTermsLike($orgCols, $term)
                            ->andIf()
                            ->filter('org.status', 1)
                            ->closeGroup();

                    $contactSearch->orIf();
                }

                if (in_array('ind', $usedTypeRefs)) {
                    $indCols = array(
                        'ind.first_name',
                        'ind.last_name'
                    );
                    $likeScoreColumns = array_merge($likeScoreColumns, $indCols);
                    $contactSearch->filterGroup()
                            ->filterTermsLike($indCols, $term)
                            ->andIf()
                            ->filter('ind.status', 1)
                            ->closeGroup();

                    $contactSearch->orIf();
                }
                $contactSearch->closeGroup();
                $contactSearch->andIf();
                $contactSearch->orderByLikeScore($likeScoreColumns, $term);
            }


            //If there is category typeRef parameter, add category conditions
            if (isset($attributes['catTypeRefs'])) {
                $catTypeRefs = $attributes['catTypeRefs'];
                $catTypeRefArray = explode(',', $catTypeRefs);
                $contactTableName = ContactFactory::getDbPrefix() . 'contact';
                $contactSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                        ->join('contact_cat_type', 'id', 'cat', 'contact_cat_type_id', 'cat_type')
                        ->filterIn('cat_type.ref', $catTypeRefArray)
                        ->groupBy('id');
            }
            $contacts = $contactSearch->select();

            $results = array();

            foreach ($contacts as $contact) {
                /* @var $item AbstractContact */
                $itemInfo = $contact->getAutocompResult($term, $useAddrBtn, $addrInfo);
                $results[] = $itemInfo;
            }

            $itemsPerPage = $contactSearch->getItemsPerPage();
            $count = $contactSearch->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            return $results;
        }
    }

    public function actionAddSuspension($attributes) {
        if (!isset($attributes['cId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $contact = ContactFactory::getModelById($attributes['cId']);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('add_suspension');
        if ($form->wasSubmitted()) {
            $typeRef = filter_input(INPUT_POST, 'suspenson_type_ref');
        } else {
            $typeRef = 'suspension';
        }
        $suspension = SuspensionFactory::buildNewModel($typeRef);
        if (empty($contact) || empty($suspension) || !$suspension->isAddable() || !$contact->isSuspendable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $suspension->setContact($contact);

        $view = $suspension->getFormView($form);
        $view->buildForm();
        $success = 0;

        if ($suspension->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $contact->getProfileViewURLAttrs();
            if (!$ajax) {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = 'refresh';
        }
        return $returnArray;
    }

    public function actionEditSuspension($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $suspension = SuspensionFactory::getModelById($attributes['id']);
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('edit_suspension');

        if (empty($suspension) || !$suspension->isEditable()) {
            GI_URLUtils::redirectToError(2000);
        }
        if ($form->wasSubmitted()) {
            $typeRef = filter_input(INPUT_POST, 'suspenson_type_ref');
            if ($typeRef !== $suspension->getTypeRef()) {
                $suspension = SuspensionFactory::changeModelType($suspension, $typeRef);
            }
        }

        $view = $suspension->getFormView($form);
        $view->buildForm();
        $success = 0;

        if ($suspension->handleFormSubmission($form)) {
            $success = 1;
            $contact = $suspension->getContact();
            $redirectURLAttributes = $contact->getProfileViewURLAttrs();
            if (!$ajax) {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = 'refresh';
        }
        return $returnArray;
    }
    
    public function actionDeleteSuspension($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $suspension = SuspensionFactory::getModelById($attributes['id']);
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('delete_suspension');

        if (empty($suspension) || !$suspension->isDeleteable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Remove Suspension?');
        $view->setMessageText('Are you sure you wish to remove this suspension?');
        $view->setSubmitButtonLabel('Remove');

        $view->buildForm();
        $success = 0;
        $contact = $suspension->getContact();
        if ($form->wasSubmitted() && $form->validate()) {
            if ($suspension->softDelete()) {
                $success = 1;
                $redirectURLAttributes = $contact->getProfileViewURLAttrs();
                if (!$ajax) {
                    GI_URLUtils::redirect($redirectURLAttributes);
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = 'refresh';
        }
        return $returnArray;
    }

    public function actionRemoveSuspension($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $suspension = SuspensionFactory::getModelById($attributes['id']);
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('remove_suspension');

        if (empty($suspension) || !$suspension->isDeleteable()) {
            GI_URLUtils::redirectToError(2000);
        }
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Remove Suspension?');
        $view->setMessageText('Are you sure you wish to remove this suspension?');
        $view->setSubmitButtonLabel('Remove');

        $view->buildForm();
        $success = 0;
        $contact = $suspension->getContact();
        if ($form->wasSubmitted() && $form->validate()) {
            if ($suspension->remove()) {
                $success = 1;
                $redirectURLAttributes = $contact->getProfileViewURLAttrs();
                if (!$ajax) {
                    GI_URLUtils::redirect($redirectURLAttributes);
                }
            }
        }

        $returnArray = GI_Controller::getReturnArray($view);
        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = 'refresh';
        }
        return $returnArray;
    }

}
