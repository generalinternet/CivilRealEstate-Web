<?php
/**
 * Description of AbstractUser
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.1.1
 */
abstract class AbstractUser extends GI_Model {
    
    /** @var AbstractRole[] */
    protected $roles = NULL;
    protected $permissionModels = NULL;
    protected $persmissionsByRef = NULL;

    /** @var AbstractSettingsNotif[] */
    protected $settingsNotifByRef = array();

    /** @var AbstractInterfacePerspective */
    protected $currentInterfacePerspective;

    /** @var AbstractContact */
    protected $contact = NULL;
    /** @var AbstractContactCat */
    protected $contactCat = NULL;
    /** @var AbstractContactOrg*/
    protected $contactOrg = NULL;
    
    protected $isSuspended = NULL;

    public function getTypeTitle() {
        return 'User';
    }
    
    /**
     * Generates random salt
     * 
     * @return string
     */
    public function generateSalt() {
        $salt = GI_StringUtils::generateRandomString(8, false, true, true, true, 2, 1);
        return $salt;
    }

    /**
     * Generates salted password
     * 
     * @param string $password
     * @param string $seasoning
     * @return string
     */
    public function generateSaltyPass($password, $seasoning) {
        $saltyPass = hash('sha512', $password . $seasoning);
        return $saltyPass;
    }
    
    /**
     * Gets a user's avatar view
     * 
     * @param string $type
     * @return GI_View
     */
    public function getUserAvatarView($type = 'avatar') {
        $ppFolder = $this->getSubFolderByRef('profile_pictures');
        //avatar_tests
        $phv = NULL;
        if(false){
            $phv = new FileAvatarPlaceholderView($this);
        }
        if(!$ppFolder){
            return $phv;
        }
        $files = FolderFactory::getFiles($ppFolder);
        if (!empty($files)) {
            $file = $files[0];
            /* @var $file File */
            $avatarView = $file->getView($type);
            return $avatarView;
        }
        return $phv;
    }
    
    public function getUserAvatarHTML($width = NULL, $height = NULL){
        $avatarView = $this->getUserAvatarView();
        if($avatarView){
            if(!is_null($width) && !is_null($height)){
                $avatarView->setSize($width, $height);
            }
            return '<span class="avatar_wrap inline_block has_img">'.$avatarView->getHTMLView().'</span>';
        } else {
            $avatar = $this->getAvatarPlaceHolderHTML($width, $height);
            return $avatar;
        }
    }
    
    public function getFolder($createIfMissing = true) {
        $userRootFolder = FolderFactory::getUserRootFolder($this);
        return $userRootFolder;
    }
    
    public function getSubFolderByRef($ref, $newSubFolderProperties = array()) {
        if(empty($this->getId())){
            return NULL;
        }
        if($ref == 'profile_pictures'){
            $userRootFolder = FolderFactory::getUserRootFolder($this);
            if (empty($userRootFolder)) {
                return NULL;
            }
            $ppFolder = $userRootFolder->getProfilePictureFolder();
            if ($ppFolder) {
                return $ppFolder;
            }
        } else {
            return parent::getSubFolderByRef($ref, $newSubFolderProperties);
        }
    }
    
    /**
     * Gets user's full name
     * 
     * @return string
     */
    public function getFullName(){
        $name = trim($this->getProperty('first_name').' '.$this->getProperty('last_name'));
        return $name;
    }
    
    /**
     * Gets the number of notifications
     * 
     * @param boolean $unreadOnly
     * @return int
     */
    public function getNotificationCount($unreadOnly = true){
        $dataSearch = NotificationFactory::search()
                ->filter('to_id', $this->getId())
                ->filter('in_system', 1);
        if($unreadOnly){
            $dataSearch->filter('viewed', 0);
        }
        $notificationCount = $dataSearch->count();
        return $notificationCount;
    }

    /**
     * Gets a view title
     * 
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = Lang::getString('user');
        if ($plural) {
            $title = Lang::getString('users');
        }
        return $title;
    }
    
    /**
     * Gets breadcrumbs
     * 
     * @return array[key=>value]
     */
    public function getBreadcrumbs() {
        $breadcrumbs = array();
        if(Permission::verifyByRef('view_users')){
            $usersUrl = GI_URLUtils::buildURL(array(
                'controller' => 'user',
                'action' => 'index',
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $usersUrl
            );
        } else {
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle()
            );
        }
        $userId = $this->getId();
        if (!is_null($userId)) {
            $breadcrumbs[] = array(
                'label' => $this->getFullName(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    /**
     * Gets a detail view
     * 
     * @return GI_View
     */
    public function getDetailView() {
        return new UserDetailView($this);
    }

    public function getViewURLAttrs() {
        return array(
            'controller' => 'user',
            'action' => 'view',
            'id' => $this->getId(),
        );
    }

    /**
     * Gets edit URL
     * 
     * @return string URL
     */
    public function getEditURL() {
        $id = $this->getId();
        $url = GI_URLUtils::buildURL(array(
                    'controller' => 'user',
                    'action' => 'edit',
                    'id' => $id
        ));
        return $url;
    }
    
    /**
     * Gets delete URL
     * 
     * @return string URL
     */
    public function getDeleteURL() {
        $id = $this->getId();
        $url = GI_URLUtils::buildURL(array(
            'controller'=>'user',
            'action'=>'delete',
            'id'=>$id,
        ));
        return $url;
    }

    /**
     * Gets main user role
     * 
     * @return AbstractRole
     */
    public function getRole() {
        $roles = $this->getRoles();
        if($roles){
            return $roles[0];
        }
        return NULL;
    }
    
    /**
     * Gets roles
     * 
     * @return string the string of roles separated by ','. '--' if there is no roles
     */
    public function getRolesTitleString(){
        $roles = $this->getRoles();
        if(!empty($roles)){
            $roleTitles = array();
            foreach($roles as $role){
                $roleTitles[] = $role->getTitle();
            }
            return implode(', ', $roleTitles);
        }
        return '--';
    }
    
    /** @return AbstractLogin */
    public function getLastLogin() {
        $id = $this->getId();
        $logDate = NULL;
        $lastLogin= NULL;
        $lastLogins = LoginFactory::search()
                ->filter('user_id', $id)
                ->orderBy('last_mod', 'DESC')
                ->setItemsPerPage(1)
                ->select();
        if($lastLogins){
            $lastLogin = $lastLogins[0];
        } else {
            $userLogins = Login_Audit::getModelsBySearchParams(array(
                'user_id' => array(
                    'comp' => '=',
                    'val' => $id
                )
            ),'last_mod DESC');
            if($userLogins){
                $lastLogin = $userLogins[0];
            }
        }
        if($lastLogin){
            return $lastLogin;
        }
        return NULL;
    }
    
    /**
     * Gets last login datetime 
     * 
     * @return string the string of last login datetime. '--' if there is no last login data
     */
    public function getLastLoginString($timeOnly = false){
        $lastLogin = $this->getLastLogin();
        if($lastLogin){
            $logDate = $lastLogin->getProperty('last_mod');
            if ($timeOnly) {
                return GI_Time::formatTimeForDisplay($logDate);
            } else {
                return GI_Time::formatDateTimeForDisplay($logDate);
            }
            
        }
        return '--';
    }
    
    public function getLastActiveSinceString(){
        $lastLogin = $this->getLastLogin();
        if($lastLogin){
            $logDate = $lastLogin->getProperty('last_mod');
            return GI_Time::formatTimeSince($logDate);            
        }
        return '--';
    }
    
    public function getContactCat() {
        if (empty($this->contactCat)) {
            //Get contact cat that is either linked to parent org, or linked to contact directly.
            //UPDATE (4.1.0) - it will only be linked to the parent org
            $search = ContactCatFactory::search();
            $search->setAutoStatus(false);
            $tableName = $search->prefixTableName('contact_cat');
            $orgJoin = $search->createLeftJoin('contact', 'id', $tableName, 'contact_id', 'ORG');
            $orgJoin->filter('ORG.status', 1);
            $search->leftJoin('contact_relationship', 'p_contact_id', 'ORG', 'id', 'REL')
                    ->leftJoin('contact', 'id', 'REL', 'c_contact_id', 'CHILD_IND');
            
            $search->leftJoin('contact', 'id', $tableName, 'contact_id', 'IND');

            
            $search->filterGroup()
                    ->filterGroup()
                    ->andIf()
                    ->filter('CHILD_IND.status', 1)
                    ->filter('CHILD_IND.source_user_id', $this->getId())
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->andIf()
                    ->filter('IND.status',1)
                    ->filter('IND.source_user_id', $this->getId())
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
            
            $search->setPageNumber(1)
                    ->setItemsPerPage(1)
                    ->orderBy('id', 'ASC')
                    ->groupBy('id');
            
            $results = $search->select();
            if (!empty($results)) {
                $this->contactCat = $results[0];
            }
            
           
        }
        return $this->contactCat;
    }

    public function setContactCat($contactCat) {
        $this->contactCat = $contactCat;
        return $this;
    }

    public function getContactOrg() {
        if (empty($this->contactOrg)) {
            $contact = $this->getContact();
            if (empty($contact) || !$contact->isIndividual()) {
                return NULL;
            }
            $this->contactOrg = $contact->getParentContactOrg();
        }
        return $this->contactOrg;
    }

    public static function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        
    }

    /** @param GI_DataSearch $dataSearch */
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $userTable = $dataSearch->prefixTableName('user');
        $dataSearch->createLeftJoin('login', 'user_id', $userTable, 'id', 'L')
                ->filter('L.status', 1);
        $dataSearch->createLeftJoin('login_audit', 'user_id', $userTable, 'id', 'LAUDIT');
        $dataSearch->ignoreStatus('L');
        $dataSearch->ignoreStatus('LAUDIT');
        $dataSearch->orderBy('L.last_mod', 'DESC')
                ->orderBy('LAUDIT.last_mod', 'DESC')
                ->orderBy('first_name', 'ASC')
                ->orderBy('last_name', 'ASC');
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \UserSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new UserSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * Gets a search form view
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type redirection type
     * @return GI_View UserSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch = NULL, $type = NULL, &$redirectArray = array()) {
        $form = new GI_Form('user_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            $redirectArray = array(
                'controller' => 'user',
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
            $name = $dataSearch->getSearchValue('name');
            if(!empty($name)){
                static::addNameFilterToDataSearch($name, $dataSearch);
            }
            
            $email = $dataSearch->getSearchValue('email');
            if(!empty($email)){
                static::addEmailFilterToDataSearch($email, $dataSearch);
            }
            
            $roleId = $dataSearch->getSearchValue('role_id');
            if(!empty($roleId) && $roleId != 'NULL'){
                static::addRoleFilterToDataSearch($roleId, $dataSearch);
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
                $name = filter_input(INPUT_POST, 'search_name'); 
                $dataSearch->setSearchValue('name', $name);

                //SEARCH BY EMAIL
                $email = filter_input(INPUT_POST, 'search_email'); 
                $dataSearch->setSearchValue('email', $email);
                
                //SEARCH BY ROLE
                $roleId = filter_input(INPUT_POST, 'search_role_id'); 
                $dataSearch->setSearchValue('role_id', $roleId);
            }
        }
        
        return true;
    }
    
    /**
     * Adds a name search text to a data search
     * @param string $term the text in a name search text field
     * @param GI_DataSearch $dataSearch
     */
    public static function addNameFilterToDataSearch($term, GI_DataSearch $dataSearch){
        $nameTerms = explode(' ', $term);
        $dataSearch->filterGroup();
        foreach($nameTerms as $nameTerm){
            if ($nameTerm != ''){
                $dataSearch->filterLike('first_name', '%'.$nameTerm.'%')
                    ->orIf()
                    ->filterLike('last_name', '%'.$nameTerm.'%');
            } 
        }
        $dataSearch->closeGroup();
        
        $cases = array();
        if (count($nameTerms) > 1) {
            $firstTerm = $nameTerms[0];
            $cases[] = $dataSearch->newCase()
                        ->filter('first_name', $firstTerm.'%', 'LIKE')
                        ->setThen(3)
                        ->setElse(0);
            $cases[] = $dataSearch->newCase()
                        ->filter('last_name', $firstTerm.'%', 'LIKE')
                        ->setThen(3)
                        ->setElse(0);
        }
        $cases[] = $dataSearch->newCase()
                    ->filter('first_name', $term.'%', 'LIKE')
                    ->setThen(1)
                    ->setElse(0);
        $cases[] = $dataSearch->newCase()
                    ->filter('first_name', $term)
                    ->setThen(3)
                    ->setElse(0);
        $cases[] = $dataSearch->newCase()
                    ->filter('last_name', $term.'%', 'LIKE')
                    ->setThen(1)
                    ->setElse(0);
        $cases[] = $dataSearch->newCase()
                    ->filter('last_name', $term)
                    ->setThen(3)
                    ->setElse(0);
        
        $dataSearch->orderByCase($cases,'DESC');
    }
    
    /**
     * Adds a email search text to a data search
     * 
     * @param string $email the text in a email search text field
     * @param GI_DataSearch $dataSearch
     */
    public static function addEmailFilterToDataSearch($email, GI_DataSearch $dataSearch){
        $dataSearch->filterLike('email', '%'.$email.'%');
        
        $cases = array();
        $cases[] = $dataSearch->newCase()
                    ->filter('email', $email.'%', 'LIKE')
                    ->setThen(1)
                    ->setElse(0);
        $cases[] = $dataSearch->newCase()
                    ->filter('email', $email)
                    ->setThen(3)
                    ->setElse(0);
        
        $dataSearch->orderByCase($cases,'DESC');
    }
    
    public static function addRoleFilterToDataSearch($roleIds, GI_DataSearch $dataSearch){
        if(!is_array($roleIds)){
            $roleIds = explode(',', $roleIds);
        }
        if(!$dataSearch->isJoinedWithTable('ROLE')){
            if(!$dataSearch->isJoinedWithTable('ULTR')){
                $userTable = $dataSearch->prefixTableName('user');
                $dataSearch->join('user_link_to_role', 'user_id', $userTable, 'id', 'ULTR');
            }
            $dataSearch->join('role', 'id', 'ULTR', 'role_id', 'ROLE');
        }
        $dataSearch->filterIn('ROLE.id', $roleIds);
    }
    
    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        $dataSearch->filterGroup()
                ->filterGroup();
        static::addNameFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addEmailFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->closeGroup()
                ->andIf();
    }
    
    /**
     * Gets UI table columns
     * 
     * @return UITableCol[]
     */
    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Avatar',
                'method_name' => 'getUserAvatarHTML',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => 'avatar_cell',
                'css_header_class' => 'avatar_cell'
            ),
            //Name
            array(
                'header_title' => 'Name',
                'method_name' => 'getFullName',
                'cell_url_method_name' => 'getViewURL',
            ),
            //Email
            array(
                'header_title' => 'Email',
                'method_attributes' => 'email',
            ),
            //Role
            array(
                'header_title' => 'Role',
                'method_name' => 'getRolesTitleString',
            ),
            //Last Login
            array(
                'header_title' => 'Last Login',
                'method_name' => 'getLastLoginString',
            ),
        );
        
        if(Permission::verifyByRef('super_admin')){
            $tableColArrays[] = array(
                'header_title' => '',
                'method_name' => 'getUserStatusCell',
                'css_class' => 'circle_status_cell',
                'css_header_class' => 'circle_status_cell'
            );
        }
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Avatar
            array(
                'method_name' => 'getUserAvatarHTML',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => 'avatar_cell',
            ),
            //Name With email address
            array(
                'method_name' => 'getNameWithEmailAddress',
                'cell_url_method_name' => 'getViewURL',
            ),
            //Role
            array(
                'method_name' => 'getRolesTitleString',
                'cell_url_method_name' => 'getViewURL',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setTitle($this->getFullName());
        $cardView->setSummary($this->getEmailAddress());
        $cardView->setSubtitle($this->getRolesTitleString());
        $cardView->setAvatarHTML($this->getUserAvatarHTML());
        $cardView->setTopRight($this->getLastActiveSinceString());
        return $cardView;
    }
    
    protected function getUserStatusCircle($class, $title){
        return '<span class="status_circle ' . $class . '" title="' . $title .'" ></span>';
    }
    
    public function getUserStatusCell(){
        $activeClass = 'red';
        $activeTitle = 'Inactive';
        $lastLogin = $this->getLastLogin();
        if($lastLogin && empty($lastLogin->getProperty('target_id')) && !$lastLogin->isExpired()){
            $activeClass = 'green';
            $activeTitle = 'Active';
        }
        $statusString = $this->getUserStatusCircle($activeClass, $activeTitle);
        
        $passClass = 'green';
        $passTitle = 'Password Okay';
        if($this->getProperty('force_pass_reset')){
            $passClass = 'red';
            $passTitle = 'Password Requires Reset';
        }
        $statusString .= $this->getUserStatusCircle($passClass, $passTitle);
        
        return $statusString;
    }
    
    /**
     * Gets roles
     * 
     * @return AbstractRole[]
     */
    public function getRoles() {
        if (empty($this->roles)) {
            $roles = RoleFactory::getRolesByUser($this);
            if (!empty($roles)) {
                $this->roles = $roles;
            }
        }
        return $this->roles;
    }
    
    public function getEmailAddress(){
        return $this->getProperty('email');
    }
    
    public function getMobileNumber(){
        return $this->getProperty('mobile');
    }
    
    public function getNameWithEmailAddress() {
        $html = '<span class="title">'.$this->getFullName().'</span>';
        $emailAddress = $this->getProperty('email');
        if (!empty($emailAddress)) {
            $html .= '<span class="subtitle">'.$emailAddress.'</span>';
        }
        return $html;
    }
    
    /**
     * Gets permissions
     * 
     * @return AbstractPermission[]
     */
    public function getPermissions() {
        if (empty($this->permissionModels)) {
            $permissions = PermissionFactory::getPermissionsByUser($this);
            if (!empty($permissions)) {
                $this->permissionModels = $permissions;
            }
        }
        return $this->permissionModels;
    }
    
    /**
     * Returns the form a user uses to edit his/her own information
     * @param GI_Form $form
     * @return UserEditView
     */
    public function getEditView(GI_Form $form) {
        return new UserEditView($form, $this);
    }
    
    /**
     * Gets a form view
     * 
     * @param GI_Form $form
     * @param AbstractUser $user
     * @return GI_View UserFormView
     */
    public function getFormView(GI_Form $form) {
        $contactCat = $this->getContactCat();
        $formView = new UserFormView($form, $this);
        if($contactCat){
            $formView->setDefaultContactCatRef($contactCat->getTypeRef());
            if(empty($contactCat->getId())){
                $formView->setShowContactCatField(false);
            }
        }
        return $formView;
    }

    public function validateForm(GI_Form $form){
        $valid = parent::validateForm($form);
        $email = trim(filter_input(INPUT_POST, 'r_email'));
        $userId = $this->getId();
        if(UserFactory::existingEmail($email, $userId)){
            $form->addFieldError('r_email', 'existing', 'This email is already being used by another user.');
            $valid = false;
        }
        $password = filter_input(INPUT_POST, 'new_password');
        $repeatPassword = filter_input(INPUT_POST, 'repeat_password');
        if (!empty($password)) {
            if ($password !== $repeatPassword) {
                $form->addFieldError('repeat_password', 'mismatch', 'You must re-enter the same password as above.');
                $valid = false;
            }
            $reason = '';
            if(!$this->validatePassword($password, $reason)){
                $form->addFieldError('new_password', 'invalid', $reason);
                $valid = false;
            }
        }
        return $valid;
    }
//    
//    /**
//     * Form submit handler
//     * 
//     * @param GI_Form $form
//     * @return boolean. false if not submitted or failed to save
//     */
//    public function handleFormSubmission(GI_Form $form) {
//        if ($this->validateForm($form)) {
//            $roleId = (int) filter_input(INPUT_POST, 'role_id');
//            $email = trim(filter_input(INPUT_POST, 'r_email'));
//
//            $firstName = filter_input(INPUT_POST, 'first_name');
//            $lastName = filter_input(INPUT_POST, 'last_name');
//            
//            $mobile = filter_input(INPUT_POST, 'mobile');
//
//            $language = filter_input(INPUT_POST, 'language');
//            if(empty($language)){
//                $language = 'english';
//            }
//            $updateContactCat = false;
//            $internal = 0;
//            $selectedCategoryType = NULL;
//            if (empty($this->getId())) {
//                $selectedCategoryType = filter_input(INPUT_POST, 'contact_cat_type_ref');
//                //old input field
//                $internal = filter_input(INPUT_POST, 'internal');
//                if (empty($internal)) {
//                    if ($selectedCategoryType === 'internal') {
//                        $internal = 1;
//                    }
//                    //Update contact category only when a new user is added.
//                    $updateContactCat = true;
//                }
//                $sampleContactCat = ContactCatFactory::buildNewModel($selectedCategoryType);
//                if (!empty($sampleContactCat)) {
//                    $this->setPropertiesFromContactCat($sampleContactCat);
//                }
//            } else {
//                $contact = $this->getContact();
//                if (!empty($contact)) {
//                    $internal = $contact->getProperty('internal');
//                }
//            }
//            
//            $password = filter_input(INPUT_POST, 'new_password');
//            $setPassword = false;
//            if (!empty($password)) {
//                $setPassword = true;
//            }
//            if (!$form->fieldErrorCount()) {
//                if (empty($this->getId()) && !is_null($password)) {
//                    $setPassword = true;
//                }
//            } else {
//                return false;
//            }
//            $this->setProperty('first_name', $firstName);
//            $this->setProperty('last_name', $lastName);
//            $this->setProperty('email', $email);
//            if(!is_null($mobile)){
//                $this->setProperty('mobile', $mobile);
//            }
//            $this->setProperty('language', $language);
//            if ($setPassword) {
//                
//                if (empty($password)) {
//                    $password = 'Password1!';
//                }
//                $salt = $this->generateSalt();
//                $this->setProperty('salt', $salt);
//                $pass = $this->generateSaltyPass($password, $salt);
//                $this->setProperty('pass', $pass);
//            }
//            if ($this->save()) {
//                $userId = $this->getId();
//                if ($updateContactCat) {
//                    $this->saveUserAsContact($internal, $form);
//                } else {
//                    $this->saveUserAsContact($internal);
//                }
//                
//                if (empty($userId) || $userId != Login::getUserId()) {
//                    $permissionIds = explode(',', filter_input(INPUT_POST, 'permission_ids'));
//                    $desiredPermissions = array();
//                    foreach($permissionIds as $permissionId){
//                        $permission = PermissionFactory::getModelById($permissionId);
//                        if($permission){
//                            $desiredPermissions[] = $permission;
//                        }
//                    }
//                    PermissionFactory::adjustUserPermissions($this, $desiredPermissions);
//                    if($this->setAndSaveUserRoles($roleId)){
//                        return true;
//                    }
//                    
//                } else {
//                    return true;
//                }
//            }
//        }
//        return false;
//    }

    /**
     * Form submit handler
     * 
     * @param GI_Form $form
     * @return boolean. false if not submitted or failed to save
     * //TODO - refactor this to be handleRegisterFormSubmission - to be used only on user creation
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($this->validateForm($form)) {
            $roleId = (int) filter_input(INPUT_POST, 'role_id');
            $email = trim(filter_input(INPUT_POST, 'r_email'));

            $firstName = trim(filter_input(INPUT_POST, 'first_name'));
            $lastName = trim(filter_input(INPUT_POST, 'last_name'));

            $mobile = filter_input(INPUT_POST, 'mobile');

            $language = filter_input(INPUT_POST, 'language');
            if (empty($language)) {
                $language = 'english';
            }
            $createContact = false;

            if (empty($this->getId())) {
                $createContact = true;
            }


            $password = filter_input(INPUT_POST, 'new_password');
            $setPassword = false;
            if (!empty($password)) {
                $setPassword = true;
            }
            if (!$form->fieldErrorCount()) {
                if (empty($this->getId()) && !is_null($password)) {
                    $setPassword = true;
                }
            } else {
                return false;
            }
            $this->setProperty('first_name', $firstName);
            $this->setProperty('last_name', $lastName);
            $this->setProperty('email', $email);
            if (!is_null($mobile)) {
                $this->setProperty('mobile', $mobile);
            }
            $this->setProperty('language', $language);
            if ($setPassword) {

                if (empty($password)) {
                    $password = 'Password1!';
                }
                $salt = $this->generateSalt();
                $this->setProperty('salt', $salt);
                $pass = $this->generateSaltyPass($password, $salt);
                $this->setProperty('pass', $pass);
            }
            if ($this->save()) {
                $userId = $this->getId();
                $selectedCategoryType = filter_input(INPUT_POST, 'contact_cat_type_ref');
                $sampleContactCat = ContactCatFactory::buildNewModel($selectedCategoryType);
                if (empty($sampleContactCat)) {
                    return $this->softDelete();
                }
                
                $userRole = NULL;
                if(!empty($roleId)){
                    $userRole = RoleFactory::getModelById($roleId);
                }
                if(!$userRole){
                    $userRole = $sampleContactCat->getNewUserDefaultRole();
                }
                
                if (empty($userRole)) {
                    return $this->softDelete();
                }
                if ($createContact) {
                    //TODO - check for parent contact id from form
                    $parentContactId = NULL;
                    if (!$this->createUserContact($selectedCategoryType, $parentContactId)) {
                        return $this->softDelete();
                    }
                }

                if (empty($userId) || $userId != Login::getUserId()) {
                    $permissionIds = explode(',', filter_input(INPUT_POST, 'permission_ids'));
                    $desiredPermissions = array();
                    foreach ($permissionIds as $permissionId) {
                        $permission = PermissionFactory::getModelById($permissionId);
                        if ($permission) {
                            $desiredPermissions[] = $permission;
                        }
                    }
                    PermissionFactory::adjustUserPermissions($this, $desiredPermissions);
                    if ($this->setAndSaveUserRoles($userRole->getId())) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }

//    protected function setPropertiesFromContactCat(AbstractContactCat $contactCat) {
//        //Do nothing
//    }

    public function validatePassword($newPassword, &$reason = ''){
        $badPass = false;
        $curSalt = $this->getProperty('salt');
        $curSaltyPass = $this->getProperty('pass');
        $newSaltyPass = $this->generateSaltyPass($newPassword, $curSalt);
        if($newSaltyPass == $curSaltyPass){
            if(!empty($reason)){
                $reason .= '<br/>';
            }
            $reason .= 'Cannot be the same as the old password.';
            $badPass = true;
        }
        
        if(!GI_StringUtils::validatePassword($newPassword, $reason)){
            $badPass = true;
        }
        
        if($badPass){
            return false;
        }
        return true;
    }
    
    public function setAndSaveUserRoles($roleIds = NULL){
        $curRoles = $this->getRoles();
        if(!empty($curRoles) && empty($roleIds)){
            return true;
        }
        $roles = array();
        if(empty($roleIds)){
            $roleOptions = RoleFactory::getLimitedRoles();
            if(!empty($roleOptions)){
                $roles[] = $roleOptions[0];
            }
        } else {
            if(!is_array($roleIds)){
                $roleIds = explode(',', $roleIds);
            }
            foreach($roleIds as $roleId){
                $role = RoleFactory::getModelById($roleId);
                if($role){
                    $roles[$roleId] = $role;
                }
            }
            
        }
        
        if (!empty($roles)) {
            if($curRoles){
                foreach($curRoles as $curRole){
                    $curRoleId = $curRole->getId();
                    if(!isset($roles[$curRoleId])){
                        if(!RoleFactory::unlinkRoleFromUser($curRole, $this)){
                            return false;
                        }
                    }
                }
            }
            
            foreach($roles as $role){
                $roleLinkResult = RoleFactory::linkRoleToUser($role, $this);
                if (!$roleLinkResult) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function getMaxRefRoleGroup(){
        $roleRankTable = dbConfig::getDbPrefix() . 'role_rank';
        $roleGroups = RoleGroupFactory::search()
                ->join('role', 'max_ref_role_rank', $roleRankTable, 'id', 'r')
                ->join('user_link_to_role', 'role_id', 'r', 'id', 'ultr')
                ->filter('ultr.user_id', $this->getId())
                ->orderBy('rank', 'DESC')
                ->groupBy('id')
                ->select();
        if($roleGroups){
            $roleGroup = $roleGroups[0];
            return $roleGroup;
        }
        return NULL;
    }
    
    public function getMaxRefRoleRank(){
        $roleGroup = $this->getMaxRefRoleGroup();
        if($roleGroup){
            return $roleGroup->getProperty('rank');
        }
        return 0;
    }
    
    public function getIsDeleteable() {
        if($this->getId() != Login::getUserId() && Permission::verifyByRef('delete_users')){
            return true;
        }
        return false;
    }
    
    public function getIsEditable() {
        if(Permission::verifyByRef('edit_users') || $this->getId() == Login::getUserId()){
            return true;
        }
        return false;
    }
    
    public function getIsConfirmed() {
        $confirmed = $this->getProperty('confirmed');
        if (empty($confirmed)) {
            return false;
        }
        return true;
    }
    
    public function sendConfirmEmailAddressEmail($confirmCode = NULL, $devModeEchoMessage = false) {
        if (empty($confirmCode)) {
            $confirmCode = GI_StringUtils::generateRandomString(32, true, true, true, true, false);
        }
        $currentDateTime = GI_Time::getDateTime();
        $this->setProperty('confirm_code', $confirmCode);
        $this->setProperty('confirm_code_sent_date', $currentDateTime);
        if (!$this->save()) {
            return false;
        }
        $url = GI_URLUtils::buildURL(array(
            'controller'=>'login',
            'action'=>'confirmEmail',
            'id'=>  $this->getId(),
            'code'=>$confirmCode,
        ), true);
        $emailView = new GenericEmailView();
        $emailView->addParagraph('Hello ' . $this->getFullName() . ',<br/>You have been added as a user to ' . ProjectConfig::getSiteTitle());
        $emailView->startParagraph()
                ->addHTML('Please click ')
                ->addLink('here', $url, false)
                ->addHTML(' to confirm your email and select a password.')
                ->closeParagraph();       
        $giEmail = new GI_Email();
        $giEmail->addMandrillTag('confirm-email');
        $giEmail->addTo($this->getProperty('email'), $this->getFullName())
                ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                ->setSubject('Confirm Email')
                ->useEmailView($emailView);
        if (DEV_MODE && $devModeEchoMessage) {
            echo $giEmail->getBody();
            die();
        } else {
            return $giEmail->send();
        }
    }
    
    /**
     * @return Contact
     */
    public function getContact(){
        if(is_null($this->contact)){
            $userId = $this->getId();
            $contact = ContactFactory::getBySourceUserId($userId);
            if(empty($contact)){
                $contact = ContactFactory::buildNewModel('ind');
                $contact->setProperty('source_user_id', $userId);
                $contact->getColour();
            }
            $this->contact = $contact;
        }
        return $this->contact;
    }
    
    
    //TODO - should only be used on new contact creation
    //TODO - move this to contact cat model
    public function createUserContact($parentContactCatTypeRef = NULL, $parentContactOrgId = NULL) {
        $contactInd = $this->getContact();
        if (!empty($contactInd) && !empty($contactInd->getId())) {
            return true;
        } else {
            $contactInd = ContactFactory::buildNewModel('ind');
        }
        if (empty($parentContactCatTypeRef) && empty($parentContactOrgId)) {
            return false;
        }
        //Contact ind
        $contactInd->setProperty('source_user_id', $this->getId());
        $contactInd->setProperty('pending', 1);
        $contactInd->setProperty('contact_ind.first_name', $this->getProperty('first_name'));
        $contactInd->setProperty('contact_ind.last_name', $this->getProperty('last_name'));
        
        if (!$contactInd->save()) {
            return false;
        }
        $newOrg = false;
        //if no parent id, create one, with contact cat of type ref
        if (!empty($parentContactOrgId)) {
            $parentOrg = ContactFactory::getModelById($parentContactOrgId);
            $contactCat = NULL;
        } else {
            $newOrg = true;
            $parentType = 'org'; //TODO set as class variable
            $parentOrg = ContactFactory::buildNewModel($parentType);
            $parentOrg->setProperty('pending', 1);
            $parentOrg->setProperty('contact_org.primary_individual_id', $contactInd->getId());
            $parentOrg->setProperty('display_name', $this->getProperty('first_name') . ' ' . $this->getProperty('last_name'));
            $contactCat = ContactCatFactory::buildNewModel($parentContactCatTypeRef);
            if (empty($contactCat)) {
                return false;
            }
            if (!$contactCat->getApplicationRequired()) {
                $parentOrg->setProperty('pending', 0);
                $contactInd->setProperty('pending', 0);
                if (!$contactInd->save()) {
                    return false;
                }
            }
        }
        if (empty($parentOrg) || !$parentOrg->save()) {
            return false;
        }
        if ($newOrg) {
            
            //create an email contact info using user's login, and associate with parent org
            $email = ContactInfoFactory::buildNewModel('email_address');
            $email->setProperty('contact_id', $parentOrg->getId());
            $email->setProperty('contact_info_email_addr.email_address', $this->getProperty('email'));
            $email->save();
            //create a billing address contact info, set region = default region, and associate w/ parent org
            $billingAddress = ContactInfoFactory::buildNewModel('address');
            $billingAddress->setProperty('contact_id', $parentOrg->getId());
            $billingAddress->setProperty('contact_info_address.addr_region', ProjectConfig::getDefaultRegionCode());
            $billingAddress->setProperty('contact_info_address.addr_country', ProjectConfig::getDefaultCountryCode());
            $billingAddress->save();
            //create a phone number contact info, if the user has one
            $mobileNumber = $this->getProperty('mobile');
            if (!empty($mobileNumber)) {
                $phone = ContactInfoFactory::buildNewModel('mobile_phone_num');
                $phone->setProperty('contact_id', $parentOrg->getId());
                $phone->setProperty('contact_info_phone_num.phone', $mobileNumber);
                $phone->save();
            }
        }
       
        if (!empty($contactCat)) {
            $contactCat->setProperty('contact_id', $parentOrg->getId());
            if (!$contactCat->save() || !ContactFactory::linkContactAndContact($parentOrg, $contactInd)) {
                return false;
            }
        }
        
        
        return true;
    }
    
    /**
     * @deprecated - use saveUserContactData instead
     * //TODO - remove
     * @param type $internal
     * @return boolean
     */
    public function saveUserAsContact($internal, GI_Form $form = NULL){
        if(dbConnection::isModuleInstalled('contact')){
            //Commented out for new signup users can create a contact data 
            //if(Permission::verifyByRef('add_contacts') && Permission::verifyByRef('edit_contacts')){
                $contact = $this->getContact();
                $contact->setProperty('contact_ind.first_name', $this->getProperty('first_name'));
                $contact->setProperty('contact_ind.last_name', $this->getProperty('last_name'));
                
            //    $internal = filter_input(INPUT_POST, 'internal');
                $contact->setProperty('internal', $internal);
                if (!$contact->save()) {
                    return false;
                }
                //@todo: to be removed because updateContactCats includes the internal case
                $contactCatModel = $contact->getContactCatModelByType('internal');
                if (!empty($internal)) {
                    if (empty($contactCatModel)) {
                        $buildNewModel = true;
                        $contactCatSearch = ContactCatFactory::search();
                        $contactCatSearch->filter('contact_id', $contact->getId())
                                ->setAutoStatus(false)
                                ->filter('status', 0);
                        $softDeletedContactCatArray = $contactCatSearch->select();
                        if (!empty($softDeletedContactCatArray)) {
                            $softDeletedContactCat = $softDeletedContactCatArray[0];
                            if ($softDeletedContactCat->unSoftDelete()) {
                                $buildNewModel = false;
                            }
                        }
                        if ($buildNewModel) {
                            $contactCatModel = ContactCatFactory::buildNewModel('internal');
                            $contactCatModel->setProperty('contact_id', $contact->getId());
                            if (!$contactCatModel->save()) {
                                return false;
                            }
                        }
                    }
                } else {
                    if (!empty($contactCatModel)) {
                        if (!$contactCatModel->softDelete()) {
                            return false;
                        }
                    }
                }
                if (!empty($form)) {
                    if (!$contact->updateContactCats($form)) {
                        return false;
                    }
                }
            //}
        }
        return true;
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
    public function setUserHasPermission($permissionRef, $hasPermission = false) {
        $this->persmissionsByRef[$permissionRef] = $hasPermission;
        $this->cacheUserHasPermission($permissionRef, $hasPermission);
    }

    protected function cacheUserHasPermission($permissionRef, $hasPermission = false) {
        if (empty($this->getId())) {
            return;
        }
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_user_perms')) {
            $allUserPerms = apcu_fetch($keyPrefix . '_user_perms');
        } else {
            $allUserPerms = array();
        }
        if (isset($allUserPerms[$this->getId()])) {
            $userPerms = $allUserPerms[$this->getId()];
        } else {
            $userPerms = array();
        }
        if ($hasPermission) {
            $userPerms[$permissionRef] = 1;
            $allUserPerms[$this->getId()] = $userPerms;
            apcu_store($keyPrefix . '_user_perms', $allUserPerms, APCU_TTL);
        }
    }

    public function clearCachedPermissions() {
        if (empty($this->getId())) {
            return true;
        }
        $keyPrefix = '';
        if (DEV_MODE) {
            $keyPrefix = ProjectConfig::getProjectBase();
        }
        if (apcu_exists($keyPrefix . '_user_perms')) {
            $allUserPerms = apcu_fetch($keyPrefix . '_user_perms');
        } else {
            return true;
        }
        if (isset($allUserPerms[$this->getId()])) {
            $allUserPerms[$this->getId()] = array();
            if (apcu_store($keyPrefix . '_user_perms', $allUserPerms, APCU_TTL)) {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }

    public function getAutocompResult($term = NULL) {
        $fullName = $this->getFullName();
        $avatarView = $this->getUserAvatarView();
        if ($avatarView) {
            $avatarView->setSize(30, 30);
            $avatar = '<span class="avatar_wrap inline_block has_img">' . $avatarView->getHTMLView() . '</span>';
        } else {
            $avatar = $this->getAvatarPlaceHolderHTML(30, 30);
        }
        $autoResult = '<span class="result_text">' . $avatar . '<h3 class="inline_block">' . GI_StringUtils::markTerm($term, $fullName) . '<span class="email">' . GI_StringUtils::markTerm($term, $this->getProperty('email')) . '</span></h3></span>';
        $result = array(
            'label' => $fullName,
            'value' => $this->getId(),
            'autoResult' => $autoResult
        );
        return $result;
    }
    
    public function getAvatarPlaceHolderHTML($width = NULL, $height = NULL){
        $avatar = '<span class="avatar_wrap avatar_placeholder inline_block">' . GI_StringUtils::getSVGIcon('avatar') . '</span>';
        return $avatar;
    }
    
    public function requiresPassReset(){
        if($this->getProperty('force_pass_reset')){
            return true;
        }
        return false;
    }
    
    public function getLogKey(){
        $userId = $this->getId();
        $loginAuditResult = Login_Audit::getByProperties(array(
            'user_id' => $userId
        ));
        if($loginAuditResult){
            $loginAudit = $loginAuditResult[0];
            $logKey = $loginAudit->getProperty('log_key');
            return $logKey;
        }
        return NULL;
    }
    
    public function getIndexURLAttrs($withPageNumber = false){
        $indexURLAttributes = array(
            'controller' => 'user',
            'action' => 'index',
        );
        $attributes = GI_URLUtils::getAttributes();
        if (isset($attributes['queryId'])) {
            $indexURLAttributes['queryId'] = $attributes['queryId'];
        }
        if ($withPageNumber && isset($attributes['pageNumber'])) {
            $indexURLAttributes['pageNumber'] = $attributes['pageNumber'];
        }
        return $indexURLAttributes;
    }
    public function getListBarURL($otherAttributes = NULL) {
        if (!Permission::verifyByRef('view_users') || !$this->isIndexViewable()) {
            $tmpNotification = NotificationFactory::buildNewModel();
            if(!$tmpNotification){
                return NULL;
            }
            return $tmpNotification->getListBarURL();
        }
        $listURLAttributes = $this->getIndexURLAttrs();
        $listURLAttributes['targetId'] = 'list_bar';
        $listURLAttributes['curId'] = $this->getId();
        if (isset($otherAttributes['fullView'])) {
            $listURLAttributes['fullView'] = $otherAttributes['fullView'];
        } else {
            $listURLAttributes['fullView'] = 1;
        }
        return GI_URLUtils::buildURL($listURLAttributes);
    }
    
    public function getNotificationSettingsView() {
        return new UserNotificationSettingsView($this);
    }
    
    public function getNotificationSettingsModel($eventId, $typeRef = 'notification') {
        $key = $typeRef . '_' . $eventId;
        if (isset($this->settingsNotifByRef[$key])) {
            return $this->settingsNotifByRef[$key];
        }
        $search = SettingsFactory::search();
        $search->filterByTypeRef($typeRef)
                ->filter('user_id', $this->getId());
        if (!empty($eventId)) {
            $search->filter('notification.event_id', $eventId);
        } else {
            $search->filterNull('notification.event_id');
        }
        $results = $search->select();
        if (!empty($results)) {
            $model = $results[0];
        } else {
            $model = SettingsFactory::buildNewModel($typeRef);
            if (empty($model)) {
                return NULL;
            }
            if (!empty($eventId)) {
                $event = EventFactory::getModelById($eventId);
                if (empty($event)) {
                    return NULL;
                }
                $eventTitle = $event->getProperty('title');
                $title = $eventTitle;
            } else {
                $title = 'Global';
                $model->setProperty('settings_notif.alt_email', $this->getProperty('email'));
                $model->setProperty('settings_notif.in_system', 1);;
            }
            $model->setProperty('title', $title);
            $model->setProperty('ref', GI_Sanitize::ref($title));
            $model->setProperty('user_id', $this->getId());
            $model->setProperty('settings_notif.event_id', $eventId);
        }
        if (!empty($model)) {
            $this->settingsNotifByRef[$key] = $model;
            return $model;
        }
        return NULL;        
    }
    
    public function getGlobalNotificationSettingsModel() {
        return $this->getNotificationSettingsModel(NULL, 'notification_global');
    }
    
    /**
     * @param GI_Form $form
     * @return AbstractGI_Uploader
     */
    public function getAvatarUploader(GI_Form $form = NULL){
        if($this->getId()){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        
        $imgUploader = GI_UploaderFactory::buildImageUploader('user_' . $appendName);
        $imgUploader->setFilesLabel('Avatar');
        $imgUploader->setBrowseLabel('Upload Image');
        $imgFolder = $this->getImageFolder();
        $imgUploader->setTargetFolder($imgFolder);
        if (!empty($form)) {
            $imgUploader->setForm($form);
        }
        
        return $imgUploader;
    }
    
    /**
     * @return Folder
     */
    public function getImageFolder(){
        $imgFolder = $this->getSubFolderByRef('profile_pictures');
        return $imgFolder;
    }
    
    /**
     * //TODO - modify for use only by super admin
     * Profile form submit handler
     * 
     * @param GI_Form $form
     * @return boolean. false if not submitted or failed to save
     */
    public function handleProfileFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $avatarUploader = $this->getAvatarUploader($form);
            
            $firstName = filter_input(INPUT_POST, 'first_name');
            $lastName = filter_input(INPUT_POST, 'last_name');
            $this->setProperty('first_name', $firstName);
            $this->setProperty('last_name', $lastName);
            
            //Password
            $password = filter_input(INPUT_POST, 'new_password');
            $repeatPassword = filter_input(INPUT_POST, 'repeat_password');
            if (!empty($password)) {
                $setPassword = true;
                if ($password !== $repeatPassword) {
                    $form->addFieldError('repeat_password', 'mismatch', 'You must re-enter the same password as above.');
                    return false;
                }
                $reason = '';
                if(!$this->validatePassword($password, $reason)){
                    $form->addFieldError('new_password', 'invalid', $reason);
                    return false;
                }
            }
            if ($setPassword) {
                $salt = $this->generateSalt();
                $this->setProperty('salt', $salt);
                $pass = $this->generateSaltyPass($password, $salt);
                $this->setProperty('pass', $pass);
            }
            
            if ($this->save()) {
                if($avatarUploader){
                    $avatarUploader->setTargetFolder($this->getImageFolder());
                    FolderFactory::putUploadedFilesInTargetFolder($avatarUploader);
                }
            
                $contact = $this->getContact();
                if (!empty($contact)) {
                    //Save Contact Ind
                    $contact->setProperty('contact_ind.first_name', $firstName);
                    $contact->setProperty('contact_ind.last_name', $lastName);
                    $colour = filter_input(INPUT_POST, 'colour');
                    $contact->setProperty('colour', $colour);
                    if (!$contact->save()) {
                        return false;
                    }
                    //Save Contact Infos
                    if(!$contact->callHandleContactInfoFormSubmission($form)) {
                        return false;
                    }
                    
                    //Save tags 
                    if (!$contact->callHandleTagFormSubmission($form)) {
                        return false;
                    }
                    
                    //
                }
                return true;
            }
        }
        return false;
    }
    
    public function sendForgotPassEmail(&$giEmail = NULL){
        $logKey = $this->getLogKey();
        if (!empty($logKey)) {
            $url = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'requestNewPass',
                'logKey' => $logKey
            ), true, true);

            $projectTitle = ProjectConfig::getSiteTitle();

            $emailView = new GenericEmailView();
            $emailView->setTitle($projectTitle . ' - Forgot Password');
            $emailView->addHTML('<h3>Forgot Password Request</h3>');

            $emailView->startBlock();
            $emailView->addParagraph('If you were not the one to request this password reset you can ignore this email.');
            $emailView->addParagraph('To reset your password, follow the link below.');
            $emailView->addButton('Reset Password', $url);
            $emailView->closeBlock();
            $emailView->addParagraph('- ' . $projectTitle);

            $giEmail = new GI_Email();
            $giEmail->addMandrillTag('forgot-password');

            $giEmail->addTo($this->getProperty('email'), $this->getFullName())
                    ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                    ->setSubject($emailView->getTitle())
                    ->useEmailView($emailView);

            if ($giEmail->send()) {
                return true;
            }
        }
        return false;
    }
    
    public function getChatUserType() {
        //@todo this is currently how we are determining which socket user list you fall into (internal = following chat boxes) (client = only in the active client list)
        if (Permission::verifyByRef('view_chat_user_list')) {
            return 'internal';
        }
        return 'client';
    }

    public function isUnconfirmed() {
        return false;
    }

    public function getCurrentInterfacePerspective() {
        if (empty($this->currentInterfacePerspective)) {
            $search = InterfacePerspectiveFactory::search();
            $tableName = $search->prefixTableName('interface_perspective');
            $search->join('role_has_interface_perspective', 'interface_perspective_id', $tableName, 'id', 'RHIP')
                    ->join('role', 'id', 'RHIP', 'role_id', 'ROLE')
                    ->join('user_link_to_role', 'role_id', 'ROLE', 'id', 'ULTR');
            $search->filter('ULTR.user_id', $this->getId());

            $search->orderBy('rank', 'desc')
                    ->setItemsPerPage(1)
                    ->setPageNumber(1);
            $results = $search->select();
            if (!empty($results)) {
                $this->currentInterfacePerspective = $results[0];
            }
        }
        return $this->currentInterfacePerspective;
    }

    public function setCurrentInterfacePerspective(AbstractInterfacePerspective $currentInterfacePerspective) {
        $this->currentInterfacePerspective = $currentInterfacePerspective;
    }

    public function isSuspended() {
        if (is_null($this->isSuspended)) {
            if (!dbConnection::isModuleInstalled('contact') || empty($this->getId())) {
                $this->isSuspended = false;
            }
            $cacheKey = $this->getIsSuspendedCacheKey();
            if (apcu_exists($cacheKey)) {
                $status = apcu_fetch($cacheKey);
                if (!empty($status)) {
                    $this->isSuspended = true;
                } else {
                    $this->isSuspended = false;
                }
                return $this->isSuspended;
            }
            
            $contact = $this->getContact();
            if (empty($contact)) {
                $this->isSuspended = false;
                return $this->isSuspended;
            }
            $suspended = false;
            $suspensions = SuspensionFactory::getSuspensionsByContact($contact, '', GI_Time::getDateTime(), true);
            if (!empty($suspensions)) {
               $suspended = true;
            } 
            $this->isSuspended = $suspended;
            
            if ($suspended) {
                apcu_add($cacheKey, 1, 900);
            } else {
                apcu_add($cacheKey, 0, 900);
            }
        }
        return $this->isSuspended;
    }
    
    public function getIsSuspendedCacheKey() {
        return 'suspension_status_user_' . $this->getId();
    }
    
    public function clearIsSuspendedCache() {
        $cacheKey = $this->getIsSuspendedCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            if (!apcu_delete($cacheKey)) {
                return false;
            }
        }
        return true;
    }

}
