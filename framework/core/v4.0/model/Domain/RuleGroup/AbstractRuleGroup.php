<?php

/**
 * Description of AbstractRuleGroup
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroup extends GI_Model {

    protected static $subjectPropertyOptions = array();
    protected $subjectModel = NULL;
    protected $rulesUsedForEvaluation = array();

    public function getSubjectPropertyOptions() {
        return static::$subjectPropertyOptions;
    }

    public function getSubjectPropertyOptionsRefAndTitleArray() {
        $propertyOptions = $this->getSubjectPropertyOptions();
        $returnArray = array();
        if (!empty($propertyOptions)) {
            foreach ($propertyOptions as $ref => $propertyOptionArray) {
                $returnArray[$ref] = $propertyOptionArray['title'];
            }
        }
        return $returnArray;
    }

    public function getSubjectPropertyOption($ref) {
        return static::$subjectPropertyOptions[$ref];
    }

    public function getSubjectPropertyValue($ref) {
        $optionArray = $this->getSubjectPropertyOption($ref);
        if (!empty($optionArray) && isset($optionArray['method_name'])) {
            $methodName = $optionArray['method_name'];
            if (is_callable(array($this, $methodName))) {
                return $this->{$methodName}();
            }
        }
        return NULL;
    }

    /**
     * @return GI_Model
     */
    public function getSubjectModel() {
        return $this->subjectModel;
    }

    protected function getIsAddable() {
        if (Permission::verifyByRef('add_rule_groups')) {
            return true;
        }
        return false;
    }

    protected function getIsViewable() {
        if (Permission::verifyByRef('view_rule_groups')) {
            return true;
        }
        return false;
    }

    protected function getIsDeleteable() {
        if (Permission::verifyByRef('delete_rule_groups')) {
            return true;
        }
        return false;
    }

    protected function getIsEditable() {
        if (Permission::verifyByRef('edit_rule_groups')) {
            return true;
        }
        return false;
    }
    
    public function isIndexViewable() {
        if (Permission::verifyByRef('view_rule_group_index')) {
            return true;
        }
        return false;
    }

    public function getRuleCount() {
        $search = RuleFactory::search();
        $search->filter('rule_group_id', $this->getProperty('id'));
        return $search->count();
    }

    public function getFormView(GI_Form $form) {
        return new RuleGroupFormView($form, $this);
    }

    public function getDetailView() {
        return new RuleGroupDetailView($this);
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
                    'controller' => 'rule',
                    'action' => 'editRuleGroup',
                    'id' => $this->getProperty('id'),
        ));
    }

    public function getViewURL() {
        $attributes = $this->getViewURLAttributes();
        return GI_URLUtils::buildURL($attributes);
    }

    public function getViewURLAttributes() {
        return array(
            'controller' => 'rule',
            'action' => 'viewRuleGroup',
            'id' => $this->getProperty('id')
        );
    }

    public function getRules(GI_Form $form = NULL, $ruleActions = NULL) {
        $rules = array();
        if (!empty($form) && $form->wasSubmitted()) {
            $seqNums = filter_input(INPUT_POST, 'rules', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            foreach ($seqNums as $seqNum) {
                $ruleId = filter_input(INPUT_POST, 'rule_id_' . $seqNum);
                $typeRef = filter_input(INPUT_POST, 'rule_type_' . $seqNum);
                if (!empty($ruleId)) {
                    $rule = RuleFactory::changeModelType(RuleFactory::getModelById($ruleId), $typeRef);
                } else {
                    $rule = RuleFactory::buildNewModel($typeRef);
                }
                $rule->setSeqNumber($seqNum);
                $rule->setRuleGroup($this);
                $rules[] = $rule;
            }
            return $rules;
        } else {
            return RuleFactory::getModelArrayByRuleGroup($this, $ruleActions);
        }
        return $rules;
    }

    public function getActionOptions() {
        return RuleActionFactory::getOptionsArray('title');
    }

    public function getResultSummaryView() {
        $view = new RuleGroupResultSummaryView($this, $this->rulesUsedForEvaluation);
        return $view;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Title',
                'method_name' => 'getTypeTitle',
                'cell_url_method_name' => 'getViewURL',
            ),
            array(
                'header_title' => '# of Rules',
                'method_name' => 'getRuleCount',
            ),
           
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    /**
     * @param GI_Model $model
     */
    public function setSubjectModel(GI_Model $model) {
        $this->subjectModel = $model;
    }
    
     public function handleFormSubmission(GI_Form $form) {
         if ($form->wasSubmitted() && $this->validateForm($form)) {
             if (!$this->save()) {
                 return false;
             }
             if (!$this->handleRuleRowsFormSubmission($form)) {
                 return false;
             }
            return true;
        }
        return false;
    }
    
    protected function handleRuleRowsFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $existingRules = RuleFactory::getModelArrayByRuleGroup($this, NULL, true);
            $ruleSeqNums = filter_input(INPUT_POST, 'rules', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($ruleSeqNums)) {
                foreach ($ruleSeqNums as $ruleSeqNum) {
                    $ruleId = filter_input(INPUT_POST, 'rule_id_' . $ruleSeqNum);
                    $typeRef = filter_input(INPUT_POST, 'rule_type_' . $ruleSeqNum);
                    if (empty($typeRef)) {
                        $typeRef = 'rule';
                    }
                    if (empty($ruleId)) {
                        $rule = RuleFactory::buildNewModel($typeRef);
                    } else {
                        $rule = RuleFactory::changeModelType($existingRules[$ruleId], $typeRef);
                        unset($existingRules[$ruleId]);
                    }
                    $rule->setSeqNumber($ruleSeqNum);
                    $rule->setRuleGroup($this);
                    if (!$rule->handleFormSubmission($form)) {
                        return false;
                    }
                }
            }
            foreach ($existingRules as $existingRule) {
                if (!$existingRule->softDelete()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

     public function validateForm(\GI_Form $form) {
         if (!$this->formValidated && $form->wasSubmitted() && $form->validate()) {
             $rules = $this->getRules($form);
             if (!empty($rules)) {
                 foreach ($rules as $rule) {
                     if (!$rule->validateForm($form)) {
                         $this->formValidated = false;
                         return $this->formValidated;
                     }
                 }
             }
             $this->formValidated = true;
         }
         return $this->formValidated;
     }
     

    
    /**
     * 
     * @param GI_Model $subjectModel
     * @param AbstractRuleAction[] $ruleActions
     */
    public function evaluate(GI_Model $subjectModel, $ruleActions) {
        $this->setSubjectModel($subjectModel);
        $this->rulesUsedForEvaluation = array();
        $rules = $this->getRules(NULL, $ruleActions);
        $result = true;
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (!$rule->evaluate()) {
                    $result = false;
                }
                $this->rulesUsedForEvaluation[] = $rule;
            }
        }
        return $result;
    }
    
    /** @return array */
    public function getBreadcrumbs() {
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'rule',
            'action' => 'index'
        ));
        $breadcrumbs[] = array(
            'label' => 'Rule Groups',
            'link' => $bcIndexLink
        );
        
        $ruleGroupId = $this->getId();
        if (!is_null($ruleGroupId)) {
            $breadcrumbs[] = array(
                'label' => $this->getTitle(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    public function getTitle(){
        return $this->getTypeTitle();
    }
    
}
