<?php
/**
 * Description of AbstractContextRole
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContextRole extends GI_Model {
    
    protected $sourceContextRole = NULL;
    protected $users = NULL;
    protected $userIdsString = NULL;
    protected $eventNotifiesArray = NULL;


    public function getTitle() {
        return $this->getProperty('title');
    }
    
    public function getEventNotifiesArray() {
        if (empty($this->eventNotifiesArray)) {
            $search = EventNotifiesFactory::search();
            $search->filter('context_role_id', $this->getId())
                    ->filterNull('role_id')
                    ->filterNull('user_id');
            $this->eventNotifiesArray = $search->select();
        }
        return $this->eventNotifiesArray;
    }
    
        /**
     * @param string $term
     * @return array
     */
    public function getAutocompResult($term = NULL){
        $title = $this->getTitle();
        $autoResultTitle = GI_StringUtils::markTerm($term, $title);
        $typeTitle = $this->getTypeTitle();
        $autoResult = '<span class="result_text">';
        $autoResult .= '<span class="inline_block">';
        $autoResult .= $autoResultTitle;
        $autoResult .= '<span class="sub">' . $typeTitle . '</span>';
        $autoResult .= '</span>';
        $autoResult .= '</span>';
        $result = array(
            'label' => $title,
            'value' => $this->getId(),
            'autoResult' => $autoResult
        );
        return $result;
    }

    public function setPropertiesFromSourceModel(AbstractContextRole $sourceModel) {
        $this->setProperty('source_context_role_id', $sourceModel->getId());
        $this->setProperty('title', $sourceModel->getProperty('title'));
        $this->setProperty('ref', $sourceModel->getProperty('ref'));
        $this->setProperty('system', $sourceModel->getProperty('system'));
        $this->setProperty('table_name', $sourceModel->getProperty('table_name'));
        return true;
    }
    
    public function getSourceContextRole() {
        if (empty($this->sourceContextRole)) {
            $this->sourceContextRole = ContextRoleFactory::getModelById($this->getProperty('source_context_role_id'));
        }
        return $this->sourceContextRole;
    }
    
    public function getUsers($fromSourceIfEmpty = false) {
        if (empty($this->users)) {
            $search = UserFactory::search();
            $tableName = UserFactory::getDbPrefix() . 'user';
            $search->join('user_has_context_role', 'user_id', $tableName, 'id', 'UHCR')
                    ->filter('UHCR.context_role_id', $this->getId());
            $this->users = $search->select(true);
            if ($fromSourceIfEmpty && empty($this->users)) {
                $sourceContextRole = $this->getSourceContextRole();
                if (!empty($sourceContextRole)) {
                    return $sourceContextRole->getUsers();
                }
            }
        }
        return $this->users;
    }
    
    public function getUserIdsString($fromSourceIfEmpty = false) {
        $string = '';
        $users = $this->getUsers($fromSourceIfEmpty);
        if (!empty($users)) {
            $ids = array_keys($users);
            $string= implode(',', $ids);
        }
        return $string;
    }

    public function linkUser(AbstractUser $user) {
        $search = new GI_DataSearch('user_has_context_role');
        $search->filter('user_id', $user->getId())
                ->filter('context_role_id', $this->getId());
        $results = $search->select();
        if (!empty($results)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('user_has_context_role');
        $softDeletedSearch->setAutoStatus(false)
                ->filter('status', 0)
                ->filter('user_id', $user->getId())
                ->filter('context_role_id', $this->getId());
        $softDeletedResults = $softDeletedSearch->select();
        if (!empty($softDeletedResults)) {
            $softDeletedDAO = $softDeletedResults[0];
            $softDeletedDAO->setProperty('status', 1);
            if ($softDeletedDAO->save()) {
                return true;
            }
        }
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('user_has_context_role');
        $newLink->setProperty('user_id', $user->getId());
        $newLink->setProperty('context_role_id', $this->getId());
        if ($newLink->save()) {
            return true;
        }
        return false;
    }

    public function unlinkUser(AbstractUser $user) {
        $search = new GI_DataSearch('user_has_context_role');
        $search->filter('user_id', $user->getId())
                ->filter('context_role_id', $this->getId());
        $results = $search->select();
        if (empty($results)) {
            return true;
        }
        foreach ($results as $linkDAO) {
            $linkDAO->setProperty('status', 0);
            if (!$linkDAO->save()) {
                return false;
            }
        }
        return true;
    }

    public function handleFormSubmission(GI_Form $form, GI_Model $subjectModel) {
        if ($form->wasSubmitted() && $form->validate()) {
            $title = filter_input(INPUT_POST, 'title');
            $this->setProperty('title', $title);
            if (empty($this->getProperty('ref'))) {
                $this->setProperty('ref', GI_Sanitize::ref($title));
            }
            $this->setProperty('table_name', $subjectModel->getTableName());
            if (!empty($subjectModel->getId())) {
                $this->setProperty('item_id', $subjectModel->getId());
            } else {
                $this->setProperty('item_id', NULL);
            }
            if (empty($this->getId())) {
                $highestPOS = (int) ContextRoleFactory::getHighestPOS($subjectModel);
                $this->setProperty('pos', $highestPOS + 10);
            }
            if (!$this->save()) {
                return false;
            }
            if (!$this->handleLinkedUsersFormSubmission($form)) {
                return false;
            }
            if (!$this->updateEventNotifiesOnFormSubmission($form, $subjectModel)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function handleLinkedUsersFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $targetUserIdString = filter_input(INPUT_POST, 'user_ids');

            if(!empty($targetUserIdString)) {
                $targetUserIds = explode(',', $targetUserIdString);
            } else {
                $targetUserIds = array();
            }
            $existingUsers = $this->getUsers();
            
            if (!empty($existingUsers) && !empty($targetUserIds)) {
                foreach ($targetUserIds as $key=>$targetUserId) {
                    if (isset($existingUsers[$targetUserId])) {
                        unset($existingUsers[$targetUserId]);
                        unset($targetUserIds[$key]);
                    }
                }
            }

            if (!empty($targetUserIds)) {
                foreach ($targetUserIds as $userIdToAdd) {
                    $userToAdd = UserFactory::getModelById($userIdToAdd);
                    if (!$this->linkUser($userToAdd)) {
                        return false;
                    }
                }
            }
            
            if (!empty($existingUsers)) {
                foreach ($existingUsers as $userToUnlink) {
                    if (!$this->unlinkUser($userToUnlink)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    protected function updateEventNotifiesOnFormSubmission(GI_Form $form, GI_Model $subjectModel) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (empty($this->getProperty('source_context_role_id')) || empty($subjectModel)) {
                return true;
            }
            $searchTableName = EventFactory::getDbPrefix() . 'event';
            $search = EventFactory::search();
            $join = $search->createJoin('event_notifies', 'event_id', $searchTableName, 'id', 'EN');
            $join->filter('EN.table_name', $subjectModel->getTableName())
                    ->filterNull('EN.item_id')
                    ->filter('EN.context_role_id', $this->getProperty('source_context_role_id'));
            $events = $search->select();
            if (!empty($events)) {
                foreach ($events as $event) {
                    $eventNotifiesSearch = EventNotifiesFactory::search();
                    $eventNotifiesSearch->filter('event_id', $event->getId())
                            ->filter('table_name', $subjectModel->getTableName())
                            ->filterNull('item_id');
                    $eventNotifiesArray = $eventNotifiesSearch->select();
                    if (!empty($eventNotifiesArray)) {
                        foreach ($eventNotifiesArray as $eventNotifies) {
                            $specificSearch = EventNotifiesFactory::search();
                            $specificSearch->filter('table_name', $subjectModel->getTableName())
                                    ->filter('item_id', $subjectModel->getId())
                                    ->filter('event_id', $eventNotifies->getProperty('event_id'));
                            $eventNotifiesContextRoleId = $eventNotifies->getProperty('context_role_id');
                            if (!empty($eventNotifiesContextRoleId) && ($eventNotifiesContextRoleId == $this->getId() || $eventNotifiesContextRoleId == $this->getProperty('source_context_role_id'))) {
                                $specificSearch->filterGroup()
                                        ->filterGroup()
                                        ->filter('context_role_id', $this->getId())
                                        ->closeGroup()
                                        ->orIf()
                                        ->filterGroup()
                                        ->andIf()
                                        ->filter('context_role_id', $this->getProperty('source_context_role_id'))
                                        ->closeGroup()
                                        ->closeGroup()
                                        ->andIf();
                                $specificResults = $specificSearch->select();
                                if (!empty($specificResults)) {
                                    $specificEventNotifies = $specificResults[0];
                                    if ($specificEventNotifies->getProperty('context_role_id') == $this->getProperty('source_context_role_id')) {
                                        $specificEventNotifies->setProperty('context_role_id', $this->getId());
                                        if (!$specificEventNotifies->save()) {
                                            return false;
                                        }
                                    }
                                } else {
                                    $newSpecificEventNotifies = EventNotifiesFactory::buildNewModel();
                                    $newSpecificEventNotifies->setPropertiesFromOtherModel($eventNotifies);
                                    $newSpecificEventNotifies->setProperty('item_id', $subjectModel->getId());
                                    $newSpecificEventNotifies->setProperty('context_role_id', $this->getId());
                                    if (!$newSpecificEventNotifies->save()) {
                                        return false;
                                    }
                                }
                            } else {
                                if (!empty($eventNotifies->getProperty('role_id'))) {
                                    $specificSearch->filter('role_id', $eventNotifies->getProperty('role_id'));
                                } else if (!empty($eventNotifies->getProperty('user_id'))) {
                                    $specificSearch->filter('user_id', $eventNotifies->getProperty('user_id'));
                                } else {
                                    $specificSearch->filter('context_role_id', $eventNotifies->getProperty('context_role_id'));
                                }
                                $specificResults = $specificSearch->select();
                                if (empty($specificResults)) {
                                    $newSpecificEventNotifies = EventNotifiesFactory::buildNewModel();
                                    $newSpecificEventNotifies->setPropertiesFromOtherModel($eventNotifies);
                                    $newSpecificEventNotifies->setProperty('item_id', $subjectModel->getId());
                                    if (!$newSpecificEventNotifies->save()) {
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return true;
        }
        return false;
    }

    public function getFormView(GI_Form $form) {
        return new ContextRoleFormView($form, $this);
    }
    
    public function getIsDeleteable() {
        //TODO  permission check
        if (empty($this->getProperty('system'))) {
            return true;
        }
        return false;
    }

    public function softDelete() {
        $childSearch = ContextRoleFactory::search();
        $childSearch->filter('source_context_role_id', $this->getId());
        $children = $childSearch->select();
        if (!empty($children)) {
            foreach ($children as $child) {
                $child->setProperty('source_context_role_id', NULL);
                if (!$child->save()) {
                    return false;
                }
            }
        }

        //soft delete all user has context role DAOs
        $search = new GI_DataSearch('user_has_context_role');
        $search->filter('context_role_id', $this->getId());
        $daoArray = $search->select();
        if (!empty($daoArray)) {
            foreach ($daoArray as $userHasContextRoleDAO) {
                $userHasContextRoleDAO->setProperty('status', 0);
                if (!$userHasContextRoleDAO->save()) {
                    return false;
                }
            }
        }

        //soft delete all event notifies
        $eventNotifiesArray = $this->getEventNotifiesArray();
        if (!empty($eventNotifiesArray)) {
            foreach ($eventNotifiesArray as $eventNotifies) {
                if (!$eventNotifies->softDelete()) {
                    return false;
                }
            }
        }

        if (!parent::softDelete()) {
            return false;
        }
        
        return true;
    }
    
    
    
    

}
