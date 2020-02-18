<?php
/**
 * Description of AbstractContactCatClientChangeSubFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactCatClientChangeSubFormView extends AbstractContactCatChangeSubFormView {
    
    /** @var AbstractContactApplicationFormView  */
    protected $applicationFormView;

    public function __construct(\GI_Form $form, \AbstractContactCat $contactCat) {
        parent::__construct($form, $contactCat);
        $this->showStepNav = false;
        $this->addJS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/application/contact_application.js');
    }

    protected function buildSteps() {
        //Add step titles
        $this->addStepTitle(10, 'Select Package');
        $this->addStepTitle(20, 'Payment Method');
        $this->addStepTitle(30, 'Confirm Payment');
        $this->addStepTitle(40, 'Review');
    }

    protected function buildFormBody() {
        switch ($this->curStep) {
            case 10:
                $this->buildSelectSubscriptionForm();
                break;
            case 20:
                $this->buildPaymentMethodForm();
                break;
            case 30:
                $this->buildConfirmPaymentForm();
                break;
            case 40:
                $this->buildReviewForm();
                break;
        }
    }
    
    protected function buildSelectSubscriptionForm() {
        $formView = $this->getApplicationFormView();
        if (empty($formView)) {
            return;
        }
        $formView->buildSelectPackageForm(false);
    }

    protected function buildPaymentMethodForm() {
        $this->addJS("https://js.stripe.com/v3/");
        $this->addJS('framework/core/' . FRMWK_CORE_VER . '/resources/js/payments/stripe_custom.js');
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/paymentfont/1.1.2/css/paymentfont.min.css');
        $formView = $this->getApplicationFormView();
        if (empty($formView)) {
            return;
        }
        $formView->buildPaymentMethodForm(false);
    }

    protected function buildConfirmPaymentForm() {
        $formView = $this->getApplicationFormView();
        if (empty($formView)) {
            return;
        }
        $formView->buildConfirmPaymentForm(false);
    }

    protected function buildReviewForm() {
        $this->form->addHTML('congrats, you are done'); //TODO - temp
    }

    /** @var AbstractContactApplicationFormView */
    protected function getApplicationFormView() {
        if (empty($this->applicationFormView)) {
            $application = ContactApplicationFactory::buildNewModel($this->model->getApplicationTypeRef());
            if (empty($application)) {
                return NULL;
            }
            $application->setContactOrg($this->model->getContact());
            $formView = $application->getFormView($this->form);
            if (empty($formView)) {
                return NULL;
            }
            $this->applicationFormView = $formView;
        }
        return $this->applicationFormView;
    }

    protected function buildFormFooter() {
        $this->form->addHTML('<div class="step_form_footer"><div class="wrap_btns">');
        $this->addCurStepField();
        $step = $this->curStep;
        switch ($step) {
            case 10:
                $this->addCancelBtn();
                $this->addNextButton('Continue');
                break;
            case 20:
                $this->addPrevButton('Back');
                $this->addCancelBtn();
                $this->addNextButton('Continue');
                break;
            case 30:
                $this->addPrevButton('Back');
                $this->addCancelBtn();
                $this->addNextButton('Purchase');
                break;
            case 40:
                $this->addSubmitButton('Back to Profile');
                break;
        }

        $this->form->addHTML('</div></div>');
    }
    
    protected function addCancelBtn($buttonText = 'Cancel') {
        $contact = $this->model->getContact();
        if (empty($contact)) {
            return;
        }
        $attrs = $contact->getViewProfileURLAttrs();
        $attrs['tab'] = 'payments';
        $url = GI_URLUtils::buildURL($attrs);
        $this->form->addHTML('<a href="'.$url.'" class="btn other_btn"><span class="btn_text">'.$buttonText.'</span></a>');
    }
    
    protected function buildFormHeader($withStep = true, $classNames = NULL) {
        if ($this->showStepTitle) {
            if (!empty($this->stepArray) && isset($this->stepArray[$this->curStep][$this->stepTitleKey])) {
                $curStepTitle = $this->stepArray[$this->curStep][$this->stepTitleKey];
            } else {
                $curStepTitle = $this->curStep;
            }
            $this->form->addHTML('<h2 class="step_title '.$classNames.'">'.$curStepTitle.'</h2>');
        }
    }

}
