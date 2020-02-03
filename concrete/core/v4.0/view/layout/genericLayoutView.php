<?php

class GenericLayoutView extends GI_View {
    
    protected $mainContent = '';
    
    public function __construct() {
        parent::__construct();
        $this->addSiteTitle(ProjectConfig::getSiteTitle());
        $this->setBase(ProjectConfig::getSiteBase());
        $this->setFavicon('favicon.ico');
        
        $this->addDefaultCSS();
        $this->addDefaultJS();
        
    }
    
    protected function addDefaultCSS(){
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.structure.min.css');
        $this->addCSS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.theme.min.css');
        $this->addCSS('resources/external/js/tag-it/tag-it.min.css');

        //Bootstrap
        if(ProjectConfig::useBootstrap()){
            $this->addCSS('resources/external/bootstrap/bootstrap-' . ProjectConfig::getBootstrapVersion() . '/css/bootstrap.min.css');
        }
        
        //Font-Awesome
        if(ProjectConfig::useFontAwesome()){
            $this->addCSS('resources/external/font-awesome-4.7.0/css/font-awesome.min.css');
        }
        
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/default.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/forms.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/form_view.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/standard_icons.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/files.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/columns.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/wysiwyg.min.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/gi_ui.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/core.css');
        $this->addCSS('framework/core/' . FRMWK_CORE_VER. '/resources/css/main_layout.css');
    }
    
    protected function addDefaultJS(){
        $this->addJS('resources/external/js/jquery-3.2.1.min.js');
        $this->addJS('resources/external/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        $this->addJS('resources/external/js/jquery.easing.1.3.min.js');
        $this->addJS('resources/external/js/iscroll/iscroll-probe.min.js');
        $this->addJS('resources/external/js/jquery.autosize.min.js');
        $this->addJS('resources/external/js/tag-it/tag-it.min.js');
        $this->addJS('resources/external/js/jquery-ui-timepicker-addon.min.js');
        
        //Bootstrap
        if(ProjectConfig::useBootstrap()){
            $this->addJS('resources/external/bootstrap/bootstrap-' . ProjectConfig::getBootstrapVersion() . '/js/bootstrap.min.js');
        }
        
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/forms.js');
        
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/core.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/layout.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/gi_modal.js');
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

    public function getMainContent() {
        $mainContent = $this->mainContent;
        return $mainContent;
    }
    
}
