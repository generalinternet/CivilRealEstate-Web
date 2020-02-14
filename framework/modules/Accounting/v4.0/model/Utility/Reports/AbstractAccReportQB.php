<?php
/**
 * Description of AbstractAccReportQB
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
use QuickBooksOnline\API\ReportService\ReportService;

abstract class AbstractAccReportQB extends AbstractAccReport {
    
    protected $qbReportObject;
    
    protected function getQBReportName() {
        return NULL;
    }
    
    public function getQBReportObject() {
        return $this->qbReportObject;
    }

    public function buildReport() {
        if (!$this->reportBuilt) {
            $dataService = QBConnection::getInstance();
            if (empty($dataService)) {
                return false;
            }
            $serviceContext = $dataService->getServiceContext();
            $reportService = new ReportService($serviceContext);
            if (!$reportService) {
                return false;
            }
            $startDate = $this->getStartDate();
            $reportService->setStartDate($startDate->format('Y-m-d'));
            $endDate = $this->getEndDate();
            $reportService->setEndDate($endDate->format('Y-m-d'));
            $reportService->setAccountingMethod("Accrual");
            $reportName = $this->getQBReportName();
            if (empty($reportName)) {
                return false;
            }
            $qbReportObject = $reportService->executeReport($reportName);
            if (!$qbReportObject) {
                return false;
            }
            $this->qbReportObject = $qbReportObject;
            $this->reportBuilt = true;
        }
        return true;
    }

    public function getDetailView() {
        return new AccReportQBDetailView($this);
    }
    
    public function getCurrency() {
        if (empty($this->currency)) {
            if (!empty($this->qbReportObject) && isset($this->qbReportObject->Header) && isset($this->qbReportObject->Header->Currency)) {
                $currencyRef = strtolower($this->qbReportObject->Header->Currency);
                if (!empty($currencyRef)) {
                    $this->currency = CurrencyFactory::getModelByRef($currencyRef);
                }
            }
            if (empty($this->currency)) {
                return parent::getCurrency();
            }
        }
        return $this->currency;
    }

    protected function buildCSV(GI_CSV $csv) {
        $this->addCurrencyAndDatesToCSV($csv, $this->qbReportObject->Header);
        $this->buildCSVColumns($csv, $this->qbReportObject->Columns);
        $this->buildCSVRows($csv, $this->qbReportObject->Rows);
        return $csv;
    }

    protected function buildCSVColumns(GI_CSV $csv, $columnsData) {
        if (!empty($columnsData)) {
            $colData = $columnsData->Column;
            if (!empty($colData)) {
                $headerRow = array();
                $colDataCount = count($colData);
                for ($i = 0; $i < $colDataCount; $i++) {
                    $headerRow[] = $colData[$i]->ColTitle;
                }
                $csv->addHeaderRow($headerRow);
            }
        }
    }

    protected function buildCSVRows(GI_CSV $csv, $rowsData, $level = 0) {
        if (!isset($rowsData->Row)) {
            return;
        }
        $rowData = $rowsData->Row;
        foreach ($rowData as $row) {
            //Header
            if (isset($row->Header)) {
                if (isset($row->Header->ColData)) {
                    $headerRow = array();
                    $headerColCount = count($row->Header->ColData);
                    for ($i = 0; $i < $headerColCount; $i++) {
                        $headerRow[] = $row->Header->ColData[$i]->value;
                    }
                    $csv->addRow($headerRow);
                }
            }
            //Col Data
            if (isset($row->ColData)) {
                $colDataCount = count($row->ColData);
                $colRow = array();
                for ($j=0;$j<$colDataCount;$j++) {
                    $colRow[] = $row->ColData[$j]->value;
                }
                $csv->addRow($colRow);
            }
            //Rows (subrows)
            if (isset($row->Rows)) {
                $newLevel = $level + 1;
                $this->buildCSVRows($csv, $row->Rows, $newLevel);
            }
            //Summary
            if (isset($row->Summary)) {
                if (isset($row->Summary->ColData)) {
                    $summaryRow = array();
                    $summaryColCount = count($row->Summary->ColData);
                    for ($k=0;$k<$summaryColCount;$k++) {
                        $summaryRow[] = $row->Summary->ColData[$k]->value;
                    }
                    $csv->addRow($summaryRow);
                }
            }
        }
    }

    protected function addCurrencyAndDatesToCSV(GI_CSV $csv, $headerData = NULL) {
        $currency = $this->getCurrency();
        if (empty($headerData)) {
            return parent::addCurrencyAndDatesToCSV($csv);
        }
        if (!empty($headerData)) {
            $reportDate = NULL;
            $endPeriod = $headerData->EndPeriod;
            $options = $headerData->Option;
            if (!empty($options)) {
                foreach ($options as $option) {
                    $Name = $option->Name;
                    if (!empty($Name) && $Name == 'report_date') {
                        $reportDate = $option->Value;
                        break;
                    }
                }
            }
            if (!empty($endPeriod) && !empty($reportDate) && $endPeriod == $reportDate) {
                $row = array(
                    'Currency: ' . $currency->getProperty('name'),
                    'As of Date: ' . $reportDate,
                );
                $csv->addHeaderRow($row);
                return true;
            }
        }

        return parent::addCurrencyAndDatesToCSV($csv);
    }

}
