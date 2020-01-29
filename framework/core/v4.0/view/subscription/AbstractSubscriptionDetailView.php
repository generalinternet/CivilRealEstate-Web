<?php
/**
 * Description of AbstractSubscriptionDetailView
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractSubscriptionDetailView extends MainWindowView {

    protected $subscription;

    public function __construct(AbstractSubscription $subscription) {
        parent::__construct();
        $this->subscription = $subscription;
    }

    protected function addViewBodyContent() {
        $subscription = $this->subscription;
        $this->addHTML('<h3>' . $subscription->getProperty('title') . '</h3>');
        $this->addHTML('<p>' . $subscription->getProperty('description') . '</p>');
        $this->addHTML('<h4 class="value">$ ' . $subscription->getProperty('price') . '</h4>');
    }

}
