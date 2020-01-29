<?php
/**
 * Description of AbstractContactInfoAddressFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
abstract class AbstractContactInfoAddressFormView extends AbstractContactInfoFormView {

    protected $fieldset = false;
    protected $fieldsetTitle = 'Address';
    protected $fieldsetClass = '';
    protected $fieldsetAdvanced = false;
    /** @var AbstractContactInfoAddress */
    protected $contactInfo;
    protected $requiredFields = array(
        'addr_country' => true,
        'addr_region' => true
    );
    
    public function __construct(GI_Form $form, AbstractContactInfo $contactInfo) {
        parent::__construct($form, $contactInfo);
        $this->formTitle = 'Edit Address';
        $typeTitle = $contactInfo->getTypeTitle();
        if (!empty($typeTitle)) {
            $this->setFieldsetTitle($typeTitle);
        }
    }
    
    public function setFieldset($fieldset = true, $title = NULL, $advanced = false){
        if (empty($title)) {
            $title = $this->contactInfo->getTypeTitle();
        }
        $this->fieldset = $fieldset;
        $this->setFieldsetTitle($title);
        $this->fieldsetAdvanced = $advanced;
        return $this;
    }
    
    public function setFieldsetTitle($fieldsetTitle){
        $this->fieldsetTitle = $fieldsetTitle;
        return $this;
    }
    
    public function setFieldsetClass($fieldsetClass){
        $this->fieldsetClass = $fieldsetClass;
        return $this;
    }
    
    /**
     * @param string $coreFieldName
     * @param boolean $required
     */
    public function setFieldRequired($coreFieldName, $required = true) {
        $this->requiredFields[$coreFieldName] = $required;
    }

    protected function addStreetField($overWriteSettings = array()) {
        $placeHolder = 'Street';
        $required = false;
        if (isset($this->requiredFields['addr_street'])) {
            $required = $this->requiredFields['addr_street'];
        }
        if($required){
            $placeHolder .= '*';
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Street',
            'placeHolder' => $placeHolder,
            'value' => $this->contactInfo->getAddrProperty('addr_street'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
            'fieldClass' => '',
            'required'=>$required,
        ), $overWriteSettings);
        
        $fieldSettings['fieldClass'] .= ' addr_street';
        
        $this->form->addField($this->getFieldName('addr_street'), 'text', $fieldSettings);
    }

    protected function addStreet2Field($overWriteSettings = array()) {
        $placeHolder = 'Street Line 2';
        $required = false;
        if (isset($this->requiredFields['addr_street_two'])) {
            $required = $this->requiredFields['addr_street_two'];
        }
        if($required){
            $placeHolder .= '*';
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Street Line 2',
            'placeHolder' => $placeHolder,
            'value' => $this->contactInfo->getAddrProperty('addr_street_two'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
            'fieldClass' => '',
            'required' => $required,
        ), $overWriteSettings);
        
        $fieldSettings['fieldClass'] .= ' addr_street_two';
        
        $this->form->addField($this->getFieldName('addr_street_two'), 'text', $fieldSettings);
    }

    protected function addCityField($overWriteSettings = array()) {
        $placeHolder = 'City';
        $required = false;
        if (isset($this->requiredFields['addr_city'])) {
            $required = $this->requiredFields['addr_city'];
        }
        if($required){
            $placeHolder .= '*';
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'City',
            'placeHolder' => $placeHolder,
            'value' => $this->contactInfo->getAddrProperty('addr_city'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
            'fieldClass' => '',
            'required' => $required,
        ), $overWriteSettings);
        
        $fieldSettings['fieldClass'] .= ' addr_city';
        
        $this->form->addField($this->getFieldName('addr_city'), 'text', $fieldSettings);
    }

    protected function addRegionField($overWriteSettings = array(), $overWriteCustomFieldSettings = array()) {
        $placeHolder = 'Province/State';
        $countryCode = $this->contactInfo->getAddrProperty('addr_country');
        $regionCode = $this->contactInfo->getAddrProperty('addr_region');
        $required = true;
        if (isset($this->requiredFields['addr_region'])) {
            $required = $this->requiredFields['addr_region'];
        }
        if($required){
            $placeHolder .= '*';
        }
        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Province/State',
            'nullText' => $placeHolder,
            'optionGroups' => $this->contactInfo->getRegionGroupArray(),
            'value' => $countryCode . '_' . $regionCode,
//            'required' => $required,
            'fieldClass' => 'field_addr_region',
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
        ), $overWriteSettings);
        
        $fieldSettings['fieldClass'] .= ' addr_region';
        $wrapClass = '';
        if($this->form->wasSubmitted()){
            $countryCode = filter_input(INPUT_POST, $this->getFieldName('addr_country'));
        }
        if(!GeoDefinitions::forceRegionForCountryCode($countryCode) && !empty($countryCode)){
            $wrapClass = 'custom_entry';
        }
        
        $this->form->addHTML('<div class="addr_region_wrap form_element ' . $wrapClass . '">');
        $this->form->addField($this->getFieldName('addr_region'), 'dropdown', $fieldSettings);
        
        $customFieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Province/State',
            'placeHolder' => 'Province/State',
            'value' => $this->contactInfo->getAddrProperty('addr_region'),
            'fieldClass' => 'field_custom_addr_region',
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
        ), $overWriteCustomFieldSettings);
        
        $this->form->addField($this->getFieldName('custom_addr_region'), 'text', $customFieldSettings);
        $this->form->addHTML('</div>');
    }

    protected function addCountryField($overWriteSettings = array()) {
        $placeHolder = 'Country';
        $required = false;
        $hideNull = false;
        if (isset($this->requiredFields['addr_country'])) {
            $required = $this->requiredFields['addr_country'];
        }
        if($required){
            $placeHolder .= '*';
            $hideNull = true;
        }
        
        $countryCode = $this->contactInfo->getAddrProperty('addr_country');
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Country',
            'nullText' => $placeHolder,
            'options' => GeoDefinitions::getCountryOptions(true),
            'optionData' => GeoDefinitions::getCountryOptionData(),
            'htmlOptions' => true,
            'value' => $countryCode,
            'hideNull' => $hideNull,
            'fieldClass' => 'field_addr_country country_select',
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
            'required'=>$required,
        ), $overWriteSettings);
        
        $fieldSettings['fieldClass'] .= ' addr_country';
        
        $this->form->addField($this->getFieldName('addr_country'), 'dropdown', $fieldSettings);
    }

    protected function addCodeField($overWriteSettings = array()) {
        $placeHolder = 'Postal/Zip Code';
        $required = false;
        if (isset($this->requiredFields['addr_code'])) {
            $required = $this->requiredFields['addr_code'];
        }
        if($required){
            $placeHolder .= '*';
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Postal/Zip Code',
            'placeHolder' => $placeHolder,
            'value' => $this->contactInfo->getAddrProperty('addr_code'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
            'fieldClass' => '',
            'required' => $required,
        ), $overWriteSettings);

        $fieldSettings['fieldClass'] .= ' addr_code';

        $this->form->addField($this->getFieldName('addr_code'), 'text', $fieldSettings);
    }
    
    protected function addTypeField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Address Type',
            'hideNull' => true,
            'options' => $this->contactInfo->getTypesArray(),
            'value' => $this->contactInfo->getTypeRef()
        ),$overWriteSettings);
        
        $this->form->addField($this->getFieldName('type_ref'), 'dropdown', $fieldSettings);
    }
    
    public function buildContactInfoFields() {
        if($this->fieldset){
            if($this->fieldsetAdvanced){
                $this->form->addHTML('<fieldset class="advanced">');
                $this->form->addHTML('<legend class="advanced_btn custom_btn"><span class="btn_text">' . $this->fieldsetTitle . '</span>'.GI_StringUtils::getIcon('add').'</legend>');

                $this->form->addHTML('<div class="advanced_content">');
            } else {
                $this->form->startFieldset($this->fieldsetTitle, array(
                    'class' => $this->fieldsetClass
                ));
            }
        }
        
        if($this->addAddrElementWrap){
            $this->form->addHTML('<div class="addr_element">');
        }
        
        if (!$this->hideTypeField) {
            $this->form->addHTML('<div class="addr_row">');
            $this->addTypeField();
            $this->form->addHTML('</div>');
        }
        
        $this->form->addHTML('<div class="addr_row">');
        $this->addStreetField();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="addr_row">');
        $this->addStreet2Field();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="addr_row halves">');
        $this->addCityField();
        $this->addRegionField();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="addr_row halves">');
        $this->addCountryField();
        $this->addCodeField();
        $this->form->addHTML('</div>');
        
        if($this->addAddrElementWrap){
            $this->form->addHTML('</div>');
        }
        
        if($this->fieldset){
            if($this->fieldsetAdvanced){
                $this->form->addHTML('</div>');
                $this->form->addHTML('</fieldset>');
            } else {
                $this->form->endFieldset();
            }
        }
        
        return NULL;
        
        //$this->form->addHTML('<div class="columns halves top_align">');
            //$this->form->addHTML('<div class="column">');
                
            //$this->form->addHTML('</div>');
            //$this->form->addHTML('<div class="column">');
                
            //$this->form->addHTML('</div>');
        //$this->form->addHTML('</div>');
        
        //$this->form->addHTML('<div class="columns thirds">');
            //$this->form->addHTML('<div class="column two_thirds">');
                
            //$this->form->addHTML('</div>');
            //$this->form->addHTML('<div class="column">');
                
            //$this->form->addHTML('</div>');
        //$this->form->addHTML('</div>');
        
    }

}
