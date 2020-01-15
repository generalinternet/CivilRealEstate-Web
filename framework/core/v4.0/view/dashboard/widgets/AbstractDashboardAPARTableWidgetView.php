<?php
/**
 * Description of AbstractDashboardAPARTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardAPARTableWidgetView extends AbstractDashboardWidgetView {

    /** @var AbstractAccReportQBApAgingSummary */
    protected $apReport;
    
    /** @var AbstractAccReportQBArAgingSummary */
    protected $arReport;
    
    public function __construct($ref) {
        parent::__construct($ref);
        $this->setHeaderIcon('dollars');
        $this->setTitle('AP/AR Summary');
        $dates = GI_Time::getFiscalYearStartAndEndDates();
        $this->apReport = AccReportFactory::buildReportObject('ap_aging_summary', $dates['start'], new DateTime(date('Y-m-d')), true);
        $this->arReport = AccReportFactory::buildReportObject('ar_aging_summary', $dates['start'], new DateTime(date('Y-m-d')), true);
    }

    protected function determineIsViewable() {
        if (Permission::verifyByRef('view_ap_ar_dashboard_widget')) {
            return true;
        }
        return false;
    }

    public function buildBodyContent() {
        $this->addHTML('<h4 class="chart_title">As of ' . GI_Time::formatDateForDisplay(GI_Time::getDate()) . '</h4>');
        $this->buildTable();
        $currencyTitle = $this->arReport->getCurrencyTitle();
        if (!empty($currencyTitle)) {
            $this->addHTML('<h6>All values in ' . $currencyTitle . '</h6>');
        }
    }

    protected function buildTable() {
      $this->addHTML('<table class="ui_table">');
      $this->buildTableHeader();
      $this->buildTableBody();
      $this->buildTableFooter();
      $this->addHTML('</table>');
    }
    
    protected function buildTableHeader() {
        $this->addHTML('<thead>');
        $this->addHTML('<tr>')
                ->addHTML('<th></th>')
                ->addHTML('<th>AP</th>')
                ->addHTML('<th>AR</th>')
                ->addHTML('</tr>');
        $this->addHTML('</thead>');
    }

    protected function buildTableBody() {
        $this->addHTML('<tbody>');
        $this->buildTableRow('Current', $this->apReport->getCurrentTotal(true), $this->arReport->getCurrentTotal(true));
        $this->buildTableRow('1 - 30', $this->apReport->getOneToThirtyTotal(true), $this->arReport->getOneToThirtyTotal(true));
        $this->buildTableRow('31 - 60', $this->apReport->getThirtyOneToSixtyTotal(true), $this->arReport->getThirtyOneToSixtyTotal(true));
        $this->buildTableRow('61 - 90', $this->apReport->getSixtyOneToNinetyTotal(true), $this->arReport->getSixtyOneToNinetyTotal(true));
        $this->buildTableRow('90 and over', $this->apReport->getNinetyOneAndOverTotal(true), $this->arReport->getNinetyOneAndOverTotal(true));
        $this->addHTML('</tbody>');
    }

    protected function buildTableRow($label, $apVal, $arVal) {
        $this->addHTML('<tr>')
                ->addHTML('<td title="'.$label.'">' . $label . '</td>')
                ->addHTML('<td title="'.$apVal.'">' . $apVal . '</td>')
                ->addHTML('<td title="'.$arVal.'">' . $arVal . '</td>')
                ->addHTML('</tr>');
    }
    
    protected function buildTableFooter() {
        $apVal = $this->apReport->getTotal(true);
        $arVal = $this->arReport->getTotal(true);
        $this->addHTML('<tfoot>');
                $this->addHTML('<tr class="total_row">')
                ->addHTML('<th title="total">Total</th>')
                ->addHTML('<th title="'.$apVal.'">' . $apVal . '</th>')
                ->addHTML('<th title="'.$arVal.'">' . $arVal . '</th>')
                ->addHTML('</tr>');
        $this->addHTML('</tfoot>');
    }

}
