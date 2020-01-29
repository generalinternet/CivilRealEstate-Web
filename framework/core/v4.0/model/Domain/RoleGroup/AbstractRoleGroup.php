<?php

abstract class AbstractRoleGroup extends GI_Model {
    
    /**
     * @var AbstractRole[] 
     */
    protected $roles = NULL;

    /**
     * Gets user's highest role group rank
     * 
     * @param string $type
     * @param int $userId
     * @return int 0 if a user has no roles
     */
    public static function getUserHighestRoleGroupRank($type = 'self', $userId = NULL) {
        if ($type === 'self') {
            $col = 'role_rank';
        } else if ($type === 'other') {
            $col = 'max_ref_role_rank';
        } else {
            return -1;
        }
        $highestRank = 0;
        if (!$userId) {
            $userId = Login::getUserId(true);
        }
        $user = UserFactory::getModelById($userId);
        $roles = $user->getRoles();
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleRankId = $role->getProperty($col);
                $roleRank = RoleGroupFactory::getModelById($roleRankId);
                $rank = $roleRank->getProperty('rank');
                if ($rank > $highestRank) {
                    $highestRank = $rank;
                }
            }
        }
        return $highestRank;
    }

    /**
     * Gets user's lowest role group rank
     * 
     * @param string $type
     * @param int $userId
     * @return int 0 if a user has no roles
     */
    public static function getUserLowestRoleGroupRank($type = 'self', $userId = NULL) {
        if ($type === 'self') {
            $col = 'role_rank';
        } else if ($type === 'other') {
            $col = 'max_ref_role_rank';
        } else {
            return -1;
        }
        if (!$userId) {
            $userId = Login::getUserId();
        }
        $user = UserFactory::getModelById($userId);
        $roles = $user->getRoles();

        $count = 0;
        $lowestRank = 0;
        foreach ($roles as $role) {
            $roleRankId = $role->getProperty($col);
            $roleRank = RoleGroupFactory::getModelById($roleRankId);
            $rank = $roleRank->getProperty('rank');
            if ($count == 0) {
                $lowestRank = $rank;
            } else if ($rank < $lowestRank) {
                $lowestRank = $rank;
            }
            $count++;
        }
        return $lowestRank;
    }

    /**
     * Builds an array of user role group's names
     * 
     * @param string $type
     * @return array[key=>value]
     */
    public static function buildRoleGroupNamesArray($type = 'self') {
        $userHighestRoleRankNumber = RoleGroup::getUserHighestRoleGroupRank($type);
        $roleRankModelArray = RoleGroupFactory::search()
                ->filterLessOrEqualTo('rank', $userHighestRoleRankNumber)
                ->orderBy('rank', 'desc')
                ->select();
        $roleRankNamesArray = array();
        if (!empty($roleRankModelArray)) {
            foreach ($roleRankModelArray as $roleRankModel) {
                $rank = $roleRankModel->getProperty('rank');
                $title = $roleRankModel->getProperty('title');
                $systemTitle = $roleRankModel->getProperty('system_title');
                $roleRankNamesArray[$systemTitle] = $rank . ' - ' . $title;
            }
        }
        return $roleRankNamesArray;
    }
    
    /**
     * Gets role name list's HTML text
     * 
     * @return string HTML text
     */
    public function getRoleNamesList() {
        $roles = $this->getRoles();
        $roleNames = '';
        if (!empty($roles)) {
            $roleNames = '<ul>';
            foreach ($roles as $role) {
                $roleNames .= '<li>' . $role->getProperty('title') . '</li>';
            }
            $roleNames .= '</ul>';
        }
        return $roleNames;
    }

    /**
     * Gets UI table columns
     * 
     * @return UITableCol[]
     */
    public static function getUITableCols() {
        $tableColArrays = array(
            //Title
            array(
                'header_title' => 'Title',
                'method_attributes' => 'role_rank.title',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => ''
            ),
            //Rank
            array(
                'header_title' => 'Rank',
                'method_attributes' => 'role_rank.rank',
            ),
            //Roles
            array(
                'header_title' => 'Roles',
                'method_name' => 'getRoleNamesList',
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setTitle($this->getTitle());
        $cardView->setSummary($this->getRoleNamesList());
        $cardView->setTopRight($this->getProperty('rank'));
        return $cardView;
    }
    
    public function getViewURLAttrs() {
        return array(
            'controller'=>'role',
            'action'=>'viewGroup',
            'roleRankId'=>$this->getId()
        );
    }
    
    /**
     * Gets edit URL
     * 
     * @return string URL
     */
    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
            'controller'=>'role',
            'action'=>'editGroup',
            'roleRankId'=>$this->getProperty('id')
        ));
    }

    /**
     * Form submit handler
     * 
     * @param GI_Form $form
     * @return AbstractRoleGroup. NULL if not submitted, failed to save or rank > 999 
     */
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $title = filter_input(INPUT_POST, 'title');
            $rank = (int) filter_input(INPUT_POST, 'rank');
            if ($rank > 999) {
                $form->addFieldError('max_rank_error', 'error', 'Maximum rank is 999');
                return NULL;
            }
            $this->setProperty('title', $title);
            $lcTitle = strtolower($title);
            $titlePieces = explode(" ", $lcTitle);
            $system_title = $titlePieces[0];
            if (sizeof($titlePieces) > 1) {
                for ($i = 1; $i < sizeof($titlePieces); $i++) {
                    $system_title .= '_' . $titlePieces[$i];
                }
            }
            $this->setProperty('system_title', $system_title);

            $this->setProperty('rank', $rank);
            if ($this->save()) {
                return $this;
            }
        }
        return NULL;
    }
    
    /**
     * Gets roles
     * 
     * @return AbstractRole[] an array of Role DAOs
     */
    public function getRoles() {
        if (empty($this->roles)) {
            $this->roles = RoleFactory::getRolesByRoleRank($this);
        }
        return $this->roles;
    }
    
    /**
     * Gets a form view
     * 
     * @param GI_Form $form
     * @return GI_View RoleGroupFormView
     */
    public function getFormView(GI_Form $form) {
        return new RoleGroupFormView($form, $this);
    }
    
    /**
     * Gets a detail view
     * 
     * @return GI_View RoleGroupDetailView
     */
    public function getDetailView() {
        return new RoleGroupDetailView($this);
    }

    /**
     * Gets breadcrumbs
     * 
     * @return array[key=>value]
     */
    public function getBreadcrumbs() {
        $breadcrumbs = array();
        $roleGroupsURL = GI_URLUtils::buildURL(array(
                    'controller' => 'role',
                    'action' => 'index',
        ));
        $breadcrumbs[] = array(
            'label' => $this->getViewTitle(true),
            'link' => $roleGroupsURL
        );
        $roleGroupId = $this->getProperty('id');
        if (!empty($roleGroupId)) {
            $breadcrumbs[] = array(
                'label'=>$this->getProperty('title'),
                'link'=>$this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    /**
     * Gets a view title
     * 
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = false) {
        $title = 'Role Group';
        if ($plural) {
            $title .= 's';
        }
        return  $title;
    }
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public static function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch){
        
    }
    
    /** @param GI_DataSearch $dataSearch */
    public static function addSortingToDataSearch(GI_DataSearch $dataSearch){
        $dataSearch->orderBy('rank', 'DESC');
    }
    
    public function getListBarURLAttrs(){
        $attrs = array(
            'controller' => 'role',
            'action' => 'index',
            'curId' => $this->getId()
        );
        $typeRef = $this->getTypeRef();
        if($typeRef){
            $attrs['type'] = $typeRef;
        }
        return $attrs;
    }

}
