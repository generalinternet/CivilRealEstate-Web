<?php
/**
 * Description of AbstractRole
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractRole extends GI_Model {
    
    protected $permissionModels = NULL;
    protected $permissionsByRef = NULL;
    /** @var AbstractRoleRank */
    protected $roleGroup = NULL;
    protected $highestCommRoleGroup = NULL;

    public function getTitle(){
        return $this->getProperty('title');
    }
    
    /**
     * Builds an array of role names
     * 
     * @param string $type
     * @return array an array of role title by system title key
     */
    public static function buildRoleNamesArray($type = 'self') {
        $userHighestRoleRank = RoleGroup::getUserHighestRoleGroupRank($type);
        $roleRankModels = RoleGroupFactory::search()
                ->filterLessOrEqualTo('rank', $userHighestRoleRank)
                ->select();
        $roleArray = array();
        if (!empty($roleRankModels)) {
            foreach ($roleRankModels as $roleRank) {
                $roleRankId = $roleRank->getProperty('id');
                $roleModelArray = Role::getByProperties(array(
                            'role_rank' => $roleRankId
                ));
                if (sizeof($roleModelArray) > 0) {
                    array_push($roleArray, $roleModelArray[0]);
                }
            }
        }
        $namesArray = array();
        if (!empty($roleArray)) {
            foreach ($roleArray as $role) {
                $title = $role->getProperty('title');
                $systemTitle = static::buildSystemTitle($title);
                $namesArray[$systemTitle] = $title;
            }
        }
        return $namesArray;
    }

    /**
     * Builds an array of role options
     * 
     * @param string $type
     * @return array an array of role title by id key
     */
    public static function buildRoleOptions($type = 'self'){
        $roleOptions = RoleFactory::buildRoleOptions($type);
        return $roleOptions;
    }

    /**
     * Handles linked permissions
     * 
     * @param array $submittedPermissionRefs
     * @return boolean
     */
    public function handleLinkedPermissionsFromFormSubmission($submittedPermissionRefs) {
        $existingPermissions = PermissionFactory::getPermissionsByRole($this);
        foreach ($existingPermissions as $key => $permission) {
            $permissionRef = $permission->getProperty('ref');
            $index = array_search($permissionRef, $submittedPermissionRefs);
            if (!empty($index)) {
                unset($submittedPermissionRefs[$index]);
                unset($existingPermissions[$key]);
            }
        }
        foreach ($submittedPermissionRefs as $index => $submittedPermissionRef) {
            $permissionArray = PermissionFactory::search()
                    ->filter('ref', $submittedPermissionRef)
                    ->select();
            if (!empty($permissionArray)) {
                $permission = $permissionArray[0];
                $linkResult = PermissionFactory::linkPermissionToRole($permission, $this);
                if (!$linkResult) {
                    return false;
                }
            }
        }
        foreach ($existingPermissions as $permissionToUnlink) {
            //unlink the permission
            $unlinkResult = PermissionFactory::unlinkPermissionFromRole($permissionToUnlink, $this);
            if (!$unlinkResult) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets role group's name
     * @return string role group's name. '' if there is no role group
     */
    public function getRoleGroupName() {
        $roleGroup = $this->getRoleGroup();
        if (!empty($roleGroup)) {
            return $roleGroup->getProperty('title');
        }
        return '';
    }
    
    public function setRoleGroup($roleGroup){
        $this->roleGroup = $roleGroup;
        if($roleGroup){
            $this->setProperty('role_rank', $roleGroup->getId());
        }
        return $this;
    }
    
    /**
     * Gets role group
     * 
     * @return RoleGroup 
     */
    public function getRoleGroup() {
        if (empty($this->roleGroup)) {
            $this->roleGroup = RoleGroupFactory::getRoleGroupByRole($this);
        }
        return $this->roleGroup;
    }
    
    public function getViewURLAttrs() {
        return array(
            'controller' => 'role',
            'action' => 'view',
            'roleId' => $this->getId()
        );
    }

    /**
     * Gets edit URL
     * 
     * @return string URL
     */
    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
                    'controller' => 'role',
                    'action' => 'edit',
                    'roleId' => $this->getProperty('id'),
                    'type'=>$this->getTypeRef(),
        ));
    }

    /**
     * Gets permissions
     * 
     * @return AbstractPermission[] an array of Permission DAOs
     */
    public function getPermissions() {
        if (empty($this->permissionModels)) {
            $this->permissionModels = PermissionFactory::getPermissionsByRole($this);
        }
        return $this->permissionModels;
    }

    /**
     * Form submit handler
     * 
     * @param GI_Form $form
     * @return AbstractRole. NULL if not submitted or failed to save
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $title = filter_input(INPUT_POST, 'title');
            if (!empty($title)) {
                $options = filter_input(INPUT_POST, 'options');
                if (!empty($options)) {
                    $editable = 1;
                } else {
                    $editable = 0;
                }
                $perms = filter_input(INPUT_POST, 'permissions', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                if (empty($perms)) {
                    $perms = array();
                }
                $this->setProperty('title', $title);
                $this->setProperty('editable', $editable);
                $roleRank = filter_input(INPUT_POST, 'role_rank');
                $roleRankModelArray = RoleGroupFactory::search()
                        ->filter('system_title', $roleRank)
                        ->select();
                if (empty($roleRankModelArray)) {
                    return NULL;
                }
                $roleRankModel = $roleRankModelArray[0];
                $this->setProperty('role_rank', $roleRankModel->getProperty('id'));
                $maxRefRoleRank = filter_input(INPUT_POST, 'max_communication_rank');
                $maxRoleRankArray = RoleGroupFactory::search()
                        ->filter('system_title', $maxRefRoleRank)
                        ->select();
                if (empty($maxRoleRankArray)) {
                    GI_URLUtils::redirectToError(4000);
                }
                $maxRefRoleRankModel = $maxRoleRankArray[0];
                $this->setProperty('max_ref_role_rank', $maxRefRoleRankModel->getProperty('id'));
                if ($this->save()) {
                    $permissions = array();
                    foreach ($perms as $ref) {
                        $permArray = PermissionFactory::search()
                                ->filter('ref', $ref)
                                ->select();
                        if (!empty($permArray)) {
                            $permissions[] = $permArray[0];
                        }
                    }
                    if (PermissionFactory::adjustRolePermissions($this, $permissions)) {
                        return $this;
                    }
                }
            }
        }
        return NULL;
    }

    /**
     * Gets a form view
     * 
     * @param GI_Form $form
     * @param array[key=>value] $permissions option check box's values
     * @param array[key=>value] $roleRankNames role rank drop down menu's values
     * @param array[key=>value] $maxRoleRankNames max communication rank drop down menu's values
     * @param GI_Model $roleGroup
     * @return GI_View RoleFormView
     */
    public function getFormView(GI_Form $form, $permissions, $roleRankNames, $maxRoleRankNames, $roleGroup = NULL) {
        if($this->getProperty('id')){
            $filteredPermissions = PermissionFactory::getFilteredRolePermissions($this);
            $preSelectedPermissions = array();
            if (!empty($filteredPermissions)) {
                foreach ($filteredPermissions as $filteredPermission) {
                    $ref = $filteredPermission->getProperty('ref');
                    $preSelectedPermissions[] = $ref;
                }
            }
        } else {
            $preSelectedPermissions = array();
            foreach($permissions as $permission){
                if($permission->getProperty('default_val')){
                    $preSelectedPermissions[] = $permission->getProperty('ref');
                }
            }
        }
        
        if (empty($roleGroup)) {
            $roleGroup = $this->getRoleGroup();
        }
        $currentRankName = NULL;
        if (!empty($roleGroup)) {
            $currentRankName = $roleGroup->getProperty('system_title');
        } 
        $currentMaxRoleRankName = NULL;
        $currentMaxRankId = $this->getProperty('max_ref_role_rank');
        if (!empty($currentMaxRankId)) {
            $currentMaxRoleGroup = RoleGroupFactory::getModelById($currentMaxRankId);
            $currentMaxRoleRankName = $currentMaxRoleGroup->getProperty('system_title');
        }
        
       
        
//        $permissionOptions = array();
//        foreach($permissions as $permission){
//            $label = $permission->getProperty('title');
//            $category = $permission->getCategory();
//            if (!empty($category)) {
//                $label .= ' <b>['.$category->getProperty('title').']</b>';
//            }
//            $permissionOptions[$permission->getProperty('ref')] = $label;
//        }
         $permissionOptionGroups = array();
        foreach ($permissions as $permission) {
            $category = $permission->getCategory();
            if (empty($category)) {
                $categoryKey = 'Core';
            } else {
                $categoryKey = $category->getProperty('title');
            }
            if (!isset($permissionOptionGroups[$categoryKey])) {
                $permissionOptionGroups[$categoryKey] = array();
            }
            $permissionOptionGroups[$categoryKey][$permission->getProperty('ref')] = $permission->getProperty('title'); 
        }
        
        $editableBefore = $this->getProperty('editable');
        //return new RoleFormView($form, $this, $permissionOptions, $preSelectedPermissions, $roleRankNames, $currentRankName, $maxRoleRankNames, $currentMaxRoleRankName, $editableBefore);
        return new RoleFormView($form, $this, $permissionOptionGroups, $preSelectedPermissions, $roleRankNames, $currentRankName, $maxRoleRankNames, $currentMaxRoleRankName, $editableBefore);
    }
    
    /**
     * Gets a detail view
     * 
     * @return RoleDetailView
     */
    public function getDetailView() {
        return new RoleDetailView($this);
    }
    
    /**
     * Gets a view title
     * 
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = false) {
        $title = 'Role';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    /**
     * Get the highest role group
     * 
     * @return AbstractRoleGroup
     */
    public function getHighestCommRoleGroup() {
        if (empty($this->highestCommRoleGroup)) {
            $maxRefRoleRank = $this->getProperty('max_ref_role_rank');
            $this->highestCommRoleGroup = RoleGroupFactory::getModelById($maxRefRoleRank);
        }
        return $this->highestCommRoleGroup;
    }
    
    /**
     * Get the highest role group's name
     * 
     * @return string '' if there is no highest role group
     */
    public function getHighestCommRoleGroupName() {
        $highestCommRoleGroup = $this->getHighestCommRoleGroup();
        if (!empty($highestCommRoleGroup)) {
            return $highestCommRoleGroup->getProperty('title');
        }
        return '';
    }

    public function hasPermission($permissionRef) {
        if (isset($this->persmissionsByRef[$permissionRef])) {
            return $this->persmissionsByRef[$permissionRef];
        }
        return NULL;
    }

    /**
     * @param String $permissionRef
     * @param Boolean $hasPermission
     */
    public function setRoleHasPermission($permissionRef, $hasPermission = false) {
        $this->persmissionsByRef[$permissionRef] = $hasPermission;
        $this->cacheRoleHasPermission($permissionRef, $hasPermission);
    }

    protected function cacheRoleHasPermission($permissionRef, $hasPermission = false) {
        if (empty($this->getId())) {
            return;
        }
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_role_perms')) {
            $allRolePerms = apcu_fetch($keyPrefix . '_role_perms');
        } else {
            $allRolePerms = array();
        }
        if (isset($allRolePerms[$this->getId()])) {
            $rolePerms = $allRolePerms[$this->getId()];
        } else {
            $rolePerms = array();
        }
        if ($hasPermission) {
            $rolePerms[$permissionRef] = 1;
        } else {
            $rolePerms[$permissionRef] = 0;
        }
        $allRolePerms[$this->getId()] = $rolePerms;
        apcu_store($keyPrefix . '_role_perms', $allRolePerms, APCU_TTL);
    }

    public function clearCachedPermissions() {
        if (empty($this->getId())) {
            return true;
        }
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_role_perms')) {
            $allRolePerms = apcu_fetch($keyPrefix . '_role_perms');
        } else {
            return true;
        }
        if (isset($allRolePerms[$this->getId()])) {
            $allRolePerms[$this->getId()] = array();
            if (apcu_store($keyPrefix . '_role_perms', $allRolePerms, APCU_TTL)) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }

    /**
     * @param string $term
     * @return array
     */
    public function getAutocompResult($term = NULL) {
        $title = $this->getProperty('title');
        $autoResultTitle = GI_StringUtils::markTerm($term, $title);
       // $typeTitle = $this->getTypeTitle();
        $autoResult = '<span class="result_text">';
        $autoResult .= '<span class="inline_block">';
        $autoResult .= $autoResultTitle;
        //$autoResult .= '<span class="sub">' . $typeTitle . '</span>';
        $autoResult .= '</span>';
        $autoResult .= '</span>';
        $result = array(
            'label' => $title,
            'value' => $this->getId(),
            'autoResult' => $autoResult
        );
        return $result;
    }

}
