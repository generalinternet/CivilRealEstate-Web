<?php
/**
 * Description of AbstractContactCatChangeSubFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactCatChangeSubFormView extends FormStepView {
    
    /** @var AbstractContactCat */
    protected $modal = false;
    protected $formBuilt = false;

    public function __construct(GI_Form $form, AbstractContactCat $contactCat) {
        parent::__construct($form, $contactCat);
    }

    protected function buildSteps() {
        //Add step titles
//        $this->addStepTitle(1, GI_StringUtils::getSVGIcon('barcode', '26px', '26px').'General');
//        $this->addStepTitle(2, GI_StringUtils::getSVGIcon('packaging', '26px', '26px').'Packaging');
//        $this->addStepTitle(3, GI_StringUtils::getSVGIcon('calculator', '26px', '26px').'Accounting');
//        $this->addStepTitle(4, GI_StringUtils::getSVGIcon('file', '26px', '26px').'Files');
//        $this->addStepTitle(5, GI_StringUtils::getSVGIcon('swap', '26px', '26px').'Substitutions');
        //    $this->setStepOptionListClassNames(3, 'hidden_child_step_texts');
    }

    public function buildStepNavURLAttrs() {
        $contact = $this->model->getContact();
        if (empty($contact)) {
            return array();
        }
        $this->setStepNavURLAttrs(array(
            'controller' => 'contactprofile',
            'action' => 'changeSubscription',
            'id' => $contact->getId(),
        ));
    }

    protected function buildFormBody() {
        switch ($this->curStep) {
            case 1:
                break;
            case 2:

                break;
            case 3:

                break;
            default:
        }
    }


}
