<?php
/**
 * Description of AbstractIncomeFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractIncomeFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'income';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'invoice':
                $model = new IncomeInvoice($map);
                break;
            case 'income':
            default:
                $model = new Income($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'income':
                $typeRefs = array('income');
                break;
            case 'invoice':
                $typeRefs = array('invoice');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    public static function buildNewIncomeModelAndLinkToModel(GI_Model $model, $newIncomeTypeRef = 'income', $currencyRef = 'usd') {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $income = IncomeFactory::buildNewModel($newIncomeTypeRef);
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return NULL;
        }
        $currency = $currencyArray[0];
        if (!empty($income) && !empty($currency)) {
            $currencyId = $currency->getProperty('id');
            $income->setProperty('currency_id', $currencyId);
            $income->setProperty('date', GI_Time::getDate());
            $income->setProperty('sortable_net', 0);
            $income->setProperty('sortable_balance', 0);
            if (!$income->save()) {
                return NULL;
            }
            $incomeId = $income->getProperty('id');
            $newLink = new $defaultDAOClass('item_link_to_income');
            $newLink->setProperty('table_name', $modelTableName);
            $newLink->setProperty('item_id', $modelId);
            $newLink->setProperty('income_id', $incomeId);
            if ($newLink->save()) {
                return $income;
            }
        }
        return NULL;
    }

    /*
     * @return Income[]
     */
    public static function getIncomeArrayFromLinkedModel(GI_Model $model, $incomeTypeRef = NULL, $includeVoid = false, $includeCancelled = false) {
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeTableName = dbConfig::getDbPrefix() . 'income';
        $incomesSearch = IncomeFactory::search()
                ->join('item_link_to_income', 'income_id', $incomeTableName, 'id', 'ilti')
                ->filter('ilti.table_name', $modelTableName)
                ->filter('ilti.item_id', $modelId);
        if (!empty($incomeTypeRef)) {
            $incomesSearch->filterByTypeRef($incomeTypeRef);
        }
        if (!$includeVoid) {
            $incomesSearch->filter('void', 0);
        }
        if (!$includeCancelled) {
            $incomesSearch->filter('cancelled', 0);
        }
        $incomes = $incomesSearch->select();
        return $incomes;
    }

    public static function linkModelAndIncomeModel(GI_Model $model, AbstractIncome $income) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeId = $income->getProperty('id');
        $incomeTableName = dbConfig::getDbPrefix() . 'income';
        $incomes = IncomeFactory::search()
                ->join('item_link_to_income', 'income_id', $incomeTableName, 'id', 'ilti')
                ->filter('ilti.table_name', $modelTableName)
                ->filter('ilti.item_id', $modelId)
                ->filter('ilti.income_id', $incomeId)
                ->select();
        if (!empty($incomes)) {
            return true;
        }
        $newLink = new $defaultDAOClass('item_link_to_income');
        $newLink->setProperty('item_id', $modelId);
        $newLink->setProperty('table_name', $modelTableName);
        $newLink->setProperty('income_id', $incomeId);
        return $newLink->save();
    }
    
    public static function unlinkModelAndIncomeModel(GI_Model $model, AbstractIncome $income) {
        $defaultDAOClass = static::getStaticPropertyValueFromChild('defaultDAOClass');
        $modelId = $model->getProperty('id');
        $modelTableName = $model->getTableName();
        $incomeId = $income->getProperty('id');
        $links = $defaultDAOClass::getByProperties('item_link_to_income', array(
                    'table_name' => $modelTableName,
                    'item_id' => $modelId,
                    'income_id' => $incomeId
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

    public static function linkIncomeAndTag(AbstractIncome $income, AbstractTag $tag) {
        $existingLinkSearch = new GI_DataSearch('income_link_to_tag');
        $existingLinkSearch->filter('income_id', $income->getProperty('id'))
                ->filter('tag_id', $tag->getProperty('id'));
        $existingLinkArray = $existingLinkSearch->select();
        if (!empty($existingLinkArray)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('income_link_to_tag');
        $softDeletedSearch->filter('income_id', $income->getProperty('id'))
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
        $newLink = new $defaultDAOClass('income_link_to_tag');
        $newLink->setProperty('income_id', $income->getProperty('id'));
        $newLink->setProperty('tag_id', $tag->getProperty('id'));
        return $newLink->save();
    }

    public static function unlinkIncomeAndTag(AbstractIncome $income, AbstractTag $tag) {
        $existingLinkSearch = new GI_DataSearch('income_link_to_tag');
        $existingLinkSearch->filter('income_id', $income->getProperty('id'))
                ->filter('tag_id', $tag->getProperty('id'));
        $existingLinkArray = $existingLinkSearch->select();
        if (empty($existingLinkArray)) {
            return true;
        }
        $existingLink = $existingLinkArray[0];
        return $existingLink->softDelete();
    }
    
        /**
     * Ensures that $tags are the only accounting location tags linked to $income
     * @param AbstractIncome $income
     * @param AbstractTag[] $tags
     * @return boolean
     */
    public static function adjustIncomeAccountingLocationTags(AbstractIncome $income, $tags) {
        $indexedTags = array();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if(!empty($tag)){
                    $indexedTags[$tag->getProperty('id')] = $tag;
                }
            }
        }
        $tagTableName = TagFactory::getDbPrefix() . 'tag';
        $tagSearch = TagFactory::search();
        $tagSearch->filterByTypeRef('accounting_loc')
                ->join('income_link_to_tag', 'tag_id', $tagTableName, 'id', 'ILTT')
                ->filter('ILTT.income_id', $income->getProperty('id'));
        $existingLinkedTags = $tagSearch->select();

        if (!empty($existingLinkedTags)) {
            foreach ($existingLinkedTags as $existingLinkedTag) {
                $tagId = $existingLinkedTag->getProperty('id');
                if (!isset($indexedTags[$tagId])) {
                    if (!static::unlinkIncomeAndTag($income, $existingLinkedTag)) {
                        return false;
                    }
                } else {
                    unset($indexedTags[$tagId]);
                }
            }
        }
        if (!empty($indexedTags)) {
            foreach ($indexedTags as $indexedTag) {
                if (!static::linkIncomeAndTag($income, $indexedTag)) {
                    return false;
                }
            }
        }
        return true;
    }
    
        
    /**
     * Get invoice array that are not linked to a invoice
     * @param type $orderId
     * @param type $contactId
     * @return type
     */
    public static function getIncomeArrayUnlinkedToInvoice($orderId, $contactId = NULL) {
        $incomeTableName = IncomeFactory::getDbPrefix() . 'income';
        $incomeSearch = IncomeFactory::search()
                ->join('order_has_income', 'income_id', $incomeTableName, 'id', 'OHI')
                ->leftJoin('item_link_to_income', 'income_id', $incomeTableName, 'id', 'ILTI')
                ->filter('OHI.order_id', $orderId)
                ->filter('cancelled', 0)
                ->filter('void', 0)
                ->filterNull('ILTI.status');
        if (!empty($contactId)) {
            $incomeSearch->filter('OHI.contact_id', $contactId);
        }
        return $incomeSearch->select();
    }
    
    /**
     * Get income by order line model
     * @param GI_Model $model
     * @return type
     */
    public static function getIncomeFromOrderLineModel(GI_Model $model) {
        $incomeItems = IncomeItemFactory::getIncomeItemsByLinkedModel($model);
        if (!empty($incomeItems)) {
            //All income items associated with one order line has the same income id
            $incomeId = $incomeItems[0]->getProperty('income_id');
            return IncomeFactory::getModelById($incomeId);
        }

        return NULL;
    }

    public static function getAllTaxRateQBIdsFromIncomes(DateTime $start = NULL, DateTime $end = NULL) {
        $taxCodeIds = array();
        $search = IncomeItemFactory::search();
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
        $incomeItems = $search->select();
        if (!empty($incomeItems)) {
            foreach ($incomeItems as $incomeItem) {
                $taxCodeIds[] = $incomeItem->getProperty('tax_code_qb_id');
            }
        }
        $taxRateIds = array();
        if (!empty($taxCodeIds)) {
            foreach ($taxCodeIds as $taxCodeId) {
                $ratesArray = QBTaxRateFactory::getRatesDataFromTaxCodeData($taxCodeId, $date, 'sales');
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

}
