<?php
/**
 * Description of AbstractRoleDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractRoleDetailView extends MainWindowView {

    /**
     * @var AbstractRole
     */
    protected $role;

    public function __construct(AbstractRole $role) {
        parent::__construct();
        $this->role = $role;
        $this->addSiteTitle('Role');
        $title = $this->role->getTitle();
        $this->setWindowTitle($title);
        $this->addSiteTitle($title);
        $roleGroup = $this->role->getRoleGroup();
        $listBarURL = $roleGroup->getListBarURL();
        $this->setListBarURL($listBarURL);
    }
    
    protected function sortPermissionsIntoCategories($permissions){
        $permissionCategories = array();
        foreach ($permissions as $permission) {
            $category = $permission->getCategory();
            if (empty($category)) {
                $categoryKey = 'core';
            } else {
                $categoryKey = $category->getProperty('ref');
            }
            if (!isset($permissionCategories[$categoryKey])) {
                $permissionCategories[$categoryKey] = array(
                    'category' => $category,
                    'permissions' => array()
                );
            }
            $permissionCategories[$categoryKey]['permissions'][$permission->getProperty('ref')] = $permission; 
        }
        return $permissionCategories;
    }
    
    protected function addWindowBtns(){
        $this->addEditBtn();
    }
    
    protected function addEditBtn(){
        if(Permission::verifyByRef('edit_roles')){
            $editURL = $this->role->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="" class="custom_btn" >' . GI_StringUtils::getIcon('edit') . '</span><span class="btn_text">Edit</span></a>');
        }
    }
    
    protected function addRoleGroupInfo(){
        $roleGroup = $this->role->getRoleGroup();
        $maxCommRoleGroup = $this->role->getHighestCommRoleGroup();
        if (!empty($roleGroup)) {
            $roleGroupTitle = $roleGroup->getProperty('title');
            $roleGroupRank = $roleGroup->getProperty('rank');
            $this->addHTML('<div class="columns halves">')
                    ->addHTML('<div class="column">');
            $this->addContentBlock($roleGroupTitle . ' (Rank: ' . $roleGroupRank . ')', 'Role Group');
            $this->addHTML('</div>');
            if (!empty($maxCommRoleGroup)) {
                $this->addHTML('<div class="column">');
                $this->addContentBlock($maxCommRoleGroup->getProperty('title') . ' (Rank: ' . $maxCommRoleGroup->getProperty('rank') . ')', 'Highest Communication Role Group');
                $this->addHTML('</div>');
            }
            $this->addHTML('</div>');
        }
    }
    
    protected function addPermissionList(){
        $allPermissions = $this->role->getPermissions();
        if (!empty($allPermissions)) {
            $sortedCategories = $this->sortPermissionsIntoCategories($allPermissions);
            $this->addHTML('<hr/>');
            $this->addHTML('<h3>Permissions</h3>');
            $this->addHTML('<div class="auto_columns quarters">');
            foreach($sortedCategories as $cateogryInfo){
                $category = $cateogryInfo['category'];
                $permissions = $cateogryInfo['permissions'];
                if($category){
                    $this->addHTML('<div class="permission_category">');
                    $this->addHTML('<h4>' . $category->getTitle() . '</h4>');
                }
                $this->addHTML('<ul class="simple_list permission_list">');
                foreach ($permissions as $permission) {
                    $permissionTitle = $permission->getProperty('title');
                    $this->addHTML('<li>' . $permissionTitle . '</li>');
                }
                $this->addHTML('</ul>');
                if($category){
                    $this->addHTML('</div>');
                }
            }
            $this->addHTML('</div>');
            
            $this->addHTML('</div>');
        }
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        
        $this->addRoleGroupInfo();
        $this->addPermissionList();
        
        $this->closePaddingWrap();
    }

}