<?php

abstract class AbstractContactQBSearchView extends GI_SearchView{
    
    public $type = '';
    protected $boxId = 'contact_qb_search_box';
    
    public function __construct(\GI_Form $form, $queryValues = array(), $type = 'supplier') {
        $this->type = $type;
        $this->setBoxId($this->boxId);
        $this->setUseBasicSearch(false);
        $this->addAdvancedBlockClass('basic_width');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->addNameField();
    }

    protected function addNameField() {
        $this->form->addField($this->getSearchFieldName('search_contact_qb_name'), 'text', array(
            'displayName' => 'Search',
            'placeHolder' => 'Search terms',
            'description' => 'Enter display name, company, or name',
            'value' => $this->getQueryValue('contact_qb_name')
        ));
    }

}
