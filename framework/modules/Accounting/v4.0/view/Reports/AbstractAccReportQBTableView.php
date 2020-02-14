<?php
/**
 * Description of AbstractAccReportQBTableView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractAccReportQBTableView extends GI_View {

    protected $accReportQB;
    protected $qbReportData;
    protected $fullView = false;

    public function __construct(AbstractAccReportQB $accReportQB) {
        parent::__construct();
        $this->qbReportData = $accReportQB->getQBReportObject();
        $this->accReportQB = $accReportQB;
        $this->addCSS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.css');
    }
    
    /**
     * @param Boolean $fullView
     */
    public function setFullView($fullView) {
        $this->fullView = $fullView;
    }

    protected function buildView() {
        if ($this->fullView) {
            $this->openViewWrap();
        }
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        if ($this->fullView) {
            $this->closeViewWrap();
        }
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }

    protected function buildViewHeader() {
       
    }

    protected function buildViewBody() {
        $this->buildTable();
    }

    protected function buildTable() {
        $this->buildHeader($this->qbReportData->Header);
        $this->buildColumns($this->qbReportData->Columns);
        $this->buildRows($this->qbReportData->Rows);
    }
    
    protected function buildHeader($headerData) {
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
            $this->addHTML('<h3>As of ' . GI_Time::formatDateForDisplay($endPeriod) . '</h3>');
        }
    }
    
    protected function buildColumns($columnsData) {
        if (!empty($columnsData)) {
            $colData = $columnsData->Column;
            if (!empty($colData)) {
                $this->addHTML('<div class="qb_table acc_report_table">');
                $this->addHTML('<div class="qb_table_row acc_report_row table_header_row">');
                $this->addHTML('<div class="flex_row">');
                $colDataCount = count($colData);
                for ($i=0;$i<$colDataCount;$i++) {
                    $class = 'flex_col';
                    if ($i == $colDataCount - 1) {
                        $class .= ' right_column';
                    }
                    $this->addHTML('<div class="'.$class.'">' . $colData[$i]->ColTitle . '</div>');
                }
                $this->addHTML('</div>');
                $this->addHTML('</div>');
                $this->addHTML('</div>');
            }
        }
    }

    protected function buildRows($rowsData, $level = 0) {
        if (!isset($rowsData->Row)) {
            return;
        }
        $rowData = $rowsData->Row;
        foreach ($rowData as $row) {
            $class = 'qb_table_row acc_report_row';
            $subrow = false;
            if ($level > 0) {
                $subrow = true;
            }
            if ($subrow) {
                $class .= ' sub_row'; 
            }
            $this->addHTML('<div class="'.$class.'">');
            //Header
            if (isset($row->Header)) {
                if (isset($row->Header->ColData)) {
                    $this->addHTML('<div class="flex_row data_header_row">');
                    $headerColCount = count($row->Header->ColData);
                    for ($i=0;$i<$headerColCount;$i++) {
                        $class = 'flex_col';
                        if ($i == $headerColCount - 1) {
                            $class .= ' right_column';
                        }
                        $this->addHTML('<div class="'.$class.'"><i>' . $row->Header->ColData[$i]->value . '</i></div>');
                    }
                    $this->addHTML('</div>');
                }
            }
            
            //Col Data
            if (isset($row->ColData)) {
                $this->addHTML('<div class="flex_row data_row">');
                $colDataCount = count($row->ColData);
                for ($j=0;$j<$colDataCount;$j++) {
                    $class = 'flex_col';
                    if ($j == $colDataCount - 1) {
                        $class .= ' right_column';
                    }
                    $this->addHTML('<div class="'.$class.'">' . $row->ColData[$j]->value . '</div>');
                }
                $this->addHTML('</div>');
            }
            //Rows (subrows)
            if (isset($row->Rows)) {
                $newLevel = $level + 1;
                $this->buildRows($row->Rows, $newLevel);
            }
            //Summary
            if (isset($row->Summary)) {
                if (isset($row->Summary->ColData)) {
                    $this->addHTML('<div class="flex_row summary_row">');
                    $summaryColCount = count($row->Summary->ColData);
                    for ($k=0;$k<$summaryColCount;$k++) {
                        $class = 'flex_col total_column';
                        if ($k == $summaryColCount - 1) {
                            $class .= ' right_column';
                        }
                        $this->addHTML('<div class="'.$class.'">' . $row->Summary->ColData[$k]->value . '</div>');
                    }
                    $this->addHTML('</div>');
                }
            }
            $this->addHTML('</div>');
        }
    }

    protected function buildViewFooter() {
        
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
