<?php
/**
 * Description of AbstractExpenseFactory
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractExpenseFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'expense';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'bill':
                $model = new ExpenseBill($map);
                break;
            case 'expense':
            default:
                $model = new Expense($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'bill':
                $typeRefs = array('bill');
                break;
            case 'expense':
                $typeRefs = array('expense');
                break;
            case 'external':
                $typeRefs = array('external');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractExpense
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    
   /**
     * @param String $typeRef
     * @return AbstractExpense
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }

    public static function buildNewExpenseModelAndLinkToModel(GI_Model $model, $newExpenseTypeRef = 'expense', $currencyRef = 'usd') {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $expense = ExpenseFactory::buildNewModel($newExpenseTypeRef);
        $currency = CurrencyFactory::getModelByRef($currencyRef);
        if (!empty($expense) && !empty($currency)) {
            $currecnyId = $currency->getProperty('id');
            $expense->setProperty('date', GI_Time::getDate());
            $expense->setProperty('sortable_net', 0);
            $expense->setProperty('sortable_balance', 0);
            $expense->setProperty('currency_id', $currecnyId);
            if (!$expense->save()) {
                return NULL;
            }
            $expenseId = $expense->getProperty('id');
            $newLink = new $defaultDAOClass('item_link_to_expense');
            $newLink->setProperty('table_name', $modelTableName);
            $newLink->setProperty('item_id', $modelId);
            $newLink->setProperty('expense_id', $expenseId);
            if ($newLink->save()) {
                return $expense;
            }
        }
        return NULL;
    }

    public static function getExpenseArrayFromLinkedModel(GI_Model $model, $expenseTypeRef = NULL) {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseTableName = dbConfig::getDbPrefix() . 'expense';
        $expensesSearch = ExpenseFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->join('item_link_to_expense', 'expense_id', $expenseTableName, 'id', 'ilte')
                ->filter('ilte.table_name', $modelTableName)
                ->filter('ilte.item_id', $modelId);
        if (!empty($expenseTypeRef)) {
            $expensesSearch->filterByTypeRef($expenseTypeRef);
        }
        $expenses = $expensesSearch->select();
        return $expenses;
    }

    public static function linkModelAndExpenseModel(GI_Model $model, AbstractExpense $expense) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseId = $expense->getProperty('id');
        $expenseTableName = dbConfig::getDbPrefix() . 'expense';
        $expenses = ExpenseFactory::search()
                ->join('item_link_to_expense', 'expense_id', $expenseTableName, 'id', 'ilte')
                ->filter('ilte.table_name', $modelTableName)
                ->filter('ilte.item_id', $modelId)
                ->filter('ilte.expense_id', $expenseId)
                ->select();
        if (!empty($expenses)) {
            return true;
        }
        $newLink = new $defaultDAOClass('item_link_to_expense');
        $newLink->setProperty('item_id', $modelId);
        $newLink->setProperty('table_name', $modelTableName);
        $newLink->setProperty('expense_id', $expenseId);
        return $newLink->save();
    }

    public static function unlinkModelAndExpenseModel(GI_Model $model, AbstractExpense $expense) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $expenseId = $expense->getProperty('id');
        $links = $defaultDAOClass::getByProperties('item_link_to_expense', array(
                    'table_name' => $modelTableName,
                    'item_id' => $modelId,
                    'expense_id' => $expenseId
        ));
        if (empty($links)) {
            return true;
        }
        foreach ($links as $linkDAO) {
            if (!$linkDAO->softDelete()) {
                return false;
            }
        }
        return true;
    }

    public static function getExpenseSumByLinkedModel(GI_Model $model, AbstractExpense $expense = NULL, $requestedCurrencyRef = 'usd', $withSymbols = false) {
        return ExpenseItemFactory::getExpenseSumByLinkedModel($model, $expense, $requestedCurrencyRef, $withSymbols);
    }

    
    public static function getAllTaxRateQBIdsFromExpenses(DateTime $start = NULL, DateTime $end = NULL) {
        $taxCodeIds = array();
        $search = ExpenseItemFactory::search();
        $date = NULL;
        if (!empty($start)) {
            $date = $start;
            $start->modify("-1 seconds");
            $search->filterGreaterThan('applicable_date', $start->format('Y-m-d H:i:s'));
        }
        if (!empty($end)) {
            $date = $end;
            $end->modify("+1 seconds");
            $search->filterLessThan('applicable_date', $end->format('Y-m-d H:i:s'));
        }
        $search->groupBy('tax_code_qb_id')
                ->orderBy('tax_code_qb_id', 'ASC');
        $expenseItems = $search->select();
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                $taxCodeIds[] = $expenseItem->getProperty('tax_code_qb_id');
            }
        }
        $taxRateIds = array();
        if (!empty($taxCodeIds)) {
            foreach ($taxCodeIds as $taxCodeId) {
                $ratesArray = QBTaxRateFactory::getRatesDataFromTaxCodeData($taxCodeId, $date, 'purchase');
                if (!empty($ratesArray)) {
                    foreach ($ratesArray as $rateId => $rateArray) {
                        if (!in_array($rateId, $taxRateIds)) {
                            $taxRateIds[] = $rateId;
                        }
                    }
                }
            }
        }
        
        return $taxRateIds;
    }

    public static function linkExpenseAndTag(AbstractExpense $expense, AbstractTag $tag) {
        $existingLinkSearch = new GI_DataSearch('expense_link_to_tag');
        $existingLinkSearch->filter('expense_id', $expense->getProperty('id'))
                ->filter('tag_id', $tag->getProperty('id'));
        $existingLinkArray = $existingLinkSearch->select();
        if (!empty($existingLinkArray)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('expense_link_to_tag');
        $softDeletedSearch->filter('expense_id', $expense->getProperty('id'))
                ->filter('tag_id', $tag->getProperty('id'))
                ->setAutoStatus(false)
                ->filter('status', 0);
        $softDeletedArray = $softDeletedSearch->select();
        if (!empty($softDeletedArray)) {
            $softDeletedLink = $softDeletedArray[0];
            $softDeletedLink->setProperty('status', 1);
            if ($softDeletedLink->save()) {
                return true;
            }
        }
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('expense_link_to_tag');
        $newLink->setProperty('expense_id', $expense->getProperty('id'));
        $newLink->setProperty('tag_id', $tag->getProperty('id'));
        return $newLink->save();
    }
    
    public static function unlinkExpenseAndTag(AbstractExpense $expense, AbstractTag $tag) {
        $existingLinkSearch = new GI_DataSearch('expense_link_to_tag');
        $existingLinkSearch->filter('expense_id', $expense->getProperty('id'))
                ->filter('tag_id', $tag->getProperty('id'));
        $existingLinkArray = $existingLinkSearch->select();
        if (empty($existingLinkArray)) {
            return true;
        }
        $existingLink = $existingLinkArray[0];
        return $existingLink->softDelete();
    }

    /**
     * Ensures that $tags are the only accounting location tags linked to $expense
     * @param AbstractExpense $expense
     * @param AbstractTag[] $tags
     * @return boolean
     */
    public static function adjustExpenseAccountingLocationTags(AbstractExpense $expense, $tags) {
        $indexedTags = array();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $indexedTags[$tag->getProperty('id')] = $tag;
            }
        }
        $tagTableName = TagFactory::getDbPrefix() . 'tag';
        $tagSearch = TagFactory::search();
        $tagSearch->filterByTypeRef('accounting_loc')
                ->join('expense_link_to_tag', 'tag_id', $tagTableName, 'id', 'ELTT')
                ->filter('ELTT.expense_id', $expense->getProperty('id'));
        $existingLinkedTags = $tagSearch->select();
        
        if (!empty($existingLinkedTags)) {
            foreach ($existingLinkedTags as $existingLinkedTag) {
                $tagId = $existingLinkedTag->getProperty('id');
                if (!isset($indexedTags[$tagId])) {
                    if (!static::unlinkExpenseAndTag($expense, $existingLinkedTag)) {
                        return false;
                    }
                } else {
                    unset($indexedTags[$tagId]);
                }
            }
        }
        
        if (!empty($indexedTags)) {
            foreach ($indexedTags as $indexedTag) {
                if (!static::linkExpenseAndTag($expense, $indexedTag)) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Get expense array that are not linked to a bill
     * @param int $orderId
     * @param string $oheTypeRef : order_has_expense type
     * @param int $contactId
     * @return AbstractExpense[]
     */
    public static function getExpenseArrayUnlinkedToBill($orderId, $oheTypeRef = NULL, $contactId = NULL) {
        $expenseTableName = ExpenseFactory::getDbPrefix() . 'expense';
        $expenseSearch = ExpenseFactory::search()
                ->join('order_has_expense', 'expense_id', $expenseTableName, 'id', 'OHE')
                ->join('order_has_expense_type', 'id', 'OHE', 'order_has_expense_type_id', 'OHET')
                ->leftJoin('item_link_to_expense', 'expense_id', $expenseTableName, 'id', 'ILTE')
                ->filter('OHE.order_id', $orderId)
                ->filter('cancelled', 0)
                ->filter('void', 0)
                ->filterNull('ILTE.status');
        if (!empty($oheTypeRef)) {
            $expenseSearch->filter('OHET.ref', $oheTypeRef);
        }
        
        if (!empty($contactId)) {
            $expenseSearch->filter('OHE.contact_id', $contactId);
        }
        $results = $expenseSearch->select();
        return $results;
    }
    
    /**
     * Get expense by order line model
     * @param GI_Model $model
     * @param type $expenseTypeRef
     * @return type
     */
    public static function getExpenseFromOrderLineModel(GI_Model $model) {
        $expenseItems = ExpenseItemFactory::getExpenseItemsByLinkedModel($model);
        if (!empty($expenseItems)) {
            //All expense items associated with one order line has the same expense id
            $expenseId = $expenseItems[0]->getProperty('expense_id');
            return ExpenseFactory::getModelById($expenseId);
        }
        
        return NULL;
    }
}
