<?php
/**
 * Description of AbstractContactLocWarehouseFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactLocWarehouseFormView extends AbstractContactLocFormView {

    protected function addTypeField() {
        $this->form->addField('type_ref', 'hidden', array(
            'value' => $this->contact->getTypeRef(),
        ));
    }

}
