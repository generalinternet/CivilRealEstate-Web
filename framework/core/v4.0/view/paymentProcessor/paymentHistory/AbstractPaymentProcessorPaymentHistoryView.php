<?php

/**
 * Description of AbstractPaymentProcessorPaymentHistoryView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractPaymentProcessorPaymentHistoryView extends MainWindowView {

    protected $payments;

    public function __construct($payments = array()) {
        parent::__construct();
        $this->payments = $payments;
    }

    protected function addViewBodyContent() {
        //DO nothing
    }

}