<?php

class LoginForgotPasswordView extends AbstractLoginForgotPasswordView {
    
    public function __construct(GI_Form $form) {
        parent::__construct($form);
        $this->addSiteTitle('Forgot Password');
        $this->setDescription(SITE_TITLE . " - Forgot Password");
    }
    
    public function buildView() {
        $this->addHeaderBannerSection();

        $this->addOpenFormWrap();
        $this->addForm();
        $this->addCloseFormWrap();
    }

    public function addHeaderBannerSection() {
        $this->addHTML('<section id="header-banner-section" class="section_type_banner banner banner_size_normal banner_page_contact">');
            
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="banner__content-wrap">');
                            $this->addHTML('<h1 class="banner__title banner__title_color_primary">Forgot Password</h1>');
                        $this->addHTML('</div>');
                    $this->addHTML("</div>");
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }

    public function addOpenFormWrap(){
        $this->addHTML('<section class="section section_bg_whtie">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-lg-10 col-lg-offset-1">');
    }

    public function addCloseFormWrap(){
                    $this->addHTML('</section>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="button button_theme_primary button_has-icon submit_btn">'.Lang::getString('reset_password').' <span class="button__icon button__icon_color_dark"></span></span>');
    }

    protected function buildForm() {
        $this->addEmailField(array('class' => 'form__input'));
        $this->addLoginActions();
        $this->addSubmitBtn();
    }

    protected function addLoginLink(){
        if($this->addLoginLink){
            $loginURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'index'
            ));
            if($this->thanks){
                $this->addHTML('<a href="'.$loginURL.'" title="' . Lang::getString('log_in') . '" class="button button_theme_dark">' . Lang::getString('log_in') . '</a>');
            } else {
                $this->form->addHTML('<a href="'.$loginURL.'" title="' . Lang::getString('log_in') . '" class="button button_theme_dark">' . Lang::getString('log_in') . '</a>');
            }
        }
    }
}
