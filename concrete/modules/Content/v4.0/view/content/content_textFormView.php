<?php

class ContentTextFormView extends AbstractContentTextFormView{    
    public function buildFormGuts() {
        $this->addHiddenTypeRefField();
        $this->form->addHTML('<div class="auto_columns halves">');

        $title =  $this->content->getProperty('title');
        if(empty($title)){
            $title = '';
        }
        $this->addTitleField(array(
            'required' => false,
            'value' => $title,
        ));

        $this->form->addHTML('</div>');
        if($this->uploader){
            $this->form->addHTML($this->uploader->getHTMLView());
        }
        $this->form->addField($this->content->getFieldName('content'), 'textarea', array(
            'displayName' => 'Content',
            'placeHolder' => 'Content',
            'value' => $this->content->getProperty('content_text.content')
        ));
    }
}
