<?php

require_once 'framework/core/' . FRMWK_CORE_VER . '/controller/AbstractLoginController.php';

class LoginController extends AbstractLoginController {
    public static function redirectAfterLogin(){
        $attributes = GI_URLUtils::getAttributes();
        $targetController = $attributes['controller'];
        $targetAction = $attributes['action'];
        if ($targetController === 'login' && $targetAction == 'index') {
            $attributes['controller'] = GI_ProjectConfig::getDefaultConroller();
            $attributes['action'] = GI_ProjectConfig::getDefaultAction();
        }

        GI_URLUtils::redirect($attributes);
    }
}
