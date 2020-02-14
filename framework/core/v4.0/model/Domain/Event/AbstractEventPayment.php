<?php
/**
 * Description of AbstractEvent
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventPayment extends AbstractEvent {

    public function process() {
        $ref = $this->getProperty('ref');
        switch ($ref) {
            case 'payment_failed':
                return $this->processPaymentFailed();
            case 'processor_error':
                return $this->processPaymentProcessorError();

            default:
                return parent::process();
        }
    }

    protected function processPaymentFailed() {
       if (!$this->setAdminUsersAsUsersToNotify()) {
            return false;
        }

        return $this->notifyUsers();
    }

    protected function processPaymentProcessorError() {
        if (!$this->setAdminUsersAsUsersToNotify()) {
            return false;
        }
        return $this->notifyUsers();
    }

    protected function setAdminUsersAsUsersToNotify() {
        //TODO - for franchised systems, also get appropriate franchise admin users
        $sysAdminRole = RoleFactory::getRoleBySystemTitle('system_admin');
        if (empty($sysAdminRole)) {
            return false;
        }
        $users = UserFactory::getUsersByRole($sysAdminRole);
        if (empty($users)) {
            return false;
        }
        $this->usersToNotify = $users;
        return true;
    }

    public function setNotificationProperties(AbstractNotification $notification) {
        $fromUser = UserFactory::getSystemUser();
        $toUser = $notification->getToUser();
        $subjectModel = $this->getSubjectModel();
        if (empty($toUser)) {
            return;
        }
        $notification->setProperty('to_id', $toUser->getId());
        $notification->setProperty('from_id', $fromUser->getId());
        $notification->setProperty('sbj', $this->getNotificationSubject());
        $notification->setProperty('msg', $this->getNotificationMessage());
        $notification->setProperty('viewed', 0);
        if (!empty($subjectModel)) {
            $notification->setProperty('table_name', $subjectModel->getTableName());
            $notification->setProperty('item_id', $subjectModel->getId());
            $notification->setProperty('url', $subjectModel->getNotificationViewURL());
        }
        return $notification;
    }

    protected function notifyUsers() {
        if (empty($this->usersToNotify)) {
            return false;
        }
        $usersToNotify = $this->usersToNotify;
        if (!empty($usersToNotify)) {
            foreach ($usersToNotify as $userToNotify) {
                NotificationService::notifyUserOnEvent($userToNotify, $this);
            }
        }
        return true;
    }

}
