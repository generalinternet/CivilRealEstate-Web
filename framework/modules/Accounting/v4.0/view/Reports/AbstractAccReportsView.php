<?php
/**
 * Description of AbstractAccReportsView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
abstract class AbstractAccReportsView extends MainWindowView {
    
    protected $reports;
    protected $startDate;
    protected $endDate;
    protected $form;
    protected $activeType = '';
    protected $iconClass = 'primary';
    protected $mainContentClass = 'qb_related_content acc_report_qb_related_content';
    protected $addViewHeader = false;
    
    public function __construct($reports, $activeType, DateTime $startDate, DateTime $endDate, GI_Form $form) {
        parent::__construct();
        $this->reports = $reports;
        $this->activeType = $activeType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->form = $form;
        $this->addJS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.js');
        $this->addCSS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.css');
        $this->addJS('resources/external/js/raphael.min.js');
        $this->addCSS('resources/external/js/morris/morris.css');
        $this->addJS('resources/external/js/morris/morris.min.js');
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/custom_morris.js');
    }

    protected function addQuickbooksBar() {
        $qbBar = QBConnection::getQuickbooksBarView();
        if ($qbBar) {
            $this->addHTML($qbBar->getHTMLView());
        }
    }

    protected function addReportsBar() {
        $currency = CurrencyFactory::getModelByRef(ProjectConfig::getDefaultCurrencyRef());
        $this->addHTML('<div class="reports_bar_wrap" id="report_bar_main" data-active-type="'.$this->activeType.'">');
        $this->addHTML('<div class="reports_bar">');
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col longer_title vert_center">');
        $this->addHTML('<h3>Currency</h3>');
        $this->addHTML('<p class="content_block">' . $currency->getProperty('name') . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col vert_center">');
        $this->addHTML('<h3>start</h3>');
        $this->addHTML('<p class="content_block">' . GI_Time::formatDateForDisplay($this->startDate->format('Y-m-d')) . '</p>');
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col vert_center">');
        $this->addHTML('<h3>end</h3>');
        $this->addHTML('<p class="content_block">' . GI_Time::formatDateForDisplay($this->endDate->format('Y-m-d')) . '</p>');
        $this->addHTML('</div>');
        $this->addReportsBarBtns();
        $this->addHTML('<div class="flex_col vert_center">');
        $this->addReportSelector();
        $this->addHTML($this->form->getForm(''));
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function openOuterWrap(){
        $this->addQuickbooksBar();
        $this->addReportsBar();
        
        if (QBConnection::isConnectionValid()) {
            $this->mainContentClass .= ' connected';
        }
        parent::openOuterWrap();
        return $this;
    }
    
    protected function addReportsBarBtns() {
        $this->addHTML('<div class="flex_col vert_center">');
            $changeDatesURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'reports'
            ));
            $this->addHTML('<a href="' . $changeDatesURL . '" title="Change Dates" class="custom_btn" id="report_change_dates"><span class="icon_wrap border circle"><span class="icon ' . $this->iconClass . ' calendar"></span></span><span class="btn_text">Change Dates</span></a>');
            $this->addHTML('</div>')
                    ->addHTML('<div class="flex_col vert_center">');
            $csvExportURL = GI_URLUtils::buildURL(array(
                        'controller' => 'accounting',
                        'action' => 'exportReportCSV',
                        'start' => $this->startDate->format('Y-m-d'),
                        'end' => $this->endDate->format('Y-m-d'),
            ));
            $this->addHTML('<a href="' . $csvExportURL . '" title="Export CSV" class="custom_btn" id="report_export_csv"><span class="icon_wrap border circle"><span class="icon ' . $this->iconClass . ' export"></span></span><span class="btn_text">CSV</span></a>');
            $this->addHTML('</div>');
    }
    
    protected function addReportSelector() {
        $options = array();
        foreach ($this->reports as $report) {
            $options[$report->getTypeRef()] = $report->getTitle();
        }
        $this->form->addField('report_type_select', 'dropdown', array(
            'options'=>$options,
            'value'=>$this->activeType,
            'hideNull'=>true,
            'showLabel'=>false,
        ));
    }
    
    protected function addViewBodyContent() {
        if (!empty($this->reports)) {
            if (isset($this->reports[$this->activeType])) {
                $activeReport = $this->reports[$this->activeType];
                $this->addReportSection($activeReport, true);
            }
            foreach ($this->reports as $typeRef=>$report) {
                if ($typeRef != $this->activeType) {
                    $this->addReportSection($report);
                }
            }
        }
        return $this;
    }
    
    protected function addReportSection(AbstractAccReport $report, $active = false) {
        $type = $report->getTypeRef();
        $url = GI_URLUtils::buildURL(array(
            'controller' => 'accounting',
                    'action' => 'getReport',
                    'type' => $type,
                    'start'=>$this->startDate->format('Y-m-d'),
                    'end'=>$this->endDate->format('Y-m-d'),
        ));
        $class = 'report_section';
        if (!($active)) {
            $class .= ' ' . $report->getHideCSSClass();
        } 
        $this->addHTML('<div class="'.$class.'" id="'.$type.'">');
        $this->addHTML('<div class="ajaxed_contents auto_load" data-url="'.$url.'">');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
}