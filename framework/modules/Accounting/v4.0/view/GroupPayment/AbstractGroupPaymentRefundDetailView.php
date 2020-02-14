<?php
/**
 * Description of AbstractGroupPaymentRefundDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentRefundDetailView extends AbstractGroupPaymentDetailView {

    public function getDetailView() {
        $view = new GroupPaymentRefundDetailView($this);
        $uploader = $this->getUploader();
        $view->setUploader($uploader);
        return $view;
    }

    protected function addAmount() {
        $paymentAmount = (float) $this->groupPayment->getProperty('amount');
        $paymentAmount = $paymentAmount * -1;
        $currency = $this->groupPayment->getCurrency();
        $displayValue = '$' .GI_StringUtils::formatMoney($paymentAmount) . ' ('.$currency->getProperty('name').')';
        $this->addContentBlock($displayValue, 'Amount');
    }

    protected function addBalance() {
        //DO Nothing
    }

}
