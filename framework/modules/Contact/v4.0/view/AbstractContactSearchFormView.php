<?php
/**
 * Description of AbstractContactSearchFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactSearchFormView extends GI_SearchView {
    
    public function __construct(\GI_Form $form, $queryValues = array()) {
        $this->setBoxId('contact_search_box');
        parent::__construct($form, $queryValues);
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="auto_columns">');
        
            $this->addNameField();
            
            $this->addAddressField();
            
            $this->addEmailField();
            
            $this->addPhoneField();
        
            $this->addContactTagsField();
        
        $this->form->addHTML('</div>');
    }
    
    protected function addNameField(){
        $this->form->addField('search_name', 'text', array(
            'displayName' => 'Search by Name',
            'placeHolder' => 'Name',
            'value' => $this->getQueryValue('name')
        ));
    }
    
    protected function addAddressField(){
        $this->form->addField('search_address', 'text', array(
            'displayName' => 'Search by Address',
            'placeHolder' => 'Street, city, or postal code',
            'value' => $this->getQueryValue('address')
        ));
    }
    
    protected function addEmailField(){
        $this->form->addField('search_email', 'text', array(
            'displayName' => 'Search by Email',
            'placeHolder' => 'Email Address',
            'value' => $this->getQueryValue('email')
        ));
    }
    
    protected function addPhoneField(){
        $this->form->addField('search_phone', 'text', array(
            'displayName' => 'Search by Phone',
            'placeHolder' => 'Phone Number',
            'value' => $this->getQueryValue('phone')
        ));
    }
    
    protected function addContactTagsField(){
        $searchContactCatType = $this->getQueryValue('contact_cat_type');
        if (!empty($searchContactCatType)) {
            $options = TagFactory::getTagOptionsArrayByTypeRef($searchContactCatType);
            $values = $this->getQueryValue('tags');
            if (!empty($options)) {
                $this->form->addField('search_tags', 'checkbox', array(
                    'displayName' => 'Search by Tags',
                    'options' => $options,
                    'value' => $values,
                    'formElementClass' => 'full_width_element',
                ));
            }
        }
    }
    
}
