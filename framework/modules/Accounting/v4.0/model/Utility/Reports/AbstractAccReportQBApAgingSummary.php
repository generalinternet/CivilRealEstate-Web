<?php

/**
 * Description of AbstractAccReportApAgingSummary
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
use QuickBooksOnline\API\ReportService\ReportName;

class AbstractAccReportQBApAgingSummary extends AbstractAccReportQB {

    protected $currentTotal;
    protected $oneToThirtyTotal;
    protected $thirtyOneToSixtyTotal;
    protected $sixtyOneToNinetyTotal;
    protected $ninetyOneAndOverTotal;
    protected $apTotal;
    protected $cacheTTL = 43200;

    public function getTitle() {
        return 'A/P Aging Summary';
    }

    protected function getQBReportName() {
        return ReportName::AGEDPAYABLES;
    }

    public function getDescription() {
        return 'Shows unpaid bills for the current period and for the last 30, 60 and 90+ days so you can see how long they’ve been open (outstanding).';
    }

    public function getColour() {
        return '137300';
    }

    public function getInitials() {
        return 'AP';
    }

    public function isViewable() {
        if ($this->overridePermissionCheck || Permission::verifyByRef('view_ap_aging_summary_report')) {
            return true;
        }
        return false;
    }

    public function buildReport() {
        if (parent::buildReport()) {
            $this->parseValuesFromReportData();
            return true;
        }
        return false;
    }

    public function getCurrentTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->currentTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_current_total');
            if (!empty($cachedValue)) {
                $this->currentTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->currentTotal)) {
            return NULL;
        }
        $value = $this->currentTotal;
        if ($setCache) {
            $this->setValueInCache('ap_current_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getOneToThirtyTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->oneToThirtyTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_one_to_thirty_total');
            if (!empty($cachedValue)) {
                $this->oneToThirtyTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->oneToThirtyTotal)) {
            return NULL;
        }
        $value = $this->oneToThirtyTotal;
        if ($setCache) {
            $this->setValueInCache('ap_one_to_thirty_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getThirtyOneToSixtyTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->thirtyOneToSixtyTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_thirty_one_to_sixty_total');
            if (!empty($cachedValue)) {
                $this->thirtyOneToSixtyTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->thirtyOneToSixtyTotal)) {
            return NULL;
        }
        $value = $this->thirtyOneToSixtyTotal;
        if ($setCache) {
            $this->setValueInCache('ap_thirty_one_to_sixty_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getSixtyOneToNinetyTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->sixtyOneToNinetyTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_sixty_one_to_ninety_total');
            if (!empty($cachedValue)) {
                $this->sixtyOneToNinetyTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->sixtyOneToNinetyTotal)) {
            return NULL;
        }
        $value = $this->sixtyOneToNinetyTotal;
        if ($setCache) {
            $this->setValueInCache('ap_sixty_one_to_ninety_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getNinetyOneAndOverTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->ninetyOneAndOverTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_ninety_one_and_over_total');
            if (!empty($cachedValue)) {
                $this->ninetyOneAndOverTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->ninetyOneAndOverTotal)) {
            return NULL;
        }
        $value = $this->ninetyOneAndOverTotal;
        if ($setCache) {
            $this->setValueInCache('ap_ninety_one_and_over_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    public function getTotal($formatForDisplay = false) {
        $setCache = false;
        if (is_null($this->apTotal) && !$this->reportBuilt) {
            $cachedValue = $this->getValueFromCache('ap_total');
            if (!empty($cachedValue)) {
                $this->apTotal = $cachedValue;
            } else {
                $this->buildReport();
                $setCache = true;
            }
        }
        if (is_null($this->apTotal)) {
            return NULL;
        }
        $value = $this->apTotal;
        if ($setCache) {
            $this->setValueInCache('ap_total', $value);
        }
        if ($formatForDisplay) {
            return '$' . GI_StringUtils::formatMoney($value);
        }
        return $value;
    }

    protected function parseValuesFromReportData() {
        $reportData = $this->getQBReportObject();
        if (empty($reportData)) {
            return false;
        }
        $rowsWrapper = $reportData->Rows;
        if (!empty($rowsWrapper)) {
            $rows = $rowsWrapper->Row;
            if (!empty($rows)) {
                $count = count($rows);
                $lastRow = $rows[$count - 1];
                $totalsRow = $lastRow->Summary->ColData;
                if (!empty($totalsRow)) {
                    $this->parseTotalsFromTotalsRowData($totalsRow);
                }
            }
        }
    }

    protected function parseTotalsFromTotalsRowData($totalsRow) {
        $keys = array(
            'current' => 1,
            'oneToThirty' => 2,
            'thirtyOneToSixty' => 3,
            'sixtyOneToNinety' => 4,
            'ninetyOneAndOver' => 5,
            'total' => 6,
        );
        $currentTotal = $totalsRow[$keys['current']]->value;
        $oneToThirty = $totalsRow[$keys['oneToThirty']]->value;
        $thirtyOneToSixty = $totalsRow[$keys['thirtyOneToSixty']]->value;
        $sixtyOneToNinety = $totalsRow[$keys['sixtyOneToNinety']]->value;
        $ninetyOneAndOver = $totalsRow[$keys['ninetyOneAndOver']]->value;
        $total = $totalsRow[$keys['total']]->value;


        if (!is_null($currentTotal)) {
            $this->currentTotal = $currentTotal;
            $this->setValueInCache('ap_current_total', $currentTotal);
        }
        if (!is_null($oneToThirty)) {
            $this->oneToThirtyTotal = $oneToThirty;
            $this->setValueInCache('ap_one_to_thirty_total', $oneToThirty);
        }
        if (!is_null($thirtyOneToSixty)) {
            $this->thirtyOneToSixtyTotal = $thirtyOneToSixty;
            $this->setValueInCache('ap_thirty_one_to_sixty_total', $thirtyOneToSixty);
        }
        if (!is_null($sixtyOneToNinety)) {
            $this->sixtyOneToNinetyTotal = $sixtyOneToNinety;
            $this->setValueInCache('ap_sixty_one_to_ninety_total', $sixtyOneToNinety);
        }
        if (!is_null($ninetyOneAndOver)) {
            $this->ninetyOneAndOverTotal = $ninetyOneAndOver;
            $this->setValueInCache('ap_ninety_one_and_over', $ninetyOneAndOver);
        }
        if (!is_null($total)) {
            $this->apTotal = $total;
            $this->setValueInCache('ap_total', $total);
        }
    }

}
