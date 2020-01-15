<?php

abstract class AbstractPermissionDeniedView extends GI_View {

    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Access Denied');
        $this->buildView();
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML('<h1>Access Denied</h1>');
        $this->addHTML('<p>You do not have permission to perform the requested action. Please contact your system administrator for more information.</p>');
        $this->closeViewWrap();
    }

}
