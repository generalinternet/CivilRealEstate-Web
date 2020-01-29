<?php
/**
 * Description of AbstractEventNotificationSettingsView
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventNotificationSettingsView extends MainWindowView {
    
    protected $sampleEvent = NULL;
    protected $subjectModel = NULL;
    
    public function __construct(AbstractEvent $sampleEvent, GI_Model $subjectModel) {
        parent::__construct();
        $this->sampleEvent = $sampleEvent;
        $this->subjectModel = $subjectModel;
    }

    protected function addViewBodyContent() {
        $this->addEventsSection();
        $this->addContextRolesSection();
        return $this;
    }
    
    protected function addEventsSection() {
        $allEvents = EventFactory::getModelArrayByTypeRef($this->sampleEvent->getTypeRef());
        if (!empty($allEvents)) {
            $this->addHTML('<h2>Notify on Event</h2>');
            $this->addEventsTable($allEvents);
            $this->addHTML('<hr />');
        }
    }

    protected function addEventsTable($events) {
        $this->addHTML('<table class="ui_table">');
        $this->addEventsTableHeader();
        $this->addHTML('<tbody>');
        foreach ($events as $event) {
            if ($event->isViewable()) {
                $this->addEventsTableRow($event);
            }
        }
        $this->addHTML('</tbody>');
        $this->addHTML('</table>');
    }

    protected function addEventsTableHeader() {
        $this->addHTML('<thread>');
        $this->addHTML('<tr>')
                ->addHTML('<th></th>')
                ->addHTML('<th colspan="3">Notify all users with</th>')
                ->addHTML('<th></th>')
                ->addHTML('</tr>');
        $this->addHTML('<tr>')
                ->addHTML('<th>Event</th>')
                ->addHTML('<th>System Role(s)</th>')
                ->addHTML('<th>' . $this->sampleEvent->getTypeTitle() . ' Role(s)</th>')
                ->addHTML('<th>Name(s)</th>')
                ->addHTML('<th></th>')
                ->addHTML('</tr>');
        $this->addHTML('</thead>');
    }

    protected function addEventsTableRow(AbstractEvent $event) {
        $event->setSubjectModel($this->subjectModel);
        $subjectModelDefaultEventNotifies = $event->getSubjectModelDefaultEventNotifies();
        $noRoles = false;
        $noContextRoles = false;
        $noUsers = false;
        if (!empty($subjectModelDefaultEventNotifies)) {
            if (!empty($subjectModelDefaultEventNotifies->getProperty('no_roles'))) {
                $noRoles = true;
            }
            if (!empty($subjectModelDefaultEventNotifies->getProperty('no_context_roles'))) {
                $noContextRoles = true;
            }
            if (!empty($subjectModelDefaultEventNotifies->getProperty('no_users'))) {
                $noUsers = true;
            }
        }
        $usedDefaultRoles = false;
        $rolesString = '';
        if (!$noRoles) {
            $roles = $event->getLinkedRoles(false);
            if (empty($roles)) {
                $roles = $event->getDefaultLinkedRoles();
                $usedDefaultRoles = true;
            }
            if (!empty($roles)) {
                foreach ($roles as $role) {
                    if ($usedDefaultRoles) {
                        $rolesString .= '<b>';
                    }
                    $rolesString .= $role->getProperty('title') . '<br />';
                    if ($usedDefaultRoles) {
                        $rolesString .= '</b>';
                    }
                }
            }
        }

        $usedDefaultContextRoles = false;
        $contextRolesString = '';
        if (!$noContextRoles) {
            $contextRoles = $event->getLinkedContextRoles(false);
            if (empty($contextRoles)) {
                $usedDefaultContextRoles = true;
                $contextRoles = $event->getDefaultLinkedContextRoles();
            }

            if (!empty($contextRoles)) {
                foreach ($contextRoles as $contextRole) {
                    if ($usedDefaultContextRoles) {
                        $contextRolesString .= '<b>';
                    }
                    $contextRolesString .= $contextRole->getTitle() . '<br />';
                    if ($usedDefaultContextRoles) {
                        $contextRolesString .= '</b>';
                    }
                }
            }
        }

        $usedDefaultUsers = false;
        $usersString = '';
        if (!$noUsers) {
            $users = $event->getLinkedUsers();
            if (empty($users)) {
                $users = $event->getDefaultLinkedUsers();
                $usedDefaultUsers = true;
            }
            if (!empty($users)) {
                foreach ($users as $user) {
                    if ($usedDefaultUsers) {
                        $usersString .= '<b>';
                    }
                    $usersString .= $user->getFullName() . '<br />';
                    if ($usedDefaultUsers) {
                        $usersString .= '</b>';
                    }
                }
            }
        }
        $editURL = $this->subjectModel->getEditNotificationSettingsURL($event);
        $editString = '';
        if (!empty($editURL)) {
            $editString = '<a href="' . $editURL . '" title="Edit Settings" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getIcon('pencil') . '</a>';
        }
        $this->addHTML('<tr>');
        $this->addHTML('<td>' . $event->getTitle() . '</td>')
                ->addHTML('<td>' . $rolesString . '</td>')
                ->addHTML('<td>' . $contextRolesString . '</td>')
                ->addHTML('<td>' . $usersString . '</td>')
                ->addHTML('<td>' . $editString . '</td>');
        $this->addHTML('</tr>');
    }

    protected function addContextRolesSection() {
        $subjectModel = $this->subjectModel;
        $this->addHTML('<h2>' . $this->sampleEvent->getTypeTitle() . ' Roles</h2>');
        $contextRoles = $subjectModel->getContextRoles(true);
        $this->addRolesTable($contextRoles);
    }

    protected function addRolesTable($contextRoles) {
        $this->addHTML('<div class="right_btns">');
        $this->addAddContextRoleButton();
        $this->addHTML('</div>');
        
        $this->addHTML('<table class="ui_table">');
        $this->addRolesTableHeader();
        $this->addHTML('<tbody>');
        foreach ($contextRoles as $contextRole) {
            $this->addRolesTableRow($contextRole);
        }
        $this->addHTML('</tbody>');
        $this->addHTML('</table>');
    }
    
    protected function addAddContextRoleButton() {
        $addURL = $this->subjectModel->getAddContextRoleURL();
        $this->addHTML('<a href="' . $addURL . '" title="Add" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getIcon('plus') . ' ' . $this->sampleEvent->getTypeTitle() .' Role</a>');
    }

    protected function addRolesTableHeader() {
        $this->addHTML('<thread>');
        $this->addHTML('<tr>')
                ->addHTML('<th>' . $this->sampleEvent->getTypeTitle() . ' Role</th>')
                ->addHTML('<th>Assigned User(s)</th>')
                ->addHTML('<th></th>') //Edit
                ->addHTML('<th></th>') //Delete
                ->addHTML('</tr>');
        $this->addHTML('</thead>');
    }

    protected function addRolesTableRow(AbstractContextRole $contextRole) {
        $usedDefaults = false;
        if (!empty($this->subjectModel->getId()) && empty($contextRole->getProperty('item_id'))) {
            $usedDefaults = true;
        }
        $titleString = '';
        $usersString = '';
        $editString = '';
        $deleteString = '';
        
        if ($usedDefaults) {
            $titleString .= '<b>';
        }
        $titleString .= $contextRole->getProperty('title');
        if ($usedDefaults) {
            $titleString .= '</b>';
        }
        
        $users = $contextRole->getUsers();
        
        if (!empty($users)) {
            foreach ($users as $user) {
                if ($usedDefaults) {
                    $usersString .= '<b>';
                }
                $usersString .= $user->getFullName();
                if ($usedDefaults) {
                    $usersString .= '</b>';
                }
                $usersString .= '<br />';
            }
        }
        $editURL = $this->subjectModel->getEditContextRoleURL($contextRole->getId());
        if (!empty($editURL)) {
            $editString = '<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getIcon('pencil') . '</a>';
        }
        if ($contextRole->isDeleteable()) {
            $deleteURL = $this->subjectModel->getDeleteContextRoleURL($contextRole->getId());
            if (!empty($deleteURL)) {
                $deleteString = '<a href="' . $deleteURL . '" title="Delete" class="custom_btn open_modal_form">' . GI_StringUtils::getIcon('eks') . '</a>';
            }
        }


        $this->addHTML('<tr>');
        $this->addHTML('<td>' . $titleString . '</td>')
                ->addHTML('<td>' . $usersString . '</td>')
                ->addHTML('<td>' . $editString . '</td>')
                ->addHTML('<td>' . $deleteString . '</td>');
        $this->addHTML('</tr>');
    }

}
