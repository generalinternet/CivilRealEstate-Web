<?php

class StaticReferralsView extends GI_View{

    protected $form;

    public function __construct(GI_Form $referralForm = null) {
        parent::__construct();
        if(empty($referralForm)){
            $referralForm = new GI_Form('referral');
        }

        $this->form = $referralForm;
        $this->buildForm();
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="referral__form-wrap">');
            $this->form->addHTML('<h3 class="referral__title">Want more info? Apply Now!</h3>');
            $this->form->addHTML('<div class="referral__form-row referral__form-row_size_half">');
                $this->form->addField('first_name', 'text', array(
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
                $this->form->addField('referred_by', 'text', array(
                    'required' => false,
                    'placeHolder' => 'Referred by'
                ));
                $this->form->addField('message', 'textarea', array(
                    'placeHolder' => 'Tell us a bit about your network'
                ));
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="referral__form-row referral__form-row_size_full">');
                $this->form->addHTML('<div class="referral__break-line"><hr></div>');
                $this->form->addHTML('<p class="referral__description"><b>* Indicates a required ﬁeld</b>  |  Your information will never be shared with any third party.</p>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="referral__button-wrap">');
            $this->form->addHTML('<span class="button button_theme_red_primary submit_btn">Connect with an Agent</span>');
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
        $title = "referrals";
        $this->addHTML('<section class="section section_type_banner banner banner_size_normal banner_page_referral">');
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
        $this->addHTML('<section class="section section_type_referral referral">');
            $this->addHTML('<div class="container">');

                $this->addHTML('<div class="row referral__description-row">');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<h3 class="referral__title">Get Rewarded For Networking and Raising Money For Charity.</h3>');
                        $this->addHTML('<div class="referral__description-cols">');
                            $this->addHTML('<p class="referral__description-line">');
                                $this->addHTML('<span class="referral__description-col-left">The Civil Real Estate model provides</span>');
                                $this->addHTML('<span class="referral__description-col-right">That\'s in addition to raising for money</span>');
                            $this->addHTML('</p>');
                            $this->addHTML('<p class="referral__description-line">');
                                $this->addHTML('<span class="referral__description-col-left">real commission based rewards</span>');
                                $this->addHTML('<span class="referral__description-col-right">for charity. This is how Civil is seeking</span>');
                            $this->addHTML('</p>');
                            $this->addHTML('<p class="referral__description-line">');
                                $this->addHTML('<span class="referral__description-col-left">for all referral partners that bring</span>');
                                $this->addHTML('<span class="referral__description-col-right">to disrupt the entire Real Estate</span>');
                            $this->addHTML('</p>');
                            $this->addHTML('<p class="referral__description-line">');
                                $this->addHTML('<span class="referral__description-col-left">home buyers and sellers to Civil.</span>');
                                $this->addHTML('<span class="referral__description-col-right">Industry, for the beneﬁt of everyone. </span>');
                            $this->addHTML('</p>');
                        $this->addHTML('</div>');
                        $this->addHTML('<div class="referral__description-illustration" alt="'.SITE_TITLE.'"></div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');

                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-10 col-md-push-1">');
                        $this->addHTML('<div class="referral__wrap">');
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
