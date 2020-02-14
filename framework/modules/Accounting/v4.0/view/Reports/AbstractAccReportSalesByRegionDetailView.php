<?php

/**
 * Description of AbstractAccReportSalesByRegionDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportSalesByRegionDetailView extends AbstractAccReportDetailView {

    protected function buildViewBody() {
        $this->buildTables();
    }
    
    protected function buildTables() {
        $values = $this->accReport->getValues();
        if (!empty($values)) {
            foreach ($values as $countryCode => $regionData) {
                $countryName = GeoDefinitions::getCountryNameFromCode($countryCode);
                $this->addHTML('<h3>' . $countryName . '</h3>');
                $this->buildTable($regionData, $countryCode);
                $this->addHTML('<br />');
            }
        }
    }
    
    protected function buildTable($regionData, $countryCode) {
        if (!empty($regionData)) {
            $this->addHTML('<div class="flex_table">');
            $sampleData = $regionData[array_keys($regionData)[0]];
            $this->buildTableHeader($sampleData);
            foreach ($regionData as $regionCode =>$data) {
                $regionName = GeoDefinitions::getRegionNameFromCode($countryCode, $regionCode);
                $this->buildTableRow($regionName, $data);
            }
            $this->addHTML('</div>');
        }
    }
    
    protected function buildTableHeader($sampleData) {
        $this->addHTML('<div class="flex_row flex_head">');
        $this->addHTML('<div class="flex_col">Destination Province/State</div>');
        foreach ($sampleData as $currencyRef => $value) {
            $this->addHTML('<div class="flex_col">');
            $currency = CurrencyFactory::getModelByRef($currencyRef);
            $this->addHTML($currency->getProperty('name') . ' Total');
            $this->addHTML('</div>');
        }
        $this->addHTML('</div>');
    }
    
    protected function buildTableRow($regionName, $data) {
        $this->addHTML('<div class="flex_row">');
        $this->addHTML('<div class="flex_col">' . $regionName . '</div>');
        foreach ($data as $currencyRef => $value) {
            $this->addHTML('<div class="flex_col">');
            $this->addHTML('$' . GI_StringUtils::formatMoney($value));
            $this->addHTML('</div>');
        }
        $this->addHTML('</div>');
    }

}
