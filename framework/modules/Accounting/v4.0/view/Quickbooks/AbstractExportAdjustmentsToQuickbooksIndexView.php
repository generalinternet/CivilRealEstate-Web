<?php

/**
 * Description of AbstractExportAdjustmentsToQuickbooksIndexView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */

abstract class AbstractExportAdjustmentsToQuickbooksIndexView extends MainWindowView {
    
    protected $form;
    protected $formBuilt = false;
    
    protected $searchForm;
    protected $searchFormBuilt = false;
    
    protected $tabs = array();
    protected $currentTabKey = 'not_yet_exported';
    protected $currentTabIndex = NULL;
    
    protected $searchStart = NULL;
    protected $searchEnd = NULL;
    
    public function __construct(GI_Form $form, GI_Form $searchForm) {
        parent::__construct();
        $this->form = $form;
        $this->searchForm = $searchForm;
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/quickbooks.js');
        $this->addSiteTitle('Accounting');
        $this->addSiteTitle('Adjustments');
        $this->setWindowTitle('Export Adjustments to Quickbooks');
    }

    /**
     * @param string $currentTabKey
     */
    public function setCurrentTab($currentTabKey) {
        $this->currentTabKey = $currentTabKey;
    }
    
    public function setCurrentTabIndex($currentTabIndex){
        $this->currentTabIndex = $currentTabIndex;
    }

    public function setSearchStart($searchStart){
        $this->searchStart = $searchStart;
        return $this;
    }
    
    public function setSearchEnd($searchEnd){
        $this->searchEnd = $searchEnd;
        return $this;
    }

    protected function addQuickbooksBar() {
        $qbBar = QBConnection::getQuickbooksBarView();
        if ($qbBar) {
            $qbBar->addExportableBarHTML('<span class="qb_btn export_qb_adjustments">Export <b class="export_count">0</b></span>');
            $this->addHTML($qbBar->getHTMLView());
        }
    }

    protected function addSearchStartField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Start Date',
            'value' => $this->searchStart
        ), $overWriteSettings);

        $this->searchForm->addField('search_start', 'date', $fieldSettings);
    }
    
    protected function addSearchEndField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'End Date',
            'value' => $this->searchEnd,
            'minDateFromField' => 'search_start'
        ), $overWriteSettings);

        $this->searchForm->addField('search_end', 'date', $fieldSettings);
    }
    
    protected function addFilterDatesSubmitBtn(){
        $this->searchForm->addHTML('<span class="submit_btn">Filter Dates</span>');
    }
    
    protected function buildSearchForm() {
        if (!$this->searchFormBuilt) {
            $this->searchForm->addHTML('<div class="columns thirds bottom_align">')
                    ->addHTML('<div class="column">');
            $this->addSearchStartField();
            $this->searchForm->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addSearchEndField();
            $this->searchForm->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addFilterDatesSubmitBtn();
            $this->searchForm->addHTML('</div>')
                    ->addHTML('</div>');
            $this->searchFormBuilt = true;
        }
    }

    public function buildView() {
        $this->addQuickbooksBar();
        parent::buildView();
        return $this;
    }
    
    protected function addViewBodyContent(){
        $this->buildSearchForm();
        $this->addHTML('<div class="right_btns">');
        $this->addHTML($this->searchForm->getForm(''));
        $this->addHTML('</div>');
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        return $this;
    }

    protected function buildTabs() {
        $tabs = array();
        $notYetExportedTab = $this->buildNotYetExportedTab();
        if (!empty($notYetExportedTab)) {
            $tabs['not_yet_exported'] = $notYetExportedTab;
        }
        $exportedTab = $this->buildExportedTab();
        if (!empty($exportedTab)) {
            $tabs['exported'] = $exportedTab;
        }
        $excludedTab = $this->buildExcludedTab();
        if (!empty($excludedTab)) {
            $tabs['excluded'] = $excludedTab;
        }
        $tabs[$this->currentTabKey]->setCurrent(true);
        $tabWrap = new GenericTabWrapView($tabs);
        $tabWrap->setTabWrapId('cogs_adj_tabs');
        return $tabWrap;
    }

    protected function addDateFieldsToTabProperties($properties){
        if(!empty($this->searchStart)){
            $properties['searchStart'] = $this->searchStart;
        }
        if(!empty($this->searchEnd)){
            $properties['searchEnd'] = $this->searchEnd;
        }
        return $properties;
    }
    
    protected function buildNotYetExportedTab() {
        $notYetExportedURL = GI_URLUtils::buildURL($this->addDateFieldsToTabProperties(array(
            'controller' => 'accounting',
            'action' => 'getQuickbooksAdjustmentsExportIndexContent',
            'exported' => 0,
            'excluded' => 0,
        )));
        $cogsNotYetExportedTab = new GenericTabView('Not Yet Exported', $notYetExportedURL, true);
        return $cogsNotYetExportedTab;
    }
    
        protected function buildExportedTab() {
        $cogsExportedURL = GI_URLUtils::buildURL($this->addDateFieldsToTabProperties(array(
            'controller' => 'accounting',
            'action' => 'getQuickbooksAdjustmentsExportIndexContent',
            'exported' => 1,
            'excluded' => 0,
        )));
        $exportedTab = new GenericTabView('Previously Exported', $cogsExportedURL, true);
        return $exportedTab;
    }

    protected function buildExcludedTab() {
        $cogsExcludedURL = GI_URLUtils::buildURL($this->addDateFieldsToTabProperties(array(
            'controller' => 'accounting',
            'action' => 'getQuickbooksAdjustmentsExportIndexContent',
            'excluded' => 1,
        )));
        $notYetExportedTab = new GenericTabView('Excluded', $cogsExcludedURL, true);
        return $notYetExportedTab;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<br><br><br>');
            $this->buildFormBody();
            $this->formBuilt = true;
        }
    }

    protected function buildFormBody() {
        $tabWrap = $this->buildTabs();
        if (!empty($tabWrap)) {
            if(isset($this->currentTabIndex)){
                $tabWrap->setCurrentTabByIndex($this->currentTabIndex);
            }
            $this->form->addHTML($tabWrap->getHTMLView());
        }
    }

}
