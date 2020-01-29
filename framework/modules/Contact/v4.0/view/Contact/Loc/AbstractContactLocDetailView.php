<?php

/**
 * Description of AbstractContactLocDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
abstract class AbstractContactLocDetailView extends AbstractContactDetailView {
    
    protected $addCategories = false;
    protected $addContactEvents = false;
    protected $addInterestRates = false;
    protected $addQuickbooksBar = false;
    protected $hasOverlay = false;
    protected $addAssignedToContacts = false;
    protected $addDefaultCurrency = false;
    protected $addUserInfo = false;
    protected $addLabourRates = false;
    protected $addFiles = false;
    
//    public function addInfoSection(\GI_View $view = NULL) {
//        if(is_null($view)){
//            $view = $this;
//        }
//        parent::addInfoSection($view);
//        $view->addHTML('<hr/><div class="auto_columns">');
//        $this->addTypeSection($view);
//        $this->addAccountingTerritorySection($view);
//        $view->addHTML('</div>');
//    }
    protected function addContactInfoBlock(GI_View $view = NULL) {
        parent::addContactInfoBlock();
        $this->addTypeSection($view);
//        $this->addAccountingTerritorySection($view);
    }
    
    protected function addTypeSection(\GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $view->addContentBlockWithWrap($this->contact->getTypeTitle(),'Location Type');
    }
    
    protected function addAccountingTerritorySection(\GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $accountingLocationTag = $this->contact->getAccountingLocationTag();
        if (!empty($accountingLocationTag)) {
            $view->addContentBlockWithWrap($accountingLocationTag->getProperty('title'), 'Accounting Territory');
        }
    }
    
}
