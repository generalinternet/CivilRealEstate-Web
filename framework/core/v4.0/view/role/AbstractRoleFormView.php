<?php
/**
 * Description of AbstractRoleFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractRoleFormView extends MainWindowView {

    /** @var GI_Form */
    protected $form;
    protected $permissionOptions;
    /** @var AbstractRole */
    protected $role;
    /** @var AbstractPermission[] */
    protected $selectedPermissions;
    protected $roleRankNames;
    protected $currentRankName;
    protected $maxRoleRankNames;
    protected $currentMaxRoleRankName;
    protected $editableBefore;

    public function __construct(GI_Form $form, AbstractRole $role, $permissionOptions, $selectedPermissions, $roleRankNames, $currentRankName, $maxRoleRankNames, $currentMaxRoleRankName, $editableBefore) {
        parent::__construct();
        $this->form = $form;
        $this->role = $role;
        $this->permissionOptions = $permissionOptions;
        $this->selectedPermissions = $selectedPermissions;
        $this->roleRankNames = $roleRankNames;
        $this->currentRankName = $currentRankName;
        $this->maxRoleRankNames = $maxRoleRankNames;
        $this->currentMaxRoleRankName = $currentMaxRoleRankName;
        $this->editableBefore = $editableBefore;
        $this->buildForm();
        $this->addSiteTitle('Role');
        
        if (empty($this->role->getId())) {
            $formTitle = Lang::getString('add_role');
        } else {
            $formTitle = Lang::getString('edit_role');
            $this->addSiteTitle($this->role->getProperty('title'));
        }
        $this->addSiteTitle($formTitle);
        $this->setWindowTitle($formTitle);
        $roleGroup = $this->role->getRoleGroup();
        if($roleGroup){
            $listBarURL = $roleGroup->getListBarURL();
            $this->setListBarURL($listBarURL);
        }
    }

    protected function buildForm() {
        $this->form->addHTML('<div class="columns halves">');
            $this->form->addField('title', 'text', array(
                'value' => $this->role->getProperty('title'),
                'displayName' => Lang::getString('title'),
                'formElementClass' => 'column',
                'required' => true
            ));
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="columns halves">');
            $this->form->addField('role_rank', 'dropdown', array(
                'options' => $this->roleRankNames,
                'value' => $this->currentRankName,
                'required' => true,
                'hideNull' => true,
                'displayName' => Lang::getString('role') . ' ' . Lang::getString('group'),
                'formElementClass' => 'column'
            ));
            $this->form->addField('max_communication_rank', 'dropdown', array(
                'options' => $this->maxRoleRankNames,
                'value' => $this->currentMaxRoleRankName,
                'required' => true,
                'hideNull' => true,
                'displayName' => Lang::getString('highest') . ' ' . Lang::getString('communication') . ' ' . Lang::getString('role') . ' ' . Lang::getString('group'),
                'formElementClass' => 'column'
            ));
        $this->form->addHTML('</div>');
        
        if ($this->editableBefore) {
            $this->form->addField('options', 'checkbox', array(
                'options' => array('editable' => 'Editable'),
                'value' => 'editable',
                'displayName' => Lang::getString('options')
            ));
        } else {
            $this->form->addField('options', 'checkbox', array(
                'options' => array('editable' => 'Editable'),
                'displayName' => Lang::getString('options')
            ));
        }
        $this->form->addHTML('<hr/>');
        $this->form->addField('permissions', 'checkbox', array(
           // 'options' => $this->permissionOptions,
            'optionGroups'=>$this->permissionOptions,
            'value' => $this->selectedPermissions,
            'displayName' => Lang::getString('permissions'),
            'formElementClass'=>'list_options column_groups zippable'
        ));
        
        $this->form->addHTML('<span class="submit_btn" title="Save" tabindex="0">'.Lang::getString('submit').'</span>');
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->addHTML($this->form->getForm(Lang::getString('submit')));
        $this->closePaddingWrap();
    }

}
