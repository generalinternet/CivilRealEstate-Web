<?php
/**
 * Description of AbstractRegion
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.1.0
 */
abstract class AbstractRegion extends GI_Model {

    /** @deprecated - since Oct. 2018 */
    protected $taxRegions = NULL;

    protected $ecoFees = NULL;
    protected $ecoFeeContact = NULL;
    protected $tableWrapId = 'region_table';
    protected $defaultLineType = 'by_unit';
    protected $addBlankLineOnAdd = false;
    protected static $searchFormId = 'region_search';

    protected $defaultQBTaxCodePurchase = NULL;
    protected $defaultQBTaxCodeSales = NULL;
    protected $defaultQBSalesOrderLineProduct = NULL;
    protected $defaultQBSalesOrderLineACEcoFeeProduct = NULL;
    
    public function __construct(\GI_DataMap $map, $factoryClassName = NULL) {
        parent::__construct($map, $factoryClassName);
        $this->tableWrapId .= '_' . $this->getProperty('region_code');
    }

    public function getCountryCode(){
        return $this->getProperty('country_code');
    }

    public function getRegionCode(){
        return $this->getProperty('region_code');
    }

    public function getRegionName(){
        return $this->getProperty('region_name');
    }

    public function getCountryName(){
        $countryCode = $this->getCountryCode();
        return GeoDefinitions::getCountryNameFromCode($countryCode);
    }


    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    /**
     * @deprecated since Oct. 2018
     * @return TaxRegion[]
     */
    public function getTaxRegions(){
        if (empty($this->taxRegions)) {
            $this->taxRegions = TaxRegionFactory::getTaxRegionsByRegion($this);
        }
        return $this->taxRegions;
    }

    /**
     * @param GI_Form $form
     * @return AbstractEcoFee[]
     */
    public function getEcoFees(GI_Form $form = NULL) {
        $ecoFees = array();
        if (!empty($form) && $form->wasSubmitted()) {
            $seqNums = filter_input(INPUT_POST, 'eco_fee_seq_nums', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            foreach ($seqNums as $seqNum) {
                $ecoFeeId = filter_input(INPUT_POST, 'eco_fee_id_' . $seqNum);
                $typeRef = filter_input(INPUT_POST, 'eco_fee_type_' . $seqNum);
                if (!empty($ecoFeeId)) {
                    $ecoFee = EcoFeeFactory::changeModelType(EcoFeeFactory::getModelById($ecoFeeId), $typeRef);
                } else {
                    $ecoFee = EcoFeeFactory::buildNewModel($typeRef);
                }
                $ecoFee->setFieldSuffix($seqNum);
                $ecoFees[] = $ecoFee;
            }
            return $ecoFees;
        } else {
            if (empty($this->ecoFees)) {
                $this->ecoFees = EcoFeeFactory::search()
                        ->filter('region_id', $this->getProperty('id'))
                        ->select();
            }
            $ecoFees = $this->ecoFees;
        }
        if (empty($ecoFees) && $this->addBlankLineOnAdd) {
            $ecoFee = EcoFeeFactory::buildNewModel($this->defaultLineType);
            $ecoFees = array($ecoFee);
        }
        return $ecoFees;
    }

    public function getEcoFeeDetailView() {
        return new RegionEcoFeeDetailView($this);
    }

    public function getEcoFeeUITableView() {
        $ecoFees = $this->getEcoFees();
        if (!empty($ecoFees)) {
            $sampleEcoFee = $ecoFees[0];
        } else {
            $sampleEcoFee = EcoFeeFactory::buildNewModel('eco_fee');
        }
        $uiTableCols = $sampleEcoFee->getUITableCols();
        $uiTableView = new UITableView($ecoFees, $uiTableCols, NULL);
        $uiTableView->setLoadMore(true);
        $uiTableView->setTableWrapId($this->getTableWrapId());
        return $uiTableView;
    }

    public function getEcoFeeFormView(GI_Form $form) {
        return new RegionEcoFeeFormView($form, $this);
    }

    public function getEcoFeeIndexTitle() {
        return 'Taxes and Eco Fees';
    }
    
    public function getQBSettingsIndexTitle() {
        return 'Regional';
    }
    
    public function getQBSettingsDetailView() {
        return new RegionQBSettingsDetailView($this);
    }
    
    public function getQBSettingsFormView(GI_Form $form) {
        return new RegionQBSettingsFormView($form, $this);
    }

    public function handleEcoFeeFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateEcoFeeForm($form)) {
            $franchiseId = QBConnection::getFranchiseId();
            $defaultPurchaseQBId = filter_input(INPUT_POST, 'default_tax_purchase_qb_id');
            $defaultSalesQBId = filter_input(INPUT_POST, 'default_tax_sales_qb_id');

            $defaultQBTaxCodePurchase = $this->getDefaultTaxCodeQBPurchase();
            if (empty($defaultQBTaxCodePurchase)) {
                $defaultQBTaxCodePurchase = RegionQBDefaultFactory::buildNewModel('purchase');
                $defaultQBTaxCodePurchase->setProperty('region_id', $this->getProperty('id'));
                if (!empty($franchiseId)) {
                    $defaultQBTaxCodePurchase->setProperty('franchise_id', $franchiseId);
                }
            }
            $defaultQBTaxCodePurchase->setProperty('qb_id', $defaultPurchaseQBId);
            if (!$defaultQBTaxCodePurchase->save()) {
                return false;
            }

            $defaultQBTaxCodeSales = $this->getDefaultTaxCodeQBSales();
            if (empty($defaultQBTaxCodeSales)) {
                $defaultQBTaxCodeSales = RegionQBDefaultFactory::buildNewModel('sales');
                $defaultQBTaxCodeSales->setProperty('region_id', $this->getProperty('id'));
                if (!empty($franchiseId)) {
                    $defaultQBTaxCodeSales->setProperty('franchise_id', $franchiseId);
                }
            }
            $defaultQBTaxCodeSales->setProperty('qb_id', $defaultSalesQBId);
            if (!$defaultQBTaxCodeSales->save()) {
                return false;
            }
            
            $contactId = filter_input(INPUT_POST, 'eco_fee_contact_id');
            $this->setProperty('eco_fee_contact_id', $contactId);
            if (!$this->save()) {
                return false;
            }
            if (!$this->handleEcoFeeRowsFormSubmission($form)) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function handleEcoFeeRowsFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $existingEcoFees = $this->getEcoFees();
            $ecoFeesToRemove = array();
            if (!empty($existingEcoFees)) {
                foreach ($existingEcoFees as $existingEcoFee) {
                    $ecoFeesToRemove[$existingEcoFee->getProperty('id')] = $existingEcoFee;
                }
            }
            $seqNums = filter_input(INPUT_POST, 'eco_fee_seq_nums', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($seqNums)) {
                foreach ($seqNums as $seqNum) {
                    $ecoFeeId = filter_input(INPUT_POST, 'eco_fee_id_' . $seqNum);
                    $typeRef = filter_input(INPUT_POST, 'eco_fee_type_' . $seqNum);
                    if (!empty($ecoFeeId)) {
                        if (isset($ecoFeesToRemove[$ecoFeeId])) {
                            $ecoFee = $ecoFeesToRemove[$ecoFeeId];
                            unset($ecoFeesToRemove[$ecoFeeId]);
                        } else {
                            $ecoFee = EcoFeeFactory::changeModelType(EcoFeeFactory::getModelById($ecoFeeId), $typeRef);
                        }
                    } else {
                        $ecoFee = EcoFeeFactory::buildNewModel($typeRef);
                    }
                    $ecoFee->setFieldSuffix($seqNum);
                    if (!$ecoFee->handleFormSubmission($form, $this)) {
                        return false;
                    }
                }
            }
            if (!empty($ecoFeesToRemove)) {
                foreach ($ecoFeesToRemove as $ecoFeeToRemove) {
                    if (!$ecoFeeToRemove->softDelete()) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function validateEcoFeeForm(\GI_Form $form) {
        if (!$form->wasSubmitted()) {
            return false;
        }
        if (!$this->formValidated) {
            $ecoFees = $this->getEcoFees($form);
            if (!empty($ecoFees)) {
                foreach ($ecoFees as $ecoFee) {
                    if (!$ecoFee->validateForm($form)) {
                        $this->formValidated = false;
                        return $this->formValidated;
                    }
                }
            }
            if (!$form->validate()) {
                $this->formValidated = false;
            } else {
                $this->formValidated = true;
            }
        }
        return $this->formValidated;
    }

    public function getDefaultEcoFeeContact() {
        if (empty($this->ecoFeeContact)) {
            $this->ecoFeeContact = ContactFactory::getModelById($this->getProperty('eco_fee_contact_id'));
        }
        return $this->ecoFeeContact;
    }

    public function getDefaultTaxCodeName($type = 'sales') {
        $qbId = $this->getDefaultTaxCodeQBId($type);
        if (!empty($qbId)) {
            $options = QBTaxCodeFactory::getOptionsArray();
            if (!empty($options) && isset($options[$qbId])) {
                return $options[$qbId];
            }
        }
        return NULL;
    }

    public function getDefaultTaxCodeQBId($type = 'sales') {
        switch ($type) {
            case 'purchase':
                $model = $this->getDefaultTaxCodeQBPurchase();
                break;
            case 'sales':
            default:
                $model = $this->getDefaultTaxCodeQBSales();
                break;
        }
        if (!empty($model)) {
            return $model->getProperty('qb_id');
        }
        return NULL;
    }

    protected function getDefaultTaxCodeQBPurchase() {
        if (empty($this->defaultQBTaxCodePurchase)) {
            $search = RegionQBDefaultFactory::search();
            $search->filter('region_id', $this->getId())
                    ->filterByTypeRef('purchase');
            if (ProjectConfig::getIsFranchisedSystem()) {
                $search->ignoreFranchise('region_qb_default');
                QBConnection::addFranchiseFilterToDataSearch($search);
            }
            $results = $search->select();
            if (!empty($results)) {
                $this->defaultQBTaxCodePurchase = $results[0];
            }
        }
        return $this->defaultQBTaxCodePurchase;
    }
    
    protected function getDefaultTaxCodeQBSales() {
        if (empty($this->defaultQBTaxCodeSales)) {
            $search = RegionQBDefaultFactory::search();
            $search->filter('region_id', $this->getId())
                    ->filterByTypeRef('sales');
            if (ProjectConfig::getIsFranchisedSystem()) {
                $search->ignoreFranchise('region_qb_default');
                QBConnection::addFranchiseFilterToDataSearch($search);
            }
            $results = $search->select();
            if (!empty($results)) {
                $this->defaultQBTaxCodeSales = $results[0];
            }
        }
        return $this->defaultQBTaxCodeSales;
    }

    public function getDefaultSalesOrderLineProductQBId() {
        $model = $this->getDefaultSalesOrderLineProduct();
        if (!empty($model)) {
            return $model->getProperty('qb_id');
        }
        return NULL;
    }

    protected function getDefaultSalesOrderLineProduct() {
        if (empty($this->defaultQBSalesOrderLineProduct)) {
            $search = RegionQBDefaultFactory::search();
            $search->filter('region_id', $this->getId())
                    ->filterByTypeRef('product', false);
            if (ProjectConfig::getIsFranchisedSystem()) {
                $search->ignoreFranchise('region_qb_default');
                QBConnection::addFranchiseFilterToDataSearch($search);
            }
            $results = $search->select();
            if (!empty($results)) {
                $this->defaultQBSalesOrderLineProduct = $results[0];
            }
        }
        return $this->defaultQBSalesOrderLineProduct;
    }
    
    public function getDefaultSalesOrderLineACEcoFeeProductQBId() {
        $model = $this->getDefaultSalesOrderLineACEcoFeeProduct();
        if (!empty($model)) {
            return $model->getProperty('qb_id');
        }
        return NULL;
    }

    protected function getDefaultSalesOrderLineACEcoFeeProduct() {
        if (empty($this->defaultQBSalesOrderLineACEcoFeeProduct)) {
            $search = RegionQBDefaultFactory::search();
            $search->filter('region_id', $this->getId())
                    ->filterByTypeRef('sales_eco_fee', false);
            if (ProjectConfig::getIsFranchisedSystem()) {
                $search->ignoreFranchise('region_qb_default');
                QBConnection::addFranchiseFilterToDataSearch($search);
            }
            $results = $search->select();
            if (!empty($results)) {
                $this->defaultQBSalesOrderLineACEcoFeeProduct = $results[0];
            }
        }
        return $this->defaultQBSalesOrderLineACEcoFeeProduct;
    }
    
    public function handleQBSettingsFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $defaultSalesOrderLineProductId = filter_input(INPUT_POST, 'default_sales_order_line_product_qb_id_' . $this->getId());
            $defaultSalesOrderLineEcoFeeProductId = filter_input(INPUT_POST, 'default_sales_order_line_ac_eco_product_qb_id_' . $this->getId());

            $defaultSalesOrderLineProduct = $this->getDefaultSalesOrderLineProduct();
            if (!empty($defaultSalesOrderLineProductId)) {
                if (empty($defaultSalesOrderLineProduct)) {
                    $defaultSalesOrderLineProduct = RegionQBDefaultFactory::buildNewModel('product');
                    $defaultSalesOrderLineProduct->setProperty('region_id', $this->getId());
                }
                $defaultSalesOrderLineProduct->setProperty('qb_id', $defaultSalesOrderLineProductId);
                if (!$defaultSalesOrderLineProduct->save()) {
                    return false;
                }
            } else {
                if (!empty($defaultSalesOrderLineProduct) && !$defaultSalesOrderLineProduct->softDelete()) {
                    return false;
                }
            }

            $defaultSalesOrderLineEcoFeeProduct = $this->getDefaultSalesOrderLineACEcoFeeProduct();
            if (!empty($defaultSalesOrderLineEcoFeeProductId)) {
                if (empty($defaultSalesOrderLineEcoFeeProduct)) {
                    $defaultSalesOrderLineEcoFeeProduct = RegionQBDefaultFactory::buildNewModel('sales_eco_fee');
                    $defaultSalesOrderLineEcoFeeProduct->setProperty('region_id', $this->getId());
                }
                $defaultSalesOrderLineEcoFeeProduct->setProperty('qb_id', $defaultSalesOrderLineEcoFeeProductId);
                if (!$defaultSalesOrderLineEcoFeeProduct->save()) {
                    return false;
                }
            } else {
                if (!empty($defaultSalesOrderLineEcoFeeProduct) && !$defaultSalesOrderLineEcoFeeProduct->softDelete()) {
                    return false;
                }
            }
            
            return true;
        }
        return false;
    }

    public function getQBSettingDescription($key = 'invoice_line') {
        switch ($key) {
            case 'regional_main':
                return 'These settings are applied to transactions according to the region in which they take place. They will override any equivalent setting defined in either the General section, or on a specific Inventory Item.';
            case 'invoice_line':
                return 'Invoice lines created from Item Sales Order lines';
            case 'invoice_line_eco':
                return 'Invoice lines created from Additional Cost - Eco Fees Sales Order lines';
            default:
                return '';
        }
    }
    
    public function getIsEcoFeeIndexViewable() {
        if (!QBTaxCodeFactory::getTaxingUsesQBAst() && Permission::verifyByRef('view_eco_fees')) {
            return true;
        }
        return false;
    }
    
    public function getIsEcoFeesEditable() {
        if (!QBTaxCodeFactory::getTaxingUsesQBAst() && Permission::verifyByRef('edit_eco_fees')) {
            return true;
        }
        return false;
    }
    
    public function getIsQBSettingsEditable() {
        if (!QBTaxCodeFactory::getTaxingUsesQBAst() && Permission::verifyByRef('edit_qb_settings')) {
            return true;
        }
        return false;
    }
    
    public function getIsQBSettingsViewable() {
        if (!QBTaxCodeFactory::getTaxingUsesQBAst() && Permission::verifyByRef('view_qb_settings')) {
            return true;
        }
        return false;
    }
   

}
