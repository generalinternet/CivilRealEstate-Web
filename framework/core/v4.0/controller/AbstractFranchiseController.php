<?php

abstract class AbstractFranchiseController extends GI_Controller {

    public function actionChangeCurrentFranchise($attributes) {
        if (!Permission::verifyByRef('franchise_head_office')) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('change_franchise');

        $currentFranchise = Login::getCurrentFranchise();
        $view = new FranchiseChangeFormView($form, $currentFranchise);
        $options = ContactFactory::getFranchiseOptionsArray();
        $view->setFranchiseOptions($options);
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        if ($form->wasSubmitted() && $form->validate()) {
            $franchiseId = filter_input(INPUT_POST, 'franchise_id');
            if (!empty($franchiseId) && $franchiseId != 'NULL') {
                $franchise = ContactFactory::getModelById($franchiseId);
                if (!empty($franchise)) {
                    Login::setCurrentFranchise($franchise);
                    $success = 1;
                }
            } elseif(Permission::verifyByRef('super_admin')){
                Login::clearCurrentFranchise();
                $success = 1;
            }
            if($success){
                $newUrlAttributes = array(
                        'controller' => 'dashboard',
                        'action' => 'index'
                    );
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                } else {
                    GI_URLUtils::redirect($newUrlAttributes);
                }
            }
            //TODO - add error alert that franchise was not changed
        }

        $returnArray = GI_Controller::getReturnArray($view);
        if ($success && !empty($newUrl)) {
            $returnArray['success'] = $success;
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }
    
    public function actionAutoChangeCurrentFranchise($attributes){
        $success = 0;
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['success'] = 0;
        $returnArray['failReason'] = 'There was an error.';
        if(!isset($attributes['id']) || empty($attributes['id'])){
            $returnArray['failReason'] = 'No ID specified.';
            return $returnArray;
        }
        if (!Permission::verifyByRef('franchise_head_office')) {
            $returnArray['failReason'] = 'Access denied.';
            return $returnArray;
        }
        $id = $attributes['id'];
        $franchise = ContactFactory::getModelById($id);
        if(!$franchise){
            $returnArray['failReason'] = 'Could not find the franchise.';
            return $returnArray;
        }
        
        Login::setCurrentFranchise($franchise);
        $returnArray['success'] = 1;
        
        return $returnArray;
    }
    
    public function actionAutoClearCurrentFranchise($attributes){
        $success = 0;
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['success'] = $success;
        $returnArray['failReason'] = 'There was an error.';
        if (!Permission::verifyByRef('super_admin')) {
            $returnArray['failReason'] = 'Access denied.';
            return $returnArray;
        }
        
        Login::clearCurrentFranchise();
        $returnArray['success'] = 1;
        
        return $returnArray;
    }

}
