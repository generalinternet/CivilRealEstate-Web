<?php
/**
 * Description of GI_ApplicationConfig
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
abstract class GI_ApplicationConfig {
    
    protected static $properties = array(
        'defaultDAOClass'=>'GenericDAO',
        'defaultDataMapClass'=>'GenericDataMap',
        'defaultDataMapEntryClass'=>'GenericDataMapEntry',
        'defaultTypeModelClass'=>'GenericTypeModel',
        'defaultTypeModelFactoryClass'=>'TypeModelFactory'
    );
    
    /**
     * Inaccessible actions when not logged in
     * @var array (ex. [controller] => boolean, [controller] => array of actions) 
     */
    protected static $protectedActions = array(
        'admin' => true,
        'accounting' => true,
        'billing'=>true,
        'contact' => true,
        'contactevent' => true,
        'content' => true,
        'order'=>true,
        'file' => true,
        'import' => true,
        'inventory' => true,
        'invoice' => true,
        'login' => array(
            'logout',
            'stillHere'
        ),
        'notification' => true,
        'permission' => true,
        'role' => true,
        'tag' => true,
        'user' => true,
        'timesheet' => true,
        'dashboard' => true,
        'project' => true,
        'schedule' => true,
        'forms' => true
    );
    
    /**
     * Use this array to define actions that do not require the user to be logged in, which overrule protected actions defined in $protectedActions
     * @var String[]
     */
    protected static $unprotectedActions = array(
        'accounting'=>array('QBWebhooks'),
    );
    
    /**
     * Inaccessible actions when logged in
     * @var array (ex. [controller] => boolean, [controller] => array of actions) 
     */
    protected static $loginProtectedActions = array(
        'login' => true
    );
    
    protected static $localProtectedActions = array();

    protected static $limitedAccessActions = array();
    
    protected static $localLimitedAccessActions = array();
    
    /**
     * Accessible actions for PublicLayout
     * @var array (ex. [controller] => boolean, [controller] => array of actions) 
     */
    protected static $publicActions = array(
        //@todo:check admin static actions like Dashboard
        //'static' => true,
        'static' => array(
            'home',
        ),
        'qna' => true,
        'login' => true,
        //@todo:check other login controller actions
        'login' => array(
            'login',
            'forgotPassword',
            'requestNewPass',
        ),
    );
    
    protected function __construct() {
        
    }

    protected function __clone() {
        
    }
    
    public static function getProperty($propertyName) {
        if (isset(static::$properties[$propertyName])) {
            return static::$properties[$propertyName];
        } else {
            return NULL;
        }
    }
    
    /**
     * Checks to see if the requested action requires the user to be logged IN
     * 
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    protected static function validateProtectedAction($controller, $action){
        if(isset(static::$protectedActions[$controller])){
            if(is_array(static::$protectedActions[$controller])){
                if(in_array($action, static::$protectedActions[$controller])){
                    if (isset(static::$unprotectedActions[$controller]) && is_array(static::$unprotectedActions[$controller])) {
                        if (in_array($action, static::$unprotectedActions[$controller])) {
                            return false;
                        }
                    }
                    return true;
                }
            } else {
                if (isset(static::$unprotectedActions[$controller]) && is_array(static::$unprotectedActions[$controller])) {
                    if (in_array($action, static::$unprotectedActions[$controller])) {
                        return false;
                    }
                }
                return static::$protectedActions[$controller];
            }
        }

        return false;
    }

    /**
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    protected static function validateLocalProtectedAction($controller, $action){
        if(isset(static::$localProtectedActions[$controller])){
            if(is_array(static::$localProtectedActions[$controller])){
                if(in_array($action, static::$localProtectedActions[$controller])){
                    return true;
                }
            } else {
                return static::$localProtectedActions[$controller];
            }
        }
        
        return false;
    }
    
    /**
     * Checks to see if the requested action requires the user to be logged OUT
     * 
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    protected static function validateLoginProtectedAction($controller, $action){
        if(isset(static::$loginProtectedActions[$controller])){
            if(is_array(static::$loginProtectedActions[$controller])){
                if(in_array($action, static::$loginProtectedActions[$controller])){
                    return true;
                }
            } else {
                return static::$loginProtectedActions[$controller];
            }
        }
        
        return false;
    }
    /**
     * Checks to see if the requested action is for public layout
     * @param type $controller
     * @param type $action
     * @return boolean
     */
    protected static function validatePublicAction($controller, $action){
        if(isset(static::$publicActions[$controller])){
            if(is_array(static::$publicActions[$controller])){
                if(in_array($action, static::$publicActions[$controller])){
                    return true;
                }
            } else {
                return static::$publicActions[$controller];
            }
        }
        
        return false;
    }
    
    public static function isLoginRequired($controller = NULL, $action = NULL){
        if(is_null($controller)){
            $controller = GI_URLUtils::getController();
        }
        
        if(is_null($action)){
            $action = GI_URLUtils::getAction();
        }
        
        if(static::validateProtectedAction($controller, $action)){
            return true;
        }
        
        if(static::validateLocalProtectedAction($controller, $action)){
            return true;
        }
        
        return false;
    }
    
    public static function isLogoutRequired($controller = NULL, $action = NULL){
        if(is_null($controller)){
            $controller = GI_URLUtils::getController();
        }
        
        if(is_null($action)){
            $action = GI_URLUtils::getAction();
        }
        
        if($controller == 'login' && $action == 'index'){
            return false;
        }
        
        if(static::validateLoginProtectedAction($controller, $action) && !static::validateProtectedAction($controller, $action)){
            return true;
        }
        
        return false;
    }
    
    public static function isPublic($controller = NULL, $action = NULL){
        if(is_null($controller)){
            $controller = GI_URLUtils::getController();
        }
        
        if(is_null($action)){
            $action = GI_URLUtils::getAction();
        }
        
        if(static::validatePublicAction($controller, $action)){
            return true;
        }
        
        return false;
    }
    
    /*
     * 'logout',
                'stillHere'
     */
    
    protected static function validateLimitedAccessAction($controller, $action) {
        if (isset(static::$limitedAccessActions[$controller])) {
            if (is_array(static::$limitedAccessActions[$controller])) {
                if (in_array($action, static::$limitedAccessActions[$controller])) {
                    return true;
                }
            } else {
                return static::$limitedAccessActions[$controller];
            }
        }

        return false;
    }

    protected static function validateLocalLimitedAccessAction($controller, $action) {
        if (isset(static::$localLimitedAccessActions[$controller])){
            if(is_array(static::$localLimitedAccessActions[$controller])){
                if(in_array($action, static::$localLimitedAccessActions[$controller])){
                    return true;
                }
            } else {
                return static::$localLimitedAccessActions[$controller];
            }
        }
        
        return false;
    }
    
    public static function isAllowedWithLimitedAccess($controller, $action) {
        if (static::validateLimitedAccessAction($controller, $action) || static::validateLocalLimitedAccessAction($controller, $action)) {
            return true;
        }
        return false;
    }

}
