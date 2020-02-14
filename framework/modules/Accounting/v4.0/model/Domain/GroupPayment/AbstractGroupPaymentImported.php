<?php

/**
 * Description of AbstractGroupPaymentImported
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentImported extends AbstractGroupPayment {

    protected $payeeString = '';
    protected $tableWrapId = 'group_payment_imported_table';
    protected static $searchFormId = 'group_payment_imported_search';

    public function getSearchFormView(GI_Form $form, $searchValues = NULL, $type = NULL) {
        $view = new GroupPaymentSearchFormView($form, $searchValues, $type);
        $view->setBoxId('group_payment_imported_search_box');
        return $view;
    }
    
    public function getPayeeString() {
        return $this->payeeString;
    }
    
    public function setPayeeString($payeeString) {
        $this->payeeString = $payeeString;
    }

}
