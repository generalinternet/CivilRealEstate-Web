<?php

require_once 'framework/modules/Contact/' . MODULE_CONTACT_VER . '/controller/AbstractContactController.php';

class ContactController extends AbstractContactController {
    public function actionAngelContactViewPdf($attributes){
        if(!Login::isLoggedIn()){
            GI_URLUtils::redirectToAccessDenied();
        }

        if(!Permission::verifyByRef('view_users')){
            GI_URLUtils::redirectToAccessDenied();
        }

        $userId = $attributes['userId'];
        $user = UserFactory::getModelById($userId);

        if(empty($user)){
            GI_URLUtils::redirectToError();
        }

        $agreementForm = AgreementFormFactory::getAgreementFormByTypeRef();
        $agreement = $agreementForm->getAgreementByUser($user); 
        if(empty($agreement)){
            GI_URLUtils::redirectToError();
        }
 
        $agreement->printOutput();
        exit();
    }
}
