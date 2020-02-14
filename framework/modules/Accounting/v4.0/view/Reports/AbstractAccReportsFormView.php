<?php
/**
 * Description of AbstractAccReportFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportsFormView extends MainWindowView {
    
    protected $form;
    protected $formBuilt = false;
    /** @var AbstractAccReport[] */
    protected $reports;
    protected $selectedTypeRef = NULL;
    protected $mainContentClass = 'qb_related_content acc_report_qb_related_content';
    
    public function __construct(GI_Form $form, $reports, $selectedTypeRef = NULL) {
        parent::__construct();
        $this->form = $form;
        $this->reports = $reports;
        $this->selectedTypeRef = $selectedTypeRef;
        $this->addCSS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.css');
        //$this->addJS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.js');
        $this->setWindowTitle('Accounting Reports');
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildTimePeriodSection();
            $this->buildReportTypeSection();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildTimePeriodSection() {
        $this->form->addHTML('<div class="form_body give_me_space">');
            $this->form->addHTML('<h2>1. choose time period</h2>');
            $this->form->addHTML('<hr />');
            $this->form->addHTML('<div class="flex_row">')
                    ->addHTML('<div class="flex_col blank_title">');
            $this->addFiscalYearField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="flex_col blank_title">');
            $this->addTimePeriodField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="flex_col sml vert_center center_align blank_title">');
            $this->form->addHTML('<p class="text_content">OR</p>');
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="flex_col med">');
            $this->addStartDateField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="flex_col">');
            $this->addEndDateField();
            $this->form->addHTML('</div>')
                    ->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addFiscalYearField() {
        $this->form->addField('fiscal_year', 'dropdown', array(
            'options'=>array_reverse(GI_Time::getFiscalYearOptionsArray(ProjectConfig::getSystemLiveDate())),
            'nullText'=>'Fiscal Year',
            'showLabel'=>false,
        ));
    }
    
    protected function addTimePeriodField() {
        $dates = GI_Time::getFiscalYearStartAndEndDates();
        $options = GI_Time::getReportingPeriodOptions($dates['start'], $dates['end']);
        $this->form->addField('time_period', 'dropdown', array(
            'options'=>$options,
            'nullText'=>'Time Period',
            'showLabel'=>false,
        ));
    }
    
    protected function addStartDateField() {
        $this->form->addField('start_date', 'date', array(
            'displayName'=>'Start Date'
        ));
    }
    
    protected function addEndDateField() {
        $this->form->addField('end_date', 'date', array(
            'displayName' => 'End Date',
            'minDateFromField' => 'start_date'
        ));
    }

    protected function buildReportTypeSection() {
        $this->form->addHTML('<div class="form_body">');
            $this->form->addHTML('<h2>2. select report type</h2>');
            $this->form->addHTML('<hr />');
            $this->form->addHTML('<div class="report_options">');
            $this->form->addField('report_type_error', 'hidden');
            $reports = $this->reports;
            $keys = array_keys($reports);
            $count = count($reports);
            $col = 0;
            $columns = 2;
            $maxColIndex = $columns - 1;
            $emptyCellCount = 0;
            $remainder = $count % $columns;
            if ($remainder > 0) {
                $emptyCellCount = $columns - $remainder;
            }
            for ($i = 0; $i < ($count + $emptyCellCount); $i++) {
                if ($col == 0) {
                    $this->form->addHTML('<div class="flex_row">');
                }
                $this->form->addHTML('<div class="flex_col">');
                if ($i < $count) {
                $report = $reports[$keys[$i]];
                $summaryView = $report->getSummaryView();
                $type = $report->getTypeRef();
                $fieldProperties = array(
                    'showLabel' => false,
                    'options'=>array(
                        $type => $summaryView->getHTMLView()
                    ),
                    'fieldClass'=>'report_type'
                );
                if($report->isDisabled()){
                    $fieldProperties['disabledOptions'] = array($type);
                }
                if (!empty($this->selectedTypeRef) && $this->selectedTypeRef == $type) {
                    $fieldProperties['value'] = $type;
                }
                $this->form->addField('report_type[]', 'radio', $fieldProperties);
                } else {
                    //Empty cell
                }
                $this->form->addHTML('</div>');
                if ($col == $maxColIndex || $i == ($count + $emptyCellCount) - 1) {
                    $this->form->addHTML('</div>');
                }
                $col++;
                if ($col > $maxColIndex) {
                    $col = 0;
                }
            }
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function buildFormFooter() {
        $this->buildSubmitSection();
    }

    protected function buildSubmitSection() {
        $this->form->addHTML('<h2>3. submit</h2>');
        $this->form->addHTML('<hr />');
        $this->form->addHTML('<div class="center_align"><span class="submit_btn">Run Report</span></div>');
    }

    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
        return $this;
    }
    
    public function buildView() {
        $this->addQuickbooksBar();
        parent::buildView();
        return $this;
    }

    protected function addQuickbooksBar() {
        $qbBar = QBConnection::getQuickbooksBarView();
        if ($qbBar) {
            $this->addHTML($qbBar->getHTMLView());
        }
    }

//    protected function openViewWrap() {
//        $qbConnectedClass = '';
//        if (QBConnection::isConnectionValid()) {
//            $qbConnectedClass = 'connected';
//        }
//        
//        $this->addHTML('<div class="content_padding qb_related_content '.$qbConnectedClass.'" id="qb_related_content_wrap">');
//        parent::openViewWrap();
//    }
    
    protected function openOuterWrap(){
        if (QBConnection::isConnectionValid()) {
            $this->mainContentClass .= ' connected';
        }
        parent::openOuterWrap();
        return $this;
    }
}

