<?php
/**
 * Description of GI_FormStepView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 * @deprecated since 4.0.0 -> use AbstractFormStepView instead
 */
abstract class GI_FormStepView extends GI_View {
    
    public static $FORM_HANDLER_FUNCTION_NAME_FIELD_NAME = 'form_handler_function_name';
    
    /**
     * @var GI_Form
     */
    protected $form;
    protected $formBuilt = false;
    protected $fullView = true;
    protected $model;
    protected $stepArray = array();
    protected $prevStep;
    protected $curStep;
    protected $nextStep;
    protected $moveStepWithoutSubmit = false;
    protected $totalSteps;
    protected $curStepFieldName = 'step';
    protected $nextStepFieldName = 'next_step';
    protected $stepTitleKey = 'title';
    protected $stepOptionKey = 'options';
    protected $stepOptionKeyModalClass = 'modal_class';
    protected $stepOptionKeyAnchorClass = 'anchor_class';
    protected $stepOptionKeyListClass = 'list_class';
    protected $stepOptionKeyBuildFormFunctionName = 'build_form_function_name';
    protected $stepOptionKeyFormHandlerFunctionName = 'form_handler_function_name';
    protected $stepNavURLAttrs = '';
    protected $forceHidePrevBtn = false;
    protected $forceHideNextBtn = false;
    protected $forceHideSubmitBtn = false;
    protected $customBtnBuildFunctionNameBeforeSubmitBtn = NULL;
    protected $customBtnBuildFunctionNameAfterSubmitBtn = false;
    protected $detectFormChange = true;
    protected $formBodyClass = array();
    protected $btnClass = array();
    protected $sideStepNav = true;
    
    /**
     * true: GI_modal, false: the main page
     * @var boolean 
     */
    protected $modal = false;
    protected $ajax = true;
    
    public function __construct(GI_Form $form, GI_Model $model = NULL) {
        parent::__construct();
        $this->form = $form;
        $this->model = $model;
        $this->buildSteps();
        $this->buildStepNavURLAttrs();
    }
    
    /** Abstract functions **/
    /** Should be implemented in a sub class**/
    protected abstract function buildSteps();
    
    public abstract function buildStepNavURLAttrs();
    
    protected abstract function buildFormBody();
    /** Abstract functions : end **/
    
    
    public function addFormBodyClass($class) {
        if (!in_array($class, $this->formBodyClass)) {
            $this->formBodyClass[] = $class;
        }
        return $this;
    }
    
    protected function getFormBodyClass(){
        $formBodyClass = '';
        if (!empty($this->formBodyClass)) {
            $formBodyClass .= implode(' ', $this->formBodyClass);
        }
        return $formBodyClass;
    }

    /**
     * @param string $type: 'submit', 'prev', 'next'
     * @param string $class: 
     */
    public function addBtnClass($type, $class) {
        if (!in_array($class, $this->btnClass)) {
            $this->btnClass[$type][] = $class;
        }
        return $this;
    }
    
    /**
     * 
     * @param string $type: 'submit', 'prev', 'next'
     * @return string
     */
    public function getBtnClass($type) {
        $formBodyClass = '';
        if (!empty($this->btnClass[$type])) {
            $formBodyClass .= implode(' ', $this->btnClass[$type]);
        }
        return $formBodyClass;
    }
    
    protected function openStepFormBody() {
        $this->form->addHTML('<div class="step_form_body');
        $formBodyClass = $this->getFormBodyClass();
        $countOfChildrenStepArray = $this->getCountOfChildrenStepArray();
        if ($countOfChildrenStepArray > 0) {
            $formBodyClass .= ' has_children_steps';
        }
        if (!empty($formBodyClass)) {
           $this->form->addHTML(' '.$formBodyClass); 
        }
        $this->form->addHTML('">');
        //$this->buldChildrenStepNav();//Comment out for now
    }
    
    protected function closeStepFormBody() {
        $this->form->addHTML('</div>');
    }
    
    protected function buldChildrenStepNav() {
        $childrenStepArray = $this->getChildrenStepArray($this->curStep);
        if (!empty($childrenStepArray)) {
            $this->form->addHTML('<nav class="children_step_nav">');
                $this->form->addHTML('<ul class="children_step_list">');
                foreach ($childrenStepArray as $childrenStep) {
                    $this->form->addHTML('<li class="children_step" data-id="'.$childrenStep['id'].'">'.$childrenStep['title'].'</li>');
                }
                $this->form->addHTML('</ul>');
            $this->form->addHTML('</nav>');
        }
    }
    
    /**
     * Add step title
     * @param int $step: 1,2,3...
     * @param string $title
     */
    public function addStepTitle($step, $title) {
        $this->stepArray[$step] = array($this->stepTitleKey => $title);
    }
    
    /**
     * Add child step to a step
     * @param int $step: 1,2,3...
     * @param string $title
     */
    public function addChildStep($step, $childStepData) {
        $this->stepArray[$step]['children'][] = $childStepData;
    }
    
    /**
     * Get children step data
     * @param type $step
     */
    public function getChildrenStepArray($step) {
        if (isset($this->stepArray[$step]['children'])) {
            return $this->stepArray[$step]['children'];
        }
        return NULL;
    }
    
    /**
     * Set children step data array
     * @param type $step
     */
    public function setChildrenStepArray($step, $childrenStepArray) {
        $this->stepArray[$step]['children'] = $childrenStepArray;
    }
    
    public function getCountOfChildrenStepArray($step = NULL) {
        if (empty($step)) {
            $step = $this->curStep;
        }
        $childrenStepArray = $this->getChildrenStepArray($step);
        return count($childrenStepArray);
    }
    
    /**
     * Add step values
     * @param array $stepData
     * @param int $stepAppendTo: 1,2,3...
     */
    public function appendToStepData($stepData, $stepAppendTo = NULL) {
        $totalSteps = $this->getTotalSteps();
        if (empty($stepAppendTo)) {
            $this->stepArray[$totalSteps+1] = $stepData;
        } else {
            for($i = 0; ($stepAppendTo+$i) < $totalSteps; $i++) {
                //push current steps after $stepAppendTo
                $this->stepArray[$totalSteps - $i + 1] = $this->stepArray[$totalSteps - $i]; 
            }
            
            $this->stepArray[$stepAppendTo + 1] = $stepData;
        }
        //Update total Steps
        $this->totalSteps = count($this->stepArray);
    }
    
    /**
     * Set modal class names
     * @param int $step: 1,2,3...
     * @param string $classNames
     */
    public function setStepOptionModalClassNames($step, $classNames) {
        $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyModalClass] = $classNames;
    }
    
    /**
     * Get modal class names
     * @param int $step: 1,2,3...
     */
    public function getStepOptionModalClassNames($step) {
        if (isset($this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyModalClass])) {
            return $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyModalClass];
        }
        return '';
    }
    
    /**
     * Set anchor class names
     * @param int $step: 1,2,3...
     * @param string $classNames
     */
    public function setStepOptionAnchorClassNames($step, $classNames) {
        $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyAnchorClass] = $classNames;
    }
    
    /**
     * Get anchor class names
     * @param int $step: 1,2,3...
     * @param string $classNames
     */
    public function getStepOptionAnchorClassNames($step) {
        if (isset($this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyAnchorClass])) {
            return $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyAnchorClass];
        }
        return '';
    }
    
     /**
     * Set li tag class names
     * @param int $step: 1,2,3...
     * @param string $classNames
     */
    public function setStepOptionListClassNames($step, $classNames) {
        $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyListClass] = $classNames;
    }
    
    /**
     * Get li class names
     * @param int $step: 1,2,3...
     * @return string
     */
    public function getStepOptionListClassNames($step) {
        if (isset($this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyListClass])) {
            return $this->stepArray[$step][$this->stepOptionKey][$this->stepOptionKeyListClass];
        }
        return '';
    }
    
    public function isModal($modal) {
        $this->modal = $modal;
    }
    
    public function setAjax($ajax) {
        $this->ajax = $ajax;
    }
    
    public function isAjax() {
        return $this->ajax;
    }
    
    public function isMoveStepWithoutSubmit($moveStepWithoutSubmit) {
        $this->moveStepWithoutSubmit = $moveStepWithoutSubmit;
    }
            
    public function isDetectFormChange($detectFormChange) {
        $this->detectFormChange = $detectFormChange;
    }
    
    public function isForceHidePrevBtn($forceHidePrevBtn) {
        $this->forceHidePrevBtn = $forceHidePrevBtn;
    }

    public function isForceHideNextBtn($forceHideNextBtn) {
        $this->forceHideNextBtn = $forceHideNextBtn;
    }
    
    public function isForceHideSubmitBtn($forceHideSubmitBtn) {
        $this->forceHideSubmitBtn = $forceHideSubmitBtn;
    }

    public function setSideStepNav($sideStepNav) {
        $this->sideStepNav = $sideStepNav;
    }
    public function getTotalSteps() {
        if (empty($this->totalSteps)) {
            $this->totalSteps = count($this->stepArray);
        }
        return $this->totalSteps;
    }
    
    public function setCurStep($curStep) {
        $this->curStep = $curStep;
    }
    
    public function getCurStep() {
        return $this->curStep;
    }
    
    public function getPrevStep() {
        if (($this->curStep - 1) > 0) {
            return ($this->curStep - 1);
        }
        return -1;
    }
    
    public function getNextStep() {
        $totalSteps = $this->getTotalSteps();
        if (($this->curStep + 1) <= $totalSteps) {
            return $this->curStep + 1;
        } 
        return -1;
    }
    
    public function getSubmittedNextStep() {
        if ($this->form->wasSubmitted()) {
            $nextStep = filter_input(INPUT_POST, 'next_step');
            if (!empty($nextStep)) {
                return $nextStep;
            }
        }
        
        return NULL;
    }
    
    public function getNextChildStep($step, $curChildStep = 0) {
        $childrenStepArray = $this->getChildrenStepArray($step);
        $nextChildStep = $curChildStep+1;
        if (isset($childrenStepArray[$nextChildStep])) {
            return $nextChildStep;
        }
        return -1;
    }
    
    public function setStepNavURLAttrs($stepNavURLAttrs) {
        $this->stepNavURLAttrs = $stepNavURLAttrs;
    }
    
    public function getStepNavURLAttrs() {
        return $this->stepNavURLAttrs;
    }
    
    public function setCurStepFieldName($curStepFieldName) {
        $this->curStepFieldName = $curStepFieldName;
    }
    
    public function setNextStepFieldName($nextStepFieldName) {
        $this->nextStepFieldName = $nextStepFieldName;
    }
    
    public function getOptionKeyModalClass() {
        return $this->stepOptionKeyModalClass;
    }
    
    public function getOptionKeyAnchorClass() {
        return $this->stepOptionKeyAnchorClass;
    }
    
    public function getOptionKeyBuildFormFunctionName() {
        return $this->stepOptionKeyBuildFormFunctionName;
    }
    
    public function getOptionKeyFormHandlerFunctionName() {
        return $this->stepOptionKeyFormHandlerFunctionName;
    }
    
    public function setBuildFormFunctionName($step, $formFunctionName) {
        $stepData = $this->stepArray[$step];
        if (!empty($stepData)) {
            $stepData[$this->stepOptionKeyBuildFormFunctionName] = $formFunctionName;
        }
    }
    public function getBuildFormFunctionName($step) {
        $stepData = $this->stepArray[$step];
        if (isset($stepData[$this->stepOptionKeyBuildFormFunctionName])) {
            return $stepData[$this->stepOptionKeyBuildFormFunctionName];
        }
        return NULL;
    }
    
    public function setFormHandlerFunctionName($step, $formHandlerFunctionName) {
        $stepData = $this->stepArray[$step];
        if (!empty($stepData)) {
            $stepData[$this->stepOptionKeyFormHandlerFunctionName] = $formHandlerFunctionName;
        }
    }
    public function getFormHandlerFunctionName($step) {
        $stepData = $this->stepArray[$step];
        if (isset($stepData[$this->stepOptionKeyFormHandlerFunctionName])) {
            return $stepData[$this->stepOptionKeyFormHandlerFunctionName];
        }
        return NULL;
    }

    /**
     * Set button options
     * @param array $buttonOptions : array of button options
     */
    protected function setButtonOptions($buttonOptions = array(
                'customBtnBuildFunctionNameBeforeSubmitBtn' => NULL,
                'customBtnBuildFunctionNameAfterSubmitBtn' => NULL,
            )){
        //Set custom button's functions if any
        if (!empty($buttonOptions['customBtnBuildFunctionNameBeforeSubmitBtn'])) {
            $this->customBtnBuildFunctionNameBeforeSubmitBtn = $buttonOptions['customBtnBuildFunctionNameBeforeSubmitBtn'];
        }
        if (!empty($buttonOptions['customBtnBuildFunctionNameAfterSubmitBtn'])) {
            $this->customBtnBuildFunctionNameAfterSubmitBtn = $buttonOptions['customBtnBuildFunctionNameAfterSubmitBtn'];
        }
    }
   
   
    protected function openFormWrap($classNames = ''){
        $this->form->addHTML('<div class="form_body_wrap step_' .$this->curStep. ' '. $classNames . '" data-step="'.$this->curStep.'">');
    }
    
    protected function closeFormWrap(){
        $this->form->addHTML('</div>');
    }
    
    protected function buildFormHeader($withStep = true, $classNames = NULL) {
        if (!empty($this->stepArray) && isset($this->stepArray[$this->curStep][$this->stepTitleKey])) {
            $curStepTitle = $this->stepArray[$this->curStep][$this->stepTitleKey];
        } else {
            $curStepTitle = $this->curStep;
        }
        $this->form->addHTML('<h1 class="step_title '.$classNames.'">'.(($withStep)? ('Step '.$this->curStep).' : ':'').$curStepTitle.'</h1>');
    }
    
    protected function buildStepNav($withStep = true, $classNames = NULL) {
        $this->form->addHTML('<nav class="step_nav '.$classNames.'">');
            if (!empty($this->stepArray)) {
                $this->form->addHTML('<ul>');
                foreach ($this->stepArray as $step => $stepData) {
                    $this->buildStepNavItem($step, $stepData, $withStep);
                }
                $this->form->addHTML('</ul>');
            } 
        $this->form->addHTML('</nav>');
    }
    
    protected function buildStepNavItem($step, $stepData, $withStep) {
        $stepTitle = $step; 
        if (isset($stepData[$this->stepTitleKey])) {
            $stepTitle = $stepData[$this->stepTitleKey];
        }
        $navURL = '';
        if (!empty($this->stepNavURLAttrs)) {
            $this->stepNavURLAttrs['step'] = $step;
            $navURL = GI_URLUtils::buildURL($this->stepNavURLAttrs);
        }
        $anchorClassNames = '';
        $anchorModalClassNames = '';   
        $listClassNames = ''; 
        if (isset($stepData[$this->stepOptionKey])) {
            $options = $stepData[$this->stepOptionKey];
            if (isset($options[$this->stepOptionKeyAnchorClass])) {
                $anchorClassNames .= $options[$this->stepOptionKeyAnchorClass];
            } else if ($this->modal){
                $anchorClassNames .= 'open_modal_form';
            }
            if ($this->modal && isset($options[$this->stepOptionKeyModalClass])) {
                $anchorModalClassNames = $options[$this->stepOptionKeyModalClass];
            }
            if (isset($options[$this->stepOptionKeyListClass])) {
                $listClassNames = $options[$this->stepOptionKeyListClass];
            }
        }
        if ($this->ajax){
            $anchorClassNames .= ' ajax_link';
        }
        $this->form->addHTML('<li class="form_step '.(($this->curStep == $step)? 'current ':'').$listClassNames.'"');
        $countOfChildrenSteps = $this->getCountOfChildrenStepArray($step);
        $this->form->addHTML(' data-children-step="'.$countOfChildrenSteps.'"');
        $this->form->addHTML('>');
        if ($this->moveStepWithoutSubmit) {
            $this->form->addHTML('<span class="title move_form_step '.$anchorClassNames.'" data-step="'.$step.'">'.(($withStep)? ('<span class="step_text">Step '.$step.' : </span>'):'').$stepTitle.'</span>');
        } else {
            if ($this->detectFormChange) {
                $anchorClassNames .= ' check_for_form_change';
            }
            if ($navURL != '') {
                $this->form->addHTML('<a href="'.$navURL.'" class="'.$anchorClassNames.'" '.(($anchorModalClassNames!='')? ' data-modal-class="'.$anchorModalClassNames.'"':'').'>'.(($withStep)? ('<span class="step_text">Step '.$step.' : </span>'):'').$stepTitle.'</a>');
            } else {
                 $this->form->addHTML('<span class="title '.$anchorClassNames.'">'.(($withStep)? ('<span class="step_text">Step '.$step.' : </span>'):'').$stepTitle.'</span>');
            }
        }
        if ($countOfChildrenSteps > 0) {
            $childrenSteps = $this->getChildrenStepArray($step);
            //Children steps
            $this->form->addHTML('<ul class="children_step_list">');
            $seq = 0;
            foreach ($childrenSteps as $childStep) {
                $childStepTitle = '';
                $childStepTab = '';
                if (isset($childStep['title'])) {
                    $childStepTitle = $childStep['title'];
                }
                if (isset($childStep['tab'])) {
                    $childStepTab = $childStep['tab'];
                }
                //$childStepUrl = $this->getStepURL($step);
                //$this->form->addHTML('<li class="child_step"><a href="'.$childStepUrl.'&tab='.$childStepTab.'">'.$childStepTitle.'</a></li>');
                
                $childStepListClassNames = '';
                $childStepClassNames = '';
                $childStepClassNames = '';
                $submenus;
                if (isset($childStep['submenus'])) {
                    $submenus = $childStep['submenus'];
                    $childStepListClassNames .= ' has_submenu submenu_cnt_'.count($submenus);
                }
                if (isset($childStep['classNames'])) {
                    $childStepClassNames = $childStep['classNames'];
                }
                $this->form->addHTML('<li class="child_step'.$childStepListClassNames.'">');
                    $this->form->addHTML('<span class="title '.$childStepClassNames.'"  data-tab="'.$childStepTab.'" data-seq-number="'.$seq.'">'.$childStepTitle.'</span>');
                
                //Submenus
                if (!empty($submenus)) {
                    $submenuTitle = '';
                    $submenuTab = '';
                    $submenuClassNames = '';
                    $this->form->addHTML('<ul class="child_step_submenus">');
                    foreach ($submenus as $submenu) {
                        if (isset($submenu['title'])) {
                            $submenuTitle = $submenu['title'];
                        }
                        if (isset($submenu['tab'])) {
                            $submenuTab = $submenu['tab'];
                        }
                        if (isset($submenu['classNames'])) {
                            $submenuClassNames = $submenu['classNames'];
                        }
                        $this->form->addHTML('<li class="child_step_submenu '.$submenuTab.'"><span class="title '.$submenuClassNames.'" data-tab="'.$submenuTab.'" data-seq-number="'.$seq.'">'.$submenuTitle.'</span>');
                    }
                    $this->form->addHTML('</ul><!--.child_step_submenus-->');
                }
                
                $this->form->addHTML('</li>');
                $seq++;
            }
            $this->form->addHTML('</ul>');
        }
        $this->form->addHTML('</li>');
    }
    protected function addNextStepField(){
        $this->form->addField($this->nextStepFieldName, 'hidden', array(
            'clearValue' => true,
            'value' => '',
        ));
    }
    
    protected function addCurStepField(){
        $this->form->addField($this->curStepFieldName, 'hidden', array(
            'value' => $this->curStep,
        ));
    }
    
    protected function addFormHandlerField(){
        $formHandlerFunctionName = $this->getFormHandlerFunctionName($this->curStep);
        if (!empty($formHandlerFunctionName)) {
            $this->form->addField(static::$FORM_HANDLER_FUNCTION_NAME_FIELD_NAME, 'hidden', array(
                'value' => $formHandlerFunctionName,
            ));
        }
    }
    
    protected function buildFormFooter() {
        $this->form->addHTML('<div class="step_form_footer">');
            $this->addCurStepField();
            $this->addPrevButton();
            $this->addSubmitButton();
            $this->addNextButton();
        $this->form->addHTML('</div>');
    }
    
    protected function getAttributeHTML($otherAttributes) {
        $otherAttributesHTML = '';
        if (is_array($otherAttributes) && !empty($otherAttributes)) {
            foreach ($otherAttributes as $key => $value) {
                $otherAttributesHTML .= $key.'="'.$value.'" ';
            }
        }
        return $otherAttributesHTML;
    }
    
    public function getStepURL($step = NULL) {
        if (empty($step)) {
            $step = $this->curStep;
        }
        
        if (!empty($this->stepNavURLAttrs)) {
            $this->stepNavURLAttrs['step'] = $step;
            if($this->ajax) {
                $this->stepNavURLAttrs['ajax'] = 1;
            }
            return GI_URLUtils::buildURL($this->stepNavURLAttrs);
        }
        return NULL;
    }
    public function getPrevStepURL() {
        $prevStep = $this->getPrevStep();
        if ($this->moveStepWithoutSubmit || $prevStep != -1) {
            if (!empty($this->stepNavURLAttrs)) {
                $this->stepNavURLAttrs['step'] = $prevStep;
                if($this->ajax) {
                    $this->stepNavURLAttrs['ajax'] = 1;
                }
                return GI_URLUtils::buildURL($this->stepNavURLAttrs);
            }
        }
        return NULL;
    }
    
    protected function addPrevButton($buttonText = 'Prev', $classNames = '', $otherAttributes = array()) {
        if (!$this->forceHidePrevBtn || $this->moveStepWithoutSubmit) {
            $prevURL = $this->getPrevStepURL();
            if ($prevURL) {
                $classNames = '';
                if (!empty($this->getBtnClass('prev'))) {
                    $classNames .= ' '.$this->getBtnClass('prev');
                }
                $otherAttributesHTML = $this->getAttributeHTML($otherAttributes);
                if ($this->moveStepWithoutSubmit) {
                    $this->form->addHTML('<span class="other_btn btn prev_btn prev_form_step '.$classNames.'" '.$otherAttributesHTML.'>'.$this->addPrevButtonText($buttonText).'</span>');
                } else {
                    if ($this->modal){
                        $classNames .= ' open_modal_form';
                    }
                    if ($this->detectFormChange) {
                        $classNames .= ' check_for_form_change';
                    }
                    if($this->ajax) {
                        $classNames .= ' ajax_link';
                    }
                    $this->form->addHTML('<a href="'.$prevURL.'" class="other_btn btn prev_btn '.$classNames.'" '.$otherAttributesHTML.'>'.$this->addPrevButtonText($buttonText).'</a>');
                }
                
            }
        }
    }
    
    protected function addPrevButtonText($buttonText) {
        return '<span class="icon_wrap"><span class="icon primary arrow_left"></span></span><span class="btn_text">'.$buttonText.'</span>';
    }

    protected function addSubmitButton($buttonText = 'Save', $classNames = '', $otherAttributes = array()) {
        // Add custom button before the sumbmit button if any
        if (!empty($this->customBtnBuildFunctionNameBeforeSubmitBtn) && method_exists($this, $this->customBtnBuildFunctionNameBeforeSubmitBtn)) {
            $fullBtnBuildFunctionName = $this->customBtnBuildFunctionNameBeforeSubmitBtn;
            $this->$fullBtnBuildFunctionName();
        }
        
        if (!$this->forceHideSubmitBtn) {
            if (!empty($this->getBtnClass('submit'))) {
                $classNames .= ' '.$this->getBtnClass('submit');
            }
            $otherAttributesHTML = $this->getAttributeHTML($otherAttributes);
            $this->form->addHTML('<span class="submit_btn btn '.$classNames.'" '.$otherAttributesHTML.' data-field-name="'.$this->nextStepFieldName.'" data-field-value=""><span class="icon_wrap"><span class="icon primary check"></span></span><span class="btn_text">'.$buttonText.'</span></span>');
        }
        
        // Add custom button after the sumbmit button if any
        if (!empty($this->customBtnBuildFunctionNameAfterSubmitBtn) && method_exists($this, $this->customBtnBuildFunctionNameAfterSubmitBtn)) {
            $fullBtnBuildFunctionName = $this->customBtnBuildFunctionNameAfterSubmitBtn;
            $this->$fullBtnBuildFunctionName();
        }
    }
    
    public function getNextStepURL() {
        $nextStep = $this->getNextStep();
        if ($nextStep != -1 && !empty($this->stepNavURLAttrs)) {
            $this->stepNavURLAttrs['step'] = $nextStep;
            if($this->ajax) {
                $this->stepNavURLAttrs['ajax'] = 1;
            }
            return GI_URLUtils::buildURL($this->stepNavURLAttrs);
        }
        
        return NULL;
    }
    
    protected function addNextButton($buttonText = 'Save and Continue', $classNames = '', $otherAttributes = array()) {
        if (!$this->forceHideNextBtn) {
            $nextStep = $this->getNextStep();
            if ($this->moveStepWithoutSubmit || $nextStep != -1) {
                $this->addNextStepField();
                $otherAttributesHTML = $this->getAttributeHTML($otherAttributes);
                if (!empty($this->getBtnClass('next'))) {
                    $classNames .= ' '.$this->getBtnClass('next');
                }
                if ($this->moveStepWithoutSubmit) {
                    $buttonText = 'Next';
                    $this->form->addHTML('<span class="btn next_btn next_form_step '.$classNames.'" '.$otherAttributesHTML.'>'.$this->addNextButtonText($buttonText).'</span>');
                } else {
                    $this->form->addHTML('<span class="btn next_btn submit_btn '.$classNames.'" data-field-name="'.$this->nextStepFieldName.'" data-field-value="' . $nextStep . '" '.$otherAttributesHTML.'>'.$this->addNextButtonText($buttonText).'</span>');
                }
            }    
        }
    }
    
    protected function addNextButtonText($buttonText) {
        return '<span class="btn_text">'.$buttonText.'</span><span class="icon_wrap"><span class="icon primary arrow_right"></span></span>';
    }
    
    /* Comment out for now
    protected function addChildStepContent($step, $childStep, $html) {
        $childrenStepArray = $this->getChildrenStepArray($step);
        if (!empty($childrenStepArray)) {
            $childStep = $childrenStepArray[$childStep];
            if (!empty($childStep)) {
                $childStep['content'] = $html;
            }
        }
    }
    
    protected function buldChildrenStepContent() {
        $childrenStepArray = $this->getChildrenStepArray($this->curStep);
        if (!empty($childrenStepArray)) {
            $this->form->addHTML('<div class="children_step_content_wrap">');
            foreach ($childrenStepArray as $childrenStep) {
                if (isset($childrenStep['content'])) {
                    $this->form->addHTML('<div class="children_step_content_wrap">'.$childrenStep['content'].'</div>');
                }
            }
            $this->form->addHTML('</div>');
        }
    }
    */
    
    public function buildForm() {
        if (!$this->formBuilt) {
            if ($this->detectFormChange) {
                $this->form->addFormClass('detect_form_change');
            }
            $this->openFormWrap();
            
            if ($this->sideStepNav) {
                //Left step nav
                $this->form->addFormClass('side_step_form');
                $this->buildStepNav();
                $this->form->addHTML('<div id="side_step_form_body">');
                $this->buildFormHeader();
            } else {
                //Top step nav
                $this->buildFormHeader();
                $this->buildStepNav();
            }
            
            $this->openStepFormBody();
            $this->buildFormBody();
            $this->closeStepFormBody();
            $this->buildFormFooter();
            if ($this->sideStepNav) {
                $this->form->addHTML('</div><!--#side_step_form_body-->');
            }
            $this->closeFormWrap();
            $this->formBuilt = true;
        }
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding" id="form_step_view_wrap">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->buildForm();
        if ($this->fullView) {
            $this->openViewWrap();
        }
        $this->addHTML($this->form->getForm(''));
        if ($this->fullView) {
            $this->closeViewWrap();
        }
    }
    
    public function setFullView($fullView){
        $this->fullView = $fullView;
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}