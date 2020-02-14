<?php
/**
 * Description of AbstractAccReportOverview
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class AbstractAccReportOverview extends AbstractAccReport {
    
    protected $inventoryAccountName = 'Inventory Asset';
    protected $cogsAccountName = 'Cost of Goods Sold';
    protected $wasteAccountName = 'Waste (COGS)';
    
    protected $profitAndLossReport = NULL;
    protected $apReport = NULL;
    protected $arReport = NULL;
    
    public function getTitle() {
        return 'Overview';
    }
    
    public function getDetailView() {
       return new AccReportOverviewDetailView($this);
    }
    
    public function getDescription() {
        return 'Shows totals for inventory valuation, COGS, Sales, A/P, A/R, and in-progress incomes and expenses.';
    }
    
    public function getColour() {
        return '4501E7';
    }

    public function getInitials() {
        return 'OV';
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            if (!$this->setProperties()) {
                return false;
            }
            $this->reportBuilt = true;
        }
        return true;
    }

    protected function setProperties() {
        $profitAndLossReport = AccReportFactory::buildReportObject('profit_and_loss', $this->getStartDate(), $this->getEndDate());
        if (empty($profitAndLossReport) || !$profitAndLossReport->buildReport()) {
            return false;
        }
        $this->profitAndLossReport = $profitAndLossReport;
        $arAgingReport = AccReportFactory::buildReportObject('ar_aging_summary', $this->getStartDate(), $this->getEndDate());
        if (empty($arAgingReport) || !$arAgingReport->buildReport()) {
            return false;
        }
        $this->arReport = $arAgingReport;
        $apAgingReport = AccReportFactory::buildReportObject('ap_aging_summary', $this->getStartDate(), $this->getEndDate());
        if (empty($apAgingReport) || !$apAgingReport->buildReport()) {
            return false;
        }
        $this->apReport = $apAgingReport;
        $this->properties['income'] = array();
        $this->properties['cogs'] = array();
        $this->properties['profit'] = array();
        $this->setPropertiesFromProfitAndLossReport($profitAndLossReport);
        $this->properties['inventory'] = array();
        $this->properties['inventory']['total'] = $this->retrieveAccountBalanceFromQuickbooks($this->inventoryAccountName);
        $this->properties['ar'] = array();
        $this->setPropertiesFromARAgingReport($arAgingReport);
        $this->properties['ap'] = array();
        $this->setPropertiesFromAPAgingReport($apAgingReport);
        $this->properties['in_progress_expenses'] = 0;
        $this->setInProgressExpenses();
        $this->properties['in_progress_incomes'] = 0;
        $this->setInProgressIncomes();
        return true;
    }

    protected function retrieveAccountBalanceFromQuickbooks($accountName) {
        $dataService = QBConnection::getInstance();
        $results = $dataService->Query("SELECT * from Account WHERE Name='" . $accountName . "'");
        $error = $dataService->getLastError();
        if ($error != null || empty($results)) {
            return NULL;
        }
        $object = $results[0];
        return $object->CurrentBalanceWithSubAccounts;
    }
    
    protected function setPropertiesFromProfitAndLossReport(AbstractAccReportQBProfitAndLoss $profitAndLossReport) {
        $qbReportObject = $profitAndLossReport->getQBReportObject();
        if (!isset($qbReportObject->Rows)) {
            return false;
        }
        $this->parseProfitAndLossRow($qbReportObject->Rows->Row);
    }
    
    protected function parseProfitAndLossRow($rowData) {
        foreach ($rowData as $row) {
            if (isset($row->Header)) {
                
            }
            if (isset($row->Rows)) {
                $this->parseProfitAndLossRow($row->Rows->Row);
            }
            if (isset($row->Summary)) {
                if (isset($row->Summary->ColData)) {
                    $summaryCols = $row->Summary->ColData;
                    $summaryColCount = count($summaryCols);
                    for ($k = 0; $k < $summaryColCount; $k++) {
                        $summaryCol = $summaryCols[$k];
                        if (isset($summaryCol->value)) {
                            if ($summaryCol->value == 'PROFIT') {
                                $this->properties['profit']['total'] = $summaryCols[$k + 1]->value;
                                break;
                            } else if ($summaryCol->value == 'Total Income') {
                                $this->properties['income']['total'] = $summaryCols[$k + 1]->value;
                                break;
                            } else if ($summaryCol->value == 'Total Cost of Goods Sold') {
                                $this->properties['cogs']['total'] = $summaryCols[$k + 1]->value;
                                break;
                            }
                        }
                    }
                }
            }
            if (isset($row->ColData)) {
                if (!empty($row->ColData)) {
                    $colData = $row->ColData;
                    $colCount = count($colData);
                    for ($i=0;$i<$colCount;$i++) {
                        $col = $colData[$i];
                        if (isset($col->value)) {
                            if ($col->value == $this->wasteAccountName) {
                                $this->properties['cogs']['waste'] = $colData[$i+1]->value;
                                break;
                            } else if ($col->value == $this->cogsAccountName) {
                                $this->properties['cogs']['cogs'] = $colData[$i+1]->value;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
    
    protected function setPropertiesFromAPAgingReport(AbstractAccReportQBApAgingSummary $apReport) {
        $qbData = $apReport->getQBReportObject();
        if (empty($qbData)) {
            return false;
        }
        $this->parseAPAgingReportRow($qbData->Rows->Row);
        return true;
    }
    
    protected function parseAPAgingReportRow($rowData) {
        if (empty($rowData)) {
            return false;
        }
        foreach ($rowData as $row) {
            if (isset($row->Summary)) {
                if (isset($row->Summary->ColData)) {
                    $colCount = count($row->Summary->ColData);
                    for ($i=0;$i<$colCount;$i++) {
                        $col = $row->Summary->ColData[$i];
                        if ($col->value === 'TOTAL') {
                            $this->properties['ap']['total'] = $row->Summary->ColData[$colCount - 1]->value;
                            break;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    protected function setPropertiesFromARAgingReport(AbstractAccReportQBArAgingSummary $arReport) {
        $qbData = $arReport->getQBReportObject();
        if (empty($qbData)) {
            return false;
        }
        $this->parseARAgingReportRow($qbData->Rows->Row);
        return true;
    }

    protected function parseARAgingReportRow($rowData) {
        if (empty($rowData)) {
            return false;
        }
        foreach ($rowData as $row) {
            if (isset($row->Summary)) {
                if (isset($row->Summary->ColData)) {
                    $colCount = count($row->Summary->ColData);
                    for ($i = 0; $i < $colCount; $i++) {
                        $col = $row->Summary->ColData[$i];
                        if ($col->value === 'TOTAL') {
                            $this->properties['ar']['total'] = $row->Summary->ColData[$colCount - 1]->value;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    protected function setInProgressExpenses() {
        $primaryCurrency = $this->getCurrency();
        if (empty($primaryCurrency)) {
            return false;
        }
        $primaryCurrencyRef = $primaryCurrency->getProperty('ref');
        if ($primaryCurrencyRef == 'cad') {
            $secondaryCurrency = CurrencyFactory::getModelByRef('usd');
        } else {
            $secondaryCurrency = CurrencyFactory::getModelByRef('cad');
        }
        $primaryCurrencyTotal = $this->getTotalInProgressExpenses($primaryCurrency);
        $secondaryCurrencyTotal = $this->getTotalInProgressExpenses($secondaryCurrency);
        if (!empty($secondaryCurrencyTotal)) {
            $secondaryCurrencyTotal = $primaryCurrency->convertToThis($secondaryCurrencyTotal, $secondaryCurrency);
        }
        $primaryCurrencyTotal += $secondaryCurrencyTotal;
        $this->properties['in_progress_expenses'] = $primaryCurrencyTotal;
        return true;
    }

    protected function getTotalInProgressExpenses(AbstractCurrency $currency, $tags = array()) {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $currencyId = $currency->getProperty('id');
        $search = ExpenseItemFactory::search()
                ->filter('cancelled', 0)
                ->filter('void', 0);
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
        $search->filter('in_progress', 1);
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
        $expenseItems = $search->select();
        $sum = 0;
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                $sum += $expenseItem->getTotal();
            }
        }
        return $sum;
    }

    protected function setInProgressIncomes() {
        $primaryCurrency = $this->getCurrency();
        if (empty($primaryCurrency)) {
            return false;
        }
        $primaryCurrencyRef = $primaryCurrency->getProperty('ref');
        if ($primaryCurrencyRef == 'cad') {
            $secondaryCurrency = CurrencyFactory::getModelByRef('usd');
        } else {
            $secondaryCurrency = CurrencyFactory::getModelByRef('cad');
        }
        $primaryCurrencyTotal = $this->getTotalInProgressIncomes($primaryCurrency);
        $secondaryCurrencyTotal = $this->getTotalInProgressIncomes($secondaryCurrency);
        if (!empty($secondaryCurrencyTotal)) {
            $secondaryCurrencyTotal = $primaryCurrency->convertToThis($secondaryCurrencyTotal, $secondaryCurrency);
        }
        $primaryCurrencyTotal += $secondaryCurrencyTotal;
        $this->properties['in_progress_incomes'] = $primaryCurrencyTotal;
        return true;
    }

    protected function getTotalInProgressIncomes(AbstractCurrency $currency, $tags = array()) {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $currencyId = $currency->getProperty('id');
        $search = IncomeItemFactory::search()
                ->filter('cancelled', 0)
                ->filter('void', 0);

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
        $search->filter('in_progress', 1);
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
        $incomeItems = $search->select();
        $sum = 0;
        if (!empty($incomeItems)) {
            foreach ($incomeItems as $incomeItem) {
                $sum += $incomeItem->getTotal();
            }
        }
        return $sum;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv);
        $this->addHeadersToCSV($csv);
        $this->addRowsToCSV($csv);
        return $csv;
    }
    
    protected function addHeadersToCSV(GI_CSV $csv) {
        $headers = array(
            'Item',
            'Total Value'
        );
        $csv->addHeaderRow($headers);
    }

    protected function addRowsToCSV(GI_CSV $csv) {
        if (isset($this->properties['inventory']['total'])) {
            $csv->addRow(array(
                'Inventory',
                $this->properties['inventory']['total']
            ));
        }
        if (isset($this->properties['cogs']['cogs'])) {
            $csv->addRow(array(
                'COGS',
                $this->properties['cogs']['cogs']
            ));
        }
        if (isset($this->properties['cogs']['waste'])) {
            $csv->addRow(array(
                'Waste',
                $this->properties['cogs']['waste']
            ));
        }
        if (isset($this->properties['income']['total'])) {
            $csv->addRow(array(
                'Sales',
                $this->properties['income']['total']
            ));
        }
        if (isset($this->properties['profit']['total'])) {
            $csv->addRow(array(
                'Profit',
                $this->properties['profit']['total'],
            ));
        }
        if (isset($this->properties['ap']['total'])) {
            $csv->addRow(array(
                'Accounts Payable',
                $this->properties['ap']['total']
            ));
        }
        if (isset($this->properties['in_progress_expenses'])) {
            $csv->addRow(array(
                'In Progress Expenses',
                $this->properties['in_progress_expenses']
            ));
        }
        if (isset($this->properties['ar']['total'])) {
            $csv->addRow(array(
                'Accounts Receivable',
                $this->properties['ar']['total']
            ));
        }
        if (isset($this->properties['in_progress_incomes'])) {
            $csv->addRow(array(
                'In Progress Incomes',
                $this->properties['in_progress_incomes']
            ));
        }
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_overview_report')) {
            return true;
        }
        return false;
    }

}
