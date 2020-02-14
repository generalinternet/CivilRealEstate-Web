<?php
/**
 * Description of AbstractAccountingExportFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    4.0.0
 */
Abstract class AbstractAccountingExportFormView extends MainWindowView {
    
    protected $form;
    protected $formBuilt = false;
    protected $fiscalYearOptions = array();
    protected $exportTypeOptions = array();
    
    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
        $this->setWindowTitle('Exports');
    }
    
    public function setFiscalYearOptions($fiscalYearOptions) {
        $this->fiscalYearOptions = $fiscalYearOptions;
    }
    
    public function setExportTypeOptions($exportTypeOptions) {
        $this->exportTypeOptions = $exportTypeOptions;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<div class="columns halves">')
                    ->addHTML('<div class="column">');
            $this->form->addHTML('<h3>Which type of export would you like to generate?</h3>');
            $this->addExportTypeField();
            $this->form->addHTML('</div>')
                    ->addHTML('<div class="column">');
            $this->addExtraInfoFields();
            $this->form->addHTML('</div>')
                    ->addHTML('</div>');
            $this->form->addHTML('<span class="submit_btn">Submit</span>');
            $this->formBuilt = true;
        }
    }
    protected function addViewBodyContent(){
        $this->addSiteTitle('Accounting');
        $this->addSiteTitle('Exports');
        $this->addHTML($this->form->getForm());
    }

    protected function addExportTypeField() {
        $this->form->addField('export_type', 'radio', array(
            'showLabel' => false,
            'options' => $this->exportTypeOptions,
            'formElementClass' => 'list_options',
            'stayOn' => true,
            'fieldClass' => 'radio_toggler'
        ));
    }

    protected function addExtraInfoFields() {
        if (Permission::verifyByRef('export_ar_invoices')) {
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="export_type" data-element="ar_invoices">');
            $this->form->addField('export_ar_invoices_cat', 'radio', array(
                'displayName' => 'Categorized By',
                'options' => array(
                    'date' => 'Invoice Date',
                    'due_date' => 'Invoice Due Date'
                ),
                'value' => 'due_date',
            ));
            $this->form->addHTML('</div>');
        }
    }

}
