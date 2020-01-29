<?php
/**
 * Description of AbstractContactRelationshipFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
abstract class AbstractContactRelationshipFormView extends GI_FormRowView{
    
    protected $seqNumFieldName = 'contact_relationships';
    protected $modelFieldPrefix = 'contact_relationship';
    
    protected $form;
    protected $contact;
    protected $contactRelationship;
    protected $linkedTypeRef;
    protected $linkedId;
    
    public function __construct(GI_Form $form, AbstractContact $contact, AbstractContactRelationship $contactRelationship, $linkedTypeRef) {
        $this->addHiddenTypeField = false;
        parent::__construct($form);
        $this->form = $form;
        $this->contactRelationship = $contactRelationship;
        $this->contact = $contact;
        $this->linkedTypeRef = $linkedTypeRef;
        $this->linkedFieldName = '';
        $this->hiddenFieldName = '';
        if (!empty($contact) && !empty($contactRelationship)) {
            if ($contact->getProperty('id') == $contactRelationship->getProperty('p_contact_id')) {
                $this->linkedId = $contactRelationship->getProperty('c_contact_id');
                $this->linkedFieldName = 'c_contact_id';
                $this->hiddenFieldName = 'p_contact_id';
            } else if ($contact->getProperty('id') == $contactRelationship->getProperty('c_contact_id')) {
                $this->linkedId = $contactRelationship->getProperty('p_contact_id');
                $this->linkedFieldName = 'p_contact_id';
                $this->hiddenFieldName = 'c_contact_id';
            }
        }
    }
    
    protected function getModelId(){
        return $this->contactRelationship->getId();
    }
    
    protected function getModelTypeRef() {
        return $this->contactRelationship->getTypeRef();
    }

    public function beforeReturningView() {
        $this->buildView();
    }

    protected function addFields() {
        $this->form->addHTML('<div class="columns fifths form_row_fields">')
                ->addHTML('<div class="column three_fifths">');
        $this->addLinkedContactFieldWrap();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addTitleFieldWrap();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addTypeField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addLinkedContactFieldWrap($overWriteSettings = array()) {
        $contactFieldName = $this->contactRelationship->getFieldName($this->linkedFieldName);
        $addTypeAttribute = 'add' . ucfirst($this->linkedTypeRef) .'Type';
        $linkedTypeModel = ContactFactory::getTypeModelByRef($this->linkedTypeRef);
        $contactAutocompleteProperties = array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => $this->linkedTypeRef,
            'ajax' => 1,
            'autocompField' => $contactFieldName,
            $addTypeAttribute => $this->linkedTypeRef,
        );
        $contactLocAutoCompURL = GI_URLUtils::buildURL($contactAutocompleteProperties);
        $this->form->addField($contactFieldName, 'autocomplete', array(
            'displayName' => $linkedTypeModel->getProperty('title'),
            'placeHolder' => 'start typing...',
            'autocompURL' => $contactLocAutoCompURL,
            'value' => $this->linkedId,
            'hideDescOnError' => false,
            'required' => true,
            'autocompMinLength' => 1,
        ));
        
        
        $this->form->addField($this->contactRelationship->getFieldName($this->hiddenFieldName), 'hidden', array(
            'value' => $this->contact->getProperty('id'),
        ));
    }
    
    protected function addTitleFieldWrap($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Title',
            'placeHolder' => 'enter title...',
            'value' => $this->contactRelationship->getProperty('title'),
        ), $overWriteSettings);
        
        $this->form->addField($this->contactRelationship->getFieldName('title'), 'text', $fieldSettings);
    }
    
    protected function addTypeField() {
        $fieldName = $this->contactRelationship->getFieldName('contact_relationship_type');
        $types = ContactRelationshipFactory::getTypesArray();
        if (count($types) > 1) {
            $this->form->addField($fieldName, 'dropdown', array(
                'value'=>$this->contactRelationship->getTypeRef(),
                'options'=>$types,
                'required'=>true,
                'displayName'=>'Type',
                'hideNull' => true
            ));
        } else {
            $this->form->addField($fieldName, 'hidden', array(
                'value'=>$this->contactRelationship->getTypeRef(),
            ));
        }
    }

    public function getFieldSuffix(){
        return $this->contactRelationship->getFieldSuffix();
    }
    
    public function getSeqNumber() {
        return $this->contactRelationship->getSeqNumber();
    }

    protected function addRemoveBtn() {
        if (Permission::verifyByRef('unlink_contacts')) {
            $this->form->addHTML('<span class="custom_btn remove_form_row">'.GI_StringUtils::getIcon('remove').'</span>');
        }
    }

}
