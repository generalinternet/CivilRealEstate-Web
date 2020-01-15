<?php
/**
 * Description of AbstractEcoFee
 * 
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.1.1
 */
abstract class AbstractEcoFee extends GI_FormRowableModel {
    
    protected $region = NULL;
    protected $confirmedAppliesToInvItems = array();
    protected $confirmedDoesNotApplyToInvItems = array();
    
    public function getRegion() {
        if (empty($this->region)) {
            $this->region = RegionFactory::getModelById($this->getProperty('region_id'));
        }
        return $this->region;
    }

    public function getAppliesTo($includeForInvItem = true, $includeForInvItemType = true, $includeForInvContainerType = true ,$limit = NULL) {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return array();
        }
        $appliesToSearch = EcoFeeAppliesToFactory::search()
                ->filter('eco_fee_id', $this->getProperty('id'));

        if (!$includeForInvItem) {
            $appliesToSearch->filterNull('inv_item_id');
        }
        if (!$includeForInvItemType) {
            $appliesToSearch->filterNull('inv_item_type_ref');
        }
        if (!$includeForInvContainerType) {
            $appliesToSearch->filterNull('inv_container_type_ref');
        }
        if (!empty($limit)) {
            $appliesToSearch->setItemsPerPage($limit);
        }
        return $appliesToSearch->select();
    }
    
    public function getFormView(GI_Form $form) {
        return new EcoFeeFormView($form, $this);
    }

    public function getDetailView() {
        return new EcoFeeDetailView($this);
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Name',
                'method_name' => 'getName',
            ),
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle',
            ),
            array(
                'header_title' => 'Rate',
                'method_name' => 'getRateString'
            ),
            array(
                'header_title' => 'Minimum Threshold',
                'method_name' => 'getMinimumThresholdString'
            ),
            array(
                'header_title' => 'Maximum Threshold',
                'method_name' => 'getMaximumThresholdString'
            ),
            array(
                'header_title' => 'Applies To',
                'method_name' => 'getAppliesToString'
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getName() {
        return $this->getProperty('name');
    }
    
    public function getRateString() {
        $rateUnit = PricingUnitFactory::getModelById($this->getProperty('rate_unit'));
        if (empty($rateUnit)) {
            return '';
        }
        $rate = $this->getProperty('rate_per_unit');
        $rateUnitTitle = $rateUnit->getProperty('title');
        return '$' . $rate . '/' . $rateUnitTitle;
    }
    
    public function getMinimumThresholdString() {
        $minUnit = PricingUnitFactory::getModelById($this->getProperty('min_unit'));
        if (empty($minUnit)) {
            return '--';
        }
        $qty = $this->getProperty('min_qty');
        return $qty . ' ' . $minUnit->getProperty('pl_title');
    }
    
    public function getMaximumThresholdString() {
        $maxUnit = PricingUnitFactory::getModelById($this->getProperty('max_unit'));
        if (empty($maxUnit)) {
            return '--';
        }
        $qty = $this->getProperty('max_qty');
        return $qty . ' ' . $maxUnit->getProperty('pl_title');
    }
    
    public function getAppliesToString() {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return '';
        }
        $appliesToArray = $this->getAppliesTo();
        if (empty($appliesToArray)) {
            return '--';
        }
        $appliesToNameArray = array();
        foreach ($appliesToArray as $appliesTo) {
            $appliesToNameArray[] = $appliesTo->getTargetName();
        }
        $appliesToString = implode(', ', $appliesToNameArray);
        return $appliesToString;
    }
    
    /**
     * @param AbstractInvItem $invItem
     * @return float The rate of the eco fee, in the same pricing units as $invItem's base package config, or NULL if the rate pricing unit
     * for this eco fee is incompatible with the pricing unit of $invItem's base package config.
     */
    public function getConvertedRateForInvItem(AbstractInvItem $invItem) {
        $convertedQty = $this->getConvertedSingleUnitQtyForInvItem($invItem);
        if (empty($convertedQty)) {
            return NULL;
        }
        $rate = (float) $this->getProperty('rate_per_unit');
        return $convertedQty * $rate;
    }

    /**
     * 
     * @param AbstractInvItem $invItem
     * @return float The number of units, in the pricing unit of $invItem's base pack config, that equals a single unit of this eco fee's pricing unit,
     * or NULL, if the pricing units are not compatible.
     */
    public function getConvertedSingleUnitQtyForInvItem(AbstractInvItem $invItem) {
        if (!$this->doesEcoFeeApplyToInvItem($invItem)) {
            return 0;
        }
        $basePackConfig = $invItem->getBasePackConfig();
        if (empty($basePackConfig)) {
            return NULL;
        }
        $basePackConfigPricingUnit = PricingUnitFactory::getModelById($basePackConfig->getProperty('inv_pack_config_base.pricing_unit_id'));
        $ecoFeePricingUnit = PricingUnitFactory::getModelById($this->getProperty('rate_unit'));
        if (empty($basePackConfigPricingUnit) || empty($ecoFeePricingUnit)) {
            return NULL;
        }
       return $ecoFeePricingUnit->convertQtyToThis(1, $basePackConfigPricingUnit);
    }

    public function doesEcoFeeApplyToInvItemByThreshold(AbstractInvItem $invItem) {
        $invItemId = $invItem->getProperty('id');
        if (isset($this->confirmedAppliesToInvItems[$invItemId])) {
            return true;
        } else if (isset($this->confirmedDoesNotApplyToInvItems[$invItemId])) {
            return false;
        }
        $ecoFeeDeterminant = $invItem->getProperty('eco_fee_determinant');
        $ecoFeeDeterminantPricingUnit = $invItem->getEcoFeeDeterminantUnit();
        $minQty = $this->getProperty('min_qty');
        $minUnit = PricingUnitFactory::getModelById($this->getProperty('min_unit'));
        $maxQty = $this->getProperty('max_qty');
        $maxUnit = PricingUnitFactory::getModelById($this->getProperty('max_unit'));

        if (!empty($minQty) && !empty($maxQty)) {
            if (!(GI_Measurement::isQtyGreaterThan($ecoFeeDeterminant, $ecoFeeDeterminantPricingUnit, $minQty, $minUnit, true) && GI_Measurement::isQtyLessThan($ecoFeeDeterminant, $ecoFeeDeterminantPricingUnit, $maxQty, $maxUnit, true))) {
                return false;
            }
        } else if (!empty($minQty) && empty($maxQty)) {
            if (!GI_Measurement::isQtyGreaterThan($ecoFeeDeterminant, $ecoFeeDeterminantPricingUnit, $minQty, $minUnit, true)) {
                return false;
            }
        } else if (empty($minQty) && !empty($maxQty)) {
            if (!GI_Measurement::isQtyLessThan($ecoFeeDeterminant, $ecoFeeDeterminantPricingUnit, $maxQty, $maxUnit, true)) {
                return false;
            }
        }
        return true;
    }
    
    public function doesEcoFeeApplyToInvItem(AbstractInvItem $invItem, $searchAppliesToLinks = true) {
        $invItemId = $invItem->getProperty('id');
        if (isset($this->confirmedAppliesToInvItems[$invItemId])) {
            return true;
        } else if (isset($this->confirmedDoesNotApplyToInvItems[$invItemId])) {
            return false;
        }
        $applies = $this->doesEcoFeeApplyToInvItemByThreshold($invItem);
        if ($applies && $searchAppliesToLinks) {
            $invContainerTypeRef = 'item';
            $basePackConfig = $invItem->getBasePackConfig();
            if (!empty($basePackConfig)) {
                $invContainerTypeRef = $basePackConfig->getProperty('inv_container_type_ref');
            }
            $appliesToSearch = EcoFeeAppliesToFactory::search()
                    ->filter('eco_fee_id', $this->getProperty('id'))
                    ->andIf()
                    ->filterGroup()
                    ->filterGroup()
                    ->filterNULL('inv_item_type_ref')
                    ->filterNULL('inv_container_type_ref')
                    ->filter('inv_item_id', $invItemId)
                    ->closeGroup()
                    ->orIf()
                    ->filterGroup()
                    ->andIf()
                    ->filterNULL('inv_item_id')
                    ->filterNULL('inv_container_type_ref');
                    
            $typeRefs = InvItemFactory::getTypeRefArrayFromTypeRef($invItem->getTypeRef());
            if (empty($typeRefs)) {
                $typeRefs = array($invItem->getTypeRef());
            }
            $appliesToSearch->filterGroup();
            foreach ($typeRefs as $typeRef) {
                $appliesToSearch->filter('inv_item_type_ref', $typeRef)
                                ->orIf();
            }
            $appliesToSearch->closeGroup()
                    ->closeGroup();
            $appliesToSearch->orIf()
                    ->filterGroup();
            $appliesToSearch->andIf()
                    ->filterNull('inv_item_type_ref')
                    ->filterNull('inv_item_id')
                    ->filterGroup();
            $containerTypeRefs = InvContainerFactory::getTypeRefArrayFromTypeRef($invContainerTypeRef);
            if (empty($containerTypeRefs)) {
                $containerTypeRefs = array($invContainerTypeRef);
            }
            foreach ($containerTypeRefs as $containerTypeRef) {
                                $appliesToSearch->filter('inv_container_type_ref', $containerTypeRef)
                                ->orIf();
            }
            $appliesToSearch->closeGroup()
                    ->andIf();
            $appliesToSearch->closeGroup();
                    $appliesToSearch->closeGroup()
                    ->andIf();
            $appliesToArray = $appliesToSearch->select();
            if (empty($appliesToArray)) {
                $applies = false;
            }
        }
        if ($applies) {
            $this->confirmedAppliesToInvItems[$invItemId] = $invItem;
            return true;
        } else {
            $this->confirmedDoesNotApplyToInvItems[$invItemId] = $invItem;
            return false;
        }
    }

    public function handleFormSubmission(GI_Form $form, AbstractRegion $region) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            if (!$this->setPropertiesFromForm($form)) {
                return false;
            }
            $this->setProperty('region_id', $region->getProperty('id'));
            if (!$this->save()) {
                return false;
            }
            if (!$this->updateAppliesTo($form)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function validateForm(\GI_Form $form) {
        if (!$form->wasSubmitted()) {
            return false;
        }
        if (!($this->validateThresholdFields($form) && $this->validateAppliesToFields($form))) {
            $this->formValidated = false;
            return $this->formValidated;
        }
        if (!$this->formValidated) {
            $this->formValidated = $form->validate();
        }
        return $this->formValidated;
    }
    
    protected function validateThresholdFields(GI_Form $form) {
        if (!$form->wasSubmitted()) {
            return false;
        }
        $minQty = filter_input(INPUT_POST, $this->getFieldName('min_qty'));
        $minUnitId = filter_input(INPUT_POST, $this->getFieldName('min_unit'));
        if ($minUnitId === 'NULL') {
            $minUnitId = NULL;
        }
        $maxQty = filter_input(INPUT_POST, $this->getFieldName('max_qty'));
        $maxUnitId = filter_input(INPUT_POST, $this->getFieldName('max_unit'));
        if ($maxUnitId === 'NULL') {
            $maxUnitId = NULL;
        }
        if (!empty($minQty) && empty($minUnitId)) {
            $form->addFieldError($this->getFieldName('min_unit'), 'No Unit', 'You must also select a unit.');
            return false;
        } else if (empty($minQty) && !empty($minUnitId)) {
            $form->addFieldError($this->getFieldName('min_qty'), 'No Qty', 'You must also select a quantity.');
            return false;
        }
        if (!empty($maxQty) && empty($maxUnitId)) {
            $form->addFieldError($this->getFieldName('max_unit'), 'No Unit', 'You must also select a unit.');
            return false;
        } else if (empty($maxQty) && !empty($maxUnitId)) {
            $form->addFieldError($this->getFieldName('max_qty'), 'No Qty', 'You must also select a quantity.');
            return false;
        }
        return true;
    }

    protected function validateAppliesToFields(GI_Form $form) {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return true;
        }
        $appliesToSelectorFieldName = $this->getFieldName('applies_to_selector');
        $appliesToSelector = filter_input(INPUT_POST, $appliesToSelectorFieldName);
        if (empty($appliesToSelector)) {
            $form->addFieldError($appliesToSelectorFieldName, 'required field', 'this field is required.');
            return false;
        }
        if ($appliesToSelector == 'type') {
            $appliesToInvItemTypeFieldName = $this->getFieldName('applies_to_inv_item_type');
            $appliesToInvItemTypeRefsArray = filter_input(INPUT_POST, $appliesToInvItemTypeFieldName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (empty($appliesToInvItemTypeRefsArray)) {
                $form->addFieldError($appliesToInvItemTypeFieldName, 'no_selected', 'you must select at least one type of item.');
                return false;
            }
        } else if ($appliesToSelector == 'container_type') {
            $appliesToInvContainerTypeFieldName = $this->getFieldName('applies_to_inv_container_type');
            $appliesToInvContainerTypeRefsArray = filter_input(INPUT_POST, $appliesToInvContainerTypeFieldName, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (empty($appliesToInvContainerTypeRefsArray)) {
                $form->addFieldError($appliesToInvContainerTypeFieldName, 'no_selected', 'you must select at least one type of container.');
            }
        } else {
            $appliesToInvItemsFieldName = $this->getFieldName('applies_to_inv_item_name');
            $appliesToInvItemIdString = filter_input(INPUT_POST, $appliesToInvItemsFieldName);
            if (empty($appliesToInvItemIdString)) {
                $form->addFieldError($appliesToInvItemsFieldName, 'no_selected', 'you must select at least one item.');
                return false;
            }
        }
        return true;
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $name = filter_input(INPUT_POST, $this->getFieldName('name'));
            $ratePerUnit = filter_input(INPUT_POST, $this->getFieldName('rate_per_unit'));
            $rateUnitId = filter_input(INPUT_POST, $this->getFieldName('rate_unit'));
            $minQty = filter_input(INPUT_POST, $this->getFieldName('min_qty'));
            $minUnitId = filter_input(INPUT_POST, $this->getFieldName('min_unit'));
            $maxQty = filter_input(INPUT_POST, $this->getFieldName('max_qty'));
            $maxUnitId = filter_input(INPUT_POST, $this->getFieldName('max_unit'));
            
            $this->setProperty('name', $name);
            $this->setProperty('rate_per_unit', $ratePerUnit);
            $this->setProperty('rate_unit', $rateUnitId);
            if (!empty($minQty)) {
                $this->setProperty('min_qty', $minQty);
            } else {
                $this->setProperty('min_qty', NULL);
            }
            if (!empty($minUnitId)) {
                $this->setProperty('min_unit', $minUnitId);
            } else {
                $this->setProperty('min_unit', NULL);
            }
            if (!empty($maxQty)) {
                $this->setProperty('max_qty', $maxQty);
            } else {
                $this->setProperty('max_qty', NULL);
            }
            if (!empty($maxUnitId)) {
                $this->setProperty('max_unit', $maxUnitId);
            } else {
                $this->setProperty('max_unit', NULL);
            }
            return true;
        }
        return false;
    }

    protected function updateAppliesTo(GI_Form $form) {
        if (!dbConnection::isModuleInstalled('inventory')) {
            return true;
        }
        $appliesToSelector = filter_input(INPUT_POST, $this->getFieldName('applies_to_selector'));
        if (empty($appliesToSelector)) {
            return false;
        }
        if ($appliesToSelector == 'type') {
            if (!$this->updateAppliesToInvItemType($form)) {
                return false;
            }
//            $appliesToByNameArray = $this->getAppliesTo(true, false, false);
//            if (!empty($appliesToByNameArray)) {
//                foreach ($appliesToByNameArray as $appliesToByName) {
//                    if (!$appliesToByName->softDelete()) {
//                        return false;
//                    }
//                }
//            }
            //TODO - new helper function to remove the applies to except for the selected kind
        } else if ($appliesToSelector == 'container_type') {
            if (!$this->updateAppliesToInvContainerType($form)) {
                return false;
            }
            
        } else {
            if (!$this->updateAppliesToInvItem($form)) {
                return false;
            }
//            $appliesToByTypeArray = $this->getAppliesTo(false, true, false);
//            if (!empty($appliesToByTypeArray)) {
//                foreach ($appliesToByTypeArray as $appliesToByType) {
//                    if (!$appliesToByType->softDelete()) {
//                        return false;
//                    }
//                }
//            }
        }
        if (!$this->cleanupAppliesTo($appliesToSelector)) {
            return false;
        }
        return true;
    }
    
    protected function cleanupAppliesTo($appliesToSelector) {
        $item = true;
        $type = true;
        $container = true;
        
        if ($appliesToSelector == 'type') {
            $type = false;
        } else if ($appliesToSelector == 'container_type') {
            $container = false;
        } else {
            $item = false;
        }
        
        $appliesToArray = $this->getAppliesTo($item, $type, $container);
        if (!empty($appliesToArray)) {
            foreach ($appliesToArray as $appliesTo) {
                if (!$appliesTo->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function updateAppliesToInvItemType(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $existingAppliesToArray = $this->getAppliesTo(false, true, false);
            $appliesToToRemove = array();
            foreach ($existingAppliesToArray as $existingAppliesTo) {
                $invItemTypeRef = $existingAppliesTo->getProperty('inv_item_type_ref');
                $appliesToToRemove[$invItemTypeRef] = $existingAppliesTo;
            }
            $submittedAppliesToInvItemRefs = filter_input(INPUT_POST, $this->getFieldName('applies_to_inv_item_type'), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            foreach ($submittedAppliesToInvItemRefs as $submittedInvItemRef) {
                if (isset($appliesToToRemove[$submittedInvItemRef])) {
                    unset($appliesToToRemove[$submittedInvItemRef]);
                } else {
                    $softDeletedSearch = EcoFeeAppliesToFactory::search()
                            ->setAutoStatus(false)
                            ->filter('status', 0)
                            ->filter('eco_fee_id', $this->getProperty('id'))
                            ->filter('inv_item_type_ref', $submittedInvItemRef);
                    $softDeletedArray = $softDeletedSearch->select();
                    if (!empty($softDeletedArray) && $softDeletedArray[0]->unsoftDelete()) {
                        $appliesTo = $softDeletedArray[0];
                    } else {
                        $appliesTo = EcoFeeAppliesToFactory::buildNewModel();
                        $appliesTo->setProperty('eco_fee_id', $this->getProperty('id'));
                        $appliesTo->setProperty('inv_item_type_ref', $submittedInvItemRef);
                        if (!$appliesTo->save()) {
                            return false;
                        }
                    }
                }
            }
            foreach ($appliesToToRemove as $appliesToToRemove) {
                if (!$appliesToToRemove->softDelete()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    
    protected function updateAppliesToInvContainerType(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $existingAppliesToArray = $this->getAppliesTo(false, false, true);
            $appliesToToRemove = array();
            foreach ($existingAppliesToArray as $existingAppliesTo) {
                $invContainerTypeRef = $existingAppliesTo->getProperty('inv_container_type_ref');
                $appliesToToRemove[$invContainerTypeRef] = $existingAppliesTo;
            }
            $submittedAppliesToInvContainerTypeRefs = filter_input(INPUT_POST, $this->getFieldName('applies_to_inv_container_type'), FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            foreach ($submittedAppliesToInvContainerTypeRefs as $submittedInvContainerRef) {
                if (isset($appliesToToRemove[$submittedInvContainerRef])) {
                    unset($appliesToToRemove[$submittedInvContainerRef]);
                } else {
                    $softDeletedSearch = EcoFeeAppliesToFactory::search()
                            ->setAutoStatus(false)
                            ->filter('status', 0)
                            ->filter('eco_fee_id', $this->getProperty('id'))
                            ->filter('inv_container_type_ref', $submittedInvContainerRef);
                    $softDeletedArray = $softDeletedSearch->select();
                    if (!empty($softDeletedArray) && $softDeletedArray[0]->unsoftDelete()) {
                        $appliesTo = $softDeletedArray[0];
                    } else {
                        $appliesTo = EcoFeeAppliesToFactory::buildNewModel();
                        $appliesTo->setProperty('eco_fee_id', $this->getProperty('id'));
                        $appliesTo->setProperty('inv_container_type_ref', $submittedInvContainerRef);
                        if (!$appliesTo->save()) {
                            return false;
                        }
                    }
                }
            }
            foreach ($appliesToToRemove as $appliesToToRemove) {
                if (!$appliesToToRemove->softDelete()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateAppliesToInvItem(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $existingAppliesToArray = $this->getAppliesTo(true, false, false);
            $appliesToToRemove = array();
            if (!empty($existingAppliesToArray)) {
                foreach ($existingAppliesToArray as $existingAppliesTo) {
                    $appliesToToRemove[$existingAppliesTo->getProperty('inv_item_id')] = $existingAppliesTo;
                }
            }
            $submittedInvItemIdsString = filter_input(INPUT_POST, $this->getFieldName('applies_to_inv_item_name'));
            if (!empty($submittedInvItemIdsString)) {
                $submittedInvItemIds = explode(',', $submittedInvItemIdsString);
                foreach ($submittedInvItemIds as $submittedInvItemId) {
                    if (isset($appliesToToRemove[$submittedInvItemId])) {
                        unset($appliesToToRemove[$submittedInvItemId]);
                    } else {
                        $softDeletedSearch = EcoFeeAppliesToFactory::search()
                                ->setAutoStatus(false)
                                ->filter('status', 0)
                                ->filter('eco_fee_id', $this->getProperty('id'))
                                ->filter('inv_item_id', $submittedInvItemId);
                        $softDeletedArray = $softDeletedSearch->select();
                        if (!empty($softDeletedArray && $softDeletedArray[0]->unsoftDelete())) {
                            $appliesTo = $softDeletedArray[0];
                        } else {
                            $appliesTo = EcoFeeAppliesToFactory::buildNewModel();
                            $appliesTo->setProperty('eco_fee_id', $this->getProperty('id'));
                            $appliesTo->setProperty('inv_item_id', $submittedInvItemId);
                            if (!$appliesTo->save()) {
                                return false;
                            }
                        }
                    }
                }
            }
            foreach ($appliesToToRemove as $appliesToToRemove) {
                if (!$appliesToToRemove->softDelete()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    public function getAppliesToInvItemTypeString() {
        $appliesToArray = $this->getAppliesTo(false, true, false);
        $typeRefString = '';
        if (!empty($appliesToArray)) {
            $tempArray = array();
            foreach ($appliesToArray as $appliesTo) {
                $tempArray[] = $appliesTo->getProperty('inv_item_type_ref');
            }
            $typeRefString = implode(',', $tempArray);
        }
        return $typeRefString;
    }
    
    public function getAppliesToInvItemTypeRefArray() {
        $appliesToArray = $this->getAppliesTo(false, true, false);
        $typeRefs = array();
        if (!empty($appliesToArray)) {
            foreach ($appliesToArray as $appliesTo) {
                $typeRefs[] = $appliesTo->getProperty('inv_item_type_ref');
            }
        }
        return $typeRefs;
    }

    public function getAppliesToInvContainerTypeRefArray() {
        $appliesToArray = $this->getAppliesTo(false, false, true);
        $typeRefs = array();
        if (!empty($appliesToArray)) {
            foreach ($appliesToArray as $appliesTo) {
                $typeRefs[] = $appliesTo->getProperty('inv_container_type_ref');
            }
        }
        return $typeRefs;
    }

    public function getAppliesToInvItemIdString() {
        $appliesToArray = $this->getAppliesTo(true, false, false);
        $idString = '';
        if (!empty($appliesToArray)) {
            $tempArray = array();
            foreach ($appliesToArray as $appliesTo) {
                $tempArray[] = $appliesTo->getProperty('inv_item_id');
            }
            $idString = implode(',', $tempArray);
        }
        return $idString;
    }
    
    public function softDelete() {
        $appliesToArray = $this->getAppliesTo();
        if (!empty($appliesToArray)) {
            foreach ($appliesToArray as $appliesTo) {
                if (!$appliesTo->softDelete()) {
                    return false;
                }
            }
        }
        return parent::softDelete();
    }
}