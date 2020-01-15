<?php
/**
 * Description of AbstractNoteSearchFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
class AbstractNoteSearchFormView extends GI_SearchView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('note_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        
        $this->addContentsField();
        
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addContentsField(){
        $this->form->addField('search_contents', 'text', array(
            'displayName' => 'Search Notes Containing',
            'placeHolder' => 'Search',
            'value' => $this->getQueryValue('contents')
        ));
    }
    
}
