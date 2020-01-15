<?php
/**
 * Description of AbstractDashboardSalesOrderValuesTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardSalesOrderValuesTableWidgetView extends AbstractDashboardOrderValuesTableWidgetView {

    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Sales Order Values');
    }

    public function buildBodyContent() {
        if (!empty($this->report)) {
            $this->report->buildReport();
            $this->totals = $this->report->getTotals()['so'];
            $this->buildTable();
        }
    }

    protected function buildTable() {
        $this->addHTML('<div class="ui_table_wrap">');
        $this->addHTML('<table class="ui_table">');
        $this->buildTableHeader();
        $this->buildTableBody();
        $this->addHTML('</table>');
        $this->addHTML('</div>');       
    }

    protected function buildTableHeader() {
        $this->addHTML('<thead>')
                ->addHTML('<tr>')
                ->addHTML('<th></th>')
                ->addHTML('<th class="sml_col"></th>')
                ->addHTML('<th>CAD</th>')
                ->addHTML('<th>USD</th>')
                ->addHTML('</tr>')
                ->addHTML('</thead>');
    }

    protected function buildTableBody() {
        $totals = $this->totals;
        if (!empty($totals)) {
            $this->buildSOTableRowPair('Daily', $totals['cad']['inp']['daily'], $totals['usd']['inp']['daily'], $totals['cad']['shp']['daily'], $totals['usd']['shp']['daily']);
            $this->buildSOTableRowPair('Week', $totals['cad']['inp']['week'], $totals['usd']['inp']['week'], $totals['cad']['shp']['week'], $totals['usd']['shp']['week']);
            $this->buildSOTableRowPair('Month', $totals['cad']['inp']['month'], $totals['usd']['inp']['month'], $totals['cad']['shp']['month'], $totals['usd']['shp']['month']);
            $this->buildSOTableRowPair('YTD', $totals['cad']['inp']['ytd'], $totals['usd']['inp']['ytd'], $totals['cad']['shp']['ytd'], $totals['usd']['shp']['ytd']);
        }
    }

    protected function buildSOTableRowPair($label, $cadInProgressValue, $usdInProgressValue, $cadShippedValue, $usdShippedValue) {
        $this->addHTML('<tr>');
        $this->buildTableRow($label, 'In Progress', $cadInProgressValue, $usdInProgressValue);
        $this->addHTML('</tr><tr>');
        $this->buildTableRow($label, 'Shipped', $cadShippedValue, $usdShippedValue);
        $this->addHTML('</tr>');
    }

    protected function buildTableRow($label, $status, $cadValue, $usdValue) {
        if ($status == 'In Progress') {
            $status = '<span class="icon gray time" title="In Progress"></span>';
        } else {
            $status = '<span class="icon gray check" title="Shipped"></span>';
        }
        $formattedCADValue = GI_StringUtils::formatMoney($cadValue);
        $formattedUSDValue = GI_StringUtils::formatMoney($usdValue);
        $this->addHTML('<td>')
                ->addHTML($label)
                ->addHTML('</td>')
                ->addHTML('<td class="sml_col">')
                ->addHTML($status)
                ->addHTML('</td>')
                ->addHTML('<td title="$' . $formattedCADValue . '">')
                ->addHTML('$' . $formattedCADValue)
                ->addHTML('</td>')
                ->addHTML('<td title="$' . $formattedUSDValue . '">')
                ->addHTML('$' . $formattedUSDValue)
                ->addHTML('</td>');
    }

    protected function determineIsViewable() {
        if (!Permission::verifyByRef('view_sales_order_values_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }
}
