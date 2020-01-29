<?php
/**
 * Description of AbstractSettingsNotif
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractSettingsNotif extends AbstractSettings {

    public function getFormView(GI_Form $form) {
        return new UserNotificationSettingsFormView($form, $this);
    }

    public function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $values = array(
                'in_system'=>0,
                'text'=>0,
                'immediate_email'=>0,
                'email_digest'=>0
            );
            $options = filter_input(INPUT_POST, 'options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($options)) {
                foreach ($options as $option) {
                    $values[$option] = 1;
                }
            }
            foreach ($values as $colKey=>$value) {
                $this->setProperty('settings_notif.' . $colKey, $value);
            }
            
            
            $phone = filter_input(INPUT_POST, 'phone');
            $email = filter_input(INPUT_POST, 'email');
            
            $this->setProperty('settings_notif.alt_phone', $phone);
            $this->setProperty('settings_notif.alt_email', $email);
            return true;
        }
        return false;
    }

    public function getEditURLAttrs() {
        $attrs = array(
            'controller' => 'user',
            'action' => 'editNotificationSettings',
        );
        if (empty($this->getId())) {
            $attrs['type'] = $this->getTypeRef();
            $eventId = $this->getProperty('settings_notif.event_id');
            if (!empty($eventId)) {
                $attrs['eventId'] = $eventId;
            }
        } else {
            $attrs['id'] = $this->getId();
        }
        return $attrs;
    }
    
    public function getEditURL() {
        return GI_URLUtils::buildURL($this->getEditURLAttrs());
    }
    
    
    public function setPropertiesFromModel(\GI_Model $model) {
        if (!($model instanceof AbstractSettingsNotif)) {
            return false;
        }
        $this->setProperty('user_id', $model->getProperty('user_id'));
        $this->setProperty('settings_notif.event_id', $model->getProperty('settings_notif.event_id'));
        $this->setProperty('settings_notif.in_system', $model->getProperty('settings_notif.in_system'));
        $this->setProperty('settings_notif.text', $model->getProperty('settings_notif.text'));
        $this->setProperty('settings_notif.immediate_email', $model->getProperty('settings_notif.immediate_email'));
        $this->setProperty('settings_notif.email_digest', $model->getProperty('settings_notif.email_digest'));
        $this->setProperty('settings_notif.alt_email', $model->getProperty('settings_notif.alt_email'));
        $this->setProperty('settings_notif.alt_phone', $model->getProperty('settings_notif.alt_phone'));
        return true;
    }
    
    public function setNotificationProperties(AbstractNotification $notification) {
        $notification->setProperty('in_system', $this->getProperty('settings_notif.in_system'));
        $notification->setProperty('text', $this->getProperty('settings_notif.text'));
        $notification->setProperty('immediate_email', $this->getProperty('settings_notif.immediate_email'));
        $notification->setProperty('email_digest', $this->getProperty('settings_notif.email_digest'));
        $notification->setProperty('to_phone', $this->getProperty('settings_notif.alt_phone'));
        $notification->setProperty('to_email', $this->getProperty('settings_notif.alt_email'));
        return true;
    }
}
