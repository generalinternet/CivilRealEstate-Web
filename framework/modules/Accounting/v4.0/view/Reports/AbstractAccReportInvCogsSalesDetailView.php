<?php
/**
 * Description of AbstractAccReportInvCoggsSalesDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportInvCogsSalesDetailView extends AbstractAccReportDetailView {

    protected $colours = array();

    public function __construct(\AbstractAccReport $accReport) {
        parent::__construct($accReport);
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/custom_morris.js');
    }

    public function buildViewBody() {
        $this->addGraphJS();
        $this->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column">');
        $this->buildInventorySection();
        $this->buildWasteSection();
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->buildCogsSection();
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->buildSalesSection();
        $this->buildEcoFeeSection();
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function buildInventorySection() {
        $this->addHTML('<div class="acc_report_section inv_section">');
        $values = $this->accReport->getProperty('inv');
        $this->addHTML('<h3>Inventory by Category</h3>');
        if (!empty($this->accReport->getInvTotal())) {
            $this->addHTML('<div class="graph_wrap" id="inv_cogs_sales_report_inv_graph"></div>');
        } else {
            $this->addHTML('<p>There is insufficient data to display graph.</p>');
        }
        
        $this->buildTable($values);
        $this->addContentBlock('$'.GI_StringUtils::formatMoney($this->accReport->getInvInTransitTotal()), 'Inventory In Transit Total');
        $this->addHTML('</div>');
    }
    
    protected function buildCogsSection() {
        $this->addHTML('<div class="acc_report_section cogs_section">');
        $values = $this->accReport->getProperty('cogs');
        $this->addHTML('<h3>COGS by Category</h3>');
        if (!empty($this->accReport->getCogsTotal())) {
            $this->addHTML('<div class="graph_wrap" id="inv_cogs_sales_report_cogs_graph"></div>');
        } else {
            $this->addHTML('<p>There is insufficient data to display graph.</p>');
        }
        $this->buildTable($values);
        $this->addHTML('</div>');
    }
    
    protected function buildWasteSection() {
        $this->addHTML('<div class="acc_report_section waste_section">');
        $values = $this->accReport->getProperty('wst');
        $this->addHTML('<h3>Waste by Category</h3>');
        $this->buildTable($values);
        $this->addHTML('</div>');
    }

    protected function buildSalesSection() {
        $this->addHTML('<div class="acc_report_section sales_section">');
        $values = $this->accReport->getProperty('sales');
        $this->addHTML('<h3>Sales by Category</h3>');
        if (!empty($this->accReport->getSalesTotal())) {
            $this->addHTML('<div class="graph_wrap" id="inv_cogs_sales_report_sales_graph"></div>');
        } else {
            $this->addHTML('<p>There is insufficient data to display graph.</p>');
        }
        $this->buildTable($values, 'IncomeItemFactory');
        $this->addHTML('</div>');
    }
    
    protected function buildEcoFeeSection() {
        $this->addHTML('<div class="acc_report_section ecofee_section">');
        $values = $this->accReport->getProperty('eco_fees');
        if (!empty($values)) {
            $this->addHTML('<h3>Eco Fees by Category</h3>');
            $this->buildTable($values, 'IncomeItemFactory');
        }
        $this->addHTML('</div>');
    }

    protected function buildTable($values, $factory = 'ExpenseItemFactory') {
        if (!empty($values)) {
            $this->addHTML('<div class="flex_table ui_table acc_report_table">');
            foreach ($values as $typeRef=>$value) {
                $model = $factory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $this->addHTML('<div class="flex_row acc_report_row">');
                        $this->addHTML('<div class="flex_col">');
                            $this->addHTML($model->getTypeTitle());
                        $this->addHTML('</div>')
                                ->addHTML('<div class="flex_col">');
                            $this->addHTML('$'.GI_StringUtils::formatMoney($value));      
                        $this->addHTML('</div>');
                    
                    $this->addHTML('</div>');
                }
            }

            $this->addHTML('</div>');
        }
    }
    
    protected function addGraphJS() {
        $this->addInvJS();
        $this->addCogsJS();
        $this->addSalesJS();
    }
    
    protected function getColourString() {
        if (empty($this->colours)) {
            $colourString = '"#3f95ff","#347ad1","#4c8ee0"';
        } else {
            $colourString = '';
            $colours = array();
            foreach ($this->colours as $colour) {
                $colours[] = '"#' . $colour . '"';
            }
            $colourString = implode(',', $colours);
        }
        return $colourString;
    }

    protected function addInvJS() {
        $colourString = $this->getColourString();
        $invTypesData = $this->accReport->getProperty('inv');
        $invTotal = $this->accReport->getInvTotal();
        if (!empty($invTypesData) && !empty($invTotal)) {
            $invJSString = 'new Morris.Donut({
                    element: "inv_cogs_sales_report_inv_graph",
                    data: [';
            $invArrayCount = count($invTypesData);
            foreach ($invTypesData as $invTypeRef => $invValue) {
                $invModel = ExpenseItemFactory::buildNewModel($invTypeRef);
                if (empty($invModel)) {
                    $invTypeName = '';
                } else {
                    $invTypeName = $invModel->getTypeTitle();
                }
                if (empty($invValue)) {
                    $invValue = 0;
                }
                $invJSString .= '{value: ' . $invValue . ', label: "' . $invTypeName . '" }';
                $currentIndex = array_search($invTypeRef, array_keys($invTypesData));
                if ($currentIndex != ($invArrayCount - 1)) {
                    $invJSString .= ',';
                }
            }
            $invJSString .= '],
                    colors: [' . $colourString . '], 
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });';
            $this->addDynamicJS($invJSString);
        }
    }

    protected function addCogsJS() {
        $colourString = $this->getColourString();
        $cogsTypesData = $this->accReport->getProperty('cogs');
        $cogsTotal = $this->accReport->getCogsTotal();
        if (!empty($cogsTypesData) && !empty($cogsTotal)) {
            $cogsJSString = 'new Morris.Donut({
                    element: "inv_cogs_sales_report_cogs_graph",
                    data: [';
            $cogsArrayCount = count($cogsTypesData);
            foreach ($cogsTypesData as $cogsTypeRef => $cogsValue) {
                $cogsModel = ExpenseItemFactory::buildNewModel($cogsTypeRef);

                if (empty($cogsValue)) {
                    $cogsValue = 0;
                }
                if (empty($cogsModel)) {
                    $cogsTypeName = '';
                } else {
                    $cogsTypeName = $cogsModel->getTypeTitle();
                }
                $cogsJSString .= '{value: ' . $cogsValue . ', label: "' . $cogsTypeName . '" }';
                $currentIndex = array_search($cogsTypeRef, array_keys($cogsTypesData));
                if ($currentIndex != ($cogsArrayCount - 1)) {
                    $cogsJSString .= ',';
                }
            }
            $cogsJSString .= '],
                    colors: [' . $colourString . '], 
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });';
            $this->addDynamicJS($cogsJSString);
        }
    }

    protected function addSalesJS() {
        $colourString = $this->getColourString();
        $salesTypesData = $this->accReport->getProperty('sales');
        $salesTotal = $this->accReport->getSalesTotal();
        if (!empty($salesTypesData) && !empty($salesTotal)) {
            $salesArrayCount = count($salesTypesData);
            $salesJSString = 'new Morris.Donut({
                    element: "inv_cogs_sales_report_sales_graph",
                    data: [';
            foreach ($salesTypesData as $salesTypeRef => $salesValue) {
                if (empty($salesValue)) {
                    $salesValue = 0;
                }
                $salesModel = IncomeItemFactory::buildNewModel($salesTypeRef);
                if (empty($salesModel)) {
                    $salesTypeName = '';
                } else {
                    $salesTypeName = $salesModel->getTypeTitle();
                }
                $salesJSString .= '{value: ' . $salesValue . ', label: "' . $salesTypeName . '" }';
                $currentIndex = array_search($salesTypeRef, array_keys($salesTypesData));
                if ($currentIndex != ($salesArrayCount - 1)) {
                    $salesJSString .= ',';
                }
            }
            $salesJSString .= '],
                    colors: [' . $colourString . '],
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });';
            $this->addDynamicJS($salesJSString);
        }
    }

}