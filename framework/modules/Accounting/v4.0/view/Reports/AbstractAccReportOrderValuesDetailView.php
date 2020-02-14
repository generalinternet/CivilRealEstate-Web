<?php
/**
 * Description of AbstractAccReportOrderValuesDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportOrderValuesDetailView extends AbstractAccReportDetailView {
    
    protected $totals = NULL;
    protected $colours = array('939393','605f5d');
    
    public function __construct(\AbstractAccReport $accReport, $totals) {
        parent::__construct($accReport);
        $this->totals = $totals;
    }

    protected function buildViewBody() {
        $this->addHTML('<h3>As of ' . $this->accReport->getEndDate()->format('M jS, Y') . '</h3>');
        $this->addHTML('<hr />');
        $this->addHTML('<div class="flex_row">');
        $this->addHTML('<div class="flex_col">');
        $this->buildPOTable();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->buildSOTable();
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addGraphJS();
    }

    protected function buildPOTable() {
        $this->addHTML('<h3>Purchase Orders</h3>');
        if (!empty($this->totals) && isset($this->totals['po']) && !empty($this->totals['po'])) {
            $this->addHTML('<div class="graph_wrap" id="order_values_purchase_graph"></div>');
            $totals = $this->totals['po'];
            $this->addHTML('<div class="flex_table ui_table">');
            $this->buildPOTableHeader();
            $this->buildPOTableRowPair('Daily', $totals['cad']['inp']['daily'], $totals['usd']['inp']['daily'], $totals['cad']['rec']['daily'], $totals['usd']['rec']['daily']);
            $this->buildPOTableRowPair('Week', $totals['cad']['inp']['week'], $totals['usd']['inp']['week'], $totals['cad']['rec']['week'], $totals['usd']['rec']['week']);
            $this->buildPOTableRowPair('Month', $totals['cad']['inp']['month'], $totals['usd']['inp']['month'], $totals['cad']['rec']['month'], $totals['usd']['rec']['month']);
            $this->buildPOTableRowPair('YTD', $totals['cad']['inp']['ytd'], $totals['usd']['inp']['ytd'], $totals['cad']['rec']['ytd'], $totals['usd']['rec']['ytd']);
            $this->addHTML('</div>');
        } else {
            $this->addHTML('<p>Error calculating Purchase Order data. If this problem perists, please contact your friendly General Internet team.</p>');
        }
    }

    protected function buildPOTableHeader() {
        $this->addHTML('<div class="flex_row flex_head">');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('Status')
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('CAD')
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('USD')
                ->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function buildPOTableRowPair($label, $cadInProgressValue, $usdInProgressValue, $cadReceivedValue, $usdReceivedValue) {
        $this->addHTML('<div class="flex_row">');
        $this->buildTableRow($label, 'In Progress', $cadInProgressValue, $usdInProgressValue);
        $this->addHTML('</div>');
        $this->addHTML('<div class="flex_row">');
        $this->buildTableRow($label, 'Received', $cadReceivedValue, $usdReceivedValue);
        $this->addHTML('</div>');
    }

    protected function buildSOTableRowPair($label, $cadInProgressValue, $usdInProgressValue, $cadShippedValue, $usdShippedValue) {
        $this->addHTML('<div class="flex_row">');
        $this->buildTableRow($label, 'In Progress', $cadInProgressValue, $usdInProgressValue);
        $this->addHTML('</div>');
        $this->addHTML('<div class="flex_row">');
        $this->buildTableRow($label, 'Shipped', $cadShippedValue, $usdShippedValue);
        $this->addHTML('</div>');
    }

    protected function buildTableRow($label, $status, $cadValue, $usdValue) {
        $this->addHTML('<div class="flex_col">')
                ->addHTML($label)
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML($status)
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('$'.GI_StringUtils::formatMoney($cadValue))
                ->addHTML('</div>');
        $this->addHTML('<div class="flex_col">')
                ->addHTML('$'.GI_StringUtils::formatMoney($usdValue))
                ->addHTML('</div>');
    }

    protected function buildSOTable() {
        $this->addHTML('<h3>Sales Orders</h3>');
        if (!empty($this->totals) && isset($this->totals['so']) && !empty($this->totals['so'])) {
            $this->addHTML('<div class="graph_wrap" id="order_values_sales_graph"></div>');
            $totals = $this->totals['so'];
            $this->addHTML('<div class="flex_table ui_table">');
            $this->buildPOTableHeader();
            $this->buildSOTableRowPair('Daily', $totals['cad']['inp']['daily'], $totals['usd']['inp']['daily'], $totals['cad']['shp']['daily'], $totals['usd']['shp']['daily']);
            $this->buildSOTableRowPair('Week', $totals['cad']['inp']['week'], $totals['usd']['inp']['week'], $totals['cad']['shp']['week'], $totals['usd']['shp']['week']);
            $this->buildSOTableRowPair('Month', $totals['cad']['inp']['month'], $totals['usd']['inp']['month'], $totals['cad']['shp']['month'], $totals['usd']['shp']['month']);
            $this->buildSOTableRowPair('YTD', $totals['cad']['inp']['ytd'], $totals['usd']['inp']['ytd'], $totals['cad']['shp']['ytd'], $totals['usd']['shp']['ytd']);
            $this->addHTML('</div>');
        } else {
            $this->addHTML('<p>Error calculating Sales Order data. If this problem perists, please contact your friendly General Internet team.</p>');
        }
    }

    protected function addGraphJS() {
        $poTotals = $this->totals['po'];
        if (empty($poTotals)) {
            return;
        }
        $colours = $this->colours;
        $colourString = "[";
        $colourCount = count($colours);
        for ($i=0;$i<$colourCount;$i++) {
            $colourString .= " '#" . $colours[$i] . "'";
            if ($i < $colourCount - 1) {
                $colourString.= ',';
            }
        }
        $colourString .= "]";
        
        $poGraphJSString = "new Morris.Bar({
                    element: 'order_values_purchase_graph',
                    data: [
                        {x: 'Daily (In Progress)', y0: ".$poTotals['cad']['inp']['daily'].", y1: ".$poTotals['usd']['inp']['daily']."},
                        {x: 'Daily (Received)', y0: ".$poTotals['cad']['rec']['daily'].", y1: ".$poTotals['usd']['rec']['daily']."},
                        {x: 'Week (In Progress)', y0: ".$poTotals['cad']['inp']['week'].", y1: ".$poTotals['usd']['inp']['week']."},
                        {x: 'Week (Received)', y0: ".$poTotals['cad']['rec']['week'].", y1: ".$poTotals['usd']['rec']['week']."},
                        {x: 'Month (In Progress)', y0: ".$poTotals['cad']['inp']['month'].", y1: ".$poTotals['usd']['inp']['month']."},
                        {x: 'Month (Received)', y0: ".$poTotals['cad']['rec']['month'].", y1: ".$poTotals['usd']['rec']['month']."},
                        {x: 'YTD (In Progress)', y0: ".$poTotals['cad']['inp']['ytd'].", y1: ".$poTotals['usd']['inp']['ytd']."},
                        {x: 'YTD (Received)', y0: ".$poTotals['cad']['rec']['ytd'].", y1: ".$poTotals['usd']['rec']['ytd']."}
                    ],
                    xkey: 'x',
                    ykeys: ['y0','y1'],
                    labels: ['CAD', 'USD'],
                    stacked: false,
                    xLabelAngle: 60,
                    preUnits: '$',
                    barColors: ".$colourString.",
                    });";

        $this->addDynamicJS($poGraphJSString);
        
        $soTotals = $this->totals['so'];
        
                $soGraphJSString = "new Morris.Bar({
                    element: 'order_values_sales_graph',
                    data: [
                        {x: 'Daily (In Progress)', y0: ".$soTotals['cad']['inp']['daily'].", y1: ".$soTotals['usd']['inp']['daily']."},
                        {x: 'Daily (Shipped)', y0: ".$soTotals['cad']['shp']['daily'].", y1: ".$soTotals['usd']['shp']['daily']."},
                        {x: 'Week (In Progress)', y0: ".$soTotals['cad']['inp']['week'].", y1: ".$soTotals['usd']['inp']['week']."},
                        {x: 'Week (Shipped)', y0: ".$soTotals['cad']['shp']['week'].", y1: ".$soTotals['usd']['shp']['week']."},
                        {x: 'Month (In Progress)', y0: ".$soTotals['cad']['inp']['month'].", y1: ".$soTotals['usd']['inp']['month']."},
                        {x: 'Month (Shipped)', y0: ".$soTotals['cad']['shp']['month'].", y1: ".$soTotals['usd']['shp']['month']."},
                        {x: 'YTD (In Progress)', y0: ".$soTotals['cad']['inp']['ytd'].", y1: ".$soTotals['usd']['inp']['ytd']."},
                        {x: 'YTD (Shipped)', y0: ".$soTotals['cad']['shp']['ytd'].", y1: ".$soTotals['usd']['shp']['ytd']."}
                    ],
                    xkey: 'x',
                    ykeys: ['y0','y1'],
                    labels: ['CAD', 'USD'],
                    stacked: false,
                    xLabelAngle: 60,
                    preUnits: '$',
                    barColors: ".$colourString.",
                    });";

        $this->addDynamicJS($soGraphJSString);
           
    }

}
