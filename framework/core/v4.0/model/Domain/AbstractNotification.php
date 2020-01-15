<?php
/**
 * Description of AbstractNotification
 * Place methods here that will be part of the module, and used for all applications
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

abstract class AbstractNotification extends GI_Model {
    
    /**
     * @var AbstractUser
     */
    protected $fromUser = NULL;
    /**
     * @var AbstractUser
     */
    protected $toUser = NULL;
    
    /**
     * Notifies by a socket id
     * 
     * @param string $socketId
     * @return boolean false if exception occurs
     */
    public static function notifyBySocketId($socketId) {
        $client = new Client(new Version1X(ProjectConfig::getSocketServerURLWithPort()));
        try {
            $client->initialize();
            $client->emit('notify', array('sid' => $socketId));
            $client->close();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public static function qbConnected($socketId){
        $client = new Client(new Version1X(ProjectConfig::getSocketServerURLWithPort()));
        try {
            $client->initialize();
            $client->emit('qb_connect', array('sid' => $socketId));
            $client->close();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    /**
     * Marks Viewed to notifications
     * 
     * @return boolean false if there is no login id
     */
    public static function markNotificationViewed() {
        $server = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $path = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $cleanPath = str_replace('//', '/', $server.$path);
        $url= ProjectConfig::getHTMLProtocol() . '://' . $cleanPath;
        $userId = Login::getUserId();
        if(!empty($userId)){
            $notifications = NotificationFactory::search()
                    ->filter('to_id', $userId)
                    ->filter('url', $url)
                    ->filter('viewed', 0)
                    ->select();
            if ($notifications) {
                foreach($notifications as $notification){
                    $notification->setProperty('viewed', 1);
                    if (!$notification->save()) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param AbstractUser $user
     * @param string $subject
     * @param array $attributes
     * @param string $msg
     * @param string $type
     * @return boolean
     */
    public static function notifyUser(AbstractUser $user, $subject, $attributes = NULL, $msg = NULL, $type = NULL) {
        $currentUserId = Login::getUserId();
        $userId = $user->getProperty('id');
        if ((int) $currentUserId != (int) $userId) {
            $notification = NotificationFactory::buildNewModel();
            $notification->setProperty('to_id', $userId);
            $notification->setProperty('from_id', Login::getUserId());
            $notification->setProperty('sbj', $subject);
            
            if(!empty($attributes)){
                $url = GI_URLUtils::buildURL($attributes, true);
                $notification->setProperty('url', $url);
            }
            if ($msg) {
                $notification->setProperty('msg', $msg);
            }
            if ($type) {
                $notification->setProperty('type', $type);
            }
            if ($notification->save()) {
                $notificationId = $notification->getId();
                if(empty($attributes)){
                    $viewNotUrl = GI_URLUtils::buildURL(array(
                        'controller' => 'static',
                        'action' => 'viewNotification',
                        'id' => $notificationId
                    ), true);
                    $notification->setProperty('url', $viewNotUrl);
                    $notification->save();
                }
                $socketsToNotify = array();
                $loginArray = LoginFactory::search()
                        ->filter('user_id', $userId)
                        ->select();
                if ($loginArray) {
                    foreach ($loginArray as $login) {
                        $socketId = $login->getProperty('socket_id');
                        array_push($socketsToNotify, $socketId);
                    }
                }
                foreach ($socketsToNotify as $socketId) {
                    static::notifyBySocketId($socketId);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return AbstractUser
     */
    public function getToUser(){
        if(is_null($this->toUser)){
            $this->toUser = UserFactory::getModelById($this->getProperty('to_id'));
        }
        return $this->toUser;
    }
    
    public function getToUserName() {
       $toUser = $this->getToUser();
       if (!empty($toUser)) {
           return $toUser->getFullName();
       }
       return '';
    }

    /**
     * @return AbstractUser
     */
    public function getFromUser() {
        if(is_null($this->fromUser)){
            $this->fromUser = UserFactory::getModelById($this->getProperty('from_id'));
        }
        return $this->fromUser;
    }

    public function getFromUserName() {
       $fromUserId = $this->getProperty('from_id');
       $fromUser = UserFactory::getModelById($fromUserId);
       if (!empty($fromUser)) {
           return $fromUser->getFullName();
       }
       return '';
    }
    
    public function getDateAndTime() {
        $inception = $this->getProperty('inception');
        return GI_Time::formatDateTimeForDisplay($inception);
    }
    
    public function getTimeSince() {
        $inception = $this->getProperty('inception');
        return GI_Time::formatTimeSince($inception, '', 0, true);
    }
    
    public function getViewURL() {
        $url = $this->getProperty('url');
        return $url;
    }
    
    public function getSummary(){
        return GI_StringUtils::summarize($this->getProperty('msg'), 100);
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setURL($this->getViewURL());
        if($this->isViewed()){
            $cardView->addCardClass('viewed');
        } else {
            $cardView->addTabClass('red');
        }
        $cardView->setTabTitle($this->getStatusTitle());
        $cardView->setTitle($this->getProperty('sbj'));
        $cardView->setSubtitle($this->getDateAndTime());
        $cardView->setTopRight($this->getTimeSince());
        $summary = '<span title="' . GI_Sanitize::htmlAttribute($this->getProperty('msg')) . '">' . $this->getSummary() . '</span>';
        $summary .= GI_StringUtils::getLabelWithValue('From', $this->getFromUserName());
        $cardView->setSummary($summary);
        
        return $cardView;
    }
    
    public function getTileTitle(){
        $tileTitle = '<span class="title">' . $this->getProperty('sbj') . '</span>';
        $tileTitle .= '<span class="subtitle">' . $this->getDateAndTime() . '</span>';
        return $tileTitle;
    }
    
    public function getFromTileCell(){
        return $this->getFromUserName();
    }
    
    public function getStatusTitle(){
        if($this->isViewed()){
            return 'Seen';
        }
        return 'Unread';
    }
    
    public function isViewed(){
        if($this->getProperty('viewed')){
            return true;
        }
        return false;
    }

    public function getBrowserNotificationDataArray() {
        $notificationArray = array();
        $notificationArray['url'] = $this->getProperty('url');
        $notificationArray['sbj'] = $this->getProperty('sbj');
        $notificationArray['model_id'] = $this->getProperty('id');
        //TODO - add detail to title
        $notificationArray['title'] = 'New Notification';
        return $notificationArray;
    }
    
    public function getBreadcrumbs() {
        $toUser = $this->getToUser();
        $breadcrumbs = array();
        if($toUser){
            $breadcrumbs[] = array(
                'label' => $toUser->getFullName() . '’s Notifications',
                'link' => $toUser->getViewURL()
            );
        }
        $breadcrumbs[] = array(
            'label' => $this->getProperty('sbj'),
            'link' => $this->getViewURL()
        );
        return $breadcrumbs;
    }
    
    public function getTileRowClass(){
        $class = 'red_card_tab';
        if($this->isViewed()){
            $class = 'viewed';
        }
        return $class;
    }
    
    public function getIsIndexViewable() {
        if(Login::isLoggedIn()){
            return true;
        }
        return false;
    }
    
    public static function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch){
        $userId = Login::getUserId();
        $dataSearch->filter('to_id', $userId);
        return $dataSearch;
    }
    
    /** @param GI_DataSearch $dataSearch */
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $orderbycase = $dataSearch->newCase()
                ->filter('viewed', 1)
                ->setThen(0)
                ->setElse(1);
        $dataSearch->orderByCase($orderbycase, 'DESC')
                ->orderBy('inception', 'DESC');
        return $dataSearch;
    }
    
    public function getViewTitle($plural = true) {
        $title = Lang::getString('notification');
        if ($plural) {
            $title = Lang::getString('notifications');
        }
        return $title;
    }
    
    /**
     * Gets UI table columns
     * 
     * @return UITableCol[]
     */
    public static function getUITableCols() {
        $tableColArrays = array(
            //Subject
            array(
                'method_name' => 'getStatusTitle'
            ),
            array(
                'header_title' => 'Subject',
                'method_name' => 'getTileTitle',
                'cell_url_method_name' => 'getViewURL'
            ),
            //From
            array(
                'header_title' => 'From',
                'method_name' => 'getFromTileCell'
            ),
            //Time Since
            array(
                'header_title' => 'Time Since',
                'method_name' => 'getTimeSince'
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getListBarURL(){
        return GI_URLUtils::buildURL(array(
            'controller' => 'notification',
            'action' => 'index'
        ));
    }

}
