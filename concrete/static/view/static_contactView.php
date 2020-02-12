<?php

class StaticContactView extends GI_View{

    protected $form;
    protected $isSent;

    public function __construct(GI_Form $contactForm = null) {
        parent::__construct();
        if(empty($contactForm)){
            $contactForm = new GI_Form('contact_form');
        }

        $this->form = $contactForm;
        $this->buildForm();
    }

    public function setSent(bool $isSent){
        $this->isSent = $isSent;
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="contact__form-wrap">');
            $this->form->addHTML('<div class="contact__form-row contact__form-row_size_half">');
                $this->form->addField('first_name', 'text', array(
                    'required' => true,
                    'placeHolder' => 'First Name*'
                ));
                $this->form->addField('last_name', 'text', array(
                    'required' => true,
                    'placeHolder' => 'Last Name*'
                ));
                $this->form->addField('r_email', 'email', array(
                    'required' => true,
                    'placeHolder' => 'Email*'
                ));
                $this->form->addField('phone', 'phone', array(
                    'required' => true,
                    'placeHolder' => 'Phone Number*'
                ));
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="contact__form-row contact__form-row_size_full">');
                $this->form->addField('message', 'textarea', array(
                    'placeHolder' => 'Drop us a line. We would love to hear from you.'
                ));
                $this->form->addHTML('<div class="contact__break-line"><hr></div>');
                $this->form->addHTML('<p class="contact__description"><b>* Indicates a required ﬁeld</b> <span>Your information will never be shared with any third party.</span></p>');
            $this->form->addHTML('</div>');
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
                    if(!$this->isSent){
                        $this->addHTML('<h3 class="contact__title">Just start by ﬁlling out this easy form.</h3>');
                        $this->addHTML('<div class="contact__wrap">');
                            $this->addHTML($this->form->getForm());
                        $this->addHTML('</div>');
                    }else{
                        $this->addThankyouMessage();
                    }
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</section>');
    }

    protected function addThankyouMessage(){
        $this->addHTML('
            <h3 class="contact__title contact__title_sent">Thank you! Your request has been sent.</h3>
            <p class="contact__description contact__description_sent">We will get back to you as soon as posible.</p>
        ');
    }

    public function beforeReturningView() {
        $this->buildView();
    }
}
