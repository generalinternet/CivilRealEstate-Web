<?php

class AdminReportsView extends GI_View {

    protected $graphData;
    protected $graphJSAdded = false;

    public function __construct($graphData) {
        parent::__construct();
        $this->graphData = $graphData;
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->buildView();
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML('<div class="columns halves">');
        $this->addHTML('<div class="column">');
        $this->addHTML('<h2>Total Cost of Goods Sold (USD)</h2>');
        $this->addContentBlock('$' . GI_StringUtils::formatMoney($this->graphData['usd_cogs_total'], true) . ' USD');
        $this->addHTML('</div>');
        $this->addHTML('<div class="column">');
        $this->addHTML('<h2>Total Sales Income (USD)</h2>');
        $this->addContentBlock('$' . GI_StringUtils::formatMoney($this->graphData['usd_sales_total'], true) . ' USD');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->buildGraphsAndTables('usd');
        $this->addHTML('<br><br><br>');
        $this->addHTML('<div class="columns halves">');
        $this->addHTML('<div class="column">');
        $this->addHTML('<h2>Total Cost of Goods Sold (CAD)</h2>');
        $this->addContentBlock('$' . GI_StringUtils::formatMoney($this->graphData['cad_cogs_total'], true) . ' CAD');
        $this->addHTML('</div>');
        $this->addHTML('<div class="column">');
        $this->addHTML('<h2>Total Sales Income (CAD)</h2>');
        $this->addContentBlock('$' . GI_StringUtils::formatMoney($this->graphData['cad_sales_total'], true) . ' CAD');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->buildGraphsAndTables('cad');
        $this->closeViewWrap();
    }

    protected function addGraphJS() {
        if (!$this->graphJSAdded) {
            $this->addFinalContent('<script type="text/javascript">
                new Morris.Donut({
                    element: "usd_cogs_graph",
                    data: [
                        {value: ' . $this->graphData['usd_cogs_acid_etched'] . ', label: "COGS - Acid Etched Glass" },
                        {value: ' . $this->graphData['usd_cogs_float'] . ', label: "COGS - Float Glass 3mm - 6mm" },
                        {value: ' . $this->graphData['usd_cogs_inventory'] . ', label: "COGS - Inventory" },
                        {value: ' . $this->graphData['usd_cogs_mirror'] . ', label: "COGS - Mirror" },
                        {value: ' . $this->graphData['usd_cogs_patterned'] . ', label: "COGS - Patterned Glass" },
                        {value: ' . $this->graphData['usd_cogs_sungate_solarban'] . ', label: "COGS - Sungate Solarban" },
                        {value: ' . $this->graphData['usd_cogs_windows_hardware'] . ', label: "COGS - Windows Hardware" },
                        {value: ' . $this->graphData['usd_cogs_wire'] . ', label: "COGS - Wired Glass" },
                        {value: ' . $this->graphData['usd_cogs_laminated'] . ', label: "COGS - Laminated Glass" },
                        {value: ' . $this->graphData['usd_cogs_cost_of_goods_sold_-_other'] . ', label: "Cost of Goods Sold - Other" }
                    ],
                    colors: ["#3f95ff","#347ad1","#4c8ee0"],
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });
                </script>');
            $this->addFinalContent('<script type="text/javascript">
                new Morris.Donut({
                    element: "cad_cogs_graph",
                    data: [
                        {value: ' . $this->graphData['cad_cogs_acid_etched'] . ', label: "COGS - Acid Etched Glass" },
                        {value: ' . $this->graphData['cad_cogs_float'] . ', label: "COGS - Float Glass 3mm - 6mm" },
                        {value: ' . $this->graphData['cad_cogs_inventory'] . ', label: "COGS - Inventory" },
                        {value: ' . $this->graphData['cad_cogs_mirror'] . ', label: "COGS - Mirror" },
                        {value: ' . $this->graphData['cad_cogs_patterned'] . ', label: "COGS - Patterned Glass" },
                        {value: ' . $this->graphData['cad_cogs_sungate_solarban'] . ', label: "COGS - Sungate Solarban" },
                        {value: ' . $this->graphData['cad_cogs_windows_hardware'] . ', label: "COGS - Windows Hardware" },
                        {value: ' . $this->graphData['cad_cogs_wire'] . ', label: "COGS - Wired Glass" },
                        {value: ' . $this->graphData['cad_cogs_laminated'] . ', label: "COGS - Laminated Glass" },
                        {value: ' . $this->graphData['cad_cogs_cost_of_goods_sold_-_other'] . ', label: "Cost of Goods Sold - Other" }
                    ],
                    colors: ["#3f95ff","#347ad1","#4c8ee0"],
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });
                </script>');
            //{value: ' . $this->graphData['sales_refunds__return'] . ', label: "Refunds & Returns" },
            $this->addFinalContent('<script type="text/javascript">
                new Morris.Donut({
                    element: "usd_sales_graph",
                    data: [
                        {value: ' . $this->graphData['usd_sales_acid_etched'] . ', label: "Acid Etched Glass" },
                        {value: ' . $this->graphData['usd_sales_float'] . ', label: "Float Glass 3mm - 6mm" },
                        {value: ' . $this->graphData['usd_sales_hardware'] . ', label: "Hardware" },
                        {value: ' . $this->graphData['usd_sales_laminated'] . ', label: "Laminated Glass" },
                        {value: ' . $this->graphData['usd_sales_mirror'] . ', label: "Mirror" },
                        {value: ' . $this->graphData['usd_sales_patterned'] . ', label: "Patterned Glass" },
                        {value: ' . $this->graphData['usd_sales_stock_items'] . ', label: "Stock Items" },
                        {value: ' . $this->graphData['usd_sales_sungate_solarban'] . ', label: "Sungate Solarban Glass" },
                        {value: ' . $this->graphData['usd_sales_windows_hardware'] . ', label: "Windows Hardware" },
                        {value: ' . $this->graphData['usd_sales_wire'] . ', label: "Wired Glass" }
                    ],
                    colors: ["#3f95ff","#347ad1","#4c8ee0"],
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });
                </script>');
            $this->addFinalContent('<script type="text/javascript">
                new Morris.Donut({
                    element: "cad_sales_graph",
                    data: [
                        {value: ' . $this->graphData['cad_sales_acid_etched'] . ', label: "Acid Etched Glass" },
                        {value: ' . $this->graphData['cad_sales_float'] . ', label: "Float Glass 3mm - 6mm" },
                        {value: ' . $this->graphData['cad_sales_hardware'] . ', label: "Hardware" },
                        {value: ' . $this->graphData['cad_sales_laminated'] . ', label: "Laminated Glass" },
                        {value: ' . $this->graphData['cad_sales_mirror'] . ', label: "Mirror" },
                        {value: ' . $this->graphData['cad_sales_patterned'] . ', label: "Patterned Glass" },
                        {value: ' . $this->graphData['cad_sales_stock_items'] . ', label: "Stock Items" },
                        {value: ' . $this->graphData['cad_sales_sungate_solarban'] . ', label: "Sungate Solarban Glass" },
                        {value: ' . $this->graphData['cad_sales_windows_hardware'] . ', label: "Windows Hardware" },
                        {value: ' . $this->graphData['cad_sales_wire'] . ', label: "Wired Glass" }
                    ],
                    colors: ["#3f95ff","#347ad1","#4c8ee0"],
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });
                </script>');
            $this->graphJSAdded = true;
        }
    }

    protected function buildGraphsAndTables($currencyRef = 'usd') {
        $this->addGraphJS();
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        $this->addHTML('<div class="columns halves">');
        $this->addHTML('<div class="column">');
        if (!empty($this->graphData[$currencyRef.'_cogs_total'])) {
            $this->addHTML('<div class="graph_wrap" id="'.$currencyRef.'_cogs_graph"></div>');
        } else {
            $this->addHTML('<p>There is insufficient data to generate graph.</p>');
        }
        $this->buildValuesTable($currency, 'cogs');
        $this->addHTML('</div>');
        $this->addHTML('<div class="column">');
        if (!empty($this->graphData[$currencyRef.'_sales_total'])) {
            $this->addHTML('<div class="graph_wrap" id="'.$currencyRef.'_sales_graph"></div>');
        } else {
            $this->addHTML('<p>There is insufficient data to generate graph.</p>');
        }
        $this->buildValuesTable($currency, 'sales');
        $this->addHTML('</div>');
    }
    
    protected function buildValuesTable(AbstractCurrency $currency, $type = 'cogs') {
        $currencyName = $currency->getProperty('name');
        $currencyRef = $currency->getProperty('ref');
        $this->addHTML('<table class="ui_table">');
        if ($type === 'cogs') {
          $this->addHTML('<tr><td>COGS - Acid Etched Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_acid_etched']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Float Glass 3mm - 6mm</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_float']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Inventory</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_inventory']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Mirror</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_mirror']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Patterned Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_patterned']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Sungate Solarban</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_sungate_solarban']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Windows Hardware</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_windows_hardware']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Wired Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_wire']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>COGS - Laminated Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_laminated']).' '.$currencyName.'</td></tr>');
          $this->addHTML('<tr><td>Cost of Goods Sold - Other</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_cogs_cost_of_goods_sold_-_other']).' '.$currencyName.'</td></tr>');
        } else if ($type === 'sales') {
            $this->addHTML('<tr><td>Acid Etched Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_acid_etched']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Float Glass 3mm - 6mm</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_float']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Stock Items</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_stock_items']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Mirror</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_mirror']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Patterned Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_patterned']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Sungate Solarban Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_sungate_solarban']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Windows Hardware</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_windows_hardware']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Wired Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_wire']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Laminated Glass</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_laminated']).' '.$currencyName.'</td></tr>');
            $this->addHTML('<tr><td>Hardware</td><td>$'.  GI_StringUtils::formatMoney($this->graphData[$currencyRef.'_sales_hardware']).' '.$currencyName.'</td></tr>');
        }
        $this->addHTML('</table>');
    }

}
