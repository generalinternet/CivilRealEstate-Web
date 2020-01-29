<?php
/**
 * Description of AbstractContentManageEditorsFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentManageEditorsFormView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractContent */
    protected $content;
    protected $addWrap = true;
    
    public function __construct(GI_Form $form, AbstractContentPageDF $content) {
        parent::__construct();
        $this->form = $form;
        $this->content = $content;
        $this->addSiteTitle($content->getViewTitle());
        $this->addSiteTitle($content->getTitle());
        $this->addSiteTitle('Manage Editors');
    }
    
    protected function addFields() {
        $this->addEditorField();
    }
    
    protected function addEditorField($overWriteSettings = array(), $overWriteAutocompProps = array()){
        $autocompProps = array(
            'controller' => 'user',
            'action' => 'autocompUser',
            'ajax' => 1
        );
        foreach ($overWriteAutocompProps as $prop => $val) {
            $autocompProps[$prop] = $val;
        }
        $autoCompURL = GI_URLUtils::buildURL($autocompProps);
        $contentEditors = ContentEditorFactory::getContentEditors($this->content);
        $contentEditorIds = array();
        foreach($contentEditors as $contentEditor){
            $contentEditorIds[] = $contentEditor->getProperty('user_id');
        }
        $value = implode(',', $contentEditorIds);
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Select Editor(s)',
            'value' => $value,
            'autocompURL' => $autoCompURL,
            'autocompMultiple' => true,
            'placeHolder' => 'start typing an editor’s name.',
            'hiddenDesc' => 'The user that will be in charge of/have access to editing this form.'
        ), $overWriteSettings);
        $this->form->addField('editor_ids', 'autocomplete', $fieldSettings);
    }
    
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
    }
    
    protected function addFormTitle(){
        $this->addMainTitle('Manage Editors');
    }
    
    protected function addSubmitBtn(){
        $saveTerm = Lang::getString('save');
        $this->form->addHTML('<span class="submit_btn" tabindex="0" title="' . $saveTerm . '">' . $saveTerm . '</span>');
    }
    
    public function buildForm(){
        $this->addFormTitle();
        
        $this->addFields();
        
        $this->addSubmitBtn();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView(){
        $this->openViewWrap();
            $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
