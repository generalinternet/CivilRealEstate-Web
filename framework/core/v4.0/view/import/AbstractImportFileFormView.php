<?php

/**
 * Description of AbstractImportFileFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractImportFileFormView extends GI_View {

    protected $form;
    protected $otherFormData;

    public function __construct($form, $otherFormData = array()) {
        parent::__construct();
        $this->form = $form;
        $this->otherFormData = $otherFormData;
    }

    protected function buildForm() {

        $this->buildFormHeader();
        $this->buildFormBody();
        $this->buildFormFooter();
    }

    protected function buildFormHeader() {
        if (isset($this->otherFormData['title'])) {
            $title = $this->otherFormData['title'];
        } else {
            $title = '';
        }

        $this->form->addHTML('<h2>Import ' . $title . '</h2>');
    }

    protected function buildFormBody() {
        $this->addImportFileField();
    }

    protected function addImportFileField() {
        $this->form->addField('import_file', 'file', array(
            'displayName' => 'Import File',
            'placeHolder' => 'Import File',
            'required' => true,
            'formElementClass' => 'form_element fake_required',
            'description' => 'Select the file you wish to import.'
        ));
    }

    protected function buildFormFooter() {
        
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->form->setBtnText('');
        $this->form->addHTML('<div class="center_btns wrap_btns"><span class="submit_btn" title="Import" tabindex="0" >Import</span></div>');
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
