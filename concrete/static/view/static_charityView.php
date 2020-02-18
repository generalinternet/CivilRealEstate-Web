<?php

class StaticCharityView extends GI_View{
    protected $form;

    public function __construct(GI_Form $form = NULL) {
        if(empty($form)){
            $form = new GI_Form('charity_form');
        }
        $this->form = $form;
        $this->buildForm();
    }

    protected function buildForm(){
        $this->addStep1();
        $this->addStep2();
        $this->addStep3();
        $this->addThankyou();
    }

    protected function addStep1(){
        $this->form->addHTML('<div class="charity__step charity__step-1">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>STEP 1</b> Select your Charity</h3>');
                $this->form->addHTML('<p class="charity__description">Choose the charity you would like funds from your real estate transaction to be directed to</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap charity__content-wrap_bg_secondary">');
                $autoCompURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'searchCharity',
                    'ajax' => 1
                ));
                $this->form->addField('charity_name', 'autocomplete', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Start Typing the Name of your Charity',
                    'autocompURL' => $autoCompURL,
                    'autocompMinLength' => 1,
                    'autocompMultiple' => false,
                ));
                $this->form->addHTML('<a href="" class="charity__form-link">see all charities »</a>');
                $this->form->addField('pick_later', 'checkbox', array(
                    'class' => 'form__input form__input_type_checkbox',
                    'options' => array(
                        'later' => 'pick later'
                    )
                ));
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<p class="charity__description">Some of Civil’s featured Charities</p>');
                $this->form->addHTML('<img src="resources/media/img/logo/logo_bar.png" alt="'.SITE_TITLE.'" class="charity__logos"> ');
                $this->form->addHTML('<a href="" class="button button_theme_primary button_has-icon charity__button_next-step">Go To Next Step <span class="button__icon"></span></a>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addStep2(){
        $this->form->addHTML('<div class="charity__step charity__step-2">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>STEP 2</b> Contact</h3>');
                $this->form->addHTML('<p class="charity__description">Please enter your phone number and/or email address.</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap charity__contact-form">');
                $this->form->addField('first_name', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'First Name *',
                    'required' => true
                ));
                $this->form->addField('last_name', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'First Name *',
                    'required' => true
                ));
                $this->form->addField('r_email', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Email',
                    'required' => true
                ));
                $this->form->addField('phone', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Phone Number',
                    'required' => true
                ));
                $this->form->addHTML('<div class="charity__contact-form-buttons">');
                    $this->form->addHTML('<a href="" class="button button_theme_primary button_has-icon charity__button_next-step">Go To Next Step <span class="button__icon"></span></a>');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addStep3(){
        $this->form->addHTML('<div class="charity__step charity__step-3">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>STEP 3</b> Buying or Selling?</h3>');
                $this->form->addHTML('<p class="charity__description">Are you interested in finding or listing a home?</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap charity__buy-or-sell">');
            $this->form->addField('buy_or_sell', 'checkbox', array(
                'class' => 'form__input form__input_type_checkbox',
                'options' => array(
                    'buying' => 'Buying',
                    'selling' => 'Selling',
                )
            ));
            $this->form->addHTML('<div class="charity__content-buttons">');
                $this->form->addHTML('<a href="" class="submit_btn button button_theme_primary button_has-icon">Submit <span class="button__icon"></span></a>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addThankyou(){
        $this->form->addHTML('<div class="charity__step charity__step-thank-you">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>THANK YOU</b> For Making a Difference!</h3>');
                $this->form->addHTML('<p class="charity__description">A Civil Real Estate Local Expert will be in contact with you promtly.</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<br>');
            $this->form->addHTML('<br>');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<div class="charity__content-buttons">');
                    $this->form->addHTML('<a href="" class="submit_btn button button_theme_primary button_has-icon">DONE <span class="button__icon button__icon_type_check"></span></a>');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    public function buildView() {
        $this->addCharityBannerSection();
        $this->addCharityFormSection();
    }

    protected function addCharityBannerSection(){
        $this->addHTML('<div class="section section_type_banner banner banner_page_charity">');
        $this->addHTML('</div>');
    }

    protected function addCharityFormSection(){
        $this->addHTML('<div class="section section_type_charity charity charity_step_1" data-step=1>');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__banner">');
                            $this->addHTML('<img src="resources/media/img/banner/charity/step_1.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-1">');
                            $this->addHTML('<img src="resources/media/img/banner/charity/step_2.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-2">');
                            $this->addHTML('<img src="resources/media/img/banner/charity/step_3.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-3">');
                            $this->addHTML('<img src="resources/media/img/banner/charity/step_4.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-4">');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__content">');
                            $this->addHTML('<div class="charity__step-selector">');
                                $this->addStepSelector();
                            $this->addHTML('</div>');
                            $this->addHTML($this->form->getForm());
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addStepSelector(){
        $stepArr = array(
            array(
                'title' => 'Select charity',
                'number' => '1'
            ),
            array(
                'title' => 'Contact Info',
                'number' => '2'
            ),
            array(
                'title' => 'Buying/Selling',
                'number' => '3'
            ),
        );
        $this->addHTML('<div class="charity__step-selector-wrap">');
            foreach($stepArr as $step){
                $this->addHTML('<div class="charity__step-selector-item">');
                    $this->addHTML('<span class="charity__step-number charity__step-number_number-'.$step['number'].'"><span>'.$step['number'].'</span></span>');
                    $this->addHTML('<span class="charity__step-title">'.$step['title'].'</span>');
                $this->addHTML('</div>');
            }
        $this->addHTML('</div>');
    }

    public function beforeReturningView()
    {
        $this->buildView();
    }
}