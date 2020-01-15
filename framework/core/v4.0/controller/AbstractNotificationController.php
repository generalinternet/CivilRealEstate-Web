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
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1) || !isset($attributes['socketId'])) {
            return array('content' => false);
        }
        $socketId = $attributes['socketId'];
        $userId = Login::getUserId();
        $loginArray = LoginFactory::search()
                ->filter('user_id', $userId)
                ->select();
        if ($loginArray) {
            $login = $loginArray[0];
            $login->setProperty('socket_id', $socketId);
            if ($login->save()) {
                return array('content' => true);
            }
        }
        return array('content' => false);
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

}
