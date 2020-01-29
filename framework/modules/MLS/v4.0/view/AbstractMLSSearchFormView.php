<?php
/**
 * Description of AbstractMLSSearchFormView
 * Back-end  MLS listing search form view
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSSearchFormView extends GI_SearchView {
    
    protected $boxId = 'mls_listing_search_box';

    protected function addBasicFormFields(){
        $this->form->addHTML('<div class="basic_search_field_wrap">');
            $this->form->addField('basic_search_field', 'text', array(
                'displayName' => 'Search',
                'placeHolder' => 'Enter MLS number or address',
                'value' => $this->getQueryValue('basic_search_field')
            ));
        $this->form->addHTML('</div>');
    }
    
    protected function buildForm() {
        $this->form->addHTML('<div class="auto_columns">');
        $this->addSearchFields();
        $this->form->addHTML('</div>');
    }
    
    protected function addSearchFields(){
        $this->addCityField();
        $this->addAddressField();
        //@todo more search fields
    }
    
    protected function addAddressField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Address',
            'placeHolder' => 'enter street address',
            'value' => $this->getQueryValue('address')
        ), $overWriteSettings);
        
        $this->form->addField('search_address', 'text', $fieldSettings);
    }
    
    protected function addCityField($overWriteSettings = array()) {
        $acFieldName = 'search_city_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'mlsCity',
                    'ajax' => 1,
                    //'valueColumn' => 'id',
                    'autocompField' => $acFieldName
                        ), false, true);
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'City',
                    'placeHolder' => 'enter city name',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $this->getQueryValue('city_id')
                        ), $overWriteSettings);
        $this->form->addField($acFieldName, 'autocomplete', $fieldSettings);
    }

}
