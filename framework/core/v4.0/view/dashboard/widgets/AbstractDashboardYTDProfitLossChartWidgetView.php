<?php

/**
 * Description of AbstractDashboardYTDProfitLossChartWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractDashboardYTDProfitLossChartWidgetView extends AbstractDashboardChartWidgetView {

    /** @var AbstractAccReportQBProfitAndLoss */
    protected $profitAndLossAccReport = NULL;
    protected $startDateTime = NULL;
    protected $endDateTime = NULL;

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('YTD Profit and Loss');
        $this->setHeaderIcon('calculator');

        $fiscalYearDates = GI_Time::getFiscalYearStartAndEndDates();
        $startDateTime = $fiscalYearDates['start'];
      //  $endDateTime = $fiscalYearDates['end'];
        $endDateTime = new DateTime(date('Y-m-d H:i:s'));
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }

    public function buildBodyContent() {
        $startDateTime = $this->startDateTime;
        $endDateTime = $this->endDateTime;
        if (empty(QBConnection::getInstance())) {
            $this->addHTML('<p>Data unavailable without connection to Quickbooks Online.</p>');
            return;
        }
        $profitAndLossAccReport = AccReportFactory::buildReportObject('profit_and_loss', $startDateTime, $endDateTime, true);
        if (empty($profitAndLossAccReport)) {
            $this->addHTML('<p>Data unavailable - access denied.</p>');
            return;
        }
        $this->profitAndLossAccReport = $profitAndLossAccReport;
        if (empty($this->profitAndLossAccReport)) {
            $this->addHTML('<p>Data Unavailable</p>');
            return;
        }
        $this->addDateRange();
        $this->addHTML('<div class="graph_wrap" id="ytd_profit_loss_widget_graph"></div>');
        $this->addGraphJS();
    }

    protected function addDateRange() {
        if (!empty($this->startDateTime) && !empty($this->endDateTime)) {
            $start = $this->startDateTime->format('M j, Y');
            $end = $this->endDateTime->format('M j, Y');
            $this->addHTML('<h4 class="chart_title">' . $start . ' - ' . $end . '</h4>');
        }
    }

    protected function addGraphJS() {
        $totalIncome = (float) $this->profitAndLossAccReport->getTotalIncome();
        $totalSales = (float) $this->profitAndLossAccReport->getTotalSales();
        $totalCOGS = (float) $this->profitAndLossAccReport->getTotalCOGS();
        $totalExpenses = (float) $this->profitAndLossAccReport->getTotalExpenses() + (float) $this->profitAndLossAccReport->getTotalOtherExpenses();
        $profit = (float) $this->profitAndLossAccReport->getTotalProfit();

        $atLeastOneNonZeroValue = false;
        if (!empty($totalIncome) || !empty($totalSales) || !empty($totalCOGS) || !empty($totalExpenses) || !empty($profit)) {
            $atLeastOneNonZeroValue = true;
        }

        if (!$atLeastOneNonZeroValue) {
            $this->addHTML('<p>Insufficient Data to Display Graph</p>');
        }

        $colours = $this->getColours();
        $colourString = "[";
        $colourCount = count($colours);
        for ($i=0;$i<$colourCount;$i++) {
            $colourString .= " '#" . $colours[$i] . "'";
            if ($i < $colourCount - 1) {
                $colourString.= ',';
            }
        }
        $colourString .= "]";

        $nonSalesIncome = $totalIncome - $totalSales;
        $graphJSString = "new Morris.Bar({
                    element: 'ytd_profit_loss_widget_graph',
                    data: [
                        {x: 'Income', y0: ".$totalSales.", y1: ".$nonSalesIncome.", y2: null, y3: null, y4: null},
                        {x: 'Expenses', y0: null, y1: null, y2: " . $totalCOGS . ", y3: ".$totalExpenses.", y4: null},
                        {x: 'Profit', y0: null, y1: null, y2: null, y3: null, y4: " . $profit . "}
                    ],
                    xkey: 'x',
                    ykeys: ['y0','y1','y2','y3','y4'],
                    labels: ['Sales', 'Other Income', 'COGS', 'Other Expenses', 'Profit'],
                    hoverCallback: function (index, options, content, row) {
                        var finalContent = $(content);
                        var cpt = 0;
                        $.each(row, function (n, v) {
                            if (n == 'x') {
                                if (v == 'Income') {
                                    $(finalContent).eq(cpt).html('Total Income: $" . $this->profitAndLossAccReport->getTotalIncome(true) . "');
                                } else if (v == 'Expenses') {
                                 $(finalContent).eq(cpt).html('Total Expenses: $" . GI_StringUtils::formatMoney($totalCOGS + $totalExpenses) . "');
                                } else if (v == 'Profit') {
                                $(finalContent).eq(cpt).html('Total Profit: $" . $this->profitAndLossAccReport->getTotalProfit(true) . "');
                                }
                            }
                            if (v == null) {
                                $(finalContent).eq(cpt).empty();
                            }
                            cpt++;
                        });

                        return finalContent;
                    },
                    stacked: true,
                    xLabelAngle: 60,
                    preUnits: '$',
                    barColors: ".$colourString.",
                    });";

        $this->addDynamicJS($graphJSString);
    }

    protected function determineIsViewable() {
        if (!ProjectConfig::getIsQuickbooksIntegrated() || !Permission::verifyByRef('view_ytd_profit_loss_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

}
