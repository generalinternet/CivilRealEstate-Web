<?php
/**
 * Description of AbstractContactInfoEmailAddrFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactInfoEmailAddrFormView extends AbstractContactInfoFormView {
    
    /** @var AbstractContactInfoEmail */
    protected $contactInfo;
    
    public function __construct(GI_Form $form, AbstractContactInfo $contactInfo) {
        parent::__construct($form, $contactInfo);
        $this->formTitle = 'Edit Email Address';
    }

    protected function addEmailField(){
        $typeTitle = $this->contactInfo->getTypeTitle();
        $this->form->addField($this->getFieldName('email'), 'email', array(
            'displayName' => $typeTitle,
            'placeHolder' => 'ex. email@domain.com',
            'value' => $this->contactInfo->getProperty('contact_info_email_addr.email_address'),
            'readOnly' => $this->readOnly,
            'disabled' => $this->disabled,
            'clearValue' => $this->clearValue,
            'autoComplete' => $this->autoComplete,
        ));
    }
    
    protected function addTypeField(){
        if(!$this->hideTypeField){
            $this->form->addField($this->getFieldName('type_ref'), 'dropdown', array(
                'displayName' => 'Email Address Type',
                'options' => $this->contactInfo->getTypesArray(),
                'hideNull' => true,
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
            $this->addEmailField();
            $this->form->addHTML('</div>');
            
        if($this->addAddrElementWrap){
            $this->form->addHTML('</div>');
        }
    }

}
