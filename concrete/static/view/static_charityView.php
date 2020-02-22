<?php

class StaticCharityView extends GI_View{
    protected $form;

    /**
     * @var CharityFormView
     */
    protected $charityForm;

    public function __construct(GI_Form $form = NULL, $attrs) {
        if(empty($form)){
            $form = new GI_Form('charity_form');
        }
        $this->form = $form;
        $this->charityForm = new CharityFormView($form);
    }

    protected $isSent = false;
    public function setSent(bool $isSent){
        $this->isSent = $isSent;
        $this->charityForm->setSent($isSent);
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
        $pieces = 'charity_step_1" data-step="1"';
        if($this->isSent){
            $pieces = 'charity_step_thankyou" data-step="thankyou"';
        }
        $this->addHTML('<div class="section section_type_charity charity '.$pieces.'>');
            $this->addHTML('<div class="container-fluid">');
                $this->addHTML('<div class="row">');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML($this->charityForm->getBannerImages());
                    $this->addHTML('</div>');
                    $this->addHTML('<div class="col-xs-12 col-md-6">');
                        $this->addHTML('<div class="charity__content">');
                            $this->addHTML($this->charityForm->getStepSelector());
                            $this->addHTML($this->charityForm->getHTMLView());
                        $this->addHTML('</div>');
                    $this->addHTML('</div>');
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    public function beforeReturningView()
    {
        $this->buildView();
    }
}