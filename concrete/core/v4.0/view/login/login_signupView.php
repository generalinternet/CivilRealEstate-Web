<?php

class LoginSignupView extends AbstractLoginRegisterView {
    
    protected $addWrapper = true;
    protected $step = 1;
    
    public function __construct(GI_Form $form, AbstractUser $user, $step = 1, $buildForm = true) {
        $this->setStep($step);
        $this->form = $form;
        $this->user = $user;
        $this->addJS('resources/external/js/jSignature/flashcanvas.js');
        $this->addJS('resources/external/js/jSignature/jSignature.min.js');
        $this->addSiteTitle('Sign Up');
        $this->setDescription(SITE_TITLE . " - Sign Up");

        if($buildForm){
            $this->buildForm();
        }
    }
    
    public function setStep($step) {
        $this->step = $step;
        return $this;
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="view_wrap">');
        return $this;
    }
    
    public function buildView() {
        if($this->addWrapper){
            $this->openViewWrap();
        }
        $this->addMainContentSection();
        if($this->addWrapper){
            $this->closeViewWrap();
        }
    }
    
    protected function buildForm() {

        $halfFormSteps = [1, 2];

        $classes = "";
        if(in_array($this->step, $halfFormSteps)){
            $classes .= ' form__form-content_type_half-form';
        }

        $this->form->addHTML('<div class="form__form-content '.$classes.' form_content sign_up_step_'.$this->step.'">');
        // $this->form->addHTML('<div class="form_body" data-step="'.$this->step.'">');

        $formView = $this->user->getSignupFormView($this->form, $this->user);
        $formView->setRegisterForm(true);
        $formView->setStep($this->step);
        $formView->buildForm();

        $userDetail = NULL;
        if($this->step > 2){
            $userDetail = $this->user->getPrimeUserDetail();
        }

        $sideDescriptionHTML = '';

        // build form buttons
        $this->form->addHTML('<div class="form__form-buttons">');
        switch ($this->step) {
            case 1:
                $loginURL = GI_URLUtils::buildURL(array(
                        'controller' => 'login',
                        'action' => 'index',
                    ));
                $this->form->addHTML('<div class="form__top-buttons">');
                $this->form->addHTML('<span class="submit_btn button button_theme_primary button_has-icon" tabindex="0"> Gain Priority Access <span class="button__icon button__icon_color_dark"></span></span>');
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="form__bottom-buttons">');
                $this->form->addHTML('<p class="form__description form__description_has-link">Already Registered? <a href="'.$loginURL.'" class="form__description-link"> SIGN IN </a></p>');
                $this->form->addHTML('</div>');
                $sideDescriptionHTML = $this->getSideDescription();
                break;

            case 2:
                $prevBuildArr = array(
                    'controller' => 'user',
                    'action' => 'signup',
                    'step' => 1,
                    'ajax' => 0,
                );
                if(Login::isLoggedIn()){
                    $prevBuildArr['id'] = Login::getUserId();
                }
                $prevURL = GI_URLUtils::buildURL($prevBuildArr);
                $this->form->addHTML('<div class="form__top-buttons"><span class="button button_theme_primary button__has-icon submit_btn" tabindex="0"> Set Password And Next <span class="button__icon button__icon_color_dark"></span></span></div>');
                $this->form->addHTML('<div class="form__bottom-buttons"><a href="'.$prevURL.'" class="button button_theme_dark" tabindex="0" >PREV STEP</a></div>');
                $sideDescriptionHTML = $this->getSideDescription();
                break;

            case 3:
                $prevURL = GI_URLUtils::buildURL($userDetail->getPrevStepAttrs($this->step, $this->user->getId()));
                $this->form->addHTML('<div class="form__left-buttons"><a href="'.$prevURL.'" class="button button_theme_dark" tabindex="0" >PREV STEP</a></div>');
                $this->form->addHTML('<div class="form__right-buttons"><span class="submit_btn button button_theme_primary button_has-icon" tabindex="0" >COMPLETE PROFILE AND NEXT  <span class="button__icon button__icon_color_dark"></span></span></div>');
                $this->form->addHTML('</span></span>');
                break;

            case 4:
                $prevURL = GI_URLUtils::buildURL($userDetail->getPrevStepAttrs($this->step, $this->user->getId()));
                $this->form->addHTML('<div class="form__left-buttons"><a href="'.$prevURL.'" class="button button_theme_dark" tabindex="0" >'.GI_StringUtils::getIcon('arrow_left', true, 'black') .' PREV STEP</a></div>');
                $this->form->addHTML('<div class="form__right-buttons"><span class="submit_btn button button_theme_primary button_has-icon" tabindex="0" >');

                if ($userDetail->getInvestorType() == UserDetailFactory::$INVESTOR_TYPE_ACCREDITED) {
                    $this->form->addHTML(' COMPLETE AND ACCREDITATION');
                } else {
                    $this->form->addHTML(' COMPLETE AND EXIT');
                }

                $this->form->addHTML('<span class="button__icon button__icon_color_dark"></span></span></div>');
                break;

            case 5:
                $prevURL = GI_URLUtils::buildURL($userDetail->getPrevStepAttrs($this->step, $this->user->getId()));
                $this->form->addHTML('<span class="form__left-buttons"><a href="'.$prevURL.'" class="button button_theme_dark" tabindex="0" >'.GI_StringUtils::getIcon('arrow_left', true, 'black') .' PREV STEP</a></span>');
                $this->form->addHTML('<span class="form__right-buttons"><span class="button button_theme_primary button_has-icon" id="signup_form_complete_button" tabindex="0" >COMPLETE <span class="button__icon button__icon_color_dark"></span></span>');
                $this->form->addHTML('</span></span>');
                $this->form->addHTML('<span class="submit_btn hide"></span>');
            default:
        }
        $this->form->addHTML('</div> <!--.form-btns-->');
        // end build form buttons

        $this->form->addHTML('</div><!--.main_form_wrap-->'); // close .main_form_wrap (user_signupFormView)

        $this->form->addHTML($sideDescriptionHTML);
        
        // $this->form->addHTML('</div><!--.form_body-->'); // close form_body
        $this->form->addHTML('</div><!--.form_content-->'); // close form_content
    }
    
    public function addMainContentSection() {
        if($this->addWrapper){
            $this->addHTML('<section class="section section_type_sign-up banner banner_size_normal banner_page_home">');
            $this->addHTML('<div class="container-fluid">');
            $this->addHTML('<div class="row">');
            $this->addHTML('<div class="col-xs-12 col-lg-8 col-lg-offset-1 form-col">');
            
            $this->addHTML('<h1 class="section__title section__title_color_white">Instant Access to <br>Pre-Vetted Opportunities</h1>');
        }

        $urlAttrs = array(
            'controller' => 'user',
            'action' => 'signup',
            'step' => $this->step,
            'ajax' => 1,
        );
        if (!empty($this->user->getId())) {
            $urlAttrs['id'] = $this->user->getId();
        }
        $registerURL = GI_URLUtils::buildURL($urlAttrs, true, true);
    
        $this->addHTML('<div id="step_form_wrap" class="ajaxed_contents form form_type_sign-up" data-url="'.$registerURL.'">');
        $this->addHTML($this->form->getForm());
        $this->addHTML('</div><!--#form_wrap-->');

        if($this->addWrapper){
            $this->addHTML('</div><!--.col-->');
            $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
            $this->addHTML('</section>');
        }                        
    }

    protected function getSideDescription(){
        $title = SITE_NAME . " - Sign up";
        $text = "The ".SITE_NAME." Team is comprised of experienced business professionals coming from finance, venture capital, enterprise, and real estate backgrounds.";
        $author = SITE_NAME." Team";

        // $subTitle = "INVESTOR SINCE 2015";
        $icon = "resources/media/img/icons/quote.png";
        // $logos = "resources/media/img/logos/quote_footer_logo.png";

        $html = '
            <div class="form__quote quote quote_type_form-quote quote_theme_primary">
                <img src="'.$icon.'" alt="'.$title.'" class="quote__icon-img">
                <div class="quote__content-wrap">
                    <p class="quote__text">'.$text.'</p>
                    <p class="quote__sub-title">- '.$author.'</p>
                </div>
            </div>
        ';
        // <p class="quote__sub-title">'.$subTitle.'</p>
        // <img src="'.$logos.'" alt="'.$title.'" class="quote__logo-img">
        return $html;
    }
}
