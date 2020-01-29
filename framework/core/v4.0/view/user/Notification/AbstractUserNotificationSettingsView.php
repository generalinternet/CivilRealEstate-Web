<?php
/**
 * Description of AbstractUserNotificationSettingsView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractUserNotificationSettingsView extends MainWindowView {
    
    protected $user;
    
    public function __construct(AbstractUser $user) {
        parent::__construct();
        $this->user = $user;
    }
    
    protected function addViewHeaderContent() {
        if ($this->user->getId() == Login::getUserId()) {
            $this->addHTML('<h1>My Notification Settings</h1>');
        } else {
            $this->addHTML("<h1>" . $this->user->getFullName() . "'s Notification Settings");
        }
        
    }
    
    protected function addViewBodyContent() {
        $this->addGlobalSection();
        $this->addEventsByTypeSection();
    }
    
    protected function addGlobalSection() {
        $globalSettings = $this->user->getGlobalNotificationSettingsModel();
        if (empty($globalSettings)) {
            return;
        }
        if (empty($globalSettings->getId()) && !$globalSettings->save()) {
            return;
        }
        $this->addHTML('<h3>Default</h3>');

        $this->addHTML('<table class="ui_table">');
        $this->addTableHeader();
        $this->addHTML('<tbody>');
        if (Login::getUserId() != $this->user->getId()) {
            $editAttributes = $globalSettings->getEditURLAttrs();
            $editAttributes['userId'] = $this->user->getId();
            $editURL = GI_URLUtils::buildURL($editAttributes);
        } else {
            $editURL = $globalSettings->getEditURL();
        }
        $this->addTableRow($globalSettings, ' ', $editURL);
        $this->addHTML('</tbody>');
        $this->addHTML('</table>');
        $this->addHTML('<br />');
    }

    protected function addEventsByTypeSection() {
        $globalSettings = $this->user->getGlobalNotificationSettingsModel();
        $eventTypes = EventFactory::getTypesArray();
        $currentUserId = Login::getUserId();
        if (!empty($eventTypes)) {
            if (isset($eventTypes['event'])) {
                unset($eventTypes['event']);
            }
            foreach ($eventTypes as $eventTypeRef => $eventTypeTitle) {
                $notificationTypeRef = 'notification';
                $eventSearch = EventFactory::search();
                $eventSearch->filterByTypeRef($eventTypeRef)
                        ->orderBy('pos', 'ASC')
                        ->orderBy('id', 'ASC');
                $events = $eventSearch->select();
                if (!empty($events)) {
                    $this->addHTML('<h3>' . $eventTypeTitle . '</h3>');
                    $this->addHTML('<table class="ui_table">');
                    $this->addTableHeader('Event');
                    $this->addHTML('<tbody>');
                    foreach ($events as $event) {
                        $eventSettings = $this->user->getNotificationSettingsModel($event->getId(), $notificationTypeRef);
                        $eventSettings->setProperty('settings_notif.event_id', $event->getId());
                        if ($currentUserId != $this->user->getId()) {
                            $editAttributes = $eventSettings->getEditURLAttrs();
                            $editAttributes['userId'] = $this->user->getId();
                            $editURL = GI_URLUtils::buildURL($editAttributes);
                        } else {
                            $editURL = $eventSettings->getEditURL();
                        }
                        
                        if (empty($eventSettings->getId())) {
                            $this->addTableRow($globalSettings, $eventSettings->getProperty('title'), $editURL);
                        } else {
                            $this->addTableRow($eventSettings, NULL, $editURL);
                        }
                        
                    }
                    $this->addHTML('</tbody>');
                    $this->addHTML('</table>');
                    $this->addHTML('<br />');
                }
            }
        }
    }
    
    protected function addTableHeader($title = '') {
        $this->addHTML('<thead>')
                ->addHTML('<tr>')
                ->addHTML('<th>'.$title.'</th>')
                ->addHTML('<th>In System</th>')
                ->addHTML('<th>Text</th>')
                ->addHTML('<th>Immediate Email</th>')
                ->addHTML('<th>Email Digest</th>')
                ->addHTML('<th>Phone</th>')
                ->addHTML('<th>Email</th>')
                ->addHTML('<th></th>') //Buttons
                ->addHTML('</tr>')
                ->addHTML('</thead>');
    }
    
    protected function addTableRow(AbstractSettingsNotif $settings, $title = NULL, $editURL = '') {
        if (empty($title)) {
            $title = $settings->getProperty('title');
        }
        $colKeys = array(
            'settings_notif.in_system',
            'settings_notif.text',
            'settings_notif.immediate_email',
            'settings_notif.email_digest',
        );
        $this->addHTML('<tr>');
        $this->addHTML('<td>' . $title . '</td>');
        foreach ($colKeys as $colKey) {
            $value = $settings->getProperty($colKey);
            if (empty($value)) {
                $icon = GI_StringUtils::getIcon('eks');
            } else {
                $icon = GI_StringUtils::getIcon('check');
            }
            
            $this->addHTML('<td>' . $icon . '</td>');
        }
        $altPhone = $settings->getProperty('settings_notif.alt_phone');
        if (empty($altPhone)) {
            $altPhone = '--';
        }
        $this->addHTML('<td>' . $altPhone . '</td>');
        $altEmail = $settings->getProperty('settings_notif.alt_email');
        if (empty($altEmail)) {
            $altEmail = '--';
        }
        $this->addHTML('<td>' . $altEmail . '</td>');
        $editContent = '';
        if (!empty($editURL)) {
            $editContent = '<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form">'.GI_StringUtils::getIcon('pencil').'</a>';
        }
        $this->addHTML('<td>'.$editContent.'</td>');
        $this->addHTML('</tr>');
    }
    
    
}