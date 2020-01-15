<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractUserController.php';

class userController extends AbstractUserController {

    public function actionSignup($attributes){
        // check if exist source factor
        if(isset($_GET['source']) && isset($_GET['sourceRef'])){
            User::setSignUpSourceFactor($_GET['source'], $_GET['sourceRef']);
        }

        if (!isset($attributes['step'])) {
            $step = 1;
        } else {
            $step = $attributes['step'];
        }

        // validate sign up
        if(!User::validateSignUp($attributes)){
            GI_URLUtils::redirectToError();
        }

        $id = '';
        if ($step > 2 && !isset($attributes['id'])) {
            $step = 1;
        }

        $isLoggedIn = Login::isLoggedIn();
        if ($isLoggedIn || (
            isset($attributes['id']) &&
            !empty($attributes['id'])
        )) {
            if($isLoggedIn){
                $user = Login::getUser();
            }else{
                $id = $attributes['id'];
                $user = UserFactory::getModelById($id);
            }
            $password = $user->getProperty('pass');
            $loginUserId = Login::getUserId();
            if (!empty($password) && $step > 2 && empty($loginUserId)) {
                GI_URLUtils::redirect(array(
                    'controller' => 'login',
                    'action' => 'index'
                ));
            }
        } else {
            $user = UserFactory::buildNewModel();
        }

        $form = new GI_Form('signup');
        $success = 0;
        $newUrl = NULL;
        $replace = 1;
        $redirect = 0;
        $isAjax = GI_URLUtils::isAJAX();

        $view = new LoginSignupView($form, $user, $step);
        if ($isAjax) {
            $view->setAddWrapper(false);
        }

        if($user->handleStepSignupFormSubmission($form, $step)){
            $success = 1;
            if($step == 1){
                $nextStepAttrs = array(
                    'controller' => 'user',
                    'action' => 'signup',
                    'step' => 2,
                    'ajax' => intval($isAjax),
                );
            }else{
                $userDetail = $user->getPrimeUserDetail();
                $nextStepAttrs = $userDetail->getNextStepAttrs($step, $user->getId());
                if (!empty($user->getId())) {
                    LoginFactory::loginAsUser($user);
                }
                if (!$isAjax) {
                    GI_URLUtils::redirect($nextStepAttrs);
                }

                if ($userDetail->getSignupNextStep($step) == -1) {
                    $redirect = 1;
                    $replace = 0;
                } else {
                    $nextStepAttrs['ajax'] = 1;
                }
            }
            $newUrl = GI_URLUtils::buildURL($nextStepAttrs);
        }

        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if ($isAjax) {
            $returnArray['replaceData'] = $replace;
            if ($success) {
                if (!empty($newUrl)) {
                    $returnArray['newUrl'] = $newUrl;
                    if ($redirect) {
                        $returnArray['redirect'] = $redirect;
                    } else {
                        $returnArray['jqueryCallbackAction'] = 'historyPushState("reload", "'.$newUrl.'", "step_form_wrap");';
                    }
                }
            }
        }

        if($redirect){
            $sourceFactorRedirectURL = User::getSignUpSourceURL();
            if(!empty($sourceFactorRedirectURL)){
                $returnArray['newUrl'] = $sourceFactorRedirectURL;
            }
        }

        return $returnArray;
    }
    
    public function actionBuildSignupStepNav($attributes){
        if (!isset($attributes['investorType']) || !GI_URLUtils::isAJAX()) {
            GI_URLUtils::redirectToError(2000);
        }
        $investorType = $attributes['investorType'];
        
        if (!isset($attributes['step'])) {
            $step = 1;
        } else {
            $step = $attributes['step'];
        }
        
        if (isset($attributes['id'])) {
            $id = $attributes['id'];
            $userDetail = UserDetailFactory::getModelById($id);
            $userDetail->setProperty('user_detail_we.investor_type', $investorType);
            //Saved the change
            $userDetail->save();
        } else {
            $userDetail = UserDetailFactory::buildNewModel('we');
            $userDetail->setProperty('user_detail_we.investor_type', $investorType);
        }

        return array (
            'mainContent' => $userDetail->buildStepNavHTML($step),
        );
        
    }

    public function actionWeAccountDetail($attributes){
        $userId = Login::getUserId();
        $user = UserFactory::getModelById($userId);

        if(isset($attributes['downloadPDF']) && $attributes['downloadPDF'] == 1){
            $agreementForm = AgreementFormFactory::getAgreementFormByTypeRef();
            $agreement = $agreementForm->getAgreementByUser($user);
            $agreement->printOutput();
            exit();
        }

        $view = new UserWeAccountDetailView($user);
        return GI_Controller::getReturnArray($view);
    }

    public function actionWeAccountEdit($attributes){
        $userId = Login::getUserId();
        $user = UserFactory::getModelById($userId);

        $form = new GI_Form('edit_account');

        $stepStr = $attributes['step'];
        $stepNum = UserWeAccountEditView::$STEP_MAPPING[$stepStr]['stepNum'];

        $userDetail = $user->getPrimeUserDetail();
        $investorType = $userDetail->getInvestorType();

        $redirectURL = NULL;

        $view = new UserWeAccountEditView($form, $user, $stepStr);

        if($user->handleStepSignupFormSubmission($form, $stepNum)){
            $redirectURL = array(
                'controller' => 'user',
                'action' => 'weAccountDetail',
            );

            // redirect to accreditation form after update investor type to 'accredited'
            $updatedInvestorType = $userDetail->getInvestorType();
            if(
                $stepNum == UserWeAccountEditView::$STEP_MAPPING['investorProfile']['stepNum'] &&
                $investorType != $updatedInvestorType &&
                $updatedInvestorType == UserDetailFactory::$INVESTOR_TYPE_ACCREDITED
            ){
                // remove client contact record to hidden in Admin contact board
//                $contactModel = ContactFactory::getBySourceUserId($userId);
//                $contactModel->softDelete();

                $redirectURL = array(
                    'controller' => 'user',
                    'action' => 'weAccountEdit',
                    'step' => 'accreditationForm'
                );
            }
        }

        if(!empty($redirectURL)){
            GI_URLUtils::redirect($redirectURL);
        }

        return GI_Controller::getReturnArray($view);
    }
}
