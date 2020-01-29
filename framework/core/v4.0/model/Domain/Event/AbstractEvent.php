<?php
/**
 * Description of AbstractEvent
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractEvent extends GI_Model {
    
    /** @var GI_Model */
    protected $subjectModel = NULL;
    
    /** @var AbstractUser[] */
    protected $usersToNotify = array();
    
    /** @var DateTime */
    protected $eventDateTime = NULL;
    protected $fromUser = NULL;
    protected $notificationMessage = '';
    protected $notificationSubject = '';
    
    protected $linkedRoles = NULL;
    protected $linkedContextRoles = NULL;
    protected $linkedUsers = NULL;
    
    protected $defaultLinkedRoles = NULL;
    protected $defaultLinkedContextRoles = NULL;
    protected $defaultLinkedUsers = NULL;
    
    protected $logMessage = '';
    
    protected $subjectModelDefaultEventNotifies = NULL;

    public function __construct(\GI_DataMap $map, $factoryClassName = NULL) {
        parent::__construct($map, $factoryClassName);
        $dateTime = new DateTime(GI_Time::getDateTime());
        $this->eventDateTime = $dateTime;
        $this->fromUser = Login::getUser();
    }
    
    public function getSubjectModel() {
        return $this->subjectModel;
    }
    
    public function getFromUser() {
        return $this->fromUser;
    }
    
    public function setFromUser(AbstractUser $fromUser) {
        $this->fromUser = $fromUser;
    }
    
    /**
     * @return DateTime
     */
    public function getEventDateTime() {
        return $this->eventDateTime;
    }
    
    public function getLogMessage() {
        return $this->logMessage;
    }

    public function save() {
        if (Permission::verifyByRef('save_event_definitions')) {
            return parent::save();
        }
        return true;
    }
    
    public function softDelete() {
        return true;
    }
    
    public function setSubjectModel(GI_Model $model) {
        $this->subjectModel = $model;
    }
    
    public function setNotificationMessage($message) {
        $this->notificationMessage = $message;
    }
    
    public function setNotificationSubject($subject) {
        $this->notificationSubject = $subject;
    }
    
    public function setLogMessage($logMessage) {
        $this->logMessage = $logMessage;
    }

    protected function notifyUsers() {
        $subjectModel = $this->getSubjectModel();
        if (empty($subjectModel)) {
            return true;
        }
        if (empty($this->usersToNotify) && !$this->determineUsersToNotify()) {
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

    protected function determineUsersToNotify() {
        //User direct assignment
        //A = using user DEFAULT setting for the subject model (no item id)
        //B = using user SPECIFIC setting for the subject model (item id)
        //C = using other user SPECIFIC setting for the subject model (item id) - means ignore default settings
        
        //Role assignment
        //D = using role DEFAULT setting for the subject model (no item id)
        //E = using role SPECIFIC setting for the subject model (item id) 
        //F = using other role SPECIFIC setting for the subject model (item id) - means it's a different role than the default
        
        //Context Role assignment
        //G = using context role DEFAULT setting for the subject model (no item id)
        //H = using context role SPECIFIC setting for the subject model (item id)
        //I = using other context role for SPECIFIC setting for the subject model (item id) - means it's a different context role than the default
        
        
        $subjectModel = $this->getSubjectModel();
        if (empty($subjectModel)) {
            return true;
        }
        $tableName = $subjectModel->getTableName();
        $itemId = $subjectModel->getId();
        $userTableName = UserFactory::getDbPrefix() . 'user';
        $search = UserFactory::search();
        $search->setAutoStatus(false)
                ->filter('status', 1);
        
        $joinA = $search->createLeftJoin('event_notifies', 'user_id', $userTableName, 'id', 'EN_A');
        $joinA->filterNull('EN_A.role_id')
                ->filterNull('EN_A.context_role_id')
                ->filter('EN_A.table_name', $tableName)
                ->filter('EN_A.event_id', $this->getId())
                ->filterNull('EN_A.item_id')
                ->filter('EN_A.status', 1);

        $roleJoin = $search->createLeftJoin('user_link_to_role', 'user_id', $userTableName, 'id', 'ULTR');
        $roleJoin->filter('ULTR.status', 1);
        
        $contextRoleJoin = $search->createLeftJoin('user_has_context_role', 'user_id', $userTableName, 'id', 'ULTCR');
        $contextRoleJoin->filter('ULTCR.status', 1);

        $joinD = $search->createLeftJoin('event_notifies', 'role_id', 'ULTR', 'role_id', 'EN_D');
        $joinD->filterNull('EN_D.user_id')
                ->filter('EN_D.event_id', $this->getId())
                ->filterNull('EN_D.context_role_id')
                ->filter('EN_D.table_name', $tableName)
                ->filterNull('EN_D.item_id')
                ->filter('EN_D.status', 1);
        $joinG = $search->createLeftJoin('event_notifies', 'context_role_id', 'ULTCR', 'context_role_id', 'EN_G');
        $joinG->filterNull('EN_G.user_id')
                ->filter('EN_G.event_id', $this->getId())
                ->filterNull('EN_G.role_id')
                ->filter('EN_G.table_name', $tableName)
                ->filterNull('EN_G.item_id')
                ->filter('EN_G.status', 1);
        if (!empty($itemId)) {
            $joinB = $search->createLeftJoin('event_notifies', 'user_id', $userTableName, 'id', 'EN_B');
            $joinB->filterNull('EN_B.role_id')
                    ->filterNull('EN_B.context_role_id')
                    ->filter('EN_B.table_name', $tableName)
                    ->filter('EN_B.item_id', $subjectModel->getId())
                    ->filter('EN_B.event_id', $this->getId())
                    ->filter('EN_B.status', 1);
            $joinC = $search->createLeftJoin('event_notifies', 'event_id', 'EN_A', 'event_id', 'EN_C');
            $joinC->filterNull('EN_C.role_id')
                    ->filterNull('EN_C.context_role_id')
                    ->filter('EN_C.table_name', $tableName)
                    ->filter('EN_C.item_id', $subjectModel->getId())
                    ->filterGroup()
                    ->filterGroup()
                    ->filterWithColumn('EN_C.user_id', 'EN_A.user_id', '!=')
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->andIf()
                    ->filterNull('EN_C.user_id')
                    ->filter('EN_C.no_users', 1)
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf()
                    ->filter('EN_C.status', 1);
            $joinE = $search->createLeftJoin('event_notifies', 'role_id', 'ULTR', 'role_id', 'EN_E');
            $joinE->filterNull('EN_E.user_id')
                    ->filterNull('EN_E.context_role_id')
                    ->filter('EN_E.table_name', $tableName)
                    ->filter('EN_E.item_id', $subjectModel->getId())
                    ->filter('EN_E.event_id', $this->getId())
                    ->filter('EN_E.status', 1);
            $joinF = $search->createLeftJoin('event_notifies', 'event_id', 'EN_D', 'event_id', 'EN_F');
            $joinF->filterNull('EN_F.user_id')
                    ->filterNull('EN_F.context_role_id')
                    ->filter('EN_F.table_name', $tableName)
                    ->filter('EN_F.item_id', $subjectModel->getId())
                    ->filterGroup()
                    ->filterGroup()
                    ->filterWithColumn('EN_F.role_id', 'EN_D.role_id', '!=')
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->andIf()
                    ->filterNull('EN_F.role_id')
                    ->filter('EN_F.no_roles', 1)
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf()
                    ->filter('EN_F.status', 1);
            $joinH = $search->createLeftJoin('event_notifies', 'context_role_id', 'ULTCR', 'context_role_id', 'EN_H');
            $joinH->filterNull('EN_H.user_id')
                    ->filterNull('EN_H.role_id')
                    ->filter('EN_H.table_name', $tableName)
                    ->filter('EN_H.item_id', $subjectModel->getId())
                    ->filter('EN_H.event_id', $this->getId())
                    ->filter('EN_H.status', 1);
            $joinI = $search->createLeftJoin('event_notifies', 'event_id', 'EN_I', 'event_id', 'EN_I');
            $joinI->filterNull('EN_I.user_id')
                    ->filterNull('EN_I.role_id')
                    ->filter('EN_I.table_name', $tableName)
                    ->filter('EN_I.item_id', $subjectModel->getId())
                    ->filterGroup()
                    ->filterGroup()
                    ->filterWithColumn('EN_I.context_role_id', 'EN_G.context_role_id', '!=')
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->andIf()
                    ->filterNull('EN_I.context_role_id')
                    ->filter('EN_I.no_context_roles', 1)
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf()
                    ->filter('EN_I.status', 1);
            $search->filterGroup()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_B.status', 1)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_A.status', 1)
                        ->filterNullOr('EN_C.status')
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_E.status', 1)
                    ->closeGroup()
                       ->orIf()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_D.status', 1)
                        ->filterNullOr('EN_F.status')
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_H.status', 1)
                    ->closeGroup()
                       ->orIf()
                    ->filterGroup()
                        ->andIf()
                        ->filter('EN_G.status', 1)
                        ->filterNullOr('EN_I.status')
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
        } else {

            $search->filterGroup()
                    ->filterGroup()
                    ->filter('EN_A.status', 1)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->filter('EN_D.status', 1)
                    ->closeGroup()
                     ->orIf()
                    ->filterGroup()
                    ->filter('EN_G.status', 1)
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
        }

        $search->groupBy('id');
        $this->usersToNotify = $search->select();
        return true;
    }
    
    public function process() {
        $this->log();
        $ref = $this->getProperty('ref');
        switch ($ref) {
            default:
                return $this->notifyUsers();
        }
    }
    
    public function log($byType = true, $byUser = true) {
        return LogService::logEvent($this, $byType, $byUser);
    }

    public function getLinkedRoles($returnDefaultIfEmpty = false) {
        if (is_null($this->linkedRoles)) {
            if (empty($this->subjectModel)) {
                return NULL;
            }
            $tableName = RoleFactory::getDbPrefix() . 'role';
            $search = RoleFactory::search();
            $itemId = $this->subjectModel->getId();
            $join = $search->createJoin('event_notifies', 'role_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $this->subjectModel->getTableName());
            if (!empty($itemId)) {
                $join->filter('EN.item_id', $itemId);
            } else {
                $join->filterNull('EN.item_id');
            }
            $search->filter('EN.event_id', $this->getId());
            $this->linkedRoles = $search->select(true);
        }
        if ($returnDefaultIfEmpty && empty($this->linkedRoles)) {
            $subjectModelDefaultEventNotifies = $this->getSubjectModelDefaultEventNotifies();
            if (!empty($subjectModelDefaultEventNotifies)) {
                if (!empty($subjectModelDefaultEventNotifies->getProperty('no_roles'))) {
                    $this->linkedRoles = array();
                    return $this->linkedRoles;
                }
            }
            return $this->getDefaultLinkedRoles();
        }
        return $this->linkedRoles;
    }

    public function getDefaultLinkedRoles() {
        if (is_null($this->defaultLinkedRoles)) {
            $subjectModel = $this->getSubjectModel();
            if (empty($subjectModel)) {
                return NULL;
            }
            $tableName = RoleFactory::getDbPrefix() . 'role';
            $search = RoleFactory::search();
            $join = $search->createJoin('event_notifies', 'role_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName());
            $join->filterNull('EN.item_id');
            $search->filter('EN.event_id', $this->getId());
            $this->defaultLinkedRoles = $search->select(true);
        }
        return $this->defaultLinkedRoles;
    }

    public function getLinkedRolesIdString($returnDefaultIfEmpty = false) {
        $value = '';
        $linkedRoles = $this->getLinkedRoles($returnDefaultIfEmpty);
        if (!empty($linkedRoles)) {
            $idsArray = array_keys($linkedRoles);
            $value = implode(',', $idsArray);
        }
        return $value;
    }

    public function getLinkedContextRoles($returnDefaultIfEmpty = false) {
        if (is_null($this->linkedContextRoles)) {
            $subjectModel = $this->getSubjectModel();
            if (empty($subjectModel)) {
                return NULL;
            }
            $tableName = ContextRoleFactory::getDbPrefix() . 'context_role';
            $search = ContextRoleFactory::search();
            $itemId = $subjectModel->getId();
            $join = $search->createJoin('event_notifies', 'context_role_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName());
            if (!empty($itemId)) {
                $join->filter('EN.item_id', $itemId);
            } else {
                $join->filterNull('EN.item_id');
            }
            $search->filter('EN.event_id', $this->getId());
            $this->linkedContextRoles = $search->select(true);
        }
        if ($returnDefaultIfEmpty && empty($this->linkedContextRoles)) {
            $subjectModelDefaultEventNotifies = $this->getSubjectModelDefaultEventNotifies();
            if (!empty($subjectModelDefaultEventNotifies)) {
                if (!empty($subjectModelDefaultEventNotifies->getProperty('no_context_roles'))) {
                    $this->linkedContextRoles = array();
                    return $this->linkedContextRoles;
                }
            }
            return $this->getDefaultLinkedContextRoles();
        }
        return $this->linkedContextRoles;
    }

    public function getDefaultLinkedContextRoles() {
        if (is_null($this->defaultLinkedContextRoles)) {
            $subjectModel = $this->getSubjectModel();
            if (is_null($subjectModel)) {
                return NULL;
            }
            $tableName = ContextRoleFactory::getDbPrefix() . 'context_role';
            $search = ContextRoleFactory::search();
            $join = $search->createJoin('event_notifies', 'context_role_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName());
            $join->filterNull('EN.item_id');
            $search->filter('EN.event_id', $this->getId());
            $this->defaultLinkedContextRoles = $search->select(true);
        }
        return $this->defaultLinkedContextRoles;
    }

    public function getLinkedContextRolesIdString($returnDefaultIfEmpty = false) {
        $value = '';
        $linkedContextRoles = $this->getLinkedContextRoles($returnDefaultIfEmpty);
        if (!empty($linkedContextRoles)) {
            $idsArray = array_keys($linkedContextRoles);
            $value = implode(',', $idsArray);
        }
        return $value;
    }

    public function getLinkedUsers($returnDefaultIfEmpty = false) {
        if (is_null($this->linkedUsers)) {
            $subjectModel = $this->getSubjectModel();
            if (empty($subjectModel)) {
                return NULL;
            }
            $tableName = UserFactory::getDbPrefix() . 'user';
            $search = UserFactory::search();
            $itemId = $subjectModel->getId();
            $join = $search->createJoin('event_notifies', 'user_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName());
            if (!empty($itemId)) {
                $join->filter('EN.item_id', $itemId);
            } else {
                $join->filterNull('EN.item_id');
            }
            $search->filter('EN.event_id', $this->getId());
            $this->linkedUsers = $search->select(true);
        }
        if ($returnDefaultIfEmpty && empty($this->linkedUsers)) {
            $subjectModelDefaultEventNotifies = $this->getSubjectModelDefaultEventNotifies();
            if (!empty($subjectModelDefaultEventNotifies)) {
                if (!empty($subjectModelDefaultEventNotifies->getProperty('no_users'))) {
                    $this->linkedUsers = array();
                    return $this->linkedUsers;
                }
            }
            return $this->getDefaultLinkedUsers();
        }
        return $this->linkedUsers;
    }

    public function getDefaultLinkedUsers() {
        if (is_null($this->defaultLinkedUsers)) {
            $subjectModel = $this->getSubjectModel();
            if (empty($subjectModel)) {
                return NULL;
            }
            $tableName = UserFactory::getDbPrefix() . 'user';
            $search = UserFactory::search();
            $join = $search->createJoin('event_notifies', 'user_id', $tableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName());
            $join->filterNull('EN.item_id');
            $search->filter('EN.event_id', $this->getId());
            $this->defaultLinkedUsers = $search->select(true);
        }
        return $this->defaultLinkedUsers;
    }

    public function getLinkedUsersIdString($returnDefaultIfEmpty = false) {
        $value = '';
        $linkedUsers = $this->getLinkedUsers($returnDefaultIfEmpty);
        if (!empty($linkedUsers)) {
            $idsArray = array_keys($linkedUsers);
            $value = implode(',', $idsArray);
        }
        return $value;
    }

    public function getSubjectModelDefaultEventNotifies() {
        if (is_null($this->subjectModelDefaultEventNotifies)) {
            $subjectModel = $this->getSubjectModel();
            if (empty($subjectModel)) {
                return NULL;
            }
            $this->subjectModelDefaultEventNotifies = $subjectModel->getDefaultEventNotifies($this);
        }
        return $this->subjectModelDefaultEventNotifies;
    }

    public function getTitle() {
        return $this->getProperty('title');
    }

    public function handleNotificationFormSubmission(GI_Form $form) {
        $subjectModel = $this->getSubjectModel();
        if (!empty($subjectModel) && $form->wasSubmitted() && $form->validate()) {
            $defaultEventNotifies = $this->getSubjectModelDefaultEventNotifies();
            if (empty($defaultEventNotifies)) {
                return false;
            }
            $roleIdsString = filter_input(INPUT_POST, 'role_ids');
            if (!empty($roleIdsString)) {
                $roleIds = explode(',', $roleIdsString);
                $defaultEventNotifies->setProperty('no_roles', 0);
            } else {
                $roleIds = array();
                $defaultEventNotifies->setProperty('no_roles', 1);
            }
            if (!$this->updateLinkedRoles($roleIds)) {
                return false;
            }

            $contextRoleIdsString = filter_input(INPUT_POST, 'context_role_ids');
            if (!empty($contextRoleIdsString)) {
                $contextRoleIds = explode(',', $contextRoleIdsString);
                $defaultEventNotifies->setProperty('no_context_roles', 0);
            } else {
                $contextRoleIds = array();
                $defaultEventNotifies->setProperty('no_context_roles', 1);
            }
            if (!$this->updateLinkedContextRoles($contextRoleIds)) {
                return false;
            }

            $userIdsString = filter_input(INPUT_POST, 'user_ids');
            if (!empty($userIdsString)) {
                $userIds = explode(',', $userIdsString);
                $defaultEventNotifies->setProperty('no_users', 0);
            } else {
                $userIds = array();
                $defaultEventNotifies->setProperty('no_users', 1);
            }
            if (!$this->updateLinkedUsers($userIds)) {
                return false;
            }
            if (!empty($subjectModel->getId()) && !$defaultEventNotifies->save()) {
                return false;
            }

            return true;
        }
        return false;
    }

    protected function updateLinkedRoles($targetRoleIds = array()) {
        $existingLinkedRoles = $this->getLinkedRoles(false);
        return $this->updateEventNotifiesLinks('role_id', $existingLinkedRoles, $targetRoleIds);
    }

    protected function updateLinkedContextRoles($targetContextRoleIds = array()) {
        $subjectModel = $this->getSubjectModel();
        if (empty($subjectModel)) {
            return false;
        }
        if (empty($subjectModel->getId())) {
            return $this->updateGeneralLinkedContextRoles($targetContextRoleIds);
        }
        return $this->updateSpecificLinkedContextRoles($targetContextRoleIds);
    }

    protected function updateGeneralLinkedContextRoles($targetContextRoleIds = array()) {
        $existingLinkedContextRoles = $this->getLinkedContextRoles(false);
        return $this->updateEventNotifiesLinks('context_role_id', $existingLinkedContextRoles, $targetContextRoleIds);
    }

    protected function updateSpecificLinkedContextRoles($targetContextRoleIds = array()) {
        $subjectModel = $this->getSubjectModel();
        if (empty($subjectModel)) {
            return false;
        }
        $itemId = $subjectModel->getId();
        foreach ($targetContextRoleIds as $key => $targetContextRoleId) {
            $targetContextRole = ContextRoleFactory::getModelById($targetContextRoleId);
            if (empty($targetContextRole)) {
                continue;
            }
            if (empty($targetContextRole->getProperty('item_id'))) {
                $newContextRole = ContextRoleFactory::buildNewModel();
                $newContextRole->setPropertiesFromSourceModel($targetContextRole);
                $newContextRole->setProperty('item_id', $itemId);
                if (!$newContextRole->save()) {
                    return false;
                }
                $linkedUsers = $targetContextRole->getUsers();
                if (!empty($linkedUsers)) {
                    foreach ($linkedUsers as $userToLink) {
                        if (!$newContextRole->linkUser($userToLink)) {
                            return false;
                        }
                    }
                }
                $targetContextRoleIds[$key] = $newContextRole->getId();
            }
        }
        $existingLinkedContextRoles = $this->getLinkedContextRoles(false);
        return $this->updateEventNotifiesLinks('context_role_id', $existingLinkedContextRoles, $targetContextRoleIds);
    }

    protected function updateLinkedUsers($targetUserIds = array()) {
        $existingLinkedUsers = $this->getLinkedUsers(false);
        return $this->updateEventNotifiesLinks('user_id', $existingLinkedUsers, $targetUserIds);
    }
    
    protected function updateEventNotifiesLinks($eventNotifiesIdColKey, $existingLinkedModels = array(), $targetModelIds = array()) {
        $subjectModel = $this->getSubjectModel();
        $tableName = $subjectModel->getTableName();
        $itemId = $subjectModel->getId();
        if (!empty($existingLinkedModels) && !empty($targetModelIds)) {
            foreach ($targetModelIds as $key => $targetId) {
                if (isset($existingLinkedModels[$targetId])) {
                    unset($existingLinkedModels[$targetId]);
                    unset($targetModelIds[$key]);
                }
            }
        }
        if (!empty($targetModelIds)) {
            foreach ($targetModelIds as $modelId) {
                //try to find a soft-delete event notifies
                $softDeletedSearch = EventNotifiesFactory::search();
                $softDeletedSearch->setAutoStatus(false)
                        ->filter('status', 0)
                        ->filter('event_id', $this->getId())
                        ->filter($eventNotifiesIdColKey, $modelId)
                        ->filter('table_name', $tableName);
                if (empty($itemId)) {
                    $softDeletedSearch->filterNull('item_id');
                } else {
                    $softDeletedSearch->filter('item_id', $itemId);
                }
                $softDeletedModels = $softDeletedSearch->select();
                if (!empty($softDeletedModels)) {
                    $softDeletedModel = $softDeletedModels[0];
                    if ($softDeletedModel->unSoftDelete()) {
                        continue;
                    }
                }
                //or create a new one
                $newModel = EventNotifiesFactory::buildNewModel();
                $newModel->setProperty('event_id', $this->getId());
                $newModel->setProperty($eventNotifiesIdColKey, $modelId);
                $newModel->setProperty('table_name', $tableName);
                if (!empty($itemId)) {
                    $newModel->setProperty('item_id', $itemId);
                }
                if (!$newModel->save()) {
                    return false;
                }
            }
        }
        if (!empty($existingLinkedModels)) {
            foreach ($existingLinkedModels as $existingLinkedUser) {
                $modelsToDeleteSearch = EventNotifiesFactory::search();
                $modelsToDeleteSearch->filter('event_id', $this->getId())
                        ->filter($eventNotifiesIdColKey, $existingLinkedUser->getId())
                        ->filter('table_name', $tableName);
                if (empty($itemId)) {
                    $modelsToDeleteSearch->filterNull('item_id');
                } else {
                    $modelsToDeleteSearch->filter('item_id', $itemId);
                }
                $modelsToDelete = $modelsToDeleteSearch->select();
                if (!empty($modelsToDelete)) {
                    foreach ($modelsToDelete as $modelToDelete) {
                        if (!$modelToDelete->softDelete()) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    public function getIsViewable() {
        if (!empty($this->getProperty('hidden_from_users'))) {
            //TODO - permission check
            
        }
        
        return true;
    }
    
    public function setNotificationProperties(AbstractNotification $notification) {
        $fromUser = $this->getFromUser();
        $toUser = $notification->getToUser();
        $subjectModel = $this->getSubjectModel();
        if (empty($subjectModel) || empty($fromUser) || empty($toUser)) {
            return;
        }
        $notification->setProperty('to_id', $toUser->getId());
        $notification->setProperty('from_id', $fromUser->getId());
        $notification->setProperty('sbj', $this->getNotificationSubject());
        $notification->setProperty('msg', $this->getNotificationMessage());
        $notification->setProperty('viewed', 0);
        $notification->setProperty('table_name',$subjectModel->getTableName());
        $notification->setProperty('item_id', $subjectModel->getId());
        $notification->setProperty('url', $subjectModel->getNotificationViewURL());
        return $notification;
    }
    
    protected function getNotificationSubject() {
        if (empty($this->notificationSubject)) {
            $subject = $this->subjectModel->getTypeTitle(false) . ' ' . $this->getTitle();
            $this->notificationSubject = $subject;
        }
        return $this->notificationSubject;
    }
    
    protected function getNotificationMessage() {
        if (empty($this->notificationMessage)) {
            $message = $this->subjectModel->getTypeTitle(false) . ' ' . $this->getTitle();
            $this->notificationMessage = $message;
        }
        return $this->notificationMessage;
    }
        
    
    
    
    

}
