<?php

abstract class AbstractPermissionDeniedView extends MainWindowView {

    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Access Denied');
        $this->setWindowTitle('Access Denied');
    }
    
    protected function addViewBodyContent() {
        $this->addHTML('<p>You do not have permission to perform the requested action. Please contact your system administrator for more information.</p>');
    }

}
