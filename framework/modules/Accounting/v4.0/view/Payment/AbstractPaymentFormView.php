<?php
/**
 * Description of AbstractPaymentFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentFormView extends GI_FormRowView {

    protected $seqNumFieldName = 'payments';
    protected $modelFieldPrefix = 'payment';
    protected $payment;

    public function __construct(\GI_Form $form, AbstractPayment $payment) {
        parent::__construct($form);
        $this->payment = $payment;
    }

    public function buildForm() {
        $this->openFormRowWrap();
        $this->addRequiredInfo();
        $this->addRemoveBtnWrap();
        $this->form->addHTML('<div class="form_row_fields">');
        $this->addFields();
        $this->form->addHTML('</div>');
        $this->closeFormRowWrap();
    }

    protected function addFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addAutocompIdField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addDateField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addAmountField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    public function getFieldName($fieldName) {
        return $this->payment->getFieldName($fieldName);
    }

    public function getFieldSuffix() {
        return $this->payment->getFieldSuffix();
    }
    
    protected function addAutocompIdField() {
        //Do nothing
    }
    
    protected function addDateField() {
        $this->form->addField($this->getFieldName('date'), 'date', array(
            'value'=>$this->payment->getProperty('date'),
            'displayName'=>'Date',
            'required'=>true,
            'fieldClass'=>'apply_payment_date'
        ));
    }
    
    protected function addAmountField() {
        $this->form->addField($this->getFieldName('amount'), 'money', array(
            'value'=>$this->payment->getProperty('amount'),
            'displayName'=>'Amount',
            'required'=>true,
            'fieldClass'=>'amount_to_apply'
        ));
    }

    protected function getModelId() {
        return $this->payment->getProperty('id');
    }

    protected function getModelTypeRef() {
        return $this->payment->getTypeRef();
    }

}
