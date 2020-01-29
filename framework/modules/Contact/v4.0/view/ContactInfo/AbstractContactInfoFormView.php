<?php
/**
 * Description of AbstractContactInfoFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
class AbstractContactInfoFormView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /** @var ContactInfo */
    protected $contactInfo;
    protected $formBuilt = false;
    protected $formTitle = '';
    protected $hideTypeField = false;
    protected $pType = NULL;
    protected $viewBuilt = false;
    protected $forceNoRemove = false;
    protected $addAddrElementWrap = true;
    protected $readOnly = false;
    protected $disabled = false;
    protected $autoComplete = true;
    protected $clearValue = false;
    
    public function __construct(GI_Form $form, AbstractContactInfo $contactInfo) {
        parent::__construct();
        $this->form = $form;
        $this->contactInfo = $contactInfo;
        $this->formTitle = 'Edit Contact Info';
    }
    
    public function setReadOnly($readOnly){
        $this->readOnly = $readOnly;
    }
    
    public function setDisabled($disabled){
        $this->disabled = $disabled;
    }
    
    public function setAutoComplete($autoComplete){
        $this->autoComplete = $autoComplete;
    }
    
    public function setClearValue($clearValue){
        $this->clearValue = $clearValue;
    }
    
    public function setPType($pType){
        $this->pType = $pType;
        return $this;
    }
    
    public function setForceNoRemove($forceNoRemove){
        $this->forceNoRemove = $forceNoRemove;
        return $this;
    }
    
    public function buildForm() {
        $fieldSuffix = $this->contactInfo->getFieldSuffix();
        $removable = false;
        if(!is_null($fieldSuffix) && $this->contactInfo->getIsFormRowable() && !$this->forceNoRemove){
            $removable = true;
        }
        $contactInfoClass = '';
        if(!$removable){
            $contactInfoClass = 'force_keep';
        }
        $this->form->addHTML('<div class="contact_info ' . $contactInfoClass . '">');
        $this->form->addHTML('<div class="contact_info_fields">');
        if($removable){
            $this->form->addHTML('<span class="remove_contact_info custom_btn gray">'.GI_StringUtils::getIcon('remove_sml').'</span>');
            $this->form->addHTML('<input name="' . $this->pType . '[]" value="' . $fieldSuffix . '" type="hidden" class="seq_count"/>');
            $this->form->addField($this->pType . '_id_' . $fieldSuffix, 'hidden', array(
                'value' => $this->contactInfo->getId(),
            ));
        }
        $this->buildContactInfoFields();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->formBuilt = true;
    }
    
    public function buildContactInfoFields(){

    }
    
    public function buildView($fullView = true){
        if (!$this->formBuilt) {
            $this->buildForm();
        }
        
        if ($fullView) {
            $this->addHTML($this->form->getForm());
        } else {
            $this->form->setBtnText('');
            $this->addHTML($this->form->getForm('', false));
        }
        
        $this->viewBuilt = true;
    }
    
    public function beforeReturningView() {
        if(!$this->viewBuilt){
            $this->buildView();
        }
    }
    
    protected function getFieldName($coreFieldName) {
        return $this->contactInfo->getFieldName($coreFieldName);
    }
    
    public function hideTypeField($hideTypeField = true) {
        $this->hideTypeField = $hideTypeField;
    }
    
    public function setAddAddrElementWrap($addAddrElementWrap){
        $this->addAddrElementWrap = $addAddrElementWrap;
        return $this;
    }

}
