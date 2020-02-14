<?php
/**
 * Description of AbstractAccReportQBProfitAndLoss
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.2
 */
use QuickBooksOnline\API\ReportService\ReportName;

abstract class AbstractAccReportQBProfitAndLoss extends AbstractAccReportQB {
    
    protected $totalIncome = NULL;
    protected $totalSales = NULL;
    protected $totalCOGS = NULL;
    protected $totalExpenses = NULL;
    protected $totalOtherExpenses = NULL;
    protected $totalProfit = NULL;
    protected $grossMargin = NULL;
    
    public function getTotalIncome($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalIncome) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_income');
            if (!empty($cachedValue)) {
                $this->totalIncome = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalIncome;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_income', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotalSales($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalSales) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_sales');
            if (!is_null($cachedValue)) {
                $this->totalSales = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalSales;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_sales', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotalCOGS($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalCOGS) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_cogs');
            if (!empty($cachedValue)) {
                $this->totalCOGS = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalCOGS;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_cogs', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotalExpenses($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalExpenses) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_expenses');
            if (!empty($cachedValue)) {
                $this->totalExpenses = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalExpenses;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_expenses', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotalOtherExpenses($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalOtherExpenses) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_other_expenses');
            if (!empty($cachedValue)) {
                $this->totalOtherExpenses = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalOtherExpenses;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_other_expenses', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotalProfit($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->totalProfit) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('total_profit');
            if (!empty($cachedValue)) {
                $this->totalProfit = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->totalProfit;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('total_profit', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getGrossMargin($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->grossMargin) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('gross_margin');
            if (!empty($cachedValue)) {
                $this->grossMargin = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        $value = $this->grossMargin;
        if (is_null($value)) {
            $value = 0;
        }
        if ($setCache) {
            $this->setValueInCache('gross_margin', $value);
        }
        if ($formatForDisplay) {
            return GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTitle() {
        return 'Profit and Loss';
    }

    protected function getQBReportName() {
        return ReportName::PROFITANDLOSS;
    }
    
    public function getDescription() {
        return 'Shows money you earned (income) and money you spent (expenses) so you can see how profitable you are. Also called an income statement.';
    }

    public function getColour() {
        return 'E700BD';
    }

    public function getInitials() {
        return 'PL';
    }
    
    public function buildReport() {
        if (parent::buildReport()) {
            $this->parseValuesFromReportData();
            return true;
        }
        return false;
    }
    
    protected function parseValuesFromReportData() {
        $reportData = $this->getQBReportObject();
        if (empty($reportData)) {
            return false;
        }
         
        $mainRows = $reportData->Rows;
        $this->parseValuesFromReportDataRows($mainRows);
        if (!is_null($this->totalSales) && !is_null($this->totalCOGS)) {
            $grossMagin = $this->totalSales - $this->totalCOGS;
            $this->grossMargin = $grossMagin;
            $this->setValueInCache('gross_margin', $grossMagin);
        }
        return true;
    }

    protected function parseValuesFromReportDataRows($rows) {
        if (isset($rows->Row)) {
            $this->parseValuesFromReportDataRow($rows->Row);
        }
        if (isset($rows->Summary)) {
            $this->parseValuesFromReportDataSummary($rows->Summary);
        }
    }

    protected function parseValuesFromReportDataRow($row) {
        if (!empty($row)) {
            foreach ($row as $r) {
                if (isset($r->Rows)) {
                    $this->parseValuesFromReportDataRows($r->Rows);
                }
                if (isset($r->Summary)) {
                    $this->parseValuesFromReportDataSummary($r->Summary);
                }
            }
            if (isset($row->Summary)) {
                $this->parseValuesFromReportDataSummary($row->Summary);
            }
        }
    }

//    protected function parseValuesFromReportDataSummary($summary) {
//        if (!isset($summary->ColData)) {
//            return;
//        }
//        $columns = $summary->ColData;
//        if (count($columns) < 2) {
//            return false;
//        }
//        $labelCol = $columns[0];
//        $valueCol = $columns[1];
//        $label = $labelCol->value;
//        if (empty($label)) {
//            return false;
//        }
//        $label = strtolower($label);
//        $value = floatval($valueCol->value);
//        if (is_null($value)) {
//            $value = 0;
//        }
//        switch ($label) {
//            case 'total sales':
//                $this->totalSales = $value;
//                $this->setValueInCache('total_sales', $value);
//                break;
//            case 'total income':
//                $this->totalIncome = $value;
//                $this->setValueInCache('total_income', $value);
//                break;
//            case 'total cost of goods sold':
//                $this->totalCOGS = $value;
//                $this->setValueInCache('total_cogs', $value);
//                break;
//            case 'total expenses':
//                $this->totalExpenses = $value;
//                $this->setValueInCache('total_expenses', $value);
//                break;
//            case 'total other expenses':
//                $this->totalOtherExpenses = $value;
//                $this->setValueInCache('total_other_expenses', $value);
//                break;
//            case 'profit':
//                $this->totalProfit = $value;
//                $this->setValueInCache('total_profit', $value);
//                break;
//        }
//        return true;
//    }

    protected function parseValuesFromReportDataSummary($summary) {
        if (!isset($summary->ColData)) {
            return;
        }
        $columns = $summary->ColData;
        if (count($columns) < 2) {
            return false;
        }
        $labelCol = $columns[0];
        $valueCol = $columns[1];

        $labelRaw = $labelCol->value;
        if (empty($labelRaw)) {
            return false;
        }
        $labelKey = trim(preg_replace('!\s+!', '_', preg_replace('/[0-9]+/', '', strtolower($labelRaw))), '_');

        $validKeys = array(
            'total_sales',
            'total_income',
            'total_cost_of_goods_sold',
            'total_expenses',
            'total_other_expenses',
            'profit',
        );

        if (!in_array($labelKey, $validKeys)) {
            $labelKey = $this->determineLabelKeyFromSummaryData($labelRaw);
        }

        $value = floatval($valueCol->value);
        switch ($labelKey) {
            case 'total_sales':
                $this->totalSales = $value;
                $this->setValueInCache('total_sales', $value);
                break;
            case 'total_income':
                $this->totalIncome = $value;
                $this->setValueInCache('total_income', $value);
                break;
            case 'total_cost_of_goods_sold':
                $this->totalCOGS = $value;
                $this->setValueInCache('total_cogs', $value);
                break;
            case 'total_expenses':
                $this->totalExpenses = $value;
                $this->setValueInCache('total_expenses', $value);
                break;
            case 'total_other_expenses':
                $this->totalOtherExpenses = $value;
                $this->setValueInCache('total_other_expenses', $value);
                break;
            case 'profit':
                $this->totalProfit = $value;
                $this->setValueInCache('total_profit', $value);
                break;
        }
        return true;
    }

    protected function determineLabelKeyFromSummaryData($labelRaw) {
        $labelRaw = strtolower($labelRaw);
        $labelKey = '';
        if (strpos($labelRaw, 'total') !== false) {
            if (strpos($labelRaw, 'sales') !== false) {
                $labelKey = 'total_sales';
            } else if (strpos($labelRaw, 'income') !== false && strpos($labelRaw, 'other') === false) {
                $labelKey = 'total_income';
            } else if (strpos($labelRaw, 'cost of goods sold') !== false) {
                $labelKey = 'total_cost_of_goods_sold';
            } else if (strpos($labelRaw, 'expenses') !== false) {
                $labelKey = 'total_expenses';
            } else if (strpos($labelRaw, 'other expenses') !== false) {
                $labelKey = 'total_other_expenses';
            }
        } else if (strpos($labelRaw, 'profit') !== false) {
            $labelKey .= 'profit';
        }
        return $labelKey;
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_profit_and_loss_report')) {
            return true;
        }
        return false;
    }

}
