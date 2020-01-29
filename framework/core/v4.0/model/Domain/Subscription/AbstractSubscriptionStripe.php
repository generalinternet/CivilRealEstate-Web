<?php
/**
 * Description of AbstractSubscriptionStripe
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractSubscriptionStripe extends AbstractSubscription {

    public function getPaymentProcessor() {
        $processor = parent::getPaymentProcessor();
        if (empty($processor)) {
            $processor = new StripePaymentProcessor();
            $this->paymentProcessor = $processor;
        }
        return $processor;
    }

    protected function subscribeContactToOnlineService(AbstractContact $contact) {
        $paymentProcessor = $this->getPaymentProcessor();
        if (empty($paymentProcessor)) {
            return false;
        }
        $paymentProcessor->setContact($contact);
        $stripeCustomerId = $paymentProcessor->getStripeCustomerId();
        if (empty($stripeCustomerId) && !$this->isFree()) {
            //TODO - need to create a new stripe customer before proceeding
        } else if (empty($stripeCustomerId) && $this->isFree()) {
            return true;
        }
        if (!$paymentProcessor->subscribeCustomerToPaymentPlan($this)) {
            return false;
        }
        return true;
    }

    protected function unsubscribeContactFromOnlineService(AbstractContact $contact) {
        $paymentProcessor = $this->getPaymentProcessor();
        if (empty($paymentProcessor)) {
            return false;
        }
        $paymentProcessor->setContact($contact);
        $stripeCustomerId = $paymentProcessor->getStripeCustomerId();
        if (empty($stripeCustomerId)) {
            return true;
        }
        if (!$paymentProcessor->unsubscribeCustomerFromPaymentPlan($this)) {
            return false;
        }
        return true;
    }
    
    public function getStripeSubscriptionId(AbstractContact $contact) {
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $contact->getId())
                ->filter('subscription_id', $this->getId());
        $search->orderBy('id', 'ASC');
        $results = $search->select();
        if (!empty($results)) {
            $linkDAO = $results[0];
            return $linkDAO->getProperty('external_id');
        }
        return NULL;
    }

    public function setStripeSubscriptionId(AbstractContact $contact, $stripeId = '') {
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $contact->getId())
                ->filter('subscription_id', $this->getId());
        $search->orderBy('id', 'ASC');
        $results = $search->select();
        if (empty($results)) {
            return false;
        }
        $linkDAO = $results[0];
        $linkDAO->setProperty('external_id', $stripeId);
        if (!$linkDAO->save()) {
            return false;
        }
        return true;
    }

}
