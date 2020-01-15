<?php
/**
 * Description of AbstractPermissionSearchFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
abstract class AbstractPermissionSearchFormView extends GI_SearchView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('permission_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="auto_columns halves">');
        $this->addTitleField();
        $this->addCategoryField();
        $this->form->addHTML('</div>');
    }
    
    protected function addTitleField(){
        $this->form->addField('search_term', 'text', array(
            'displayName' => 'Search by Title/Ref',
            'placeHolder' => 'Title or reference',
            'value' => $this->getQueryValue('term')
        ));
    }
    
    protected function addCategoryField(){
        $options = PermissionCategoryFactory::getOptionsArray();
        $this->form->addField('search_category_id', 'dropdown', array(
            'displayName' => 'Search by Category',
            'value' => $this->getQueryValue('search_category_id'),
            'options' => $options
        ));
    }
    
}
