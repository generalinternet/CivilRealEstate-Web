<?php

class StaticViewNotificationView extends MainWindowView {
    
    /**
     * @var AbstractNotification
     */
    protected $notification = NULL;

    public function __construct(AbstractNotification $notification) {
        parent::__construct();        
        $this->notification = $notification;
        $this->addSiteTitle('Notification');
        $sbj = $notification->getProperty('sbj');
        $this->addSiteTitle($sbj);
        $this->setWindowTitle($sbj);
        $listBarURL = GI_URLUtils::buildURL(array(
            'controller' => 'notification',
            'action' => 'index',
            'curId' => $this->notification->getId()
        ));
        $this->setListBarURL($listBarURL);
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        
        $this->addHTML('<div class="columns halves">');
            $this->addHTML('<div class="column">');
                $this->addHTML('<p><b>To:</b> ' . $this->notification->getToUserName(), '</p>');
                $this->addHTML('<p><b>From:</b> ' . $this->notification->getFromUserName(), '</p>');
                $this->addHTML('<p><b>Date/Time:</b> ' . $this->notification->getDateAndTime(), '</p>');
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        
        $this->addHTML('<div class="notification_message">');
            $this->addHTML($this->notification->getProperty('msg'));
        $this->addHTML('</div>');
        
        $this->closePaddingWrap();
    }
    
}
