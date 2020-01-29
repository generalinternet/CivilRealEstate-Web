<?php

class StaticSuspendedView extends MainWindowView {

    public function __construct() {
        parent::__construct();    
        $this->addSiteTitle('Access Suspended');
        $this->setWindowTitle('Access Suspended');
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->addHTML('<p>We were unable to perform the requested action.</p>');
        $this->addHTML('<p>Your account has been temporarily suspended.</p>');
        $this->addHTML('<p>Please contact the system administrator for futher assistance.</p>'); //TODO - change to use settings for name, email
        $this->addHTML('<hr/>');
             
        $this->closePaddingWrap();
    }
    

    
}
