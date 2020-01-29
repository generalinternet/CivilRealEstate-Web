<?php
/**
 * Description of GI_SearchView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.4
 */
abstract class GI_SearchView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /** @var GI_Form */
    protected $origForm = NULL;
    protected $queryValues;
    protected $boxId = NULL;
    protected $useAjax = false;
    protected $targetElementId = '';
    protected $modelClass = NULL;
    protected $searchAttributes = NULL;
    protected $showSearchBox = false;
    protected $useShadowBox = NULL;
    protected $useBasicSearch = NULL;
    protected $hideAdvancedSearch = false;
    protected $searchType = NULL;
    protected $basicBlockClasses = array();
    protected $advancedBlockClasses = array();

    public function __construct(GI_Form $form, $queryValues = array()) {
        parent::__construct();
        $this->form = $form;
        $this->origForm = clone $form;
        if(is_null($this->useShadowBox)){
            $this->setUseShadowBox(ProjectConfig::useShadowBoxSearch());
        }
        if(is_null($this->useBasicSearch)){
            $this->setUseBasicSearch(ProjectConfig::useBasicSearch());
        }
        if(is_null($this->searchType)){
            $this->setSearchType(ProjectConfig::getDefaultSearchType());
        }
        $this->queryValues = $queryValues;
        $this->rebuildForm(false);
    }
    
    abstract protected function buildForm();
    
    public function getBasicBlockClass() {
        return implode(' ', $this->basicBlockClasses);
    }

    public function getAdvancedBlockClass() {
        return implode(' ', $this->advancedBlockClasses);
    }

    public function addBasicBlockClass($basicBlockClass) {
        $this->basicBlockClasses[] = $basicBlockClass;
        return $this;
    }

    public function addAdvancedBlockClass($advancedBlockClass) {
        $this->advancedBlockClasses[] = $advancedBlockClass;
        return $this;
    }
    
    protected function rebuildForm($resetForm = true){
        if ($resetForm) {
            $this->form = $this->origForm;
        }
        
        $this->addSearchTypeField();
        $this->buildBasicForm();
        
        $this->form->addHTML('<div class="advanced_search_block ' . $this->getAdvancedBlockClass());
        $searchType = $this->getQueryValue('search_type');
        if (!$this->useBasicSearch || (empty($searchType) && $this->searchType === 'advanced') || $searchType === 'advanced') {
            $this->form->addHTML(' open');
        }
        $this->form->addHTML('">');
        $this->buildForm();
        $this->addBtns();
        $this->form->addHTML('</div>');
    }
    
    public function setBoxId($boxId){
        $this->boxId = $boxId;
        return $this;
    }
    
    public function getBoxId(){
        return $this->boxId;
    }
    
    public function setModelClass($modelClass){
        if(class_exists($modelClass)){
            $this->modelClass = $modelClass;
            //$this->form = $this->origForm; //Commented out because moving it into rebuildForm
            $this->rebuildForm();
        }
        return $this;
    }
    
    protected function getSearchFieldName($fieldName){
        if(!is_null($this->modelClass)){
            $modelClass = $this->modelClass;
            return $modelClass::getSearchFieldName($fieldName);
        }
        return $fieldName;
    }
    
    public function setTargetElementId($targetElementId){
        $this->targetElementId = $targetElementId;
        return $this;
    }
    
    public function getTargetElementId(){
        return $this->targetElementId;
    }
    
    public function setSearchAttributes($attributes){
        $this->searchAttributes = $attributes;
        return $this;
    }
    
    /**
     * @param boolean $useAjax
     * @return \GI_PageBarView
     */
    public function setUseAjax($useAjax, $targetElementId = NULL, $attributes = NULL){
        if(!is_null($attributes)){
            $this->setSearchAttributes($attributes);
        }
        $this->useAjax = $useAjax;
        if(is_null($this->searchAttributes)){
            $curAttributes = GI_URLUtils::getAttributes();
        } else {
            $curAttributes = $this->searchAttributes;
        }
        $curAttributes['ajax'] = 1;
        $formAction = GI_URLUtils::buildURL($curAttributes, true);
        $this->form->setFormAction($formAction);
        if(!empty($targetElementId)){
            $this->setTargetElementId($targetElementId);
        }
        return $this;
    }
    
    public function getQueryValue($valueKey){
        if(isset($this->queryValues[$valueKey])){
            return $this->queryValues[$valueKey];
        } else {
            return NULL;
        }
    }
    
    public function getQueryId(){
        if(isset($this->queryValues['queryId'])){
            return $this->queryValues['queryId'];
        } else {
            return 0;
        }
    }
    
    public function setShowSearchBox($showSearchBox){
        $this->showSearchBox = $showSearchBox;
        return $this;
    }
    
    public function setUseShadowBox($useShadowBox){
        $this->useShadowBox = $useShadowBox;
        return $this;
    }
    
    public function getUseShadowBox(){
        return $this->useShadowBox;
    }
    
    public function setSearchType($searchType){
        $this->searchType = $searchType;
        return $this;
    }
    
    public function setUseBasicSearch($useBasicSearch){
        $this->useBasicSearch = $useBasicSearch;
        if(!$useBasicSearch){
            $this->searchType = 'advanced';
        } else {
            $this->searchType = 'basic';
        }
        return $this;
    }
    
    public function setHideAdvancedSearch($hideAdvancedSearch){
        $this->hideAdvancedSearch = $hideAdvancedSearch;
        $this->rebuildForm();
        return $this;
    }
    
    protected function addBtns(){
        $this->form->addHTML('<div class="search_btns">');
            if ($this->useBasicSearch) {
                $this->form->addHTML('<span class="btn other_btn toggle_search_type_btn open_basic_search_block" tabindex="0"><span class="icon_wrap"><span class="icon swap"></span></span><span class="btn_text">Basic</span></span>');
            }
            $this->form->addHTML('<span class="btn other_btn close_search_box gray"><span class="icon_wrap"><span class="icon eks"></span></span><span class="btn_text">Close</span></span>');
            $this->form->addHTML('<span class="btn submit_btn" tabindex="0"><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
        $this->form->addHTML('</div>');
    }
    
    protected function openSearchBoxWrap(){
        $searchIdAttr = '';
        if(!empty($this->boxId)){
            $searchIdAttr = 'id="' . $this->boxId . '"';
        }
        
        $targetElementAttr = '';
        if(!empty($this->targetElementId)){
            $targetElementAttr = 'data-target-id="' . $this->targetElementId . '"';
        }
        
        $searchBoxClass = '';
        if($this->useAjax){
            $searchBoxClass .= 'use_ajax';
        }
        if($this->showSearchBox) {
            $searchBoxClass .= ' show_box';
        }
        $this->addHTML('<div class="search_box ' . $searchBoxClass . '" ' . $searchIdAttr . ' ' . $targetElementAttr . '>');
        return $this;
    }
    
    protected function closeSearchBoxWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView(){
        $this->openSearchBoxWrap();
        $this->addHTML($this->form->getForm('Search'));
        $this->closeSearchBoxWrap();
    }
    
    protected function addSearchTypeField(){
        $searchType = $this->getQueryValue('search_type');
        if(empty($searchType)){
            $searchType = $this->searchType;
        }
        $this->form->addField('search_type', 'hidden', array(
            'value' => $searchType,
        ));
    }
    
    protected function buildBasicForm(){
        $searchType = $this->getQueryValue('search_type');
        $this->form->addHTML('<div class="basic_search_block ' . $this->getBasicBlockClass());
        if ($this->useBasicSearch && ((empty($searchType) && $this->searchType === 'basic') || $searchType === 'basic')) {
            $this->form->addHTML(' open');
        }
        $this->form->addHTML('">');
            $this->addBasicFormFields();
            $this->addBasicFormBtns();
        $this->form->addHTML('</div>');
    }
    
    protected function addBasicFormFields(){
        $this->form->addHTML('<div class="basic_search_field_wrap">');
            $this->form->addField('basic_search_field', 'text', array(
                'displayName' => 'Search',
                'placeHolder' => 'Enter search terms',
                'value' => $this->getQueryValue('basic_search_field')
            ));
        $this->form->addHTML('</div>');
    }
    
    protected function addBasicFormBtns(){
        $this->form->addHTML('<div class="search_btns">');
            if (!$this->hideAdvancedSearch) {
                $this->form->addHTML('<span class="btn other_btn toggle_search_type_btn open_advanced_search_block" tabindex="0"><span class="icon_wrap"><span class="icon swap"></span></span><span class="btn_text">Advanced</span></span>');
            }
            
            $this->form->addHTML('<span class="btn other_btn close_search_box gray"><span class="icon_wrap"><span class="icon eks"></span></span><span class="btn_text">Close</span></span>');
            
            $this->form->addHTML('<span class="btn submit_btn" tabindex="0"><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
        $this->form->addHTML('</div>');
    }
    
    protected function addTagField($overWriteSettings = array(), $overWriteAutocompProps = array()){
        $autocompProps = array(
            'controller' => 'tag',
            'action' => 'autocompTag',
            'type' => 'tag',
            'ajax' => 1
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autocompURL = GI_URLUtils::buildURL($autocompProps);
        
        $tagIds = $this->getQueryValue('tag_ids');
        $value = NULL;
        if(!empty($tagIds)){
            $value = implode(',', $tagIds);
        }
        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Search by Tag(s)',
            'placeHolder' => 'search for tag(s)',
            'value' => $value,
            'autocompURL' => $autocompURL,
            'autocompMultiple' => true
        ), $overWriteSettings);
        $fieldName = 'search_tag_ids';
        
        if(isset($overWriteSettings['fieldName'])){
            $fieldName = $overWriteSettings['fieldName'];
        }
        $this->form->addField($fieldName, 'autocomplete', $fieldSettings);
    }
            
    public function getShadowBoxURL(){
        if(is_null($this->searchAttributes)){
            $curAttributes = GI_URLUtils::getAttributes();
        } else {
            $curAttributes = $this->searchAttributes;
        }
        $curAttributes['search'] = 1;
        if(isset($this->queryValues['queryId'])){
            $curAttributes['queryId'] = $this->queryValues['queryId'];
        }
        
        return GI_URLUtils::buildURL($curAttributes);
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
