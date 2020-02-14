<?php
/**
 * Description of AbstractGroupPaymentOutputView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentCreditOutputView extends PDFLayoutView{
    
    protected $groupPayment;
    protected $outputTypeLabel = 'Credit Note';
    protected $showPrintDate = false;
    
    public function __construct(AbstractGroupPaymentCredit $groupPayment) {
        $this->groupPayment = $groupPayment;
        $this->outputTypeLabel .= ' ' . $groupPayment->getProperty('transaction_number');
        parent::__construct();
    }
    
    public function getRightHeaderContent() {
        return '';
    }

    public function buildView() {
        $this->addHTML('<div class="pdf_content">');
        $this->addHTML('<h1>Credit Note</h1>');
        $this->addHTML('<table>')
                ->addHTML('<tr>')
                ->addHTML('<td width="40%" valign="top">');
        $this->addDetailsSection();
        $this->addHTML('</td>')
                ->addHTML('<td width="40%" valign="top" style="margin-left:5%;">');
        $this->addContactDetailsSection();
        $this->addHTML('</td>')
                ->addHTML('</tr>')
                ->addHTML('</table>');
        $this->addMemoSection();
        $this->addHTML('<br />');
        $this->addAppliedToInvoicesSection();
        $this->addHTML('</div>');
    }

    protected function addDetailsSection() {
        $this->addHTML('<div class="clear">&nbsp;</div>');
        $this->addHTML('<p><b>Transaction #</b>&nbsp;&nbsp;' . $this->groupPayment->getProperty('transaction_number') .'</p><br/>');
        $this->addHTML('<p><b>Issued</b>&nbsp;&nbsp;' . GI_Time::formatDateForDisplay($this->groupPayment->getProperty('date')) .'</p><br/>');
        $currency = $this->groupPayment->getCurrency();
        $currencyName = $currency->getProperty('name');
        $this->addHTML('<p><b>Amount</b>&nbsp;&nbsp;$' . GI_StringUtils::formatMoney($this->groupPayment->getProperty('amount')) .' '. $currencyName .'</p><br/>');
        $balance = $this->groupPayment->getBalance(true);
        $this->addHTML('<p><b>Balance</b>&nbsp;&nbsp;' . $balance .' ' . $currencyName.'</p><br/>');
    }
    
    protected function addContactDetailsSection() {
        $this->addHTML('<div class="clear">&nbsp;</div>');
        $contact = $this->groupPayment->getContact();
        $contactInfo = $contact->getBillingAddressInfo();
        if (!empty($contactInfo)) {
            $addressString = $contactInfo->getAddressString(true);
        } else {
            $addressString = $contact->getAddress(true);
        }
        $this->addHTML('<p><b>Issued To</b>&nbsp;&nbsp;' . $contact->getName() .'</p><br/>');
        $this->addHTML('<p>'.$addressString.'</p>');
    }
    
    protected function addMemoSection() {
        $this->addHTML('<div class="clear">&nbsp;</div>');
        $this->addHTML('<p><b>Memo</b></p>');
        $this->addHTML('<p>'.$this->groupPayment->getProperty('memo').'</p>');
    }
    
    protected function addAppliedToInvoicesSection() {
        $payments = $this->groupPayment->getPayments();
        $currency = $this->groupPayment->getCurrency();
        if (!empty($payments)) {
            $this->addHTML('<h2>Applied To Invoices</h2>');
            foreach ($payments as $payment) {
                $invoiceNumber = $payment->getAppliedToName();
                $appliedAmount = GI_StringUtils::formatMoney($payment->getProperty('amount'));
                $appliedDate = GI_Time::formatDateForDisplay($payment->getProperty('date'));
                $this->addHTML('<p>'.$appliedDate.'&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$invoiceNumber.'</b>&nbsp;&nbsp;&nbsp;&nbsp;$'.$appliedAmount.' '.$currency->getProperty('name').'</p>');
            }
        }
        
    }

    public function getHTMLFooter(Mpdf\Mpdf $pdf = NULL){
        $footer = '<p>';
        $footer .= '</p><br/>';
        $footer .= parent::getHTMLFooter($pdf);
        return $footer;
    }
    
}