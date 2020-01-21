<?php

class StaticContactView extends GI_View{

    protected $form;

    public function __construct(GI_Form $contactForm = null) {
        parent::__construct();
        if(empty($contactForm)){
            $contactForm = new GI_Form('contact');
        }

        $this->form = $contactForm;
        $this->buildForm();
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="contact__form-wrap">');
            $this->form->addField('firts_name', 'text', array(
                'required' => true,
                'placeHolder' => 'First Name*'
            ));
            $this->form->addField('last_name', 'text', array(
                'required' => true,
                'placeHolder' => 'Last Name*'
            ));
            $this->form->addField('email', 'email', array(
                'required' => true,
                'placeHolder' => 'Email*'
            ));
            $this->form->addField('phone', 'phone', array(
                'required' => true,
                'placeHolder' => 'Phone Number*'
            ));
            $this->form->addField('referral', 'text', array(
                'placeHolder' => 'Referral Source'
            ));
            $this->form->addField('address', 'text', array(
                'placeHolder' => 'Address'
            ));
            $this->form->addField('city', 'text', array(
                'placeHolder' => 'City'
            ));
            $this->form->addField('province', 'text', array(
                'placeHolder' => 'Province'
            ));
            $this->form->addField('postal_code', 'number', array(
                'placeHolder' => 'Postal Code'
            ));
            $this->form->addField('mail_list', 'checkbox', array(
                'displayName' => 'Check this box to signup for our mailing list'
            ));
            $this->form->addHTML('<p class="contact__description"><b>* Indicates a required ﬁeld</b>  |  Your information will never be shared with any third party.</p>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="contact__button-wrap">');
            $this->form->addHTML('<span class="button button_theme_primary submit_btn">Connect with an Agent</span>');
        $this->form->addHTML('</div>');
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addViewBody();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="view_wrap">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    public function addViewBody() {
        $this->addHeaderBannerSection();
        $this->addFormSection();
    }
    
    public function addHeaderBannerSection() {
        $title = "contact us";
        $this->addHTML('<section class="section section_type_banner banner banner_size_normal banner_page_contact">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12">');
                        $this->addHTML('<div class="banner__content-wrap text-center">');
                            $this->addHTML('<h1 class="banner__title">'.$title.'</h1>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }

    public function addFormSection(){
        $this->addHTML('<section class="section section_type_contact contact">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<div class="contact__wrap">');
                            $this->addHTML('<h3 class="contact__title">Just start by ﬁlling out this easy form.</h3>');
                            $this->addHTML($this->form->getForm());
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }

    public function beforeReturningView() {
        $this->buildView();
    }
}
