<?php
/**
 * Description of GI_FormRowTaxableView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class GI_FormRowTaxableView extends GI_FormRowView {
    
    public function __construct(\GI_Form $form) {
        parent::__construct($form);
        $this->addFormRowClass('form_row');
    }

    protected function getSubTotal() {
        return NULL;
    }
    
    protected function addSubtotalField($overWriteSettings = array()) {
        $subtotal = $this->getSubTotal();
        $value = NULL;
        if (!empty($subtotal)) {
            $value = GI_StringUtils::formatMoneyForField($subtotal);
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Subtotal',
                    'placeHolder' => 'Subtotal',
                    'fieldClass' => 'taxable_row_subtotal',
                    'formElementClass'=>'taxable_row_subtotal_felm',
                    'value' => $value,
                        ), $overWriteSettings);

        $this->form->addField($this->getFieldName('subtotal'), 'money', $fieldSettings);
    }

    protected function getTaxCodeQBId() {
        return NULL;
    }
    
    protected function addTaxField($overWriteSettings = array(), $date = NULL, $ratesType = 'sales') {
        $taxCodeQbId = $this->getTaxCodeQBId();
        if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
            if (!empty($taxCodeQbId)) {
                $taxCodeQbId = 1;
            } else {
                $taxCodeQbId = 0;
            }
        }
        $overWriteSettings['value'] = $taxCodeQbId;
        $this->form->addTaxField($this->getFieldName('tax_code_qb_id'), $overWriteSettings, $date, $ratesType);
    }

    protected function getTotal() {
        return NULL;
    }

    protected function addTotalField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Total',
                    'placeHolder' => 'Total',
                    'fieldClass'=>'taxable_row_total',
                    'value' =>$this->getTotal(),
                        ), $overWriteSettings);

        $this->form->addField($this->getFieldName('total'), 'money', $fieldSettings);
    }

    protected function openFormRowWrap() {
        $seqNumber = $this->forceGetSeqNumber();
        $this->form->addHTML('<div class="taxable_row ' . $this->getFormRowClass() . '" data-seq-number="' . $seqNumber . '">');
    }

    protected function closeFormRowWrap() {
        $this->form->addHTML('</div>');
    }

}
