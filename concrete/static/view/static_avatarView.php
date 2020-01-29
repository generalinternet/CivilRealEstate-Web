<?php

class StaticAvatarView extends GI_View{

    public function __construct() {
        parent::__construct();
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addViewBody();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="view_wrap">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    public function addViewBody() {
        $maxNum = 4;
        $width = '200px';
        $height = '200px';
        for($i=1;$i<=$maxNum;$i++){
            $this->addHTML('<span class="avatar_wrap" style="width:' . $width . ';height:' . $height . ';">');
            $this->addHTML(GI_StringUtils::getSVGAvatar($i, $width, $height));
            $this->addHTML('</span>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
