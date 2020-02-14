<?php
/**
 * Description of AbstractAccReportSalesBySKUDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportSalesBySKUDetailView extends AbstractAccReportDetailView {

    protected $colours = NULL;
    
    /**
     * @param String[] $colours
     */
    public function setColours($colours) {
        $this->colours = $colours;
    }

    public function __construct(\AbstractAccReport $accReport) {
        parent::__construct($accReport);
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/custom_morris.js');
    }

    protected function buildViewBody() {
        $brandRefs = $this->accReport->getBrandRefs();
        if (!empty($brandRefs)) {
            $count = count($brandRefs);
            if ($count == 1) {
                $brand = InvItemBrandFactory::getModelByBrandRef($brandRefs[0]);
                if (!empty($brand)) {
                    $title = $brand->getProperty('title');
                    $this->addGraphAndTable($title, $brandRefs[0]);
                }
            } else {
                $colIndex = 0;
                $numberOfBrandsPerRow = 2;
                for ($i=0;$i<$count;$i++) {
                    $brandRef = $brandRefs[$i];
                    $brand = InvItemBrandFactory::getModelByBrandRef($brandRef);
                    if (!empty($brand)) {
                        if ($colIndex == 0) {
                            $this->addHTML('<div class="flex_row">');
                        }
                        $this->addHTML('<div class="flex_col">');
                        $this->addGraphAndTable($brand->getProperty('title'), $brandRef);
                        $this->addHTML('</div>');
                        if ($colIndex == $numberOfBrandsPerRow - 1) {
                            $this->addHTML('</div>');
                        }
                        $colIndex++;
                        if ($colIndex == $numberOfBrandsPerRow) {
                            $colIndex = 0;
                        }
                    }
                }
            }
        } else {
            $brandRefs = array('totals');
            $this->addGraphAndTable('', 'totals');
        }
        $this->addGraphJS($brandRefs);
    }
    
    protected function addGraphAndTable($title, $brandRef) {
        if (!empty($title)) {
            $this->addHTML('<h2>' . $title . '</h2>');
        }
        $this->addGraph($brandRef);
        $properties = $this->accReport->getProperty($brandRef);
        $this->addTable($properties);
    }
    
    protected function addGraph($brandRef) {
        $this->addHTML('<div class="graph_wrap" id="sales_by_sku_'.$brandRef.'_graph"></div>');
    }

    protected function addGraphJS($brandRefs) {
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
        foreach ($brandRefs as $brandRef) {
            $properties = $this->accReport->getProperty($brandRef);
            if (!empty($properties)) {
                $brandItemsData = $properties;
                
                $brandJSString = 'new Morris.Donut({
                    element: "sales_by_sku_'.$brandRef.'_graph",
                    data: [';
                $brandItemsCount = count($brandItemsData);
                foreach ($brandItemsData as $invItemId => $salesTotal) {
                    $invItemModel = InvItemFactory::getModelById($invItemId);
                    if ($salesTotal > 0) {
                        $brandJSString .= '{value: ' . $salesTotal . ', label: "' . $invItemModel->getProperty('sku') . '" }';
                        $currentIndex = array_search($invItemId, array_keys($brandItemsData));
                        if ($currentIndex != ($brandItemsCount - 1)) {
                            $brandJSString .= ',';
                        }
                    }
                }
                $brandJSString .= '],
                    colors: [' . $colourString . '], 
                    resize: true,
                    formatter: function(x, data){
                        return "$"+numberWithCommas(data.value.toFixed(2));
                    }
                });';
                $this->addDynamicJS($brandJSString);
                }
            }
           
    }

    protected function addTable($properties) {
        if (!empty($properties)) {
            $this->addHTML('<div class="flex_table ui_table acc_report_table">');
            $this->addHTML('<div class="flex_row flex_head acc_report_row table_header_row">')
                    ->addHTML('<div class="flex_col">SKU</div>')
                    ->addHTML('<div class="flex_col">Item Name</div>')
                    ->addHTML('<div class="flex_col">Sales</div>')
                    ->addHTML('</div>');
            foreach ($properties as $invItemId => $salesTotal) {

                $invItem = InvItemFactory::getModelById($invItemId);
                if (!empty($invItem) && $salesTotal > 0) {
                    $this->addHTML('<div class="flex_row acc_report_row">');
                    $this->addHTML('<div class="flex_col">' . $invItem->getProperty('sku') . '</div>');
                    $this->addHTML('<div class="flex_col">' . $invItem->getProperty('name') . '</div>');
                    $this->addHTML('<div class="flex_col">$' . GI_StringUtils::formatMoney($salesTotal) . '</div>');
                    $this->addHTML('</div>');
                }
            }
            $this->addHTML('</div>');
        }
    }
    
}