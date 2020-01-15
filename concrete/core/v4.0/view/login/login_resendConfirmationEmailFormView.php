<?php

class LoginResendConfirmationEmailFormView extends AbstractLoginResendConfirmationEmailFormView {
    
    public function __construct(GI_Form $form) {
        parent::__construct($form);
        $this->addSiteTitle('Resend Confirmation Email');
        $this->setDescription(SITE_TITLE . " - Resend Confirmation Email");
    }
    
}
