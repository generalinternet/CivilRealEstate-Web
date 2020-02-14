<?php
/**
 * Description of AbstractTagListFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagListFormView extends GI_View {

    protected $inputType = 'autocomplete';
    protected $showListTitle = true;
    protected $tagType = 'tag';
    protected $fieldName = 'tag_ids';
    protected $placeHolder = 'Start typing...';
    protected $listTitle = 'Tags';
    protected $contextRef = NULL;
    /** @var GI_Form */
    protected $form;
    /** @var GI_Model */
    protected $subjectModel;
    protected $overWriteFieldSettings = array();
    protected $overWriteAutocompProps = array();
    protected $tagIds = NULL;
    protected $required = false;

    public function __construct(GI_Form $form, GI_Model $subjectModel) {
        parent::__construct();
        $this->form = $form;
        $this->subjectModel = $subjectModel;
    }
    
    public function setInputType($inputType) {
        $this->inputType = $inputType;
        return $this;
    }
    
    public function setShowListTitle($showListTitle) {
        $this->showListTitle = $showListTitle;
        return $this;
    }
    
    public function setTagType($tagType) {
        $this->tagType = $tagType;
        return $this;
    }
    
    public function setFieldName($fieldName) {
        $this->fieldName = $fieldName;
        return $this;
    }
    
    public function setPlaceHolder($placeHolder) {
        $this->placeHolder = $placeHolder;
        return $this;
    }
    
    public function setListTitle($listTitle) {
        $this->listTitle = $listTitle;
        return $this;
    }
    
    public function setContextRef($contextRef){
        $this->contextRef = $contextRef;
        return $this;
    }
    
    public function setOverWriteFieldSettings($overWriteFieldSettings = array()){
        $this->overWriteFieldSettings = $overWriteFieldSettings;
        return $this;
    }
    
    public function setOverWriteAutocompProps($overWriteAutocompProps = array()){
        $this->overWriteAutocompProps = $overWriteAutocompProps;
        return $this;
    }
    
    public function setTagIds($tagIds){
        $this->tagIds = $tagIds;
        return $this;
    }
    
    public function setRequired($required){
        $this->required = $required;
        return $this;
    }
    
    public function getInputType() {
        return $this->inputType;
    }

    public function getShowListTitle() {
        return $this->showListTitle;
    }

    public function getTagType() {
        return $this->tagType;
    }

    public function getFieldName() {
        return $this->fieldName;
    }

    public function getPlaceHolder() {
        return $this->placeHolder;
    }

    public function getListTitle() {
        return $this->listTitle;
    }
    
    public function getContextRef(){
        return $this->contextRef;
    }

    public function getOverWriteFieldSettings() {
        return $this->overWriteFieldSettings;
    }

    public function getOverWriteAutocompProps() {
        return $this->overWriteAutocompProps;
    }
    
    public function getTagIds(){
        return $this->tagIds;
    }
    
    public function getRequired(){
        return $this->required;
    }
    
    public function buildForm() {
        switch($this->inputType){
            case 'onoff':
                $this->buildOnOffFields();
                break;
            case 'checkbox':
                $this->buildCheckboxField();
                break;
            case 'dropdown':
                $this->buildDropdownField();
                break;
            case 'autocomplete':
                $this->buildAutocompField();
                break;
        }
    }
    
    public function getSubmittedTagIds(){
        if(!$this->form->wasSubmitted()){
            return NULL;
        }
        $tagIds = array();
        $fieldName = $this->getFieldName();
        switch($this->inputType){
            case 'onoff':
                $typeRef = $this->getTagType();
                $allTags = TagFactory::getByRef($typeRef);
                foreach ($allTags as $tag) {
                    $tagId = $tag->getId();
                    $tagValue = filter_input(INPUT_POST, $fieldName . '_' . $tagId);
                    if (!empty($tagValue)) {
                        $tagIds[] = $tagId;
                    }
                }
                break;
            case 'checkbox':
                $tagIds = filter_input(INPUT_POST, $fieldName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                break;
            case 'dropdown':
                $tagId = filter_input(INPUT_POST, $fieldName);
                $tagIds[] = $tagId;
                break;
            case 'autocomplete':
                $tagIdString = filter_input(INPUT_POST, $fieldName);
                $tagIds = explode(',', $tagIdString);
                break;
        }
        return $tagIds;
    }
    
    public function buildView() {
        $this->buildForm();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        return parent::beforeReturningView();
    }
    
    protected function buildOnOffFields(){
        $tagTypeRef = $this->getTagType();
        $contextRef = $this->getContextRef();
        $tagIds = array();
        if($this->subjectModel->getId()){
            $tagIds = $this->subjectModel->getTagIds($tagTypeRef, $contextRef);
        }
        if(empty($tagIds)){
            $tagIds = $this->getTagIds();
            if(!is_array($tagIds)){
                $tagIds = explode(',', $tagIds);
            }
        }
        $allTags = TagFactory::getByRef($tagTypeRef);
        if (!empty($allTags)) {
            if($this->getShowListTitle()){
                $listTitle = $this->getListTitle();
                $fieldsetSettings = array();
                if($this->getRequired()){
                    $fieldsetSettings['class'] = 'fake_required';
                }
                $this->form->startFieldset($listTitle, $fieldsetSettings);
            }
            $this->form->addHTML('<div class="inline_form_elements">');
            $fieldName = $this->getFieldName();
            $overWriteFieldSettings = $this->getOverWriteFieldSettings();
            foreach ($allTags as $tag) {
                $tagTitle = $tag->getProperty('title');
                $tagId = $tag->getId();
                $value = 0;
                if (in_array($tagId, $tagIds)) {
                    $value = 1;
                }
                $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => $tagTitle,
                    'value' => $value,
                    'formElementClass' => 'inline_label tag_list_field'
                ), $overWriteFieldSettings);
                $this->form->addField($fieldName . '_' . $tagId, 'onoff', $fieldSettings);
            }
            $this->form->addHTML('</div>');
            if($this->getShowListTitle()){
                $this->form->endFieldset();
            }
        }
    }
    
    protected function buildCheckboxField(){
        $tagTypeRef = $this->getTagType();
        $contextRef = $this->getContextRef();
        $overWriteFieldSettings = $this->getOverWriteFieldSettings();
        
        $value = NULL;
        $tagIds = array();
        if($this->subjectModel->getId()){
            $tagIds = $this->subjectModel->getTagIds($tagTypeRef, $contextRef);
            $value = $tagIds;
        }
        if(empty($tagIds)){
            $tagIds = $this->getTagIds();
            if(!is_array($tagIds)){
                $tagIds = explode(',', $tagIds);
            }
        }
        $options = TagFactory::getTagOptionsArrayByTypeRef($tagTypeRef);
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $this->getListTitle(),
            'showLabel' => $this->getShowListTitle(),
            'options' => $options,
            'value' => $value,
            'formElementClass' => 'tag_list_field',
            'required' => $this->getRequired()
        ), $overWriteFieldSettings);
        $this->form->addField($this->getFieldName(), 'checkbox', $fieldSettings);
    }
    
    protected function buildDropdownField(){
        $tagTypeRef = $this->getTagType();
        $contextRef = $this->getContextRef();
        $overWriteFieldSettings = $this->getOverWriteFieldSettings();
        
        $value = NULL;
        $tagIds = array();
        if($this->subjectModel->getId()){
            $tagIds = $this->subjectModel->getTagIds($tagTypeRef, $contextRef);
            $value = $tagIds[0];
        }
        if(empty($tagIds)){
            $tagIds = $this->getTagIds();
            if(!is_array($tagIds)){
                $tagIds = explode(',', $tagIds);
            }
            $value = $tagIds[0];
        }
        $options = TagFactory::getTagOptionsArrayByTypeRef($tagTypeRef);
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $this->getListTitle(),
            'showLabel' => $this->getShowListTitle(),
            'options' => $options,
            'value' => $value,
            'formElementClass' => 'tag_list_field',
            'required' => $this->getRequired()
        ), $overWriteFieldSettings);
        $this->form->addField($this->getFieldName(), 'dropdown', $fieldSettings);
    }
    
    protected function buildAutocompField(){
        $tagTypeRef = $this->getTagType();
        $contextRef = $this->getContextRef();
        $overWriteAutocompProps = $this->getOverWriteAutocompProps();
        $overWriteFieldSettings = $this->getOverWriteFieldSettings();
        
        $autocompProps = array(
            'controller' => 'tag',
            'action' => 'autocompTag',
            'type' => $tagTypeRef,
            'valueColumn' => 'id',
            'ajax' => 1
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autocompURL = GI_URLUtils::buildURL($autocompProps);

        $value = NULL;
        $tagIds = array();
        if($this->subjectModel->getId()){
            $tagIds = $this->subjectModel->getTagIds($tagTypeRef, $contextRef);
            $value = implode(',', $tagIds);
        }
        if(empty($tagIds)){
            $tagIds = $this->getTagIds();
            if(is_array($tagIds)){
                $tagIds = implode(',', $tagIds);
            }
            $value = $tagIds;
        }
        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'autocompURL' => $autocompURL,
            'displayName' => $this->getListTitle(),
            'placeHolder' => $this->getPlaceHolder(),
            'autocompMultiple' => true,
            'autocompMinLength' => 0,
            'autocompAppendTo' => 'tag_list_autocomp',
            'value' => $value,
            'formElementClass' => 'tag_list_field',
            'required' => $this->getRequired()
        ), $overWriteFieldSettings);
        $this->form->addField($this->getFieldName(), 'autocomplete', $fieldSettings);
    }
    
    /*
    public function buildForm() {
        
        if ($this->inputType == 'onoff') {
            if (!empty($this->allTags)) {
                $this->form->startFieldset($this->listTitle);
                $this->form->addHTML('<div class="inline_form_elements">');
                 foreach ($this->allTags as $tag) {
                     $tagTitle = $tag->getProperty('title');
                     $tagId = $tag->getProperty('id');
                     $value = 0;
                     if (isset($this->existingTags[$tagId])) {
                         $value = 1;
                     }
                     $this->form->addField($this->onoffFieldPrefix . $tagId, 'onoff', array(
                         'required'=>false,
                         'displayName'=>$tagTitle,
                         'value'=>$value,
                         'formElementClass'=>'inline_label'
                     ));
                 }
                 $this->form->addHTML('</div>');
                 $this->form->endFieldset();
            }
        } else {
            if (!empty($this->allTags)) {
                if ($this->showListTitle) {
                    $this->form->startFieldset($this->listTitle);
                }
                if ($this->inputType == 'dropdown') {
                    $options = array();
                    $values = array();
                    foreach ($this->allTags as $tag) {
                        $tagId = $tag->getProperty('id');
                        $options[$tagId] = $tag->getProperty('title');
                        if (isset($this->existingTags[$tagId])) {
                            $values[] = $tagId;
                        }
                    }
                    $this->form->addField($this->fieldName, 'dropdown', array(
                        'showLabel'=>false,
                        'options'=>$options,
                        'value'=>$values,
                    ));
                } else if ($this->inputType == 'autocomplete') {
                    $acAttr = array(
                        'controller' => 'tag',
                        'action' => 'autocompTag',
                        'type' => $this->acType,
                        'valueColumn' => 'id',
                        'ajax' => 1
                    );
                    $tagURL = GI_URLUtils::buildURL($acAttr);
                    $values = array();

                    foreach ($this->existingTags as $this->existingTag) {
                        $values[] = $this->existingTag->getId();
                    }
                    $this->form->addField($this->fieldName, 'autocomplete', array(
                        'showLabel'=>false,
                        'placeHolder' => $this->placeHolder,
                        'autocompURL' => $tagURL,
                        'autocompMultiple' => true,
                        'autocompMinLength' => 0,
                        'value'=> implode(',', $values),
                        
                    ));
                }
                $this->form->endFieldset();
            }
        }
            
    }
    */
    
}
