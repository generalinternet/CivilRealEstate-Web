<?php
/**
 * Description of GI_URLUtils
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
class GI_URLUtils {

    protected static $attributes = array();
    protected static $parsedURLStrings = array();
    
    public static function buildURL($attributes, $includeBase = false, $forceUnclean = false) {
        if (empty($attributes) ||  !isset($attributes['controller']) ||  !isset($attributes['action'])) {
            return NULL;
        }
        
        $baseURL = ProjectConfig::getProjectBase();
        
        if($forceUnclean || !ProjectConfig::cleanURLs()){
            $url = $baseURL . '/index.php?controller=';
        } else {
            return static::buildCleanURL($attributes, $includeBase);
        }
        
        $controller = $attributes['controller'];
        $action = $attributes['action'];

        if ($forceUnclean || !ProjectConfig::cleanURLs()) {
            $url .= $controller . '&action=' . $action;
        } else {
            if ($controller === 'static') {
                $url .= '/' . $action;
            } elseif ($controller !== GI_ProjectConfig::getDefaultController() || $action !== GI_ProjectConfig::getDefaultAction()) {
                $url .= '/' . $controller . '/' . $action;
            } else {
                $includeBase = true;
            }
        }
        unset($attributes['controller']);
        unset($attributes['action']);
        foreach ($attributes as $column => $value) {
            if ($forceUnclean || !ProjectConfig::cleanURLs()) {
                $url .= '&' . $column . '=' . $value;
            } else {
                $url .= '/'.$value;
            }
        }
        
        if($includeBase){
            $url = GI_URLUtils::getBaseURL(true) . $url;
        }
        
        return $url;
    }
    
    public static function buildCleanURL($attributes, $includeBase = false){
        if (empty($attributes) ||  !isset($attributes['controller']) ||  !isset($attributes['action'])) {
            return NULL;
        }
        
        $baseURL = ProjectConfig::getProjectBase();
        
        $url = $baseURL;
        
        $controller = $attributes['controller'];
        $action = $attributes['action'];

        if ($controller === 'static') {
            $url .= '/' . $action;
        } elseif ($controller !== GI_ProjectConfig::getDefaultController() || $action !== GI_ProjectConfig::getDefaultAction()) {
            $url .= '/' . $controller . '/' . $action;
        } else {
            $includeBase = true;
        }
        
        unset($attributes['controller']);
        unset($attributes['action']);
        foreach ($attributes as $column => $value) {
            $url .= '/'.$value;
        }
        
        if($includeBase){
            $url = GI_URLUtils::getBaseURL(true) . $url;
        }
        
        return $url;
    }
    
    public static function getAttribute($attribute){
        $attributes = static::getAttributes();
        if(isset($attributes[$attribute])){
            return $attributes[$attribute];
        }
        return NULL;
    }
    
    public static function getAttributes() {
        if(empty(static::$attributes)){
            $attributes = filter_input_array(INPUT_GET);

            if(isset($attributes['controller'])){
                $attributes['controller'] = strtolower($attributes['controller']);
            } else {
                $attributes['controller'] = NULL;
            }

            if(isset($attributes['action'])){
                $attributes['action'] = $attributes['action'];
            } else {
                $attributes['action'] = NULL;
            }

            if(empty($attributes['controller']) || empty($attributes['action'])) {
                $attributes['controller'] = GI_ProjectConfig::getDefaultController();
                $attributes['action'] = GI_ProjectConfig::getDefaultAction();
            }
            static::$attributes = $attributes;
        }
        
        return static::$attributes;
    }
    
    public static function setAttribute($attribute, $value){
        $attributes = static::getAttributes();
        $attributes[$attribute] = $value;
        
        static::$attributes = $attributes;
    }
    
    public static function getBaseURL($fullBase = false) {
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
        if($fullBase){
            $base = '';
            if(DEV_MODE){
                $base .= ProjectConfig::getHTMLProtocol() . '://';
                $base .= $host;
                return $base; //TEMP?
            }
            $base .= ProjectConfig::getSiteBase();
            return $base;
        }
        return $host;
    }
    
    public static function redirect($attributes) {
        EventService::processEvents();
        $url = static::buildURL($attributes);
        Header('Location: ' . $url);
        exit();
    }
    
    public static function redirectToURL($url){
        Header('Location: ' . $url);
        exit();
    }
    
    public static function redirectToError($code = NULL, $message = NULL, $returnURL = NULL){
        $attributes = array(
            'controller' => 'static',
            'action' => 'error'
        );
        if($code){
            $attributes['errorCode'] = $code;
        }
        if($message){
            $attributes['errorMsg'] = $message;
        }
        if($returnURL){
            $attributes['returnURL'] = urlencode($returnURL);
        }
        if(static::isAJAX()){
            $attributes['ajax'] = 1;
        }
        static::redirect($attributes);
    }
    
    public static function redirectToQBError(QuickBooksOnline\API\Core\HttpClients\FaultHandler $error) {
        $rawResponseBody = $error->getResponseBody();
        $error->parseResponse($rawResponseBody);
        $message = '';
        $message .= '<b>'.$error->getIntuitErrorMessage() . '</b><br>';
        $message .= $error->getIntuitErrorDetail() . '<br><br>';
        $message .= 'Technical Reference: <br>';
        $message .= $error->getIntuitErrorCode() . ' ' .  $error->getIntuitErrorType() .'<br>';
        $message .= $error->getIntuitTid() . '<br>';
        static::redirectToError(6000, $message);
    }


    public static function getLastAttributes() {
        $lastAttributes = SessionService::getValue('last_attributes');
        if (!empty($lastAttributes)) {
            return $lastAttributes;
        }
        return NULL;
    }

    public static function setLastAttributes($attributes = array()) {
        if(empty($attributes)){
            $attributes = static::getAttributes();
        }
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            unset($attributes['ajax']);
        }
        if(empty($attributes)){
            SessionService::unsetValue('last_attributes');
        } else {
            SessionService::setValue('last_attributes', $attributes);
        }
    }

    public static function redirectToAccessDenied() {
        if(!Login::isLoggedIn()){
            static::setLastAttributes();
        }
        $attributes = array(
            'controller' => 'permission',
            'action' => 'denied'
        );
        if(static::isAJAX()){
            $attributes['ajax'] = 1;
        }
        static::redirect($attributes);
    }
    
    public static function isAJAX(){
        $ajax = filter_input(INPUT_GET, 'ajax');
        if($ajax == 1){
            return true;
        }
        return false;
    }
    
    public static function getController(){
        $controller = filter_input(INPUT_GET, 'controller');
        if(empty($controller)){
            $controller = GI_ProjectConfig::getDefaultController();
        }
        return $controller;
    }
    
    public static function getAction(){
        $action = filter_input(INPUT_GET, 'action');
        if(empty($action)){
            $action = GI_ProjectConfig::getDefaultAction();
        }
        return $action;
    }
    
    public static function refresh(){
        Header('Refresh: 0');
        exit();
    }
    
    /**
     * Checks whether the given link is the current page
     * 
     * @param string $link
     * @param boolean $strictLinkCheck if false this also checks to see if the current page is a sub link of the given link
     * @return boolean
     */
    public static function isLinkCurrent($link, $strictLinkCheck = false) {
        if(empty($link)){
            return false;
        }
        $urlArray = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
        $untrimmedCurURL = implode('/', $urlArray);
        $curURL = substr($untrimmedCurURL, strlen(ProjectConfig::getProjectBase()));
        
        $lowerRequestURI = strtolower($_SERVER['REQUEST_URI']);
        $lowerLink = strtolower($link);
        $lowerCurURL = strtolower($curURL);
        
        $current = false;
        if ($lowerLink == $lowerRequestURI || ltrim($lowerLink, '/') == $lowerCurURL || $lowerCurURL == $lowerLink) {
            $current = true;
        }
        
        if(!$current && !$strictLinkCheck){
            if($lowerLink == '.' && (empty($lowerCurURL) || $lowerCurURL == 'index.php')){
                $current = true;
            }
            /*elseif (substr($lowerRequestURI, 0, strlen($lowerLink)) == $lowerLink || substr($lowerCurURL, 0, strlen($lowerLink)) == $lowerLink) {
                $current = true;
            }*/
        }
        
        return $current;
    }
    
    public static function getControllerFromURL($urlString){
        $urlAttrs = static::getAttributesFromURL($urlString);
        if(isset($urlAttrs['controller'])){
            return $urlAttrs['controller'];
        }
        return NULL;
    }
    
    public static function getActionFromURL($urlString){
        $urlAttrs = static::getAttributesFromURL($urlString);
        if(isset($urlAttrs['action'])){
            return $urlAttrs['action'];
        }
        return NULL;
    }
    
    public static function getAttributesFromURL($urlString){
        if(!isset(static::$parsedURLStrings[$urlString])){
            $tmpAttrs = explode('&', $urlString);
            $attributes = array();
            foreach($tmpAttrs as $tmpAttrString){
                $attrData = explode('=', $tmpAttrString);
                $attr = str_replace(array(
                    'index.php',
                    '?'), '', $attrData[0]);
                $value = $attrData[1];
                $attributes[$attr] = $value;
            }
            static::$parsedURLStrings[$urlString] = $attributes;
        }
        return static::$parsedURLStrings[$urlString];
    }

}
