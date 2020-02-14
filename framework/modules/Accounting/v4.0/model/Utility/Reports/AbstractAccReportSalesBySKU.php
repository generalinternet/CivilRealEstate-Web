<?php
/**
 * Description of AbstractAccReportSalesBySKU
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportSalesBySKU extends AbstractAccReport {
    
    protected $brandRefs = NULL;
    

    public function getTitle() {
        return 'Sales by SKU';
    }
    
    public function getDescription() {
        return 'Shows total sales for each item (by SKU) so you can see which ones generate the most revenue for you.';
    }
    
    public function getColour() {
        return '00E0E5';
    }
    
        public function getInitials() {
        return 'SK';
    }
    
    public function getBrandRefs() {
        return $this->brandRefs;
    }
    
    /**
     * @param String[] $brandRefs
     */
    public function setBrandRefs($brandRefs) {
        $this->brandRefs = $brandRefs;
    }

    public function getDetailView() {
        return new AccReportSalesBySKUDetailView($this);
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            $currency = $this->getCurrency();
            if (empty($currency)) {
                return false;
            }
            $currencyRef = $currency->getProperty('ref');
            if ($currencyRef == 'cad') {
                $secondCurrency = CurrencyFactory::getModelByRef('usd');
            } else {
                $secondCurrency = CurrencyFactory::getModelByRef('cad');
            }
            if (!empty($this->brandRefs)) {
                foreach ($this->brandRefs as $brandRef) {
                    $currencySales = $this->getSalesBySKU($currency, $brandRef);
                    $secondCurrencySales = $this->getSalesBySKU($secondCurrency, $brandRef);
                    $secondCurrencySales = $this->convertSalesToCurrency($secondCurrencySales, $secondCurrency, $currency);
                    $this->properties[$brandRef] = GI_Math::mergeAndAddArrays($currencySales, $secondCurrencySales);
                }
            } else {
                $currencySales = $this->getSalesBySKU($currency);
                $secondCurrencySales = $this->getSalesBySKU($secondCurrency);
                $secondCurrencySales = $this->convertSalesToCurrency($secondCurrencySales, $secondCurrency, $currency);
                $this->properties['totals'] = GI_Math::mergeAndAddArrays($currencySales, $secondCurrencySales);
            }
            $this->reportBuilt = true;
        }
        return true;
    }

    protected function convertSalesToCurrency($sales, AbstractCurrency $sourceCurrency, AbstractCurrency $destinationCurrency) {
        if (!empty($sales)) {
            foreach ($sales as $invItemId => $value) {
                $sales[$invItemId] = $destinationCurrency->convertToThis($value, $sourceCurrency);
            }
        }
        return $sales;
    }

    protected function mergeAndAddArrays($array1, $array2) {
        $sums = array();
        foreach (array_keys($array1 + $array2) as $key) {
            $sums[$key] = (isset($array1[$key]) ? $array1[$key] : 0) + (isset($array2[$key]) ? $array2[$key] : 0);
        }
        return $sums;
    }

    protected function getSalesBySKU(AbstractCurrency $currency = NULL, $brandRef = NULL, $tags = array()) {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $returnArray = array();
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
        }
        $brand = NULL;
        if (!empty($brandRef)) {
            $brandSearch = InvItemBrandFactory::search()
                    ->filter('brand_ref', $brandRef);
            $brands = $brandSearch->select();
            if (!empty($brands)) {
                $brand = $brands[0];
            }
        }
        if (!empty($currency)) {
            $currencyId = $currency->getId();
        } else {
            $currencyId = ProjectConfig::getDefaultCurrencyId();
        }
        $invItemTableName = InvItemFactory::getDbPrefix() . 'inv_item';
        $invItemSearch = InvItemFactory::search();
        $invItemSearch->join('order_line_sales', 'inv_item_id', $invItemTableName, 'id', 'OLS')
                ->join('order_line', 'id', 'OLS', 'parent_id', 'OLINE')
                ->join('order', 'id', 'OLINE', 'order_id', 'ORDERZ')
                ->filter('ORDERZ.currency_id', $currencyId)
                ->filterGreaterThan('OLINE.subtotal', 0);
        if (!empty($startDate)) {
            $invItemSearch->filterGreaterThan('ORDERZ.date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $invItemSearch->filterLessThan('ORDERZ.date', $endDateSearchable);
        }
        if (!empty($brand)) {
            $invItemSearch->filter('inv_item_brand_id', $brand->getProperty('id'));
        }
        $invItems = $invItemSearch->groupBy('id')
                ->select();
        if (!empty($invItems)) {
            foreach ($invItems as $invItem) {
                $orderLineTableName = OrderLineFactory::getDbPrefix() . 'order_line';
                $orderLineSearch = OrderLineFactory::search()
                        ->filterByTypeRef('sales');
                $orderLineSearch->join('order', 'id', $orderLineTableName, 'order_id', 'ORDER')
                        ->join('order_has_income', 'order_id', 'ORDER', 'id', 'OHI')
                        ->join('income', 'id', 'OHI', 'income_id', 'INCOME')
                        ->filter('INCOME.currency_id', $currencyId)
                        ->filter('sales.inv_item_id', $invItem->getId())
                        ->filterNotNull('sales.shipped_ti_id');
                if (!empty($startDate)) {
                    $orderLineSearch->filterGreaterThan('INCOME.applicable_date', $startDateSearchable);
                }
                if (!empty($endDate)) {
                    $orderLineSearch->filterLessThan('INCOME.applicable_date', $endDateSearchable);
                }
                if (!empty($tags)) {
                    $orderLineSearch->join('income_link_to_tag', 'income_id', 'INCOME', 'id', 'tl');
                    $orderLineSearch->andIf();
                    $orderLineSearch->filterGroup();
                    $tagCount = count($tags);
                    for ($i = 0; $i < $tagCount; $i++) {
                        if ($i > 0) {
                            $orderLineSearch->orIf();
                        }
                        $tag = $tags[$i];
                        $orderLineSearch->filter('tl.tag_id', $tag->getProperty('id'));
                    }
                    $orderLineSearch->closeGroup();
                    $orderLineSearch->andIf();
                }
                $sumArray = $orderLineSearch->sum(array('subtotal' => 'subtotal'));
                $returnArray[$invItem->getProperty('id')] = $sumArray['subtotal'];
            }
        }
        arsort($returnArray, SORT_NUMERIC);
        return $returnArray;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeadersToCSV($csv);
        if (!empty($this->brandRefs)) {
            foreach ($this->brandRefs as $brandRef) {
                $brand = InvItemBrandFactory::getModelByBrandRef($brandRef);
                if (!empty($brand)) {
                    $csv->addRow(array(
                        strtoupper($brand->getProperty('title')),
                    ));
                    $properties = $this->getProperty($brandRef);
                    $this->addRowsToCSV($csv, $properties);
                }
            }
        } else {
            $properties = $this->getProperty('totals');
            $this->addRowsToCSV($csv, $properties);
        }
        return $csv;
    }
    
    protected function addHeadersToCSV(GI_CSV $csv) {
        $headers = array(
            'SKU',
            'Item Name',
            'Sales'
        );
        $csv->addHeaderRow($headers);
    }

    protected function addRowsToCSV(GI_CSV $csv, $properties) {
        foreach ($properties as $invItemId => $sales) {
            $invItem = InvItemFactory::getModelById($invItemId);
            if (!empty($invItem)) {
                $sku = $invItem->getProperty('sku');
                $name = $invItem->getProperty('name');
                $row = array(
                    $sku,
                    $name,
                    '$' . GI_StringUtils::formatMoney($sales),
                );
                $csv->addRow($row);
            }
        }
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_sales_by_sku_report')) {
            return true;
        }
        return false;
    }

}
