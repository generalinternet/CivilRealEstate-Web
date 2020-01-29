<?php
/**
 * Description of AbstractNotificationService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

abstract class AbstractNotificationService extends GI_Service {
    
    /**
     * This method takes user notification settings into consideration
     * @param AbstractUser $user
     * @param AbstractEvent $event
     * @return boolean
     */
    public static function notifyUserOnEvent(AbstractUser $user, AbstractEvent $event) {
        if ($user->getId() == Login::getUserId()) {
            return true;
        }
        $settings = $user->getNotificationSettingsModel($event->getId());
        if (empty($settings->getId())) {
            $settings = $user->getNotificationSettingsModel(NULL, 'notification_global');
        }
        if (empty($settings)) {
            $settings = SettingsFactory::buildNewModel('notification_global');
            $settings->setProperty('settings_notif.in_system', 1);
            $settings->setProperty('settings_notif.alt_phone', $user->getProperty('mobile'));
            $settings->setProperty('settings_notif.email_phone', $user->getProperty('email'));
        } 

        $notification = NotificationFactory::buildNewModel();
        $notification->setProperty('event_id', $event->getId());
        $notification->setToUser($user);
        $event->setNotificationProperties($notification);
        $settings->setNotificationProperties($notification);
        
        if (!$notification->save()) {
            return false;
        }

        if (!DEV_MODE && !ProjectConfig::getIsWorkerServer()) {
            AWSService::sendMessageToSQSQueue('notification', array(
                'id' => $notification->getId(),
            ));
        } else {
            if (!empty($notification->getProperty('in_system'))) {
                static::notifyUserInSystem($notification);
            }

            if (!empty($notification->getProperty('text'))) {
                static::notifyUserByText($notification);
            }

            if (!empty($notification->getProperty('immediate_email'))) {
                static::notifyUserByEmail($notification);
            }
        }
        return true;
    }

    /**
     * This method does not consider user notification settings
     * @param AbstractNotification $notification
     * @return boolean
     */
    public static function notifyUserInSystem(AbstractNotification $notification) {
        $user = $notification->getToUser();
        $socketUsersToNotify = array();
        $loginArray = LoginFactory::search()
                ->filter('user_id', $user->getId())
                ->select();
        if (!empty($loginArray)) {
            foreach ($loginArray as $login) {
                $socketUserId = $login->getProperty('socket_user_id');
                $socketUsersToNotify[] = $socketUserId;
            }
        }
        if (!empty($socketUsersToNotify)) {
            foreach ($socketUsersToNotify as $socketUserId) {
                static::notifyBySocketUserId($socketUserId);
            }
        }
        return true;
    }

    /**
     * This method does not consider user notification settings
     * @param AbstractNotification $notification
     * @return boolean
     */
    public static function notifyUserByText(AbstractNotification $notification) {
        $toPhone = $notification->getProperty('to_phone');
        if (empty($toPhone)) {
            return false;
        }
        $message = $notification->getProperty('msg'); 
        $url = $notification->getProperty('url');
        if (!empty($url)) {
            $message .= '. To view, please click here:<![CDATA[' . $url . ']]>';
        }
        $sms = new GI_SMS(GI_SMS::formatNumberE164($toPhone), $message);
        /**DEBUG LOG**/
            GI_LogFile::addToDebugLog('Notification Service: notifying by [sms] to [' . $toPhone . ']');
            GI_LogFile::addToDebugLog('----Start Message----');
            GI_LogFile::addToDebugLog($notification->getProperty('msg'));
            GI_LogFile::addToDebugLog($url);
            GI_LogFile::addToDebugLog('----End Message----');
        /**END DEBUG LOG**/
        if (DEV_MODE && !$sms->sendTestMessage()) {
            return false;
        } else if (!DEV_MODE && !$sms->sendMessage()) {
            return false;
        }
        return true;
    }

    /**
     * This method does not consider user notification settings
     * @param AbstractNotification $notification
     * @return boolean
     */
    public static function notifyUserByEmail(AbstractNotification $notification) {
        $toEmail = $notification->getProperty('to_email');
        if (empty($toEmail)) {
            return false;
        }
        $url = $notification->getProperty('url');
        $emailView = new GenericEmailView();
        $emailView->setTitle($notification->getProperty('sbj'));
        $emailView->startBlock();

        $emailView->addParagraph($notification->getProperty('msg'));
        if (!empty($url)) {
            $emailView->startParagraph()
                    ->addHTML('Please click ')
                    ->addLink('here', $url, false)
                    ->addHTML(' to view.')
                    ->closeParagraph();
        }

        $emailView->closeBlock();

        $giEmail = new GI_Email();
        $tags = $notification->getMandrillTags();
        $giEmail->addMandrillTags($tags);
        $toName = 'Unregistered User';
        $toUser = UserFactory::getModelById($notification->getProperty('to_id'));
        if (!empty($toUser)) {
            $toName = $toUser->getFullName();
        }
        $giEmail->addTo($toEmail, $toName)
                ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                ->setSubject($notification->getProperty('sbj'))
                ->useEmailView($emailView);

        
        /**DEBUG LOG**/
            GI_LogFile::addToDebugLog('Notification Service: notifying by [email] to [' . $toEmail . '], [' . $toName . ']');
            GI_LogFile::addToDebugLog('----Start Message----');
            GI_LogFile::addToDebugLog($notification->getProperty('msg'));
            GI_LogFile::addToDebugLog($url);
            GI_LogFile::addToDebugLog('----End Message----');
        /**END DEBUG LOG**/
            
        $giEmail->send();
        return true;
    }

    /**
     * @deprecated remove in v5 (notifyBySocketUserId should be used instead)
     * @param string $socketId
     * @return boolean
     */
    public static function notifyBySocketId($socketId) {        
        $data = array(
            'sid' => $socketId
        );
        return static::socketEmit('notify', $data);
    }

    public static function notifyBySocketUserId($socketUserId) {
        $data = array(
            'socketUserId' => $socketUserId
        );
        return static::socketEmit('notify', $data);
    }
    
    public static function sendChatMsg(AbstractUser $user, $msg, $otherMsgData = array(), $socketUserId = NULL){
        $toSocketUserId = Login::getSocketUserId($user->getId());
        static::sendChatMsgToSocketUser($toSocketUserId, $msg, $otherMsgData, $socketUserId);
    }
    
    public static function sendChatMsgToSocketUser($toSocketUserId, $msg, $otherMsgData = array(), $socketUserId = NULL){
        if(empty($socketUserId)){
            $socketUserId = Login::getSocketUserId();
        }
        $msgData = array(
            'msg' => $msg,
            'toSocketUserIds' => array($toSocketUserId)
        );
        $finalMsgData = array_merge($msgData, $otherMsgData);
        return static::socketEmit('chat_msg', $finalMsgData);
    }
    
    protected static function getEngine(){
        $queryString = 'appRef=' . ProjectConfig::getSessionName();
        $user = Login::getUser();
        if($user){
            $queryString .= '&nickname=' . $user->getFullName();
            $queryString .= '&userId=' . $user->getId();
            $queryString .= '&chatUserType=' . $user->getChatUserType();
        }
        $socketUserId = Login::getSocketUserId();
        if(empty($socketUserId)){
            $socketUserId = GI_URLUtils::getAttribute('socketUserId');
        }
        if($socketUserId){
            $queryString .= '&socketUserId=' . $socketUserId;
        }
        $engine = new Version1X(ProjectConfig::getSocketServerURLWithPort() . '?' . $queryString);
        return $engine;
    }
    
    public static function joinChat(){
        $user = Login::getUser();
        $data = array(
            'userId' => $user->getId(),
            'nickname' => $user->getFullName()
        );
        return static::socketEmit('join', $data);
    }
    
    public static function socketEmit($emit, $data){
        if (!ProjectConfig::openSocket()) {
            return true;
        }
        $engine = static::getEngine();
        $client = new Client($engine);
        try {
            $client->initialize();
            $client->emit($emit, $data);
            return true;
        } catch (Exception $ex) {
            die(var_dump($ex->getMessage()));
            return false;
        }
    }

}
