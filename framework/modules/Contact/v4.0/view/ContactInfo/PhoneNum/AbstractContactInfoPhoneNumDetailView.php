<?php

abstract class AbstractContactInfoPhoneNumDetailView extends GI_View {
    
    protected $contactInfo;
    protected $title = 'Phone Number';
    
    public function __construct(AbstractContactInfoPhoneNum $contactInfoPhoneNum) {
        parent::__construct();
        $this->contactInfo = $contactInfoPhoneNum;
        $this->title = $contactInfoPhoneNum->getTypeTitle();
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    protected function buildView() {
        $phoneNum = $this->contactInfo->getProperty('contact_info_phone_num.phone');
        if (!empty($phoneNum)) {
            $classNames = '';
            $qbLinked = $this->contactInfo->getProperty('qb_linked');
            if (!empty($qbLinked)) {
                $classNames .= ' qb_linked';
            }

            $this->addHTML('<div class="contact_info_view phone_view'.$classNames.'">');
            $this->addContentBlock($phoneNum, $this->title);
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}