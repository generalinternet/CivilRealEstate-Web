<?php
/**
 * Description of AbstractRepGen
 * 
 * A class for generating report outputs
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractRepGen {
    
    /** @var GI_DataSearch */
    protected $dataSearch = NULL;
    protected $pageNumber = 1;
    protected $itemsPerPage = NULL;
    protected $filename = NULL;
    protected $csvObject = NULL;
    protected $timeTrackerId = NULL;
    protected $reportTitle = 'Report';
    
    public function __construct() {
        $this->itemsPerPage = ProjectConfig::getUITableItemsPerPage();
    }
    
    public function getDataSearch() {
        return $this->dataSearch;
    }

    public function getPageNumber() {
        return $this->pageNumber;
    }
    
    public function getReportTitle() {
        return $this->reportTitle;
    }

    public function getTotalNumberOfPages() {
        if (empty($this->dataSearch) || empty($this->itemsPerPage)) {
            return 0;
        }
        $total = $this->dataSearch->getCount();
        return ceil($total / $this->itemsPerPage);
    }

    public function setDataSearch(GI_DataSearch $dataSearch) {
        $this->dataSearch = $dataSearch;
    }

    public function setPageNumber($pageNumber) {
        $this->pageNumber = $pageNumber;
    }
    
    public function setReportTitle($title) {
        $this->reportTitle = $title;
    }
    
    public function setItemsPerPage($itemsPerPage) {
        $this->itemsPerPage = $itemsPerPage;
    }
    
    public function setFilename($filename) {
        $this->filename = $filename;
    }
    
    public function setTimeTrackerId($timeTrackerId) {
        $this->timeTrackerId = $timeTrackerId;
    }
    
    protected function buildSearch() {
        return false;
    }

    public function generateReport() {
        if (empty($this->dataSearch) && !$this->buildSearch()) {
            return false;
        }
        $results = $this->dataSearch->select();

        if (empty($results)) {
            return false;
        }
        return $this->addResultsToOutput($results);
    }
    
    protected function addResultsToOutput($results) {
        
    }
    
    public function getCSVFilePath() {
        if (!empty($this->csvObject)) {
            return $this->csvObject->getCSVFilePath();
        } 
        return NULL;
    }
    
    public function getProgressBarView($nextURL = NULL) {
        $totalNumberOfPages = $this->getTotalNumberOfPages();
        if (empty($totalNumberOfPages)) {
            $percentage = 100;
        } else {
            $percentage = $this->getPageNumber() / $totalNumberOfPages * 100;
        }
        $view = new GenericProgressBarView();
        $view->setPercentage($percentage);
        $view->setTimeTrackerId($this->timeTrackerId);
        $view->setProgressBarTitle('Exporting ' . $this->getReportTitle());
        $view->setProgressDesc('Preparing ' . $this->getReportTitle());
        if (!empty($nextURL)) {
            $view->setNextURL($nextURL);
        } else {
            $csvFile = $this->getCSVFilePath();
            $view->setProgressForward(false);
            $view->addHTML('<p>Your '.$this->getReportTitle().' is ready to <a href="' . $csvFile . '" target="_blank" title="Download '.$this->getReportTitle().'">download</a>.</p>');
        }
        return $view;
    }
    
}