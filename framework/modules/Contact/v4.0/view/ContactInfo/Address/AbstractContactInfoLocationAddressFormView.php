<?php
/**
 * Description of AbstractContactInfoLocationAddressFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactInfoLocationAddressFormView extends AbstractContactInfoAddressFormView {

    protected $countryFieldReadOnly = false;

    public function setCountryFieldReadOnly($countryFieldReadOnly) {
        $this->countryFieldReadOnly = $countryFieldReadOnly;
    }

    protected function addStreetField($overWriteSettings = array()) {
        $overWriteSettings['required'] = true;
        return parent::addStreetField($overWriteSettings);
    }

    protected function addCityField($overWriteSettings = array()) {
        $overWriteSettings['required'] = true;
        return parent::addCityField($overWriteSettings);
    }

    protected function addCountryField($overWriteSettings = array()) {
        if ($this->countryFieldReadOnly) {
            $overWriteSettings['readOnly'] = true;
        }
        $overWriteSettings['required'] = true;
        return AbstractContactInfoAddressFormView::addCountryField($overWriteSettings);
    }

    protected function addCodeField($overWriteSettings = array()) {
        $overWriteSettings['required'] = true;
        return parent::addCodeField($overWriteSettings);
    }

}
