<?php

class ContentFileColFormView extends AbstractContentFileColFormView{
    public function buildFormGuts() {
        $this->addHiddenTypeRefField();
        $this->form->addHTML('<div class="auto_columns halves">');
        $this->addTitleField(array('required' => false));
        $this->addStyleFields();
        $this->form->addHTML('</div>');
        if($this->uploader){
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }
}
