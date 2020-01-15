<?php
/**
 * Description of AbstractContentFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFormView extends MainWindowView {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractContent */
    protected $content;
    protected $isSortable = true;
    protected $showRef = false;
    protected $showStyleFields = false;
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    protected $viewBuilt = false;
    protected $showTitleField = true;
    protected $showTagField = true;
    protected $displayAsChild = false;
    protected $formTitle = '';
    protected $addWrap = false;
    
    public function __construct(GI_Form $form, AbstractContent $content, $buildForm = true) {
        parent::__construct();
        $this->form = $form;
        $this->content = $content;
        $this->addSiteTitle('Content');
        $typeTitle = $this->content->getViewTitle();
        if($typeTitle != 'Content'){
            $this->addSiteTitle($typeTitle);
        }
        if(empty($this->content->getId())){
            $this->addSiteTitle('Add');
            $actionTerm = 'Add';
        } else {
            $this->addSiteTitle($this->content->getTitle());
            $this->addSiteTitle('Edit');
            $actionTerm = 'Edit';
        }
        if($buildForm){
            $this->buildForm();
        }
        if(!isset($this->form->curContentNumber)){
            $this->form->curContentNumber = 0;
        }
        $this->addCSS('framework/modules/Content/' . MODULE_CONTENT_VER . '/resources/content.css');
        $this->addJS('framework/modules/Content/' . MODULE_CONTENT_VER . '/resources/content.js');
        $this->setListBarURL($this->content->getListBarURL());
        $this->formTitle = $actionTerm . ' ' . $this->content->getTypeTitle();
        $this->setWindowTitle($this->formTitle);
    }
    
    public function setDisplayAsChild($displayAsChild){
        $this->displayAsChild = $displayAsChild;
        if($displayAsChild){
            $this->setAddOuterWrap(false);
            $this->setAddViewWrap(false);
            $this->setAddViewHeader(false);
            $this->setAddViewBodyWrap(false);
            $this->setAddViewFooter(false);
        }
        return $this;
    }
    
    public function setShowRef($showRef){
        $this->showRef = $showRef;
        return $this;
    }
    
    public function setShowStyleFields($showStyleFields){
        $this->showStyleFields = $showStyleFields;
        return $this;
    }
    
    public function setUploader(AbstractGI_Uploader $uploader = NULL){
        $this->uploader = $uploader;
        return $this;
    }
    
    protected function addBtns(){
        $this->form->addHTML('<div class="right_btns">');
            $this->addRemoveBtn();
            $this->addSortBtn();
        $this->form->addHTML('</div>');
    }
    
    protected function openFormBlockWrap(){
        $typeRef = $this->content->getTypeRef();
        $contentNumber = $this->content->getContentNumber();
        $this->form->addHTML('<div class="content_form_block_wrap ' . $typeRef . '" data-content-number="' . $contentNumber . '" data-parent-number="' . $this->content->getParentNumber() . '" data-type-ref="' . $typeRef . '">');
        $this->addSortPreview();
    }
    
    protected function closeFormBlockWrap(){
        $this->form->addHTML('</div>');
    }
    
    public function buildForm($buildInnerContentForm = true){
        $this->openFormBlockWrap();
        
        $this->addBtns();
        
        if($this->displayAsChild){
            $this->form->addHTML('<h3>' . $this->formTitle . '</h3>');
        }
        
        $this->form->addHTML('<div class="content_form_block">');
            $this->buildFormGuts();
        $this->form->addHTML('</div>');
        
        if($buildInnerContentForm){
            $this->buildInnerContentForm();
        }
        
        $this->closeFormBlockWrap();
    }
    
    protected function addRemoveBtn(){
        $parentNumber = $this->content->getParentNumber();
        if(!is_null($parentNumber)){
            $this->form->addHTML('<span class="remove_content custom_btn" title="Remove Content">' . GI_StringUtils::getIcon('remove') . '</span>');
        }
    }
    
    protected function addSortBtn(){
        $contentNumber = $this->content->getContentNumber();
        if($contentNumber != 0 && $this->isSortable){
            $this->form->addHTML('<span class="sort_handle custom_btn" title="Sort Content">' . GI_StringUtils::getIcon('sort') . '</span>');
        }
    }
    
    public function addTitleField($overWriteSettings = array()){
        if($this->showTitleField){
            $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => 'Title',
                'placeHolder' => 'Title',
                'required' => true,
                'value' => $this->content->getProperty('title')
            ), $overWriteSettings);

            $fieldType = 'text';
            if(isset($overWriteSettings['fieldType'])){
                $fieldType = $overWriteSettings['fieldType'];
            }
            $this->form->addField($this->content->getFieldName('title'), $fieldType, $fieldSettings);
        }
    }
    
    public function addRefField($overWriteSettings = array()){
        if($this->showRef){
            $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => 'Reference',
                'placeHolder' => 'Reference',
                'description' => 'Must contain no spaces, no accented characters, no capital letters, and no special characters.',
                'value' => $this->content->getProperty('ref')
            ), $overWriteSettings);

            $fieldType = 'text';
            if(isset($overWriteSettings['fieldType'])){
                $fieldType = $overWriteSettings['fieldType'];
            }
            $this->form->addField($this->content->getFieldName('ref'), $fieldType, $fieldSettings);
        }
    }
    
    protected function addTagField($overWriteSettings = array()) {
        if($this->showTagField){
            $tagLimit = $this->content->getTagLimit();
            $tagTerm = 'Tags';
            if($tagLimit == 1){
                $tagTerm = 'Tag';
            }
            $tagFieldName = $this->content->getFieldName('tag_ids');
            $acURL = GI_URLUtils::buildURL(array(
                'controller' => 'tag',
                'action' => 'autocompTag',
                'ajax' => 1,
                'type' => 'content',
                'valueColumn' => 'id',
                'autocompField' => $tagFieldName
            ), false, true);
            $tagIds = $this->content->getTagIds();
            $tagIdsString = implode(',', $tagIds);
            $fieldSettings = GI_Form::overWriteSettings(array(
                'displayName' => $tagTerm,
                'placeHolder' => 'SELECT',
                'autocompURL' => $acURL,
                'autocompMinLength' => 0,
                'autocompMultiple' => true,
                'autocompLimit' => $tagLimit,
                'value' => $tagIdsString
            ), $overWriteSettings);
            if (Permission::verifyByRef('assign_content_tags')) {
                $this->form->addField($tagFieldName, 'autocomplete', $fieldSettings);
            } else {
                $this->form->addField($tagFieldName, 'hidden', array(
                    'value' => $tagIdsString
                ));
            }
        }
    }
    
    public function addHiddenTypeRefField(){
        $this->form->addField($this->content->getFieldName('type_ref'), 'hidden', array(
            'value' => $this->content->getTypeRef()
        ));
    }
    
    public function buildFormGuts() {
        $this->addHiddenTypeRefField();
        
        $this->form->addHTML('<div class="auto_columns halves">');
        $this->addTitleField();
        $this->addStyleFields();
        $this->addRefField();
        $this->addTagField();
        $this->form->addHTML('</div>');
        
        if($this->uploader){
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }
    
    public function addTitleTagField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Title Tag',
            'options' => array(
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'div' => 'Div',
                'span' => 'Span',
                'b' => 'Bold',
                'i' => 'Italic',
                'u' => 'Underline'
            ),
            'hideNull' => true,
            'value' => $this->content->getTitleTag()
        ), $overWriteSettings);

        $this->form->addField($this->content->getFieldName('title_tag'), 'dropdown', $fieldSettings);
    }
    
    public function addCSSClassField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'CSS Class',
            'placeHolder' => 'CSS Class',
            'value' => $this->content->getProperty('html_class')
        ), $overWriteSettings);

        $this->form->addField($this->content->getFieldName('html_class'), 'text', $fieldSettings);
    }
    
    public function addStyleFields(){
        if($this->showStyleFields){
            $this->form->addHTML('<div class="columns halves">');
            $this->addTitleTagField(array(
                'formElementClass' => 'column'
            ));
            
            $this->addCSSClassField(array(
                'formElementClass' => 'column'
            ));
            $this->form->addHTML('</div>');
        }
    }
    
    public function buildInnerContentForm(){
        $childTypes = $this->content->getChildTypes();
        $contentNumber = $this->content->getContentNumber();
        $parentNumber = $this->content->getParentNumber();
        $this->form->addHTML('<input type="hidden" value="' . $contentNumber . '" name="content_numbers[]" />');
        if(!is_null($parentNumber)){
            $this->form->addHTML('<input type="hidden" value="' . $contentNumber . '" name="children_of_' . $parentNumber . '[]" />');
        }
        if(!empty($childTypes)){
            $contentInContentClass = 'content_in_content_form';
            if($this->isSortable){
                $contentInContentClass .= ' sortable';
            }
            $this->form->addHTML('<div class="' . $contentInContentClass . '">');

                $innerContents = $this->content->getInnerContent($this->form);
                foreach($innerContents as $innerContent){
                    if(empty($innerContent->getParentNumber())){
                        $innerContent->setParentNumber($this->content->getContentNumber());
                    }
                    $childNumber = $innerContent->getContentNumber();
                    if(empty($childNumber)){
                        $this->form->curContentNumber++;
                        $innerContent->setContentNumber($this->form->curContentNumber);
                    } else {
                        $this->form->curContentNumber = $childNumber;
                    }
                    $view = $innerContent->getFormView($this->form, false);
                    $view->setDisplayAsChild(true);
                    $view->buildForm(true);
                }
                
                $this->form->addHTML('<div class="add_content_in_content">');
                foreach($childTypes as $childType){
                    $childContentType = $childType->getEmptyChildContent();
                    $this->form->addHTML('<span class="custom_btn add_content" data-type-ref="' . $childContentType->getTypeRef() . '" data-min-children="' . $childType->getProperty('min_children') . '" data-max-children="' . $childType->getProperty('max_children') . '">' . GI_StringUtils::getIcon('add') . '<span class="btn_text">' . $childContentType->getTypeTitle() . '</span></span>');
                }
                $this->form->addHTML('</div>');
                //$this->form->addHTML('<span class="custom_btn add_content" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text">Content</span></span>');
            $this->form->addHTML('</div>');
        }
        $contentId = $this->content->getProperty('id');
        if(!empty($contentId)){
            $this->form->addHTML('<input type="hidden" value="' . $contentId . '" name="' . $this->content->getFieldName('content_id') . '" />');
        }
    }
    
    protected function addSortPreview(){
        $this->form->addHTML('<div class="sort_preview">');
            $this->form->addHTML('<h2>' . $this->content->getTypeRef() . $this->content->getFieldName('') . '</h2>');
        $this->form->addHTML('</div>');
    }
    
    public function addViewBodyContent(){
        if($this->displayAsChild){
            $this->addHTML($this->form->getForm('', false));
        } else {
            $this->form->addHTML('<span class="submit_btn" title="' . Lang::getString('save') . '">' . Lang::getString('save') . '</span>');
            $this->openPaddingWrap();
                $this->addHTML('<div class="content_form_parent" data-cur-content-number="' . $this->form->curContentNumber . '">');
                $this->addHTML($this->form->getForm());
                $this->addHTML('</div>');
            $this->closePaddingWrap();
        }
    }
    /*
    public function buildView($buildForm = true) {
        if($buildForm){
            $this->form->addHTML('<span class="submit_btn" title="' . Lang::getString('save') . '">' . Lang::getString('save') . '</span>');
            $this->openViewWrap();
                $this->addHTML('<div class="content_form_parent" data-cur-content-number="' . $this->form->curContentNumber . '">');
                $this->addHTML($this->form->getForm());
                $this->addHTML('</div>');
            $this->closeViewWrap();
        } else {
            $this->addHTML($this->form->getForm('', false));
        }
        $this->viewBuilt = true;
    }
     * 
     */
    
    public function beforeReturningView() {
        if(!$this->viewBuilt){
            $this->buildView();
        }
    }
    
}
