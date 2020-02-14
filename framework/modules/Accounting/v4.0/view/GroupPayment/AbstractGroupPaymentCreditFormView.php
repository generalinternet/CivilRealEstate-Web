<?php
/**
 * Description of AbstractGroupPaymentCreditFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentCreditFormView extends AbstractGroupPaymentFormView {

    protected function buildHeader() {
        if (empty($this->groupPayment->getProperty('id'))) {
            $verb = 'Add';
        } else {
            $verb = 'Edit';
        }
        $this->form->addHTML('<h1>' . $verb . ' ' . $this->groupPayment->getTypeTitle() . '</h1>');
    }

    protected function addPaymentTypeField() {
        $this->form->addField('group_payment_type', 'hidden', array(
            'value' => $this->groupPayment->getTypeRef()
        ));
    }

    protected function buildTopFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentTypeField();
        $this->addFromContactField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column two_thirds">');
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentAmountField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addCurrencyField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addDateField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function buildBottomFields() {
        
    }

    protected function buildMemoAndUploaderFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->buildMemoField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
   //    $this->addPrintField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addFromContactField($displayName = '') {
        parent::addFromContactField('For Contact');
    }

    protected function addPaymentAmountField($displayName = 'Amount') {
        parent::addPaymentAmountField('Credit Amount');
    }
    
    protected function addDateField($displayName = 'Date') {
        parent::addDateField('Issue Date');
    }
    
    protected function addPrintField() {
        $this->form->addField('print', 'onoff', array(
            'displayName'=>'Print Credit Note',
            'value'=>1
        ));
    }

}
