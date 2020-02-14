<?php
/**
 * Description of AbstractContactPaymentsDetailView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactPaymentsDetailView extends MainWindowView {
    
    protected $contact = NULL;
    protected $paymentMethods = NULL;
    protected $payments = NULL;
    
    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
    }
    
    public function setPayments($payments) {
        $this->payments = $payments;
    }
    
    public function setPaymentMethods($paymentMethods) {
        $this->paymentMethods = $paymentMethods;
    }

    protected function addViewBodyContent() {
        $this->addHTML('<div class="auto_columns halves">');
        $this->addSubscriptionsSection();
        $this->addPaymentMethodsSection();
        $this->addHTML('</div>');
        $this->addPaymentHistorySection();
    }

    protected function addPaymentMethodsSection() {
        $this->addHTML('<div class="contact_payment_methods">');
        if ($this->contact->canAddPaymentMethod()) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contactprofile',
                        'action' => 'addPaymentMethod',
                        'id' => $this->contact->getId(),
            ));
            $this->addHTML('<div class="right_btns">');
            $this->addHTML('<a href="' . $addURL . '" title="Add Payment Method" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getSVGIcon('plus') . '<span class="btn_text">Payment Method</span></a>');
            $this->addHTML('</div>');
        }
        $this->addHTML('<h3>Payment Methods</h3>');
        $defaultPaymentMethod = $this->contact->getDefaultPaymentMethod();
        $paymentMethods = $this->contact->getPaymentMethods();
        
        $canChangeDefaultPaymentMethod = $this->contact->canChangeDefaultPaymentMethod();
        $canRemovePaymentMethod = $this->contact->canRemovePaymentMethod();
        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                $id = $paymentMethod['id'];
                $isDefault = false;
                if ($id === $defaultPaymentMethod['id']) {
                    $isDefault = true;
                }
                $this->addHTML('<div class="flex_row vert_center">')
                        ->addHTML('<div class="flex_col">');
                $view = new CreditCardDetailView($paymentMethod);
                $view->setOnlyBodyContent(true);
                $view->setIsDefault($isDefault);
                $this->addHTML($view->getHTMLView());
                $this->addHTML('</div>')
                        ->addHTML('<div class="flex_col xx_sml">');
                if (!$isDefault && $canRemovePaymentMethod) {
                    $removeURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contactprofile',
                                'action' => 'removePaymentMethod',
                                'id' => $paymentMethod['id'],
                                'cId' => $this->contact->getId(),
                    ));
                    $this->addHTML('<a href="' . $removeURL . '" title="Remove Payment Method" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getSVGIcon('trash') . '</a>');
                }
                $this->addHTML('</div>')
                        ->addHTML('<div class="flex_col xx_sml">');
                if (!$isDefault && $canChangeDefaultPaymentMethod) {
                    $setAsDefaultURL = GI_URLUtils::buildURL(array(
                                'controller' => 'contactprofile',
                                'action' => 'setPaymentMethodAsDefault',
                                'id' => $paymentMethod['id'],
                                'cId' => $this->contact->getId(),
                    ));
                    $this->addHTML('<a href="' . $setAsDefaultURL . '" title="Set as Default Payment Method" class="custom_btn open_modal_form" data-modal-class="medium_sized">' . GI_StringUtils::getSVGIcon('check') . '</a>');
                }
                $this->addHTML('</div>')
                        ->addHTML('</div>');
            }
        } else {
            $this->addHTML('<p>No payment method found</p>');
        }
        $this->addHTML('</div>');
    }

    protected function addSubscriptionsSection() {
        $this->addHTML('<div class="contact_subscriptions">');
        $changeURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contactprofile',
                    'action' => 'changeSubscription',
                    'id' => $this->contact->getId(),
        ));
        $this->addHTML('<div class="right_btns">');
        $this->addHTML('<a href="' . $changeURL . '" title="Change Subscription" class="custom_btn">' . GI_StringUtils::getSVGIcon('pencil') . '<span class="btn_text">Change Subscription</span></a>');
        $this->addHTML('</div>');
        $this->addHTML('<h3>Current Subscription</h3>');
        $currentContactHasSubscription = $this->contact->getCurrentContactHasSubscription();
        if (!empty($currentContactHasSubscription)) {
            $view = $currentContactHasSubscription->getDetailView();
            $view->setOnlyBodyContent(true);
            $this->addHTML($view->getHTMLView());
        } else {
            $this->addHTML('<p>No subscription found</p>');
        }
        $upcomingContactHasSubscription = $this->contact->getUpcomingContactHasSubscription();
        if (!empty($upcomingContactHasSubscription)) {
            $this->addHTML('<h3>Upcoming Subscription</h3>');
            $view = $upcomingContactHasSubscription->getDetailView();
            $view->setOnlyBodyContent(true);
            $this->addHTML($view->getHTMLView());
        }

        $this->addHTML('</div>');
    }

    protected function addPaymentHistorySection() {
        $historyView = $this->contact->getChargeHistoryView();
        if (!empty($historyView)) {
            $this->addHTML('<h3>History</h3>');
            $historyView->setOnlyBodyContent(true);
            $this->addHTML($historyView->getHTMLView());
        }
    }

}
