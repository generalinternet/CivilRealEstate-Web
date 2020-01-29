<?php
/**
 * Description of AbstractUserController
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.2
 */
class AbstractUserController extends GI_Controller {

    protected $pageLinks = 2;
    protected $userItemsPerPage = 10;
    
    public function actionIndex($attributes) {
        if (!Permission::verifyByRef('view_users')) {
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
        
        $userType = 'user';
        if(isset($attributes['type'])){
            $userType = $attributes['type'];
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }

        $search = UserFactory::searchRestricted()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        if(!empty($userType) && $userType != 'user'){
            $search->filterByTypeRef($userType);
        }
        
        //Get searchView
        $sampleUser = UserFactory::buildNewModel();
        
        $sampleUser->addCustomFiltersToDataSearch($search);
        
        $redirectArray = array();
        $searchView = $sampleUser->getSearchForm($search, $userType, $redirectArray);
        $sampleUser->addSortingToDataSearch($search);
        
        $pageBarLinkProps = $attributes;
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleUser)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $users = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleUser->getUIRolodexCols();
                $uiTableView = new UIRolodexView($users, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleUser->getUITableCols();
                $uiTableView = new UITableView($users, $uiTableCols, $pageBar);
            }
            
            $view = new UserIndexView($users, $uiTableView, $sampleUser, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }

    public function actionEdit($attributes) {
        // ID attributes check
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        // Permission check
        $userId = $attributes['id'];
        $curUserId = Login::getUserId();
        if (!Permission::verifyByRef('edit_users') && $userId!=$curUserId) {
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $user = UserFactory::getModelById($userId);
        // Model by ID check
        if (empty($user)) {
            GI_URLUtils::redirectToError(4001);
        }
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        $avatarUploader = GI_UploaderFactory::buildImageUploader('user_edit_' . $userId);
        $avatarUploader->setFilesLabel('Avatar');
        $avatarUploader->setBrowseLabel('Upload Image');
        $ppFolder = $user->getSubFolderByRef('profile_pictures');
        $avatarUploader->setTargetFolder($ppFolder);
        
        $fileUploader = GI_UploaderFactory::buildUploader('user_edit_files_' . $userId);
        $myFilesFolder = $user->getFolder();
        $fileUploader->setTargetFolder($myFilesFolder);
        $fileUploader->setDownloadZip(true, false);
        
        $form = new GI_Form('edit_user');
//        $view = new UserEditView($form, $user);
        $view = $user->getFormView($form);
        $view->buildForm();
        $view->setAvatarUploader($avatarUploader);
        $view->setFileUploader($fileUploader);
        $controllerName = GI_StringUtils::sanitizeControllerClassName(get_called_class());
        $success = 0;
        if($user->handleFormSubmission($form)){
            $success = 1;
            $redirectURLAttributes = $user->getViewURLAttrs();
            if($ajax){
                //Change the view to a detail view
                $view = $user->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }

        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $user->getBreadcrumbs();
        $editUserURL = GI_URLUtils::buildURL(array(
            'controller' => $controllerName,
            'action' => 'edit',
            'id' => $userId
        ));
        $breadcrumbs[] = array(
            'label' => Lang::getString('edit_user'),
            'link' => $editUserURL
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new data
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar");historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        } else {
            //Set the list bar with index view
            $returnArray['listBarURL'] = $user->getListBarURL();
        }
        return $returnArray;
    }

    public function actionAdd($attributes) {
        if (!Permission::verifyByRef('add_users')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = '';
        }
        $user = UserFactory::buildNewModel($type);

        if (empty($user)) {
            GI_URLUtils::redirectToError(4000);
        }
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        $form = new GI_Form('add_user');
        $view = $user->getFormView($form);
        $view->buildForm();
        $success = 0;
        if($user->handleFormSubmission($form)){
            LogService::logAdd($user, $user->getViewTitle(false) . ': ' . $user->getFullName());
            LogService::setIgnoreNextLogView(true);
            $success = 1;
            $redirectURLAttributes = $user->getViewURLAttrs();
            if($ajax){
                //Change the view to a detail view
                $view = $user->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $user->getBreadcrumbs();
        $addUserURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'add'
        ));
        $breadcrumbs[] = array(
            'label' => Lang::getString('add_user'),
            'link' => $addUserURL
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if($ajax){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new data
                $curId = $user->getId();
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$curId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        } else {
            //Set the list bar with index view
            $returnArray['listBarURL'] = $user->getListBarURL();
        }
        return $returnArray;
    }
    
    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $userId = $attributes['id'];
        $curUserId = Login::getUserId();
        if (!Permission::verifyByRef('view_users') && $userId!=$curUserId) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $user = UserFactory::getModelById($userId);
        if (empty($user)) {
            GI_URLUtils::redirectToError(4001);
        }
        $ajax = false;
        if(isset($attributes['ajax'])){
            $ajax = $attributes['ajax'];
        }
        if ($userId == Login::getUserId()) {
            if(isset($attributes['pageNumber'])){
                $pageNumber = $attributes['pageNumber'];
            } else {
                $pageNumber = 1;
            }
            $notificationSearch = NotificationFactory::search();
            $orderbycase = $notificationSearch->newCase()
                        ->filter('viewed', 1)
                        ->setThen(0)
                        ->setElse(1);
            $notificationSearch->filter('to_id', $userId)
                    ->orderByCase($orderbycase, 'DESC')
                    ->orderBy('inception', 'DESC')
                    ->setPageNumber($pageNumber)
                    ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage());
            
            $notifications = $notificationSearch->select();

            $linkArray = array(
                'controller' => 'user',
                'action' => 'view',
                'id' => $userId
            );
            $pageBar = $notificationSearch->getPageBar($linkArray);
            $uiTableCols = Notification::getUITableCols();
            $uiTableView = new NotificationUITableView($notifications, $uiTableCols, $pageBar);
            $notificationTableView = $uiTableView;
        } else {
            $notificationTableView = NULL;
        }
        $view = $user->getDetailView();
        $view->setNotificationTableView($notificationTableView);
        LogService::logView($user, $user->getViewTitle(false) . ': ' . $user->getFullName());
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $user->getBreadcrumbs();
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if ($ajax) {
            $returnArray['jqueryCallbackAction'] = 'setCurrentOnListBar('.$userId.');';
        }
        return $returnArray;
    }
    
    public function actionDelete($attributes, $deleteProperties = array()) {
        $redirectProps = array(
            'controller' => 'user',
            'action' => 'index'
        );

        if(isset($attributes['targetId'])){
            $redirectProps['targetId'] = $attributes['targetId'];
        } else {
            $redirectProps['targetId'] = 'list_bar';
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'UserFactory',
            'redirectOnSuccess' => $redirectProps,
            'newUrlRedirect' => 1,
        );

        return parent::actionDelete($attributes, $deleteProperties);
    }

    public function actionSendConfirmationEmail($attributes) {
        if (!isset($attributes['id']) || !isset($attributes['ajax']) || !($attributes['ajax']) == 1) {
            return NULL;
        }
        $id = $attributes['id'];
        $form = new GI_Form('send_confirmation_email');
        $view = new GenericAcceptCancelFormView($form);
        $user = UserFactory::getModelById($id);
        if (empty($user)) {
            GI_URLUtils::redirectToError();
        }
        $userName = $user->getFullName();
        $userEmail = $user->getProperty('email');
        $view->setHeaderText('Send Confirmation Email');
        $view->setMessageText('Send confirmation email to ' . $userName . ' (' . $userEmail . ')?');
        $view->setSubmitButtonLabel('Yes');

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($user->sendConfirmEmailAddressEmail()) {
                $newUrl = 'refresh';
                $success = 1;
            }

            //$newUrlAttributes['jqueryAction'] = 'giModalClose();';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    public function actionAutocompUser($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            
            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach($curVals as $userId){
                $user = UserFactory::getModelById($userId);
                if($user){
                    $acResult = $user->getAutocompResult();

                    foreach($acResult as $key => $val){
                        if(!isset($results[$key])){
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            $search = UserFactory::searchRestricted()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $search->setPageNumber($pageNumber);
            }
            
            if (isset($attributes['type']) && !empty($attributes['type'])) {
                $typeRefs = explode(',', $attributes['type']);
                $search->filterGroup();
                foreach ($typeRefs as $typeRef) {
                    $search->filterByTypeRef($typeRef);
                    $search->orIf();
                }
                $search->closeGroup();
                $search->andIf();
            }
            
            if (!empty($term)) {
                $nameTerms = explode(' ', $term);
                $search->filterGroup();
                foreach ($nameTerms as $nameTerm){
                    if ($nameTerm != ''){
                        $search->filterLike('first_name', '%'.$nameTerm.'%')
                            ->orIf()
                            ->filterLike('last_name', '%'.$nameTerm.'%')
                            ->orIf()
                            ->filterLike('email', '%'.$nameTerm.'%');
                    }
                }
                $search->closeGroup();

                $cases = array();
                if (count($nameTerms) > 1) {
                    $firstTerm = $nameTerms[0];
                    $cases[] = $search->newCase()
                                ->filter('first_name', $firstTerm.'%', 'LIKE')
                                ->setThen(3)
                                ->setElse(0);
                    $cases[] = $search->newCase()
                                ->filter('last_name', $firstTerm.'%', 'LIKE')
                                ->setThen(3)
                                ->setElse(0);
                }
                $cases[] = $search->newCase()
                            ->filter('first_name', $term.'%', 'LIKE')
                            ->setThen(1)
                            ->setElse(0);
                $cases[] = $search->newCase()
                            ->filter('first_name', $term)
                            ->setThen(3)
                            ->setElse(0);
                $cases[] = $search->newCase()
                            ->filter('last_name', $term.'%', 'LIKE')
                            ->setThen(1)
                            ->setElse(0);
                $cases[] = $search->newCase()
                            ->filter('last_name', $term)
                            ->setThen(3)
                            ->setElse(0);
                $cases[] = $search->newCase()
                            ->filter('email', $term.'%', 'LIKE')
                            ->setThen(1)
                            ->setElse(0);
                $cases[] = $search->newCase()
                            ->filter('email', $term)
                            ->setThen(3)
                            ->setElse(0);

                $search->orderByCase($cases,'DESC');
            }

            $users = $search->select();
            $results = array();

            foreach($users as $user){
                $highestRoleGroupRank = RoleGroup::getUserHighestRoleGroupRank('other');
                $usersRoleRank = RoleGroup::getUserHighestRoleGroupRank('self', $user->getId());
                if($highestRoleGroupRank >= $usersRoleRank){
                    /* @var $invItem InvItem */
                    $userInfo = $user->getAutocompResult($term);
                    $results[] = $userInfo;
                }
            }
            
            $itemsPerPage = $search->getItemsPerPage();
            $count = $search->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            return $results;
        }
    }
    
    public function actionActivityIndex($attributes) {
        $sampleActivity = RecentActivityFactory::buildNewModel('activity');
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
        $search = RecentActivityFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        $search->setSearchValue('search_type', 'advanced');

        if (empty($queryId) && isset($attributes['id'])) {
            $search->setSearchValue('user_id', $attributes['id']);
        }
        $sampleActivity->addCustomFiltersToDataSearch($search);

        $redirectArray = array();
        $sampleActivity->addOrderBysToDataSearch($search);
        $searchView = $sampleActivity->getSearchForm($search,$redirectArray);
        
        $pageBarLinkProps = $attributes;
        if (isset($attributes['queryId'])) {
            unset($pageBarLinkProps['queryId']);
        }
        if (isset($attributes['pageNumber'])) {
            unset($pageBarLinkProps['pageNumber']);
        }
        if (isset($attributes['tabbed'])) {
            unset($pageBarLinkProps['tabbed']);
            $searchView = NULL;
        }

        $actionResult = ActionResultFactory::buildActionResult();
        if (!empty($searchView)) {
            $actionResult->setSearchView($searchView);
        }
        $actionResult->setSampleModel($sampleActivity)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);

        if (!GI_URLUtils::getAttribute('search')) {
            $activities = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleActivity->getUIRolodexCols();
                $uiTableView = new UIRolodexView($activities, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleActivity->getUITableCols();
                $uiTableView = new UITableView($activities, $uiTableCols, $pageBar);
            }
            $pageBar->setItemsPerPage(10);
            
            $view = new RecentActivityIndexView($activities, $uiTableView, $sampleActivity, $searchView);
            if (GI_URLUtils::isAJAX()) {
                $view->setIsTabbed(true);
            }
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        } 


        $returnArray = $actionResult->getIndexReturnArray();

        return $returnArray;
    }
    
    public function actionViewNotificationSettings($attributes) {
        if (isset($attributes['userId'])) {
            $user = UserFactory::getModelById($attributes['userId']);
        } else{
            $user = Login::getUser();
        }
        if (empty($user)) {
            GI_URLUtils::redirectToError(3000);
        }
        //TODO - permission check
        $view = $user->getNotificationSettingsView();
        if (isset($attributes['contentOnly']) && $attributes['contentOnly'] == '1') {
            $view->setOnlyBodyContent(true);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionEditNotificationSettings($attributes) {
        if (!isset($attributes['id']) && !isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['userId']) && Permission::verifyByRef('super_admin')) {
            $userId = $attributes['userId'];
        } else {
            $userId = Login::getUserId();
        }
        $user = UserFactory::getModelById($userId);
        if (empty($user)) {
            GI_URLUtils::redirectToError(2000);
        }
        $eventId = NULL;
        if (isset($attributes['id'])) {
            $settings = SettingsFactory::getModelById($attributes['id']);
            if (empty($settings)) {
                GI_URLUtils::redirectToError(2000);
            }
        } else {
            $type = $attributes['type'];
            if ($type !== 'notification_global') {
                if (!isset($attributes['eventId'])) {
                    GI_URLUtils::redirectToError(2000);
                }
                $eventId = $attributes['eventId'];
            }
            $settings = $user->getNotificationSettingsModel($eventId, $type);
            $globalSettings = $user->getGlobalNotificationSettingsModel();
            $settings->setPropertiesFromModel($globalSettings);
            $settings->setProperty('settings_notif.event_id', $eventId);
        }
        if ($settings->getProperty('user_id') != $userId) {
            GI_URLUtils::redirectToError(2000);
        }
        $form = new GI_Form('edit_settings');
        $view = $settings->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($settings->handleFormSubmission($form)) {
            $success = 1;
            $newUrl = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

}
