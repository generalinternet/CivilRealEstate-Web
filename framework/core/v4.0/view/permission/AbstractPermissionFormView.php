<?php
/**
 * Description of AbstractPermissionFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractPermissionFormView extends MainWindowView {

    /** @var GI_Form */
    protected $form;
    /** @var AbstractPermission */
    protected $permission;

    public function __construct(GI_Form $form, AbstractPermission $permission) {
        parent::__construct();
        $this->form = $form;
        $this->permission = $permission;
        $this->addSiteTitle('Permission');
        if(empty($this->permission->getProperty('id'))){
            $this->addSiteTitle('Add');
            $formTitle = 'Add Permission';
        } else {
            $this->addSiteTitle($this->permission->getProperty('title'));
            $this->addSiteTitle('Edit');
            $formTitle = 'Edit Permission';
        }
        $this->buildForm();
        
        $this->setWindowTitle($formTitle);
        $listBarURL = $this->permission->getListBarURL();
        $this->setListBarURL($listBarURL);
    }

    protected function buildForm(){
        $this->form->addHTML('<div class="auto_columns halves">');
        
        $this->addTitleField();
        
        $this->addRefField();
        
        $this->addCategoryField();
        
        $this->addOnByDefaultField();
        
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<span class="submit_btn" title="Save">'.Lang::getString('submit').'</span>');
    }
    
    protected function addTitleField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->permission->getProperty('title'),
            'displayName' => Lang::getString('title'),
            'placeHolder' => Lang::getString('title'),
            'required' => true,
        ), $overWriteSettings);
        $this->form->addField('title', 'text', $fieldSettings);
    }
    
    protected function addRefField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->permission->getProperty('ref'),
            'displayName' => 'Reference',
            'placeHolder' => 'Reference',
            'required' => true,
            'readOnly' => $this->permission->isCore()
        ), $overWriteSettings);
        $this->form->addField('ref', 'text', $fieldSettings);
    }
    
    protected function addCategoryField($overWriteSettings = array()){
        $options = PermissionCategoryFactory::getOptionsArray();
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->permission->getProperty('permission_category_id'),
            'displayName' => 'Category',
            'options' => $options
        ), $overWriteSettings);
        $this->form->addField('permission_category_id', 'dropdown', $fieldSettings);
    }
    
    protected function addOnByDefaultField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->permission->getProperty('default_val'),
            'displayName' => 'On by Default',
            'readOnly' => $this->permission->isCore()
        ), $overWriteSettings);
        
        $this->form->addField('default_val', 'onoff', $fieldSettings);
    }

    public function addViewBodyContent() {
        $this->addHTML($this->form->getForm());
    }

}
