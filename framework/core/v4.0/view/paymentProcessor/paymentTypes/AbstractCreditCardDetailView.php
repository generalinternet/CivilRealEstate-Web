<?php
/**
 * Description of AbstractCreditCardDetailView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractCreditCardDetailView extends MainWindowView {
    
    protected $creditCardDataArray = array();
    
    public function __construct($creditCardDataArray) {
        parent::__construct();
        $this->creditCardDataArray = $creditCardDataArray;
    }
    
    protected function addViewBodyContent() {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addBrand();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addLastFour();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addExpiry();
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addBrand() {
        if (isset($this->creditCardDataArray['brand'])) {
            $brand = $this->creditCardDataArray['brand'];
        } else {
            $brand = '';
        }
        $this->addHTML($brand);
    }

    protected function addLastFour() {
        if (isset($this->creditCardDataArray['last_four'])) {
            $lastFour = $this->creditCardDataArray['last_four'];
        } else {
            $lastFour = '';
        }
        $this->addHTML('Ending in ' . $lastFour);
    }

    protected function addExpiry() {
        if (isset($this->creditCardDataArray['exp_month'])) {
            $expMonth = $this->creditCardDataArray['exp_month'];
        } else {
            $expMonth = '';
        }
        if (isset($this->creditCardDataArray['exp_year'])) {
            $expYear = $this->creditCardDataArray['exp_year'];
        } else {
            $expYear = '';
        }
        $expiry = $expMonth . '/' . $expYear;
        $this->addHTML('Expiring in ' .$expiry);
    }

}
