<?php

/**
 * Description of AbstractAccReportSalesByRegion
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportSalesByRegion extends AbstractAccReport {
    
    protected $values = array();
    protected $countryCodes = array(
            'CAN',
            'USA'
        );
    protected $regionCodes = array();
    
    
    public function getTitle() {
        return 'Sales by Province/State';
    }

    public function getDescription() {
        return 'Shows total sales for each Province/State over a given time period.';
    }

    public function getColour() {
        return '00E0E5';
    }

    public function getInitials() {
        return 'PS';
    }

    public function getDetailView() {
        return new AccReportSalesByRegionDetailView($this);
    }
    
    public function getValues() {
        $this->buildReport();
        return $this->values;
    }
    
    public function setCountryCodes($countryCodes) {
        $this->countryCodes = $countryCodes;
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            $this->setRegionCodes();
            $this->calculateValues();
            $this->reportBuilt = true;
        }
        return true;
    }
    
    protected function setRegionCodes() {
        $regions = array();
        if (empty($this->countryCodes)) {
            return;
        }
        foreach ($this->countryCodes as $countryCode) {
            $regions[$countryCode] = GeoDefinitions::getRegionCodesByCountry($countryCode);
        }
        $this->regionCodes = $regions;
    }
    
    protected function calculateValues() {
        $primaryCurrencyRef = NULL;
        $secondaryCurrencyRef = NULL;
        $primaryCurrency = $this->getCurrency();
        if (!empty($primaryCurrency)) {
            $primaryCurrencyRef = $primaryCurrency->getProperty('ref');
        }
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (!empty($secondaryCurrency)) {
            $secondaryCurrencyRef = $secondaryCurrency->getProperty('ref');
        }
        if ($primaryCurrencyRef === $secondaryCurrencyRef) {
            $secondaryCurrencyRef = NULL;
        }
        $values = array();
        
        if (!empty($this->regionCodes)) {
            
            foreach ($this->regionCodes as $countryCode=>$regionCodes) {
                $countryValues = array();
                if (!empty($regionCodes)) {
                    foreach ($regionCodes as $regionCode) {
                        $regionValues = array();
                        if (!empty($primaryCurrencyRef)) {
                            $regionValues[$primaryCurrencyRef] = $this->calculateSalesByRegion($primaryCurrency, $countryCode, $regionCode);
                        }
                        if (!empty($secondaryCurrencyRef)) {
                            $regionValues[$secondaryCurrencyRef] = $this->calculateSalesByRegion($secondaryCurrency, $countryCode, $regionCode);
                        }
                        $countryValues[$regionCode] = $regionValues;
                    }
                }
                $values[$countryCode] = $countryValues;
            }
        }
        $this->values = $values;
    }
    
    protected function calculateSalesByRegion(AbstractCurrency $currency, $countryCode, $regionCode) {
        $tableName = OrderLineFactory::getDbPrefix() . 'order_line';
        $search = OrderLineFactory::search();
        $search->filterByTypeRef('sales');
        $search->join('order_shipment', 'id', $tableName, 'order_shipment_id', 'OSHIP')
                ->join('order_shipment_sales', 'parent_id', 'OSHIP', 'id', 'OSHIPSALES')
                ->join('contact_info', 'id', 'OSHIPSALES', 'ship_to_addr_id', 'CI')
                ->join('contact_info_address', 'parent_id', 'CI', 'id', 'CIA')
                ->join('order', 'id', $tableName, 'order_id', 'ORDER')
                ->join('order_line_sales', 'parent_id', $tableName, 'id', 'OLSALES')
                ->join('time_interval', 'id', 'OLSALES', 'shipped_ti_id', 'TI');
        $search->filter('CIA.addr_region', $regionCode)
                ->filter('CIA.addr_country', $countryCode)
                ->filter('ORDER.currency_id', $currency->getId());
        $startDateTime = $this->getStartDate();
        $endDateTime = $this->getEndDate();
        $search->filterGreaterOrEqualTo('TI.start_date_time', $startDateTime->format('Y-m-d') . ' 00:00:00');
        $search->filterLessOrEqualTo('TI.end_date_time', $endDateTime->format('Y-m-d') . ' 23:59:59');
        $results = $search->sum(array('subtotal'));
        if (!empty($results)) {
            return $results['subtotal'];
        }
        return 0;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeadersToCSV($csv);
        $values = $this->getValues();
        if (!empty($values)) {
            foreach ($values as $countryCode => $regionsData) {
                $this->addRowsToCSV($csv, $countryCode, $regionsData);
            }
        }
        return $csv;
    }

    protected function addCurrencyAndDatesToCSV(GI_CSV $csv) {
        $startDate = $this->getStartDate()->format('Y-m-d');
        $endDate = $this->getEndDate()->format('Y-m-d');
        $row = array(
            'Start: ' . $startDate,
            'End: ' . $endDate,
        );
        $csv->addHeaderRow($row);
    }

    protected function addHeadersToCSV(GI_CSV $csv) {
        $values = $this->getValues();
        $headers = array(
            'Destination Province/State',
            'Country'
        );
        if (!empty($values)) {
            $firstCountryValues = $values[array_keys($values)[0]];
            $firstRegionValues = $firstCountryValues[array_keys($firstCountryValues)[0]];
            foreach ($firstRegionValues as $currencyRef => $value) {
                $currency = CurrencyFactory::getModelByRef($currencyRef);
                $headers[] = $currency->getProperty('name') . ' Total';
            }
            
             $csv->addHeaderRow($headers);
        }
    }

    protected function addRowsToCSV(GI_CSV $csv, $countryCode, $regionsData) {
        $countryName = GeoDefinitions::getCountryNameFromCode($countryCode);
        foreach ($regionsData as $regionCode => $regionData) {
            $regionRow = array();
            $regionName = GeoDefinitions::getRegionNameFromCode($countryCode, $regionCode);
            $regionRow[] = $regionName;
            $regionRow[] = $countryName;
            foreach ($regionData as $currencyRef => $value) {
                $regionRow[] = $value;
            }
            $csv->addRow($regionRow);
        }
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_sales_by_region_report')) {
            return true;
        }
        return false;
    }

}
