<?php

class LoginIndexView extends AbstractLoginIndexView {

    public function __construct(GI_Form $form) {
        parent::__construct($form);
        $this->addSiteTitle('Log In');
        $this->setDescription(SITE_TITLE . " - Log In");
    }

    protected function openFormBody($class ='') {
        $this->form->addHTML('<div class="main_body form_body'.$class.' login__form form">');
    }

    protected function addSubmitBtn(){
        $this->form->addHTML('<div class="login__submit-wrap">');
        $this->form->addHTML('<span class="submit_btn button button_has-icon button_theme_primary">LOG IN <span class="button__icon button__icon_color_dark"></span></span>');
        $this->form->addHTML('</div>');

        $signUpURL = GI_URLUtils::buildCleanURL(array(
            'controller' => 'user',
            'action' => 'signup',
        ));
        $this->form->addHTML('<div class="login__sign-up-link-wrap">New Investor? <a href="'.$signUpURL.'" class="login__sign-up-link">Sign up here!</a> </div>');
    }

    protected function addEmailField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'class' => 'form__input form__input_label-color_grey',
            'displayName' => 'Email',
            'placeHolder' => 'Email',
            'autoFocus' => true,
            'value' => '',
            'required' => true
        ), $fieldSettings);
        
        $this->form->addField('email', 'email', $defaultFieldSettings);
    }
    
    protected function addPasswordField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'class' => 'form__input form__input_label-color_grey',
            'displayName' => 'Password',
            'placeHolder' => 'Password',
            'value' => '',
            'required' => true
        ), $fieldSettings);
        $this->form->addField('password', 'password', $defaultFieldSettings);
    }

    protected function addRememberMeField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'class' => 'form__input form__input_label-color_white form__input_type_checkbox',
            'displayName' => 'Remember Me',
            'value' => '0',
            'required' => false,
            'onoffStyleAsCheckbox' => true
        ), $fieldSettings);
        if($this->addRememberMe){
            $this->form->addField('remember_me', 'onoff', $defaultFieldSettings);
        }
    }
}
