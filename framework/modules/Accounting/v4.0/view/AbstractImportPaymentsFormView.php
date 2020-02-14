<?php
/**
 * Description of AbstractImportPaymentsFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractImportPaymentsFormView extends GI_View {
    
    protected $form;
    protected $groupPayments;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, $groupPayments) {
        parent::__construct();
        $this->form = $form;
        $this->groupPayments = $groupPayments;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    protected function buildFormHeader() {
        $this->form->addHTML('<h1>Import Payments</h1>');
    }

    protected function buildFormBody() {
        $this->buildTable($this->groupPayments);
    }
    
    protected function buildTable($groupPayments) {
        if (!empty($this->groupPayments)) {
//            $this->form->addHTML('<div class="flex_table">')
//                    ->addHTML('<div class="flex_row flex_head">')
//                    ->addHTML('<div class="flex_col sml">Import</div>')
//                    ->addHTML('<div class="flex_col">Date</div>')
//                    ->addHTML('<div class="flex_col">Amount</div>')
//                    ->addHTML('<div class="flex_col">Currency*</div>')
//                    ->addHTML('<div class="flex_col">Payee</div>')
//                    ->addHTML('<div class="flex_col">Vendor*</div>')
//                    ->addHTML('</div>');
//            foreach ($groupPayments as $rowNumber => $groupPayment) {
//                $this->buildTableRow($groupPayment, $rowNumber);
//            }
//            $this->form->addHTML('</div>');
            $this->form->addHTML('<table class="ui_table">');
            $this->form->addHTML('<thead>')
                    ->addHTML('<tr>')
                    ->addHTML('<th>Import</th>')
                    ->addHTML('<th>Date</th>')
                    ->addHTML('<th>Amount</th>')
                    ->addHTML('<th>Currency*</th>')
                    ->addHTML('<th>Payee</th>')
                    ->addHTML('<th>Vendor*</th>')
                    ->addHTML('</tr>')
                    ->addHTML('</thead>')
                    ->addHTML('<tbody>');
            foreach ($groupPayments as $rowNumber => $groupPayment) {
                $this->buildTableRow($groupPayment, $rowNumber);
            }
            $this->form->addHTML('</tbody>')
                    ->addHTML('</table>');
        }
    }

    protected function buildTableRow(AbstractGroupPayment $groupPayment, $rowNumber) {
//        $this->form->addHTML('<tr>')
//                ->addHTML('<td>' . $this->addImportSelectField($rowNumber) . '</td>')
//                ->addHTML('<td>' . $this->addDateCell($groupPayment) . '</td>')
//                ->addHTML('<td>' . $this->addAmountCell($groupPayment) . '</td>')
//                ->addHTML('<td>' . $this->addCurrencyField($groupPayment, $rowNumber) . '</td>')
//                ->addHTML('<td>' . $this->addPayeeCell($groupPayment) . '</td>')
//                ->addHTML('<td>' . $this->addVendorField($groupPayment, $rowNumber) . '</td>')
//                ->addHTML('</tr>');
        $this->form->addHTML('<tr>');
        $this->addImportSelectField($rowNumber);
        $this->addDateCell($groupPayment);
        $this->addAmountCell($groupPayment);
        $this->addCurrencyField($groupPayment, $rowNumber);
        $this->addPayeeCell($groupPayment);
        $this->addVendorField($groupPayment, $rowNumber);
        $this->form->addHTML('</tr>');
    }

    protected function addImportSelectField($rowNumber) {
        $this->form->addHTML('<td>');
        $this->form->addField('select_' . $rowNumber, 'onoff', array(
            'displayName'=>'',
            'value'=>1,
        ));
        $this->form->addHTML('</td>');
    }
    
    protected function addDateCell(AbstractGroupPayment $groupPayment) {
        $this->form->addHTML('<td>' . GI_Time::formatDateForDisplay($groupPayment->getProperty('date')) . '</td>');
    }
    
    protected function addAmountCell(AbstractGroupPayment $groupPayment) {
        $this->form->addHTML('<td>$' . GI_StringUtils::formatMoney($groupPayment->getProperty('amount')) . '</td>');
    }
    
    protected function addPayeeCell(AbstractGroupPaymentImported $groupPayment) {
        $payeeString = $groupPayment->getPayeeString();
        if (empty($payeeString)) {
            $payeeString = '--';
        }
        $this->form->addHTML('<td>'.$payeeString.'</td>');
    }

    protected function addCurrencyField(AbstractGroupPayment $groupPayment, $rowNumber) {
        $this->form->addHTML('<td>');
        if (ProjectConfig::getHasMultipleCurrencies()) {
            $options = CurrencyFactory::getOptionsArray('name');
            $this->form->addField('currency_' . $rowNumber, 'dropdown', array(
                'options' => $options,
                'value' => $groupPayment->getProperty('currency_id'),
                'displayName' => '',
                'required'=>true,
                'showLabel'=>false,
            ));
        } else {
            $currencyId = ProjectConfig::getDefaultCurrencyId();
            $currency = CurrencyFactory::getModelById($currencyId);
            $this->form->addField('currency_' . $rowNumber, 'hidden', array(
                'value'=>  $currencyId,
            ));
            $this->form->addHTML($currency->getProperty('name'));
        }
        $this->form->addHTML('</td>');
    }

    protected function addVendorField(AbstractGroupPayment $groupPayment, $rowNumber) {
        $this->form->addHTML('<td>');
        $autocompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1,
            'catTypeRefs' => 'vendor',
        ));
        $this->form->addField('contact_id_' . $rowNumber, 'autocomplete', array(
            'displayName' => '',
            'placeHolder' => 'start typing...',
            'autocompURL' => $autocompURL,
            'value' => $groupPayment->getProperty('contact_id'),
            'required' => true,
            'showLabel'=>false,
        ));
        $this->form->addHTML('</td>');
    }

    protected function addTypeField(AbstractGroupPayment $groupPayment, $rowNumber) {
        $options = GroupPaymentFactory::getTypesArray();
        $this->form->addField('type_' . $rowNumber, 'dropdown', array(
            'options'=>$options,
            'displayName'=>'',
            'value'=>$groupPayment->getTypeRef(),
        ));
    }

    protected function buildFormFooter() {
        $this->addButtons();
    }
    
    protected function addButtons() {
        $this->form->addHTML('<span class="submit_btn">Import Selected</span>');
    }
    
    protected function buildView() {
        $this->buildForm();
        $this->openViewWrap();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
       $this->buildView();
    }

}
