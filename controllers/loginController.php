<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractLoginController.php';

class LoginController extends AbstractLoginController {
    
    public static function redirectAfterLogout(){
        GI_URLUtils::redirect(array(
            'controller' => 'static',
            'action' => 'home'
        ));
    }

    //put your code here
    public static function redirectAfterLogin(){
        $attributes = GI_URLUtils::getAttributes();
        $targetController = $attributes['controller'];
        $targetAction = $attributes['action'];
        if ($targetController === 'login' && $targetAction == 'index') {
            $attributes['controller'] = GI_ProjectConfig::getDefaultConroller();
            $attributes['action'] = GI_ProjectConfig::getDefaultAction();
        }

        // redirect to investment post if exist previous clicking on "sign up to download"
        $investmentRedirectURL = User::getSignUpSourceURL();
        if(!empty($investmentRedirectURL)){
            Header('Location: ' . $investmentRedirectURL);
            die();
        }

        GI_URLUtils::redirect($attributes);
    }
}
