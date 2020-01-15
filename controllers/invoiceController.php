<?php

require_once 'framework/modules/Invoice/' . MODULE_INVOICE_VER . '/controller/AbstractInvoiceController.php';

class InvoiceController extends AbstractInvoiceController {

    public function actionIndex($attributes) {
        $attributes['generalTypeSearch'] = 1;
        return parent::actionIndex($attributes);
    }
    
}
