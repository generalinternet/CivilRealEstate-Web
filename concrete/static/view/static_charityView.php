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
        // $this->addStep1();
        // $this->addStep2();
        $this->addStep3();
    }

    protected function addStep1(){
        $this->form->addHTML('<div class="charity__step charity__step-1">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>STEP 1</b> Select your Charity</h3>');
                $this->form->addHTML('<p class="charity__description">Choose the charity you would like funds from your real estate transaction to be directed to</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap charity__content-wrap_bg_secondary">');
                // $this->form->addHTML('<div class="charity__input-wrap">');
                //     $this->form->addHTML('<label class="charity__input-label" for="">Choose your Charity</label>');
                //     $this->form->addHTML('<input type="text" class="charity__input" placeholder="Start Typing the Name of your Charity">');
                // $this->form->addHTML('</div>');
                $this->form->addField('charity_name', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Start Typing the Name of your Charity',
                ));
                $this->form->addHTML('<a href="" class="charity__form-link">see all charities »</a>');
                // $this->form->addHTML('<span class="charity__checkbox-wrap">');
                //     $this->form->addHTML('<input type="checkbox" class="charity__checkbox-input">');
                //     $this->form->addHTML('<label for="" class="charity__checkbox-label">pick later</label>');
                // $this->form->addHTML('</span>');
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
                $this->form->addHTML('<a href="" class="button button_theme_primary button_has-icon">Go To Next Step <span class="button__icon"></span></a>');
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
                    $this->form->addHTML('<a href="" class="button button_theme_primary button_has-icon">Go To Next Step <span class="button__icon"></span></a>');
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
            $this->form->addField('pick_later', 'checkbox', array(
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

    public function buildView() {
        $this->addCharityBannerSection();
        $this->addCharityFormSection();
    }

    protected function addCharityBannerSection(){
        $this->addHTML('<div class="section section_type_banner banner banner_page_charity">');
        $this->addHTML('</div>');
    }

    protected function addCharityFormSection(){
        $this->addHTML('<div class="section section_type_charity charity">');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__banner">');
                            // $this->addHTML('<img src="resources/media/img/banner/charity/step_1.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-1">');
                            $this->addHTML('<img src="resources/media/img/banner/charity/step_2.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-2">');
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
                    $this->addHTML('<span class="charity__step-number">'.$step['number'].'</span>');
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