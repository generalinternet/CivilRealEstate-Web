<?php

/**
 * Description of AbstractContactApplicationClientFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.1
 */
abstract class AbstractContactApplicationClientFormView extends AbstractContactApplicationFormView {

    protected $formWrapID = '';
    protected $submitButtonLabel = '';

    protected function buildFormByStatus(AbstractContactApplicationStatus $status) {
        $ref = $status->getProperty('ref');
        switch ($ref) {
            case 'select_package':
                $this->setWindowTitle('Select Package');
                $this->formWrapID = 'select_package_wrap';
                $this->buildSelectPackageForm();
                break;
            case 'payment_method':
                $this->setWindowTitle('Payment Method');
                $this->formWrapID = 'payment_wrap';
                $this->buildPaymentMethodForm();
                break;
            case 'payment':
                $this->setWindowTitle('Confirm Payment');
                $this->formWrapID = 'payment_wrap';
                $this->buildConfirmPaymentForm();
                break;
            case 'payment_results':
                $this->setWindowTitle('Payment Result');
                $this->formWrapID = 'payment_result_wrap';
                $this->buildPaymentResultsForm();
                break;
            default:
                GI_URLUtils::redirectToError(2001);
        }
    }

    public function buildSelectPackageForm($addButtons = true) {
        $this->addSelectPackageSection();
        if ($addButtons) {
            $this->buildFormButtons();
        }
    }

    public function buildPaymentMethodForm($addButtons = true) {        
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addViewPackageSection();
        $this->addHTML('<br /><br />');
        $this->addPaymentSmallPrintSection();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addPaymentProcessorSection();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        if ($addButtons) {
            if (empty($this->submitButtonLabel)) {
                $submitButtonLabel = 'save new card';
            } else {
                $submitButtonLabel = $this->submitButtonLabel;
            }
            $this->buildFormButtons('back', $submitButtonLabel);
        }
    }

    public function buildConfirmPaymentForm($addButtons = true) {
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        //$this->addSelectPackageSection(false);
        $this->addViewPackageSection();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addConfirmPaymentMessageSection();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        $this->form->addHTML('<br>');
        if ($addButtons) {
            $this->buildFormButtons('back', 'purchase');
        }
    }
    
    protected function addConfirmPaymentMessageSection() {
        $card = NULL;
        $contactOrg = $this->application->getContactOrg();
        if (!empty($contactOrg)) {
            $card = $contactOrg->getDefaultPaymentMethod();
            if (!empty($card)) {
                $cardDetailView = new CreditCardDetailView($card);
                $cardDetailView->setIsDefault(true);
                $cardDetailView->setOnlyBodyContent(true);
                $this->form->addHTML($cardDetailView->getHTMLView());
            }
        }

        $subscriptionString = '.';
        $subscription = $this->application->getSubscription();
        if (!empty($subscription)) {
            $subscriptionString = '<b>' . $subscription->getPriceSummaryString() . '</b>.';
        }
        $this->form->addHTML('<p>');
        $this->form->addHTML('By clicking Purchase, you are agreeing to pay ' . $subscriptionString . ' You will have the opportunity to modify your selected package and payment method, or cancel your account, at any time');
        $this->form->addHTML('</p>');
        
    }

    public function buildPaymentResultsForm() {
        $this->addPaymentSuccessMessage();
        $this->addNextStepInstructions();

        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitButton('complete profile');
        $this->form->addHTML('</div>');
    }

    protected function addPaymentSuccessMessage() {
        $message = 'Congratulations! You have successfully registered. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        $this->form->addHTML('<p>' . $message . '</p>');
    }

    protected function addNextStepInstructions() {
        $message = 'Click the button below to complete your profile. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        $this->form->addHTML('<p>' . $message . '</p>');
    }

    protected function addCompleteProfileButton() {
        $this->from->addHTML();
    }

    protected function addSelectPackageSection($fullDescription = true) {
        $this->openPackageWrap();
        $value = $this->application->getProperty('subscription_id');
        $subscriptionOptions = $this->application->getSubscriptionOptions();
        if (!empty($subscriptionOptions)) {
            $setInitValue = false;
            foreach ($subscriptionOptions as $subscription) {
                if(empty($value) && !$setInitValue){
                    $value = $subscription->getId();
                    $setInitValue = true;
                }
                $this->addSelectPackageField($subscription, $value, $fullDescription);
            }
        }
        $this->closePackageWrap();
    }

    protected function addViewPackageSection() {
        $subscription = $this->application->getSubscription();
        if (empty($subscription)) {
            return;
        }
        $detailView = $subscription->getDetailView();
        if (empty($detailView)) {
            return;
        }
        $detailView->setOnlyBodyContent(true);
        $detailView->setForm($this->form);
        $detailView->setIsSelected(true);
        $selectPackageURL = $this->application->getSelectPackageStepURL();
        $this->form->addHTML('<div class="form_element">');
        $this->form->addHTML('<h3>');
        $this->form->addHTML('<span class="title">Selected Package</span>');
        $this->form->addHTML('<a href="' . $selectPackageURL . '" title="" class="custom_btn" >' . GI_StringUtils::getSVGIcon('pencil') . '<span class="btn_text">Change</span></a>');
        $this->form->addHTML('</h3>');
        
        $this->form->addHTML($detailView->getHTMLView());
        $this->form->addHTML('</div>');
    }

    protected function addSelectPackageField(AbstractSubscription $subscription, $value) {
        $detailView = $subscription->getDetailView();
        if($detailView){
            $detailView->setForm($this->form);
            $subscriptionId = $subscription->getId();
            if (!empty($value) && $value == $subscriptionId) {
                $detailView->setIsSelected(true);
            }
            $detailView->setOnlyBodyContent(true);
            $this->form->addHTML($detailView->getHTMLView());
        }
    }

    protected function addPaymentProcessorSection() {
        $this->addJS("https://js.stripe.com/v3/");
        $this->addJS('framework/core/' . FRMWK_CORE_VER . '/resources/js/payments/stripe_custom.js');
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/paymentfont/1.1.2/css/paymentfont.min.css');
        $paymentProcessor = new StripePaymentProcessor();
        $contactOrg = $this->application->getContactOrg();
        
        if ($this->form->wasSubmitted()) {
            $value = filter_input(INPUT_POST, 'selected_card');
        } else {
            $value = $contactOrg->getDefaultPaymentMethod();
        }
        if (!empty($value) && $value !== 'new') {
            $this->submitButtonLabel = 'use selected card';
        } else {
            $this->submitButtonLabel = 'save new card';
        }

        $paymentProcessor->setContact($contactOrg);
        $cards = $paymentProcessor->getCards();
        if (!empty($cards)) {
            foreach ($cards as $cardId => $card) {
                $this->form->addHTML('<div class="credit_card_option">');
                $cardView = new CreditCardDetailView($card);
                $cardView->setOnlyBodyContent(true);
                if($cardId == $value){
                    $cardView->setIsDefault(true);
                }
                $this->form->addField('selected_card', 'radio', array(
                    'options' => array(
                        $cardId => $cardView->getHTMLView(),
                    ),
                    'value'=>$value,
                    'showLabel'=>false,
                    'fieldClass' => 'radio_toggler',
                    'formElementClass' => 'list_options',
                    'stayOn'=>true
                ));
                $this->form->addHTML('</div>');
            }
            $this->form->addField('selected_card', 'radio', array(
                'options' => array(
                    'new' => 'Add a New Card'
                ),
                'showLabel'=>false,
                'fieldClass' => 'radio_toggler',
                'stayOn'=>true
                
            ));
        } else {
            $this->form->addField('selected_card', 'hidden', array(
                'value' => 'new'
            ));
        }
        $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="selected_card" data-element="new">');
        $creditCardFormView = $this->application->getCreditCardFormView($this->form);
        if (!empty($creditCardFormView)) {
            $creditCardFormView->buildForm();
            $creditCardFormView->setOnlyBodyContent(true);
        }
        $this->form->addHTML('</div>');
    }

    protected function addPaymentSmallPrintSection() {
        $this->form->addHTML('<h7>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</h7>');
    }

    protected function addOpenWrap() {
        $this->addHTML('<div id="' . $this->formWrapID . '" class="contact_application_form_wrap">');
        return $this;
    }

    protected function addCloseWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    protected function addViewBodyContent() {
        $this->addOpenWrap();

        $this->addHTML($this->form->getForm(''));

        $this->addCloseWrap();

        return $this;
    }

    protected function openPackageWrap() {
        $this->form->addHTML('<div class="package_wrap">');
        return $this;
    }

    protected function closePackageWrap() {
        $this->form->addHTML('</div>');
        return $this;
    }

}
