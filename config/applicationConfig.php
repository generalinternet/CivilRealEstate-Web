<?php

class ApplicationConfig extends GI_ApplicationConfig {
    
    protected static $localProtectedActions = array(
        'agreement' => true,
    );
    
    protected static $unprotectedActions = array(
        'user'=>array('signup'),
    );
    
    /**
     * Accessible actions for PublicLayout
     * @var array (ex. [controller] => boolean, [controller] => array of actions) 
     */
    protected static $publicActions = array(
        //@todo:check admin static actions like Dashboard
        'static' => true,
        'qna' => true,
        'user' => array(
            'signup',
            'weAccountDetail',
            'weAccountEdit',
        ),
        //@todo:check other login controller actions
        'login' => array(
            'login',
            'forgotPassword',
            'requestNewPass',
        ),
    );
}
