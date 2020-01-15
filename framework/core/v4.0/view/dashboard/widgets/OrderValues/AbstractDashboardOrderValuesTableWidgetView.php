<?php
/**
 * Description of AbstractDashboardOrderValuesTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardOrderValuesTableWidgetView extends AbstractDashboardWidgetView {
    
    protected $report = NULL;
    protected $totals = NULL;

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setHeaderIcon('dollars');
        $dates = GI_Time::getFiscalYearStartAndEndDates();
        $this->report = AccReportFactory::buildReportObject('order_values', $dates['start'], $dates['end'], true);
    }
}
