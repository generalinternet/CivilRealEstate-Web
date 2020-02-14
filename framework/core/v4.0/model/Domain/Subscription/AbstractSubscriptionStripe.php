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

    protected function determineStartDateForContactSubscription(AbstractContact $contact) {
        $currentSub = $contact->getCurrentSubscription();
        if (empty($currentSub)) {
            return parent::determineStartDateForContactSubscription($contact);
        }
        if ($currentSub->isFree()) {
            return parent::determineStartDateForContactSubscription($contact);
        } else {
            $currentPrice = (float) $currentSub->getProperty('price');
            $thisPrice = (float) $this->getProperty('price');
            if (!GI_Math::floatEquals($currentPrice, $thisPrice) && $thisPrice > $currentPrice) {
                return parent::determineStartDateForContactSubscription($contact);
            }
            //else, start date of new sub is end of paid period of current subscription
            $paymentProcessor = new StripePaymentProcessor();
            $paymentProcessor->setContact($contact);
            $dates = $paymentProcessor->getStripeSubscriptionBillingPeriodDates($currentSub);
            
            if (!empty($dates) && isset($dates['end'])) {
                $startDateTime = $dates['end'];
                if (!empty($startDateTime)) {
                    return $startDateTime->format('Y-m-d H:i:s');
                }
            }
        }
        return parent::determineStartDateForContactSubscription($contact);
    }

    protected function determineEndDateForContactSubscription(AbstractContact $contact) {
        $currentSub = $contact->getCurrentSubscription();
        if (empty($currentSub)) {
            return parent::determineEndDateForContactSubscription($contact);
        }
        if ($currentSub->isFree()) {
            return parent::determineEndDateForContactSubscription($contact);
        } else {
            //TODO - this needs to be revised. Need to get next sub and compare price to that, not this
            $currentPrice = (float) $currentSub->getProperty('price');
            $thisPrice = (float) $this->getProperty('price');
            if (!GI_Math::floatEquals($currentPrice, $thisPrice) && $thisPrice > $currentPrice) {
                return parent::determineStartDateForContactSubscription($contact);
            }
            //else, start date of new sub is end of paid period of current subscription
            $paymentProcessor = new StripePaymentProcessor();
            $paymentProcessor->setContact($contact);
            $dates = $paymentProcessor->getStripeSubscriptionBillingPeriodDates($currentSub);
            
            if (!empty($dates) && isset($dates['end'])) {
                $endDateTime = $dates['end'];
                if (!empty($endDateTime)) {
                    return $endDateTime->format('Y-m-d H:i:s');
                }
            }
        }
        return parent::determineEndDateForContactSubscription($contact);
    }

    public function getExternalBillingPeriodDates(AbstractContact $contact) {
        if ($this->isFree()) {
            return parent::getExternalBillingPeriodDates($contact);
        }
        $paymentProcessor = $this->getPaymentProcessor();
        $paymentProcessor->setContact($contact);
        return $paymentProcessor->getStripeSubscriptionBillingPeriodDates($this);
    }

}
