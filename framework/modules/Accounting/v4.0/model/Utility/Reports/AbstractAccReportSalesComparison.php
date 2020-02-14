<?php

/**
 * Description of AbstractAccReportSalesComparison
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportSalesComparison extends AbstractAccReport {

    protected $typeRef = 'sales_comparison';
    protected $title = 'Sales Comparison';
    
    protected $dates = NULL;
    protected $reports = NULL;
    protected $salesTotals = NULL;
    
    public function __construct($typeRef, \DateTime $startDate, \DateTime $endDate) {
        parent::__construct($typeRef, $startDate, $endDate);
    }

    public function getDescription() {
        return 'Shows the past 12 months of sales, compared to the previous 12 months of sales.';
    }

    public function getColour() {
        return '000000';
    }

    public function getInitials() {
        return 'SC';
    }

    public function isViewable() {
        if (Permission::verifyByRef('view_sales_comparison_report')) {
            return true;
        }
        return false;
    }

    public function getDetailView() {
        return new AccReportSalesComparisonDetailView($this);
    }

    public function buildReport() {
        if (!$this->calculateDates()) {
            return false;
        }
        
        if (!$this->buildProfitAndLossReports()) {
            return false;
        }
        
        if (!$this->buildSalesTotals()) {
            return false;
        }
        $this->reportBuilt = true;
        
        return true;
    }

    protected function calculateDates() {
        $today = new DateTime(GI_Time::getDateTime());
        $month = $today->format('m');
        $day = $today->format('d');
        
        $fiscalYearStartAndEndDates = GI_Time::getFiscalYearStartAndEndDates($this->getStartDate());
        if (empty($fiscalYearStartAndEndDates)) {
            return false;
        }

        $currentDate = GI_Time::getCompleteDateInRange($month, $day, $fiscalYearStartAndEndDates['start'], $fiscalYearStartAndEndDates['end']);
        if (empty($currentDate)) {
            return false;
        }
        $this->endDate = $currentDate;
        $startDate = new DateTime($currentDate->format('Y-m-d'));
        $startDate->modify("first day of this month");
        $startDate->modify("-11 months");
        $this->startDate = $startDate;
        
        $this->buildDatesArray('current', $currentDate);
        $previousDate = GI_Time::getCompleteDateInRange($month, $day, $fiscalYearStartAndEndDates['start'], $fiscalYearStartAndEndDates['end']);
        $previousDate->modify("-12 months");
        $this->buildDatesArray('previous', $previousDate);
        if (empty($this->dates)) {
            return false;
        }
        return true;
    }
    
    protected function buildDatesArray($key, DateTime $endDateTime) {
        $workingDateTime = new DateTime($endDateTime->format('Y-m-d') . ' 00:00:00');
        $workingDateTime->modify("-11 months");
        $workingDateTime->modify("first day of this month");
        $specificDates = array();
        while ($workingDateTime < $endDateTime) {
            $startDate = $workingDateTime->format('Y-m-d');
            $workingDateTime->modify(("last day of this month"));
            $endDate = $workingDateTime->format('Y-m-d');
            $dates = array(
                'start'=>$startDate,
                'end'=>$endDate,
            );
            $specificDates[$workingDateTime->format('Y_m')] = $dates;
            $workingDateTime->modify("+1 days");
        }
        if (empty($this->dates)) {
            $this->dates = array();
        }
        $this->dates[$key] = $specificDates;
    }
    
    protected function buildProfitAndLossReports() {
        if (empty($this->dates) || !isset($this->dates['current']) || !isset($this->dates['previous'])) {
          return false;
        }
       $currentDates = $this->dates['current'];
       if (!$this->populateProfitAndLossReports('current', $currentDates)) {
           return false;
       }
       
       $previousDates = $this->dates['previous'];
       if (!$this->populateProfitAndLossReports('previous', $previousDates)) {
           return false;
       }
        
        return true;
    }
    
    protected function populateProfitAndLossReports($key, $dates) {
        $reports = array();
        foreach ($dates as $dateArray) {
            $startDate = new DateTime($dateArray['start']);
            $endDate = new DateTime($dateArray['end']);
            $report = AccReportFactory::buildReportObject('profit_and_loss', $startDate, $endDate, true);
            if (empty($report)) {
                return false;
            }
            $reports[$startDate->format('Y_m')] = $report;
        }
        if (empty($this->reports)) {
            $this->reports = array();
        }
        $this->reports[$key] = $reports;
        return true;
    }

    protected function buildSalesTotals() {
        if (empty($this->reports) || !isset($this->reports['current']) || !isset($this->reports['previous'])) {
            return false;
        }

        $currentReports = $this->reports['current'];
        if (!$this->populateSalesTotalsFromReports('current', $currentReports)) {
            return false;
        }

        $previousReports = $this->reports['previous'];
        if (!$this->populateSalesTotalsFromReports('previous', $previousReports)) {
            return false;
        }
        return true;
    }

    protected function populateSalesTotalsFromReports($key, $reports) {
        if (empty($reports)) {
            return false;
        }
        $totals = array();
        foreach ($reports as $monthKey => $reportObject) {
            $sales = $reportObject->getTotalSales();
            if (empty($sales)) {
                $sales = 0;
            }
            $totals[$monthKey] = $sales;
        }
        if (empty($this->salesTotals)) {
            $this->salesTotals = array();
        }
        $this->salesTotals[$key] = $totals;
        return true;
    }
    
    public function getSalesTotals() {
        if (!$this->reportBuilt && !$this->buildReport()) {
            return NULL;
        }
        return $this->salesTotals;
    }

}
