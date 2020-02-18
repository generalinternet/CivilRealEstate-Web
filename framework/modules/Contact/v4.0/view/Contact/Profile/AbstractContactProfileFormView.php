<?php

/**
 * Description of AbstractContactProfileFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactProfileFormView extends FormStepView {

    /** @var AbstractContact */
    protected $model;
    
    public function __construct(\GI_Form $form, \GI_Model $model = NULL) {
        parent::__construct($form, $model);
        $this->model = $model;
        $currentInterfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        if (!empty($currentInterfacePerspectiveRef) && ($currentInterfacePerspectiveRef !== 'admin') && !$this->model->getIsProfileComplete()) {
            $this->showStepNav = false;
        }
    }

    public function setCurStep($curStep) {
        $this->curStep = $curStep;
        $nextStep = $this->getNextStep();
        $currentInterfacePerspectiveRef = Login::getCurrentInterfacePerspectiveRef();
        if (!empty($currentInterfacePerspectiveRef) && ($currentInterfacePerspectiveRef !== 'admin') && !$this->model->getIsProfileComplete() && $nextStep !== -1) {
            $this->forceHideSubmitBtn = true;
        }
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

    /**
     * Implement abstract functions : buildStepNavURLAttrs
     */
    public function buildStepNavURLAttrs() {
        if (!empty($this->model->getId())) {
            $this->setStepNavURLAttrs(array(
                'controller' => 'contactprofile',
                'action' => 'edit',
                'id' => $this->model->getId(),
            ));
        }
    }

    protected function buildFormHeader($withStep = true, $classNames = NULL) {
        parent::buildFormHeader(false);
    }

    protected function buildStepNav($withStep = true, $classNames = NULL) {
        parent::buildStepNav(false);
    }

    /**
     * @param string $curTab
     * @return \AbstractInvItemDetailView
     */
    public function setCurTab($curTab) {
        $this->curTab = $curTab;
        return $this;
    }

    /**
     * Implement abstract functions : buildFormBody
     */
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
    
    protected function addTagListFormViews(){
        $tagListFormViews = $this->model->getTagListFormViews($this->form);
        if(empty($tagListFormViews)){
            return;
        } else {
            foreach($tagListFormViews as $tagListFormView){
                $tagListFormView->setRequired(true);
                $this->form->addHTML($tagListFormView->getHTMLView());
            }
        }
    }

}
