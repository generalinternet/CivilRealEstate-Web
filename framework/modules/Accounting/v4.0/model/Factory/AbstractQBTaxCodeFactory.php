<?php
/**
 * Description of AbstractQBTaxCodeFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.1.1
 */
abstract class AbstractQBTaxCodeFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'qb_tax_code';
    protected static $models = array();
    protected static $modelsByQBId = array();
    protected static $exemptTaxCodeQBIds = array();
    protected static $countriesThatUseQBAst = array(
        'USA'
    );

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new QBTaxCode($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param Integer $qbId
     * @return AbstractQBTaxCode
     */
    public static function getModelByQBId($qbId) {
        $franchiseId = QBConnection::getFranchiseId();
        if (isset(static::$modelsByQBId[$franchiseId][$qbId])) {
            return static::$modelsByQBId[$franchiseId][$qbId];
        }
        $search = static::search();
        $search->filter('qb_id', $qbId)
                ->ignoreFranchise('qb_tax_code');
        QBConnection::addFranchiseFilterToDataSearch($search);
        $results = $search->orderBy('id')
                ->select();
        if (!empty($results)) {
            $model = $results[0];
            if (!isset(static::$modelsByQBId[$franchiseId])) {
                static::$modelsByQBId[$franchiseId] = array();
            }
            static::$modelsByQBId[$franchiseId][$qbId] = $model;
            return $model;
        }
        return NULL;
    }

    /**
     * Returns an array of arrays, one for each tax code, with all the rates data nested within
     * @param String $date - Used to apply historical rates, if applicable.
     * @return String[]
     */
    public static function getQBTaxCodeData($date = NULL) {
        if (static::getTaxingUsesQBAst()) {
            return array();
        }
        $franchiseId = QBConnection::getFranchiseId();
        $dataArray = array();
        if (apcu_exists('qb_tax_codes_' . $franchiseId)) {
            $dataArray = apcu_fetch('qb_tax_codes_' . $franchiseId);
        }
        $dataService = NULL;
        if (empty($dataArray)) {
            if (ProjectConfig::getIsQuickbooksIntegrated()) {
                $dataService = QBConnection::getInstance();
            }
        }
        if (!empty($dataService)) {
            $qbData = static::getTaxCodeDataFromQB();
            $dataArray = static::updateDBFromQBData($qbData);
        }
        if (empty($dataArray)) {
            $dataArray = static::getQBTaxCodeDataFromDB();
        }
        if (!empty($dataArray)) {
            apcu_store('qb_tax_codes_' . $franchiseId, $dataArray, ProjectConfig::getApcuTTL());
            $dataArray = static::updateQBTaxCodeDataByDate($dataArray, $date);
        }
        return $dataArray;
    }

    protected static function getTaxCodeDataFromQB() {
        $taxCodes = array();
        $dataService = QBConnection::getInstance();
        if (empty($dataService)) {
            return NULL;
        }
        $query = "Select * from TaxCode";
        try {
            $results = $dataService->Query($query);
            $error = $dataService->getLastError();
            if (!empty($error)) {
                $results = array();
            }
        } catch (Exception $ex) {
            $results = array();
        }
        if (!empty($results)) {
            foreach ($results as $taxCode) {
                $id = $taxCode->Id;
                $active = $taxCode->Active;
                if (!($active == 'true')) {
                    $modelSearch = static::search()
                            ->filter('qb_id', $id)
                            ->filter('active', 1)
                            ->ignoreFranchise('qb_tax_code');
                    QBConnection::addFranchiseFilterToDataSearch($modelSearch);
                    $models = $modelSearch->select();
                    if (!empty($models)) {
                        foreach ($models as $model) {
                            $model->setProperty('active', 0);
                            $model->save();
                        }
                    }
                    continue;
                }
                $name = $taxCode->Name;
                $description = $taxCode->Description;
                
                $taxCodeValues = array();
                $taxCodeValues['name'] = $name;
                $taxCodeValues['description'] = $description;
                $taxCodeValues['active'] = 1;
                $salesRatesArray = array();
                $purchaseRatesArray = array();

                if (isset($taxCode->SalesTaxRateList) && isset($taxCode->SalesTaxRateList->TaxRateDetail)) {
                    $salesTaxRateDetails = $taxCode->SalesTaxRateList->TaxRateDetail;
                    if (!empty($salesTaxRateDetails)) {
                        if (!is_array($salesTaxRateDetails)) {
                            $salesTaxRateDetails = array($salesTaxRateDetails);
                        }
                        foreach ($salesTaxRateDetails as $salesTaxRateDetail) {
                            $salesTaxRateId = $salesTaxRateDetail->TaxRateRef;
                            $rate = QBTaxRateFactory::getQBTaxRateDataByQBId($salesTaxRateId, NULL, 'sales');
                            if (!empty($rate)) {
                                if ($rate['rate'] != 0) {
                                    $salesRatesArray[$salesTaxRateId] = $rate;
                                }
                            } else {
                                //TODO - error
                            }
                        }
                    }
                }
                $taxCodeValues['sales_rates'] = $salesRatesArray;

                if (isset($taxCode->PurchaseTaxRateList) && isset($taxCode->PurchaseTaxRateList->TaxRateDetail)) {
                    $purchaseTaxRateDetails = $taxCode->PurchaseTaxRateList->TaxRateDetail;
                    if (!empty($purchaseTaxRateDetails)) {
                        if (!is_array($purchaseTaxRateDetails)) {
                            $purchaseTaxRateDetails = array($purchaseTaxRateDetails);
                        }
                        foreach ($purchaseTaxRateDetails as $purchaseTaxRateDetail) {
                            $purchaseTaxRateId = $purchaseTaxRateDetail->TaxRateRef;
                            $rate = QBTaxRateFactory::getQBTaxRateDataByQBId($purchaseTaxRateId, NULL, 'purchase');
                            if (!empty($rate)) {
                                if ($rate['rate'] != 0) {
                                    $purchaseRatesArray[$purchaseTaxRateId] = $rate;
                                }
                            } else {
                                //TODO - error
                            }
                        }
                    }
                }
                $taxCodeValues['purchase_rates'] = $purchaseRatesArray;
                $taxCodes[$id] = $taxCodeValues;
            }
        }

        return $taxCodes;
    }

    protected static function updateDBFromQBData($qbData) {
         if (!empty($qbData)) {
            $franchiseId = QBConnection::getFranchiseId();
            foreach ($qbData as $qbId => $taxCodeArray) {
                $name = $taxCodeArray['name'];
                $description = $taxCodeArray['description'];
                $salesRates = $taxCodeArray['sales_rates'];
                $purchaseRates = $taxCodeArray['purchase_rates'];
                $active = $taxCodeArray['active'];
                $model = static::getModelByQBId($qbId);
                if (empty($model)) {
                    $model = static::buildNewModel();
                    $model->setProperty('name', $name);
                    $model->setProperty('description', $description);
                    $model->setProperty('qb_id', $qbId);
                    $model->setProperty('active', $active);
                    if (!empty($franchiseId)) {
                        $model->setProperty('franchise_id', $franchiseId);
                    }
                    if (!$model->save()) {
                        return NULL;
                    }
                }
                if (!empty($salesRates)) {
                    foreach ($salesRates as $salesRateQBId => $salesRateData) {
                        if (!$model->linkToTaxRates($salesRateQBId)) {
                            return NULL;
                        }
                    }
                }
                if (!empty($purchaseRates)) {
                    foreach ($purchaseRates as $purchaseRateQBId => $purchaseRateData) {
                        if (!$model->linkToTaxRates($purchaseRateQBId)) {
                            return NULL;
                        }
                    }
                }
            }
        }
        return $qbData;
    }
    
    protected static function getQBTaxCodeDataFromDB() {
        $dataArray = array();
        $search = static::search();
        $search->filter('active', 1)
                ->ignoreFranchise('qb_tax_code');
        QBConnection::addFranchiseFilterToDataSearch($search);
        $models = $search->select();
        if (!empty($models)) {
            foreach ($models as $model) {
                $qbId = $model->getProperty('qb_id');
                $dataArray[$qbId] = $model->getDataArray();
            }
        }
        return $dataArray;
    }
    
    protected static function updateQBTaxCodeDataByDate($dataArray, $date = NULL) {
        if (empty($date)) {
            return $dataArray;
        }
        $reqDateTime = new DateTime($date);
        if (!empty($dataArray)) {
            foreach ($dataArray as $codeQBId=>$codeData) {
                if (isset($codeData['sales_rates']) && !empty($codeData['sales_rates'])) {
                    $salesRates = $codeData['sales_rates'];
                    foreach ($salesRates as $salesRateQBId => $salesRateData) {
                        $replace = false;
                        $sedt = new DateTime($salesRateData['effective_date']);
                        if ($reqDateTime < $sedt) {
                            $replace = true;
                        } else {
                            $endDate = $salesRateData['end_date'];
                            if (!empty($endDate)) {
                                $sendt = new DateTime($endDate);
                                if ($reqDateTime > $sendt) {
                                    $replace = true;
                                }
                            }
                        }
                        if ($replace) {
                            $salesReplacementData = QBTaxRateFactory::getQBTaxRateDataByQBId($salesRateQBId, $date, 'sales');
                            $dataArray[$codeQBId]['sales_rates'][$salesRateQBId] = $salesReplacementData;
                        }
                        
                    }
                }
                if (isset($codeData['purchase_rates']) && !empty($codeData['purchase_rates'])) {
                    $purchaseRates = $codeData['purchase_rates'];
                    foreach ($purchaseRates as $purchaseRateQBId => $purchaseRateData) {
                        $replace = false;
                        $pedt = new DateTime($purchaseRateData['effective_date']);
                        if ($reqDateTime < $pedt) {
                            $replace = true;
                        } else {
                            $endDate = $purchaseRateData['end_date'];
                            if (!empty($endDate)) {
                                $pendt = new DateTime($endDate);
                                if ($reqDateTime > $pendt) {
                                    $replace = true;
                                }
                            }
                        }
                        if ($replace) {
                            $purchaseReplacementData = QBTaxRateFactory::getQBTaxRateDataByQBId($purchaseRateQBId, $date, 'purchase');
                            $dataArray[$codeQBId]['purchase_rates'][$purchaseRateQBId] = $purchaseReplacementData;
                        }
                    }
                }
            }
        }
        return $dataArray;
    }

    /**
     * 
     * @param AbstractQBTaxCode $taxCode
     * @param AbstractQBTaxRate $taxRate
     * @return boolean
     */
    public static function linkQBTaxCodeAndQBTaxRate(AbstractQBTaxCode $taxCode, AbstractQBTaxRate $taxRate) {
        $existingLinkSearch = new GI_DataSearch('qb_tax_code_has_rate');
        $existingLinkSearch->filter('qb_tax_code_id', $taxCode->getId())
                ->filter('qb_tax_rate_id', $taxRate->getId());
        $existingLinks = $existingLinkSearch->select();
        if (empty($existingLinks)) {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $newLink = new $defaultDAOClass('qb_tax_code_has_rate');
            $newLink->setProperty('qb_tax_code_id', $taxCode->getId());
            $newLink->setProperty('qb_tax_rate_id', $taxRate->getId());
            if (!$newLink->save()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 
     * @param String $qbId
     * @param String $date
     * @return String[]
     */
    public static function getQBTaxCodeDataById($qbId, $date = NULL) {
        $taxCodeData = static::getQBTaxCodeData($date);
        if (!empty($taxCodeData) && isset($taxCodeData[$qbId])) {
            return $taxCodeData[$qbId];
        }
        return array();
    }

    /**
     * 
     * @param String $date
     * @return String[]
     */
    public static function getOptionsArray($date = NULL) {
        $taxCodes = static::getQBTaxCodeData($date);
        $options = array();
        if (!empty($taxCodes)) {
            foreach ($taxCodes as $taxCodeId => $taxCodeValues) {
                $name = $taxCodeValues['name'];
                $options[$taxCodeId] = $name;
            }
        }
        return $options;
    }

    /**
     * 
     * @param String $taxCodeQBId
     * @param Float $netAmount
     * @param String $date
     * @param String $type - Used to determine which rates to apply - 'sales' or 'purchase' are valid
     * @return String[]
     */
    public static function getQBTaxTotals($taxCodeQBId, $netAmount, $date = NULL, $type = 'sales') {
        $totals = array();
        $taxRates = QBTaxRateFactory::getRatesDataFromTaxCodeData($taxCodeQBId, $date, $type);
        if (!empty($taxRates)) {
            foreach ($taxRates as $taxRateId => $taxRateArray) {
                $rate = (float) $taxRateArray['rate'];
                $taxAmount = round($netAmount * $rate / 100, 2);
                $taxRateArray['amount'] = $taxAmount;
                $totals[$taxRateId] = $taxRateArray;
            }
        }
        return $totals;
    }
    
    /**
     * 
     * @param type $taxCodeQBId
     * @return type
     */
    public static function getQBTaxCodeName($taxCodeQBId) {
        $options = static::getOptionsArray();
        if (!empty($options) && isset($options[$taxCodeQBId])) {
            return $options[$taxCodeQBId];
        }
        return NULL;
    }
    
    public static function verifyQBTaxCodeData() {
        $taxCodeData = static::getQBTaxCodeData();
        if (!empty($taxCodeData)) {
            return true;
        }
        return false;
    }
    
    public static function getExemptTaxCodeQBId() {
        $franchiseId = QBConnection::getFranchiseId();
        if (!isset(static::$exemptTaxCodeQBIds[$franchiseId])) {
            $search = static::search();
            $search->filter('name', 'Exempt')
                    ->filter('active', 1)
                    ->ignoreFranchise('qb_tax_code');
            QBConnection::addFranchiseFilterToDataSearch($search);
            $results = $search->select();
            if (!empty($results)) {
                $model = $results[0];
                static::$exemptTaxCodeQBIds[$franchiseId] = $model->getProperty('qb_id');
            }
        }
        if (isset(static::$exemptTaxCodeQBIds[$franchiseId])) {
            return static::$exemptTaxCodeQBIds[$franchiseId];
        }
        return NULL;
    }
    
    /**
     * @param string $type
     * @param string $regionCode
     * @param string $countryCode
     * @param integer $contactId
     * @return integer
     */
    public static function determineTaxCodeQBId($type = NULL, $regionCode = NULL, $countryCode = NULL, $contactId = NULL){
        $taxCodeId = NULL;
        if (empty($type)) {
            $type = 'sales';
        }
        $contact = NULL;
        if (!empty($contactId)) {
            $contact = ContactFactory::getModelById($contactId);
        }
        if (static::getTaxingUsesQBAst()) {
            $val = 1;
            if ($type == 'sales') {
                if (!empty($contact)) {
                    return $contact->getQBAstTaxFieldDefaultVal();
                }
            } else {
                $val = 0;
                $qbSettings = QBConnection::getQBSettingsModel();
                if (!empty($qbSettings)) {
                    $val = $qbSettings->getProperty('settings_qb.ast_po_line_def_val');
                    if (empty($val)) {
                        $val = 0;
                    }
                }
            }
            return $val;
        }
        if (!empty($contact)) {
            $taxCodeId = $contact->getQuickbooksDefaultTaxCodeRef();
        }

        if (empty($taxCodeId)) {
            $region = RegionFactory::getModelByCodes($countryCode, $regionCode);

            if (!empty($region)) {
                $taxCodeId = $region->getDefaultTaxCodeQBId($type);
            }
        }
        
        if (empty($taxCodeId)) {
            $taxCodeId = static::getExemptTaxCodeQBId();
        }
        return $taxCodeId;
    }

    public static function getTaxingUsesQBAst($franchiseId = NULL) {
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            return false;
        }
        if (is_null($franchiseId)) {
            $franchiseId = QBConnection::getFranchiseId();
        }
        if (apcu_exists('uses_qb_ast_' . $franchiseId)) {
            $uses = apcu_fetch('uses_qb_ast_' . $franchiseId);
            if (!empty($uses)) {
                return true;
            } else {
                return false;
            }
        }
        $qbSettingsModel = QBConnection::getQBSettingsModel();
        if (empty($franchiseId)) {
            $usesAst = ProjectConfig::getPrimaryInternalOrgUsesQBAst();
            if (!empty($qbSettingsModel)) {
                if ($usesAst) {
                    $qbSettingsModel->setProperty('auto_sales_tax', 1);
                    static::cacheUsesAst(0, true);
                } else {
                    $qbSettingsModel->setProperty('auto_sales_tax', 0);
                    static::cacheUsesAst(0, false);
                }
                $qbSettingsModel->save();
                return $usesAst;
            }
        } else {
            if (!empty($qbSettingsModel)) {
                if (!is_null($qbSettingsModel->getProperty('auto_sales_tax'))) {
                    if ($qbSettingsModel->getProperty('auto_sales_tax') == 1) {
                        static::cacheUsesAst($franchiseId, true);
                        return true;
                    } else {
                        static::cacheUsesAst($franchiseId, false);
                        return false;
                    }
                }
            }
            $franchise = ContactFactory::getModelById($franchiseId);
            if (!empty($franchise)) {
                $franchiseAddresses = $franchise->getContactInfoAddresses('billing');
                if(!empty($franchiseAddresses)) {
                    $franchiseAddress = $franchiseAddresses[0];
                    $countryCode = $franchiseAddress->getProperty('contact_info_address.addr_country');
                    if (in_array($countryCode, static::$countriesThatUseQBAst)) {
                        $usesAst = true;
                        static::cacheUsesAst($franchiseId, true);
                    } else {
                        $usesAst = false;
                        static::cacheUsesAst($franchiseId, false);
                    }
                    return $usesAst;
                }
            }
        }
        return false;
    }
    
    protected static function cacheUsesAst($franchiseId, $usesAst) {
        if ($usesAst) {
            $val = 1;
        } else {
            $val = 0;
        }
       return apcu_store('uses_qb_ast_' . $franchiseId, $val);
    }

}
