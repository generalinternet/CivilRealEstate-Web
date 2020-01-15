<?php
/**
 * Description of AbstractGI_Index
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.2
 */
abstract class AbstractGI_Index{
    
    protected static $sessionName = NULL;
    protected static $controller = NULL;
    protected static $action = NULL;
    protected static $trackRequestTime = false;
    /** @var DateTime */
    protected static $requestStartObj = NULL;
    /** @var DateTime */
    protected static $requestEndObj = NULL;
    protected static $requestTime = NULL;
    
    public static function setSessionName($sessionName){
        static::$sessionName = $sessionName;
    }
    
    public static function getSessionName(){
        if(is_null(static::$sessionName)){
            static::setSessionName(ProjectConfig::getSessionName());
        }
        return static::$sessionName;
    }
    
    public static function setController($controller = NULL){
        static::$controller = $controller;
    }
    
    public static function getController(){
        if(empty(static::$controller)){
            static::setController(GI_URLUtils::getController());
        }
        return strtolower(static::$controller);
    }
    
    public static function getAction(){
        if(empty(static::$action)){
            static::setAction(GI_URLUtils::getAction());
        }
        return static::$action;
    }
    
    public static function setAction($action = NULL){
        static::$action = $action;
    }
    
    public static function getCurrentSessionName(){
        return session_name();
    }
    
    public static function startSession(){
        session_name(static::getSessionName());
        session_start();
    }
    
    public static function routeRequest(){
        static::requestStart();
        $attributes = GI_URLUtils::getAttributes();

        $reqController = GI_URLUtils::getController();
        $reqAction = GI_URLUtils::getAction();
        
        if(static::validateLogKey()){
            static::routeLoggedInRequest($reqController, $reqAction);
        } else {
            static::routeLoggedOutRequest($reqController, $reqAction);
        }
        
        $controller = static::getController();
        $action = static::getAction();

        if(GI_URLUtils::isAJAX()){
            static::routeAJAXRequest($controller, $action, $attributes);
        } else {
            static::routePageRequest($controller, $action, $attributes);
        }
        static::requestEnd();
    }
    
    public static function routeAJAXRequest($controller, $action, $attributes){
        $returnArray = static::call($controller, $action, $attributes);

        if (isset($attributes['targetId']) && $attributes['targetId'] == 'main_window' && isset($returnArray['mainContent'])) {
            //Prepend breadcrumb to the main content
            $breadcrumbView = static::getBreadcrumbView($returnArray);
            $returnArray['mainContent'] = '<div id="breadcrumbs">'.$breadcrumbView->getHTMLView().'</div>'.$returnArray['mainContent'];
        }
        
        Header('Content-Type: application/json');
        if(GI_ErrorFactory::getErrorCount()){
            $errorString = GI_ErrorFactory::getErrorString();
            die(json_encode(array(
                'mainContent' => $errorString
            )));
        }
        die(json_encode($returnArray));
    }
    
    public static function routePageRequest($controller, $action, $attributes){
        $layoutArray = static::call($controller, $action, $attributes);
        $menuView = GI_MenuFactory::getMenuView('basic');
        $layoutArray['menu'] = $menuView;
        $layoutArray['breadcrumbMenu'] = static::getBreadcrumbView($layoutArray);
        $user = UserFactory::getModelById(Login::getUserId());
        if($user){
            $layoutArray['userAvatar'] = $user->getUserAvatarView();
        }
        
        $layoutArray['currentUser'] = $user;
        
        $layoutView = static::getLayoutView($layoutArray, $controller, $action);
        
        $layoutView->addBodyClass('controller_' . $controller);
        $layoutView->addBodyClass('action_' . $action);
        if (isset($layoutArray['listBarURL'])) {
            $layoutView->setListBarURL($layoutArray['listBarURL']);
        }
        if (isset($layoutArray['listBarClass'])) {
            $layoutView->setListBarClass($layoutArray['listBarClass']);
        }
        
        Notification::markNotificationViewed();
        $layoutView->display();
    }
    
    public static function getBreadcrumbView($returnArray){
        $breadcrumbView = GI_MenuFactory::getMenuView('basic');
        $breadcrumbView->addMenuItem('main','Home','.');
        if(isset($returnArray['breadcrumbs']) && !empty($returnArray['breadcrumbs'])){            
            foreach($returnArray['breadcrumbs'] as $crumb){
                $label = '';
                if(isset($crumb['label'])){
                    $label = $crumb['label'];
                }
                $link = '';
                if(isset($crumb['link'])){
                    $link = $crumb['link'];
                }
                $breadcrumbView->addMenuItem('main', $label, $link);
            }            
        }
        return $breadcrumbView;
    }
    
    public static function call($controller, $action, $attributes) {
        if(file_exists('controllers/' . $controller . 'Controller.php')){
            require_once('controllers/' . $controller . 'Controller.php');
            $tempController = $controller . 'Controller';
            $fullControllerName = new $tempController();
            $fullActionName = 'action' . ucfirst($action);
            if(method_exists($fullControllerName, $fullActionName) || $controller === 'static'){
                return $fullControllerName->{ $fullActionName }($attributes);
            } else {
                require_once('controllers/staticController.php');
                $staticController = new StaticController();
                return $staticController->errorAction($controller, $action, $attributes);
            }
        } else {
            require_once('controllers/staticController.php');
            $staticController = new StaticController();
            return $staticController->errorController($controller, $action, $attributes);
        }
    }
    
    /**
     * @param array $layoutArray
     * @param string $controller
     * @param string $action
     * @return \AbstractLayoutView
     */
    public static function getLayoutView($layoutArray = array(), $controller = '', $action = ''){
        $layoutView = NULL;
        if(ApplicationConfig::isPublic($controller, $action)){
            $layoutView = new PublicLayoutView($layoutArray);
        } else {
            if($controller !== 'login'){
                $layoutView = new MainLayoutView($layoutArray);
            } else {
                $layoutView = new LoginLayoutView($layoutArray);
            }
        }
        
        return $layoutView;
    }
    
    /**
     * @return AbstractLogin
     */
    public static function validateLogKey(){
        $reqController = GI_URLUtils::getController();
        $reqAction = GI_URLUtils::getAction();
        $ajax = GI_URLUtils::isAJAX();
        $forceLogOut = false;
        if($ajax && $reqController == 'login' && $reqAction == 'index'){
            $forceLogOut = true;
        }
        
        $dbName = dbConfig::getDbName();
        if (!empty($dbName)) {
            if (isset($_SESSION['log_key'])) {
                try {
                    $loginResult = LoginFactory::search()
                            ->filter('log_key', $_SESSION['log_key'])
                            ->select();
                    if ($loginResult) {
                        $login = $loginResult[0];
                        $rememberMe = true;
                        if(filter_input(INPUT_COOKIE, 'remember_me') == NULL){
                            $rememberMe = false;
                        }
                        
                        if (($forceLogOut || $login->isExpired()) && !$rememberMe) {
                            $login->logOut();
                            GI_URLUtils::redirect(GI_URLUtils::getAttributes());
                        } else {
                            static::verifyLoggedInUser($login);
                            return true;
                        }
                    }
                } catch (Exception $e) {
                    trigger_error('Log key validation failed: ' . get_class($e) . ' - ' . $e->getMessage());
                }
            }
        }
        
        return false;
    }
    
    protected static function verifyLoggedInUser(AbstractLogin $login){
        $userId = $login->getProperty('user_id');
        Login::setUserId($userId);
        Login::setUserLang();
        FolderFactory::verifyUserRootFolder();
        $user = UserFactory::getModelById($userId);
        FolderFactory::verifyUserRootFolder($user);
        FolderFactory::verifyUserProfilePicturesFolder($user);
        FolderFactory::verifyTempFolder();
        if(!GI_URLUtils::isAJAX()){
            $login->setProperty('active', 1);
            $login->save();
        }
        return true;
    }
    
    protected static function routeLoggedOutRequest($controller = NULL, $action = NULL){
        if(ApplicationConfig::isLoginRequired($controller, $action)){
            static::setController('login');
            static::setAction('index');
        } else {
            static::setController($controller);
            static::setAction($action);
        }
    }
    
    protected static function routeLoggedInRequest($controller = NULL, $action = NULL){
        if(ApplicationConfig::isLogoutRequired($controller, $action)){
            static::setController(GI_ProjectConfig::getDefaultConroller());
            static::setAction(GI_ProjectConfig::getDefaultAction());
        } else {
            $user = Login::getUser();
            if($user->requiresPassReset() && ApplicationConfig::isLoginRequired($controller, $action)){
                static::setController('login');
                static::setAction('forcePassReset');
            } else {
                static::setController($controller);
                static::setAction($action);
            }
        }
    }
    
    public static function initSystem() {
        static::startSession();
        if (ProjectConfig::getIsQuickbooksIntegrated() && !empty(Login::getUser()) && dbConnection::isModuleInstalled('accounting')) {
            QBTaxCodeFactory::verifyQBTaxCodeData();
        }
    }
    
    public static function setTrackRequestTime($trackRequestTime){
        static::$trackRequestTime = $trackRequestTime;
}
    
    public static function requestStart(){
        if(static::$trackRequestTime){
            static::$requestStartObj = new DateTime();
        }
    }
    
    public static function requestEnd(){
        if(static::$trackRequestTime){
            static::$requestEndObj = new DateTime();
            $startString = GI_Time::formatDateTime(static::$requestStartObj);
            $endString = GI_Time::formatDateTime(static::$requestEndObj);
            static::$requestTime = GI_Time::formatTimeSince($startString, $endString);
            echo '<span class="admin_only">' . static::$requestTime . '</span>';
        }
    }
    
}
