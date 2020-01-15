<?php

abstract class AbstractTagListFormView extends GI_View {
    
    protected $allTags;
    protected $existingTags;
    protected $form;
    protected $listTitle = 'Tags';


    public function __construct(GI_Form $form, $allTags, $existingTags) {
        parent::__construct();
        $this->form = $form;
        $this->allTags = $allTags;
        $this->existingTags = $existingTags;
    }
    
    public function setListTitle($listTitle) {
        $this->listTitle = $listTitle;
        return $this;
    }
    
    public function buildForm() {
        if (!empty($this->allTags)) {
           $this->form->startFieldset($this->listTitle);
           $this->form->addHTML('<div class="inline_form_elements">');
            foreach ($this->allTags as $tag) {
                $tagTitle = $tag->getProperty('title');
                $tagId = $tag->getProperty('id');
                $value = 0;
                if (isset($this->existingTags[$tagId])) {
                    $value = 1;
                }
                $this->form->addField('tag_' . $tagId, 'onoff', array(
                    'required'=>false,
                    'displayName'=>$tagTitle,
                    'value'=>$value,
                    'formElementClass'=>'inline_label'
                ));
            }
            $this->form->addHTML('</div>');
            $this->form->endFieldset();
        }
    }
    
    public function buildView() {
        $this->buildForm();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
