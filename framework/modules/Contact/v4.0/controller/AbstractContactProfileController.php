<?php

/**
 * Description of AbstractContactProfileController
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactProfileController extends GI_Controller {

    public function actionIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'client';
        }
        
        if (isset($attributes['pending'])) {
            $pending = $attributes['pending'];
        } else {
            $pending = 0;
        }
        $sampleContactCat = ContactCatFactory::buildNewModel($type);
        if (!$sampleContactCat->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $contactType = 'org';
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

        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $contactSearch = ContactFactory::search()
                ->join('contact_type', 'id', $contactTableName, 'contact_type_id', 'contact_type');

        $contactSearch->filterByTypeRef($contactType);

        if ($type != 'category') {
            $typeRefsArray = ContactCatFactory::getTypeRefArray($type);
            $topLevelType = $typeRefsArray[0];
            $contactSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                    ->join('contact_cat_type', 'id', 'cat', 'contact_cat_type_id', 'cat_type')
                    ->filter('cat_type.ref', $topLevelType)
                    ->groupBy('id');
            if ($topLevelType === 'client') {
                $contactSearch->join('contact_cat_client', 'parent_id', 'cat', 'id', 'client')
                        ->join('contact_cat_client_type', 'id', 'client', 'contact_cat_client_type_id', 'client_type');
                $contactSearch->filter('client_type.ref', $typeRefsArray[1]);
            } else if ($topLevelType === 'vendor') {
                $contactSearch->join('contact_cat_vendor', 'parent_id', 'cat', 'id', 'vendor')
                        ->join('contact_cat_vendor_type', 'id', 'vendor', 'contact_cat_vendor_type_id', 'vendor_type');
                $contactSearch->filter('vendor_type.ref', $typeRefsArray[1]);
            }
        }
        $contactSearch->filter('pending', $pending);

        $contactSearch->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $sampleContact = ContactFactory::buildNewModel($contactType);
        $redirectArray = array();
        $pageBarLinkProps = $attributes;
        $searchView = $sampleContactCat->getProfileSearchForm($contactSearch, $type, $sampleContact, $redirectArray);
        $searchView->setUseBasicSearch(false);
        $searchView->setSearchType('advanced');
        
        $sampleContact->setDefaultContactCatTypeRef($type);
        $sampleContact->addCustomFiltersToProfileDataSearch($contactSearch);
        $sampleContact->addSortingToProfileDataSearch($contactSearch);
        

        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleContactCat)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if (!GI_URLUtils::getAttribute('search')) {
            $contacts = $contactSearch->select();
            $pageBar = $contactSearch->getPageBar($pageBarLinkProps);
            $listTitle = '';
            if ($contactType) {
                $sampleContact = ContactFactory::buildNewModel($contactType);
                $listTitle = $sampleContact->getViewTitle(false);
            }
            if ($targetId == 'list_bar') {
                //Tile style view
               // $uiTableCols = $sampleContactCat->getUIRolodexCols();
                $uiTableCols = $sampleContactCat->getProfileUIRolodexCols();
                //$uiTableView = new UIRolodexView($contacts, $uiTableCols, $pageBar);
                $uiTableView = new ContactProfileUIRolodexView($contacts, $uiTableCols, $pageBar);
                $uiTableView->setGetURLMethod('getViewProfileURL');
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if (isset($attributes['curId']) && $attributes['curId'] != '') {
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleContactCat->getUITableCols();
                $uiTableView = new UITableView($contacts, $uiTableCols, $pageBar);
            }

            $view = new ContactProfileIndexView($contacts, $uiTableView, $sampleContactCat, $searchView);

            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        $sampleContact->setContactCat($sampleContactCat);
        $returnArray = $actionResult->getIndexReturnArray();
        if ($targetId == 'list_bar') {
            $returnArray['listBarURL'] = $sampleContact->getProfileListBarURL();
            $returnArray['listBarClass'] = 'loaded';
        }
        return $returnArray;
    }

    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$contact->isViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $ajax = false;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $view = $contact->getProfileDetailView();

        if (isset($attributes['tab'])) {
            $view->setCurTab($attributes['tab']);
        }

        $returnArray = GI_Controller::getReturnArray($view);
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = 'setCurrentOnListBar(' . $id . ');';
        }
        $returnArray['breadcrumbs'] = $contact->getBreadcrumbs();
        if (Login::getCurrentInterfacePerspectiveRef() === 'admin') {
            $returnArray['layoutView'] = 'mainLayoutView';
        }
       
        return $returnArray;
    }

    public function actionAdd($attributes) {
        if (isset($attributes['type'])) {
            $catType = $attributes['type'];
        } else {
            $catType = 'client';
        }
        $type = 'org';
        $contact = ContactFactory::buildNewModel($type);
        $cat = ContactCatFactory::buildNewModel($catType);

        if (!$cat->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }

        $contact->setContactCat($cat);
        $cat->setContact($contact);
        $step = 10;
        if (isset($attributes['tab'])) {
            $tab = $attributes['tab'];
        } else {
            $tab = 0;
        }
        $ajax = 0;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('profile');

        $form->setFormAction(GI_URLUtils::buildURL(array(
                    'controller' => 'contactprofile',
                    'action' => 'add',
                    'type' => $catType,
                    'step' => $step,
                    'tab' => $tab,
                    'ajax' => $ajax,
        )));
       // $view = new ContactOrgProfileBasicFormView($form, $contact);
      $view = $contact->getProfileFormView($form, false, $step);
        $view->setAjax($ajax);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        $redirectURL = NULL;
        if ($contact->handleProfileFormSubmission($form, $step)) {
            //Get next step
            $nextStep = $view->getSubmittedNextStep();
            if (!empty($nextStep)) {
                //Check if there is a child step
                $nextChildStep = $view->getNextChildStep($step, $tab);
                if ($nextChildStep != -1) {
                    //Get the current step and add the child step
                    $newUrlAttrs = $contact->getProfileStepNavURLAttrs($step, $ajax);
                    $newUrlAttrs['tab'] = $nextChildStep;
                } else {
                    //Move to the next step
                    $newUrlAttrs = $contact->getProfileStepNavURLAttrs($nextStep, $ajax);
                }
            } else {
                //Forward to the detail page
                $newUrlAttrs = $contact->getProfileViewURLAttrs();
                $redirectURL = GI_URLUtils::buildURL($newUrlAttrs);
            }
            if ($ajax) {
                $newUrlAttrs['ajax'] = $ajax;
                $newUrl = GI_URLUtils::buildURL($newUrlAttrs);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttrs);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);

        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = $newUrl;
            $returnArray['ajax'] = $ajax;
            if ($redirectURL) {
                //Reload listbar to update changes
                $curId = $contact->getId();
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", ' . $curId . ');historyPushState("reload", "' . $redirectURL . '", "main_window");';
            }
        }

        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) { //contact id
            GI_URLUtils::redirectToError(2000);
        }
        $contact = ContactFactory::getModelById($attributes['id']);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['step'])) {
            $step = $attributes['step'];
        } else {
            $step = 10;
        }

        if (isset($attributes['tab'])) {
            $tab = $attributes['tab'];
        } else {
            $tab = 0;
        }

        $ajax = 0;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'item';
        }
        $form = new GI_Form('qna_profile');

        $form->setFormAction(GI_URLUtils::buildURL(array(
                    'controller' => 'contactprofile',
                    'action' => 'edit',
                    'id' => $contact->getId(),
                    'step' => $step,
                    'tab' => $tab,
                    'ajax' => $ajax,
        )));
        $view = $contact->getProfileFormView($form, false, $step, $tab);
        $view->setAjax($ajax);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        $redirectURL = NULL;
        if ($contact->handleProfileFormSubmission($form, $step)) {
            //Get next step
            $nextStep = $view->getSubmittedNextStep();
            if (!empty($nextStep)) {
                //Check if there is a child step
                $nextChildStep = $view->getNextChildStep($step, $tab);
                if ($nextChildStep != -1) {
                    //Get the current step and add the child step
                    $newUrlAttrs = $contact->getProfileStepNavURLAttrs($step, $ajax);
                    $newUrlAttrs['tab'] = $nextChildStep;
                } else {
                    //Move to the next step
                    $newUrlAttrs = $contact->getProfileStepNavURLAttrs($nextStep, $ajax);
                }
            } else {
                //Forward to the detail page
                $newUrlAttrs = $contact->getProfileViewURLAttrs();
                $redirectURL = GI_URLUtils::buildURL($newUrlAttrs);
            }
            if ($ajax) {
                $newUrlAttrs['ajax'] = $ajax;
                $newUrl = GI_URLUtils::buildURL($newUrlAttrs);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttrs);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);

        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = $newUrl;
            $returnArray['ajax'] = $ajax;
            if ($redirectURL) {
                //Reload listbar to update changes
                $curId = $contact->getId();
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", ' . $curId . ');historyPushState("reload", "' . $redirectURL . '", "main_window");';
            }
        }
        if (Login::getCurrentInterfacePerspectiveRef() === 'admin') {
            $returnArray['layoutView'] = 'mainLayoutView';
        }
        return $returnArray;
    }

    public function actionApplication($attributes) {
        if (isset($attributes['id'])) {
            $application = ContactApplicationFactory::getModelById($attributes['id']);
        } else {
            if (!isset($attributes['type'])) {
                GI_URLUtils::redirectToError(2000);
            }
            $application = NULL;
            $userId = Login::getUserId();
            if (!empty($userId)) {
                $search = ContactApplicationFactory::search();
                $search->filter('user_id', $userId);
                $search->setItemsPerPage(1)
                        ->setPageNumber(1)
                        ->orderBy('id', 'ASC');
                $results = $search->select();
                if (!empty($results)) {
                    $application = $results[0];
                }
            }
            if (empty($application)) {
                $application = ContactApplicationFactory::buildNewModel($attributes['type']);
            }
        }
        if (empty($application)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form($application->getFormId());
        $view = $application->getFormView($form);
        $status = NULL;

        if (isset($attributes['sId'])) {
            $status = ContactApplicationStatusFactory::getModelById($attributes['sId']);
            if (empty($status) || ($status->getTypeRef() !== $application->getTypeRef())) {
                $status = NULL;
            }
        }
        if (empty($status)) {
            $status = $application->getCurrentStatus();
            if (empty($status)) {
                GI_URLUtils::redirectToError(2001);
            }
        }
        $backURL = NULL;
        $previousStatus = ContactApplicationStatusFactory::getPreviousStatusModelByStatusModel($status);
        if (!empty($previousStatus)) {
            $backURLAttrs = array(
                'controller' => 'contactprofile',
                'action' => 'application',
                'sId' => $previousStatus->getId(),
            );
            if (!empty($application->getId())) {
                $backURLAttrs['id'] = $application->getId();
            } else {
                $backURLAttrs['type'] = $application->getTypeRef();
            }
            $backURL = GI_URLUtils::buildURL($backURLAttrs);
        }
        if (!empty($backURL)) {
            $view->setBackButtonURL($backURL);
        }
        $view->buildForm($status);
        if ($application->handleFormSubmission($form, $status)) {
            $nextStatus = ContactApplicationStatusFactory::getNextStatusModelByStatusModel($status);
            if (!empty($status->getProperty('hold')) || empty($nextStatus)) {
                GI_URLUtils::redirect($application->getCompletedRedirectURLAttrs());
            }
            GI_URLUtils::redirect(array(
                'controller' => 'contactprofile',
                'action' => 'application',
                'id' => $application->getId(),
                'sId' => $nextStatus->getId(),
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionSendConfirmationEmail($attributes) {
        if (!isset($attributes['id']) || !isset($attributes['pId'])) {
            return NULL;
        }
        $id = $attributes['id']; //contact id
        $form = new GI_Form('send_confirmation_email');

        $contact = ContactFactory::getModelById($id);
        if (empty($contact) || !$contact->isIndividual()) {
            GI_URLUtils::redirectToError();
        }
        $pId = $attributes['pId'];
        $parentContactOrg = ContactFactory::getModelById($pId);
        if (empty($parentContactOrg) || !$parentContactOrg->isOrganization()) {
            GI_URLUtils::redirectToError();
        }
        $view = $contact->getSendConfirmEmailFormView($form, $parentContactOrg);
        if (!empty($contact->getId()) && $parentContactOrg->getProperty('contact_org.primary_individual_id') === $contact->getId()) {
            $view->setOrgLoginEmail($parentContactOrg->getEmailAddress());
        }
        
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($contact->handleSendConfirmEmailForm($form, $parentContactOrg)) {
            $newUrl = 'refresh';
            $success = 1;
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    public function actionAddPerson($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $contactOrg = ContactFactory::getModelById($attributes['id']);
        if (empty($contactOrg)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'ind';
        }
        $contactInd = ContactFactory::buildNewModel($type);
        if (empty($contactInd)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$contactInd->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $contactInd->setParentContactOrg($contactOrg);
        $ajax = 0;
        if (isset($attributes['ajax'])) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('add_person');

        $view = $contactInd->getProfileFormView($form, false);
     //   $view->setAjax($ajax);
        $view->buildForm();

        $success = 0;
        $newUrl = NULL;
        //    $redirectURL = NULL;
        if ($contactInd->handleProfileFormSubmission($form, 1)) {
            
            if (!ContactRelationshipFactory::establishRelationship($contactOrg, $contactInd)) {
                GI_URLUtils::redirectToError();
            }

            $newUrlAttrs = $contactInd->getProfileViewURLAttrs();

            if ($ajax) {
                $newUrlAttrs['ajax'] = $ajax;
                $newUrl = GI_URLUtils::buildURL($newUrlAttrs);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttrs);
            }
        }

        $returnArray = GI_Controller::getReturnArray($view);

        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = $newUrl;
            $returnArray['ajax'] = $ajax;
        }

        return $returnArray;
    }

    public function actionAutocompContact($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)) {
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        $addrFieldPrefix = '';
        $addrFieldSuffix = '';
        $addrTypeRef = 'address';

        if (isset($attributes['useAddrBtn']) && $attributes['useAddrBtn']) {
            $useAddrBtn = true;
            if (isset($attributes['addrFieldPrefix'])) {
                $addrFieldPrefix = $attributes['addrFieldPrefix'];
            }
            if (isset($attributes['addrFieldSuffix'])) {
                $addrFieldSuffix = $attributes['addrFieldSuffix'];
            }
            if (isset($attributes['addrTypeRef'])) {
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
                    $acResult = $contact->getProfileAutocompResult(NULL, $useAddrBtn, $addrInfo);

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
            $usedTypeRefs = array(
                'org',
            );
            
            $fieldMap = $this->getContactFieldMap();

            if(isset($attributes['type']) && !empty($attributes['type'])){
                $usedTypeRefs = array();
                $typeRefs = explode(',',$attributes['type']);
                $contactSearch->filterGroup();
                foreach($typeRefs as $typeRef){
                    $usedTypeRefs[] = $fieldMap[$typeRef];
                    $contactSearch->filterByTypeRef($typeRef);
                    $contactSearch->orIf();
                }
                $contactSearch->closeGroup();
                $contactSearch->andIf();
            }
            $orgType = 'org';
            if (isset($attributes['orgType'])) {
                $orgType = $attributes['orgType'];
            }
            $sampleContactOrg = ContactFactory::buildNewModel($orgType);
            if(!empty($term)){

                $sampleContactOrg->addNameFilterToProfileDataSearch($term, $contactSearch);
            }

            if (isset($attributes['catTypeRef'])) {
                $catTypeRef = $attributes['catTypeRef'];
                $contactTableName = ContactFactory::getDbPrefix() . 'contact';
                $contactSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                        ->join('contact_cat_type', 'id', 'cat', 'contact_cat_type_id', 'cat_type')
                        ->filter('cat_type.ref', $catTypeRef)
                        ->groupBy('id');
            }
            $sampleContactOrg->addCustomFiltersToDataSearch($contactSearch);

            $contacts = $contactSearch->select();

            $results = array();

            foreach ($contacts as $contact) {
                /* @var $item AbstractContact */
                $itemInfo = $contact->getProfileAutocompResult($term, $useAddrBtn, $addrInfo);
                $results[] = $itemInfo;
            }

            $itemsPerPage = $contactSearch->getItemsPerPage();
            $count = $contactSearch->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

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

    public function actionChangeSubscription($attributes) {
        if (!isset($attributes['id'])) { //contact id
            GI_URLUtils::redirectToError(2000);
        }
        $contact = ContactFactory::getModelById($attributes['id']);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        $contactCat = $contact->getContactCat();
        if (empty($contactCat)) {
            GI_URLUtils::redirectToError(1000);
        }
        if (!$contact->canSelectSubscription()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['step'])) {
            $step = $attributes['step'];
        } else {
            $step = 10;
        }
        $ajax = 0;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('payment_form');
        $form->setFormAction(GI_URLUtils::buildURL(array(
                    'controller' => 'contactprofile',
                    'action' => 'changeSubscription',
                    'id' => $contact->getId(),
                    'step' => $step,
                    'ajax' => $ajax,
        )));

        $view = $contactCat->getChangeSubFormView($form, false, $step);
        if (empty($view)) {
            GI_URLUtils::redirectToError(1000);
        }
        $view->setAjax($ajax);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        $redirectURL = NULL;
        if ($contactCat->handleChangeSubscriptionFormSubmission($form, $step)) {
            //Get next step
            $nextStep = $view->getSubmittedNextStep();
            if (!empty($nextStep)) {
                $newUrlAttrs = $contactCat->getChangeSubscriptionStepNavURLAttrs($nextStep, $ajax);
            } else {
                //Forward to the detail page
                $newUrlAttrs = $contact->getProfileViewURLAttrs();
                $newUrlAttrs['tab'] = 'payments';
                $redirectURL = GI_URLUtils::buildURL($newUrlAttrs);
            }
            if ($ajax) {
                $newUrlAttrs['ajax'] = $ajax;
                $newUrl = GI_URLUtils::buildURL($newUrlAttrs);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttrs);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);

        if ($ajax) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = $newUrl;
            $returnArray['ajax'] = $ajax;
            if ($redirectURL) {
                //Reload listbar to update changes
                $curId = $contact->getId();
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", ' . $curId . ');historyPushState("reload", "' . $redirectURL . '", "main_window");';
            }
        }
//        if (Login::getCurrentInterfacePerspectiveRef() === 'admin') {
//            $returnArray['layoutView'] = 'mainLayoutView';
//        }
        return $returnArray;
        
    }

    public function actionAddPaymentMethod($attributes) {
        $ajax = false;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = true;
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];

        $contact = ContactFactory::getModelById($id);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$contact->canAddPaymentMethod()) { 
            GI_URLUtils::redirectToAccessDenied();
        }
        $paymentProcessor = $contact->getPaymentProcessor();
        if (empty($paymentProcessor)) {
            GI_URLUtils::redirectToError(1000);
        }

        $form = new GI_Form('payment_form');
        $view = $paymentProcessor->getCreditCardFormView($form);
        $view->setIsEmbedded(false);

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $email = $contact->getEmailAddress();
            if (!$paymentProcessor->handleCreditCardFormSubmission($form, $email)) {
                $message = 'ERROR';
                $errorCode = $paymentProcessor->getErrorCode();
                if (!empty($errorCode)) {
                    $message .= ' (' . $errorCode . ')';
                }
                $errorMessage = $paymentProcessor->getErrorMessage();
                if (!empty($errorMessage)) {
                    $message .= ' - ' . $errorMessage;
                }
                if (!empty($message)) {
                    $form->addFieldError('card_errors', 'processing error', $message);
                }
            } else {
                $success = 1;
                if ($ajax) {
                    $newUrl = 'refresh';
                } else {
                    GI_URLUtils::redirect(array(
                        'controller' => 'contactprofile',
                        'action' => 'view',
                        'id' => $id,
                        'tab' => 'payments'
                    ));
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

    public function actionRemovePaymentMethod($attributes) {
        $ajax = false;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = true;
        }
        $id = $attributes['id']; //card id
        $cId = $attributes['cId'];
        if (empty($id) || empty($cId)) {
            GI_URLUtils::redirectToError(2000);
        }
        $contact = ContactFactory::getModelById($cId);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$contact->canRemovePaymentMethod()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $paymentMethods = $contact->getPaymentMethods();
        if (empty($paymentMethods)) {
            GI_URLUtils::redirectToError(2000);
        }
        $paymentMethod = NULL;
        foreach ($paymentMethods as $payMethod) {
            if ($payMethod['id'] == $id) {
                $paymentMethod = $payMethod;
                break;
            }
        }
        if (empty($paymentMethod)) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('remove_payment_method');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Remove Payment Method');
        $cardView = new CreditCardDetailView($paymentMethod);
        $cardView->setOnlyBodyContent(true);
        $view->setMessageText($cardView->getHTMLView() . '<br/><p>Are you sure you wish to remove this payment method?</p>');
        $view->setSubmitButtonLabel('Yes');

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {

            $paymentProcessor = $contact->getPaymentProcessor();
            if (empty($paymentProcessor)) {
                GI_URLUtils::redirectToError(1000);
            }
            if ($paymentProcessor->removePaymentMethod($contact, $paymentMethod['id'])) {
                $success = 1;
                if ($ajax) {
                    $newUrl = 'refresh';
                } else {
                    GI_URLUtils::redirect(array(
                        'controller' => 'contactprofile',
                        'action' => 'view',
                        'id' => $cId,
                        'tab' => 'payments'
                    ));
                }
            } else {
                GI_URLUtils::redirectToError(1000);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionSetPaymentMethodAsDefault($attributes) {
        $ajax = false;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $ajax = true;
        }
        $id = $attributes['id']; //card id
        $cId = $attributes['cId'];
        if (empty($id) || empty($cId)) {
            GI_URLUtils::redirectToError(2000);
        }
        $contact = ContactFactory::getModelById($cId);
        if (empty($contact)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$contact->canChangeDefaultPaymentMethod()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $paymentMethods = $contact->getPaymentMethods();
        if (empty($paymentMethods)) {
            GI_URLUtils::redirectToError(2000);
        }
        $paymentMethod = NULL;
        foreach ($paymentMethods as $payMethod) {
            if ($payMethod['id'] == $id) {
                $paymentMethod = $payMethod;
                break;
            }
        }
        if (empty($paymentMethod)) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $form = new GI_Form('change_default_payment');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Change Default Payment Method');
        $cardView = new CreditCardDetailView($paymentMethod);
        $cardView->setOnlyBodyContent(true);
        $view->setMessageText($cardView->getHTMLView() . '<br/><p>Are you sure you wish to set this as your default payment method?</p>');
        $view->setSubmitButtonLabel('Yes');

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $paymentProcessor = $contact->getPaymentProcessor();
            if (empty($paymentProcessor)) {
                GI_URLUtils::redirectToError(1000);
            }
            $settings = $contact->getPaymentSettings($paymentProcessor->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                GI_URLUtils::redirectToError(1000);
            }

            $settings->setDefaultPaymentMethodId($paymentMethod['id']);

            if ($paymentProcessor->updateCustomerDefaultPaymentMethod($settings) && $settings->save()) {
                $success = 1;
                if ($ajax) {
                    $newUrl = 'refresh';
                } else {
                    GI_URLUtils::redirect(array(
                        'controller' => 'contactprofile',
                        'action' => 'view',
                        'id' => $cId,
                        'tab' => 'payments'
                    ));
                }
            } else {
                GI_URLUtils::redirectToError(1000);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    


}
