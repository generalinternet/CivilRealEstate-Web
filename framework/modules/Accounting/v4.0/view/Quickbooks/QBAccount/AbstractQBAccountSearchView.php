<?php

abstract class AbstractQBAccountSearchView extends GI_SearchView{

    protected $boxId = 'qb_account_search_box';
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId($this->boxId);
        $this->setUseBasicSearch(false);
        $this->addAdvancedBlockClass('basic_width');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->addNameField();
    }

    protected function addNameField() {
        $this->form->addField($this->getSearchFieldName('search_account_name'), 'text', array(
            'displayName' => 'Search by Account Name',
            'placeHolder' => 'Name',
            'value' => $this->getQueryValue('account_name')
        ));
    }

}
