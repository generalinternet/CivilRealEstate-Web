<?php

abstract class AbstractRoleGroupFormView extends MainWindowView {
    
    protected $form;
    protected $roleGroup;
    
    public function __construct(GI_Form $form, AbstractRoleGroup $roleGroup) {
        parent::__construct();
        $this->form = $form;
        $this->roleGroup = $roleGroup;
        $this->buildForm();
        $this->addSiteTitle('Role Group');
        if (empty($this->roleGroup->getId())) {
            $this->setWindowTitle(Lang::getString('add_role_group'));
            $this->addSiteTitle('Add');
        } else {
            $this->addMainTitle(Lang::getString('edit_role_group'));
            $this->setWindowTitle($this->roleGroup->getTitle());
            $this->addSiteTitle('Edit');
        }
        $listBarURL = $this->roleGroup->getListBarURL();
        $this->setListBarURL($listBarURL);
    }

    protected function buildForm() {
        $this->addTitleField();
        $this->addRankField();
        $this->addSubmitBtn();
    }
    
    protected function addTitleField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'required' => true,
            'displayName' => Lang::getString('title'),
            'value' => $this->roleGroup->getProperty('role_rank.title'),
            'placeHolder' => 'ex. Sales Team'
        ));
        
        $this->form->addField('title', 'text', $fieldSettings);
    }
    
    protected function addRankField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'required' => true,
            'displayName' => Lang::getString('role') . ' ' . Lang::getString('group') . '  ' . Lang::getString('rank'),
            'value' => $this->roleGroup->getProperty('role_rank.rank'),
            'placeHolder' => 'ex. 500'
        ));
        
        $this->form->addField('rank', 'integer_pos', $fieldSettings);
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" title="' . Lang::getString('save') . '">' . Lang::getString('save') . '</span>');
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        
        $this->addHTML($this->form->getForm());
        
        $this->closePaddingWrap();
    }

}
