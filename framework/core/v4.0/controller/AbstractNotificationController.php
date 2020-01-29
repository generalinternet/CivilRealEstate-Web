<?php

/**
 * Description of AbstractNotificationController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.3
 */
abstract class AbstractNotificationController extends GI_Controller {

    public function actionIndex($attributes) {
        $sampleNotification = NotificationFactory::buildNewModel();
        if (!$sampleNotification->isIndexViewable()) {
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
        
        $search = NotificationFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        $sampleNotification->addCustomFiltersToDataSearch($search);

        $pageBarLinkProps = $attributes;

        $redirectArray = array();
        $searchView = NULL;
        $sampleNotification->addSortingToDataSearch($search);
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleNotification)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $notifications = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols =  $sampleNotification->getUIRolodexCols();
                $uiTableView = new UIRolodexView($notifications, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                $uiTableView->setLoadLinksWithAJAX(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                $uiTableCols = $sampleNotification->getUITableCols();
                $uiTableView = new UITableView($notifications, $uiTableCols, $pageBar);
            }
            
            $view = new GenericListBarView($notifications, $uiTableView, $sampleNotification);
            $view->setViewWrapClass('card_view');
            $view->setWindowTitle('Notifications');
            $view->setWindowIcon('bell');
            $view->setAddListBtns(false);
            
            $markNotificationsViewedURL = GI_URLUtils::buildURL(array(
                'controller' => 'notification',
                'action' => 'markAllNotificationsSeen',
                'id' => Login::getUserId(),
            ));
            $view->addWindowBtnHTML('<a href="' . $markNotificationsViewedURL . '" title="Mark All Notifications Viewed" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon primary check"></span></span><span class="btn_text">Mark All Viewed</span></a>');
            
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
    }

    //AJAX
    public function actionSetSocketId($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['socketId']) || !isset($attributes['socketUserId'])) {
            return array('content' => false);
        }
        $socketNickname = '';
        $socketId = $attributes['socketId'];
        $socketUserId = $attributes['socketUserId'];
        $userId = Login::getUserId();
        $login = Login::getLoginRecord();
        $joinConvoGroups = array();
        //use $socketLoggedIn to determine what the previous socketLoggedIn was and if we need to re-submit the information to the socket
        $socketLoggedIn = false;
        if(isset($attributes['socketLoggedIn']) && $attributes['socketLoggedIn'] == 1){
            $socketLoggedIn = true;
        }
        if($login){
            $login->setProperty('socket_id', $socketId);
            $login->setProperty('socket_user_id', $socketUserId);
            if ($login->save()) {
                $user = Login::getUser();
                if($user && $user->getEmailAddress() != 'system'){
                    $socketNickname .= $user->getFullName();
                }
                if(!$socketLoggedIn){
                    if(Permission::verifyByRef('super_admin')){
                        $joinConvoGroups[] = 'support';
                    }
                    foreach($joinConvoGroups as $joinConvoGroup){
                        $data = array(
                            'convoGroup' => $joinConvoGroup
                        );
                        NotificationService::socketEmit('join_convo_group', $data);
                    }
                    $socketData = array(
                        'userId' => $userId,
                        'nickname' => $socketNickname,
                        'chatUserType' => $user->getChatUserType()
                    );
                    NotificationService::socketEmit('update_socket_data', $socketData);
                }
                return array(
                    'content' => true,
                    'socketNickname' => $socketNickname,
                    'userId' => $userId,
                    'joinConvoGroups' => $joinConvoGroups
                );
            }
        }
        $tmpUser = UserFactory::buildNewModel();
        if($socketLoggedIn){
            NotificationService::socketEmit('leave_convo_group', array(
                'leaveAllGroups' => 1
            ));
            $socketData = array(
                'userId' => NULL,
                'nickname' => NULL,
                'chatUserType' => $tmpUser->getChatUserType()
            );
            NotificationService::socketEmit('update_socket_data', $socketData);
        }
        return array(
            'content' => false,
            'socketNickname' => $socketNickname . ' [' . $socketId . ']'
        );
    }

    //AJAX
    public function actionGetUnseenNotificationData($attributes) {
        $userId = Login::getUserId();
        $unreadNotifications = NotificationFactory::search()
                ->filter('to_id', $userId)
                ->filter('user_notified', 0)
                ->filter('viewed', 0)
                ->select();
        $user = UserFactory::getModelById($userId);
        $totalCount = $user->getNotificationCount();
        $notificationData = $this->buildBrowserNotificationData($unreadNotifications);
        $returnArray = array();
        $returnArray['content'] = true;
        $returnArray['totalCount'] = $totalCount;
        $returnArray['count'] = sizeof($notificationData);
        $returnArray['b_data'] = $notificationData;
        return $returnArray;
    }

    //AJAX
    public function actionMarkNotificationSeen($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['modelId'])) {
            return array('content' => NULL);
        }
        $modelId = $attributes['modelId'];
        $model = NotificationFactory::getModelById($modelId);
        $model->setProperty('user_notified', 1);
        if ($model->save()) {
            return array('content' => true);
        }
        array('content' => false);
    }

    protected function buildBrowserNotificationData($notificationModels) {
        $returnArray = array();
        foreach ($notificationModels as $notification) {
            $returnArray[] = $notification->getBrowserNotificationDataArray();
        }
        return $returnArray;
    }

    public function actionMarkAllNotificationsSeen($attributes) {
        if (!isset($attributes['id']) || !isset($attributes['ajax']) || !($attributes['ajax']) == 1) {
            return NULL;
        }
        $id = $attributes['id'];
        $form = new GI_Form('mark_notifications_seen');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Mark All Notifications as Viewed');
        $view->setMessageText('Are you sure you want to mark all notifications as viewed?');
        $view->setSubmitButtonLabel('Yes');

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            $notificationSearch = NotificationFactory::search()
                    ->filter('to_id', $id)
                    ->filter('viewed', 0);
            $unviewedNotifications = $notificationSearch->select();
            if (!empty($unviewedNotifications)) {
                foreach ($unviewedNotifications as $unviewedNotification) {
                    $unviewedNotification->setProperty('viewed', 1);
                    $unviewedNotification->save();
                }
            }

            //$newUrlAttributes['jqueryAction'] = 'giModalClose();';
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
    
    public function actionGetAlertView($attributes){
        $msg = NULL;
        if(isset($attributes['msg'])){
            $msg = $attributes['msg'];
        }
        $colour = 'gray';
        if(isset($attributes['colour'])){
            $colour = $attributes['colour'];
        }
        
        $finalMsg = $msg;
        if(isset($attributes['code'])){
            $code = $attributes['code'];
            $codeMsg = AlertService::getMessageFromCode($code);
            if(!empty($codeMsg)){
                $finalMsg = $codeMsg;
                if(!empty($msg)){
                    $finalMsg .= '<p class="sml_text">' . $msg . '</p>';
                }
            }
        }
        
        if(empty($finalMsg)){
            $finalMsg = 'You have been alerted.';
        }
        
        $pendingAlert = new Alert();
        $pendingAlert->setMessage($finalMsg);
        $pendingAlert->setColour($colour);
        
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = $pendingAlert->getAlertHTML();
        return $returnArray;
    }

}
