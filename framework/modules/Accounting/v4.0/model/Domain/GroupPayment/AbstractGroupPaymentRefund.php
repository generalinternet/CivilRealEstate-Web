<?php
/**
 * Description of AbstractGroupPaymentRefund
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentRefund extends AbstractGroupPayment {

    /**
     * @param GI_Form $form
     * @param AbstractPayment $payment
     * @return \AccountingGroupPaymentFormView
     */
    public function getFormView(GI_Form $form, AbstractPayment $payment) {
        $view = new AccountingGroupPaymentRefundFormView($form, $this, $payment);
        $view->setGPTypeLocked(true);
        return $view;
    }

    public function handleFormSubmission(GI_Form $form, AbstractPayment $examplePayment) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            if (!$this->setPropertiesFromForm($form)) {
                return NULL;
            }
            $refundAmount = (float) filter_input(INPUT_POST, 'payment_amount');
            $negatedRefundAmount = $refundAmount * -1;
            $this->setProperty('amount', $negatedRefundAmount);
            $this->setProperty('default_payment_type_ref', $examplePayment->getTypeRef());
            $invoiceId = filter_input(INPUT_POST, 'invoice_id');
            $invoice = InvoiceFactory::getModelById($invoiceId);
            if (empty($invoice) || !$invoice->isRefundable()) {
                GI_URLUtils::redirectToError(1000);
            }
             $taxRegionIds = filter_input(INPUT_POST, 'tax_region_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
             $taxRegions = array();
             $combinedTaxRate = 0;
             if (!empty($taxRegionIds)) {
                 foreach ($taxRegionIds as $taxRegionId) {
                     $taxRegion = TaxRegionFactory::getModelById($taxRegionId);
                     $taxRegions[$taxRegionId] = $taxRegion;
                     $combinedTaxRate += (float) $taxRegion->getProperty('rate');
                 }
             }

            if (!$this->save()) {
                return NULL;
            }
            $income = $invoice->getIncome();
            $examplePayment->setProperty('group_payment_id', $this->getProperty('id'));
            $examplePayment->setProperty('amount', $negatedRefundAmount);
            $examplePayment->setProperty('date', $this->getProperty('date'));
            $examplePayment->setProperty('applicable_date', $this->getProperty('date'));
            $examplePayment->setProperty('void', 0);
            $examplePayment->setProperty('cancelled', 0);
            $examplePayment->setProperty('payment_income.income_id', $income->getProperty('id'));
            if (!$examplePayment->save()) {
                return NULL;
            }
            $principalSum = (float) $refundAmount/(1 + $combinedTaxRate);
            $taxSum = $refundAmount - $principalSum;
            $taxAdjustedNegatedAmount = $negatedRefundAmount + $taxSum;
            $incomeItem = IncomeItemFactory::buildNewModel('refund');
            $incomeItem->setProperty('income_id', $income->getProperty('id'));
            $incomeItem->setProperty('tax_code_qb_id', $this->getProperty('tax_code_qb_id'));
            $incomeItem->setProperty('net_amount', $taxAdjustedNegatedAmount);
            $incomeItem->setProperty('applicable_date', $this->getProperty('date'));
            $incomeItem->setProperty('void', 0);
            $incomeItem->setProperty('cancelled', 0);
            if ($invoice->isFinalized()) {
                $incomeItem->setProperty('in_progress', 0);
            } else {
                $incomeItem->setProperty('in_progress', 1);
            }
            if (!$incomeItem->save()) {
                return NULL;
            }
            
            if (!IncomeItemFactory::linkIncomeItemsAndTaxRegionWithBalancing(array($incomeItem), $taxRegions)) {
                return false;
            }
            
            $invoiceLine = InvoiceLineFactory::buildNewModel('price_per_refund');
            $invoiceLine->setProperty('invoice_id', $invoice->getProperty('id'));
            $invoiceLine->setProperty('name', 'Refund');
            $invoiceLine->setProperty('description', $this->getProperty('memo'));
            $invoiceLine->setProperty('income_item_type_ref', 'refund');
            $invoiceLine->setProperty('invoice_line_price_per.qty', 1);
            $invoiceLine->setProperty('invoice_line_price_per.price', $taxAdjustedNegatedAmount);
            $invoiceLine->setProperty('invoice_line_price_per.total', $taxAdjustedNegatedAmount);
            $invoiceLine->setProperty('invoice_line_price_per_refund.group_payment_id', $this->getProperty('id'));
            $invoiceLine->setProperty('invoice_line_price_per_refund.payment_id', $examplePayment->getProperty('id'));
            if (!$invoiceLine->save()) {
                return NULL;
            }
            
            if (!($income->save() && $invoice->save())) {
                return NULL;
            }
            if (!IncomeItemFactory::linkIncomeItemToModel($invoiceLine, $incomeItem)) {
                return NULL;
            }
            return $this;
        }
        return NULL;
    }

    public function validateForm(\GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $invoiceId = filter_input(INPUT_POST, 'invoice_id');
            $invoice = InvoiceFactory::getModelById($invoiceId);
            if (empty($invoice)) {
                return false;
            }
            $income = $invoice->getIncome();
            if (empty($income)) {
                return false;
            }
            $maxRefundableSum = (float) $income->getMaxRefundableSum();
            $refundAmount = (float) filter_input(INPUT_POST, 'payment_amount');
            if (!GI_Math::floatEquals($refundAmount, $maxRefundableSum)) {
                if ($refundAmount > $maxRefundableSum) {
                    $form->addFieldError('payment_amount', 'exceed', 'The refund amount cannot exceed the sum of payments applied to this invoice');
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function void($notes = '') {
        $invoiceLineTableName = InvoiceLineFactory::getDbPrefix() . 'invoice_line';
        $invoiceLineSearch = InvoiceLineFactory::search();
        $invoiceLineSearch->join('invoice_line_price_per', 'parent_id', $invoiceLineTableName, 'id', 'ILPP')
                ->join('invoice_line_price_per_refund', 'parent_id', 'ILPP', 'id', 'ILPPR')
                ->filter('ILPPR.group_payment_id', $this->getProperty('id'));
        $invoiceLines = $invoiceLineSearch->select();
        if (!empty($invoiceLines)) {
            foreach ($invoiceLines as $invoiceLine) {
                $invoice = $invoiceLine->getInvoice();
                $incomeItems = $invoiceLine->getIncomeItems();
                if (!empty($incomeItems)) {
                    foreach ($incomeItems as $incomeItem) {
                        if (!(IncomeItemFactory::unlinkIncomeItemFromModel($invoiceLine, $incomeItem) && $incomeItem->void())) {
                            return false;
                        }
                    }
                }
                $invoiceLineMap = $invoiceLine->getMap();
                if (!($invoiceLineMap->softDelete() && $invoice->save())) {
                    return false;
                }
            }
        }
        return parent::void($notes);
    }

    public function getDetailView() {
        $view = new AccountingGroupPaymentRefundDetailView($this);
        return $view;
    }

    public function getIsEditable() {
        return false;
    }

    public function getIsVoidable() {
        return false;
    }

}
