<?php

abstract class AbstractFileEditFormView extends GI_View {
    
    protected $form;
    protected $file;

    public function __construct($form, File $file) {
        parent::__construct();
        $this->form = $form;
        $this->file = $file;
        $this->buildForm();
    }

    protected function buildForm() {
        $this->form->addField('new_name', 'text', array(
            'required' => true,
            'displayName' => 'New File Name',
            'placeHolder' => 'Enter new file name...',
            'value' => $this->file->getDisplayName()
        ));
        
        if($this->file->isImage()){
            $this->form->addHTML('<div class="columns halves">');
                $this->form->addField('title_tag', 'text', array(
                    'displayName' => 'HTML Title Tag',
                    'placeHolder' => 'Enter Title Tag',
                    'value' => $this->file->getProperty('title_tag'),
                    'formElementClass' => 'column'
                ));
                $this->form->addField('alt_tag', 'text', array(
                    'displayName' => 'HTML Alt Tag',
                    'placeHolder' => 'Enter Alt Tag',
                    'value' => $this->file->getProperty('alt_tag'),
                    'formElementClass' => 'column'
                ));
            $this->form->addHTML('</div>');
        }
        
        $this->form->addField('description', 'text', array(
            'displayName' => 'Short Description',
            'placeHolder' => 'Enter file description',
            'value' => $this->file->getProperty('description')
        ));
        $this->form->addContent('<div class="center_btns wrap_btns"><span class="submit_btn" tabindex="0" >Save</span></div>');
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap()
                ->addHTML('<h1>Edit File</h1>');
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
