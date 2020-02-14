<?php

abstract class AbstractQBExportInvAdjustmentWasteFormView extends AbstractExportAdjustmentsToQBFormView {

    /** @var AbstractInvAdjustmentWaste */
    protected $model;

    public function __construct(\GI_Form $form, AbstractInvAdjustmentWaste $model, $values) {
        parent::__construct($form, $model, $values);
    }

    protected function addExportableModelInfoSection() {
        $itemNameAndPackConfig = $this->model->getNameAndPackConfigContentsString();
        $itemSKU = $this->model->getInvItemSKU();
        $qty = $this->model->getQtyForExport();
        $dateEntered = $this->model->getAdjustmentDate(true);
        $shipmentNumber = $this->model->getEnteredByUserName();
        $this->form->addHTML('<div class="form_section">');
        $this->form->addHTML('<h2 class="form_section_title">Wasted Item(s)</h2>');
        $this->form->addHTML('<div class="form_section_body">');
        $this->form->addHTML('<div class="flex_table no_border white_bg_head">');
        $this->form->addHTML('<div class="flex_row flex_head">')
                ->addHTML('<div class="flex_col">SKU</div>')
                ->addHTML('<div class="flex_col lrg">Item: Item Package</div>')
                ->addHTML('<div class="flex_col sml">Qty</div>')
                ->addHTML('<div class="flex_col">Date Entered</div>')
                ->addHTML('<div class="flex_col">Entered By</div>')
                ->addHTML('</div>');
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">'.$itemSKU.'</div>')
                ->addHTML('<div class="flex_col lrg">'.$itemNameAndPackConfig.'</div>')
                ->addHTML('<div class="flex_col sml">'.$qty.'</div>')
                ->addHTML('<div class="flex_col">'.$dateEntered.'</div>')
                ->addHTML('<div class="flex_col">'.$shipmentNumber.'</div>')
                ->addHTML('</div>');
        $this->form->addHTML('</div><!--.flex_table-->')
                ->addHTML('</div><!--.form_section_body-->')
                ->addHTML('</div><!--.form_section-->');
    }

    protected function buildFormBody() {
        $this->form->addHTML('<div class="form_section">');
        $this->form->addHTML('<h2 class="form_section_title">Wasted Stock Expenses</h2>');
        
        $seqNum = 0;
        $values = $this->values;
        $debitAccountQBId = $this->model->getInvWasteAccountQBId();
        if (!empty($values)) {
            foreach ($values as $currencyRef => $currencyValues) {
                $this->form->addHTML('<div class="form_section_body">');
                $currencyTotal = 0;
                $currencyModel = CurrencyFactory::getModelByRef($currencyRef);
                $this->form->addHTML('<h3 class="table_title qb_colour">' . $currencyModel->getLongName() . '</h3>');
                $this->form->addHTML('<div class="flex_table no_border white_bg_head dgrey_bg_footer">');
                $this->form->addHTML('<div class="flex_row flex_head">')
                        ->addHTML('<div class="flex_col med">Expense Type</div>')
                        ->addHTML('<div class="flex_col sml">Value</div>')
                        ->addHTML('<div class="flex_col med">Credit Account</div>')
                        ->addHTML('<div class="flex_col lrg">Debit Account</div>')
                        ->addHTML('<div class="flex_col lrg">Memo</div>')
                        ->addHTML('</div>');
                foreach ($currencyValues as $qbInvAssetAccountQbId => $assetValues) {
                    $invAssetAccount = QBAccountFactory::getModelByQBId($qbInvAssetAccountQbId);
                    foreach ($assetValues as $poLineTypeRef => $value) {
                        if (!empty($value)) {
                            $currencyTotal += $value;
                            $catTitle = '';
                            $exampleQBJournalEntryCat = QBJournalEntryCatFactory::getModelByPOLineTypeRef($poLineTypeRef);
                            if (!empty($exampleQBJournalEntryCat)) {
                                $catTitle = $exampleQBJournalEntryCat->getProperty('title');
                            }
                            $this->form->addHTML('<div class="flex_row">')
                                    ->addHTML('<div class="flex_col med">');
                            $this->form->addHTML('<input name="qb_adj_iaw_seq_nums[]" value="' . $seqNum . '" type="hidden" class="seq_count"/>');
                            $this->form->addField('po_line_type_ref_' . $seqNum, 'hidden', array(
                                'value'=>$poLineTypeRef,
                            ));
                            $this->form->addHTML($catTitle);
                            $this->form->addHTML('</div>')
                                    ->addHTML('<div class="flex_col sml">');
                            $this->form->addField('cogs_val_' . $seqNum, 'hidden', array(
                                'value'=>$value,
                            ));
                            $this->form->addField('currency_id_' . $seqNum, 'hidden', array(
                                'value'=>$currencyModel->getId(),
                            ));
                            $this->form->addHTML('$' . GI_StringUtils::formatMoney($value));
                            $this->form->addHTML('</div>')
                                    ->addHTML('<div class="flex_col med">');
                            $this->form->addField('cred_acct_qb_id_' . $seqNum, 'hidden', array(
                                'value'=>$invAssetAccount->getProperty('qb_id'),
                            ));
                            $this->form->addHTML($invAssetAccount->getNumberAndName());
                            $this->form->addHTML('</div>')
                                    ->addHTML('<div class="flex_col lrg">');
                            $this->addDebitAccountField($seqNum, $debitAccountQBId);
                            $this->form->addHTML('</div>')
                                    ->addHTML('<div class="flex_col lrg">');
                            $this->addMemoField($seqNum);
                            $this->form->addHTML('</div>')
                                    ->addHTML('</div>');
                        }
                        $seqNum++;
                    }
                }
                $this->form->addHTML('<div class="flex_row flex_foot">')
                        ->addHTML('<div class="flex_col med">' . $currencyModel->getProperty('name') . ' TOTAL</div>')
                        ->addHTML('<div class="flex_col sml">$' . GI_StringUtils::formatMoney($currencyTotal) . '</div>')
                        ->addHTML('<div class="flex_col med"></div>')
                        ->addHTML('<div class="flex_col lrg"></div>')
                        ->addHTML('<div class="flex_col lrg"></div>')
                        ->addHTML('</div>');
                $this->form->addHTML('</div><!--.flex_table-->')
                        ->addHTML('</div><!--.form_section_body-->');
            }
            $this->form->addHTML('</div><!--.form_section-->');
        }
    }

    protected function addDebitAccountField($seqNum, $value) {
        $options = QBAccountFactory::getAccountOptionsArray(array('expense'), $value);
        $this->form->addField('debit_acct_qb_id_' . $seqNum, 'dropdown', array(
            'options'=>$options,
            'value'=>$value,
            'showLabel'=>false,
            'hideNull'=>true,
            'formElementClass'=>'autofocus_off',
        ));
    }
    
    protected function addMemoField($seqNum) {
        $value = $this->model->buildQuickbooksJournalEntryDescription();
        $this->form->addField('memo_' . $seqNum, 'text', array(
            'value'=>$value,
            'showLabel'=>false,
        ));
    }
}