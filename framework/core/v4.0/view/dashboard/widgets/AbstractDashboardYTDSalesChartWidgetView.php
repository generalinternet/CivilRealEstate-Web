<?php

/**
 * Description of AbstractDashboardYTDSalesChartWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardYTDSalesChartWidgetView extends AbstractDashboardChartWidgetView {

    /** @var AbstractAccReportQBProfitAndLoss[] */
    protected $profitAndLossAccReports = NULL;
    protected $startDateTime = NULL;
    protected $endDateTime = NULL;
    
    protected $pointFillColours = array('ffffff');
    protected $lineWidth = 3;
    protected $pointSize = 6;

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('YTD Sales');
        $this->setHeaderIcon('calculator');
    }
    
    public function setPointFillColours($pointFillColours) {
        $this->pointFillColours = $pointFillColours;
    }
    
    public function setLineWidth($lineWidth) {
        $this->lineWidth = $lineWidth = $lineWidth;
    }
    
    public function setPointSize($pointSize) {
        $this->pointSize = $pointSize;
    }

    public function buildBodyContent() {
        if (empty(QBConnection::getInstance())) {
            $this->addHTML('<p>Data unavailable without connection to Quickbooks Online.</p>');
            return;
        } 
        $fiscalYearDates = GI_Time::getFiscalYearStartAndEndDates();
        $startDateTime = $fiscalYearDates['start'];
        $fiscalYearEndDateTime = $fiscalYearDates['end'];
        $this->startDateTime = $startDateTime;
        $endDateTime = new DateTime(date('Y-m-d 00:00:00'));
        //$this->endDateTime = $fiscalYearDateTime;
        $this->endDateTime = $endDateTime;
        $monthKeys = array(
            '01',
            '02',
            '03',
            '04',
            '05',
            '06',
            '07',
            '08',
            '09',
            '10',
            '11',
            '12'
        );
        $fiscalYearStartMonth = $startDateTime->format('m') . "";

        $reports = array();
        $startKey = array_search($fiscalYearStartMonth, $monthKeys);
        if ($startKey === false) {
            $this->profitAndLossAccReports = $reports;
            return;
        }
        for ($i = $startKey; $i < 12; $i++) {
            $dates = GI_Time::getDatesByFiscalYearAndReportingPeriod($startDateTime, $fiscalYearEndDateTime, $monthKeys[$i]);
            if (!empty($dates)) {
                $reportStart = $dates['start'];
                $reportEnd = $dates['end'];
                $reports[$i + 1] = AccReportFactory::buildReportObject('profit_and_loss', new DateTime($reportStart), new DateTime($reportEnd), true);
            }
        }
        if ($startKey > 0) {
            for ($j = 0; $j < $startKey; $j++) {
                $dates = GI_Time::getDatesByFiscalYearAndReportingPeriod($startDateTime, $fiscalYearEndDateTime, $monthKeys[$j]);
                if (!empty($dates)) {
                    $reportStart = $dates['start'];
                    $reportEnd = $dates['end'];
                    $reports[$j + 1] = AccReportFactory::buildReportObject('profit_and_loss', new DateTime($reportStart), new DateTime($reportEnd), true);
                }
            }
        }
        $this->profitAndLossAccReports = $reports;

        if (empty($this->profitAndLossAccReports)) {
            $this->addHTML('<p>No Data Available.</p>');
            return;
        }
        $todayDateTime = new DateTime(date('Y-m-d'));
        $salesData = array();
        foreach ($this->profitAndLossAccReports as $report) {
            $totalSales = $report->getTotalSales();
            $reportStartDateTime = $report->getStartDate();
            if (empty($reportStartDateTime)) {
                continue;
            }
            if (empty($totalSales)) {
                if ($reportStartDateTime < $todayDateTime) {
                    $totalSales = 0;
                } else {
                    $totalSales = 'null';
                }
            }
                $key = $reportStartDateTime->format('Y-m');
                $salesData[$key] = $totalSales;
        }
        $this->addDateRange();
        $this->addHTML('<div class="graph_wrap" id="ytd_sales_widget_graph"></div>');
        $this->addGraphJS($salesData);
        
    }

    protected function addGraphJS($salesData) {
        if (empty($salesData)) {
            $this->addHTML('<p>Insufficient Data to Display Graph</p>');
        }

        $colours = $this->getColours();
        $colourString = "[";
        $colourCount = count($colours);
        for ($i = 0; $i < $colourCount; $i++) {
            $colourString .= "'#" . $colours[$i] . "'";
            if ($i < $colourCount - 1) {
                $colourString .= ",";
            }
        }
        $colourString .= "]";
        
        $pointFillColours = $this->pointFillColours;
        $pointFillColourString = "[";
        $pointFillColourCount = count($pointFillColours);
        for ($j=0; $j < $pointFillColourCount; $j++) {
            $pointFillColourString .= "'#" . $pointFillColours[$j] . "'";
            if ($j < $pointFillColourCount - 1) {
                $pointFillColourString .= ",";
            }
        }
        $pointFillColourString .= "]";
        
        $graphJSString = "new Morris.Line({
                    element: 'ytd_sales_widget_graph',
                    data: [";
        foreach ($salesData as $x=>$y) {
            $graphJSString .= "{ x: '".$x."', y: ".$y."},";
        }
        $graphJSString .= "],
                    xkey: 'x',
                    ykeys: ['y'],
                    labels: ['Sales'],
                    xLabelAngle: 90,
                    xLabelMargin: 0,
                    lineColors: ".$colourString.",
                    lineWidth: ".$this->lineWidth.",
                    pointSize: ".$this->pointSize.",
                    pointFillColors: ".$pointFillColourString.",
                    pointStrokeColors: ".$colourString.",
                    xLabelFormat: function(x) {
                        return $.datepicker.formatDate('M', new Date(x));
                    },
                    hoverCallback: function (index, options, content, row) {
                        var finalContent = $(content);
                        var cpt = 0;
                        $.each(row, function (n, v) {
                            if (n == 'x') {
                                var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                var date = new Date(v);
                                var mKey = date.getMonth() + 1;
                                if (mKey == 12) {
                                    mKey = 0;
                                }
                                $(finalContent).eq(cpt).html(months[mKey]);
                            } else {
                                if (v == null) {
                                    $(finalContent).eq(cpt).empty();
                                }
                            }
                            cpt++;
                        });
                        return finalContent;
                    },
                    preUnits: '$',
                    });";

        $this->addDynamicJS($graphJSString);
    }

    protected function addDateRange() {
        if (!empty($this->startDateTime) && !empty($this->endDateTime)) {
            $start = $this->startDateTime->format('M j, Y');
            $end = $this->endDateTime->format('M j, Y');
            $this->addHTML('<h4 class="chart_title">' . $start . ' - ' . $end . '</h4>');
        }
    }

    protected function determineIsViewable() {
        if (!ProjectConfig::getIsQuickbooksIntegrated() || !Permission::verifyByRef('view_ytd_sales_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }
}