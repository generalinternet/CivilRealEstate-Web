<?php

class StaticSVGIconsView extends MainWindowView {
    
    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('SVG Icons');
        $this->setWindowTitle('SVG Icons');
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
            $this->addHTML('<div id="svg_icon_section">');
                $svgDirPath = 'framework/core/' . FRMWK_CORE_VER. '/resources/media/svgs/';
                $svgFileNames = scandir($svgDirPath);
                $this->addHTML('<div class="content_padding">');
                foreach($svgFileNames as $svgFileName){
                    if (preg_match('/^.*\.(svg)$/i', $svgFileName)) {
                        $ext = pathinfo($svgFileName, PATHINFO_EXTENSION);
                        $svgIcon = substr($svgFileName, (strpos($svgFileName, "icon_svg_") + 9), strlen($svgFileName) - 9 - strlen($ext) - 1);
                        $this->addHTML('<span style="padding:1em;display:inline-block;" title="'.$svgIcon.'">'.GI_StringUtils::getSVGIcon($svgIcon, '2em', '2em').'</span>');
                    }
                }
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        $this->closePaddingWrap();
    }
    
}
