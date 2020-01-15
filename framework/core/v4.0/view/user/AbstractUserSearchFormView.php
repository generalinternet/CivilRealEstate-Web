<?php
/**
 * Description of AbstractUserSearchFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
abstract class AbstractUserSearchFormView extends GI_SearchView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('user_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="columns thirds">');
            $this->addNameField();
            $this->addEmailField();
            $this->addRoleField();
        $this->form->addHTML('</div>');
    }
    
    protected function addNameField(){
        $this->form->addField('search_name', 'text', array(
            'displayName' => 'Search by Name',
            'placeHolder' => 'First or last name',
            'formElementClass' => 'column',
            'value' => $this->getQueryValue('name')
        ));
    }
    
    protected function addEmailField(){
        $this->form->addField('search_email', 'text', array(
            'displayName' => 'Search by Email',
            'placeHolder' => 'Email address',
            'formElementClass' => 'column',
            'value' => $this->getQueryValue('email')
        ));
    }
    
    protected function addRoleField(){
        $roleOptions = Role::buildRoleOptions();
        if (count($roleOptions) > 1) {
            $this->form->addField('search_role_id', 'dropdown', array(
                'options' => $roleOptions,
                'formElementClass' => 'column',
                'value' => $this->getQueryValue('role_id'),
                'displayName' => 'Search by Role'
            ));
        }
    }
    
}
