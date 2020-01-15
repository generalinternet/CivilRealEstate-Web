<?php
/**
 * Description of AbstractContentSearchFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
class AbstractContentSearchFormView extends GI_SearchView{
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('content_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="auto_columns halves">');
        
            $this->addSearchFields();
        
        $this->form->addHTML('</div>');
    }
    
    protected function addSearchFields(){
        $this->addTitleField();
        $this->addDateFields();
    }
    
    protected function addTitleField(){
        $this->form->addField('search_title', 'text', array(
            'displayName' => 'Search by Title/Ref',
            'placeHolder' => 'Title/Ref',
            'value' => $this->getQueryValue('title')
        ));
    }
    
    protected function addDateFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addStartDateField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addEndDateField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addStartDateField() {
        $this->form->addField('search_start_date', 'date', array(
            'displayName' => 'Created Between',
            'placeHolder' => 'Start Date',
            'value' => $this->getQueryValue('start_date')
        ));
    }

    protected function addEndDateField() {
        $this->form->addField('search_end_date', 'date', array(
            'displayName' => 'And',
            'placeHolder' => 'End Date',
            'value' => $this->getQueryValue('end_date'),
            'minDateFromField' => 'search_start_date'
        ));
    }
    
}
