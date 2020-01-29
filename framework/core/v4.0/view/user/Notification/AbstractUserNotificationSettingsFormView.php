<?php

/**
 * Description of AbstractUserNotificationSettingsFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserNotificationSettingsFormView extends GI_View {

    protected $form;
    /** @var AbstractSettingsNotif */
    protected $settings;
    protected $formBuilt = false;

    public function __construct(GI_Form $form, AbstractSettingsNotif $settings) {
        parent::__construct();
        $this->form = $form;
        $this->settings = $settings;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }
    
    public function buildFormHeader() {
        
    }
    
    public function buildFormBody() {
        $this->addNotificationOptionsField();
        $this->addPhoneField();
        $this->addEmailField();
    }
    
    protected function addNotificationOptionsField() {
        $options = array(
            'in_system'=>'In System',
            'text'=>'Text',
            'immediate_email'=>'Immediate Email',
            'email_digest'=>'Email Digest'
        );
        $values = array();
        if (!empty($this->settings->getProperty('settings_notif.in_system'))) {
            $values[] = 'in_system';
        }
        if (!empty($this->settings->getProperty('settings_notif.text'))) {
            $values[] = 'text';
        }
        if (!empty($this->settings->getProperty('settings_notif.immediate_email'))) {
            $values[] = 'immediate_email';
        }
        if (!empty($this->settings->getProperty('settings_notif.email_digest'))) {
            $values[] = 'email_digest';
        }
        $this->form->addField('options', 'checkbox', array(
            'displayName'=>'Options',
            'value'=>$values,
            'options'=>$options,
        ));
    }
    
    protected function addPhoneField() {
        $this->form->addField('phone', 'phone', array(
            'displayName' => 'Phone',
            'value' => $this->settings->getProperty('settings_notif.alt_phone')
        ));
    }

    protected function addEmailField() {
        $this->form->addField('email', 'email', array(
            'displayName' => 'Email',
            'value' => $this->settings->getProperty('settings_notif.alt_email')
        ));
    }

    public function buildFormFooter() {
        $this->addButtons();
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }

    public function addCancelBtn() {
        $this->form->addHTML('<span class="other_btn gray close_gi_modal">Cancel</span>');
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}