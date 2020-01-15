<?php
/**
 * Description of AbstractLayoutView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.2
 */
abstract class AbstractLayoutView extends GI_View {
    
    protected $mainContent = '';
    protected $contentCSSClass = '';
    protected $fullscreen = false;
    protected $addFullscreenToggle = false;
    protected $iconWidthMenu = '26px';
    protected $iconHeightMenu = '26px';

    public function __construct($layoutArray) {
        parent::__construct();
        $this->addSiteTitle(ProjectConfig::getSiteTitle());
        $this->setBase(ProjectConfig::getSiteBase());
        $this->setFavicon('favicon.ico');
        
        $this->addDefaultCSS();
        $this->addDefaultJS();
        
        $this->setMainContent($layoutArray['mainContent']);
        $this->setPageProperties($layoutArray['pageProperties']);
        
        if(ProjectConfig::useFullscreenToggle() && $this->addFullscreenToggle){
            $this->fullscreen = filter_input(INPUT_COOKIE, 'fullscreen');
            if($this->fullscreen){
                $this->addBodyClass('fullscreen');
            }
        }
    }
    
    protected function addDefaultCSS(){
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.structure.min.css');
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.theme.min.css');
        $this->addCSS('resources/external/js/tag-it/tag-it.min.css');
        $this->addCSS('resources/external/js/selectric/selectric_no_style.min.css');
        
        //Bootstrap
        if(ProjectConfig::useBootstrap()){
            $this->addCSS('resources/external/bootstrap/bootstrap-' . ProjectConfig::getBootstrapVersion() . '/css/bootstrap.min.css');
        }
        
        //Font-Awesome
        if(ProjectConfig::useFontAwesome()){
            $this->addCSS('resources/external/font-awesome-4.7.0/css/font-awesome.min.css');
        }
        
        //ColorPickers
        if(ProjectConfig::useColourPicker()){
            $this->addCSS('resources/external/js/wheelcolorpicker/wheelcolorpicker.min.css');
        }
        
        //WYSIWYG
        if(ProjectConfig::useWYSIWYG()){
            $this->addCSS('resources/external/js/trumbowyg/trumbowyg.min.css');
        }
        
        //TABS
        if(ProjectConfig::useTabs()){
            $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/js/tabs/tabs.css');
        }
        
        //WIZARD
        if(ProjectConfig::useWizard()){
            $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/js/wizard/wizard.css');
        }
        
        //SYNTAXHIGHLIGHTER
        if(ProjectConfig::useSyntaxHighlighter()){
            $this->addCSS('resources/external/js/syntaxhighlighter/themes/' . ProjectConfig::getSyntaxHighlighterStyle() . '.css');
        }
        
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/default.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/forms.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/form_view.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/standard_icons.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/files.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/columns.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/wysiwyg.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/gi_ui.css');
        $this->addLayoutCSS();
    }
    
    protected function addLayoutCSS(){
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/main_layout.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/theme.css');
        $this->addCSS('resources/css/theme.css');
    }
    
    protected function addDefaultJS(){
        $this->addJS('resources/external/js/jquery-3.2.1.min.js');
        $this->addJS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        $this->addJS('resources/external/js/jquery.easing.1.3.min.js');
        $this->addJS('resources/external/js/iscroll/iscroll-probe.min.js');
        $this->addJS('resources/external/js/jquery.autosize.min.js');
        $this->addJS('resources/external/js/tag-it/tag-it.min.js');
        $this->addJS('resources/external/js/jquery-ui-timepicker-addon.min.js');
        $this->addJS('resources/external/js/selectric/jquery.selectric.min.js');
        
        //Bootstrap
        if(ProjectConfig::useBootstrap()){
            $this->addJS('resources/external/bootstrap/bootstrap-' . ProjectConfig::getBootstrapVersion() . '/js/bootstrap.min.js');
        }
        
        //ColorPickers
        if(ProjectConfig::useColourPicker()){
            $this->addJS('resources/external/js/wheelcolorpicker/jquery.wheelcolorpicker.min.js');
        }
        
        //WYSIWYG
        if(ProjectConfig::useWYSIWYG()){
            $this->addJS('resources/external/js/trumbowyg/trumbowyg.min.js');
            $this->addJS('resources/external/js/trumbowyg/plugins/cleanpaste/trumbowyg.cleanpaste.min.js');
            $this->addJS('resources/external/js/trumbowyg/plugins/preformatted/trumbowyg.preformatted.min.js');
            $this->addJS('resources/external/js/trumbowyg/plugins/table/trumbowyg.table.min.js');
//            $this->addJS('resources/external/js/trumbowyg/plugins/template/trumbowyg.template.js');
        }
        
        //SYNTAXHIGHLIGHTER
        if(ProjectConfig::useSyntaxHighlighter()){
            $this->addJS('resources/external/js/syntaxhighlighter/syntaxhighlighter.min.js');
        }
        
        //TABS
        if(ProjectConfig::useTabs()){
            $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/tabs/tabs.js');
        }
        
        //WIZARD
        if(ProjectConfig::useWizard()){
            $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/wizard/wizard.js');
        }
        
        //File uploading
        if(ProjectConfig::fileUploads()){
            $this->addJS('resources/external/js/pluploader/plupload.full.min.js');
            $this->addJS('resources/external/js/pluploader/jquery.ui.plupload/jquery.ui.plupload.min.js');
            $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/file_system.js');
        }
    }
    
    public function addAlerts(){
        $pendingAlerts = AlertService::getPendingAlerts();
        if(!$pendingAlerts){
            return $this;
        }
        $this->addHTML('<div id="page_alerts" class="alerts_wrap">');
        foreach($pendingAlerts as $pendingAlert){
            $this->addHTML($pendingAlert->getAlertHTML());
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    public function addHeader($title = '') {
        parent::addHeader($title);
        return $this->addAlerts();
    }

    public function display(){
        $this->addHeader();
        
        $this->addHTML($this->getMainContent());
        
        $this->addFooter();
        echo $this->html;
    }

    public function setMainContent($html) {
        $this->mainContent = $html;
    }

    public function setContentCSSClass($class) {
        $this->contentCSSClass = $class;
    }

    public function getMainContent() {
        $mainContent = $this->mainContent;
        return $mainContent;
    }
    
    protected function openContentWrapDiv($class = '') {
        $this->addHTML('<div id="content_wrap" class="' . $class . '">');
        return $this;
    }

    protected function closeContentWrapDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addMenuBtn(){
        $this->addHTML('<div id="menu_btn" title="Open Navigation Menu"><span class="icon menu" ></span></div>');
        return $this;
    }
    
    protected function openMenuWrapDiv($class = ''){
        $this->addHTML('<div id="main_nav_wrap" class="' . $class . '">');
        return $this;
    }
    
    protected function closeMenuWrapDiv(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openMenuDiv($class = ''){
        $this->addHTML('<div id="main_nav" class="' . $class . '">');
        $this->addFullscreenToggleBtn();
        $this->addHTML('<nav>');
        return $this;
    }
    
    protected function closeMenuDiv(){
        $this->addHTML('</nav></div>');
        return $this;
    }
    
    protected function addMenuContent(){
        return $this;
    }
    
    protected function addFullscreenToggleBtn(){
        if(ProjectConfig::useFullscreenToggle() && $this->addFullscreenToggle){
            if($this->fullscreen){
                $toggleExpandClass = 'minimize';
            } else {
                $toggleExpandClass = 'maximize';
            }
            
            $this->addHTML('<div id="toggle_fullscreen">');
            $this->addHTML('<span class="icon gray ' . $toggleExpandClass . '" ></span>');
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    protected function addMenu(){
        $this->openMenuWrapDiv()
                ->openMenuDiv()
                ->addMenuContent()
                ->closeMenuDiv()
                ->closeMenuWrapDiv();
        return $this;
    }
    
    protected function addLogo($fileName = 'logo.svg', $path="resources/media/img/logos/"){
        $this->addHTML('<a href="." id="logo" title="' . ProjectConfig::getSiteTitle() . '">');
            $logoPath = $path . $fileName;
            if (file_exists($logoPath)){
                $ext = pathinfo($logoPath, PATHINFO_EXTENSION);
                if($ext == 'svg'){
                    $this->addHTML(file_get_contents($logoPath));
                } else {
                    $this->addHTML('<img src="'.$logoPath.'" alt="'.ProjectConfig::getSiteTitle().'" title="'.ProjectConfig::getSiteTitle().'">');
                }
            } else {
                $this->addHTML('<span class="text_logo">'.ProjectConfig::getSiteTitle().'</span>');
            }
            $this->addHTML('</a>');
        return $this;
    }
    
    protected function getMenuTextWithSVGIcon($icon, $title, $classNames = 'left_icon') {
        return $this->getTextWithSVGIcon($icon, $title, $this->iconWidthMenu, $this->iconHeightMenu, $classNames);
    }
}
