<?php

/**
 * Description of AbstractContactCatClientFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactCatClientFormView extends AbstractContactCatFormView {

    protected function buildFormBody() {
        parent::buildFormBody();
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addDefaultPricingRegionField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            $this->addTermsField();
        }
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            $this->addInterestRatesField();
        }
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    public function addTermsField() {
        $termsURL = GI_URLUtils::buildUrl(array(
                    'controller' => 'autocomplete',
                    'action' => 'terms',
                    'ajax' => 1
        ));
        $this->form->addField('terms_id', 'autocomplete', array(
            'value' => $this->contactCat->getProperty('contact_cat_client.terms_id'),
            'displayName' => 'Terms',
            'autocompURL' => $termsURL,
            'required' => false,
            'autocompMinLength' => 0,
            'description' => 'Payment terms.',
            'placeHolder' => 'Payment Terms'
        ));
    }

    protected function addInterestRatesField() {
        $useDefaultRate = 1;
        $interestRatePercent = $this->contactCat->getProperty('contact_cat_client.interest_rate');
        $cmpdXDays = $this->contactCat->getProperty('contact_cat_client.cmpd_x_days');

        if ($this->form->wasSubmitted()) {
            $useDefaultRate = filter_input(INPUT_POST, 'use_default_rate');
        } else if (!empty($interestRatePercent) || !empty($cmpdXDays)) {
            $useDefaultRate = 0;
        }
        $this->form->addField('use_default_rate', 'onoff', array(
            'displayName' => 'Use Default Interest Rates',
            'value' => $useDefaultRate,
            'onoffStyleAsCheckbox' => true,
        ));

        $settingsDefIntRate = $this->contactCat->getSettingsDefIntRate();
        if (!empty($settingsDefIntRate)) {
            $defaultRate = $settingsDefIntRate->getProperty('interest_rate');
            $defaultCompoundDays = $settingsDefIntRate->getProperty('cmpd_x_days');
        } else {
            $defaultRate = 0.0;
            $defaultCompoundDays = 0;
        }

        $this->form->addHTML('<div class="def_int_rate_desc' . ((!$useDefaultRate) ? ' hide_on_load' : '') . '">');
        $this->form->addHTML('<p class="content_block">');
        $this->form->addHTML('Interest Rate : ' . ($defaultRate * 100) . ' %<br>');
        $this->form->addHTML('Compounded every ' . $defaultCompoundDays . ' day(s)');
        $this->form->addHTML('</p>');
        $this->form->addHTML('</div>');

        $this->form->addHTML('<div class="def_int_rate_form' . (($useDefaultRate) ? ' hide_on_load' : '') . '">');
        $this->form->addHTML('<div class="columns halves top_align">')
                ->addHTML('<div class="column">');

        $this->form->addField('interest_rate', 'percentage', array(
            'displayName' => 'Interest Rate',
            'value' => $interestRatePercent * 100,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addField('cmpd_x_days', 'integer', array(
            'displayName' => 'Compounded every x Day(s)',
            'placeHolder' => 'Days',
            'value' => $cmpdXDays,
        ));
        $this->form->addHTML('</div>')
                ->addHTML('</div>')
                ->addHTML('</div><!--.def_int_rate_form-->');
    }
    
    protected function addDefaultPricingRegionField($overWriteSettings = array()) {
        $value = $this->contactCat->getProperty('contact_cat_client.default_pricing_region_id');
        $options = PricingRegionFactory::getOptionsArray();
        if (!empty($options)) {
            if (empty($value)) {
                reset($options);
                $value = key($options);
            }
            if (!Permission::verifyByRef('set_pricing_region') || count($options) < 2) {
                $this->form->addField('default_pricing_region_id', 'hidden', array(
                    'value'=>$value,
                ));
            } else {
                $fieldSettings = GI_Form::overWriteSettings(array(
                    'options'=>$options,
                    'value'=>$value,
                    'displayName'=>'Pricing Region',
                ), $overWriteSettings);
                $this->form->addField('default_pricing_region_id', 'dropdown', $fieldSettings);
            }
        } 
    }

}
