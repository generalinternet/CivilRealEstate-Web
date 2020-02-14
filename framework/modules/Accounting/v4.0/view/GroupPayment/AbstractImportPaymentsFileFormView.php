<?php
/**
 * Description of AbstractImportPaymentsFileFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractImportPaymentsFileFormView extends AbstractImportFileFormView {

    protected function buildFormHeader() {
        if (isset($this->otherFormData['title'])) {
            $title = $this->otherFormData['title'];
        } else {
            $title = '';
        }

        $this->form->addHTML('<h1>Import ' . $title . '</h1>');
    }

    protected function buildFormBody() {
        $this->addImportFileField();
    }

}
