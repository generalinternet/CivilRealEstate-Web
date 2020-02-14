<?php
/**
 * Description of AbstractQBTaxCode
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractQBTaxCode extends GI_Model {
    
    public function getDataArray() {
        $dataArray = array();
        $dataArray['name'] = $this->getProperty('name');
        $dataArray['description'] = $this->getProperty('description');
        $dataArray['active'] = $this->getProperty('active');
        $dataArray['sales_rates'] = $this->getSalesRatesData();
        $dataArray['purchase_rates'] = $this->getPurchaseRatesData();
        return $dataArray;
    }
    
    protected function getSalesRatesData() {
        $dataArray = array();
        $date = Date('Y-m-d');
        $taxRateModels = QBTaxRateFactory::getModelArrayByQBTaxCode($this, $date, 'sales');
        if (!empty($taxRateModels)) {
            foreach ($taxRateModels as $taxRateModel) {
                $qbId = $taxRateModel->getProperty('qb_id');
                $dataArray[$qbId] = $taxRateModel->getDataArray();
            }
        }
        return $dataArray;
    }

    protected function getPurchaseRatesData() {
        $dataArray = array();
        $date = Date('Y-m-d');
        $taxRateModels = QBTaxRateFactory::getModelArrayByQBTaxCode($this, $date, 'purchase');
        if (!empty($taxRateModels)) {
            foreach ($taxRateModels as $taxRateModel) {
                $qbId = $taxRateModel->getProperty('qb_id');
                $dataArray[$qbId] = $taxRateModel->getDataArray();
            }
        }
        return $dataArray;
    }

    public function linkToTaxRates($qbTaxRateQBId) {
        $taxRates = QBTaxRateFactory::getModelArrayByQBId($qbTaxRateQBId);
        if (!empty($taxRates)) {
            foreach ($taxRates as $taxRate) {
                if (!QBTaxCodeFactory::linkQBTaxCodeAndQBTaxRate($this, $taxRate)) {
                    return false;
                }
            }
        }
        return true;
    }
    
}
