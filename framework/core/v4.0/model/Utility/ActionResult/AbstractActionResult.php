<?php
/**
 * Description of AbstractActionResult
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractActionResult {
    
    /** @var GI_View */
    protected $view = NULL;
    /** @var GI_DataSearch */
    protected $search = NULL;
    /** @var GI_SearchView */
    protected $searchView = NULL;
    protected $uiTableClass = 'UITableView';
    /** @var AbstractUITableView */
    protected $uiTableView = NULL;
    protected $useAjax = false;
    protected $pageBarLinkProps = array();
    /** @var GI_Model */
    protected $sampleModel = NULL;
    /** @var GI_Model[] */
    protected $models = array();
    /** @var UITableCol[] */
    protected $uiTableCols = NULL;
    protected $redirectArray = array();
    /** @var GI_PageBarView */
    protected $pageBar = NULL;
    
    /** @return GI_View */
    public function getView() {
        return $this->view;
    }

    /** @return GI_DataSearch */
    public function getSearch() {
        return $this->search;
    }

    /** @return GI_SearchView */
    public function getSearchView() {
        return $this->searchView;
    }

    public function getUITableClass(){
        return $this->uiTableClass;
    }
    
    /** @return AbstractUITableView */
    public function getUITableView() {
        if(empty($this->uiTableView)){
            $uiTableClass = $this->getUITableClass();
            $this->uiTableView = new $uiTableClass();
        }
        return $this->uiTableView;
    }

    /** @return boolean */
    public function getUseAjax() {
        return $this->useAjax;
    }
    
    /** @return array */
    public function getPageBarLinkProps() {
        return $this->pageBarLinkProps;
    }
    
    /** @return GI_Model */
    public function getSampleModel() {
        return $this->sampleModel;
    }
    
    /** @return GI_Model[]*/
    public function getModels(){
        return $this->models;
    }
    
    public function getUITableCols(){
        $sampleModel = $this->getSampleModel();
        if(empty($this->uiTableCols) && !empty($sampleModel)){
            $this->uiTableCols = $sampleModel->getUITableCols();
        }
        return $this->uiTableCols;
    }
    
    public function getRedirectArray(){
        return $this->redirectArray;
    }
    
    /** @return GI_PageBarView */
    public function getPageBar(){
        $search = $this->getSearch();
        if(empty($this->pageBar) && !empty($search)){
            $pageBarLinkProps = $this->getPageBarLinkProps();
            $this->pageBar = $search->getPageBar($pageBarLinkProps);
        }
        return $this->pageBar;
    }

    public function setView(GI_View $view) {
        $this->view = $view;
        return $this;
    }

    public function setSearch(GI_DataSearch $search) {
        $this->search = $search;
        return $this;
    }

    public function setSearchView(GI_SearchView $searchView = NULL) {
        $this->searchView = $searchView;
        return $this;
    }

    public function setUITableClass($uiTableClass){
        $this->uiTableClass = $uiTableClass;
        return $this;
    }
    
    public function setUITableView(AbstractUITableView $uiTableView) {
        $this->uiTableView = $uiTableView;
        return $this;
    }

    public function setUseAjax($useAjax) {
        $this->useAjax = $useAjax;
        return $this;
    }
    
    public function setPageBarLinkProps($pageBarLinkProps) {
        $this->pageBarLinkProps = $pageBarLinkProps;
        return $this;
    }
    
    public function setSampleModel(GI_Model $sampleModel) {
        $this->sampleModel = $sampleModel;
        return $this;
    }
    
    public function setModels($models){
        $this->models = $models;
        return $this;
    }
    
    public function setUITableCols($uiTableCols){
        $this->uiTableCols = $uiTableCols;
        return $this;
    }
    
    public function setRedirectArray($redirectArray){
        $this->redirectArray = $redirectArray;
        return $this;
    }
    
    public function setPageBar(GI_PageBarView $pageBar){
        $this->pageBar = $pageBar;
        return $this;
    }

    public function getIndexReturnArray() {
        $returnArray = array();
        
        $view = $this->getView();
        $search = $this->getSearch();
        $uiTableView = $this->getUITableView();
        $sampleModel = $this->getSampleModel();
        $searchView = $this->getSearchView();
        $pageBar = $this->getPageBar();
        
        if($pageBar && $this->getUseAjax()){
            $pageBar->setUseAjax(true);
        }
        
        if(!$uiTableView){
            $models = $this->getModels();
            $uiTableCols = $this->getUITableCols();
            $uiTableView = new UITableView($models, $uiTableCols, $pageBar);
        }
        
        if($sampleModel){
            $uiTableView->setTableWrapId($sampleModel->getTableWrapId());
            $uiTableView->setNoModelMessage('No ' . strtolower($sampleModel->getViewTitle(true)) . ' found.');
        }
        
        if($searchView){
            if($this->getUseAjax()){
                $searchView->setUseAjax(true);
            }
            if($sampleModel){
                $searchView->setTargetElementId($sampleModel->getTableWrapId());
            }
        }
        
        if(GI_URLUtils::getAttribute('search')){
            $redirectArray = $this->getRedirectArray();
            //Basic/advanced search
            if (isset($redirectArray['newUrl'])) {
                //After submitting the basic search, redirect to the new url
                $returnArray['success'] = 1;
                $returnArray['newUrl'] = $redirectArray['newUrl'];
                if (isset($redirectArray['newUrlTargetId'])) {
                    $returnArray['newUrlTargetId'] = $redirectArray['newUrlTargetId'];
                }
                if (isset($redirectArray['jqueryAction'])) {
                    $returnArray['jqueryAction'] = $redirectArray['jqueryAction'];
                }
            } else {
                //Initial loading for the search page
                if($searchView){
                    $searchView->setShowSearchBox(true);
                    $returnArray = GI_Controller::getReturnArray($searchView);
                }
                if($search){
                    $returnArray['returnSearch'] = $search->getSearchValues();
                }
            }
        } else {
            $tabbed = GI_URLUtils::getAttribute('tabbed');
            if (GI_URLUtils::isAJAX() && $tabbed != 1 && $uiTableView) {
                $onlyRows = GI_URLUtils::getAttribute('onlyRows');
                $fullView = GI_URLUtils::getAttribute('fullView');
                if ($onlyRows == 1) {
                    $returnArray['uiTableRows'] = $uiTableView->getRows();
                } elseif ($fullView == 1) {
                    // In case of showing the whole view by ajax
                    $returnArray = GI_Controller::getReturnArray($view);
                    if($sampleModel){
                        $returnArray['breadcrumbs'] = $sampleModel->getBreadcrumbs();
                    }
                } else {
                    $returnArray['uiTable'] = $uiTableView->getHTMLView();
                }
            } elseif($view){
                $returnArray = GI_Controller::getReturnArray($view);
                if($sampleModel){
                    $returnArray['breadcrumbs'] = $sampleModel->getBreadcrumbs();
                }
            }
        }
        return $returnArray;
    }
    
}
