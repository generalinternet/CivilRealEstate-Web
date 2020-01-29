<?php
/**
 * Description of AbstractRoleGroupDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractRoleGroupDetailView extends MainWindowView {

    /** @var AbstractRoleGroup */
    protected $roleGroup;

    public function __construct(AbstractRoleGroup $roleGroup) {
        parent::__construct();
        $this->roleGroup = $roleGroup;
        $this->addSiteTitle('Role Group');
        $title = $this->roleGroup->getProperty('title');
        $this->addSiteTitle($title);
        $this->setListBarURL(GI_URLUtils::buildURL(array(
            'controller' => 'role',
            'action' => 'index'
        )));
        $rank = $this->roleGroup->getProperty('rank');
        $this->setWindowTitle($title . ' <span class="thin">' . Lang::getString('rank') . ' ' . $rank . '</span>');
        $listBarURL = $this->roleGroup->getListBarURL();
        $this->setListBarURL($listBarURL);
    }
    
    protected function addWindowBtns(){
        $this->addAddRoleBtn();
        $this->addEditRoleRankBtn();
    }
    
    protected function addAddRoleBtn(){
        if(Permission::verifyByRef('add_roles')){
            $addRoleURL = GI_URLUtils::buildURL(array(
                'controller'=>'role',
                'action'=>'add',
                'groupId'=>$this->roleGroup->getProperty('id'),
            ));
            $this->addHTML('<a href="' . $addRoleURL . '" title="' . Lang::getString('add_role') . '" class="custom_btn" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text">' . Lang::getString('add_role') . '</span></a>');
        }
    }
    
    protected function addEditRoleRankBtn(){
        if(Permission::verifyByRef('edit_role_ranks')){
            $editURL = $this->roleGroup->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="" class="custom_btn open_modal_form" ' . Lang::getString('edit_role_group') . ' >' . GI_StringUtils::getIcon('edit') . '<span class="btn_text">' . Lang::getString('edit') . '</span></a>');
        }
    }
    
    protected function addRolesSection(){
        $roles = $this->roleGroup->getRoles();
        $this->addContentBlockTitle('Roles');
        if (!empty($roles)) {
            $this->addHTML('<div class="auto_columns">');
            foreach ($roles as $role) {
                $viewRoleURL = $role->getViewURL();
                $title = $role->getProperty('title');
                $this->addHTML('<div class="role_block"><a href="'.$viewRoleURL.'" class="content_block ajax_link" title="View Role">'.$title.'</a></div>');
            }
            $this->addHTML('</div>');
        } else {
            $this->addHTML('<p>No roles found.</p>');
        }
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        
        $this->addRolesSection();
        
        $this->closePaddingWrap();
    }

}
