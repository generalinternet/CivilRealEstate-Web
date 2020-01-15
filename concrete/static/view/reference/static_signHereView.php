<?php

class StaticSignHereView extends GI_View {
    
    /** @var GI_Form */
    protected $form = NULL;
    protected $saved = false;

    public function __construct(GI_Form $form = NULL) {
        $this->addJS('resources/external/js/jSignature/flashcanvas.js');
        $this->addJS('resources/external/js/jSignature/jSignature.min.js');
        parent::__construct(); 
        $this->form = $form;
        $this->addSiteTitle('Sign Here');
    }
    
    public function setSent($sent){
        $this->saved = $sent;
        return $this;
    }
    
    public function buildForm(){
        $form = $this->form;
        $signatureField = 'signature';
        $user = Login::getUser();
        $sigFile = NULL;
        if($user){
            $sigFile = FileFactory::getSignatureFromModel($signatureField, $user);
        }
        $form->addField($signatureField, 'signature', array(
            'displayName' => 'Signature',
//            'required' => true,
            'signatureFile' => $sigFile
        ));
        
        $form->addField('confirm', 'onoff', array(
            'displayName' => 'Confirm Change',
            'required' => true,
        ));
        
        $form->addHTML('<span class="submit_btn" title="Submit">Submit</span>');
    }
    
    protected function buildView() {
        $this->addHTML('<div class="content_padding">');
        $this->addHTML('<h1>Sign Here</h1>');
        if($this->saved){
            $this->addHTML('<p>Signed.</p>');
        } else {
            if(!is_null($this->form)){
                $formHTML = $this->form->getForm();        
                $this->addHTML($formHTML);
            }
        }
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
