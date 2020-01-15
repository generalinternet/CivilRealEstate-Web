<?php
/**
 * Description of AbstractAutocompleteController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.2
 */
class AbstractLabourRateController extends GI_Controller {
    
    public function actionAdd($attributes) {
        if(!Permission::verifyByRef('add_labour_rates')){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'labour';
        }
        
        $form = new GI_Form('add_labour_rate');
        
        $labourRate = LabourRateFactory::buildNewModel($type);
        if (is_null($labourRate)) {
            GI_URLUtils::redirectToError(4000);
        }
        
        if (isset($attributes['title'])) {
            $labourRate->setProperty('title', $attributes['title']);
        }
        
        $view = $labourRate->getFormView($form);
        
        $success = 0;
        $autocompId = NULL;
        if ($labourRate->handleFormSubmission($form)) {
            if (isset($attributes['ajax']) && $attributes['ajax']) {
                $success = 1;
                $autocompId = $labourRate->getProperty('id');
            }
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $labourRate->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
            'controller' => 'labourRate',
            'action' => 'add',
            'type' => $type
        ));
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        $returnArray['success'] = $success;
        $returnArray['autocompId'] = $autocompId;
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'labour';
        }
        $id = $attributes['id'];
        $labourRate = LabourRateFactory::getModelById($id);
        if (empty($labourRate)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if($labourRate->getProperty('uid') != Login::getUserId() && !Permission::verifyByRef('edit_labour_rates')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('edit_labour_rate');
        $view = $labourRate->getFormView($form);
        
        if ($labourRate->handleFormSubmission($form)) {
            
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $labourRate->getBreadcrumbs();
        $editLink = GI_URLUtils::buildURL(array(
            'controller' => 'labourRate',
            'action' => 'edit',
            'type' => $type
        ));
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }
    
    public function actionAddUserHasLabourRate($attributes) {
        if(!Permission::verifyByRef('add_user_has_labour_rates')){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('add_user_has_labour_rate');
        
        $userHasLabourRate = UserHasLabourRateFactory::buildNewModel();
        if (is_null($userHasLabourRate)) {
            GI_URLUtils::redirectToError(4000);
        }
        
        if (!isset($attributes['userId'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $userId = $attributes['userId'];
        $userHasLabourRate->setProperty('user_id', $userId);
        
        $view = $userHasLabourRate->getFormView($form);
        
        $success = 0;
        $autocompId = NULL;
        if ($userHasLabourRate->handleFormSubmission($form)) {
            if (isset($attributes['ajax']) && $attributes['ajax']) {
                $success = 1;
                $autocompId = $userHasLabourRate->getProperty('id');
            }
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = $userHasLabourRate->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
            'controller' => 'labourRate',
            'action' => 'addUserHasLabourRate',
            'userId' => $userId
        ));
        $breadcrumbs[] = array(
            'label' => 'Add User Labour Rate',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        $returnArray['success'] = $success;
        $returnArray['autocompId'] = $autocompId;
        return $returnArray;
    }
    
}
