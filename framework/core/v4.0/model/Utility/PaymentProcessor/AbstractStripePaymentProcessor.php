<?php
/**
 * Description of AbstractStripePaymentProcessor
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractStripePaymentProcessor extends AbstractPaymentProcessor {

    protected $stripeCustomerObject = NULL;
    protected $stripeCustomerId = NULL;
    
    protected static $settingsPaymentTypeRef = 'payment_stripe';
    protected static $subscriptionTypeRef = 'stripe';
    

    public function __construct() {
        \Stripe\Stripe::setApiKey(ProjectConfig::getStripeSecretKey());
    }

    public function getStripeCustomerId() {
        if (empty($this->stripeCustomerId)) {
            $settings = $this->contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                return NULL;
            }
            $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
            if (empty($custId)) {
                return NULL;
            }
            $this->stripeCustomerId = $custId;
        }
        return $this->stripeCustomerId;
    }

    public function getIdempotentcyKey() {
        $contact = $this->getContact();
        if (empty($contact)) {
            return GI_StringUtils::generateRandomString(16);
        }
        $cacheKey = 'stripe_idem_key_' . $contact->getId();
        if (apcu_exists($cacheKey)) {
            return apcu_fetch($cacheKey);
        }
        
        $key = GI_StringUtils::generateRandomString(16);
        apcu_store($cacheKey, $key, 5);
        return $key;
    }

    public function getCreditCardFormView(GI_Form $form) {
        $view = new StripePaymentProcessorCreditCardFormView($form);
        $view->setContact($this->getContact());
        return $view;
    }

    public function getCharges() {
        if (empty($this->charges)) {
            $cacheKey = $this->getChargesCacheKey();
            if (!empty($cacheKey) && apcu_exists($cacheKey)) {
                $this->charges = apcu_fetch($cacheKey);
                return $this->charges;
            }
            $limit = 10;
            $contact = $this->getContact();
            if (empty($contact)) {
                return NULL;
            }
            $settings = $contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                return NULL;
            }
            $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
            if (empty($custId)) {
                return NULL;
            }
            try {
                $chargeObjectArray = \Stripe\Charge::all([
                    'limit' => $limit,
                    'customer'=>$custId
                    ]);
            } catch (Exception $ex) {
                return NULL;
            }
            $charges = array();
            if (!empty($chargeObjectArray)) {
                $chargeObjectArrayData = $chargeObjectArray->data;
                foreach ($chargeObjectArrayData as $chargeObject) {
                    $id = $chargeObject->id;
                    $amount = $chargeObject->amount / 100;
                    $description = $chargeObject->description;
                    $receiptURL = $chargeObject->receipt_url;
                    $status = $chargeObject->status;
                    $charge = array(
                        'id' => $id,
                        'amount' => $amount,
                        'description'=>$description,
                        'receipt_url'=>$receiptURL,
                        'status'=>$status,
                    );

                    $createdTimestamp = $chargeObject->created;
                    if (!empty($createdTimestamp)) {
                        $dateTime = new DateTime("@$createdTimestamp");
                        GI_Time::getUserTimezone($dateTime);
                        $charge['date_time'] = $dateTime->format('Y-m-d H:i:s');
                    }


                    $paymentSource = $chargeObject->source;
                    if (!empty($paymentSource)) {
                        $cardLastFour = $paymentSource->last4;
                        $cardBrand = $paymentSource->brand;
                        $charge['card_brand'] = $cardBrand;
                        $charge['card_last_four'] = $cardLastFour;
                    }
                    
                    $charges[] = $charge;
                }
            }
            if (!empty($cacheKey)) {
                apcu_store($cacheKey, $charges, 86400);
            }
            $this->charges = $charges;
        }
        return $this->charges;
    }

    public function getPaymentMethods() {
        if (empty($this->paymentMethods)) {
            $this->paymentMethods = $this->getCards();
        }
        return $this->paymentMethods;
    }
    
    public function getDefaultPaymentMethod() {
        return $this->getDefaultCard();
    }


    public function handleCreditCardFormSubmission(GI_Form $form, $emailForReceipt = NULL) {
        if ($form->wasSubmitted() && $this->validateCreditCardForm($form)) {
            $contactOrg = $this->getContact();
            $stripeToken = filter_input(INPUT_POST, 'stripeToken'); //has all required CC data
            $name = filter_input(INPUT_POST, 'name');
            
            if (empty($stripeToken) || empty($name)) {
                return false;
            }

            $settings = $contactOrg->getPaymentSettings($this->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                $settings = SettingsFactory::buildNewModel($this->getSettingsPaymentTypeRef());
                $settings->setProperty('title', 'Stripe Payments');
                $settings->setProperty('ref', $this->getSettingsPaymentTypeRef());
                $settings->setProperty('settings_payment.contact_id', $contactOrg->getId());
                if (!$settings->save()) {
                    return false;
                }
            }

            if (empty($settings->getProperty('settings_payment_stripe.stripe_cust_id'))) {
                if (!$this->saveNewStripeCustomerWithCard($stripeToken, $name, $emailForReceipt, $settings)) {
                    return false;
                }
            } else {
                if (!$this->addNewCardToExistingCustomer($stripeToken, $settings, true)) {
                    return false;
                }
            }
            
            if (!$settings->save()) {
                return false;
            }
            
            
            return true;
        }
        return false;
    }

    protected function saveNewStripeCustomerWithCard($stripeToken, $name, $email, $settings) {
        if (empty($email)) {
            $email = '';
        }
        $this->resetErrors();
        try {
            $customer = \Stripe\Customer::create([
                        'source' => $stripeToken,
                        'email' => $email,
                        'name' => $name,
            ], ['idempotency_key' => $this->getIdempotentcyKey()]);
        } catch (Exception $ex) {
            $this->setErrorMessage($ex->getMessage());
            $this->setErrorCode($ex->getCode());
            return false;
        }
        $custId = $customer->id;
        $defaultPayment = $customer->default_source;

        $settings->setProperty('settings_payment_stripe.stripe_cust_id', $custId);
        $settings->setProperty('settings_payment_stripe.default_payment_method', $defaultPayment);

        $cacheKey = $this->getCardsCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            apcu_delete($cacheKey);
        }

        return true;
    }

    public function addNewCardToExistingCustomer($stripeToken, $settings, $setAsDefault = false) {
        $this->resetErrors();
        $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
        if (empty($custId)) {
            return false;
        }
        try {
            $card = \Stripe\Customer::createSource(
                            $custId,
                            ['source' => $stripeToken],
                            ['idempotency_key' => $this->getIdempotentcyKey()]
            );
        } catch (Exception $ex) {
            $this->setErrorMessage($ex->getMessage());
            $this->setErrorCode($ex->getCode());
            return false;
        }
        if ($setAsDefault) {
            $settings->setProperty('settings_payment_stripe.default_payment_method', $card->id);
            if (!($this->updateCustomerDefaultPaymentMethod($settings) && $settings->save())) {
                return false;
            }
        }
        $cacheKey = $this->getCardsCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            apcu_delete($cacheKey);
        }
        return true;
    }

    public function updateCustomerDefaultPaymentMethod($settings) {
        $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
        $defaultPaymentMethod = $settings->getProperty('settings_payment_stripe.default_payment_method');
        if (empty($custId) || empty($defaultPaymentMethod)) {
            return false;
        }
        try {
            \Stripe\Customer::update(
                    $custId,
                    ['default_source' => $defaultPaymentMethod]
            );
        } catch (Exception $ex) {
            return false;
        }
                $cacheKey = $this->getCardsCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            apcu_delete($cacheKey);
        }
        return true;
    }
    
    public function removePaymentMethod(AbstractContactOrg $contactOrg, $stripePaymentMethodId) {
        $settings = $contactOrg->getPaymentSettings($this->getSettingsPaymentTypeRef());
        if (empty($settings)) {
            return false;
        }
        $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
        if (empty($custId)) {
            return false;
        }
        try {
            \Stripe\Customer::deleteSource(
                    $custId,
                    $stripePaymentMethodId
            );
        } catch (Exception $ex) {
            return false;
        }
        $cacheKey = $this->getCardsCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            apcu_delete($cacheKey);
        }
        return true;
    }

    public function processPayment(AbstractContactOrg $contactOrg, $amount, $currencyRef) {
        $settings = $contactOrg->getPaymentSettings($this->getSettingsPaymentTypeRef());
        if (empty($settings)) {
            return false;
        }
        $custId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
        if (empty($custId)) {
            return false;
            //TODO - error - card not charged message
        }
        try {
            $charge = \Stripe\Charge::create([
                        'amount' => $amount,
                        'currency' => $currencyRef,
                        'customer' => $custId,
            ], ['idempotency_key' => $this->getIdempotentcyKey()]);
        } catch (Exception $ex) {
            return false;
        }
        
        //TODO - confirm that card was charged

        return true;
    }

    /**
     * For processing one-time payments
     * API Call
     * @param string $stripeToken
     */
    protected function processPaymentWithToken($stripeToken, $amount, $currencyRef, $description) {
        try {
            $charge = \Stripe\Charge::create([
                        'amount' => $amount,
                        'currency' => $currencyRef,
                        'description' => $description,
                        'source' => $stripeToken
            ], ['idempotency_key' => $this->getIdempotentcyKey()]);
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }

    public function getStripeCustomerObject() {
        if (empty($this->stripeCustomerObject)) {
            $contact = $this->getContact();
            if (empty($contact)) {
                return NULL;
            }
            $settings = $contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                return NULL;
            }
            $stripeCustId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
            if (empty($stripeCustId)) {
                return NULL;
            }
            $stripeCustomer = \Stripe\Customer::retrieve($stripeCustId);
            if (!empty($stripeCustomer)) {
                $defaultPaymentMethod = $stripeCustomer->default_source;
                $settings = $this->contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
                if (!empty($settings) && $settings->getProperty('settings_payment_stripe.default_payment_method') !== $defaultPaymentMethod) {
                    $settings->setProperty('settings_payment_stripe.default_payment_method', $defaultPaymentMethod);
                    $settings->save();
                }
            }
            
            $this->stripeCustomerObject = $stripeCustomer;
        }
        return $this->stripeCustomerObject;
    }

    public function getCards() {
        $cacheKey = $this->getCardsCacheKey();
        if (!empty($cacheKey) && apcu_exists($cacheKey)) {
            return apcu_fetch($cacheKey);
        }
        $cards = array();
        $customerObject = $this->getStripeCustomerObject();
        if (!empty($customerObject)) {
            $sources = $customerObject->sources;
            if (empty($sources)) {
                return $cards;
            }
            $cardDataArray = $sources->data; //will contain all payment methods, not just cards
            if (!empty($cardDataArray)) {
                foreach ($cardDataArray as $cardData) {
                    $object = $cardData->object;
                    if ($object !== 'card') {
                        continue;
                    }
                    $card = array();
                    $card['id'] = $cardData->id;
                    $card['brand'] = $cardData->brand;
                    $card['exp_month'] = $cardData->exp_month;
                    $card['exp_year'] = $cardData->exp_year;
                    $card['last_four'] = $cardData->last4;
                    $cards[$card['id']] = $card;
                }
            }
        }
        if (!empty($cacheKey)) {
            apcu_store($cacheKey, $cards, 86400);
        }
        return $cards;
    }

    public function getDefaultCard() {
        $settings = $this->contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
        if (!empty($settings) && !empty($settings->getProperty('settings_payment_stripe.default_payment_method'))) {
            $defaultSource = $settings->getProperty('settings_payment_stripe.default_payment_method');
        } else {
            $customerObject = $this->getStripeCustomerObject();
            if (!empty($customerObject)) {
                $defaultSource = $customerObject->default_source;
            }
        }
        if (empty($defaultSource)) {
            return NULL;
        }
        $cards = $this->getCards();
        if (!empty($cards) && isset($cards[$defaultSource])) {
            return $cards[$defaultSource];
        }

        return NULL;
    }
    
    protected function getCardsCacheKey() {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        return 'contact_' . $contact->getId() . '_c_cards';
    }

    protected function getChargesCacheKey() {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        return 'contact_' . $contact->getId() . '_charges';
    }

    public static function getProductId() {
        if (defined('STRIPE_PRODUCT_ID')) {
            return STRIPE_PRODUCT_ID;
        }
        return NULL;
    }

    public function subscribeCustomerToPaymentPlan(AbstractSubscriptionStripe $subscription) {
        $planId = $subscription->getProperty('subscription_stripe.stripe_plan_id');
        if (empty($planId)) {
            return false;
        }
        $contact = $this->getContact();
        if (empty($contact)) {
            return false;
        }
        $settings = $contact->getPaymentSettings($this->getSettingsPaymentTypeRef());
        if (empty($settings)) {
            return false;
        }
        $stripeCustId = $settings->getProperty('settings_payment_stripe.stripe_cust_id');
        if (empty($stripeCustId)) {
            return false;
        }
        $isAlreadySubscribed = $this->isCustomerSubscribedToPaymentPlan($subscription);
        if (is_null($isAlreadySubscribed)) {
            return false;
        }

        if (!$isAlreadySubscribed) {
            try {
                $stripeSubscription = \Stripe\Subscription::create([
                            'customer' => $stripeCustId,
                            'items' => [['plan' => $planId]],
                ]);
            } catch (Exception $ex) {
                return false;
                //TODO - error message
            }
            $subId = $stripeSubscription->id;
            $subscription->setStripeSubscriptionId($contact, $subId);
            
            $chargesCacheKey = $this->getChargesCacheKey();
            if (!empty($chargesCacheKey)) {
                apcu_delete($chargesCacheKey);
            }
            
        }
        return true;
    }

    //TODO - use cache to minimize API calls
    public function isCustomerSubscribedToPaymentPlan(AbstractSubscriptionStripe $subscription) {
        $planId = $subscription->getProperty('subscription_stripe.stripe_plan_id');
        if (empty($planId)) {
            return NULL;
        }
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        $stripeCustomer = $this->getStripeCustomerObject();
        if (empty($stripeCustomer)) {
            return NULL;
        }
        $subscriptions = $stripeCustomer->subscriptions;
        if (empty($subscriptions)) {
            return false;
        }
        $subscriptionsData = $subscriptions->data;
        if (empty($subscriptionsData)) {
            return false;
        }
        foreach ($subscriptionsData as $subscriptionData) {
            $items = $subscriptionData->items;
            if (empty($items)) {
                continue;
            }
            $itemsData = $items->data;
            if (empty($itemsData)) {
                continue;
            }
            foreach ($itemsData as $itemData) {
                $plan = $itemData->plan;
                if (empty($plan)) {
                    continue;
                }
                if ($plan->id === $planId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function unsubscribeCustomerFromPaymentPlan(AbstractSubscriptionStripe $subscription) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return false;
        }
        $stripeSubId = $subscription->getStripeSubscriptionId($contact);
        if (empty($stripeSubId)) {
            return true;
        }
        try {
            $sripeSub = \Stripe\Subscription::retrieve(
                            $stripeSubId
            );
            $sripeSub->cancel();
        } catch (Exception $ex) {
            return false;
        }
        $subscription->setStripeSubscriptionId($contact, '');
        return true;
    }

    public function getChargeHistoryView() {
        if (empty($this->contact)) {
            return NULL;
        }
        $charges = $this->getCharges();
        $view = new StripePaymentProcessorPaymentHistoryView($charges);
        return $view;
    }
    
}
