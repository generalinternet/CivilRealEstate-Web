<?php

class UserDetail extends AbstractUserDetail {
    protected $signupStepDataArray = array(
        array(
            'step' => 1,
            'title' => 'Signup',
            'icon' => 'person',
        )
    );
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if ($this->save()) {
                return true;
            }
        }
        return false;
    }
    
    public function getNextStepAttrs($curStep = 1, $userId = NULL, $ajax = 0){
        return array(
            'controller' => 'static',
            'action' => 'message',
        );
    }
    
    public function getPrevStepAttrs($curStep = 1, $userId = NULL, $ajax = 0){
        return array(
            'controller' => 'user',
            'action' => 'signup',
            'step' => $curStep,
            'ajax' => $ajax,
        );
    }
    
    public function getSignupStepDataArray() {
        return $this->signupStepDataArray;
    }
    
    public function setSignupStepDataArray($signupStepDataArray) {
        $this->signupStepDataArray = $signupStepDataArray;
    }
    
    public function getTotalSignupStep() {
        return count($this->getSignupStepDataArray());
    }
    
    public function buildStepNavHTML($curStep = 1) {
        $signupStepDataArray = $this->getSignupStepDataArray();
        $buildNavAttrs = array(
                    'controller' => 'user',
                    'action' => 'buildSignupStepNav',
            );
        if (!empty($this->getId())) {
            $buildNavAttrs['id'] = $this->getId();
        }
        $buildNavURL = GI_URLUtils::buildURL($buildNavAttrs);
        $html = '<nav class="step_nav" data-url="'.$buildNavURL.'">';
        if (!empty($signupStepDataArray)) {
            $html .= '<ul>';
            foreach ($signupStepDataArray as $stepData) {
                $classNames = 'form_step';
                if ($stepData['step'] == $curStep) {
                    $classNames .= ' current';
                }
                $html .= '<li class="'.$classNames.'" data-step="'.$stepData['step'].'">';
                    $html .= '<span class="title">';
                    //$html .= '<span class="step_nav_step">'.$stepData['step'].'</span>';
                    if (isset($stepData['icon'])) {
                        $html .= '<span class="step_nav_icon">'.GI_StringUtils::getSVGIcon($stepData['icon']).'</span>';
                    }
                    $html .= '<span class="step_nav_title">'.$stepData['title'].'</span>';
                    $html .= '</span>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</nav>';
        
        return $html;
    }
}