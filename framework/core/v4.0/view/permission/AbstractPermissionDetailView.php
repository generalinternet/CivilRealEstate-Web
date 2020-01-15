<?php
/**
 * Description of AbstractPermissionDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractPermissionDetailView extends MainWindowView {

    /**
     * @var AbstractPermission
     */
    protected $permission;

    public function __construct(AbstractPermission $permission) {
        parent::__construct();
        $this->permission = $permission;
        $this->addSiteTitle('Permission');
        $this->addSiteTitle($this->permission->getProperty('title'));
        $title = $this->permission->getProperty('title');
        $this->setWindowTitle('Permission <span class="thin">' . $title.'</span>');
        $listBarURL = $permission->getListBarURL();
        $this->setListBarURL($listBarURL);
    }

    protected function addEditBtn(){
        $editURL = $this->permission->getEditURL();
        $this->addHTML('<a href="' . $editURL . '" title="" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
    }
    
    protected function addWindowBtns(){
        $this->addEditBtn();
    }
    
    public function addViewBodyContent() {
        $roles = $this->permission->getRoles();
        $this->addContentBlockTitle('Roles');
        if (!empty($roles)) {
            $this->addHTML('<ul class="inline_blocks">');
            foreach ($roles as $role) {
                $viewRoleURL = $role->getViewURL();
                $title = $role->getProperty('title');
                $this->addHTML('<li class="linked"><a href="'.$viewRoleURL.'" title="View Role">'.$title.'</a></li>');
            }
            $this->addHTML('</ul>');
        }
    }

}
