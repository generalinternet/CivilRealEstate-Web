<?php

class TestFormView extends GI_View {

    protected $form;

    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
    }

    public function buildForm() {
        $this->buildFormBody();
    }

    protected function buildFormBody() {
        $this->buildClientField();
        $this->form->addHTML('<br />');
        $this->buildVendorField();
        $this->form->addHTML('<br />');
        $this->buildInternalField();
        $this->form->addHTML('<br />');
    }

    protected function buildClientField($overWriteSettings = array()) {

        $autocompProps = array(
            'controller' => 'contactprofile',
            'action' => 'autocompContact',
            'ajax' => 1,
            'autocompField' => 'client_contact_id',
            'catTypeRef' => 'client',
        );
        $autocompURL = GI_URLUtils::buildURL($autocompProps);

        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Client',
                    'placeHolder' => 'start typing...',
                    'autocompURL' => $autocompURL,
                    'value' => '53',
                    'hideDescOnError' => false
                        ), $overWriteSettings);

        $this->form->addField('client_contact_id', 'autocomplete', $fieldSettings);
    }

    protected function buildVendorField($overWriteSettings = array()) {
        $autocompProps = array(
            'controller' => 'contactprofile',
            'action' => 'autocompContact',
            'ajax' => 1,
            'autocompField' => 'vendor_contact_id',
            'catTypeRef' => 'vendor',
        );
        $autocompURL = GI_URLUtils::buildURL($autocompProps);

        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Vendor',
                    'placeHolder' => 'start typing...',
                    'autocompURL' => $autocompURL,
                    'value' => '',
                    'hideDescOnError' => false
                        ), $overWriteSettings);

        $this->form->addField('vendor_contact_id', 'autocomplete', $fieldSettings);
    }

    protected function buildInternalField($overWriteSettings = array()) {
        $autocompProps = array(
            'controller' => 'contactprofile',
            'action' => 'autocompContact',
            'ajax' => 1,
            'autocompField' => 'internal_contact_id',
            'catTypeRef' => 'internal',
        );
        $autocompURL = GI_URLUtils::buildURL($autocompProps);

        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => 'Internal',
                    'placeHolder' => 'start typing...',
                    'autocompURL' => $autocompURL,
                    'value' => '',
                    'hideDescOnError' => false
                        ), $overWriteSettings);

        $this->form->addField('internal_contact_id', 'autocomplete', $fieldSettings);
    }

    public function beforeReturningView() {
        $this->addHTML($this->form->getForm(''));
    }

}
