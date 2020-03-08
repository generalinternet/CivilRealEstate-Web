<?php

class CharityFormView extends GI_View{

    protected $form = NULL;

    public function __construct(GI_Form $form = NULL, $isSent)
    {
        parent::__construct();
        if(empty($form)){
            $form = new GI_Form('charity_form');
        }
        $this->form = $form;
        $charityURL = GI_URLUtils::buildCleanURL(array(
            'controller' => 'static',
            'action' => 'charity'
        ));
        $this->form->setFormAction($charityURL);
        $this->setSent($isSent);

        $this->buildForm();
    }

    protected $isSent = false;
    public function setSent(bool $isSent){
        $this->isSent = $isSent;
    }

    protected function buildForm(){
        if($this->isSent){
            return $this->addThankyou();
        }
        $this->addStep1();
        $this->addStep2();
        $this->addStep3();
    }

    protected function addStep1(){
        $this->form->addHTML('<div class="charity__step charity__step-1" data-step="1">');
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
                $otherArr = array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Start Typing the Name of your Charity',
                    'autocompURL' => $autoCompURL,
                    'autocompMinLength' => 1,
                    'autocompMultiple' => false
                );
                $this->form->addField('charity_id', 'autocomplete', $otherArr);
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
        $this->form->addHTML('<div class="charity__step charity__step-2" data-step="2">');
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
                    'placeHolder' => 'Email *',
                    'required' => true
                ));
                $this->form->addField('phone', 'text', array(
                    'class' => 'form__input form__input_type_text',
                    'placeHolder' => 'Phone Number'
                ));
                $this->form->addHTML('<div class="charity__contact-form-buttons">');
                    $this->form->addHTML('<a href="" class="button button_theme_primary button_has-icon charity__button_next-step">Go To Next Step <span class="button__icon"></span></a>');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addStep3(){
        $this->form->addHTML('<div class="charity__step charity__step-3" data-step="3">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>STEP 3</b> Buying or Selling?</h3>');
                $this->form->addHTML('<p class="charity__description">Are you interested in finding or listing a home?</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="charity__content-wrap charity__buy-or-sell">');
            $this->form->addField('buy_or_sell', 'radio', array(
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
        $this->form->addHTML('<div class="charity__step charity__step-thankyou data-step="thank-you">');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<h3 class="charity__title"><b>THANK YOU</b> For Making a Difference!</h3>');
                $this->form->addHTML('<p class="charity__description">A Civil Real Estate Local Expert will be in contact with you promtly.</p>');
            $this->form->addHTML('</div>');
            $this->form->addHTML('<br>');
            $this->form->addHTML('<br>');
            $this->form->addHTML('<div class="charity__content-wrap">');
                $this->form->addHTML('<div class="charity__content-buttons">');
                    $indexURL = GI_URLUtils::buildCleanURL(array(
                        'controller' => 'static',
                        'action' => 'index',
                    ));
                    $this->form->addHTML('<a href="'.$indexURL.'" class="button button_theme_primary button_has-icon">DONE <span class="button__icon button__icon_type_check"></span></a>');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    public function getBannerImages(){
        return '<div class="charity__banner">'.
            '<img src="resources/media/img/banner/charity/step_1.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-1">'.
            '<img src="resources/media/img/banner/charity/step_2.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-2">'.
            '<img src="resources/media/img/banner/charity/step_3.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-3">'.
            '<img src="resources/media/img/banner/charity/step_4.png" alt="'.SITE_TITLE.'" class="charity__banner-img charity__banner-img_step-4">'.
        '</div>';
    }


    public function getStepSelector(){
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
        $html = '<div class="charity__step-selector">';
        $html .= '<div class="charity__step-selector-wrap">';
            foreach($stepArr as $step){
                $html .= '<div class="charity__step-selector-item">';
                    $html .= '<span class="charity__step-number charity__step-number_number-'.$step['number'].'"><span>'.$step['number'].'</span></span>';
                    $html .= '<span class="charity__step-title">'.$step['title'].'</span>';
                $html .= '</div>';
            }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    public function beforeReturningView()
    {
        $this->addHTML($this->form->getForm());
    }
}