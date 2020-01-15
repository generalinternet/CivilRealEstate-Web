<?php

class LoginResetPasswordView extends AbstractLoginResetPasswordView {
    
    public function __construct(GI_Form $form) {
        parent::__construct($form);
        $this->addSiteTitle('Reset password');
        $this->setDescription(SITE_TITLE . " - Reset password");
    }
    
    public function buildView() {
        $this->addHeaderBannerSection();

        $this->addOpenFormWrap();
        $this->addHTML($this->form->getForm());
        $this->addCloseFormWrap();
    }

    public function addHeaderBannerSection() {
        $this->addHTML('<section id="header-banner-section" class="section_type_banner banner banner_size_normal banner_page_contact">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="banner__content-wrap">');
                            $this->addHTML('<h1 class="banner__title banner__title_color_primary">Reset Password</h1>');
                        $this->addHTML('</div>');
                    $this->addHTML("</div>");
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }

    public function addOpenFormWrap(){
        $this->addHTML('<section>');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-lg-8 col-lg-offset-2">');
    }

    public function addCloseFormWrap(){
                    $this->addHTML('</section>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    public function buildForm() {
        $user = Login::getUser();
        if($user && $user->requiresPassReset()){
            $this->form->addHTML('<div class="alert_message red"><p>For your security, we require you to reset your password.</p></div>');
        }
        $this->form->addHTML('<h3 class="section__sub-title">Enter a New Password</h3>');
        $this->addPasswordRules();
        $this->addPasswordField();
        $this->addRePasswordField();
        $this->addSubmitBtn();
    }
    
    protected function addPasswordRules(){
        $this->form->addHTML('<h4 class="sml_text">Your password</h4>');
        $this->form->addHTML('<ul class="simple_list sml_text">');
        $this->form->addHTML('<li>Cannot be the same as your current password.</li>');
        $minLength = ProjectConfig::getPassMinLength();
        if($minLength > 1){
            $this->form->addHTML('<li>Must be at least ' . $minLength . ' characters long.</li>');
        }
        
        $forceUpper = ProjectConfig::getPassReqUpper();
        if($forceUpper){
            $this->form->addHTML('<li>Must contain at least 1 uppercase letter.</li>');
        }
        
        $forceLower = ProjectConfig::getPassReqLower();
        if($forceLower){
            $this->form->addHTML('<li>Must contain at least 1 lowercase letter.</li>');
        }
        
        $forceSymbol = ProjectConfig::getPassReqSymbol();
        if($forceSymbol){
            $this->form->addHTML('<li>Must contain at least 1 symbol. (ex. #,@,!,?)</li>');
        }
        
        $forceNum = ProjectConfig::getPassReqNum();
        if($forceNum){
            $this->form->addHTML('<li>Must contain at least 1 number.</li>');
        }
        
        $this->form->addHTML('<li>Cannot contain any whitespace.</li>');
        $this->form->addHTML('</ul>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="button button_theme_primary button_has-icon submit_btn">' . Lang::getString('save_password') . ' <span class="button__icon button__icon_color_dark"></span> </span>');
    }
}
