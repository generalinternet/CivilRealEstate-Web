<?php
/**
 * Description of AbstractRuleController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleController extends GI_Controller {
    
    public function actionIndex($attributes) {
        $ruleGroupSearch = RuleGroupFactory::search();
        $type = 'group';
        $sampleRuleGroup = RuleGroupFactory::buildNewModel($type);
        if (!$sampleRuleGroup->isIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $ruleGroupClass = get_class($sampleRuleGroup);
        $ruleGroups = $ruleGroupSearch->select();
        $pageBarLinkArray = $attributes;
        $pageBar = $ruleGroupSearch->getPageBar($pageBarLinkArray);
        $uiTableCols = $ruleGroupClass::getUITableCols();
        $uiTableView = new UITableView($ruleGroups, $uiTableCols, $pageBar);
        $searchView = NULL;
        $view = new RuleGroupIndexView($ruleGroups, $uiTableView, $sampleRuleGroup, $searchView);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $sampleRuleGroup->getBreadcrumbs();
        return $returnArray;
    }

    public function actionPreAddRuleGroup($attributes) {
        $sampleRuleGroup = RuleGroupFactory::buildNewModel('group');
        if (!$sampleRuleGroup->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('pre_add_rule_group');
        $redirect = false;
        $view = NULL;
        $typeOptions = RuleGroupFactory::getTypesArray();
        $typeRef = NULL;
        if (isset($typeOptions['group'])) {
            unset($typeOptions['group']);
        }
        $view = new RuleGroupPreAddFormView($form, $typeOptions);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($redirect || ($form->wasSubmitted() && $form->validate())) {
            if (empty($typeRef)) {
                $typeRef = filter_input(INPUT_POST, 'rule_group_type_ref');
            }
            $search = RuleGroupFactory::search()
                    ->filterByTypeRef($typeRef, false);
            $ruleGroupArray = $search->select();
            if (!empty($ruleGroupArray)) {
                $ruleGroup = $ruleGroupArray[0];
                $newUrlAttributes = array(
                    'controller' => 'rule',
                    'action' => 'editRuleGroup',
                    'id' => $ruleGroup->getProperty('id')
                );
            } else {
                $newUrlAttributes = array(
                    'controller' => 'rule',
                    'action' => 'addRuleGroup',
                    'type' => $typeRef,
                );
            }
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $newUrl = GI_URLUtils::buildURL($newUrlAttributes);
                $success = 1;
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionAddRuleGroup($attributes) {
        if (!isset($attributes['type'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $typeRef = $attributes['type'];
        $existingSearch = RuleGroupFactory::search()
                ->filterByTypeRef($typeRef, false);
        $existingRuleGroupArray = $existingSearch->select();
        if (!empty($existingRuleGroupArray)) {
            $existingRuleGroup = $existingRuleGroupArray[0];
            GI_URLUtils::redirect(array(
                'controller'=>'rule',
                'action'=>'editRuleGroup',
                'id'=>$existingRuleGroup->getProperty('id'),
            ));
        }
        $ruleGroup = RuleGroupFactory::buildNewModel($typeRef);
        if (empty($ruleGroup)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$ruleGroup->isAddable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('add_rule_group');
        $view = $ruleGroup->getFormView($form);
        $view->buildForm();
        if ($ruleGroup->handleFormSubmission($form)) {
            GI_URLUtils::redirect(array(
                'controller'=>'rule',
                'action'=>'viewRuleGroup',
                'id'=>$ruleGroup->getProperty('id')
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $ruleGroup->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Add Rule Group',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        return $returnArray;
    }

    public function actionEditRuleGroup($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $ruleGroup = RuleGroupFactory::getModelById($id);
        if (empty($ruleGroup)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$ruleGroup->isEditable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $form = new GI_Form('edit_rule_group');
        $view = $ruleGroup->getFormView($form);
        $view->buildForm();
        if ($ruleGroup->handleFormSubmission($form)) {
            $viewURLAttributes = $ruleGroup->getViewURLAttributes();
            GI_URLUtils::redirect($viewURLAttributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $ruleGroup->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'Edit Rule Group',
            'link' => GI_URLUtils::buildURL($attributes)
        );
        return $returnArray;
    }

    public function actionViewRuleGroup($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $ruleGroup = RuleGroupFactory::getModelById($id);
        if (empty($ruleGroup)) {
            GI_URLUtils::redirectToError(2000);
        }
        if (!$ruleGroup->isViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $view = $ruleGroup->getDetailView();
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $ruleGroup->getBreadcrumbs();
        return $returnArray;
    }

    public function actionAddRule($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['seq'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $seq = $attributes['seq'];
        $typeRef = 'rule';
        $groupTypeRef = 'group';
        if (isset($attributes['typeRef']) && $attributes['typeRef']) {
            $typeRef = $attributes['typeRef'];
        }
        if (isset($attributes['gType']) && $attributes['gType']) {
            $groupTypeRef = $attributes['gType'];
        }
        $tempRuleGroup = RuleGroupFactory::buildNewModel($groupTypeRef);
        $rule = RuleFactory::buildNewModel($typeRef);
        if (empty($rule)) {
            return $returnArray;
        }
        $rule->setRuleGroup($tempRuleGroup);
        $rule->setSeqNumber($seq);
        $tempForm = new GI_Form('temp_form');
        $formView = $rule->getFormView($tempForm);
        $formView->setFullView(false);
        $formView->buildForm();
        return array(
            'formRow' => $formView->getHTMLView()
        );
    }

    public function actionAddRuleCondition($attributes) {
        $returnArray = GI_Controller::getReturnArray();
        if (!isset($attributes['ajax']) || $attributes['ajax'] != 1 || !isset($attributes['seq']) || !isset($attributes['ruleSeq'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $ruleSeq = $attributes['ruleSeq'];
        $seq = intval($attributes['seq']);
        $typeRef = 'condition';
        $ruleTypeRef = 'rule';
        $ruleGroupTypeRef = 'group';
        if (isset($attributes['typeRef']) && $attributes['typeRef']) {
            $typeRef = $attributes['typeRef'];
        }
        if (isset($attributes['rTypeRef']) && $attributes['rTypeRef']) {
            $ruleTypeRef = $attributes['rTypeRef'];
        }
        if (isset($attributes['rGroupType']) && $attributes['rGroupType']) {
            $ruleGroupTypeRef = $attributes['rGroupType'];
        }
        $condition = RuleConditionFactory::buildNewModel($typeRef);
        if (empty($condition)) {
            return $returnArray;
        }
        $tempRule = RuleFactory::buildNewModel($ruleTypeRef);
        $tempGroup = RuleGroupFactory::buildNewModel($ruleGroupTypeRef);
        $tempRule->setRuleGroup($tempGroup);
        $condition->setRule($tempRule);
        $condition->setRuleGroup($tempGroup);
        $condition->setFieldSuffix($ruleSeq);
        $condition->setSeqNumber($seq);
        $tempForm = new GI_Form('temp_form');
        $formView = $condition->getFormView($tempForm);
        $formView->setFullView(false);
        $formView->buildForm();
        return array(
            'formRow' => $formView->getHTMLView()
        );
    }

}