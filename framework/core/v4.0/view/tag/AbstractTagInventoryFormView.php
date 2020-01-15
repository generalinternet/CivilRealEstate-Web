<?php
/**
 * Description of AbstractTagInventoryFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractTagInventoryFormView extends AbstractTagFormView {

    protected function addTitleField() {
        $value = $this->tag->getProperty('title');
        if (empty($value)) {
            $value = 'Inventory Tag';
        }
        $this->form->addField('title', 'text', array(
            'value' => $value,
            'displayName' => 'Label'
        ));
    }

}
