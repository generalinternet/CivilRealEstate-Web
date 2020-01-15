<?php

class StaticContactView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form = NULL;
    protected $sent = false;

    public function __construct(GI_Form $form = NULL) {
        parent::__construct();        
        if(!is_null($form)){
            $this->form = $form;
            $this->buildForm();
        }
        $this->addSiteTitle('Contact');
    }
    
    public function setSent($sent){
        $this->sent = $sent;
        return $this;
    }
    
    protected function buildForm(){
        $form = $this->form;

        $form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">1</span> Tell us about yourself</h2>');

        $form->addHTML('<div class="columns halves">');
            $form->addField('first_name', 'text', array(
                'class' => 'form__input',
                'displayName' => 'First Name',
                'placeHolder' => 'Enter your first name...',
                'required' => true,
                'formElementClass' => 'column'
            ));
            $form->addField('last_name', 'text', array(
                'class' => 'form__input',
                'displayName' => 'Last Name',
                'placeHolder' => 'Enter your last name...',
                'formElementClass' => 'column'
            ));
        $form->addHTML('</div>');

        $form->addHTML('<div class="columns halves">');
            $form->addField('r_email', 'email', array(
                'class' => 'form__input',
                'displayName' => 'Email Address',
                'placeHolder' => 'Enter your email...',
                'required' => true,
                'formElementClass' => 'column'
            ));
            $form->addField('phone', 'phone', array(
                'class' => 'form__input',
                'displayName' => 'Phone Number',
                'placeHolder' => 'Enter your phone number...',
                'formElementClass' => 'column'
            ));
        $form->addHTML('</div>');

        // $form->addField('is_accredited', 'radio', array(
        //     'class' => 'form__input form__input_type_radio',
        //     'options' => array(
        //         'yes' => 'YES',
        //         'no' => 'NO',
        //         'not_sure' => 'NOT SURE',
        //     ),
        //     'value' => 'editable',
        //     'displayName' => 'Are you an accredited investor?'
        // ));

        $form->addHTML('<br><br>');
        $form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">2</span> What is your message?</h2>');
        $form->addField('message', 'textarea', array(
            'class' => 'form__input form__input_type_textarea',
            'displayName' => 'Message',
            'placeHolder' => 'Enter your message...',
            'required' => true
        ));

        $form->addHTML('<div class="text-center">');
            $form->addHTML('<span class="button button_theme_primary button_has-icon submit_btn" title="Submit">SUBMIT FORM <span class="button__icon button__icon_color_dark"></span></span>');
        $form->addHTML('</div>');
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
        $this->addMainContentSection();
    }
    
    public function addHeaderBannerSection() {
        $title = "Here’s <br>How to Contact Us …";

        $this->addHTML('<section id="header-banner-section" class="section_type_banner banner banner_size_normal banner_page_contact">');            
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="banner__content-wrap">');
                        $this->addHTML('<div class="col-xs-12 col-lg-6 left-col">');
                            $this->addHTML('<h1 class="banner__title banner__title_color_primary">'.$title.'</h1>');
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }
    
    public function addMainContentSection() {
        $this->addHTML('<section class="section section_type_contact-form">');
            $this->addHTML('<div class="container">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-sm-4">');
                        $this->addHTML('<div class="about-info">');
                            $this->addHTML('<div class="about-info__block">');
                                $this->addHTML('<h3 class="about-info__title">Email</h3>');
                                $this->addHTML('<p class="about-info__content">'.PublicLayoutView::getEmailLink().'</p>');
                            $this->addHTML('</div>');
                            // $this->addHTML('<div class="about-info__block">');
                            //     $this->addHTML('<h3 class="about-info__title">Phone</h3>');
                            //     $this->addHTML('<p class="about-info__content"><a href="tel:'.SITE_PHONE.'">'.SITE_PHONE.'</a></p>');
                            // $this->addHTML('</div>');
                            // $this->addHTML('<div class="about-info__block">');
                            //     $this->addHTML('<h3 class="about-info__title">Address</h3>');
                            //     $siteAddress = implode('<br>', [SITE_ADDR_STREET, SITE_ADDR_CITY, SITE_ADDR_REGION, SITE_ADDR_CODE]);
                            //     $this->addHTML('<p class="about-info__content">'.$siteAddress.'</p>');
                            // $this->addHTML('</div>');
                        $this->addHTML('</div>');
                    $this->addHTML("</div>");
                    $this->addHTML('<div class="col-xs-12 col-sm-8">');
                        $this->addHTML('<div class="form form_type_contact-form">');
                            $this->addContactForm();
                        $this->addHTML("</div>");
                    $this->addHTML("</div>");
                $this->addHTML('</div><!--.row-->');
            $this->addHTML('</div><!--.container-->'); 
        $this->addHTML('</section>');
    }

    protected function addContactForm(){
        if($this->sent){
            $this->addParagraph('<h3 class="content_block_title">Thank you, your message has been sent.</h3>');
            $contactURL = GI_URLUtils::buildURL(array(
                'controller' => 'static',
                'action' => 'contact'
            ));
            $this->addHTML('<a href="' . $contactURL . '" title="Send Another Message" class="other_btn">Send Another Message</a>');
        } else {
            if(!is_null($this->form)){
                $formHTML = $this->form->getForm();        
                $this->addHTML($formHTML);
            }
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
