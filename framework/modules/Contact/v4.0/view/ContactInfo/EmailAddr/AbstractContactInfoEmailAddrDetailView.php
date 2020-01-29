<?php

abstract class AbstractContactInfoEmailAddrDetailView extends GI_View {
    
    protected $contactInfo;
    protected $title = 'Email Address';
    
    public function __construct(AbstractContactInfoEmailAddr $contactInfoEmailAddr) {
        parent::__construct();
        $this->contactInfo = $contactInfoEmailAddr;
        $this->title = $contactInfoEmailAddr->getTypeTitle();
    }
    
    public function setTitle($title){
        $this->title = $title;
    }

    protected function buildView() {
        $emailAddress = $this->contactInfo->getProperty('contact_info_email_addr.email_address');
        if (!empty($emailAddress)) {
            $classNames = '';
            $qbLinked = $this->contactInfo->getProperty('qb_linked');
            if (!empty($qbLinked)) {
                $classNames .= ' qb_linked';
            }

            $this->addHTML('<div class="contact_info_view email_view'.$classNames.'">');
            $this->addContentBlock($emailAddress, $this->title);
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}
