<?php

abstract class AbstractRoleController extends GI_Controller {

    public function actionAdd($attributes) {
        if (!Permission::verifyByRef('add_roles')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('add_role');
        if (isset($attributes['groupId'])) {
            $roleGroupId = $attributes['groupId'];
        } else {
            $roleGroupId = NULL;
        }
        $currentUser = UserFactory::getModelById(Login::getUserId());
        $userPermissions = $currentUser->getPermissions();
        $roleGroupNamesArray = RoleGroup::buildRoleGroupNamesArray('self');
        $maxRoleGroupNames = RoleGroup::buildRoleGroupNamesArray('other');
        
        $roleModel = RoleFactory::buildNewModel();
        $roleGroup = RoleGroupFactory::getModelById($roleGroupId);
        $roleModel->setRoleGroup($roleGroup);
        $view = $roleModel->getFormView($form, $userPermissions, $roleGroupNamesArray, $maxRoleGroupNames, $roleGroup);
        
        $updatedRoleModel = $roleModel->handleFormSubmission($form, $roleGroup);
        
        if (!empty($updatedRoleModel)) {
            $roleId = $updatedRoleModel->getProperty('id');
            GI_URLUtils::redirect(array(
                'controller'=>'role',
                'action'=>'view',
                'roleId'=>$roleId
            ));
        }
        
        $breadcrumbs = $roleGroup->getBreadcrumbs();
        $breadcrumbs[] = array(
            'label'=>$roleModel->getViewTitle(true),
           // 'link'=>''
        );
        $breadcrumbs[] = array(
            'label'=>'Add ' . $roleModel->getViewTitle(),
            'link' =>  GI_URLUtils::buildURL($attributes),
        );
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!Permission::verifyByRef('edit_roles')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $roleId = $attributes['roleId'];
        $roleModel = RoleFactory::getModelById($roleId);
        $form = new GI_Form('edit_role');
        
        $currentUser = UserFactory::getModelById(Login::getUserId());
        $userPermissions = $currentUser->getPermissions();
        if (empty($currentUser)) {
            $userPermissions = array();
        } else {
            $userPermissions = PermissionFactory::getPermissionsByUser($currentUser);
        }
        $roleGroupNames = RoleGroup::buildRoleGroupNamesArray('self');
        $maxRoleGroupRankNames = RoleGroup::buildRoleGroupNamesArray('other');
        $view = $roleModel->getFormView($form, $userPermissions, $roleGroupNames, $maxRoleGroupRankNames);
        
        $updatedRoleModel = $roleModel->handleFormSubmission($form);
        if (!empty($updatedRoleModel)) {
            $updatedRoleModelId = $updatedRoleModel->getProperty('id');
            GI_URLUtils::redirect(array(
                'controller'=>'role',
                'action'=>'view',
                'roleId'=>$updatedRoleModelId
            ));
        }
        
        $roleGroup = $roleModel->getRoleGroup();
        $breadcrumbs = $roleGroup->getBreadcrumbs();
        $breadcrumbs[] = array(
            'label' => $roleModel->getViewTitle(true),
        );
        $breadcrumbs[] = array(
            'label' => $roleModel->getProperty('title'),
            'link' => GI_URLUtils::buildURL(array(
                'controller' => 'role',
                'action' => 'view',
                'roleId' => $roleId
            )),
        );
        $breadcrumbs[] = array(
            'label' => 'Edit ' . $roleModel->getViewTitle(),
            'link' => GI_URLUtils::buildURL($attributes),
        );
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionView($attributes) {
        if (!Permission::verifyByRef('view_roles') || !(isset($attributes['roleId']))) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }

        $roleId = $attributes['roleId'];
        $roleModel = RoleFactory::getModelById($roleId);
        if ($roleModel) {
            $view = $roleModel->getDetailView();
            $roleGroup = $roleModel->getRoleGroup();
            $breadcrumbs = $roleGroup->getBreadcrumbs();
            $breadcrumbs[] = array(
                'label' => $roleModel->getViewTitle(true),
            );
            $breadcrumbs[] = array(
                'label' => $roleModel->getProperty('title'),
                'link' => GI_URLUtils::buildURL($attributes),
            );
            $returnArray = GI_Controller::getReturnArray($view);
            $returnArray['breadcrumbs'] = $breadcrumbs;
            return $returnArray;
        }
        GI_URLUtils::redirectToError();
    }

    public function actionSoftDelete($attributes) {
        if (!Permission::verifyByRef('delete_roles')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['roleId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $roleId = $attributes['roleId'];
        $role = RoleFactory::getModelById($roleId);
        if (empty($role)) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }
        $unlinkUsersResult = RoleFactory::unlinkRoleFromAllUsers($role);
        $permissions = PermissionFactory::getPermissionsByRole($role);
        foreach ($permissions as $permission) {
            $unlinkResult = PermissionFactory::unlinkPermissionFromRole($permission, $role);
            if (!$unlinkResult) {
                GI_URLUtils::redirectToError(3000);
            }
        }
        if ($unlinkUsersResult && $unlinkResult && $role->softDelete()) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }
    }

    public function actionIndex($attributes) {        
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        $type = NULL;
        if(isset($attributes['type'])){
            $type = $attributes['type'];
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }

        $search = RoleGroupFactory::searchRestricted()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        if(!empty($type)){
            $search->filterByTypeRef($type);
        }
        
        $sampleRoleGroup = RoleGroupFactory::buildNewModel();
        
        $sampleRoleGroup->addCustomFiltersToDataSearch($search);
        
        $redirectArray = array();
        $searchView = NULL;
//        $searchView = $sampleRoleGroup->getSearchForm($search, $type, $redirectArray);
        $sampleRoleGroup->addSortingToDataSearch($search);
        
        $pageBarLinkProps = $attributes;
        
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($sampleRoleGroup)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        if(!GI_URLUtils::getAttribute('search')){
            $roleGroups = $search->select();
            $pageBar = $search->getPageBar($pageBarLinkProps);
            
            if ($targetId == 'list_bar') {
                //Tile style view
                $uiTableCols = $sampleRoleGroup->getUIRolodexCols();
                $uiTableView = new UIRolodexView($roleGroups, $uiTableCols, $pageBar);
                $uiTableView->setLoadMore(true);
                $uiTableView->setShowPageBar(false);
                if(isset($attributes['curId']) && $attributes['curId'] != ''){
                    $uiTableView->setCurId($attributes['curId']);
                }
            } else {
                //List style view
                $uiTableCols = $sampleRoleGroup->getUITableCols();
                $uiTableView = new UITableView($roleGorups, $uiTableCols, $pageBar);
            }
            
            $view = new RoleGroupIndexView($roleGroups, $uiTableView, $sampleRoleGroup, $searchView);
            $actionResult->setView($view)
                    ->setPageBar($pageBar)
                    ->setUITableView($uiTableView);
        }
        
        $returnArray = $actionResult->getIndexReturnArray();
        
        return $returnArray;
        
        
        
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = NULL;
        }
        $sampleRoleGroup = RoleGroupFactory::buildNewModel($type);
        $allRoleGroups = RoleGroupFactory::search()
                ->orderBy('rank', 'DESC')
                ->select();
        if (empty($allRoleGroups)) {
            GI_URLUtils::redirectToError(4000);
        }
        $userHighestRoleGroupRank = RoleGroup::getUserHighestRoleGroupRank();
        $roleGroups = array();
        foreach ($allRoleGroups as $oneRoleGroup) {
            $rank = $oneRoleGroup->getProperty('rank');
            if ($rank <= $userHighestRoleGroupRank) {
                $roleGroups[] = $oneRoleGroup;
            }
        }
        $uiTableCols = RoleGroup::getUITableCols();
        $uiTableView = new UITableView($roleGroups, $uiTableCols);
        $view = new RoleGroupIndexView($roleGroups, $uiTableView);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $sampleRoleGroup->getBreadcrumbs();
        return $returnArray;
    }

    public function actionEditGroup($attributes) {
        if (!Permission::verifyByRef('edit_role_ranks')) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }
        $form = new GI_Form('edit_role_group');
        $roleGroupId = $attributes['roleRankId'];
        $roleGroupModel = RoleGroupFactory::getModelById($roleGroupId);
        $updatedRoleGroupModel = $roleGroupModel->handleFormSubmission($form);
        
        $success = 0;
        $newURL = '';
        if (!empty($updatedRoleGroupModel)) {
            $success = 1;
            $newURLAttrs = array(
                'controller' => 'role',
                'action' => 'viewGroup',
                'roleRankId' => $roleGroupId
            );
            if(GI_URLUtils::isAJAX()){
                $newURL = GI_URLUtils::buildURL($newURLAttrs);
            } else {
                GI_URLUtils::redirect($newURLAttrs);
            }
        }
        
        $view = $roleGroupModel->getFormView($form);
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $roleGroupModel->getBreadcrumbs();
        $breadcrumbs[] = array(
            'label'=>'Edit ' . $roleGroupModel->getViewTitle(),
            'link'=>  GI_URLUtils::buildURL($attributes),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        $returnArray['success'] = $success;
        if(!empty($newURL)){
            $returnArray['newUrl'] = $newURL;
        }
        return $returnArray;
    }

    public function actionAddGroup($attributes) {
        if (!Permission::verifyByRef('add_role_ranks')) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }
        $form = new GI_Form('add_role_group');
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = '';
        }
        $roleGroup = RoleGroupFactory::buildNewModel($type);
        if (empty($roleGroup)) {
            GI_URLUtils::redirectToError(4000);
        }
        $roleGroupUpdated = $roleGroup->handleFormSubmission($form);
        $success = 0;
        $newURL = '';
        if (!empty($roleGroupUpdated)) {
            $success = 1;
            $newURLAttrs = array(
                'controller' => 'role',
                'action' => 'viewGroup',
                'roleRankId' => $roleGroup->getId()
            );
            if(GI_URLUtils::isAJAX()){
                $newURL = GI_URLUtils::buildURL($newURLAttrs);
            } else {
                GI_URLUtils::redirect($newURLAttrs);
            }
        }
        $view = $roleGroup->getFormView($form);
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $roleGroup->getBreadcrumbs();
        $breadcrumbs[] = array(
            'label'=>'Add ' . $roleGroup->getViewTitle(),
            'link'=>  GI_URLUtils::buildURL($attributes)
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        $returnArray['success'] = $success;
        if(!empty($newURL)){
            $returnArray['newUrl'] = $newURL;
        }
        return $returnArray;
    }

    public function actionViewGroup($attributes) {
        if (!Permission::verifyByRef('view_role_ranks')) {
            GI_URLUtils::redirect(array(
                'controller' => 'role',
                'action' => 'index'
            ));
        }
        $roleGroupId = $attributes['roleRankId'];
        $roleGroupModel = RoleGroupFactory::getModelById($roleGroupId);
        if (empty($roleGroupModel)) {
            GI_URLUtils::redirectToError(4001);
        }
        $view = $roleGroupModel->getDetailView();
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $roleGroupModel->getBreadcrumbs();
        return $returnArray;
    }

    public function actionSoftDeleteRoleGroup($attributes) {
        $roleGroupId = $attributes['roleRankId'];
        $roleArray = Role::getByProperties(array(
                    'role_rank' => $roleGroupId
        ));
        if (sizeof($roleArray) > 0) {
            //TODO: Add alert 'cannot delete role rank - currently assigned to at least 1 role'
        } else {
            $roleGroup = RoleGroupFactory::getModelById($roleGroupId);
            if (empty($roleGroup)) {
                GI_URLUtils::redirectToError(4001);
            }
            $roleGroup->softDelete();
        }
        return $this->actionIndex($attributes);
    }

}
