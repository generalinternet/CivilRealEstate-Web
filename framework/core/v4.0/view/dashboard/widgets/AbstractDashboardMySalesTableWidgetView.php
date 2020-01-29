<?php
/**
 * Description of AbstractDashboardMySalesTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractDashboardMySalesTableWidgetView extends AbstractDashboardWidgetView {
    
    protected $report;
    protected $currencies = array();

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setHeaderIcon('dollars');
        $this->setTitle('My Sales');
        $dates = GI_Time::getFiscalYearStartAndEndDates();
        $this->report = AccReportFactory::buildReportObject('sales_by_salesperson', $dates['start'], new DateTime(date('Y-m-d')), true);
    }

    protected function determineIsViewable() {
        if (Permission::verifyByRef('view_my_sales_dashboard_widget')) {
            return true;
        }
        return false;
    }

    public function buildBodyContent() {
        if (empty($this->report)) {
            $this->addHTML('<p>Data Unavailable</p>');
            return;
        }
        $user = Login::getUser();
        if (empty($user)) {
            $this->addHTML('<p>No sales data is available for the current user.</p>');
            return;
        }
        $totals = array();
        if (!empty($this->report)) {
            $this->report->buildReport();
            $totals = $this->report->getTotals();
            $currency = $this->report->getCurrency();
            $this->currencies[] = $currency;
            $secondaryCurrency = $this->report->getSecondaryCurrency();
            if (!empty($secondaryCurrency) && $secondaryCurrency->getId() !== $currency->getId()) {
                $this->currencies[] = $secondaryCurrency;
            }
        }
        if (empty($totals) || !isset($totals[$user->getId()])) {
            $this->addHTML('<p>No sales data is available for the current user.</p>');
            return;
        }
        $userTotals = $totals[$user->getId()];
        $this->addHTML('<h5>Total Sales from Shipped Items</h5>');
        $this->buildTable($userTotals);
    }

    protected function buildTable($userTotals) {
        $this->addHTML('<div class="ui_table_wrap">');
        $this->addHTML('<table class="ui_table">');
        $this->buildTableHeader();
        $this->buildTableBody($userTotals);
        $this->addHTML('</table>');
        $this->addHTML('</div>');
    }

    protected function buildTableHeader() {
        $this->addHTML('<thead>')
                ->addHTML('<tr>')
                ->addHTML('<th></th>');
        if (!empty($this->currencies)) {
            foreach ($this->currencies as $currency) {
                $this->addHTML('<th class="med_col">' . $currency->getProperty('name') . '</th>');
            }
        }
        $this->addHTML('</tr>')
                ->addHTML('</thead>');
    }

    protected function buildTableBody($userTotals) {
         $currencyRefs = array();
         if (!empty($this->currencies)) {
             foreach ($this->currencies as $currency) {
                 $currencyRefs[] = $currency->getProperty('ref');
             }
         }
         if (empty($userTotals) || empty($currencyRefs)) {
             return;
         }
         $keys = array(
             'daily'=>'Daily',
             'weekly'=>'Week',
             'monthly'=>'Month',
             'ytd'=>'YTD',
         );
         $this->addHTML('<tbody>');
         foreach ($keys as $key => $title) {
             $keyTotals = $userTotals[$key];
             $this->addHTML('<tr>');
             $this->addHTML('<td>' . $title . '</td>');
             foreach ($currencyRefs as $currencyRef) {
                 $formattedValue = '$' . GI_StringUtils::formatMoney($keyTotals[$currencyRef]);
                 $this->addHTML('<td title="'.$formattedValue.'">' . $formattedValue . '</td>');
             }
             $this->addHTML('</tr>');
         }
         $this->addHTML('</tbody>');
    }

}
