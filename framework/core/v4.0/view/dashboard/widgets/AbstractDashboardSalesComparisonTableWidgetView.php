<?php
/**
 * Description of AbstractDashboardSalesComparisonTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractDashboardSalesComparisonTableWidgetView extends AbstractDashboardWidgetView {

    protected $report;
    protected $todayDateTime;

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setHeaderIcon('dollars');
        $this->setTitle('Sales Comparison');
        $todayDateTime = new DateTime(GI_Time::getDateTime());
        $this->todayDateTime = $todayDateTime;
        $this->report = AccReportFactory::buildReportObject('sales_comparison', $todayDateTime, $todayDateTime, true);
    }

    protected function determineIsViewable() {
        if (Permission::verifyByRef('view_sales_comparison_dashboard_widget')) {
            return true;
        }
        return false;
    }

    public function buildBodyContent() {
        if (empty($this->report)) {
            $this->addHTML('<p>Data Unavailable</p>');
            return;
        }
        if (!$this->report->buildReport()) {
            return;
        }
        $totals = $this->report->getSalesTotals();
        if (empty($totals) || !isset($totals['current']) || !isset($totals['previous'])) {
            $this->addHTML('<p>Error loading data.</p>');
        } else {
            $this->addHTML('<h4 class="chart_title">As of ' . GI_Time::formatDateForDisplay(GI_Time::getDate()) . '</h4>');
            $this->buildTable($totals);
            $currencyTitle = $this->report->getCurrencyTitle();
            if (!empty($currencyTitle)) {
                $this->addHTML('<h6>All values in ' . $currencyTitle . '</h6>');
            }
        }
    }

    protected function buildTable($totals) {
        $this->addHTML('<div class="flex_table ui_table">');
        $this->buildTableHeader();
        $this->buildTableRows($totals);
        $this->addHTML('</div>');
    }

    protected function buildTableHeader() {
        $this->addHTML('<div class="flex_row flex_head">')
                ->addHTML('<div class="flex_col">')
                ->addHTML('')
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('Previous Yr.')
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('Current Yr.')
                ->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildTableRows($totals) {
        $months = array();
        foreach ($totals['previous'] as $monthKey => $previousTotal) {
            $monthKey = str_replace('_', '-', $monthKey);
            $monthDateTime = new DateTime($monthKey . '-01 00:00:00');
            $monthName = $monthDateTime->format('F');
            $months[] = $monthName;
        }
        $currentTotals = array_values($totals['current']);
        $previousTotals = array_values($totals['previous']);
        $count = count($previousTotals);
        for ($i=($count-1);$i>5;$i--) {
             $currentTotal = '$' . GI_StringUtils::formatMoney($currentTotals[$i]);
             $previousTotal = '$' . GI_StringUtils::formatMoney($previousTotals[$i]);
             $monthName = $months[$i];
            $this->addHTML('<div class="flex_row">')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML($monthName)
                    ->addHTML('</div>')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML('<span title="'.$previousTotal.'">' . $previousTotal.'</span>')
                    ->addHTML('</div>')
                    ->addHTML('<div class="flex_col">')
                    ->addHTML('<span title="'.$currentTotal.'">' . $currentTotal .'</span>')
                    ->addHTML('</div>')
                    ->addHTML('</div>');
        
        }
    }

    protected function buildTableRow($label, $previousTotal, $currentTotal) {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">')
                ->addHTML($label)
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('$' . GI_StringUtils::formatMoney($previousTotal))
                ->addHTML('</div>')
                ->addHTML('<div class="flex_col">')
                ->addHTML('$' . GI_StringUtils::formatMoney($currentTotal))
                ->addHTML('</div>')
                ->addHTML('</div>');
    }
        
}