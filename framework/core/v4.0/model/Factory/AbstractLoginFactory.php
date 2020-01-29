<?php
/**
 * Description of AbstractLoginFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.1
 */
class AbstractLoginFactory extends GI_ModelFactory {
    
    //All Abstract Factory classes must contain these 2 fields
    protected static $primaryDAOTableName = 'login';
    protected static $models = array(); //this is used like an object pool by the superclass

    public static function validateModelFranchise(\GI_Model $model) {
        return true;
    }

    //All Abstract Factory classes must have this method defined
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Login($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    //All Abstract Factory classes must have this method defined
    /**
     * 
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    /**
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return Login
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    public static function getLogKey() {
        $userId = Login::getUserId();
        if (empty($userId)) {
            return static::createNewLogin();
        }
        $loginArray = static::search()
                ->filter('user_id', Login::getUserId())
                ->select();
        if ($loginArray) {
            $login = $loginArray[0];
            $logKey = $login->getProperty('log_key');
            $socketUserId = GI_URLUtils::getAttribute('socketUserId');
            if(!empty($socketUserId)){
                $login->setProperty('socket_user_id', $socketUserId);
            }
            if($login->isExpired() && $logKey != NULL) {
                return static::refreshLogin($logKey);
            } else {
                return $logKey;
            }
        } else {
            return static::createNewLogin();
        }
    }

    public static function refreshLogin($logKey) {
        $loginArray = static::search()
                ->filter('log_key', $logKey)
                ->select();
        if ($loginArray) {
            $login = $loginArray[0];
            $login->setProperty('active', 1);
            if ($login->save()) {
                return $login->getProperty('log_key');
            }
        }
        return static::createNewLogin();
    }

    public static function createNewLogin(AbstractUser $user = NULL) {
        $originalLogKey = false;
        //loop to ensure non-duplicate log keys, system-wide
        $login = static::buildNewModel();
        while (!$originalLogKey) {
            $logKey = $login->generateLogKey();
            $loginArray = static::search()
                    ->filter('log_key', $logKey)
                    ->select();
            if (empty($loginArray)) {
                $originalLogKey = true;
            }
        }
        $login->setProperty('active', 1);
        $login->setProperty('log_key', $logKey);
        $socketUserId = GI_URLUtils::getAttribute('socketUserId');
        if(!empty($socketUserId)){
            $login->setProperty('socket_user_id', $socketUserId);
        }
        if (!empty($user)) {
            $userId = $user->getId();
        } else {
            $userId = Login::getUserId();
        }
        if (empty($userId)) {
            return '';
        }
        $login->setProperty('user_id', $userId);
        if ($login->save()) {
            return $logKey;
        }
        return '';
    }
    
    /**
     * @return AbstractLoginIndexView
     */
    public static function getLoginView(){
        $form = new GI_Form('login_form');
        $loginView = new LoginIndexView($form);
        if(static::logIn($form)){
            GI_URLUtils::refresh();
        }
        return $loginView;
    }
    
    public static function attemptToRemember(){
        $cookieEmail = filter_input(INPUT_COOKIE, 'email');
        $cookieSaltyPass = filter_input(INPUT_COOKIE, 'salty_pass');
        $cookieRememberMe = filter_input(INPUT_COOKIE, 'remember_me');
        if ($cookieEmail != NULL && $cookieSaltyPass != NULL & $cookieRememberMe != NULL) {
            $userArray = UserFactory::search()
                    ->filter('email', $cookieEmail)
                    ->select();
            if ($userArray) {
                $user = $userArray[0];
                $pass = $user->getProperty('pass');
                if ($cookieSaltyPass === $pass) {
                    Login::setUserId($user->getId());
                    Login::setUserLang();
                    FolderFactory::verifyUserRootFolder();
                    FolderFactory::verifyTempFolder(); //For ROOT user
                    FolderFactory::verifyUserRootFolder($user);
                    FolderFactory::verifyUserProfilePicturesFolder($user);
                    SessionService::setValue('log_key', static::getLogKey());
                    return true;
                }
            }
        }
        return false;
    }
    
    public static function logIn(GI_Form $form = NULL){
        $cookieRememberMe = filter_input(INPUT_COOKIE, 'remember_me');
        if ($cookieRememberMe != NULL) {
            if(static::attemptToRemember()){
                return true;
            }
            Login::destroySession();
            GI_URLUtils::refresh();
            return false;
        } else if($form && $form->wasSubmitted() && $form->validate()){
            $email = filter_input(INPUT_POST, 'email');
            $password = filter_input(INPUT_POST, 'password');
            $rememberMe = filter_input(INPUT_POST, 'remember_me');
            if (static::validateCredentials($email, $password, $form)) {
                SessionService::setValue('log_key', static::getLogKey());
                if ($rememberMe) {
                    Login::setCookie('email', $email);
                    Login::setCookie('salty_pass', Login::getSaltyPass());
                    Login::setCookie('remember_me', $rememberMe);
                }
                if ($form->fieldErrorCount() == 0) {
                    FolderFactory::verifyUserRootFolder();
                    FolderFactory::verifyTempFolder();
                    return true;
                }
            }
        }
        return false;
    }
    
    public static function validateCredentials($email, $password, GI_Form $form = NULL, $setLogKey = false) {
        $userArray = UserFactory::search()
                ->setAutoFranchise(false)
                ->filter('email', $email)
                ->select();
        if (!empty($userArray)) {
            $user = $userArray[0];
            $pass = $user->getProperty('pass');
            $salt = $user->getProperty('salt');
            $saltyPass = $user->generateSaltyPass($password, $salt);
            if ($saltyPass === $pass) {
                static::loginAsUser($user, $setLogKey);
                return true;
            } else {
                if($form){
                    $form->addFieldError('password', 'invalid', 'Invalid password.');
                }
            }
        } else {
            if($form){
                $form->addFieldError('email', 'invalid', 'Invalid email.');
            }
        }

        return false;
    }
    
    public static function loginAsUser(AbstractUser $user, $setLogKey = true){
        $saltyPass = $user->getProperty('pass');
        Login::setSaltyPass($saltyPass);
        Login::setUserId($user->getId());
        FolderFactory::verifyUserRootFolder($user);
        FolderFactory::verifyUserProfilePicturesFolder($user);
        if($setLogKey){
            SessionService::setValue('log_key', static::getLogKey()); 
        }
        return true;
    }
    
    /**
     * @return GI_DataSearch
     */
    public static function search() {
        $search = parent::search();
        $search->setAutoFranchise(false);
        return $search;
    }

}
