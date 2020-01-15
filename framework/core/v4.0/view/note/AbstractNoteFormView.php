<?php

abstract class AbstractNoteFormView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /** @var Note */
    protected $note;
    protected $formAction = 'Add';
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    protected $addFormHeader = true;
    
    public function __construct(GI_Form $form, AbstractNote $note) {
        parent::__construct();
        $this->form = $form;
        $this->note = $note;
        $this->addSiteTitle('Notes');
        $typeTitle = $this->note->getViewTitle();
        $this->addSiteTitle($typeTitle);
        if(!is_null($note->getId())){
            $this->addSiteTitle($this->note->getTitle());
            $this->formAction = 'Edit';
        }
    }
    
    /**
     * @param GI_Uploader $uploader
     * @return \AbstractNoteFormView
     */
    public function setUploader(AbstractGI_Uploader $uploader){
        $this->uploader = $uploader;
        return $this;
    }
    
    /**
     * @param boolean $addFormHeader
     * @return \AbstractNoteFormView
     */
    public function setAddFormHeader($addFormHeader){
        $this->addFormHeader = $addFormHeader;
        return $this;
    }
    
    protected function addFormHeader(){
        if ($this->addFormHeader) {
            $typeTitle = 'Note';
            $this->form->addContent('<h1>' . $this->formAction . ' ' . $typeTitle . '</h1>');
        }
    }
    
    public function buildForm() {
        $this->addFormHeader();
        
        $this->form->addHTML('<div class="note_form_wrap">');
        $this->addNoteField();
        $this->form->addHTML('<div class="button_wrap">');
        $this->addUploaderBtn();
        $this->addSubmitBtn();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="note_uploader">');
            $this->form->addHTML('<div class="columns thirds">')
                    ->addHTML('<div class="column">');
            $this->addNotifyUsersField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column two_thirds">');
            $this->addUploader();
            $this->form->addHTML('</div>')
                    ->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    protected function addNoteField(){
        $fieldSettings = array(
            'value' => $this->note->getProperty('note'),
            'wygBtnHTML' => false,
            'wygBtnLink' => false,
            'wygBtnRule' => false,
            'wygBtnCode' => false,
            'wygBtnTable' => false,
            'wygBtnFormat' => false,
            'wygBtnUnformat' => false,
            'wygBtnUndo' => false,
            'wygBtnFullscreen' => false,
            'formElementClass'=>'note_text',
            'showLabel'=>false,
        );
        $this->form->addField('note', 'wysiwyg', $fieldSettings);
    }

    protected function addUploader() {
        if ($this->uploader) {
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }

    protected function addNotifyUsersField() {
        $userURL = GI_URLUtils::buildURL(array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1
        ));
        $this->form->addField('users_to_notify', 'autocomplete', array(
            'displayName' => 'Users to Notify',
            'placeHolder' => 'start typing...',
            'autocompURL' => $userURL,
            'autocompMultiple' => true
        ));
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addSubmitBtn(){
        $this->form->addContent('<span class="submit_btn" title="Save"><span class="icon_wrap"><span class="icon primary post"></span></span></span>');
    }
    
    protected function addUploaderBtn() {
        $this->form->addHTML('<div class="wrap_btns">');
        $this->form->addHTML('<span class="browse_computer"><span class="icon_wrap"><span class="icon paperclip"></span></span></span>');
        $this->form->addHTML('</div>');
    }

    public function buildView() {
        $this->openViewWrap();
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
