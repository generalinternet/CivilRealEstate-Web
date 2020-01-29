<?php
/**
 * Description of AbstractContactInfo
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactInfo extends GI_Model {

    protected $fieldPrefix = NULL;
    protected $fieldSuffix = NULL;
    protected $isFormRowable = true;
    protected $contact = NULL;

    public function __construct($map) {
        parent::__construct($map);
        $this->setFieldPrefix('contact_info_');
    }
    
    /**
     * @return AbstractContact
     */
    public function getContact() {
        if (empty($this->contact)) {
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }

    public function getFieldPrefix() {
        return $this->fieldPrefix;
    }

    public function setFieldPrefix($fieldPrefix) {
        $this->fieldPrefix = $fieldPrefix;
        return $this;
    }
    
    public function getFieldSuffix() {
        return $this->fieldSuffix;
    }
    
    public function setFieldSuffix($fieldSuffix) {
        $this->fieldSuffix = $fieldSuffix;
        return $this;
    }
    
    public function setIsFormRowable($isFormRowable = true) {
        $this->isFormRowable = $isFormRowable;
    }
    
    public function getIsFormRowable() {
        return $this->isFormRowable;
    }
    
    public function getFieldName($coreFieldName) {
        $fieldName = $this->fieldPrefix . $coreFieldName;
        if(!is_null($this->fieldSuffix)){
            $fieldName .= '_' .$this->fieldSuffix;
        }
        return $fieldName;
    }
    
    /**
     * @param GI_Form $form
     * @param type $otherData
     * @return \AbstractContactInfoFormView
     */
    public function getFormView(GI_Form $form, $otherData = array()) {
        $formView = new ContactInfoFormView($form, $this, $otherData);
        return $formView;
    }
    
    public function setPropertiesFromForm(GI_Form $form){
        
    }

    public function validateForm(\GI_Form $form) {
        return parent::validateForm($form);
    }
    
    public function handleFormSubmission(GI_Form $form) {
        if (!$this->validateForm($form)) {
            return false;
        }
        
        $targetTypeRef = filter_input(INPUT_POST, $this->getFieldName('type_ref'));
        if (!empty($targetTypeRef)) {
            if ($targetTypeRef !== $this->getTypeRef()) {
                $contactInfoId = $this->getId();
                if(!empty($contactInfoId)){
                    $newModel = ContactInfoFactory::changeModelType($this, $targetTypeRef);
                    $newModel->setFieldSuffix($this->getFieldSuffix());
                } else {
                    $newModel = ContactInfoFactory::buildNewModel($targetTypeRef);
                
                    $newModel->setProperty('contact_id', $this->getProperty('contact_id'));
                    $newModel->setFieldSuffix($this->getFieldSuffix());
                }
                return $newModel->handleFormSubmission($form);
            }
        }
        
        $this->setPropertiesFromForm($form);
        
        if ($this->save()) {
            return $this;
        }
        return NULL;
    }
    
    /** @return AbstractContactInfoDetailView */
    public function getDetailView() {
        return NULL;
    }

    public function getTypesArray() {
        $typesArray = ContactInfoFactory::getTypesArray($this->getTypeRef());
        return $typesArray;
    }

    public function getFormColumnClass() {
        return '';
    }

    public function getFormBlockAlignment() {
        return 'single_column';
    }
    
    public function setRequiredDefaultProperties() {
        return true;
    }
    
    public function isQuickbooksLinked() {
        if (!empty($this->getProperty('qb_linked'))) {
            return true;
        }
        return false;
    }
    
    public function save() {
        $changed = $this->getHasChanged();
        if (parent::save()) {
            if ($this->isQuickbooksLinked() && $changed) {
                $contact = $this->getContact();
                if (!empty($contact)) {
                    $contactQB = $contact->getContactQB();
                    if (!empty($contactQB)) {
                        $contactQB->setProperty('export_required', 1);
                        if (!$contactQB->save()) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

}
