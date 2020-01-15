<?php

class StaticEULAView extends MainWindowView {
    
    protected $lastUpdated = '2018-07-17';
    
    public function __construct() {
        $this->addSiteTitle('EULA');
        $this->setWindowTitle('End-User License Agreement');
        parent::__construct();
    }
    
    protected function getLastUpdatedDate(){
        return $this->lastUpdated;
    }
    
    protected function getAppName(){
        return ProjectConfig::getAppName();
    }
    
    protected function getLegalName(){
        return ProjectConfig::getLegalName();
    }
    
    public function addViewBodyContent() {
        $this->openPaddingWrap();
        $this->addDocument();
        $this->closePaddingWrap();
    }
    
    protected function addDocument(){
        $this->addParagraph('Last updated: <i>' . GI_Time::formatDateForDisplay($this->lastUpdated) . '</i>');
        
        $this->addParagraph('Please read this End-User License Agreement ("Agreement") carefully before clicking the "I Agree" button, downloading or using ' . $this->getAppName() . ' ("Application").');
        $this->addParagraph('By clicking the "I Agree" button, downloading or using the Application, you are agreeing to be bound by the terms and conditions of this Agreement.');
        $this->addParagraph('If you do not agree to the terms of this Agreement, do not click on the "I Agree" button and do not download or use the Application.');
        
        $this->addParagraphTitle('License');
        $this->addParagraph($this->getLegalName() . ' grants you a revocable, non-exclusive, non-transferable, limited license to download, install and use the Application solely for your personal, non-commercial purposes strictly in accordance with the terms of this Agreement.');
        
        $this->addParagraphTitle('Restrictions');
        $this->addParagraph('You agree not to, and you will not permit others to:');
        $this->addUnorderedList(array(
            'license, sell, rent, lease, assign, distribute, transmit, host, outsource, disclose or otherwise commercially exploit the Application or make the Application available to any third party.'
        ));

        $this->addParagraphTitle('Modifications to Application');
        $this->addParagraph($this->getLegalName() . ' reserves the right to modify, suspend or discontinue, temporarily or permanently, the Application or any service to which it connects, with or without notice and without liability to you.');

        $this->addParagraphTitle('Term and Termination');
        $this->addParagraph('This Agreement shall remain in effect until terminated by you or ' . $this->getLegalName() . '.');
        $this->addParagraph($this->getLegalName() . ' may, in its sole discretion, at any time and for any or no reason, suspend or terminate this Agreement with or without prior notice.');
        $this->addParagraph('This Agreement will terminate immediately, without prior notice from ' . $this->getLegalName() . ', in the event that you fail to comply with any provision of this Agreement. You may also terminate this Agreement by deleting the Application and all copies thereof from your mobile device or from your desktop.');
        $this->addParagraph('Upon termination of this Agreement, you shall cease all use of the Application and delete all copies of the Application from your mobile device or from your desktop.');

        $this->addParagraphTitle('Severability');
        $this->addParagraph('If any provision of this Agreement is held to be unenforceable or invalid, such provision will be changed and interpreted to accomplish the objectives of such provision to the greatest extent possible under applicable law and the remaining provisions will continue in full force and effect.');

        $this->addParagraphTitle('Amendments to this Agreement');
        $this->addParagraph($this->getLegalName() . ' reserves the right, at its sole discretion, to modify or replace this Agreement at any time. If a revision is material we will provide at least 30 (changes this) days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.');

        $this->addParagraphTitle('Contact Information');
        $contactUsURL = GI_URLUtils::buildURL(array(
            'controller' => 'static',
            'action' => 'contact'
        ));
        $this->addParagraph('If you have any questions about this Agreement, please <a href="' . $contactUsURL . '" title="Contact Us">contact us</a>.');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
