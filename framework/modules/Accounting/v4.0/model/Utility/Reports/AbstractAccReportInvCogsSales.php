<?php
/**
 * Description of AbstractAccReportInvCogsSales
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    3.0.1
 */
abstract class AbstractAccReportInvCogsSales extends AbstractAccReport {

    protected $tags = NULL;
    protected $invTypeRefs = array();
    protected $cogsTypeRefs = array();
    protected $wstTypeRefs = array();
    protected $salesTypeRefs = array();
    protected $ecoFeeTypeRefs = array();
    
    protected $invTotal = 0;
    protected $inTransitInvTotal = 0;
    protected $cogsTotal = 0;
    protected $wstTotal = 0;
    protected $salesTotal = 0;
    protected $ecoFeesTotal = 0;

    public function __construct($typeRef, DateTime $startDate, DateTime $endDate) {
        parent::__construct($typeRef, $startDate, $endDate);
        $this->invTypeRefs = ExpenseItemFactory::getTypesArray('inv', false, 'title');
        $this->cogsTypeRefs = ExpenseItemFactory::getTypesArray('cogs', false, 'title');
        $this->wstTypeRefs = ExpenseItemFactory::getExpenseItemWstTypeRefs();
        $this->salesTypeRefs = IncomeItemFactory::getTypesArray('sales', false, 'title');
        $this->ecoFeeTypeRefs = IncomeItemFactory::getIncomeItemEcoFeeTypeRefs();
    }
    
    public function getInvTotal() {
        return $this->invTotal;
    }
    
    public function getInvInTransitTotal() {
        return $this->inTransitInvTotal;
    }
    
    public function getCogsTotal() {
        return $this->cogsTotal;
    }
    
    public function getWstTotal() {
        return $this->wstTotal;
    }
    
    public function getSalesTotal() {
        return $this->salesTotal;
    }
    
    public function getEcoFeesTotal() {
        return $this->ecoFeesTotal;
    }

    public function setInvTypeRefs($invTypeRefs) {
        $this->invTypeRefs = $invTypeRefs;
    }

    public function setCogsTypeRefs($cogsTypeRefs) {
        $this->cogsTypeRefs = $cogsTypeRefs;
    }

    public function setWstTypeRefs($wstTypeRefs) {
        $this->wstTypeRefs = $wstTypeRefs;
    }

    public function setSalesTypeRefs($salesTypeRefs) {
        $this->salesTypeRefs = $salesTypeRefs;
    }

    public function setEcoFeeTypeRefs($ecoFeeTypeRefs) {
        $this->ecoFeeTypeRefs = $ecoFeeTypeRefs;
    }

    public function getTitle() {
        return 'Inventory/COGS/Sales by Category';
    }

    public function getDescription() {
        return 'Shows detailed breakdowns of inventory valuation, COGS, and Sales, by category';
    }

    public function getColour() {
        return '1DA32A';
    }

    public function getInitials() {
        return 'IC';
    }

    public function getDetailView() {
        return new AccReportInvCogsSalesDetailView($this);
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            if (!$this->setInvProperties()) {
                return false;
            }
            if (!$this->setCOGSProperties()) {
                return false;
            }
            if (!$this->setWasteProperties()) {
                return false;
            }
            if (!$this->setSalesProperties()) {
                return false;
            }
            if (!$this->setEcoFeeProperties()) {
                return false;
            }
            $this->reportBuilt = true;
        }
        return true;
    }

    protected function setInvProperties() {
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (empty($primaryCurrency) || empty($secondaryCurrency)) {
            return false;
        }
        $primaryCurrencyProperties = $this->calculateInvProperties($primaryCurrency);
        $secondaryCurrencyProperties = $this->calculateInvProperties($secondaryCurrency);
        if (!empty($secondaryCurrencyProperties)) {
            foreach ($secondaryCurrencyProperties as $key=>$value) {
                $secondaryCurrencyProperties[$key] = $primaryCurrency->convertToThis($value, $secondaryCurrency);
            }
            $primaryCurrencyProperties = GI_Math::mergeAndAddArrays($primaryCurrencyProperties, $secondaryCurrencyProperties);
        }
        $this->properties['inv'] = $primaryCurrencyProperties;
        
        $this->calculateInTransitInvTotal();
        
        return true;
    }

    protected function calculateInvProperties(AbstractCurrency $currency) {
        $currencyRef = $currency->getProperty('ref');
        $invProperties = array();
        foreach ($this->invTypeRefs as $invTypeRef => $invTypeName) {
            $subTotal = $this->getSubTotalInventoryExpenses($invTypeRef, NULL, $currencyRef, NULL, $this->getEndDate(), false, $this->tags);
            $cogsTypeRef = ExpenseItemFactory::convertInvTypeRefToCogsTypeRef($invTypeRef);
            $invFutureCogsSubTotal = 0;
            if (!empty($cogsTypeRef) && !empty($this->getEndDate())) {
                $invFutureCogsSubTotal = $this->getInvFutureConvertedToCOGSSubTotal($cogsTypeRef, NULL, $currencyRef, NULL, $this->getEndDate(), $this->tags);
            }
            $wstTypeRef = GI_StringUtils::replaceFirst('inv', 'wst', $invTypeRef);
            $invFutureWasteSubTotal = 0;
            if (!empty($wstTypeRef) && !empty($this->getEndDate())) {
                $invFutureWasteSubTotal = $this->getInvFutureConvertedToWstSubTotal($wstTypeRef, NULL, $currencyRef, NULL, $this->getEndDate(), $this->tags);
            }
            $value = $subTotal + $invFutureCogsSubTotal + $invFutureWasteSubTotal;
            $invProperties[$invTypeRef] = $value;
            $this->invTotal += $value;
        }
        return $invProperties;
    }
    
    protected function calculateInTransitInvTotal() {
        $invInTransit = 0;
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (!empty($primaryCurrency) && !empty($secondaryCurrency)) {
            $primaryCurrencyTotal = $this->calculateInTransitInvTotalByCurrency($primaryCurrency, $this->getStartDate(), $this->getEndDate());
            $secondaryCurrencyTotal = $this->calculateInTransitInvTotalByCurrency($secondaryCurrency, $this->getStartDate(), $this->getEndDate());
            $convertedSecondaryCurrencyTotal = 0;
            if (!empty($secondaryCurrencyTotal)) {
                $convertedSecondaryCurrencyTotal = $primaryCurrency->convertToThis($secondaryCurrencyTotal, $secondaryCurrency);
            }
            $invInTransit = $primaryCurrencyTotal + $convertedSecondaryCurrencyTotal;
        }
        $this->inTransitInvTotal = $invInTransit;
    }
    
    public function calculateInTransitInvTotalByCurrency(AbstractCurrency $currency, DateTime $startDate = NULL, DateTime $endDate = NULL) {
        $total = 0;
        $tableName = ExpenseItemFactory::getDbPrefix() . 'expense_item';
        $search = ExpenseItemFactory::search();
        $search->join('expense', 'id', $tableName, 'expense_id', 'EXP')
                ->filter('EXP.currency_id', $currency->getId())
                ->filter('in_progress', 0);

        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }

        $join1 = $search->createJoin('item_link_to_expense_item', 'expense_item_id', $tableName, 'id', 'ILTEI1', 'left');
        $join1->filter('ILTEI1.table_name', 'inv_stock');

        $search->filterNull('ILTEI1.status');

        $search->ignoreStatus('ILTEI1');
        $join2 = $search->createJoin('item_link_to_expense_item', 'expense_item_id', $tableName, 'id', 'ILTEI2');
        $join2->filter('ILTEI2.table_name', 'bill_line');

        $search->join('bill_line', 'id', 'ILTEI2', 'item_id', 'BL')
                ->join('bill', 'id', 'BL', 'bill_id', 'BILL')
                ->filterNotNull('BILL.quickbooks_export_date');
        $sumArray = $search->sum(array('net_amount'));
        if (isset($sumArray['net_amount'])) {
            $total = $sumArray['net_amount'];
        }
        return $total;
    }

    public function getSubTotalInventoryExpenses($expenseItemInvTypeRef = NULL, $expenseId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $includeInProgress = true, $tags = array(), $general = false) {
        $search = ExpenseItemFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if (empty($expenseItemInvTypeRef)) {
            $expenseItemInvTypeRef = 'inv';
            $general = true;
        }
        $search->filterByTypeRef($expenseItemInvTypeRef, $general);
        if (!empty($expenseId)) {
            $search->filter('expense_id', $expenseId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $search->join('expense', 'id', $expenseItemTableName, 'expense_id', 'e');
        $search->filter('e.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }
        if (!$includeInProgress) {
            $search->filter('in_progress', 0);
        }
        if (!empty($tags)) {
            $search->join('expense_link_to_tag', 'expense_id', 'e', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $sum = 0;
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            $sum = $sumArray['net_amount'];
        }
        return $sum;
    }

    public function getInvFutureConvertedToCOGSSubTotal($expenseItemCOGSTypeRef = NULL, $expenseId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $tags = array(), $general = false) {
        $search = ExpenseItemFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if (empty($expenseItemCOGSTypeRef)) {
            $expenseItemCOGSTypeRef = 'cogs';
            $general = true;
        }
        $search->filterByTypeRef($expenseItemCOGSTypeRef, $general);
        if (!empty($expenseId)) {
            $search->filter('expense_id', $expenseId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $search->join('expense', 'id', $expenseItemTableName, 'expense_id', 'e');
        $search->filter('e.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('e.applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('e.applicable_date', $endDateSearchable);
            $search->filterGreaterThan('applicable_date', $endDateSearchable);
        }
        if (!empty($tags)) {
            $search->join('expense_link_to_tag', 'expense_id', 'e', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $search->filter('in_progress', 0);
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            return $sumArray['net_amount'];
        } else {
            return NULL;
        }
    }

    public function getInvFutureConvertedToWstSubTotal($expenseItemWstTypeRef = NULL, $expenseId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $tags = array(), $general = false) {
        $search = ExpenseItemFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if (empty($expenseItemWstTypeRef)) {
            $expenseItemWstTypeRef = 'wst';
            $general = true;
        }
        $search->filterByTypeRef($expenseItemWstTypeRef, $general);
        if (!empty($expenseId)) {
            $search->filter('expense_id', $expenseId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $search->join('expense', 'id', $expenseItemTableName, 'expense_id', 'e');
        $search->filter('e.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('e.applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('e.applicable_date', $endDateSearchable);
            $search->filterGreaterThan('applicable_date', $endDateSearchable);
        }
        if (!empty($tags)) {
            $search->join('expense_link_to_tag', 'expense_id', 'e', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $search->filter('in_progress', 0);
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            return $sumArray['net_amount'];
        } else {
            return NULL;
        }
    }

    protected function setCOGSProperties() {
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (empty($primaryCurrency) || empty($secondaryCurrency)) {
            return false;
        }
        $primaryCurrencyProperties = $this->calculateCOGSProperties($primaryCurrency);
        $secondaryCurrencyProperties = $this->calculateCOGSProperties($secondaryCurrency);
        if (!empty($secondaryCurrencyProperties)) {
            foreach ($secondaryCurrencyProperties as $key=>$value) {
                $secondaryCurrencyProperties[$key] = $primaryCurrency->convertToThis($value, $primaryCurrency);
            }
            $primaryCurrencyProperties = GI_Math::mergeAndAddArrays($primaryCurrencyProperties, $secondaryCurrencyProperties);
        }
        $this->properties['cogs'] = $primaryCurrencyProperties;
        return true;
    }

    protected function calculateCOGSProperties(AbstractCurrency $currency) {
        $currencyRef = $currency->getProperty('ref');
        $cogsProperties = array();
        foreach ($this->cogsTypeRefs as $cogsTypeRef => $cogsTypeName) {
            $value = $this->getSubTotalCOGSExpenses($cogsTypeRef, NULL, $currencyRef, $this->getStartDate(), $this->getEndDate(), false, $this->tags);
            $cogsProperties[$cogsTypeRef] = $value;
            $this->cogsTotal += $value;
        }
        return $cogsProperties;
    }

    public function getSubTotalCOGSExpenses($expenseItemCOGSTypeRef = NULL, $expenseId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $includeInProgress = true, $tags = array(), $general = false) {
        $search = ExpenseItemFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if (empty($expenseItemCOGSTypeRef)) {
            $expenseItemCOGSTypeRef = 'cogs';
            $general = true;
        }
        $search->filterByTypeRef($expenseItemCOGSTypeRef, $general);

        if (!empty($expenseId)) {
            $search->filter('expense_id', $expenseId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $search->join('expense', 'id', $expenseItemTableName, 'expense_id', 'e');
        $search->filter('e.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }
        if (!$includeInProgress) {
            $search->filter('in_progress', 0);
        }
        if (!empty($tags)) {
            $search->join('expense_link_to_tag', 'expense_id', 'e', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            return $sumArray['net_amount'];
        } else {
            return NULL;
        }
    }

    protected function setWasteProperties() {
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (empty($primaryCurrency) || empty($secondaryCurrency)) {
            return false;
        }
        $primaryCurrencyProperties = $this->calculateWasteProperties($primaryCurrency);
        $secondaryCurrencyProperties = $this->calculateWasteProperties($secondaryCurrency);
        if (!empty($secondaryCurrencyProperties)) {
            foreach ($secondaryCurrencyProperties as $key=>$value) {
                $secondaryCurrencyProperties[$key] = $primaryCurrency->convertToThis($value, $secondaryCurrency);
            }
            $primaryCurrencyProperties = GI_Math::mergeAndAddArrays($primaryCurrencyProperties, $secondaryCurrencyProperties);
        }
        $this->properties['wst'] = $primaryCurrencyProperties;
        return true;
    }

    protected function calculateWasteProperties(AbstractCurrency $currency) {
        $currencyRef = $currency->getProperty('ref');
        $wstProperties = array();
        if (!empty($this->wstTypeRefs)) {
            foreach ($this->wstTypeRefs as $wstTypeRef) {
                $wstModel = ExpenseItemFactory::buildNewModel($wstTypeRef);
                if (!empty($wstModel)) {
                    $value = $this->getSubTotalWasteExpenses($wstTypeRef, NULL, $currencyRef, $this->getStartDate(), $this->getEndDate(), false, $this->tags, false);
                    $wstProperties[$wstTypeRef] = $value;
                    $this->wstTotal += $value;
                }
            }
        }
        return $wstProperties;
    }

    public function getSubTotalWasteExpenses($expenseItemWstTypeRef = NULL, $expenseId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $includeInProgress = true, $tags = array(), $general = false) {
        $search = ExpenseItemFactory::search()
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if (empty($expenseItemWstTypeRef)) {
            $expenseItemWstTypeRef = 'wst';
            $general = true;
        }
        $search->filterByTypeRef($expenseItemWstTypeRef, $general);
        if (!empty($expenseId)) {
            $search->filter('expense_id', $expenseId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $expenseItemTableName = dbConfig::getDbPrefix() . 'expense_item';
        $search->join('expense', 'id', $expenseItemTableName, 'expense_id', 'e');
        $search->filter('e.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }
        if (!$includeInProgress) {
            $search->filter('in_progress', 0);
        }
        if (!empty($tags)) {
            $search->join('expense_link_to_tag', 'expense_id', 'e', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $sum = 0;
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            $sum = $sumArray['net_amount'];
        }
        return $sum;
    }

    protected function setSalesProperties() {
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (empty($primaryCurrency) || empty($secondaryCurrency)) {
            return false;
        }
        $primaryCurrencyProperties = $this->calculateSalesProperties($primaryCurrency);
        $secondaryCurrencyProperties = $this->calculateSalesProperties($secondaryCurrency);
        if (!empty($secondaryCurrencyProperties)) {
            foreach ($secondaryCurrencyProperties as $key=>$value) {
                $secondaryCurrencyProperties[$key] = $primaryCurrency->convertToThis($value, $secondaryCurrency);
            }
           $primaryCurrencyProperties = GI_Math::mergeAndAddArrays($primaryCurrencyProperties, $secondaryCurrencyProperties);
        }
        $this->properties['sales'] = $primaryCurrencyProperties;
        return true;
    }

    protected function calculateSalesProperties(AbstractCurrency $currency) {
        $currencyRef = $currency->getProperty('ref');
        $salesProperties = array();
        foreach ($this->salesTypeRefs as $salesTypeRef => $salesTypeName) {
            $value = $this->getSubTotalSales($salesTypeRef, NULL, $currencyRef, $this->startDate, $this->endDate, false, $this->tags);
            $salesProperties[$salesTypeRef] = $value;
            $this->salesTotal += $value;
        }
        return $salesProperties;
    }

    public function getSubTotalSales($incomeItemSalesTypeRef = NULL, $incomeId = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $includeInProgress = true, $tags = array(), $general = false) {
        $search = IncomeItemFactory::search()
                ->filter('cancelled', 0)
                ->filter('void', 0);
        if (empty($incomeItemSalesTypeRef)) {
            $incomeItemSalesTypeRef = 'sales';
            $general = true;
        }
        $search->filterByTypeRef($incomeItemSalesTypeRef, $general);
        if (!empty($incomeId)) {
            $search->filter('income_id', $incomeId);
        }
        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $incomeItemTableName = dbConfig::getDbPrefix() . 'income_item';
        $search->join('income', 'id', $incomeItemTableName, 'income_id', 'i');
        $search->filter('i.void', 0)
                ->filter('i.cancelled', 0);
        $search->filter('i.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }
        if (!$includeInProgress) {
            $search->filter('in_progress', 0);
        }
        if (!empty($tags)) {
            $search->join('income_link_to_tag', 'income_id', 'i', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            return $sumArray['net_amount'];
        }
        return NULL;
    }

    protected function setEcoFeeProperties() {
        $primaryCurrency = $this->getCurrency();
        $secondaryCurrency = $this->getSecondaryCurrency();
        if (empty($primaryCurrency) || empty($secondaryCurrency)) {
            return false;
        }
        $primaryCurrencyProperties = $this->calculateEcoFeeProperties($primaryCurrency);
        $secondaryCurrencyProperties = $this->calculateEcoFeeProperties($secondaryCurrency);
        if (!empty($secondaryCurrencyProperties)) {
            foreach ($secondaryCurrencyProperties as $key=>$value) {
                $secondaryCurrencyProperties[$key] = $primaryCurrency->convertToThis($value, $secondaryCurrency);
            }
            $primaryCurrencyProperties = GI_Math::mergeAndAddArrays($primaryCurrencyProperties, $secondaryCurrencyProperties);
        }
        
        $this->properties['eco_fees'] = $primaryCurrencyProperties;
        return true;
    }

    protected function calculateEcoFeeProperties(AbstractCurrency $currency) {
        $ecoFeeProperties = array();
        if (!empty($this->ecoFeeTypeRefs)) {
            $currencyRef = $currency->getProperty('ref');
            foreach ($this->ecoFeeTypeRefs as $ecoFeeTypeRef) {
                $ecoFeeModel = IncomeItemFactory::buildNewModel($ecoFeeTypeRef);
                if (!empty($ecoFeeModel)) {
                    $value = $this->getSubTotalSalesEcoFees($ecoFeeTypeRef, $currencyRef, $this->startDate, $this->endDate, false, $this->tags, NULL, false);
                    $ecoFeeProperties[$ecoFeeTypeRef] = $value;
                    $this->ecoFeesTotal += $value;
                }
            }
        }
        return $ecoFeeProperties;
    }

    public function getSubTotalSalesEcoFees($incomeItemSalesEcoFeeTypeRef = NULL, $currencyRef = 'usd', DateTime $startDate = NULL, DateTime $endDate = NULL, $includeInProgress = true, $tags = array(), $brandRef = '', $general = false) {
        $search = IncomeItemFactory::search()
                ->filter('cancelled', 0)
                ->filter('void', 0);
        if (empty($incomeItemSalesEcoFeeTypeRef)) {
            $incomeItemSalesEcoFeeTypeRef = 'sales_ac_eco';
            $general = true;
        }
        $search->filterByTypeRef($incomeItemSalesEcoFeeTypeRef, $general);

        $currencyArray = CurrencyFactory::search()
                ->filter('ref', $currencyRef)
                ->select();
        if (empty($currencyArray)) {
            return 0;
        }
        $currency = $currencyArray[0];
        $currencyId = $currency->getProperty('id');
        $incomeItemTableName = dbConfig::getDbPrefix() . 'income_item';
        $search->join('income', 'id', $incomeItemTableName, 'income_id', 'i');
        $search->filter('i.void', 0)
                ->filter('i.cancelled', 0);
        $search->filter('i.currency_id', $currencyId);
        if (!empty($startDate)) {
            $startDateObject = clone $startDate;
            $startDateObject->sub(new DateInterval('P1D'));
            $startDateSearchable = $startDateObject->format('Y-m-d');
            $search->filterGreaterThan('applicable_date', $startDateSearchable);
        }
        if (!empty($endDate)) {
            $endDateObject = clone $endDate;
            $endDateObject->add(new DateInterval('P1D'));
            $endDateSearchable = $endDateObject->format('Y-m-d');
            $search->filterLessThan('applicable_date', $endDateSearchable);
        }
        if (!$includeInProgress) {
            $search->filter('in_progress', 0);
        }
        if (!empty($tags)) {
            $search->join('income_link_to_tag', 'income_id', 'i', 'id', 'tl');
            $search->andIf();
            $search->filterGroup();
            $tagCount = count($tags);
            for ($i = 0; $i < $tagCount; $i++) {
                if ($i > 0) {
                    $search->orIf();
                }
                $tag = $tags[$i];
                $search->filter('tl.tag_id', $tag->getProperty('id'));
            }
            $search->closeGroup();
            $search->andIf();
        }
        $sumArray = $search->sum('net_amount');
        if (isset($sumArray['net_amount'])) {
            return $sumArray['net_amount'];
        }
        return NULL;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeaderRowToCSV($csv);
        $this->addRowsToCSV($csv);
        return $csv;
    }
    
    protected function addHeaderRowToCSV(GI_CSV $csv) {
        $csv->addHeaderRow(array(
            'Item',
            'Total Value'
        ));
    }
    
    protected function addRowsToCSV(GI_CSV $csv) {
        $this->addInventoryRowsToCSV($csv);
        $this->addCOGSRowsToCSV($csv);
        $this->addWasteRowsToCSV($csv);
        $this->addSalesRowsToCSV($csv);
        $this->addEcoFeesRowsToCSV($csv);
    }
    
    protected function addInventoryRowsToCSV(GI_CSV $csv) {
        $csv->addRow(array('INVENTORY'));
        $properties = $this->getProperty('inv');
        if (!empty($properties)) {
            foreach ($properties as $typeRef=>$value) {
                $model = ExpenseItemFactory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $csv->addRow(array(
                        $model->getTypeTitle(),
                        $value
                    ));
                }
            }
        }
    }

    protected function addCOGSRowsToCSV(GI_CSV $csv) {
        $csv->addRow(array('COGS'));
        $properties = $this->getProperty('cogs');
        if (!empty($properties)) {
            foreach ($properties as $typeRef => $value) {
                $model = ExpenseItemFactory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $csv->addRow(array(
                        $model->getTypeTitle(),
                        $value
                    ));
                }
            }
        }
    }

    protected function addWasteRowsToCSV(GI_CSV $csv) {
        $csv->addRow(array('WASTE'));
        $properties = $this->getProperty('wst');
        if (!empty($properties)) {
            foreach ($properties as $typeRef => $value) {
                $model = ExpenseItemFactory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $csv->addRow(array(
                        $model->getTypeTitle(),
                        $value
                    ));
                }
            }
        }
    }

    protected function addSalesRowsToCSV(GI_CSV $csv) {
        $csv->addRow(array('SALES'));
        $properties = $this->getProperty('sales');
        if (!empty($properties)) {
            foreach ($properties as $typeRef => $value) {
                $model = IncomeItemFactory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $csv->addRow(array(
                        $model->getTypeTitle(),
                        $value
                    ));
                }
            }
        }
    }

    protected function addEcoFeesRowsToCSV(GI_CSV $csv) {
        $csv->addRow(array('ECO FEES'));
        $properties = $this->getProperty('eco_fees');
        if (!empty($properties)) {
            foreach ($properties as $typeRef => $value) {
                $model = IncomeItemFactory::buildNewModel($typeRef);
                if (!empty($model)) {
                    $csv->addRow(array(
                        $model->getTypeTitle(),
                        $value
                    ));
                }
            }
        }
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_inv_cogs_sales_report')) {
            return true;
        }
        return false;
    }

}
