<?php
/**
 * Description of AbstractContentTextFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextFormView extends AbstractContentFormView {
    
    public function buildFormGuts() {
        parent::buildFormGuts();
        
        $this->form->addField($this->content->getFieldName('content'), 'textarea', array(
            'displayName' => 'Content',
            'placeHolder' => 'Content',
            'value' => $this->content->getProperty('content_text.content')
        ));
    }
    
}
