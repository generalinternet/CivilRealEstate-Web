<?php
/**
 * Description of AbstractPermissionController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractPermissionController extends GI_Controller {

    public function actionIndex($attributes) {
        if(!Permission::verifyByRef('view_permissions')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        
        if(isset($attributes['queryId'])){
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }
        
        $samplePermission = PermissionFactory::buildNewModel();
        $search = PermissionFactory::searchRestricted()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        $pageBarLinkProps = $attributes;
        
        $redirectArray = array();
        $searchView = $samplePermission->getSearchForm($search, $redirectArray);
        
        $samplePermission->addCustomFiltersToDataSearch($search);
        $samplePermission->addSortingToDataSearch($search);
        $actionResult = ActionResultFactory::buildActionResult();
        $actionResult->setSearchView($searchView)
                ->setSampleModel($samplePermission)
                ->setUseAjax(true)
                ->setRedirectArray($redirectArray);
        
        $permissions = $search->select();
        $pageBar = $search->getPageBar($pageBarLinkProps);
        
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols = $samplePermission->getUIRolodexCols();
            $uiTableView = new UIRolodexView($permissions, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(isset($attributes['curId']) && $attributes['curId'] != ''){
                $uiTableView->setCurId($attributes['curId']);
            }
        } else {
            //List style view
            $uiTableCols = $samplePermission->getUITableCols();
            $uiTableView = new UITableView($permissions, $uiTableCols, $pageBar);
            if(isset($attributes['addSelectRowCol']) && $attributes['addSelectRowCol'] == 1){
                $uiTableView->addDefaultSelectRowColumn('Permission');
            }
        }
        $view = new PermissionIndexView($permissions, $uiTableView, $samplePermission, $searchView);
        $actionResult->setView($view)
                ->setPageBar($pageBar)
                ->setUITableView($uiTableView);
        $returnArray = $actionResult->getIndexReturnArray();
        return $returnArray;
    }
    
    public function actionAdd($attributes){
        if(!Permission::verifyByRef('add_permissions')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('add_permission');
        
        $permission = PermissionFactory::buildNewModel();
        if (is_null($permission)) {
            GI_URLUtils::redirectToError();
        }
        
        $view = $permission->getFormView($form);
        
        $success = 0;
        if ($permission->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttrs = $permission->getViewURLAttrs();
            if(GI_URLUtils::isAJAX()){
                //Change the view to a detail view
                $view = $permission->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttrs);
            } else {
                GI_URLUtils::redirect($redirectURLAttrs);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $permission->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
            'controller' => 'permission',
            'action' => 'add'
        ));
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if(GI_URLUtils::isAJAX()){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new data
                $curId = $permission->getId();
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar", '.$curId.');historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        } else {
            //Set the list bar with index view
            $returnArray['listBarURL'] = $permission->getListBarURL();
        }
        return $returnArray;
    }
    
    public function actionEdit($attributes){
        if(!Permission::verifyByRef('edit_permissions')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $id = $attributes['id'];
        $permission = PermissionFactory::getModelById($id);
        if (empty($permission)) {
            GI_URLUtils::redirectToError(4001);
        } elseif(!Permission::verifyByRef($permission->getProperty('ref'))){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('edit_permission');
        $view = $permission->getFormView($form);
        
        $success = 0;
        if ($permission->handleFormSubmission($form)) {
            $success = 1;
            $redirectURLAttributes = $permission->getViewURLAttrs();
            if(GI_URLUtils::isAjax()){
                //Change the view to a detail view
                $view = $permission->getDetailView();
                $redirectURL = GI_URLUtils::buildURL($redirectURLAttributes);
            } else {
                GI_URLUtils::redirect($redirectURLAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $permission->getBreadcrumbs();
        $editLink = GI_URLUtils::buildURL(array(
            'controller' => 'permission',
            'action' => 'edit',
            'id' => $id
        ));
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if(GI_URLUtils::isAjax()){
            $returnArray['success'] = $success;
            if ($success) {
                //Set the list bar with index view to update new data
                $returnArray['jqueryCallbackAction'] = 'reloadInElementByTargetId("list_bar");historyPushState("reload", "'.$redirectURL.'", "main_window");';
            }
        } else {
            //Set the list bar with index view
            $returnArray['listBarURL'] = $permission->getListBarURL();
        }
        return $returnArray;
    }
    
    public function actionView($attributes){
        if(!Permission::verifyByRef('view_permissions')){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $permission = PermissionFactory::getModelById($id);
        if (empty($permission)) {
            GI_URLUtils::redirectToError(4001);
        } elseif(!Permission::verifyByRef($permission->getProperty('ref'))){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $permission->getDetailView();
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $permission->getBreadcrumbs();
        return $returnArray;
    }
    
    public function actionDenied($attributes){
        $view = new PermissionDeniedView();
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Access Denied',
                'link' => GI_URLUtils::buildURL($attributes)
            )
        );
        return $returnArray;
    }
    
    public function actionAutocompPermission($attributes){
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)){
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }

        if(isset($attributes['curVal'])){
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            
            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach($curVals as $permissionId){
                $permission = PermissionFactory::getModelById($permissionId);
                if($permission){
                    if(isset($attributes['notInCatIds'])){
                        $notInCatIds = explode(',', $attributes['notInCatIds']);
                        if(in_array($permission->getProperty('permission_category_id', $notInCatIds))){
                            continue;
                        }
                    }
                    $acResult = $permission->getAutocompResult();

                    foreach($acResult as $key => $val){
                        if(!isset($results[$key])){
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            
            return $results;
        } else {
            if(isset($_REQUEST['term'])){
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }

            $search = PermissionFactory::searchRestricted()
                    ->setItemsPerPage(ProjectConfig::getAutocompleteItemLimit());
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $search->setPageNumber($pageNumber);
            }

            if(!empty($term)){
                $search->filterTermsLike('title,ref', $term)
                        ->orderByLikeScore('title,ref', $term);
            }
            
            if(isset($attributes['notInCatIds'])){
                $notInCatIds = explode(',', $attributes['notInCatIds']);
                $search->filterNotIn('permission_category_id', $notInCatIds);
            }

            $permissions = $search->select();

            $results = array();

            foreach ($permissions as $permission) {
                /* @var $item AbstractContact */
                $info = $permission->getAutocompResult($term);
                $results[] = $info;
            }

            $itemsPerPage = $search->getItemsPerPage();
            $count = $search->getCount();
            $this->addAutocompNavToResults($results, $count, $itemsPerPage, $pageNumber);

            return $results;
        }
    }

}
