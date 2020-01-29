<?php
/**
 * Description of AbstractContactInfoPhoneNumFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactInfoPhoneNumFormView extends AbstractContactInfoFormView {
    
    /** @var AbstractContactInfoPhoneNum */
    protected $contactInfo;

    public function __construct(GI_Form $form, AbstractContactInfo $contactInfo) {
        parent::__construct($form, $contactInfo);
        $this->formTitle = 'Edit Phone Number';
    }
    
    protected function addPhoneField(){
        $typeTitle = $this->contactInfo->getTypeTitle();
        $this->form->addField($this->getFieldName('phone_num'), 'phone', array(
            'displayName' => $typeTitle,
            'placeHolder' => 'ex. 604-123-1234',
            'value' => $this->contactInfo->getProperty('contact_info_phone_num.phone'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
        ));
    }
    
    protected function addTypeField(){
        if(!$this->hideTypeField){
            $this->form->addField($this->getFieldName('type_ref'), 'dropdown', array(
                'displayName' => 'Phone Number Type',
                'hideNull' => true,
                'options' => $this->contactInfo->getTypesArray(),
                'value' => $this->contactInfo->getTypeRef(),
                'readOnly' => $this->readOnly,
                'disabled' => $this->disabled,
                'clearValue' => $this->clearValue,
                'autoComplete' => $this->autoComplete,
            ));
        }
    }
    
    public function buildContactInfoFields(){
        if($this->addAddrElementWrap){
            $this->form->addHTML('<div class="addr_element">');
        }
            $this->form->addHTML('<div class="addr_row halves">');
            $this->addTypeField();
            $this->addPhoneField();
            $this->form->addHTML('</div>');
            
        if($this->addAddrElementWrap){
            $this->form->addHTML('</div>');
        }
    }

}
