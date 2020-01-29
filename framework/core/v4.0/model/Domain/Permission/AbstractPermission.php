<?php
/**
 * Description of AbstractPermission
 * Place methods here that will be part of the module, and used for all applications
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
abstract class AbstractPermission extends GI_Model{
    
    protected $roles = NULL;
    protected static $selectRowFieldName = 'permission';
    protected $permissionCategory = NULL;

    /**
     * Verifies permission by $ref
     * 
     * @param string $ref
     * @param int $userId
     * @return boolean false if there is no permission
     */
    public static function verifyByRef($ref, $userId=NULL) {
        if (!$userId) {
            $userId = Login::getUserId();
        }
        $user = UserFactory::getModelById($userId);
        if (empty($user)) {
            return false;
        }
        $permission = PermissionFactory::getModelByRef($ref);
        if (!$permission) {
            return false;
        }
        
        if (static::verifyByRefAndUserFromCache($ref, $user) || PermissionFactory::isPermissionLinkedToUser($permission, $user)) {
            return true;
        }
        $roles = $user->getRoles();
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $cachedRoleStatus = static::verifyByRefAndRoleFromCache($ref, $role);
                if (!is_null($cachedRoleStatus)) {
                    return $cachedRoleStatus;
                }
                $permLinkedToRoleStatus = PermissionFactory::isPermissionLinkedToRole($permission, $role);
                if ($permLinkedToRoleStatus) {
                    return true;
                }
            }
        }

        return false;
    }
    
    protected static function verifyByRefAndUserFromCache($ref, AbstractUser $user) {
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_user_perms')) {
            $allUserPerms = apcu_fetch($keyPrefix . '_user_perms');
            if (!empty($allUserPerms)) {
                if (isset($allUserPerms[$user->getId()])) {
                    $userPerms = $allUserPerms[$user->getId()];
                    if (isset($userPerms[$ref])) {
                        $value = $userPerms[$ref];
                        if (!empty($value)) {
                            return true;
                        }
                    }
                }
            }
        }
        return NULL;
    }

    protected static function verifyByRefAndRoleFromCache($ref, AbstractRole $role) {
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_role_perms')) {
            $allRolePerms = apcu_fetch($keyPrefix . '_role_perms');
            if (!empty($allRolePerms)) {
                $roleId = $role->getId();
                if (empty($roleId)) {
                    return NULL;
                }
                if (isset($allRolePerms[$roleId])) {
                    $rolePerms = $allRolePerms[$roleId];
                    if (isset($rolePerms[$ref])) {
                        $value = $rolePerms[$ref];
                        if (!is_null($value)) {
                            if ($value == 1) {
                                return true;
                            } else if ($value == 0) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return NULL;
    }
    
    public static function getUITableCols() {
        $tableColArrays = array(
            //Title
            array(
                'header_title' => 'Title',
                'method_attributes' => 'title',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => ''
            ),
            //Rank
            array(
                'header_title' => 'Ref',
                'method_attributes' => 'ref',
            ),
            //Rank
            array(
                'header_title' => 'Core',
                'method_name' => 'isCore',
                'method_attributes' => true,
                'css_header_class' => '',
                'css_class' => ''
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Title
            array(
                'method_attributes' => 'title',
            ),
            //Ref
            array(
                'method_attributes' => 'ref',
            ),
            //Core
            array(
                'method_name' => 'isCore',
                'method_attributes' => false,
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    /**
     * @return boolean
     */
    public function isCore($returnString = false){
        $isCore = false;
        if($this->getProperty('is_core')){
            $isCore = true;
        }
        if($returnString){
            if($isCore){
                return '<span class="icon check gray"></span>';
            } else {
                return '<span class="icon remove gray"></span>';
            }
        }
        return $isCore;
    }
    
    public function getViewURLAttrs() {
        return array(
            'controller' => 'permission',
            'action' => 'view',
            'id' => $this->getProperty('id')
        );
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
            'controller' => 'permission',
            'action' => 'edit',
            'id' => $this->getProperty('id')
        ));
    }

    /**
     * @param GI_Form $form
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $newPermission = false;
            if(empty($this->getProperty('id'))){
                $newPermission = true;
            }
            $title = filter_input(INPUT_POST, 'title');
            $ref = filter_input(INPUT_POST, 'ref');
            $defaultVal = filter_input(INPUT_POST, 'default_val');
            $categoryId = filter_input(INPUT_POST, 'permission_category_id');
            
            $cleanRef = GI_Sanitize::ref($ref, $reasonCleaned);
            if($cleanRef != $ref){
                $form->addFieldError('ref', 'unclean', 'Provided reference ' . $reasonCleaned . '.');
                return false;
            }
            
            $existingRefSearch = PermissionFactory::search()
                    ->filter('ref', $cleanRef);
            if(!empty($this->getProperty('id'))){
                $existingRefSearch->filterNotEqualTo('id', $this->getProperty('id'));
            }
            $existingRef = $existingRefSearch->select();
            if(!empty($existingRef)){
                $form->addFieldError('ref', 'not_unique', 'Provided reference has already been used.');
                return false;
            }
            
            $this->setProperty('title', $title);
            $this->setProperty('ref', $cleanRef);
            $this->setProperty('default_val', $defaultVal);
            $this->setProperty('permission_category_id', $categoryId);
            if($this->save()){
                PermissionFactory::linkPermissionToRoleByRoleGroupRef($cleanRef, 'gi_bos_administrator');
                if($newPermission){
                    $editPermissionRef = 'add_permissions';
                } else {
                    $editPermissionRef = 'edit_permissions';
                }
                $editPermission = PermissionFactory::getModelByRef($editPermissionRef);
                
                $myRoles = RoleFactory::getRolesByPermission($editPermission, Login::getUser());
                foreach($myRoles as $myRole){
                    PermissionFactory::linkPermissionToRole($this, $myRole);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param GI_Form $form
     * @return PermissionFormView
     */
    public function getFormView(GI_Form $form) {
        return new PermissionFormView($form, $this);
    }
    
    /**
     * @return PermissionDetailView
     */
    public function getDetailView() {
        return new PermissionDetailView($this);
    }
    
    public function getViewTitle($plural = false) {
        $title = 'Permission';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getBreadcrumbs() {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'permission',
            'action' => 'index'
        ));
        $breadcrumbs[] = array(
            'label' => 'Permissions',
            'link' => $bcIndexLink
        );
        $permissionId = $this->getProperty('id');
        if (!is_null($permissionId)) {
            $breadcrumbs[] = array(
                'label' => $this->getProperty('title'),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    public function getRoles() {
        if (empty($this->roles)) {
            $this->roles = RoleFactory::getRolesByPermission($this);
        }
        return $this->roles;
    }
    
    public function getCategory() {
        if (empty($this->permissionCategory)) {
            $this->permissionCategory = PermissionCategoryFactory::getModelById($this->getProperty('permission_category_id'));
        }
        return $this->permissionCategory;
    }
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getRef(){
        return $this->getProperty('ref');
    }
    
    public function getDescription(){
        //@todo add permission descriptions
        return $this->getProperty('description');
    }
    
    public function getCategoryTitle(){
        $category = $this->getCategory();
        if($category){
            return $category->getTitle();
        }
        return NULL;
    }
    
    public function getAutocompResult($term = NULL){
        $permissionTitle = $this->getTitle();
        $autoResult = '<span class="result_text">' . GI_StringUtils::markTerm($term, $permissionTitle) . '</span>';
        $result = array(
            'label' => $permissionTitle,
            'value' => $this->getId(),
            'autoResult' => $autoResult
        );
        return $result;
    }
    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        return $dataSearch;
    }
    
    public function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $dataSearch->orderBy('title', 'ASC');
        return $dataSearch;
    }
    
    public function getListBarURLAttrs(){
        $attrs = array(
            'controller' => 'permission',
            'action' => 'index',
            'curId' => $this->getId()
        );
        $typeRef = $this->getTypeRef();
        if($typeRef){
            $attrs['type'] = $typeRef;
        }
        return $attrs;
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setTitle($this->getTitle());
        $cardView->setSummary($this->getDescription());
        $cardView->setSubtitle($this->getRef());
        $cardView->setTopRight($this->getCategoryTitle());
        return $cardView;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \PermissionSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new PermissionSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * Gets a search form view
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type redirection type
     * @return GI_View PermissionSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch = NULL, $type = NULL, &$redirectArray = array()) {
        $form = new GI_Form('permission_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            $redirectArray = array(
                'controller' => 'permission',
                'action' => 'index',
                'targetId' => 'list_bar',
                'queryId' => $queryId
            );
            if(!empty($type)){
                $redirectArray['type'] = $type;
            }
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlTargetId'] = 'list_bar';
                    $redirectArray['jqueryAction'] = 'clearMainPanel();';
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addBasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            //Advanced Search
            $term = $dataSearch->getSearchValue('term');
            if(!empty($term)){
                static::addTermFilterToDataSearch($term, $dataSearch);
            }
            
            $categoryId = $dataSearch->getSearchValue('category_id');
            if(!empty($categoryId) && $categoryId != 'NULL'){
                static::addCategoryFilterToDataSearch($categoryId, $dataSearch);
            }
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $dataSearch->clearSearchValues();
            $searchType = filter_input(INPUT_POST, 'search_type');
            if (empty($searchType) || $searchType === 'basic') {
                $dataSearch->setSearchValue('search_type', 'basic');
                $basicSearchField = filter_input(INPUT_POST, 'basic_search_field');
                $dataSearch->setSearchValue('basic_search_field', $basicSearchField);
            } else {
                $dataSearch->setSearchValue('search_type', 'advanced');
                //SEARCH BY NAME
                $term = filter_input(INPUT_POST, 'search_term'); 
                $dataSearch->setSearchValue('term', $term);
                
                //SEARCH BY CATEGORY
                $categoryId = filter_input(INPUT_POST, 'search_category_id'); 
                $dataSearch->setSearchValue('category_id', $categoryId);
            }
        }
        
        return true;
    }
    
    /**
     * Adds a name search text to a data search
     * @param string $termString the text in a name search text field
     * @param GI_DataSearch $dataSearch
     */
    public static function addTermFilterToDataSearch($termString, GI_DataSearch $dataSearch){
        $terms = explode(' ', $termString);
        $dataSearch->filterGroup();
        $columns = array(
            'title',
            'ref'
        );
        $dataSearch->filterTermsLike($columns, $terms);
        $dataSearch->orderByLikeScore($columns, $terms);
    }
    
    /**
     * Adds a name search text to a data search
     * @param string $categoryId the text in a name search text field
     * @param GI_DataSearch $dataSearch
     */
    public static function addCategoryFilterToDataSearch($categoryId, GI_DataSearch $dataSearch){
        $dataSearch->filter('permission_category_id', $categoryId);
    }
    
    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        static::addTermFilterToDataSearch($basicSearchField, $dataSearch);
    }
    
    public function getCatalogItemView(){
        $view = new GenericView();
        $view->addHTML('<div class="catalog_item">');
        $view->addHTML('<h3>' . $this->getTitle() . '</h3>');
        $view->addHTML('</div>');
        return $view;
    }
    
}
