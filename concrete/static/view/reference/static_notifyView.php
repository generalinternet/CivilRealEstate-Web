<?php

class StaticNotifyView extends GI_View {
    
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
        $this->addSiteTitle('Notify');
    }
    
    public function setSent($sent){
        $this->sent = $sent;
        return $this;
    }
    
    protected function buildForm(){
        $form = $this->form;
        $form->addHTML('<div class="columns halves">');
        $form->addHTML('<div class="column">');
        $userURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1
        ));
        $this->form->addField('user_ids', 'autocomplete', array(
            'displayName' => 'Notify User',
            'placeHolder' => 'Start typing user name...',
            'autocompURL' => $userURL,
            'autocompMultiple' => true,
            'autocompMinLength' => 0,
            'required' => true
        ));
        $this->form->addField('subject', 'text', array(
            'displayName' => 'Subject',
            'placeHolder' => 'Notification subject',
            'required' => true
        ));
        $form->addHTML('</div>');
        $form->addHTML('</div>');
        $this->form->addField('message', 'wysiwyg', array(
            'displayName' => 'Message',
            'placeHolder' => 'Notification message',
            'required' => true
        ));
        $form->addHTML('<span class="submit_btn">Submit</span>');
    }
    
    protected function buildView() {
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_header">');
            $this->addMainTitle('Notify');
            $this->addHTML('</div>');
            $this->addHTML('<div class="view_body">');
                if($this->sent){
                    $this->addHTML('<p>Your notification has been sent.</p>');
                } else {
                    if(!is_null($this->form)){
                        $formHTML = $this->form->getForm();        
                        $this->addHTML($formHTML);
                    } else {
                        $this->addHTML('<p>You must be logged in to send notifications.</p>');
                    }
                }
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
