<?php
/**
 * Description of AbstractIncomeItemFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractIncomeItemFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'income_item';
    protected static $models = array();
    protected static $optionsArray = array();
    /**
     * @param string $typeRef
     * @return AbstractIncomeItem
     */
    public static function buildNewModel($typeRef = '', $inProgress = false) {
        $model = parent::buildNewModel($typeRef);
        if ($inProgress) {
            $model->setProperty('in_progress', 1);
        }
        return $model;
    }

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'income':
            case 'sales':
            case 'sales_item':
            case 'refund':
            case 'sales_ac':
            case 'sales_ac_shipping':
            case 'sales_ac_other':
            case 'sales_ac_eco':
            case 'eco_can_ab':
            case 'eco_can_bc':
            case 'eco_can_mb':
            case 'eco_can_nb':
            case 'eco_can_nl':
            case 'eco_can_ns':
            case 'eco_can_nt':
            case 'eco_can_nu':
            case 'eco_can_on':
            case 'eco_can_pe':
            case 'eco_can_qc':
            case 'eco_can_sk':
            case 'eco_can_yt':
            default:
                $model = new IncomeItem($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'income':
                $typeRefs = array('income');
                break;
            case 'sales':
                $typeRefs = array('sales', 'sales');
                break;
            case 'sales_item':
                $typeRefs = array('sales', 'sales_item', 'sales_item');
                break;
            case 'refund':
                $typeRefs = array('sales', 'refund');
                break;
            case 'sales_ac':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac');
                break;
            case 'sales_ac_shipping':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_shipping');
                break;
            case 'sales_ac_other':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_other');
                break;
            case 'sales_ac_eco':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'sales_ac_eco');
                break;
            case 'eco_can_ab':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_ab');
                break;
            case 'eco_can_bc':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_bc');
                break;
            case 'eco_can_mb':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_mb');
                break;
            case 'eco_can_nb':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_nb');
                break;
            case 'eco_can_nl':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_nl');
                break;
            case 'eco_can_ns':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_ns');
                break;
            case 'eco_can_nt':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_nt');
                break;
            case 'eco_can_nu':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_nu');
                break;
            case 'eco_can_on':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_on');
                break;
            case 'eco_can_pe':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_pe');
                break;
            case 'eco_can_qc':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_qc');
                break;
            case 'eco_can_sk':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_sk');
                break;
            case 'eco_can_yt':
                $typeRefs = array('sales', 'sales_ac', 'sales_ac_eco', 'eco_can_yt');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @deprecated This approach should not longer be required
     * @param type $typeRef
     * @return string
     */
    public static function getPTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $numberOfRefs = sizeof($typeRefsArray);
        if ($numberOfRefs > 2) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            return $pTypeRef;
        } else {
            $pTypeRef = 'income';
        }
        return $pTypeRef;
    }

    protected static function getIncomeItemDataSearchByLinkedModel(GI_Model $model, AbstractIncome $income = NULL, $includeVoidedItems = false, $includeCancelledItems = false) {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeItemTableName = dbConfig::getDbPrefix() . 'income_item';
        $incomeItemSearch = IncomeItemFactory::search()
                ->join('item_link_to_income_item', 'income_item_id', $incomeItemTableName, 'id', 'iltii')
                ->filter('iltii.table_name', $modelTableName)
                ->filter('iltii.item_id', $modelId);
        if (!empty($income)) {
            $incomeId = $income->getProperty('id');
            $incomeItemSearch->filter('income_id', $incomeId);
        }
        if (!$includeVoidedItems) {
            $incomeItemSearch->filter('void', 0);
        }
        if (!$includeCancelledItems) {
            $incomeItemSearch->filter('cancelled', 0);
        }
        return $incomeItemSearch;
    }

    public static function getIncomeItemsByLinkedModel(GI_Model $model, AbstractIncome $income = NULL, $includeVoidedItems = false, $includeCancelledItems = false) {
        $incomeItemSearch = static::getIncomeItemDataSearchByLinkedModel($model, $income, $includeVoidedItems, $includeCancelledItems);
        $existingIncomeItems = $incomeItemSearch->select();
        return $existingIncomeItems;
    }
    
    public static function getIncomeItemsCountByLinkedModel(GI_Model $model, AbstractIncome $income = NULL, $includeVoidedItems = false) {
        $incomeItemSearch = static::getIncomeItemDataSearchByLinkedModel($model, $income, $includeVoidedItems);
        return $incomeItemSearch->count();
    }
        
    /**
     * Adjusts the income items and links related to a model
     * @param GI_Model $model
     * @param AbstractIncome $income
     * @param int $targetQty
     * @param decimal $totalNet
     * @param string $newTypeRef
     * @return AbstractIncomeItem[]
     */
    public static function adjustIncomeItemsByLinkedModel(GI_Model $model, AbstractIncome $income, $targetQty = 1, $totalNet = 0, $newTypeRef = 'income', $inProgress = false) {
        if ($targetQty == 0) {
            $result = IncomeItemFactory::removeIncomeItemsLinkedToModel($model, $income);
            if ($result) {
                return array();
            } else {
                return NULL;
            }
        }
        
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $taxCodeQBId = $model->getTaxCodeQBId();
        $existingIncomeItems = array();
        $preExistingIncomeItems = static::getIncomeItemsByLinkedModel($model, $income);
        if (!empty($preExistingIncomeItems)) {
            if ($preExistingIncomeItems[0]->getTypeRef() != $newTypeRef) {
                foreach ($preExistingIncomeItems as $preExistingItem) {
                    if ($preExistingItem->getTypeRef() != $newTypeRef) {
                        $existingIncomeItems[] = IncomeItemFactory::changeModelType($preExistingItem, $newTypeRef);
                    } else {
                        $existingIncomeItems[] = $preExistingItem;
                    }
                }
            } else {
                $existingIncomeItems = $preExistingIncomeItems;
            }
        }
        
        $amounts = array();
        GI_Math::divideMoneyWithoutLoss($totalNet, $targetQty, $amounts);
        
        $incomeItems = array();
        if (empty($existingIncomeItems)) { //CASE 1 - there aren't any, so we have to create all of them
            for ($i = 0; $i < $targetQty; $i++) {
                $specificAmount = $amounts[$i];
                $incomeItem = IncomeItemFactory::buildNewModel($newTypeRef, $inProgress);
                if (!empty($taxCodeQBId)) {
                    $incomeItem->setProperty('tax_code_qb_id', $taxCodeQBId);
                }
                $incomeItems[] = static::linkSingleIncomeItemToModel($model, $income, $incomeItem, $specificAmount);
            }
            return $incomeItems;
        } else { //CASE 2 - there are some, so we have to check them
            $numberOfExistingItems = count($existingIncomeItems);
            //2A - There are the same number as target
            if ($numberOfExistingItems == $targetQty) {
                $incomeItems = $existingIncomeItems;
                //2B - There is a different number than the target, so we have to adjust what is there
            } else if ($numberOfExistingItems > $targetQty) { //2Bi - There are too many, so some need to be removed
                for ($i = 0; $i < $targetQty; $i++) {
                    $currentItem = $existingIncomeItems[$i];
                    $incomeItems[] = $currentItem;
                    unset($existingIncomeItems[$i]);
                }
                //remove the rest
                foreach ($existingIncomeItems as $key => $itemToDelete) {
                    $itemToDeleteId = $itemToDelete->getProperty('id');
                    $itemLinkToIncomeItemToDeleteDAOArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                        'table_name' => $modelTableName,
                        'item_id' => $modelId,
                        'income_item_id' => $itemToDeleteId
                    ));
                    if (empty($itemLinkToIncomeItemToDeleteDAOArray)) {
                        return NULL;
                    }
                    $itemToDeleteLinkDAO = $itemLinkToIncomeItemToDeleteDAOArray[0];
                    if (!$itemToDeleteLinkDAO->softDelete() || !$itemToDelete->softDelete()) {
                        return NULL;
                    }
                    unset($existingIncomeItems[$key]);
                }
            } else { //2Bii - There aren't enough, so we need to add some
                foreach ($existingIncomeItems as $existingIncomeItem) {
                    $incomeItems[] = $existingIncomeItem;
                }
                $difference = $targetQty - $numberOfExistingItems;
                $softDeletedIncomeItemArray = static::getSoftDeletedIncomeItemsByLinkedModel($model);
                for ($j = 0; $j < $difference; $j++) {
                    $specificAmount = $amounts[$j];
                    $incomeItem = NULL;
                    if (!empty($softDeletedIncomeItemArray) && isset($softDeletedIncomeItemArray[$j])) {
                        $softDeletedIncomeItem = $softDeletedIncomeItemArray[$j];
                        $softDeletedIncomeItem->setProperty('status', 1);
                        if ($softDeletedIncomeItem->save()) {
                            $incomeItem = $softDeletedIncomeItem;
                            unset($softDeletedIncomeItemArray[$j]);
                        }
                    }
                    if (empty($incomeItem)) {
                        $incomeItem = IncomeItemFactory::buildNewModel($newTypeRef, $inProgress);
                    }
                    $incomeItemId = $incomeItem->getProperty('id');
                    $newLink = NULL;
                    if (!empty($incomeItemId)) {
                        $softDeletedLinkArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                            'table_name' => $modelTableName,
                            'item_id' => $modelId,
                            'income_item_id' => $incomeItemId,
                                        ), 'client', 0);

                        if ($softDeletedLinkArray) {
                            $softDeletedLink = $softDeletedLinkArray[0];
                            $softDeletedLink->setProperty('status', 1);
                            if ($softDeletedLink->save()) {
                                $newLink = $softDeletedLink;
                            }
                        }
                    }
                    $incomeItems[] = static::linkSingleIncomeItemToModel($model, $income, $incomeItem, $specificAmount, $newLink);
                }
            }
            //Adjust the net_amount of each
            $incomeItemCount = count($incomeItems);
            for ($k = 0; $k < $incomeItemCount; $k++) {
                $iItem = $incomeItems[$k];
                $specificAmount = $amounts[$k];
                $iItem->setProperty('net_amount', $specificAmount);
                if ($inProgress) {
                    $iItem->setProperty('in_progress', 1);
                } else {
                    $iItem->setProperty('in_progress', 0);
                }
                if (!empty($taxCodeQBId)) {
                    $iItem->setProperty('tax_code_qb_id', $taxCodeQBId);
                }
                if (!$iItem->save()) {
                    return NULL;
                }
            }
            return $incomeItems;
        }
    }

    protected static function linkSingleIncomeItemToModel(GI_Model $model, AbstractIncome $income, AbstractIncomeItem $incomeItem, $netAmount, $linkDAO = NULL) {
        if (empty($linkDAO)) {
            $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
            $linkDAO = new $defaultDAOClass('item_link_to_income_item');
            if (empty($linkDAO)) {
                return NULL;
            }
        }
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeId = $income->getProperty('id');
        $incomeItem->setProperty('income_id', $incomeId);
        $incomeItem->setProperty('net_amount', $netAmount);
        if (!$incomeItem->save()) {
            return NULL;
        }
        $incomeItemId = $incomeItem->getProperty('id');
        $linkDAO->setProperty('table_name', $modelTableName);
        $linkDAO->setProperty('item_id', $modelId);
        $linkDAO->setProperty('income_item_id', $incomeItemId);
        if (!$linkDAO->save()) {
            return NULL;
        }
        return $incomeItem;
    }

    public static function linkIncomeItemToModel(GI_Model $model, AbstractIncomeItem $incomeItem) {
        $modelId = $model->getProperty('id');
        $incomeItemId = $incomeItem->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $search = new GI_DataSearch('item_link_to_income_item');
        $search->filter('table_name', $modelTableName)
                ->filter('item_id', $modelId)
                ->filter('income_item_id', $incomeItemId)
                ->setAutoStatus(false);
        $linkArray = $search->select();
        $buildNewLink = false;
        if (!empty($linkArray)) {
            $link = $linkArray[0];
            if (empty($link->getProperty('status'))) {
                $link->setProperty('status', 1);
                if (!$link->save()) {
                    $buildNewLink = true;
                }
            }
        } else {
            $buildNewLink = true;
        }
        if ($buildNewLink) {
            $newLink = new $defaultDAOClass('item_link_to_income_item');
            $newLink->setProperty('table_name', $modelTableName);
            $newLink->setProperty('item_id', $modelId);
            $newLink->setProperty('income_item_id', $incomeItemId);
            if (!$newLink->save()) {
                return false;
            }
        }
        return true;
    }

    public static function unlinkIncomeItemFromModel(GI_Model $model, AbstractIncomeItem $incomeItem) {
        $modelId = $model->getProperty('id');
        $incomeItemId = $incomeItem->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        //TODO - use DataSearch
        $linkArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                    'table_name' => $modelTableName,
                    'item_id' => $modelId,
                    'income_item_id' => $incomeItemId
        ));
        if (empty($linkArray)) {
            return true;
        }
        foreach ($linkArray as $linkDAO) {
            if (!$linkDAO->softDelete()) {
                return false;
            }
        }
        return true;
    }

    public static function getNumberOfModelsLinkedToIncomeItem(AbstractIncomeItem $incomeItem) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $incomeItemId = $incomeItem->getProperty('id');
        $linkDAOArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                    'income_item_id' => $incomeItemId
        ));
        if (empty($linkDAOArray)) {
            return 0;
        }
        return sizeof($linkDAOArray);
    }

    protected static function getSoftDeletedIncomeItemsByLinkedModel(GI_Model $model, AbstractIncome $income = NULL) {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeItemTableName = dbConfig::getDbPrefix() . 'income_item';
        $incomeItemSearch = IncomeItemFactory::search()
                ->filter('status', 0)
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->join('item_link_to_income_item', 'income_item_id', $incomeItemTableName, 'id', 'iltii')
                ->filter('iltii.table_name', $modelTableName)
                ->filter('iltii.item_id', $modelId)
                ->filter('iltii.status', 0);
        if (!empty($income)) {
            $incomeId = $income->getProperty('id');
            $incomeItemSearch->filter('income_id', $incomeId);
        }
        $softDeletedIncomeItemArray = $incomeItemSearch->select();
        return $softDeletedIncomeItemArray;
    }

    public static function linkNIncomeItemsToNModelsFromModel(GI_Model $sourceModel, $targetModels, AbstractIncome $income) {
        $incomeItems = IncomeItemFactory::getIncomeItemsByLinkedModel($sourceModel, $income);
        $numberOfIncomeItems = (int) sizeof($incomeItems);
        $numberOfTargetModels = (int) sizeof($targetModels);
        if ($numberOfIncomeItems != $numberOfTargetModels) {
            return false;
        }
        for ($i = 0; $i < $numberOfTargetModels; $i++) {
            $incomeItem = $incomeItems[$i];
            $targetModel = $targetModels[$i];
            $netAmount = $incomeItem->getProperty('net_amount');
            $returnModel = IncomeItemFactory::linkSingleIncomeItemToModel($targetModel, $income, $incomeItem, $netAmount);
            if (empty($returnModel)) {
                return false;
            }
        }
        return true;
    }

    public static function removeIncomeItemsLinkedToModel(GI_Model $model, AbstractIncome $income) {
        $incomeItems = IncomeItemFactory::getIncomeItemsByLinkedModel($model, $income);
        if (empty($incomeItems)) {
            return true;
        }
        foreach ($incomeItems as $incomeItem) {
            $numberOfLinks = $incomeItem->getNumberOfModelsLinkedToIncomeItem();
            $unlinkResult = IncomeItemFactory::unlinkIncomeItemFromModel($model, $incomeItem);
            if ($numberOfLinks == 1) {
                //if so, just remove the link and soft delete the income item
                if (!$unlinkResult || !$incomeItem->softDelete()) {
                    return false;
                }
            } else {
                if (!$unlinkResult) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function changeIncomeItemLinkFromOneModelToAnother(GI_model $sourceModel, GI_Model $destinationModel, AbstractIncomeItem $incomeItem) {
        $sourceModelId = $sourceModel->getProperty('id');
        $sourceModelTableName = $sourceModel->getTableName();
        $incomeItemId = $incomeItem->getProperty('id');
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $linkDAOArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                    'table_name' => $sourceModelTableName,
                    'item_id' => $sourceModelId,
                    'income_item_id' => $incomeItemId
        ));
        if (empty($linkDAOArray)) {
            return false;
        }
        $linkDAO = $linkDAOArray[0];
        $linkDAO->setProperty('table_name', $destinationModel->getTableName());
        $linkDAO->setProperty('item_id', $destinationModel->getProperty('id'));
        return $linkDAO->save();
    }
    
    public static function copyLinksToIncomeItemsFromOneModelToAnother(GI_Model $sourceModel, GI_Model $targetModel) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $sourceModelId = $sourceModel->getProperty('id');
        $sourceModelTableName = $sourceModel->getTableName();
        $linkArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
            'table_name'=>$sourceModelTableName,
            'item_id'=>$sourceModelId
        ));
        if (!empty($linkArray)) {
            foreach ($linkArray as $link) {
                $incomeItemId = $link->getProperty('income_item_id');
                $targetModelId = $targetModel->getProperty('id');
                $targetTableName = $targetModel->getTableName();
                $existingLinkArray = $defaultDAOClass::getByProperties('item_link_to_income_item', array(
                    'table_name'=>$targetTableName,
                    'item_id'=>$targetModelId,
                    'income_item_id'=>$incomeItemId
                ));
                if (empty($existingLinkArray)) {
                    $newLink = new $defaultDAOClass('item_link_to_income_item');
                    $newLink->setProperty('table_name', $targetTableName);
                    $newLink->setProperty('item_id', $targetModelId);
                    $newLink->setProperty('income_item_id', $incomeItemId);
                    if (!$newLink->save()) {
                        return false;
                    }
                }
            }
            return true;
        }
    }

    public static function moveItemsFromIncomeToIncome(AbstractIncome $source, AbstractIncome $destination) {
        $items = $source->getIncomeItems();
        $desinationId = $destination->getProperty('id');
        foreach ($items as $item) {
            $item->setProperty('income_id', $desinationId);
            if (!$item->save()) {
                return false;
            }
        }
        if (!$source->save() && $destination->save()) {
            return false;
        }
        return true;
    }

    /**
     * @param AbstractIncomeItem $initIncomeItem
     * @param GI_Model[] $models
     * @return boolean
     */
    public static function splitIncomeItemBetweenModels(AbstractIncomeItem $initIncomeItem, $models = array()){
        array_values($models);
        $modelCount = count($models);
        if(!$modelCount){
            return false;
        }
        
        $netAmount = $initIncomeItem->getNetTotal();
        $typeRef = $initIncomeItem->getTypeRef();
        $inProgress = $initIncomeItem->getProperty('in_progress');
        $income = $initIncomeItem->getIncome();
        $links = static::getIncomeItemLinksFromIncomeItem($initIncomeItem);
        $taxCodeQBId = $initIncomeItem->getTaxCodeQBId();
        
        $amounts = array();
        GI_Math::divideMoneyWithoutLoss($netAmount, $modelCount, $amounts);
        $initAmount = $amounts[0];
        $initModel = $models[0];
        
        $incomeItems = array();
        for ($i = 1; $i < $modelCount; $i++) {
            $specificAmount = $amounts[$i];
            $model = $models[$i];
            $incomeItem = static::buildNewModel($typeRef, $inProgress);
            $incomeItem->setProperty('tax_code_qb_id', $taxCodeQBId);
            $incomeItems[] = static::linkSingleIncomeItemToModel($model, $income, $incomeItem, $specificAmount);
            if(!static::linkIncomeItemToOtherIncomeItemLinks($incomeItem, $links)){
                return false;
            }
        }
        //handle init income item
        $incomeItems[] = static::linkSingleIncomeItemToModel($initModel, $income, $initIncomeItem, $initAmount);

        
        return true;
    }
    
    /**
     * @param AbstractIncomeItem $incomeItem
     * @return GI_DAO[]
     */
    public static function getIncomeItemLinksFromIncomeItem(AbstractIncomeItem $incomeItem){
        $incomeItemId = $incomeItem->getId();
        $search = new GI_DataSearch('item_link_to_income_item');
        $search->filter('income_item_id', $incomeItemId);
        $links = $search->select();
        return $links;
    }
    
    /**
     * @param AbstractIncomeItem $incomeItem
     * @param GI_DAO[] $links
     * @return boolean
     */
    public static function linkIncomeItemToOtherIncomeItemLinks(AbstractIncomeItem $incomeItem, $links = array()){
        $incomeItemId = $incomeItem->getId();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        if(!$links){
            return false;
        }
        foreach($links as $link){
            if($link->getProperty('income_item_id') == $incomeItemId){
                continue;
            }
            $newLink = new $defaultDAOClass('item_link_to_income_item');
            $newLink->setProperty('table_name', $link->getProperty('table_name'));
            $newLink->setProperty('item_id', $link->getProperty('item_id'));
            $newLink->setProperty('income_item_id', $incomeItemId);
            if (!$newLink->save()) {
                return false;
            }
        }
        return true;
    }
    
    public static function getIncomeItemEcoFeeTypeRefs() {
        return array(
            'sales_ac_eco',
            'eco_can_ab',
            'eco_can_bc',
            'eco_can_mb',
            'eco_can_nb',
            'eco_can_nl',
            'eco_can_ns',
            'eco_can_nt',
            'eco_can_nu',
            'eco_can_on',
            'eco_can_pe',
            'eco_can_qc',
            'eco_can_sk',
            'eco_can_yt',
        );
    }
    
    public static function getTypesArray($rootType = NULL, $topLevelWithIdAsKey = false, $typeProperty = 'title', $stopAtRoot = false, $excludeBranches = false, $includeBranchRefs = array()) {
        $typesArray = parent::getTypesArray($rootType, $topLevelWithIdAsKey, $typeProperty, $stopAtRoot, $excludeBranches, $includeBranchRefs);
        $ecoFeeTypes = static::getIncomeItemEcoFeeTypeRefs();
        foreach ($ecoFeeTypes as $ecoFeeType) {
            if (isset($typesArray[$ecoFeeType])) {
                unset($typesArray[$ecoFeeType]);
            }
        }
        return $typesArray;
    }

}
