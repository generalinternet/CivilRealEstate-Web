<?php
/**
 * Description of AbstractLogin
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractLogin extends GI_Model {

    protected static $saltyPass = NULL;
    protected static $userId = NULL;
    protected static $currentFranchise = NULL;
    protected static $forceNoFranchise = false;
    protected static $cookieList = array(
        'email',
        'salty_pass',
        'remember_me',
        'franchise_id',
        'inv_loc_id',
    );
    protected static $jsCookieList = array(
        'myChatInfo',
        'myChatInfoStep',
        'chatOpen',
        'socketUserId',
        'openChatConvoIds',
        'chatDoNotDisturb'
    );
    
    /**
     * @var AbstractUser
     */
    protected static $curUser = NULL;
    
    protected static $minutesToExpire = 30;
    
    public function __construct(\GI_DataMap $map, $factoryClassName = NULL) {
        parent::__construct($map, $factoryClassName);
        $this->map->setUsedState('login.id', true);
        if(!$this->getId()){
            $this->setSystemProperties();
        }
    }
    
    public static function getCookiePath(){
        if(DEV_MODE){
            return ProjectConfig::getSiteBase();
        }
        return '/';
    }

    /**
     * Returns a 64-digit alphanumeric value
     * 
     * @return string - Alphanumeric
     */
    public function generateLogKey() {
        $key = GI_StringUtils::generateRandomString(12, false, true, true, true, false, 63, 0);
        return $key;
    }
    
    public static function isLoggedIn(){
        $userId = static::getUserId();
        if(!empty($userId)){
            return true;
        }
        return false;
    }

    /**
     * Gets user id
     * 
     * @return int user id
     */
    public static function getUserId($returnSystemIfNull = false) {
        if($returnSystemIfNull && is_null(static::$userId)){
            $systemUser = UserFactory::getByEmail('system');
            if($systemUser){
                return $systemUser->getProperty('id');
            }
        }
        return static::$userId;
    }
    
    /**
     * @param int $userId
     * @return AbstractLogin
     */
    public static function getLoginRecord($userId = NULL){
        if(empty($userId)){
            $userId = static::getUserId();
        }
        $loginResult = LoginFactory::search()
                ->filter('user_id', $userId)
                ->select();
        if($loginResult) {
            $login = $loginResult[0];
            return $login;
        }
        return NULL;
    }
    
    public static function getSocketId($userId = NULL){
        $login = static::getLoginRecord($userId);
        if($login){
            return $login->getProperty('socket_id');
        }
        return NULL;
    }
    
    public static function getSocketUserId($userId = NULL){
        $login = static::getLoginRecord($userId);
        if($login){
            return $login->getProperty('socket_user_id');
        }
        return NULL;
    }
        
    /** @return AbstractContactOrgFranchise */
    public static function getCurrentFranchise() {
        if (!ProjectConfig::getIsFranchisedSystem()) {
            return NULL;
        }
        if (!empty(static::$currentFranchise)) {
            return static::$currentFranchise;
        }
        if(static::$forceNoFranchise){
            return NULL;
        }
        
        $franchiseId = NULL;
        if (Permission::verifyByRef('franchise_head_office')) {
            $franchiseId = filter_input(INPUT_COOKIE, 'franchise_id');
        } 
        if (empty($franchiseId)) {
            $user = static::getUser();
            if (!empty($user)) {
                $franchiseId = $user->getProperty('franchise_id');
            }
        }
        
        $franchise = ContactFactory::getModelById($franchiseId);
        if(empty($franchise) && !Permission::verifyByRef('super_admin')){
            $search = ContactFactory::search()
                    ->filterByTypeRef('franchise')
                    ->orderBy('id')
                    ->setSortAscending(true)
                    ->setItemsPerPage(1);
            
            ContactFactory::addFranchiseFiltersForFranchiseList($search);
        
            $franchiseResults = $search->select();
            
            if($franchiseResults){
                $franchise =  $franchiseResults[0];
            }
        }
        static::setCurrentFranchise($franchise);
        return $franchise;
    }
    
    public static function setForceNoFranchise($forceNoFranchise){
        static::$forceNoFranchise = $forceNoFranchise;
    }

    public static function setCurrentFranchise(AbstractContactOrgFranchise $franchise = NULL) {
        if (ProjectConfig::getIsFranchisedSystem() && !empty($franchise)) {
            self::$currentFranchise = $franchise;
            if (Permission::verifyByRef('franchise_head_office')) {
                $franchiseId = $franchise->getProperty('id');
                static::setCookie('franchise_id', $franchiseId);
            }
        }
    }
    
    public static function clearCurrentFranchise(){
        if (!ProjectConfig::getIsFranchisedSystem() || Permission::verifyByRef('super_admin')) {
            static::clearCookie('franchise_id');
        }
    }

    public static function setSaltyPass($saltyPass) {
        static::$saltyPass = $saltyPass;
    }

    public static function getSaltyPass(){
        return static::$saltyPass;
    }

    /**
     * Sets user id
     * 
     * @param int $userId user id
     */
    public static function setUserId($userId) {
        static::$userId = $userId;
    }

    /**
     * Gets current user
     * 
     * @return AbstractUser
     */
    public static function getUser($returnSystemIfNull = false){
        if(is_null(static::$curUser)){
            static::$curUser = UserFactory::getModelById(static::getUserId($returnSystemIfNull));
        }
        return static::$curUser;
    }
    
    /**
     * Logout function
     * 
     * @return boolean true if logout process is completed successfully
     */
    public function logOut() {
        if ($this->delete()) {
            static::destroySession();
            return true;
        }
        return false;
    }

    protected function delete() {
        if ($this->map->delete()) {
            return true;
        }
        return false;
    }
    
    public static function getMinutesToExpire(){
        return static::$minutesToExpire;
    }
    
    public function isExpired(){
        $lastMod = $this->getProperty('last_mod');
        $lastModObj = new DateTime($lastMod);
        $expiredObj = new DateTime('-' . static::getMinutesToExpire() . ' minutes');

        if ($lastModObj < $expiredObj) {
            $this->setProperty('active', 0);
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Clears cookies
     */
    public static function clearCookies() {
        foreach(static::$cookieList as $cookie){
            static::clearCookie($cookie);
        }
        foreach(static::$jsCookieList as $cookie){
            static::clearCookie($cookie);
        }
    }
    
    public static function getCookieName($cookie){
        if(in_array($cookie, static::$jsCookieList)){
            return ProjectConfig::getSessionName() . '_' . $cookie;
        }
        return $cookie;
    }
    
    public static function clearCookie($cookie){
        $cookieName = static::getCookieName($cookie);
        $dateObj = new DateTime('-1 day');
        $cookieExpiry = $dateObj->getTimestamp();
        $cookiePath = static::getCookiePath();
        setcookie($cookieName, NULL, $cookieExpiry, $cookiePath);
        return true;
    }
    
    public static function setCookie($cookie, $value, $expiry = '+30 days'){
        $cookieName = static::getCookieName($cookie);
        $dateObj = new DateTime($expiry);
        $cookieExpiry = $dateObj->getTimestamp();
        $cookiePath = static::getCookiePath();
        setcookie($cookieName, $value, $cookieExpiry, $cookiePath);
        return true;
    }
    
    public static function getCookie($cookie){
        $cookieName = static::getCookieName($cookie);
        return filter_input(INPUT_COOKIE, $cookieName);
    }

    /**
     * Destroys all data registered to a session
     */
    public static function destroySession() {
        static::clearCookies();
        session_destroy();
    }
    
    public static function getIPAddr(){
        $httpClientIP = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
        $httpXForwardedFor = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
        $remoteAddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        if (!empty($httpClientIP)) {
            $ip = $httpClientIP;
        } elseif (!empty($httpXForwardedFor)){
            $ip = $httpXForwardedFor;
        } else {
            $ip = $remoteAddr;
        }
        return $ip;
    }

    protected function getHttpUserAgent() {
        $httpUserAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        return $httpUserAgent;
    }

    protected function getInfoURL() {
        $curURL = ltrim(filter_input(INPUT_SERVER, 'REQUEST_URI'), '/');
        if (empty($curURL)) {
            $curURL = '.';
        }
        return $curURL;
    }

    protected function getHttpRef() {
        $httpRef = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        return $httpRef;
    }

    protected function getDeviceType() {
        /**
         * @todo set "type" property to device type
         */
        $deviceType = 'desktop';
        $detect = new \Detection\MobileDetect();
        if($detect->isMobile()){
            $deviceType = 'mobile';
            if($detect->isTablet()){
                $deviceType = 'tablet';
            }
        }
        return $deviceType;
    }

    /**
     * Sets the properties for the Login instance for which the values can be determined from the system
     */
    public function setSystemProperties() {
        $ip = static::getIPAddr();
        $this->setProperty('info_ip', $ip);
        
        $deviceType = static::getDeviceType();
        $this->setProperty('type', $deviceType);
        
        $httpRef = static::getHttpRef();
        $this->setProperty('info_ref', $httpRef);
        
        $httpUserAgent = static::getHttpUserAgent();
        $this->setProperty('info_agent', $httpUserAgent);
        
        $curURL = static::getInfoURL();
        $this->setProperty('info_url', $curURL);
    }

    /**
     * Sets user's language to a session
     */
    public static function setUserLang() {
        if (!isset($_SESSION['user_lang'])) {
            $user = UserFactory::getModelById(static::$userId);
            $userLang = $user->getProperty('language');
            if ($userLang) {
                $filename = 'lang_' . $userLang . '.php';
                require_once('config/language/' . $filename);
                $_SESSION['user_lang'] = $userLang;
            } else {
                require_once('config/language/lang_english.php');
                $_SESSION['user_lang'] = 'english';
            }
        } else {
            $userLang = $_SESSION['user_lang'];
            if ($userLang) {
                $filename = 'lang_' . $userLang . '.php';
                require_once('config/language/' . $filename);
            } else {
                require_once('config/language/lang_english.php');
                $_SESSION['user_lang'] = 'english';
            }
        }
    }

    /*     * Temp* */

    public static function isPublicUser() {
        $user = Login::getUser();
        if (!empty($user)) {
            $roleArray = RoleFactory::search()
                ->filterIn('title', ['Limited User', 'QnA Vendor'])
                ->select(true);
           
            if (!empty($roleArray)) {
                $currentUserRole = $user->getRole();
                if($currentUserRole && !array_key_exists($currentUserRole->getId(), $roleArray)){
                    return false;
                }
            }
        }
        return true;
    }
}
