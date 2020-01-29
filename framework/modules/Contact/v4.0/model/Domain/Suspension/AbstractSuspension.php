<?php
/**
 * Description of AbstractSuspension
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractSuspension extends GI_Model {

    protected $contact;
    protected $users;

    public function setContact(AbstractContact $contact) {
        $this->contact = $contact;
        $this->setProperty('contact_id', $contact->getId());
    }

    public function getContact() {
        if (empty($this->contact)) {
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }

    public function getIsAddable() {
        if (Permission::verifyByRef('add_suspensions')) {
            return true;
        }
        return false;
    }

    public function getIsEditable() {
        if (Permission::verifyByRef('edit_suspensions')) {
            return true;
        }
        return false;
    }

    public function getIsDeleteable() {
        if (Permission::verifyByRef('delete_suspensions')) {
            return true;
        }
        return false;
    }

    public function getIsViewable() {
        if (Permission::verifyByRef('view_suspensions')) {
            return true;
        }
        return false;
    }

    public function getFormView(GI_Form $form) {
        return new SuspensionFormView($form, $this);
    }

    public function getDetailView() {
        return new SuspensionDetailView($this);
    }

    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $wasActive = true;
            if (empty($this->getId()) || !$this->isActive()) {
                $wasActive = false;
            }
            $this->setPropertiesFromForm($form);
            $this->setProperty('system', 0);
            $this->setProperty('active', 1);
            if (!$this->save()) {
                return false;
            }
            $affectedUsers = $this->getAffectedUsers();
            if (!empty($affectedUsers)) {
                foreach ($affectedUsers as $user) {
                    $user->clearIsSuspendedCache();
                }
            }
            if (!$wasActive && $this->isActive()) {
                $this->sendAccountSuspendedEmailToAffectedUsers();
            }
            return true;
        }
        return false;
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        $startDateTime = filter_input(INPUT_POST, 'start_date_time');
        $endDateTime = filter_input(INPUT_POST, 'end_date_time');
        $notes = filter_input(INPUT_POST, 'notes');

        $this->setProperty('start_date_time', $startDateTime);
        $this->setProperty('end_date_time', $endDateTime);
        $this->setProperty('notes', $notes);
    }

    public function validateForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {

            return true;
        }
        return false;
    }

    public function getAddURL() {
        $attrs = $this->getAddURLAttrs();
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs);
        }
        return NULL;
    }

    public function getAddURLAttrs() {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        return array(
            'controller'=>'contact',
            'action'=>'addSuspension',
            'cId'=>$contact->getId(),
        );
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL($this->getEditURLAttrs());
    }

    public function getEditURLAttrs() {
        return array(
            'controller'=>'contact',
            'action'=>'editSuspension',
            'id'=>$this->getId(),
        );
    }

    public function getDeleteURL() {
        return GI_URLUtils::buildURL($this->getDeleteURLAttrs());
    }

    public function getDeleteURLAttrs() {
        return array(
            'controller'=>'contact',
            'action'=>'deleteSuspension',
            'id'=>$this->getId(),
        );
    }

    public function getRemoveURL() {
        return GI_URLUtils::buildURL($this->getRemoveURLAttrs());
    }

    public function getRemoveURLAttrs() {
        return array(
            'controller'=>'contact',
            'action'=>'removeSuspension',
            'id'=>$this->getId(),
        );
    }

    public function isActive() {
        $endDate = $this->getProperty('end_date_time');
        $active = $this->getProperty('active');
        if (!empty($endDate)) {
            $endDateTime = new DateTime($endDate);
            $currentDateTime = new DateTime(GI_Time::getDateTime());
            if ($currentDateTime < $endDateTime && !empty($active)){
                return true;
            }
        } else {
            if (!empty($active)) {
                return true;
            }
        }
        return false;
    }

    public function getAddedByName() {
        $user = UserFactory::getModelById($this->getProperty('uid'));
        if (!empty($user)) {
            return $user->getFullName();
        }
        return '';
    }

    public function getAffectedUsers() {
        if (empty($this->users)) {
            $search = $this->getAffectedUsersDataSearch();
            $this->users = $search->select();
        }
        return $this->users;
    }

    public function getAffectedUsersCount() {
        if (empty($this->users)) {
        $search = $this->getAffectedUsersDataSearch();
        if (!empty($search)) {
            return $search->count();
        }
        } else {
            return count($this->users);
        }
        return NULL;
    }

    /**
     *
     * @return GI_DataSearch
     */
    protected function getAffectedUsersDataSearch() {
        $search = UserFactory::search();
        $tableName = $search->prefixTableName('user');

        $directJoin = $search->createLeftJoin('contact', 'source_user_id', $tableName, 'id', 'DIR_CON');
            $directJoin->filter('DIR_CON.status', 1);
            $search->ignoreStatus('DIR_CON');

            $viaJoin = $search->createLeftJoin('contact', 'source_user_id', $tableName, 'id', 'VIA_CON');
            $viaJoin->filter('VIA_CON.status', 1);
            $search->ignoreStatus('VIA_CON');

            $viaRelJoin = $search->createLeftJoin('contact_relationship', 'c_contact_id', 'VIA_CON', 'id', 'CON_REL');
            $viaRelJoin->filter('CON_REL.status', 1);
            $search->ignoreStatus('CON_REL');

            $search->filterGroup()
                    ->filterGroup()
                     ->filter('DIR_CON.id', $this->getProperty('contact_id'))
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->filter('CON_REL.p_contact_id', $this->getProperty('contact_id'))
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
            return $search;
    }

    public function remove() {
        if (!$this->isDeleteable()) {
            return false;
        }
        $affectedUsers = $this->getAffectedUsers();
        if (!empty($affectedUsers)) {
            foreach ($affectedUsers as $user) {
                if (!$user->clearIsSuspendedCache()) {
                    return false;
                }
            }
        }
        $this->setProperty('active', 0);
        $this->setProperty('end_date_time', GI_Time::getDateTime());
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public function softDelete() {
        if (!$this->isDeleteable()) {
            return false;
        }
        $affectedUsers = $this->getAffectedUsers();
        if (!empty($affectedUsers)) {
            foreach ($affectedUsers as $user) {
                if (!$user->clearIsSuspendedCache()) {
                    return false;
                }
            }
        }
        return parent::softDelete();
    }

    public function sendAccountSuspendedEmailToAffectedUsers(AbstractUser $user = NULL, $reason = '') {
        if (!empty($user)) {
            $users = array($user);
        } else {
            $users = $this->getAffectedUsers();
        }
        if (empty($users)) {
            return true;
        }
        $supportEmail = ProjectConfig::getSupportEmail();
        $message = 'Your account has been temporarily suspended';
        if (!empty($reason)) {
            $message .= ' due to ' . $reason . '.';
        } else {
            $message .- '.';
        }
        $message .= ' Please contact support at <a href="mailto:' . $supportEmail . '">' . $supportEmail . '</a> for more information.';
        $emailView = new GenericEmailView();
        $emailView->setTitle(ProjectConfig::getSiteTitle() . ' Attention Required');
        $emailView->addHTML(ProjectConfig::getSiteTitle() . ' <h3>Attention Required</h3>');
        $emailView->startBlock();
        $emailView->addHTML('<h4>Account Suspended</h4>');
        $emailView->addParagraph($message);
        $emailView->closeBlock();
        if (!DEV_MODE) {
            foreach ($users as $user) {
                $giEmail = new GI_Email();
                $giEmail->addTo($user->getProperty('email'), $user->getFullName())
                        ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                        ->setSubject(ProjectConfig::getSiteTitle() . ' Attention Required')
                        ->useEmailView($emailView);

                if (!$giEmail->send()) {
                    return false;
                }
            }
        } 
        return true;
    }

}
