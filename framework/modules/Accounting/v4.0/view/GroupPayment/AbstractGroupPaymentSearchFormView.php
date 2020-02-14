<?php

/**
 * Description of AbstractGroupPaymentSearchFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
class AbstractGroupPaymentSearchFormView extends GI_SearchView {

    public $type = '';
    protected $boxId = 'group_payment_search_box';

    public function __construct(\GI_Form $form, $queryValues = array(), $type = 'expense') {
        $this->type = $type;
        $this->setBoxId($this->boxId);
        parent::__construct($form, $queryValues);
    }

    protected function buildForm() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');

        $this->form->addField('search_trans_number', 'text', array(
            'displayName' => 'Search by Transaction Number',
            'placeHolder' => 'Transaction Number',
            'value' => $this->getQueryValue('transaction_number')
        ));

        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');

        $contactAutoCompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1
        ));

        $this->form->addHTML('<div class="columns thirds">');
        $showCurrency = false;
        $contactClass = '';
        if (ProjectConfig::getHasMultipleCurrencies()) {
            $showCurrency = true;
            $contactClass = 'column two_thirds';
        }
        $this->form->addField('search_contact_id', 'autocomplete', array(
            'displayName' => 'Contact',
            'placeHolder' => 'start typing...',
            'autocompURL' => $contactAutoCompURL,
            'value' => $this->getQueryValue('contact_id'),
            'hideDescOnError' => false,
            'formElementClass' => 'get_addr ' . $contactClass
        ));
        if ($showCurrency) {
            $this->form->addField('search_currency_id', 'dropdown', array(
                'displayName' => 'Currency',
                'options' => CurrencyFactory::getOptionsArray('name'),
                'value' => $this->getQueryValue('currency_id'),
                'formElementClass' => 'column'
            ));
        } else {
            $this->form->addDefaultCurrencyField($this->getQueryValue('currency_id'), 'search_currency_id');
        }

        $this->form->addHTML('</div>');

        $this->form->addHTML('</div>')
                ->addHTML('</div>');

        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');

        $this->addDateFields();

        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addDateFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addStartDateField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addEndDateField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addStartDateField() {
        $this->form->addField('search_start_date', 'date', array(
            'displayName' => 'Dates Between',
            'placeHolder' => 'Start Date',
            'value' => $this->getQueryValue('start_date')
        ));
    }

    protected function addEndDateField() {
        $this->form->addField('search_end_date', 'date', array(
            'displayName' => 'And',
            'placeHolder' => 'End Date',
            'value' => $this->getQueryValue('end_date')
        ));
    }

}
