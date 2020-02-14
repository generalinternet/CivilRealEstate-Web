<?php
/**
 * Description of AbstractExpenseItemFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractExpenseItemFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'expense_item';
    protected static $models = array();

    /**
     * @param string $typeRef
     * @return AbstractExpenseItem
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
            case 'expense':
            case 'general':
            case 'cogs':
            case 'cogs_item':
            case 'cogs_ac':
            case 'cogs_ac_shipping':
            case 'cogs_ac_other':
            case 'inv':
            case 'inv_item':
            case 'inv_ac':
            case 'inv_ac_shipping':
            case 'inv_ac_other':
            case 'wst':
            default:
                $model = new ExpenseItem($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'expense':
                $typeRefs = array('expense');
                break;
            case 'general':
                $typeRefs = array('general', 'general');
                break;
            case 'cogs':
                $typeRefs = array('cogs', 'cogs');
                break;
            case 'cogs_item':
                $typeRefs = array('cogs', 'cogs_item', 'cogs_item');
                break;
            case 'cogs_ac':
                $typeRefs = array('cogs', 'cogs_ac', 'cogs_ac');
                break;
            case 'cogs_ac_shipping':
                $typeRefs = array('cogs', 'cogs_ac', 'cogs_ac_shipping');
                break;
            case 'cogs_ac_other':
                $typeRefs = array('cogs', 'cogs_ac', 'cogs_ac_other');
                break;
            case 'inv':
                $typeRefs = array('inv', 'inv');
                break;
            case 'inv_item':
                $typeRefs = array('inv', 'inv_item', 'inv_item');
                break;
            case 'inv_ac':
                $typeRefs = array('inv','inv_ac', 'inv_ac');
                break;
            case 'inv_ac_shipping':
                $typeRefs = array('inv','inv_ac', 'inv_ac_shipping');
                break;
            case 'inv_ac_other':
                $typeRefs = array('inv', 'inv_ac', 'inv_ac_other');
                break;
            case 'wst':
                $typeRefs = array('wst', 'wst');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * 
     * @param string $typeRef
     * @return string
     * @deprecated since version 3.0
     */
    public static function getPTypeRef($typeRef) {
        $typeRefsArray = static::getTypeRefArray($typeRef);
        $numberOfRefs = sizeof($typeRefsArray);
        if ($numberOfRefs > 2) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            return $pTypeRef;
        } else {
            $pTypeRef = 'expense';
        }
        return $pTypeRef;
    }

    /**
     * 
     * @param GI_Model $model
     * @param AbstractExpense $expense
     * @return DataSearch
     */
    public static function getExpenseItemDataSearchByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL, $includeVoidedItems = false, $includeCancelledItems = false){
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $expenseItemSearch = ExpenseItemFactory::search();
        $linkJoin = $expenseItemSearch->createJoin('item_link_to_expense_item', 'expense_item_id', $expenseItemTableName, 'id', 'iltei');
        $linkJoin->filter('iltei.table_name', $modelTableName);
        $expenseItemSearch->filter('iltei.item_id', $modelId);
        if (!empty($expense)) {
            $expenseId = $expense->getProperty('id');
            $expenseItemSearch->filter('expense_id', $expenseId);
        }
        if (!$includeVoidedItems) {
            $expenseItemSearch->filter('void', 0);
        } 
        if (!$includeCancelledItems) {
            $expenseItemSearch->filter('cancelled', 0);
        }
        $expenseItemSearch->groupBy('id');
        return $expenseItemSearch;
    }
    
    public static function getExpenseItemsByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL, $includeVoidedItems = false, $limit = NULL) {
        $expenseItemSearch  = static::getExpenseItemDataSearchByLinkedModel($model, $expense, $includeVoidedItems = false);
        if (!empty($limit)) {
            $expenseItemSearch->setItemsPerPage($limit)
                    ->setPageNumber(1);
        }
        $expenseItemSearch->setSortAscending(true);
        $existingExpenseItems = $expenseItemSearch->select();
        return $existingExpenseItems;
    }
    
    public static function getExpenseItemsCountByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL, $includeVoidedItems = false) {
        $expenseItemSearch = static::getExpenseItemDataSearchByLinkedModel($model, $expense, $includeVoidedItems);
        return $expenseItemSearch->count();
    }
    
    public static function getExpenseSumByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL, $requestedCurrencyRef = 'usd', $withSymbols = false) {
        $expenseItemSearch  = static::getExpenseItemDataSearchByLinkedModel($model, $expense);
        $sum = $expenseItemSearch->sum('net_amount');
        $netAmount = (float) $sum['net_amount'];
        
        $expenseItemSearch->setItemsPerPage(1);
        $expenseItems = $expenseItemSearch->select();
        if($expenseItems){
            $expenseItem = $expenseItems[0];
            $expense = $expenseItem->getExpense();
            $currency = $expense->getCurrency();
            
            if (!empty($currency)) {
                $currencyName = $currency->getProperty('currency.name');
                $currencySymbol = $currency->getProperty('currency.symbol');
                $exRateToUSD = $currency->getProperty('currency.sys_ex_rate_to_usd');
                
                $currencyRef = $currency->getProperty('currency.ref');
                if ($requestedCurrencyRef != $currencyRef) {
                    $requestedCurrencyArray = CurrencyFactory::search()
                            ->filter('currency.ref', $requestedCurrencyRef)
                            ->select();
                    if (!empty($requestedCurrencyArray)) {
                        $requestedCurrency = $requestedCurrencyArray[0];
                        $currencyName = $requestedCurrency->getProperty('currency.name');
                        $currencySymbol = $requestedCurrency->getProperty('currency.symbol');
                        $exRateToUSD = $requestedCurrency->getProperty('currency.sys_ex_rate_to_usd');
                    }
                }
                
                $exchange = round((1 / $exRateToUSD), 2);
                $netAmount = $netAmount * $exchange;
                if ($withSymbols) {
                    $netAmount = $currencySymbol . GI_StringUtils::formatMoney($netAmount) . ' ' . $currencyName;
                }
            }
        }
        return $netAmount;
    }

    /**
     * Adjusts the expense items and links related to a model
     * @param GI_Model $model
     * @param AbstractExpense $expenseModel
     * @param int $targetNumber
     * @param decimal $totalNet
     * @param string $newTypeRef
     * @return AbstractExpenseItem[]
     */

    public static function adjustExpenseItemsByLinkedModel(GI_Model $model, AbstractExpense $expense, $targetQty = 1, $totalNet = 0, $newTypeRef = 'expense', $inProgress = false, AbstractExpenseItemGroup $expenseItemGroup = NULL) {
        if ($targetQty == 0) {
            $result = ExpenseItemFactory::removeExpenseItemsLinkedToModel($model, $expense);
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
        
        $existingExpenseItems = array();
        $preExistingExpenseItems = static::getExpenseItemsByLinkedModel($model, $expense);
        if (!empty($preExistingExpenseItems)) {
            if ($preExistingExpenseItems[0]->getTypeRef() != $newTypeRef) {
                foreach ($preExistingExpenseItems as $preExistingItem) {
                    $preExisitngTypeRef = $preExistingItem->getTypeRef();
                    if (($preExisitngTypeRef != $newTypeRef) && (strpos($preExisitngTypeRef, 'cogs') === false)) { //and type ref doesn't include cogs
                        $existingExpenseItems[] = ExpenseItemFactory::changeModelType($preExistingItem, $newTypeRef);
                    } else {
                        $existingExpenseItems[] = $preExistingItem;
                    }
                }
            } else {
                $existingExpenseItems = $preExistingExpenseItems;
            }
        }

        $amounts = array();
        GI_Math::divideMoneyWithoutLoss($totalNet, $targetQty, $amounts);
        $expenseItems = array();
        if (empty($existingExpenseItems)) { //CASE 1 - there aren't any, so we have to create all of them
            for ($i = 0; $i < $targetQty; $i++) {
                $specificAmount = $amounts[$i];
                $expenseItem = ExpenseItemFactory::buildNewModel($newTypeRef, $inProgress);
                if (!empty($taxCodeQBId)) {
                    $expenseItem->setProperty('tax_code_qb_id', $taxCodeQBId);
                }
                $expenseItems[] = static::linkSingleExpenseItemToModel($model, $expense, $expenseItem, $specificAmount, NULL, $expenseItemGroup);
            }
            return $expenseItems;
        } else { //CASE 2 - there are some, so we have to check them
            $numberOfExistingItems = sizeof($existingExpenseItems);
            //2A - There are the same number as target
            if ($numberOfExistingItems == $targetQty) {
                $expenseItems = $existingExpenseItems;
                //2B - There is a different number than the target, so we have to adjust what is there
            } else if ($numberOfExistingItems > $targetQty) { //2Bi - There are too many, so some need to be removed
                for ($i = 0; $i < $targetQty; $i++) {
                    $currentItem = $existingExpenseItems[$i];
                    $expenseItems[] = $currentItem;
                    unset($existingExpenseItems[$i]);
                }
                //remove the rest
                foreach ($existingExpenseItems as $key=>$itemToDelete) {
                    $itemToDeleteId = $itemToDelete->getProperty('id');
                    $itemLinkToExpenseItemToDeleteDAOArray = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
                        'table_name' => $modelTableName,
                        'item_id' => $modelId,
                        'expense_item_id' => $itemToDeleteId
                    ));
                    if (empty($itemLinkToExpenseItemToDeleteDAOArray)) {
                        return NULL;
                    }
                    $itemToDeleteLinkDAO = $itemLinkToExpenseItemToDeleteDAOArray[0];
                    if (!$itemToDeleteLinkDAO->softDelete() || !$itemToDelete->softDelete()) {
                        return NULL;
                    }
                    unset($existingExpenseItems[$key]);
                }
            } else { //2Bii - There aren't enough, so we need to add some
                foreach ($existingExpenseItems as $existingExpenseItem) {
                    $expenseItems[] = $existingExpenseItem;
                }
                $difference = $targetQty - $numberOfExistingItems;
                $softDeletedExpenseItemArray = static::getSoftDeletedExpenseItemsByLinkedModel($model);
                for ($j = 0; $j < $difference; $j++) {
                    $specificAmount = $amounts[$j];
                    $expenseItem = NULL;
                    if (!empty($softDeletedExpenseItemArray) && isset($softDeletedExpenseItemArray[$j])) {
                        $softDeletedExpenseItem = $softDeletedExpenseItemArray[$j];
                        $softDeletedExpenseItem->setProperty('status', 1);
                        if ($softDeletedExpenseItem->save()) {
                            $expenseItem = $softDeletedExpenseItem;
                             unset($softDeletedExpenseItemArray[$j]);
                        }
                    }
                    if (empty($expenseItem)) {
                        $expenseItem = ExpenseItemFactory::buildNewModel($newTypeRef, $inProgress);
                    }
                    $expenseItemId = $expenseItem->getProperty('id');
                    $newLink = NULL;
                    if (!empty($expenseItemId)) {
                        $softDeletedLinkArray = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
                            'table_name' => $modelTableName,
                            'item_id' => $modelId,
                            'expense_item_id' => $expenseItemId,
                        ), 'client', 0);

                        if ($softDeletedLinkArray) {
                            $softDeletedLink = $softDeletedLinkArray[0];
                            $softDeletedLink->setProperty('status', 1);
                            if ($softDeletedLink->save()) {
                                $newLink = $softDeletedLink;
                            }
                        }
                    }
                    $expenseItems[] = static::linkSingleExpenseItemToModel($model, $expense, $expenseItem, $specificAmount, $newLink);
                }
            }
            //Adjust the net_amount of each
            $expenseItemCount = count($expenseItems);
            for ($k=0;$k<$expenseItemCount;$k++) {
                $eItem = $expenseItems[$k];
                $specificAmount = $amounts[$k];
                $eItem->setProperty('net_amount', $specificAmount);
                if ($inProgress) {
                    $eItem->setProperty('in_progress', 1);
                } else {
                    $eItem->setProperty('in_progress', 0);
                }
                if (!empty($taxCodeQBId)) {
                    $eItem->setProperty('tax_code_qb_id', $taxCodeQBId);
                }
                if (!empty($expenseItemGroup)) {
                    $eItem->setProperty('expense_item_group_id', $expenseItemGroup->getProperty('id'));
                }
                if (!$eItem->save()) {
                    return NULL;
                }
            }

            if (!empty($taxCodeQBId) && !empty($expenseItemGroup)) {
                $expenseItemGroup->setProperty('tax_code_qb_id', $taxCodeQBId);
                $expenseItemGroup->save();
            }
            return $expenseItems;
        }
    }

    protected static function linkSingleExpenseItemToModel(GI_Model $model, AbstractExpense $expense, AbstractExpenseItem $expenseItem, $netAmount, $linkDAO = NULL, AbstractExpenseItemGroup $group = NULL) {
        if (empty($linkDAO)) {
            $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
            $linkDAO = new $defaultDAOClass('item_link_to_expense_item');
            if (empty($linkDAO)) {
                return NULL;
            }
        }
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseId = $expense->getProperty('id');
        $expenseItem->setProperty('expense_id', $expenseId);
        $expenseItem->setProperty('net_amount', $netAmount);
        $expenseItem->setProperty('void', 0);
        $expenseItem->setProperty('cancelled', 0);
        if (!empty($group)) {
            $expenseItem->setProperty('expense_item_group_id', $group->getProperty('id'));
        }
        if (!$expenseItem->save()) {
            return NULL;
        }
        $expenseItemId = $expenseItem->getProperty('id');
        $linkDAO->setProperty('table_name', $modelTableName);
        $linkDAO->setProperty('item_id', $modelId);
        $linkDAO->setProperty('expense_item_id', $expenseItemId);
        if (!$linkDAO->save()) {
            return NULL;
        }
        return $expenseItem;
    }

    public static function linkExpenseItemToModel(GI_Model $model, AbstractExpenseItem $expenseItem) {
        $modelId = $model->getProperty('id');
        $expenseItemId = $expenseItem->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $search = new GI_DataSearch('item_link_to_expense_item');
        $search->filter('table_name', $modelTableName)
                ->filter('item_id', $modelId)
                ->filter('expense_item_id', $expenseItemId)
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
            $newLink = new $defaultDAOClass('item_link_to_expense_item');
            $newLink->setProperty('table_name', $modelTableName);
            $newLink->setProperty('item_id', $modelId);
            $newLink->setProperty('expense_item_id', $expenseItemId);
            if (!$newLink->save()) {
                return false;
            }    
        }
        return true;
    }

    public static function unlinkExpenseItemFromModel(GI_Model $model, AbstractExpenseItem $expenseItem) {
        $modelId = $model->getProperty('id');
        $expenseItemId = $expenseItem->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        //TODO - use DataSearch
        $linkArray = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
            'table_name'=>$modelTableName,
            'item_id'=>$modelId,
            'expense_item_id'=>$expenseItemId
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
    
    public static function getNumberOfModelsLinkedToExpenseItem(AbstractExpenseItem $expenseItem) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $expenseItemId = $expenseItem->getProperty('id');
        $linkDAOArray = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
            'expense_item_id'=>$expenseItemId
        ));
        if (empty($linkDAOArray)) {
            return 0;
        }
        return sizeof($linkDAOArray);
    }
        
    protected static function getSoftDeletedExpenseItemsByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL) {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $expenseItemSearch = ExpenseItemFactory::search()
                ->filter('status', 0)
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->join('item_link_to_expense_item', 'expense_item_id', $expenseItemTableName, 'id', 'iltei')
                ->filter('iltei.table_name', $modelTableName)
                ->filter('iltei.item_id', $modelId)
                ->filter('iltei.status', 0);
        if (!empty($expense)) {
            $expenseId = $expense->getProperty('id');
            $expenseItemSearch->filter('expense_id', $expenseId);
        }
        $softDeletedExpenseItemArray = $expenseItemSearch->select();
        return $softDeletedExpenseItemArray;
    }
    
    public static function linkNExpenseItemsToNModelsFromModel(GI_Model $sourceModel, $targetModels, AbstractExpense $expense) {
        $expenseItems = ExpenseItemFactory::getExpenseItemsByLinkedModel($sourceModel, $expense);
        $numberOfExpenseItems = (int) sizeof($expenseItems);
        $numberOfTargetModels = (int) sizeof($targetModels);
        if ($numberOfExpenseItems != $numberOfTargetModels) {
            return false;
        }
        for ($i=0;$i<$numberOfTargetModels;$i++) {
          $expenseItem = $expenseItems[$i];
          $targetModel = $targetModels[$i];
          $netAmount = $expenseItem->getProperty('net_amount');
          $returnModel = ExpenseItemFactory::linkSingleExpenseItemToModel($targetModel, $expense, $expenseItem, $netAmount);
          if (empty($returnModel)) {
              return false;
          }
        }
        return true;
    }
    
    public static function removeExpenseItemsLinkedToModel(GI_Model $model, AbstractExpense $expense) {
        $expenseItems = ExpenseItemFactory::getExpenseItemsByLinkedModel($model, $expense);
        if (empty($expenseItems)) {
            return true;
        }
        foreach ($expenseItems as $expenseItem) {
            $numberOfLinks = $expenseItem->getNumberOfModelsLinkedToExpenseItem();
            $unlinkResult = ExpenseItemFactory::unlinkExpenseItemFromModel($model, $expenseItem);
            if ($numberOfLinks == 1) {
                //if so, just remove the link and soft delete the expense item
                if (!$unlinkResult || !$expenseItem->softDelete()) {
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
    
    public static function changeExpenseItemLinkFromOneModelToAnother(GI_model $sourceModel, GI_Model $destinationModel, AbstractExpenseItem $expenseItem) {
        $sourceModelId = $sourceModel->getProperty('id');
        $sourceModelTableName = $sourceModel->getTableName();
        $expenseItemId = $expenseItem->getProperty('id');
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $linkDAOArray = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
            'table_name'=>$sourceModelTableName,
            'item_id'=>$sourceModelId,
            'expense_item_id'=>$expenseItemId
        ));
        if (empty($linkDAOArray)) {
            return false;
        }
        $linkDAO = $linkDAOArray[0];
        $linkDAO->setProperty('table_name',$destinationModel->getTableName());
        $linkDAO->setProperty('item_id', $destinationModel->getProperty('id'));
        return $linkDAO->save();
    }
    
    public static function updateExpenseItemsWithEqualPortionOfSum($expenseItems, $sum) {
        if (!empty($expenseItems)) {
            $sums = array();
            $numberOfItems = count($expenseItems);
            GI_Math::divideMoneyWithoutLoss($sum, $numberOfItems, $sums);
            for ($i = 0; $i < $numberOfItems; $i++) {
                $expenseItem = $expenseItems[$i];
                $expenseItem->setProperty('net_amount', $sums[$i]);
                if (!$expenseItem->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function moveItemsFromExpenseToExpense(AbstractExpense $source, AbstractExpense $destination) {
        $items = $source->getExpenseItems();
        $desinationId = $destination->getProperty('id');
        foreach ($items as $item) {
            $item->setProperty('expense_id', $desinationId);
            if (!$item->save()) {
                return false;
            }
        }
        if (!$source->save() && $destination->save()) {
            return false;
        }
        return true;
    }

    public static function getLinkedTaxLinkToRegionsByExpenseItem(AbstractExpenseItem $expenseItem, $includeDeleted = false) {
        $dataSearch = new GI_DataSearch('expense_item_link_to_tax_in_region');
        $dataSearch->filter('expense_item_id', $expenseItem->getProperty('id'));
        if($includeDeleted){
            $dataSearch->filterNotNull('status');
        }
        $existingTaxLinks = $dataSearch->select();
        return $existingTaxLinks;
    }

    /**
     * @deprecated - use GI_StringUtils::replaceFirst() instead
     * @return String
     */
    public static function convertInvTypeRefToCogsTypeRef($invTypeRef) {
        if (substr($invTypeRef, 0, 4) == 'inv_') {
            return substr($invTypeRef, 4);
        }
        return $invTypeRef;
    }

    /**
     * @deprecated - use GI_StringUtils::replaceFirst() instead
     * @return String
     */
    public static function convertCogsTypeRefToInvTypeRef($cogsTypeRef) {
        if (substr($cogsTypeRef, 0, 4) != 'inv_') {
            return 'inv_' . $cogsTypeRef;
        }
        return $cogsTypeRef;
    }

    public static function convertInvToCogs(AbstractExpenseItem $expenseItem) {
        $curTypeRef = $expenseItem->getTypeRef();
        if (!substr($curTypeRef, 0, 3) == 'inv') {
            return $expenseItem;
        }
        $newTypeRef = GI_StringUtils::replaceFirst('inv', 'cogs', $curTypeRef);
        $typeRefExists = static::getTypeRefArrayFromTypeRef($newTypeRef);
        if (empty($typeRefExists)) {
            $newTypeRef = 'cogs';
        }
        $newExpenseItem = static::convertExpenseItemTypeAndSave($expenseItem, $newTypeRef);
        return $newExpenseItem;
    }

    public static function convertCogsToInv(AbstractExpenseItem $expenseItem) {
        $curTypeRef = $expenseItem->getTypeRef();
        if (!substr($curTypeRef, 0, 3) == 'cogs') {
            return $expenseItem;
        }
        $newTypeRef = GI_StringUtils::replaceFirst('cogs', 'inv', $curTypeRef);
        $typeRefExists = static::getTypeRefArrayFromTypeRef($newTypeRef);
        if (empty($typeRefExists)) {
            $newTypeRef = 'inv';
        }
        $newExpenseItem = static::convertExpenseItemTypeAndSave($expenseItem, $newTypeRef);
        return $newExpenseItem;
    }

    public static function convertInvToWst(AbstractExpenseItem $expenseItem) {
        $curTypeRef = $expenseItem->getTypeRef();
        if (!substr($curTypeRef, 0, 3) == 'inv') {
            return $expenseItem;
        }
        $newTypeRef = GI_StringUtils::replaceFirst('inv', 'wst', $curTypeRef);
        $typeRefExists = static::getTypeRefArrayFromTypeRef($newTypeRef);
        if(empty($typeRefExists)){
            $newTypeRef = 'wst';
        }
        $newExpenseItem = static::convertExpenseItemTypeAndSave($expenseItem, $newTypeRef);
        return $newExpenseItem;
    }
    
    public static function convertCogsToWst(AbstractExpenseItem $expenseItem) {
        $curTypeRef = $expenseItem->getTypeRef();
        if (!substr($curTypeRef, 0, 3) == 'cogs') {
            return $expenseItem;
        }
        $newTypeRef = GI_StringUtils::replaceFirst('cogs', 'wst', $curTypeRef);
        $typeRefExists = static::getTypeRefArrayFromTypeRef($newTypeRef);
        if (empty($typeRefExists)) {
            $newTypeRef = 'wst';
        }
        $newExpenseItem = static::convertExpenseItemTypeAndSave($expenseItem, $newTypeRef);
        return $newExpenseItem;
    }

    protected static function convertExpenseItemTypeAndSave(AbstractExpenseItem $expenseItem, $newTypeRef) {
        $newExpenseItem = static::changeModelType($expenseItem, $newTypeRef);
        $newExpenseItem->setProperty('applicable_date', GI_Time::getDate());
        if (!$newExpenseItem->save()) {
            return NULL;
        }
        return $newExpenseItem;
    }

    /**
     * @deprecated - use convertInvToCogs instead
     * @param AbstractExpenseItem $expenseItem
     * @return \AbstractExpenseItem
     */
    public static function changeInvTypeToCogsType(AbstractExpenseItem $expenseItem) {
        $currentTypeRef = $expenseItem->getTypeRef();
        if (!substr($currentTypeRef, 0, 4) == 'inv_') {
            return $expenseItem;
        }
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $expenseItemId = $expenseItem->getProperty('id');
        
        //TOP LEVEL DAO
        $topLevelCogsTypeModel = TypeModelFactory::getTypeModelByRef('cogs', 'expense_item_type');
        if (empty($topLevelCogsTypeModel)) {
            return NULL;
        }
        $topLevelCogsTypeId = $topLevelCogsTypeModel->getProperty('id');
        $topLevelDAO = $defaultDAOClass::getById('expense_item', $expenseItemId);
        if (empty($topLevelDAO)) {
            return NULL;
        }
        $topLevelDAO->setProperty('expense_item_type_id', $topLevelCogsTypeId);
        $topLevelDAO->setProperty('applicable_date', date('Y-m-d'));
        if (!$topLevelDAO->save()) {
            return NULL;
        }
        //INV LEVEL DAO
        $invLevelDAOArray = $defaultDAOClass::getByProperties('expense_item_inv', array(
            'parent_id'=>$expenseItemId
        ));
        if (empty($invLevelDAOArray)) {
            return NULL;
        }
        $invLevelDAO = $invLevelDAOArray[0];
        if (!$invLevelDAO->softDelete()) {
            return NULL;
        }
        //COGS LEVEL DAO
        $cogsTypeRef = ExpenseItemFactory::convertInvTypeRefToCogsTypeRef($currentTypeRef);
        $cogsLevelTypeModel = TypeModelFactory::getTypeModelByRef($cogsTypeRef, 'expense_item_cogs_type');
        if (empty($cogsLevelTypeModel)) {
            return NULL;
        }
        $cogsLevelTypeId = $cogsLevelTypeModel->getProperty('id');
        $cogsDAO = new $defaultDAOClass('expense_item_cogs');
        if (empty($cogsDAO)) {
            return NULL;
        }
        $cogsDAO->setProperty('status', 1);
        $cogsDAO->setProperty('parent_id', $expenseItemId);
        $cogsDAO->setProperty('expense_item_cogs_type_id', $cogsLevelTypeId);
        if (!$cogsDAO->save()) {
            return NULL;
        }
        $updatedExpenseItem = ExpenseItemFactory::getModelById($expenseItemId);
        return $updatedExpenseItem;
    }

    /**
     * @deprecated - use convertCogsToInv instead
     * @param AbstractExpenseItem $expenseItem
     * @return \AbstractExpenseItem
     */
    public static function changeCogsTypeToInvType(AbstractExpenseItem $expenseItem) {
        $currentTypeRef = $expenseItem->getTypeRef();
        if (substr($currentTypeRef, 0, 4) == 'inv_') {
            return $expenseItem;
        }
        $expense = $expenseItem->getExpense();
        $expenseApplicableDate = $expense->getProperty('applicable_date');
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $expenseItemId = $expenseItem->getProperty('id');
        //TOP LEVEL DAO
        $topLevelInvTypeModel = TypeModelFactory::getTypeModelByRef('inv', 'expense_item_type');
        if (empty($topLevelInvTypeModel)) {
            return NULL;
        }      
        $topLevelInvTypeId = $topLevelInvTypeModel->getProperty('id');
        $topLevelDAO = $defaultDAOClass::getById('expense_item', $expenseItemId);

        if (empty($topLevelDAO)) {
            return NULL;
        }

        $topLevelDAO->setProperty('expense_item_type_id', $topLevelInvTypeId);
        $topLevelDAO->setProperty('applicable_date', $expenseApplicableDate);
        if (!$topLevelDAO->save()) {
            return NULL;
        }
        
        //COGS LEVEL DAO
        $cogsLevelDAOArray = $defaultDAOClass::getByProperties('expense_item_cogs', array(
                    'parent_id' => $expenseItemId
        ));
        if (empty($cogsLevelDAOArray)) {
            return NULL;
        }
        $cogsLevelDAO = $cogsLevelDAOArray[0];
        if (!$cogsLevelDAO->softDelete()) {
            return NULL;
        }
        
        //GOOD
        //INV LEVEL DAO
        $invTypeRef = ExpenseItemFactory::convertCogsTypeRefToInvTypeRef($currentTypeRef);
        $invLevelTypeModel = TypeModelFactory::getTypeModelByRef($invTypeRef, 'expense_item_inv_type');
        if (empty($invLevelTypeModel)) {
            return NULL;
        }
        $invLevelTypeId = $invLevelTypeModel->getProperty('id');
        $invDAO = new $defaultDAOClass('expense_item_inv');
        
        if (empty($invDAO)) {
            return NULL;
        }
        $invDAO->setProperty('status', 1);
        $invDAO->setProperty('parent_id', $expenseItemId);
        $invDAO->setProperty('expense_item_inv_type_id', $invLevelTypeId);
        if (!$invDAO->save()) {
            return NULL;
        }
        
        $updatedExpenseItem = ExpenseItemFactory::getModelById($expenseItemId);
        return $updatedExpenseItem;
    }
    
    public static function TagExpenseItem(AbstractExpenseItem $expenseItem, $tagRef) {
        $tag = TagFactory::getModelByTagRefAndTypeRef($tagRef, 'expense_item');
        if (empty($tag)) {
            return false;
        }
        $expenseItemId = $expenseItem->getProperty('id');
        $tagId = $tag->getProperty('id');
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $searchArray = array(
            'expense_item_id'=>$expenseItemId,
            'tag_id'=>$tagId
        );
        $existingLinkDAOArray = $defaultDAOClass::getByProperties('expense_item_link_to_tag', $searchArray);

        if (!empty($existingLinkDAOArray)) {
            return true;
        }
        $searchArray['status'] = 0;
        $softDeletedLinkDAOArray = $defaultDAOClass::getByProperties('expense_item_link_to_tag', $searchArray);
        if (!empty($softDeletedLinkDAOArray)) {
            $softDeletedDAO = $softDeletedLinkDAOArray[0];
            $softDeletedDAO->setProperty('status', 1);
            if ($softDeletedDAO->save()) {
                return true;
            }
        }
        $newLinkDAO = new $defaultDAOClass('expense_item_link_to_tag');
        $newLinkDAO->setProperty('expense_item_id', $expenseItemId);
        $newLinkDAO->setProperty('tag_id', $tagId);
        if ($newLinkDAO->save()) {
            return true;
        }
        return false;
    } 

    public static function unTagExpenseItem(AbstractExpenseItem $expenseItem, $tagRef) {
        //TODO - implement this function
    }

    /**
     * @param AbstractExpenseItem $initExpenseItem
     * @param GI_Model[] $models
     * @return boolean
     */
    public static function splitExpenseItemBetweenModels(AbstractExpenseItem $initExpenseItem, $models = array()){
        array_values($models);
        $modelCount = count($models);
        if(!$modelCount){
            return false;
        }
        
        $netAmount = $initExpenseItem->getNetTotal();
        $typeRef = $initExpenseItem->getTypeRef();
        $inProgress = $initExpenseItem->getProperty('in_progress');
        $taxCodeQBId = $initExpenseItem->getProperty('tax_code_qb_id');
        $expense = $initExpenseItem->getExpense();
        $expenseItemGroup = ExpenseItemGroupFactory::getModelById($initExpenseItem->getProperty('expense_item_group_id'));
        $links = static::getExpenseItemLinksFromExpenseItem($initExpenseItem);
        
        $amounts = array();
        GI_Math::divideMoneyWithoutLoss($netAmount, $modelCount, $amounts);
        $initAmount = $amounts[0];
        $initModel = $models[0];
        
        $expenseItems = array();
        for ($i = 1; $i < $modelCount; $i++) {
            $specificAmount = $amounts[$i];
            $model = $models[$i];
            $expenseItem = static::buildNewModel($typeRef, $inProgress);
            $expenseItem->setProperty('tax_code_qb_id', $taxCodeQBId);
            $expenseItems[] = static::linkSingleExpenseItemToModel($model, $expense, $expenseItem, $specificAmount, NULL, $expenseItemGroup);
            if(!static::linkExpenseItemToOtherExpenseItemLinks($expenseItem, $links)){
                return false;
            }
        }
        //handle init expense item
        $expenseItems[] = static::linkSingleExpenseItemToModel($initModel, $expense, $initExpenseItem, $initAmount);
        
        return true;
    }
    
    /**
     * @param AbstractExpenseItem $initExpenseItem
     * @param array $proportions
     * @return boolean|AbstractExpenseItem[]
     */
    public static function splitExpenseItemByProportions(AbstractExpenseItem $initExpenseItem, $proportions = array()){
        array_values($proportions);
        $proportionCount = count($proportions);
        if(!$proportionCount){
            return false;
        }
        
        $netAmount = $initExpenseItem->getNetTotal();
        $typeRef = $initExpenseItem->getTypeRef();
        $inProgress = $initExpenseItem->getProperty('in_progress');
        $taxCodeQBId = $initExpenseItem->getProperty('tax_code_qb_id');
        $expense = $initExpenseItem->getExpense();
        $expenseItemGroupId = $initExpenseItem->getProperty('expense_item_group_id');
        $links = static::getExpenseItemLinksFromExpenseItem($initExpenseItem);
        $remainingAmount = $netAmount;
        $expenseItems = array();
        for ($i = 0; $i < $proportionCount; $i++) {
            $proportion = $proportions[$i];
            if ($i != $proportionCount - 1) {
                $amount = (float) $netAmount * $proportion;
                $remainingAmount -= $amount;
            } else {
                $amount = $remainingAmount;
                $remainingAmount = 0;
            }
            if($i == 0){
                $expenseItem = $initExpenseItem;
            } else {
                $expenseItem = static::buildNewModel($typeRef, $inProgress);
            }
            $expenseItem->setProperty('expense_id', $expense->getId());
            $expenseItem->setProperty('net_amount', $amount);
            $expenseItem->setProperty('void', 0);
            $expenseItem->setProperty('cancelled', 0);
            $expenseItem->setProperty('expense_item_group_id', $expenseItemGroupId);
            $expenseItem->setProperty('tax_code_qb_id', $taxCodeQBId);
            if (!$expenseItem->save()) {
                return false;
            }
            
            if($i != 0){
                if(!static::linkExpenseItemToOtherExpenseItemLinks($expenseItem, $links)){
                    return false;
                }
            }
            $expenseItems[] = $expenseItem;
        }        
        return $expenseItems;
    }
    
    /**
     * @param AbstractExpenseItem $expenseItem
     * @return GI_DAO[]
     */
    public static function getExpenseItemLinksFromExpenseItem(AbstractExpenseItem $expenseItem){
        $expenseItemId = $expenseItem->getId();
        $search = new GI_DataSearch('item_link_to_expense_item');
        $search->filter('expense_item_id', $expenseItemId);
        $links = $search->select();
        return $links;
    }
    
    /**
     * @param AbstractExpenseItem $expenseItem
     * @param GI_DAO[] $links
     * @return boolean
     */
    public static function linkExpenseItemToOtherExpenseItemLinks(AbstractExpenseItem $expenseItem, $links = array()) {
        $expenseItemId = $expenseItem->getId();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        if (!$links) {
            return true;
        }
        foreach ($links as $link) {
            if ($link->getProperty('expense_item_id') == $expenseItemId) {
                continue;
            }
            $linkSearch = new GI_DataSearch('item_link_to_expense_item');
            $linkSearch->filter('table_name', $link->getProperty('table_name'))
                    ->filter('item_id', $link->getProperty('item_id'))
                    ->filter('expense_item_id', $expenseItemId);
            $results = $linkSearch->select();
            if (empty($results)) {
                $newLink = new $defaultDAOClass('item_link_to_expense_item');
                $newLink->setProperty('table_name', $link->getProperty('table_name'));
                $newLink->setProperty('item_id', $link->getProperty('item_id'));
                $newLink->setProperty('expense_item_id', $expenseItemId);
                if (!$newLink->save()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param AbstractExpenseItem $expenseItem
     * @param GI_DAO[] $links
     * @return boolean
     */
    public static function unlinkExpenseItemFromExpenseItemLinks(AbstractExpenseItem $expenseItem, $links = array()){
        $expenseItemId = $expenseItem->getId();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        if(!$links){
            return false;
        }
        foreach($links as $link){
            $modelId = $link->getProperty('item_id');
            $modelTableName = $link->getProperty('table_name');
            $expenseItemId = $expenseItem->getId();
            $existingLinkResult = $defaultDAOClass::getByProperties('item_link_to_expense_item', array(
                'table_name' => $modelTableName,
                'item_id' => $modelId,
                'expense_item_id' => $expenseItemId
            ));
            if (empty($existingLinkResult)) {
                return true;
            }
            foreach ($existingLinkResult as $existingLink) {
                if (!$existingLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function getExpenseItemWstTypeRefs() {
        return array(
            'wst'
        );
    }
    
    /**
     * @param AbstractExpense $expense
     * @param AbstractExpenseItem[] $mExpenseItems
     * @param float[] $proportions
     * @param GI_Model $linkedModel
     * @param boolean $copyLinks
     * @return AbstractExpenseItem[]
     */
    public static function transformMExpenseItemsIntoNExpenseItemsByProportion(AbstractExpense $expense, $mExpenseItems, $proportions, GI_Model $linkedModel, $copyLinks = true, $ignoreLinksFromTable = array()) {
        if (empty($mExpenseItems) || empty($proportions)) {
            return array();
        }
        $links = array();
        if($copyLinks){
        foreach($mExpenseItems as $mExpenseItem){
            $mLinks = static::getExpenseItemLinksFromExpenseItem($mExpenseItem);
                if(!empty($ignoreLinksFromTable)){
                    foreach($mLinks as $mLinkKey => $mLink){
                        $tableName = $mLink->getProperty('table_name');
                        if(in_array($tableName, $ignoreLinksFromTable)){
                            unset($mLinks[$mLinkKey]);
                        }
                    }
                }
            $links = array_merge($links, $mLinks);
        }
        }
        $firstMExpenseItem = $mExpenseItems[0];
        $taxCodeQBid = $firstMExpenseItem->getProperty('tax_code_qb_id');
        $expenseItemGroupId = $firstMExpenseItem->getProperty('expense_item_group_id');
        $nExpenseItems = array();
        $mExpenseItemSum = 0;
        $newItemTypeRef = $firstMExpenseItem->getTypeRef();
        foreach ($mExpenseItems as $mExpenseItem) {
            $mExpenseItemSum += $mExpenseItem->getProperty('net_amount');
        }
        $remainingSum = $mExpenseItemSum;
        $count = count($proportions);
        for ($i=0;$i<$count;$i++) {
            if (isset($mExpenseItems[$i])) {
                $nExpenseItem = $mExpenseItems[$i];
                unset($mExpenseItems[$i]);
            } else {
                $nExpenseItem = static::buildNewModel($newItemTypeRef);
                $nExpenseItem->setProperty('expense_id', $expense->getProperty('id'));
                $nExpenseItem->setProperty('applicable_date', $firstMExpenseItem->getProperty('applicable_date'));
                $nExpenseItem->setProperty('in_progress', 1);
                $nExpenseItem->setProperty('expense_item_group_id', $expenseItemGroupId);
                $nExpenseItem->setProperty('tax_code_qb_id', $taxCodeQBid);
            }
            if ($i != ($count - 1)) {
                $newNetAmount = (float) round(($mExpenseItemSum * $proportions[$i]),2);
                $remainingSum -= $newNetAmount;
            } else {
                $newNetAmount = $remainingSum;
                $remainingSum = 0;
            }
            $nExpenseItem->setProperty('net_amount', $newNetAmount);
            if (!($nExpenseItem->save() && static::linkExpenseItemToModel($linkedModel, $nExpenseItem) && static::linkExpenseItemToOtherExpenseItemLinks($nExpenseItem, $links))) {
                return NULL;
            }
            $nExpenseItems[] = $nExpenseItem;
        }
        if (!empty($mExpenseItems)) {
            foreach ($mExpenseItems as $mExpenseItem) {
                if (!(static::unlinkExpenseItemFromModel($linkedModel, $mExpenseItem) && static::unlinkExpenseItemFromExpenseItemLinks($mExpenseItem, $links) && $mExpenseItem->softDelete())) {
                    return NULL;
                }
            }
        }

        return $nExpenseItems;
    }

    /**
     * @param AbstractExpenseItemGroup $group
     * @return AbstractExpenseItem[]
     */
    public static function getModelArrayByExpenseItemGroup(AbstractExpenseItemGroup $group, $limit = NULL) {
        $search = static::search();
        $search->filter('expense_item_group_id', $group->getProperty('id'));
        if (!empty($limit)) {
            $search->setItemsPerPage($limit);
            $search->setPageNumber(1);
        }
        $search->orderBy('id');
        return $search->select();
    }

    public static function combineExpenseItemsByIds($expenseItemIds, &$resultString = ''){
        //get the total sum for all provided expense item ids
        $search = static::search()
                ->filterIn('id', $expenseItemIds);
        $sums = $search->sum('net_amount');
        $netAmountTot = $sums['net_amount'];
        
        //get the first expense item and set the new total
        $mainExpenseItemId = $expenseItemIds[0];
        $mainExpenseItem = static::getModelById($mainExpenseItemId);
        if(!$mainExpenseItem){
            return false;
        }
        
        $mainExpenseItem->setProperty('net_amount', $netAmountTot);
        
        //remove the first expense item from the array so it doesn't get deleted
        unset($expenseItemIds[0]);
        
        //delete the expense item links
        $deleteLinkSearch = new GI_DataSearch('item_link_to_expense_item');
        $deleteLinkSearch->filterIn('expense_item_id', $expenseItemIds);
        $resultLinkString = '';
        $deleteLinkSearch->massUpdate(array(
            'status' => 0
        ),$resultLinkString,true,true);
        
        //delete the expense items
        $deleteSearch = static::search()
                ->filterIn('id', $expenseItemIds);
        $resultEIString = '';
        $deleteSearch->massUpdate(array(
            'status' => 0
        ),$resultEIString,true,true);
        
        $resultString = 'LINKS:' . $resultLinkString . 'ITEMS:' . $resultEIString;
        
        //save the final expense item with the full total
        return $mainExpenseItem->save();
    }

}
