<?php
/**
 * Description of AbstractDashboardSalesBySalespersonTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardSalesBySalespersonTableWidgetView extends AbstractDashboardWidgetView {

    protected $report;
    protected $currencies = array();
    protected $form = NULL;

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setHeaderIcon('dollars');
        $this->setTitle('Sales by Salesperson');
        $dates = GI_Time::getFiscalYearStartAndEndDates();
        $this->report = AccReportFactory::buildReportObject('sales_by_salesperson', $dates['start'], new DateTime(date('Y-m-d')), true);
    }

    protected function determineIsViewable() {
        if (Permission::verifyByRef('view_sales_by_salesperson_dashboard_widget')) {
            return true;
        }
        return false;
    }

    public function buildBodyContent() {
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
        if (empty($totals)) {
            $this->addHTML('<p>No sales data is available.</p>');
            return;
        }
        $this->buildTables();
    }
    
    protected function buildTables() {
        $options = array(
            'daily'=>'Daily',
            'weekly'=>'Week',
            'monthly'=>'Month',
            'ytd'=>'YTD'
        );
        $this->form = new GI_Form('sales_by_salesperson');
        $this->form->addHTML('<div class="right_btns">');
        $this->form->addField('sales_by_salesperson_select', 'dropdown', array(
            'showLabel'=>false,
            'options'=>$options,
            'hideNull'=>true,
            'value' => 'ytd',
            'fieldClass' => 'toggler'
        ));
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<h5>Total sales from shipped items</h5>');

        foreach ($options as $key => $title) {
            $this->form->addHTML('<div class="toggler_element form_element" data-group="sales_by_salesperson_select" data-element="' . $key . '">');
            $this->buildTable($key, $title);
            $this->form->addHTML('</div>');
        }
        $this->addHTML($this->form->getForm(''));
    }

    protected function buildTable($key, $title) {
        $totals = $this->report->getTotals();
        if (!empty($totals)) {
            $this->form->addHTML('<table class="ui_table">');
            $this->buildTableHeader();
            $this->buildTableBody($key);
            $this->form->addHTML('</table>');
        }
    }

    protected function buildTableHeader() {
        $this->form->addHTML('<thead>');
        $this->form->addHTML('<tr>');
        $this->form->addHTML('<th></th>');
        foreach ($this->currencies as $currency) {
            $this->form->addHTML('<th class="med_col">' . $currency->getProperty('name') . '</th>');
        }
        $this->form->addHTML('</tr>');
        $this->form->addHTML('</thead>');
    }

    protected function buildTableBody($key) {
        $totals = $this->report->getTotals();
        if (!empty($totals)) {
            $this->form->addHTML('<tbody>');
            foreach ($totals as $userId => $userTotals) {
                $user = UserFactory::getModelById($userId);
                $this->buildTableRow($user, $userTotals, $key);
            }
            $this->form->addHTML('</tbody>');
        }
    }

    protected function buildTableRow(AbstractUser $user, $userTotals, $key) {
        $this->form->addHTML('<tr>');
        $this->buildTableCell($user->getFullName());
        foreach ($this->currencies as $currency) {
            $formattedValue = '$' . GI_StringUtils::formatMoney($userTotals[$key][$currency->getProperty('ref')]);
            $this->buildTableCell($formattedValue, 'med_col');
        }
        
        $this->form->addHTML('</tr>');
    }
    
    protected function buildTableCell($value, $class = '') {
        if (!empty($class)) {
            $opener = '<td class="'.$class.'">';
        } else {
            $opener = '<td>';
        }
        $this->form->addHTML($opener);
        $this->form->addHTML('<span title="'.$value.'">' . $value . '</span>');
        $this->form->addHTML('</td>');
    }

}
