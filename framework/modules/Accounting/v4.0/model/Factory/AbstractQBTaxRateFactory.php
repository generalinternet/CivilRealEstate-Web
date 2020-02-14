<?php
/**
 * Description of AbstractQBTaxRateFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.1.1
 */
abstract class AbstractQBTaxRateFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_tax_rate';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'purchase':
            case 'sales':
            default:
                $model = new QBTaxRate($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'rate':
                $typeRefs = array('rate');
                break;
            case 'purchase':
                $typeRefs = array('purchase');
                break;
            case 'sales':
                $typeRefs = array('sales');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param Integer $qbTaxRateQBId
     * @param String $date
     * @return String[]
     */
    public static function getQBTaxRateDataByQBId($qbTaxRateQBId, $date = NULL, $rateType = 'sales') {
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            return array();
        }
        $franchiseId = QBConnection::getFranchiseId();
        $dataArray = array();
        if (apcu_exists('qb_tax_rates_' . $franchiseId)) {
            $taxRates = apcu_fetch('qb_tax_rates_' . $franchiseId);
            if (!empty($taxRates) && isset($taxRates[$qbTaxRateQBId])) {
                $dataArray = $taxRates[$qbTaxRateQBId];
            }
        }
        if (empty($date)) {
            $date = Date('Y-m-d');
        }
        if (!empty($dataArray)) {
            $effectiveDate = $dataArray['effective_date'];
            $endDate = $dataArray['end_date'];
            $effectiveDateTime = new DateTime($effectiveDate);
            $providedDateTime = new DateTime($date);
            if ($providedDateTime >= $effectiveDateTime) {
                $endDateTime = NULL;
                if (!empty($endDate)) {
                    $endDateTime = new DateTime($endDate);
                }
                if (empty($endDateTime) || ($providedDateTime <= $endDateTime)) {
                    return $dataArray;
                }
            } else {
                $dataArray = array();
            }
        }
        if (!empty($dataArray)) {
            return $dataArray;
        }
        $dataService = NULL;
        if (ProjectConfig::getIsQuickbooksIntegrated()) {
            $dataService = QBConnection::getInstance();
        }
        if (!empty($dataService)) {
            $qbData = static::getTaxRateDataFromQB($qbTaxRateQBId);
            if (!empty($qbData)) {
                $dataArray = static::updateDBFromQBData($qbData, $rateType, $date);
            }
        }
        if (empty($dataArray)) {
            $model = static::getModelFromQBIdAndDate($qbTaxRateQBId, $date);
            if (!empty($model)) {
                $dataArray = $model->getDataArray();
            }
        }
        if (!empty($dataArray) && empty($dataArray['end_date'])) { //Only store current rates in cache
            $taxRates = array();
            if (apcu_exists('qb_tax_rates_' . $franchiseId)) {
                $taxRates = apcu_fetch('qb_tax_rates_' . $franchiseId);
            }
            $taxRates[$qbTaxRateQBId] = $dataArray;
            apcu_store('qb_tax_rates_' . $franchiseId, $taxRates, ProjectConfig::getApcuTTL());
        }
        return $dataArray;
    }

    protected static function getTaxRateDataFromQB($qbTaxRateQBId) {
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            return array();
        }
        $dataService = QBConnection::getInstance();
        $rateQuery = "Select * from TaxRate where id='" . $qbTaxRateQBId . "'";
        try {
            $results = $dataService->Query($rateQuery);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                $results = array();
            }
        } catch (Exception $ex) {
            $results = array();
        }
        if (!empty($results)) {
            $qbRate = $results[0];
            return $qbRate;
        }
        return NULL;
    }

    protected static function updateDBFromQBData($qbData, $rateType, $date = NULL) {
        $franchiseId = QBConnection::getFranchiseId();
        $name = $qbData->Name;
        $description = $qbData->Description;
        $qbId = $qbData->Id;
        $activeBool = $qbData->Active;
        if ($activeBool == 'true') {
            $active = 1;
        } else {
            $active = 0;
        }
        $effectiveTaxRate = $qbData->EffectiveTaxRate;
        if (!is_array($effectiveTaxRate)) {
            $effectiveTaxRate = array($effectiveTaxRate);
        }
        $dataArray = array();
        if (!empty($effectiveTaxRate)) {
            foreach ($effectiveTaxRate as $etr) {
                $effectiveDate = $etr->EffectiveDate;
                $rate = $etr->RateValue;
                $endDate = $etr->EndDate;
                $search = static::search();
                $search->filter('qb_id', $qbId)
                        ->filter('effective_date', $effectiveDate)
                        ->filter('rate', $rate)
                        ->ignoreFranchise('qb_tax_rate');
                QBConnection::addFranchiseFilterToDataSearch($search);
                $results = $search->select();
                if (empty($results)) {
                    $model = static::buildNewModel($rateType);
                    $model->setProperty('qb_id', $qbId);
                    $model->setProperty('rate', $rate);
                    $model->setProperty('name', $name);
                    $model->setProperty('description', $description);
                    $model->setProperty('effective_date', $effectiveDate);
                    $model->setProperty('end_date', $endDate);
                    if (!empty($franchiseId)) {
                        $model->setProperty('franchise_id', $franchiseId);
                    }
                    if (!$model->save()) {
                        return false;
                    }
                } else {
                    $model = $results[0];
                }
                if (empty($date)) {
                    if (empty($endDate)) {
                        $dataArray = $model->getDataArray();
                    }
                } else {
                    $effectiveDateTime = new DateTime($effectiveDate);
                    $providedDateTime = new DateTime($date);
                    if ($providedDateTime >= $effectiveDateTime) {
                        $endDateTime = NULL;
                        if (!empty($endDate)) {
                            $endDateTime = new DateTime($endDate);
                        }
                        if (empty($endDateTime) || ($providedDateTime <= $endDateTime)) {
                            $dataArray = $model->getDataArray();
                        }
                    }
                }
            }
        }
        return $dataArray;
    }

    protected static function getModelFromQBIdAndDate($qbTaxRateQBId, $date) {
        $search = static::search();
        $search->filter('qb_id', $qbTaxRateQBId)
                ->ignoreFranchise('qb_tax_rate')
                ->filterLessOrEqualTo('effective_date', $date)
                ->filterGroup()
                ->filterGroup()
                ->filterGreaterOrEqualTo('end_date', $date)
                ->closeGroup()
                ->orIf()
                ->filterGroup()
                ->filterNull('end_date')
                ->closeGroup()
                ->closeGroup()
                ->andIf();
        QBConnection::addFranchiseFilterToDataSearch($search);
        $results = $search->select();
        if (!empty($results)) {
            return $results[0];
        }
        return NULL;
    }
    
    /**
     * 
     * @param Integer $qbId
     * @return AbstractQBTaxRate[]
     */
    public static function getModelArrayByQBId($qbId) {
        $search = static::search();
        $search->filter('qb_id', $qbId)
                ->ignoreFranchise('qb_tax_rate');
        QBConnection::addFranchiseFilterToDataSearch($search);
        return $search->select();
    }

    /**
     * 
     * @param AbstractQBTaxCode $qbTaxCode
     * @param String $date
     * @param String $typeRef
     * @return AbstractQBTaxRate[]
     */
    public static function getModelArrayByQBTaxCode(AbstractQBTaxCode $qbTaxCode, $date = NULL, $typeRef = 'sales') {
        $search = static::search();
        $tableName = static::getDbPrefix() . 'qb_tax_rate';
        $search->join('qb_tax_code_has_rate', 'qb_tax_rate_id', $tableName, 'id', 'QBTCHR')
                ->filter('QBTCHR.qb_tax_code_id', $qbTaxCode->getId())
                ->filterByTypeRef($typeRef)
                ->ignoreFranchise('qb_tax_rate');
        if (!empty($date)) {
            $search->filterLessOrEqualTo('effective_date', $date)
                    ->filterGroup()
                    ->filterGroup()
                    ->filterGreaterOrEqualTo('end_date', $date)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->filterNull('end_date')
                    ->closeGroup()
                    ->closeGroup()
                    ->andIf();
        }
        if (!empty($date)) {
            $search->groupBy('qb_id');
        }
        QBConnection::addFranchiseFilterToDataSearch($search);
        $search->orderBy('id');

        return $search->select();
    }

    /**
     * 
     * @param Integer $taxCodeQBId
     * @param String $date
     * @param String $type
     * @return String[]
     */
    public static function getRatesDataFromTaxCodeData($taxCodeQBId, $date = NULL, $type = 'sales') {
        $taxCode = QBTaxCodeFactory::getQBTaxCodeDataById($taxCodeQBId, $date);
        if (!empty($taxCode)) {
            switch ($type) {
                case 'purchase':
                    return $taxCode['purchase_rates'];
                case 'sales':
                default:
                    return $taxCode['sales_rates'];
            }
        }
        return NULL;
    }

    /**
     * 
     * @param String $type
     * @param String $date
     * @return String[]
     */
    public static function getQBRatesDataFromTaxCodesData($type = 'sales', $date = NULL) {
        $taxRates = array();
        $taxCodes = QBTaxCodeFactory::getQBTaxCodeData($date);
        if (!empty($taxCodes)) {
            foreach ($taxCodes as $taxCodeId => $taxCodeArray) {
                if ($type == 'sales') {
                    $ratesKey = 'sales_rates';
                } else if ($type == 'purchase') {
                    $ratesKey = 'purchase_rates';
                }
                $rates = NULL;
                if (isset($taxCodeArray[$ratesKey])) {
                    $rates = $taxCodeArray[$ratesKey];
                }
                if (!empty($rates)) {
                    foreach ($rates as $rateId => $rateArray) {
                        $taxRates[$rateId] = $rateArray;
                    }
                }
            }
        }

        return $taxRates;
    }

    public static function getQBTaxRateName($taxRateQBId) {
        $ratesData = static::getQBTaxRateDataByQBId($taxRateQBId);
        if (!empty($ratesData)) {
            return $ratesData['description'];
        }
        return NULL;
    }
    

}
