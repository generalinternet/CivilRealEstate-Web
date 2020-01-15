<?php
/**
 * Description of AbstractFolderFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractFolderFormView extends GI_View {
    
    /** GI_Form */
    protected $form;
    /** AbstractFolder */
    protected $folder;
    protected $addWrap = true;

    public function __construct(GI_Form $form, AbstractFolder $folder) {
        parent::__construct();
        $this->form = $form;
        $this->folder = $folder;
    }

    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }
    
    public function buildForm() {
        $this->buildFormHeader();
        $this->buildFormBody();
        $this->buildFormFooter();
    }
    
    protected function buildFormHeader() {
        
    }
    
    protected function buildFormFooter() {
        $this->addSubmitBtn();
    }
    
    protected function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }
    
    protected function buildFormBody() {
        $this->form->addHTML('<div class="form_body">');
        $this->addFields();
        $this->form->addHTML('</div>');
    }
    
    protected function addFields() {
        $this->addTitleField();
    }
    
    protected function addTitleField($overWriteSettings = array()){        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Title',
            'placeHolder' => 'Folder title',
            'required' => true,
            'value' => $this->folder->getProperty('title')
        ), $overWriteSettings);
        
        $this->form->addField('title', 'text', $fieldSettings);
    }

    protected function openViewWrap(){
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }
    
    protected function closeViewWrap(){
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        
        
        if($this->folder->getId()){
            $title = 'Edit Folder - <i>' . $this->folder->getTitle() . '</i>';
        } else {
            $title = 'Add Folder';
            $parentFolder = $this->folder->getParentFolder();
            if($parentFolder){
                $title .= ' to <i>' . $parentFolder->getTitle() . '</i>';
            }
        }
        $this->addMainTitle($title);
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
