<?php
/**
 * Description of AbstractContactOrgProfileBasicFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract Class AbstractContactOrgProfileBasicFormView extends MainWindowView {
    
    protected $contactOrg;
    protected $primaryIndividual;
    protected $form;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractContactOrg $contactOrg) {
        parent::__construct();
        $this->form = $form;
        $this->contactOrg = $contactOrg;
        $primaryIndividual = $contactOrg->getPrimaryIndividual();
        if (empty($primaryIndividual)) {
            $primaryIndividual = ContactFactory::buildNewModel('ind');
        }
        $this->primaryIndividual = $primaryIndividual;
    }
    
    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }


    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody($this->form);
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }
    
    protected function buildFormHeader() {
        
    }

    public function buildFormBody() {
        $this->addNameFields();
        $this->form->addHTML('<br/>');
        $this->addContactInfoForms();
    }

    protected function addNameFields() {
        $this->form->addHTML('<div class="auto_columns">');
            $this->form->addHTML('<div class="form_element full_width">');
                $this->addCompanyNameField();
            $this->form->addHTML('</div>');
            $this->addFirstNameField();
            $this->addLastNameField();
            $this->form->addHTML('<div class="form_element full_width">');
                $this->addDisplayNameField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addCompanyNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Company',
            'value' => $this->contactOrg->getProperty('contact_org.title')
        ), $overwriteSettings);
        $this->form->addField('company_name', 'text', $fieldSettings);
    }

    protected function addFirstNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'First Name',
            'value' => $this->primaryIndividual->getProperty('contact_ind.first_name'),
        ), $overwriteSettings);
        $this->form->addField('first_name', 'text', $fieldSettings);
    }

    protected function addLastNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Last Name',
            'value'=>$this->primaryIndividual->getProperty('contact_ind.last_name'),
        ), $overwriteSettings);
        $this->form->addField('last_name', 'text', $fieldSettings);
    }

    protected function addDisplayNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Display Name as',
            'value' => $this->contactOrg->getProperty('display_name'),
            'required' => true
        ), $overwriteSettings);
        $this->form->addField('display_name', 'text', $fieldSettings);
    }

    protected function buildFormFooter() {
        $this->addSubmitButton();
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Submit</span>');
    }

    protected function addContactInfoForms() {
        $pTypeRefs = array();
        $contactInfos = $this->contactOrg->getContactInfoArrayFromForm($this->form);
        $this->form->addHTML('<div class="auto_columns">');
        foreach ($contactInfos as $pTypeRef => $contactInfos) {
            $pTypeRefs[] = $pTypeRef;
            $pType = ContactInfoFactory::buildNewModel($pTypeRef);
            if (empty($contactInfos)) {
                continue;
            }
            $formBlockAlignment = $contactInfos[0]->getFormBlockAlignment();

            $this->form->addHTML('<div class="' . $formBlockAlignment . '">');
            $this->form->startFieldset($pType->getTypeTitle());

            $contactInfoWrapClass = '';
       //     if($this->forceContactInfo){
                $contactInfoWrapClass .= ' force_one_contact_info';
      //      }
            
            $addAddrElementWrap = true;
            if($formBlockAlignment == 'multi_column'){
                $contactInfoWrapClass .= ' auto_columns';
            } else {
                $addAddrElementWrap = false;
            }
            
            $this->form->addHTML('<div class="contact_infos_wrap ' . $pTypeRef . ' ' . $contactInfoWrapClass . '">');
            
            $itemCount = 0;
            foreach ($contactInfos as $contactInfo) {
                $contactInfo->setFieldSuffix($itemCount);
                $contactInfoFormView = $contactInfo->getFormView($this->form);
                $contactInfoFormView->setPType($pTypeRef);
                $contactInfoFormView->buildForm();
                $itemCount++;
            }
            
            if($this->contactOrg->multiInfoEnabled($pTypeRef)){
                $addContactInfoURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'addContactInfo',
                    'type'=> $pTypeRef
                ));
                $this->form->addHTML('<a href="' . $addContactInfoURL . '" class="custom_btn add_contact_info" tabindex="0">'.GI_StringUtils::getIcon('add').'<span class="btn_text">' . $pType->getTypeTitle() . '</span></a>');
            }
            
            $this->form->addHTML('</div>');
            
            $this->form->endFieldset();
                
            $this->form->addHTML('</div>');
        }
        $this->form->addHTML('</div>');
        
        $this->form->addField('p_type_refs', 'hidden', array(
            'value' => implode(',', $pTypeRefs)
        ));
    }
    
    
    
}