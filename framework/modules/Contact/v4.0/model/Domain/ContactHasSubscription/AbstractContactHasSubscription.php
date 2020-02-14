<?php

/**
 * Description of AbstractContactHasSubscription
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactHasSubscription extends GI_Model {

    /** @var AbstractSubscription */
    protected $subscription = NULL;
    /** @var AbstractContact */
    protected $contact = NULL;
    
    protected $nextPaymentDate = NULL;
    protected $externalId = NULL;
    protected $startDate = NULL;
    protected $endDate = NULL;
    protected $isActive = NULL;
    /**
     * 
     * @return AbstractSubscription
     */
    public function getSubscription() {
        if (empty($this->subscription)) {
            $this->subscription = SubscriptionFactory::getModelById($this->getProperty('subscription_id'));
        }
        return $this->subscription;
    }

    /**
     * 
     * @return AbstractContact
     */
    public function getContact() {
        if (empty($this->contact)) {
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }

    public function setSubscription(AbstractSubscription $subscription) {
        $this->subscription = $subscription;
        $this->setProperty('subscription_id', $subscription->getId());
    }

    public function setContact(AbstractContact $contact) {
        $this->contact = $contact;
        $this->setProperty('contact_id', $contact->getId());
    }

    public function getDetailView() {
        return new ContactHasSubscriptionDetailView($this);
    }

    public function getNextPaymentDate($formatForDisplay = true) {
        if (empty($this->nextPaymentDate)) {
            $date = NULL;
            $cacheKey = $this->getNextPaymentDateCacheKey();
            if (!empty($cacheKey) && apcu_exists($cacheKey)) {
                $date = apcu_fetch($cacheKey);
                $currentDate = GI_Time::getDateTime();
                if (!GI_Time::isSameDay($currentDate, $date)) {
                    $this->nextPaymentDate = $date;
                    if ($formatForDisplay) {
                        return GI_Time::formatDateForDisplay($date);
                    }
                    return $date;
                } else {
                    apcu_delete($cacheKey);
                }
            }

            $subscription = $this->getSubscription();
            if (empty($subscription)) {
                return NULL;
            }
//            $paymentProcessor = $subscription->getPaymentProcessor();
//            if (empty($paymentProcessor)) {
//                return NULL;
//            }
            $contact = $this->getContact();
            if (empty($contact)) {
                return NULL;
            }
     //       $paymentProcessor->setContact($contact);
     //       $billingDates = $paymentProcessor->getStripeSubscriptionBillingPeriodDates($subscription);
            $billingDates = $subscription->getExternalBillingPeriodDates($contact);
            if (!empty($billingDates) && isset($billingDates['end'])) {
                $endDateTimeObject = $billingDates['end'];
                $nextPaymentDate = $endDateTimeObject->format('Y-m-d');
                $this->nextPaymentDate = $nextPaymentDate;
                apcu_store($cacheKey, $nextPaymentDate, 43200);
            }
        }
        $date = $this->nextPaymentDate;
        if (!empty($date) && $formatForDisplay) {
            return GI_Time::formatDateForDisplay($date);
        }
        return $date;
    }
    
    protected function getNextPaymentDateCacheKey() {
        return 'sub_next_pay_date_' . $this->getId();
    }
    
    public function getStartDate($formatForDisplay = true) {
       $dateTime = new DateTime($this->getProperty('start_date_time'));
       $date = $dateTime->format('Y-m-d');
       if ($formatForDisplay) {
           return GI_Time::formatDateForDisplay($date);
       }
       return $date;
    }
    
    public function getEndDate($formatForDisplay = true) {
        $endDateString = $this->getProperty('end_date_time');
        if (empty($endDateString)) {
            return NULL;
        }
        $dateTime = new DateTime($endDateString);
        $date = $dateTime->format('Y-m-d');
        if ($formatForDisplay) {
            return GI_Time::formatDateForDisplay($date);
        }
        return $date;
    }

    public function isActive() {
        if (is_null($this->isActive)) {
            $currentDateTime = new DateTime(GI_Time::getDateTime());
            $startDateTime = new DateTime($this->getProperty('start_date_time'));
            if (!($currentDateTime > $startDateTime)) {
                $this->isActive = false;
                return $this->isActive;
            }
            $endDateString = $this->getProperty('end_date_time');
            if (empty($endDateString)) {
                $this->isActive = true;
                return $this->isActive;
            }
            $endDateTime = new DateTime($endDateString);
            if ($currentDateTime < $endDateTime) {
                $this->isActive = true;
                return $this->isActive;
            }
            $this->isActive = false;
        }
        return $this->isActive;
    }

}
