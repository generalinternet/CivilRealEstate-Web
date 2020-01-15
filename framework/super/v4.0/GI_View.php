<?php
/**
 * Description of GI_View
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.10
 */
abstract class GI_View {

    protected $css = array();
    protected $js = array();
    protected $finalHeadHtml = '';
    protected $finalHtml = '';
    protected $metaKeywords = array();
    protected $metaDesc = '';
    protected $metaAuthor = '';
    protected $metaAuthorEmail = '';
    protected $metaTags = array();
    protected $favicon = '';
    protected $siteTitles = array();
    protected $titleDivider = ' | ';
    protected $reverseSiteTitle = false;
    protected $mobileViewport = true;
    protected $charset = 'UTF-8';
    protected $bodyClass = array();
    protected $base = '';
    protected $baseTarget = NULL;
    protected $html;
    protected $dynamicJS = '';
    /** @var AbstractGI_Uploader[] */
    protected static $uploaders = array();
    protected static $recaptchaUsed = false;
    protected $modalClass = '';
    protected $bodyAttrs = array();

    public function __construct() {
        $reverseSiteTitle = ProjectConfig::reverseSiteTitle();
        $this->setReverseSiteTitle($reverseSiteTitle);
        $this->resetHTML();
    }
    
    public function resetHTML(){
        $this->html = '';
        return $this;
    }
    
    protected function saveUploader(AbstractGI_Uploader $uploader){
        $uploaderName = $uploader->getUploaderName();
        if(!isset(static::$uploaders[$uploaderName])){
            static::$uploaders[$uploaderName] = $uploader;
        }
        return $this;
    }
    
    public static function setRecaptchaUsed($recaptchaUsed){
        static::$recaptchaUsed = $recaptchaUsed;
    }

    protected function fileExists($url) {
        if (file_exists($url)) {
            return true;
        } elseif (ini_get('allow_url_fopen')) {
            $url_info = parse_url($url);
            if (isset($url_info['scheme'])) {
                $url_headers = get_headers($url);
                if ($url_headers[0] == 'HTTP/1.1 404 Not Found' || empty($url_headers)) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        //COULD NOT CHECK
        return true;
    }

    protected function unsetAwsS3Vars() {
        static::$awsS3URL = NULL;
        static::$AWSaccessKey = NULL;
        static::$policy = NULL;
        static::$signature = NULL;
        static::$keyBase = NULL;
    }
    
    public function addMetaTag($name, $content){
        $this->metaTags[] = array(
            'name' => $name,
            'content' => $content
        );
        return $this;
    }

    protected function getMetaData() {
        $html = '<meta charset="' . $this->charset . '">';
        if (!empty($this->metaKeywords)) {
            $html .= '<meta name="keywords" content="' . implode(', ', $this->metaKeywords) . '">';
        }
        if (!empty($this->metaDesc)) {
            $html .= '<meta name="description" content="' . $this->metaDesc . '">';
        }
        if (!empty($this->metaAuthor) && !empty($this->metaAuthorEmail)) {
            $html .= '<meta name="author" content="' . $this->metaAuthor . ', ' . $this->metaAuthorEmail . '">';
        }
        
        foreach($this->metaTags as $metaTag){
            $html .= '<meta name="' . $metaTag['name'] . '" content="' . $metaTag['content'] . '" />';
        }
        return $html;
    }
    
    public function addBodyAttr($attr, $val){
        $this->bodyAttrs[$attr] = $val;
        return $this;
    }
    
    protected function getBodyAttrString(){
        $string = '';
        if(!empty($this->bodyAttrs)){
            foreach($this->bodyAttrs as $attr => $val){
                $string .= $attr . '="' . $val . '" ';
            }
        }
        return $string;
    }

    /**
     * Adds the HTML header to the page, and opens the body
     * 
     * @param string $title
     */
    protected function addHeader($title = '') {
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/gi_error.css');
        if (!empty($title)) {
            $this->addSiteTitle($title);
        }
        $html = '<!DOCTYPE html><html><head><base href="' . $this->base . '" ' . $this->getBaseTargetAttr() . ' /><title>' . $this->getSiteTitle() . '</title>' . $this->getMetaData();
        if ($this->mobileViewport) {
            $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black">';
        }
        if (!empty($this->favicon)) {
            $html .= '<link rel="shortcut icon" type="image/x-icon" href="' . $this->favicon . '" />';
        }

        $html .= $this->getCSS();

        $bodyClass = '';
        if(Login::getUserId()){
            $this->addBodyClass('logged_in');
            $user = Login::getUser();
            if($user){
                $this->addBodyAttr('data-user-first-name', $user->getProperty('first_name'));
                $this->addBodyAttr('data-user-last-name', $user->getProperty('last_name'));
            }
        }
        if(ApplicationConfig::isLoginRequired()){
            $this->addBodyClass('protected_page');
        }
        if (!empty($this->bodyClass)) {
            $bodyClass = 'class="' . implode(' ', $this->bodyClass) . '"';
        }
        $bodyAttrString = $this->getBodyAttrString();
        $html .= $this->finalHeadHtml;
        $html .= '</head><body ' . $bodyClass . ' ' . $bodyAttrString . '>';
        $this->html .= $html;
        return $this;
    }
    
    protected function addHTML($html){
        $this->html .= $html;
        return $this;
    }
    
    /**
     * @deprecated since version 2.0
     * @param string $html
     * @return GI_View
     */
    protected function addContent($html) {
        return $this->addHTML($html);
    }
    
    public static function getContentBlock($value, $title = NULL, $forceShow = false, $displayValue = NULL, $emptyValue = '--', $titleTag = NULL){
        $contentBlock = '';
        if(!empty($value) || $forceShow){
            if(!is_null($title)){
                $contentBlock .= static::getContentBlockTitle($title, $titleTag);
            }
            
            if(is_null($displayValue)){
                $displayValue = $value;
            }
            
            if(empty($value)){
                $displayValue = $emptyValue;
            }
            
            $contentBlock .= '<p class="content_block">';
            $contentBlock .= $displayValue;
            $contentBlock .= '</p>';
        }
        return $contentBlock;
    }
    
    protected function addContentBlock($value, $title = NULL, $forceShow = false, $displayValue = NULL, $emptyValue = '--', $titleTag = NULL){
        $this->addHTML(static::getContentBlock($value, $title, $forceShow, $displayValue, $emptyValue, $titleTag));
        return $this;
    }
    
    public static function getContentBlockTitle($title, $titleTag = NULL, $titleClass = ''){
        if(empty($titleTag)){
            $titleTag = DEFAULT_CONTENT_BLOCK_TITLE_TAG;
        }
        $contentBlockTitle = '<' . $titleTag . ' class="content_block_title ' . $titleClass . '" title="' . GI_Sanitize::htmlAttribute($title) . '">' . $title . '</' . $titleTag . '>';
        return $contentBlockTitle;
    }
    
    protected function addContentBlockTitle($title, $titleTag = NULL, $titleClass = ''){
        $this->addHTML(static::getContentBlockTitle($title, $titleTag, $titleClass));
        return $this;
    }
    
    protected function addContentBlockWithWrap($value, $title = NULL, $forceShow = false, $displayValue = NULL, $emptyValue = '--', $titleTag = NULL){
        if(!empty($value) || $forceShow){
            $this->addHTML('<div class="content_block_wrap">');
            $this->addHTML(static::getContentBlock($value, $title, $forceShow, $displayValue, $emptyValue, $titleTag));
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    /**
     * @param string $html HTML string to append to the page AFTER js files have been added
     */
    protected function addFinalContent($html){
        $this->finalHtml .= $html;
        return $this;
    }
    
    /**
     * @param @param string $js JS string to append to the page AFTER js files have been added
     * @return $this
     */
    public function addDynamicJS($js){
        $this->dynamicJS .= $js;
        return $this->addFinalContent('<script type="text/javascript">' . $js . '</script>');
    }
    
    public function getDynamicJS(){
        return $this->dynamicJS;
    }
    
    /**
     * @param string $html HTML string to append to the page RIGHT BEFORE the </head> tag
     */
    protected function addFinalHeaderContent($html){
        $this->finalHeadHtml .= $html;
        return $this;
    }

    protected function getErrors(){
        $errorString = '';
        if (GI_ErrorFactory::getErrorCount()) {
            $errorString .= '<div class="gi_errors_wrap">';
            $errorString .= '<span class="other_btn close_gi_errors"><span class="icon_wrap"><span class="icon eks"></span></span><span class="btn_text">Close All Errors</span></span>';
            $errorString .= GI_ErrorFactory::getErrorString();
            $errorString .= '</div>';
        }
        return $errorString;
    }
    
    protected function getMimeTypeScriptString(){
        $mimeTypesImgs = array(
            array(
                'title' => 'Image files',
                'extensions' => 'jpg,gif,png,bmp,jpeg,tif'
            )
        );
        $mimeTypesAll = array(
            array(
                'title' => 'Image files',
                'extensions' => 'jpg,gif,png,bmp,jpeg,tif'
            ),
            array(
                'title' => 'Zip files',
                'extensions' => 'zip,gtar,tar'
            ),
            array(
                'title' => 'Audio files',
                'extensions' => 'mp3,mpeg,wav'
            ),
            array(
                'title' => 'Video files',
                'extensions' => 'avi,wmv,mov,qt,mp4,swf,ogg,ogv,ae'
            ),
            array(
                'title' => 'Text files',
                'extensions' => 'doc,docx,pdf,ppt,xls,xlsx,xml,csv,txt,ttf,eot,otf,dfont'
            ),
            array(
                'title' => 'Design files',
                'extensions' => 'ai,eps,indd,ps,psd,fla,ico'
            ),
            array(
                'title' => 'Code files',
                'extensions' => 'css,html,htm,js'
            ),
            array(
                'title' => 'Email files',
                'extensions' => 'vcf,vard,msg,eml,email'
            )
        );
        $mimeTypeString = '<script type="text/javascript">';
        $mimeTypeString .= 'var mime_types_all = ' . json_encode($mimeTypesAll) . ';';
        $mimeTypeString .= 'var mime_types_imgs = ' . json_encode($mimeTypesImgs) . ';';
        $mimeTypeString .= '</script>';
        return $mimeTypeString;
    }
    
    public function getUploaderScripts(){
        $uploaderScript = '';
        if (!empty(static::$uploaders)) {
            foreach(static::$uploaders as $uploader){
                $uploaderScript .= $uploader->getScript();
            }
        }
        return $uploaderScript;
    }

    protected function getJSConstString(){
        $constString = '';
        if(ProjectConfig::getHTMLProtocol() == 'https'){
            $constString .= 'const USE_HTTPS = true;';
        } else {
            $constString .= 'const USE_HTTPS = false;';
        }
        if(!empty($constString)){
            return '<script type="text/javascript">' . $constString . '</script>';
        }
        return NULL;
    }

    /**
     * Adds the HTML footer to the page, and closes the body
     */
    protected function addFooter() {
        $errorCount = GI_ErrorFactory::getErrorCount();
        if($errorCount){
            $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_error.js');
        }
        if(static::$recaptchaUsed){
            $this->addJS('https://www.google.com/recaptcha/api.js');
        }
        $this->html .= $this->getJSConstString();
        $this->html .= $this->getJS();
        $this->html .= $this->getMimeTypeScriptString();
        if (!empty(static::$uploaders)) {
            $this->html .= '<script type="text/javascript">';
            $this->html .= '$(function() {';
            $this->html .= static::getUploaderScripts();
            $this->html .= '});';
            $this->html .= '</script>';
        }
        $this->html .= $this->finalHtml;
        if(defined('GOOGLE_ANALYTICS') && !empty(GOOGLE_ANALYTICS)){
            $this->html .= '<script>
            (function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,"script","https://www.google-analytics.com/analytics.js","ga");

            ga("create", "'.GOOGLE_ANALYTICS.'", "auto");
            ga("send", "pageview");
          </script>';
        }
        $this->html .= static::getErrors();
        $this->html .= '</body></html>';
        return $this;
    }
    
    public function beforeReturningView(){
        
    }
    
    public function getHTMLView() {
        $this->beforeReturningView();
        return $this->html;
    }

    public function addBodyClass($class) {
        if (!in_array($class, $this->bodyClass)) {
            array_push($this->bodyClass, $class);
        }
    }

    public function addCSS($cssFile, $checkExists = true) {
        $chkFile = substr($cssFile, 0, strpos($cssFile, "?"));
        $versionAddChar = '?';
        if (empty($chkFile)) {
            $chkFile = $cssFile;
        } else {
            $versionAddChar = '&';
        }
        if (!$checkExists || $this->fileExists($chkFile)) {
            if (!in_array($cssFile, $this->css)) {
                $urlInfo = parse_url($chkFile);
                if (!isset($urlInfo['scheme'])) {
                    $finalCSSFile = $cssFile .= $versionAddChar . 'v=' . filemtime($chkFile);
                } else {
                    $finalCSSFile = $cssFile;
                }
                array_push($this->css, $finalCSSFile);
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function getCSSFilePaths(){
        return $this->css;
    }
    
    protected function getCSS(){
        $html = '';
        if(!empty($this->css)){
            foreach($this->css as $file){
                $html .= '<link rel="stylesheet" type="text/css" href="'.$file.'" />';
            }
        }
        return $html;
    }
    
    public function addJS($jsFile, $checkExists = true){
        $chkFile = substr($jsFile, 0, strpos($jsFile, "?"));
        $versionAddChar = '?';
        if(empty($chkFile)){
            $chkFile = $jsFile;
        } else {
            $versionAddChar = '&';
        }
        if(!$checkExists || $this->fileExists($chkFile)){
            if(!in_array($jsFile, $this->js)){
                $urlInfo = parse_url($chkFile);
                if (!isset($urlInfo['scheme'])){
                    $finalJSFile = $jsFile .= $versionAddChar.'v='.filemtime($chkFile);
                } else {
                    $finalJSFile = $jsFile;
                }
                array_push($this->js,$finalJSFile);
            }
            return true;
        } else {
            return false;
        }
    }
    
    protected function getJS(){
        $html = '';
        if(!empty($this->js)){
            foreach($this->js as $file){
                $html .= '<script type="text/javascript" src="'.$file.'"></script>';
            }
        }
        return $html;        
    }
    
    public function getJSFilePaths(){
        return $this->js;
    }
    
    public function addKeywords($keyword){
        $keywords = explode(',',$keyword);
        foreach($keywords as $word){
            if(!in_array($word,$this->metaKeywords)){
                array_push($this->metaKeywords,$word);
            }
        }
    }
    
    public function getSiteTitle(){
        $titleString = '';
        if($this->reverseSiteTitle){
            $this->siteTitles = array_reverse($this->siteTitles, true);
        }
        $this->siteTitles = array_values($this->siteTitles);
        foreach($this->siteTitles as $key => $title){
            if(!empty($titleString) && !$this->reverseSiteTitle){
                $titleString .= $this->titleDivider;
            }
            $titleString .= $title;
            if($this->reverseSiteTitle && (count($this->siteTitles)-1) != $key){
                $titleString .= $this->titleDivider;
            }
        }
        return $titleString;
    }
    
    public function addSiteTitle($title){
        array_push($this->siteTitles,$title);
    }
    
    public function setBase($base){
        $this->base = $base;
    }
    
    public function setBaseTarget($baseTarget){
        $this->baseTarget = $baseTarget;
    }
    
    protected function getBaseTargetAttr(){
        if($this->baseTarget){
            return 'target="' . $this->baseTarget .'"';
        }
        return NULL;
    }
    
    public function setDescription($content){
        $this->metaDesc = $content;
    }
    
    public function setAuthor($author, $email){
        $this->metaAuthor = $author;
        $this->metaAuthorEmail = $email;
    }
    
    public function setFavicon($favicon){
        if($this->fileExists($favicon)){
            $this->favicon = $favicon;
            return true;
        } else {
            return false;
        }
    }
    public function setTitleDivider($titleDivider){
        $this->titleDivider = $titleDivider;
    }
    public function setReverseSiteTitle($reverseSiteTitle){
        $this->reverseSiteTitle = $reverseSiteTitle;
    }
    public function setMobileViewport($mobileViewport){
        $this->mobileViewport = $mobileViewport;
    }
    public function setCharset($charset){
        $this->charset = $charset;
    }
    
    public function getPageProperties(){
        $properties = array(
            'css' => $this->css,
            'js' => $this->js,
            'metaKeywords' => $this->metaKeywords,
            'metaDesc' => $this->metaDesc,
            'metaAuthor' => $this->metaAuthor,
            'metaAuthorEmail' => $this->metaAuthorEmail,
            'metaTags' => $this->metaTags,
            'favicon' => $this->favicon,
            'siteTitles' => $this->siteTitles,
            'titleDivider' => $this->titleDivider,
            'bodyClass' => $this->bodyClass,
            'finalHtml' => $this->finalHtml
        );
        return $properties;
    }
    
    public function setPageProperties($properties){
        if(isset($properties['css']) && !empty($properties['css'])){
            foreach($properties['css'] as $cssFile){
                if(!in_array($cssFile, $this->css)){
                    array_push($this->css,$cssFile);
                }
            }
        }
        if(isset($properties['js']) && !empty($properties['js'])){
            foreach($properties['js'] as $jsFile){
                if(!in_array($jsFile, $this->js)){
                    array_push($this->js,$jsFile);
                }
            }
        }
        if(isset($properties['metaKeywords']) && !empty($properties['metaKeywords'])){
            foreach($properties['metaKeywords'] as $keyword){
                if(!in_array($keyword, $this->metaKeywords)){
                    array_push($this->metaKeywords,$keyword);
                }
            }
        }
        if(isset($properties['bodyClass']) && !empty($properties['bodyClass'])){
            foreach($properties['bodyClass'] as $class){
                if(!in_array($class, $this->bodyClass)){
                    array_push($this->bodyClass,$class);
                }
            }
        }
        if(isset($properties['siteTitles']) && !empty($properties['siteTitles'])){
            foreach($properties['siteTitles'] as $title){
                array_push($this->siteTitles,$title);
            }
        }
        if(isset($properties['metaDesc']) && !empty($properties['metaDesc'])){
            $this->metaDesc = $properties['metaDesc'];
        }
        if(isset($properties['metaAuthor']) && !empty($properties['metaAuthor'])){
            $this->metaAuthor = $properties['metaAuthor'];
        }
        if(isset($properties['metaAuthorEmail']) && !empty($properties['metaAuthorEmail'])){
            $this->metaAuthorEmail = $properties['metaAuthorEmail'];
        }
        if(isset($properties['metaTags']) && !empty($properties['metaTags'])){
            foreach($properties['metaTags'] as $metaTag){
                $this->addMetaTag($metaTag['name'], $metaTag['content']);
            }
        }
        if(isset($properties['favicon']) && !empty($properties['favicon'])){
            $this->favicon = $properties['favicon'];
        }
        if(isset($properties['titleDivider']) && !empty($properties['titleDivider'])){
            $this->titleDivider = $properties['titleDivider'];
        }
        
        if(isset($properties['finalHtml']) && !empty($properties['finalHtml'])){
        $this->finalHtml .= $properties['finalHtml'];
        }
    }
    public static function getAdvancedBlockButtons($btnOptionsArray, $targetId = NULL, $targetRef = NULL) {
        if (empty($btnOptionsArray)) {
            //A details button is default
            $btnOptionsArray[] = array('type'=>'details');
        }
        $hasDetailsButton = false;
        $advancedButtons = '<span class="advanced_btn_wrap">';
        if (is_array($btnOptionsArray)) {
            foreach ($btnOptionsArray as $btnOptionArray) {
                if (!empty($btnOptionArray) && is_array($btnOptionArray)) {
                    if(isset($btnOptionArray['type']) && $btnOptionArray['type']  == 'details') {
                        $hasDetailsButton = true;
                    }
                    $advancedButtons .= static::getAdvancedBlockButton($btnOptionArray, $targetId, $targetRef);
                }
            }
            if (!$hasDetailsButton) {
                //A details button is default
                $advancedButtons .= static::getAdvancedBlockButton(array('type'=>'details'), $targetId, $targetRef);
            }
        }
        $advancedButtons .= '</span>';
        return $advancedButtons;
    }
    public static function getAdvancedBlockButton($btnOptionArray, $targetId = NULL, $targetRef = NULL) {
        $advancedButton = '';
        $btnType = '';
        if (isset($btnOptionArray['type'])) {
            $btnType = $btnOptionArray['type'];
        }
        switch ($btnType) {
            case 'add':
                $advancedButton = static::getAdvancedBlockLinkButton($btnOptionArray);
                break;
            case 'edit':
                $advancedButton = static::getAdvancedBlockLinkButton($btnOptionArray);
                break;
            case 'delete':
                $advancedButton = static::getAdvancedBlockLinkButton($btnOptionArray);
                break;
            case 'details':
                $advancedButton = static::getAdvancedBlockDetailButton($btnOptionArray, $targetId, $targetRef);
                break;
            default:
                $advancedButton = static::getAdvancedBlockLinkButton($btnOptionArray);
        }
        return $advancedButton;
    }
    
    public static function getAdvancedBlockDetailButton($btnOptionArray, $targetId = NULL, $targetRef = NULL) {
        $advancedButton = '<span class="custom_btn advanced_btn';
        if (isset($btnOptionArray['class_names'])) {
            $advancedButton .= ' '.$btnOptionArray['class_names'];
        }
        $advancedButton .= '"';
        if (!is_null($targetId)) {
            $advancedButton .= ' data-adv-id="'.$targetId.'"';
        }
        if (!is_null($targetRef)) {
            $advancedButton .= ' data-adv-ref="'.$targetRef.'"';
        }
        if (isset($btnOptionArray['other_data'])) { // data-*** = ""
            $advancedButton .= ' '.$btnOptionArray['other_data'];
        }
        $btnTitle = '';
        if (isset($btnOptionArray['title'])) {
            $btnTitle = $btnOptionArray['title'];
        }
        $btnOpenIcon = 'arrow_left border';
        if (isset($btnOptionArray['open_icon'])) {
            $btnOpenIcon = $btnOptionArray['open_icon'];
        }
        $btnCloseIcon = 'arrow_down border';
        if (isset($btnOptionArray['close_icon'])) {
            $btnCloseIcon = $btnOptionArray['close_icon'];
        }
        $advancedButton .= '><span class="icon_wrap"><span class="icon toggle_icon '.$btnOpenIcon.'" data-open-icon="'.$btnOpenIcon.'" data-close-icon="'.$btnCloseIcon.'"></span></span>';
        if(!empty($btnTitle)){
            $advancedButton .= '<span class="btn_text">'.$btnTitle.'</span>';
        }
        $advancedButton .= '</span>';
        
        return $advancedButton;
    }
    
    public static function getAdvancedBlockLinkButton($btnOptionArray) {
        if (isset($btnOptionArray['view'])) {
            //In case of a view, just return the view
            return '<div class="custom_btn link_btn">'.$btnOptionArray['view'].'</div>';
        } else {
            //In case of a link, build html
            $btnType = '';
            if (isset($btnOptionArray['type'])) {
                $btnType = $btnOptionArray['type'];
            }
            $btnTitle = NULL;
            $btnHoverTitle = NULL;
            $btnIcon = NULL;
            switch ($btnType) {
                case 'add':
                    $btnIcon = 'plus';
                    $btnTitle = 'Add';
                    $btnHoverTitle = 'Add';
                    break;
                case 'delete':
                    $btnIcon = 'trash';
                    $btnTitle = 'Delete';
                    $btnHoverTitle = 'Delete';
                    break;
                case 'edit':
                    $btnIcon = 'pencil';
                    $btnTitle = 'Edit';
                    $btnHoverTitle = 'Edit';
                default:
            }

            if (isset($btnOptionArray['title'])) {
                $btnTitle = $btnOptionArray['title'];
                if(empty($btnHoverTitle)){
                    $btnHoverTitle = $btnTitle;
                }
            }
            if(isset($btnOptionArray['hoverTitle'])){
                $btnHoverTitle = $btnOptionArray['hoverTitle'];
            }
            if (isset($btnOptionArray['icon'])) {
                $btnIcon = $btnOptionArray['icon'];
            }
            if (isset($btnOptionArray['icon_class'])){
                $btnIcon .= ' ' . $btnOptionArray['icon_class'];
            }
            $advancedButton = '<a class="custom_btn link_btn';
            if (isset($btnOptionArray['class_names'])) {
                $advancedButton .= ' '.$btnOptionArray['class_names'];
            }
            $advancedButton .= '"';
            if (isset($btnOptionArray['link'])) {
                $advancedButton .= ' href="'.$btnOptionArray['link'].'"';
            }

            if (isset($btnOptionArray['other_data'])) { // data-*** = ""
                $advancedButton .= ' '.$btnOptionArray['other_data'];
            }

            if(!empty($btnHoverTitle)){
                $advancedButton .= ' title="' . $btnHoverTitle . '"';
            }
            if (isset($btnOptionArray['target'])) {
                $advancedButton .= ' target="'.$btnOptionArray['target'].'"';
            }
            $advancedButton .= '>'.GI_StringUtils::getIcon($btnIcon);
            if(!empty($btnTitle)){
                $advancedButton .= '<span class="btn_text">'.$btnTitle.'</span>';
            }
            $advancedButton .= '</a>';

            return $advancedButton;
        }
    }
    
    public function getOpenAdvancedBlockWrap($title, $headerIcon = NULL, $targetRef = NULL, $isOpenOnLoad = NULL, $classNames = '', $isAddToSidebar = false, $btnOptionsArray = NULL, $isEmpty = false, $targetId = NULL, $titleTag = NULL, $ajaxAutoLoadUrl = NULL, $ajaxTargetHTML = NULL){
        $advancedBlock = '<div class="advanced';
        if ($isOpenOnLoad) {
            $advancedBlock .= ' open';
        }
        if ($classNames) {
            $advancedBlock .= ' '.$classNames;
        }
        $advancedBlock .= '"';
        if (!is_null($targetId)) {
            $advancedBlock .= ' id="'.$targetId.'"';
        }
        if (!is_null($targetRef)) {
            $advancedBlock .= ' data-ref="'.$targetRef.'"';
        }
        $advancedBlock .= '>';
        if (is_null($targetId)) {
            $advancedBlock .= '<div class="advanced_header">';
//            if ($isAddToSidebar && !empty($btnOptionsArray)) {
//                //In case of buttons on the sidebar
//                //Show a details button only
//                $btnDetailsOptionArray = array();
//                foreach ($btnOptionsArray as $btnOptionArray) {
//                    if (!empty($btnOptionArray) && is_array($btnOptionArray) && $btnOptionArray['type'] == 'details') {
//                        $btnDetailsOptionArray[] = $btnOptionArray;
//                        break;
//                    }
//                }
//                $advancedBlock .= static::getAdvancedBlockButtons($btnDetailsOptionArray, $targetId, $targetRef);
//            } else {
//                //In case of buttons on the advanced block
//                $advancedBlock .= static::getAdvancedBlockButtons($btnOptionsArray, $targetId, $targetRef);
//            }
            //Show all menus
            $advancedBlock .= static::getAdvancedBlockButtons($btnOptionsArray, $targetId, $targetRef);
            
            //Header icon
            if (!is_null($headerIcon)) {
                $title = $this->getTextWithSVGIcon($headerIcon, $title);
            }
            
            if (is_null($titleTag)) {
                $advancedBlock .= '<h2 class="advanced_title advanced_btn">'.$title.'</h2>';
            } else {
                $advancedBlock .= '<'.$titleTag.' class="advanced_title">'.$title.'</'.$titleTag.'>';
            }
            $advancedBlock .= '</div><!--.advanced_header-->';
        }
        
        $advancedBlock .= '<div class="advanced_content';
        if ($isOpenOnLoad) {
            $advancedBlock .= ' open_load'; //@todo: add CSS or change js
        }
        
        if ($isEmpty) {
            $advancedBlock .= ' empty'; 
        }
        $advancedBlock .= '"';
        
        
        $advancedBlock .= '>';
        
        if (!is_null($ajaxAutoLoadUrl)) {
            $advancedBlock .= '<div class="ajaxed_contents auto_load" id="ajaxed_contents_'.$targetRef.'" data-url="'.$ajaxAutoLoadUrl.'"></div>';
        } else if (!is_null($ajaxTargetHTML)) {
            // Instead of using the 'advanced_content' div, custom target html can be used but it should include ajax url and the class names 'ajaxed_contents auto_load'
            // i.e. <div id="custom_loader" class='ajaxed_contents auto_load' data-url="..."></div>
            $advancedBlock .= $ajaxTargetHTML;
        }
        
        return $advancedBlock;
    }
    
    public function openAdvancedBlockWrap($title, $headerIcon = NULL, $targetRef = NULL, $isOpenOnLoad = NULL, $classNames = '', $isAddToSidebar = false, $btnOptionsArray = NULL, $isEmpty = false, $targetId = NULL, $titleTag = NULL, $ajaxAutoLoadUrl = NULL, $ajaxTargetHTML = NULL) {
        $this->addHTML($this->getOpenAdvancedBlockWrap($title, $headerIcon, $targetRef, $isOpenOnLoad, $classNames, $isAddToSidebar, $btnOptionsArray, $isEmpty, $targetId, $titleTag, $ajaxAutoLoadUrl, $ajaxTargetHTML));
        if($isAddToSidebar && method_exists($this, 'addAdvancedBlockToSidebar')){
            $this->addAdvancedBlockToSidebar($title, $targetRef, $btnOptionsArray, $headerIcon, $classNames);
        }
        return $this;
    }
    
    public function getCloseAdvancedBlockWrap() {
        $advancedBlock = '</div><!--.advanced_content-->';
        $advancedBlock .= '</div><!--.advanced-->';
        
        return $advancedBlock;
    }
    
    public function closeAdvancedBlockWrap() {
        $this->addHTML($this->getCloseAdvancedBlockWrap());
        return $this;
    }
    
    public function getAdvancedBlock($title, $viewHTMLValue, $btnOptionsArray = NULL, $targetId = NULL, $isOpenOnLoad = false, $emptyValue = '--', $titleTag = NULL, $targetRef = NULL, $classNames = '', $headerIcon = NULL, $isAddToSidebar = false, $ajaxAutoLoadUrl = NULL, $ajaxTargetHTML = NULL){
        $isEmpty = false;
        if (empty($viewHTMLValue) && empty($ajaxAutoLoadUrl) && empty($ajaxTargetHTML)) {
            $isEmpty = true; 
        }
        
        $advancedBlock = $this->getOpenAdvancedBlockWrap($title, $headerIcon, $targetRef, $isOpenOnLoad, $classNames, $isAddToSidebar, $btnOptionsArray, $isEmpty, $targetId, $titleTag, $ajaxAutoLoadUrl, $ajaxTargetHTML);
        
        if ($isEmpty) {
            $advancedBlock .= $emptyValue;
        } else {
            $advancedBlock .= $viewHTMLValue;
        }
        
        $advancedBlock .= $this->getCloseAdvancedBlockWrap();
        
        return $advancedBlock;
    }
    
    protected function addAdvancedBlock($title, $viewHTMLValue, $btnOptionsArray = NULL, $targetId = NULL, $isOpenOnLoad = false, $emptyValue = '--', $titleTag = NULL, $targetRef = NULL, $classNames = NULL, $headerIcon = NULL, $isAddToSidebar = false, $ajaxAutoLoadUrl = NULL, $ajaxTargetHTML = NULL){
        $advancedBlock = $this->getAdvancedBlock($title, $viewHTMLValue, $btnOptionsArray, $targetId, $isOpenOnLoad, $emptyValue, $titleTag, $targetRef, $classNames, $headerIcon, $isAddToSidebar, $ajaxAutoLoadUrl, $ajaxTargetHTML);
        $this->addHTML($advancedBlock);
        if($isAddToSidebar && method_exists($this, 'addAdvancedBlockToSidebar')){
            $this->addAdvancedBlockToSidebar($title, $targetRef, $btnOptionsArray, $headerIcon, $classNames);
        }
        
        return $this;
    }
    
    public function addHTMLTag($string, $tag = 'span', $class = '', $id = '', $attrs = array()){
        if(!empty($id)){
            $attrs['id'] = $id;
        }
        if(!empty($class)){
            $attrs['class'] = $class;
        }
        $finalString = GI_StringUtils::surroundWithTag($string, $tag, $attrs);
        $this->addHTML($finalString);
        return $this;
    }
    
    public function addMainTitle($title, $class = 'main_head', $id = '', $attrs = array()){
        return $this->addHTMLTag($title, 'h2', $class, $id, $attrs);
    }
    
    public function addParagraphTitle($title, $class = '', $id = '', $attrs = array()){
        return $this->addHTMLTag($title, 'h4', $class, $id, $attrs);
    }
    
    public function addParagraph($paragraph, $class = '', $id = '', $attrs = array()){
        return $this->addHTMLTag($paragraph, 'p', $class, $id, $attrs);
    }
    
    public function addStrongParagraph($string, $class = '', $id = '', $attrs = array()){
        $finalString = GI_StringUtils::surroundWithTag($string, 'strong');
        return $this->addParagraph($finalString, $class, $id, $attrs);
    }
    
    public function addEmphasizedParagraph($string, $class = '', $id = '', $attrs = array()){
        $finalString = GI_StringUtils::surroundWithTag($string, 'em');
        return $this->addParagraph($finalString, $class, $id, $attrs);
    }
    
    public function addUnorderedList($list = array(), $class = 'simple_list', $id = '', $attrs = array()){
        $liItems = '';
        foreach($list as $item){
            $liItems .= GI_StringUtils::surroundWithTag($item, 'li');
        }
        return $this->addHTMLTag($liItems, 'ul', $class, $id, $attrs);
    }
    
    public function addOrderedList($list = array(), $class = 'simple_list', $id = '', $attrs = array()){
        $liItems = '';
        foreach($list as $item){
            $liItems .= GI_StringUtils::surroundWithTag($item, 'li');
        }
        return $this->addHTMLTag($liItems, 'ol', $class, $id, $attrs);
    }
    
    protected function getTextWithSVGIcon($icon, $title , $width = '26px', $height = '26px', $classNames = 'left_icon') {
        $html = GI_StringUtils::getSVGIcon($icon, $width, $height, $classNames);
        $html .= '<span class="icon_text">'.$title.'</span>';
        return $html;
    }
    
    public function getModalClass(){
        return $this->modalClass;
    }
    
    public function setModalClass($modalClass){
        $this->modalClass = $modalClass;
        return $this;
    }
    
    public function addModalClass($modalClass){
        if(!empty($modalClass)){
            $this->modalClass .= ' ';
        }
        $this->modalClass .= $modalClass;
        return $this;
    }
    
}
