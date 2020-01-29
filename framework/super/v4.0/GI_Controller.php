<?php
/**
 * Description of GI_Controller
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class GI_Controller {
    
    protected static $returnArray = array(
        'mainContent' => '',
        'pageProperties' => array()
    );
    
    public static function getReturnArray(GI_View $view = NULL) {
        $returnArray = static::$returnArray;
        if(!is_null($view)){
            $returnArray['mainContent'] = $view->getHTMLView();
            $returnArray['pageProperties'] = $view->getPageProperties();
            $modalClass = $view->getModalClass();
            if (!empty($modalClass)) {
                $returnArray['modalClass'] = $modalClass;
            }
            $curMenuRef = $view->getCurLayoutMenuRef();
            if (!empty($curMenuRef)) {
                $returnArray['curMenuRef'] = $curMenuRef;
            }
            $cssFilePaths = $view->getCSSFilePaths();
            $jsFilePaths = $view->getJSFilePaths();
            
            $scriptArray = array_merge($cssFilePaths, $jsFilePaths);
            foreach($scriptArray as $scriptKey => $scriptPath){
                $scriptArray[$scriptKey] = substr($scriptPath, 0, strpos($scriptPath, '?'));
            }
            
            $returnArray['dynamicScripts'] = $scriptArray;
            $targetAttribute = GI_URLUtils::getAttribute('targetId');
            if (isset($targetAttribute)) {
                $returnArray['targetId'] = $targetAttribute;
            }
            
            if(method_exists($view, 'getListBarURL')){
                $listBarURL = $view->getListBarURL();
                if(!empty($listBarURL)){
                    $returnArray['listBarURL'] = $listBarURL;
                }
            }
            if(method_exists($view, 'getListBarClass')){
                $listBarClass = $view->getListBarClass();
                if(!empty($listBarClass)){
                    $returnArray['listBarClass'] = $listBarClass;
                }
            }
            
            $uploaderScripts = $view->getUploaderScripts();
            if(!empty($uploaderScripts)){
                $returnArray['uploaderScripts'] = $uploaderScripts;
            }
        }
        return $returnArray;
    }
    
    public static function formatException(Exception $ex, $attributes = array()){
        if(isset($attributes['controller'])){
            $controller = $attributes['controller'];
        } else {
            $controller = filter_input(INPUT_GET, 'controller');
        }
        if(isset($attributes['action'])){
            $action = $attributes['action'];
        } else {
            $action = filter_input(INPUT_GET, 'action');
        }
        $view = new StaticErrorView($controller, $action, $attributes, 'exception', array('exception' => $ex));
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = $view->getHTMLView();
        $returnArray['pageProperties'] = $view->getPageProperties();
        return $returnArray;
    }

    public function actionDelete($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id']) || empty($deleteProperties)) {
            GI_URLUtils::redirectToError(2000);
        }
        $modelId = $attributes['id'];
        $factoryClassName = $deleteProperties['factoryClassName'];
        $model = $factoryClassName::getModelById($modelId);
        if (empty($model)) {
            GI_URLUtils::redirectToError(4001);
        }
        $form = new GI_Form('delete_model');
        $view = $model->getDeleteFormView($form);
        if(isset($deleteProperties['message'])){
            $view->setMessage($deleteProperties['message']);
        }
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        $jqueryAction = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($model->handleDeleteForm($form)) {
                $newUrlArray = $deleteProperties['redirectOnSuccess'];
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    if(isset($deleteProperties['refresh']) && $deleteProperties['refresh']){
                        $newUrl = 'refresh';
                    } else {
                        if (!empty($newUrlArray)) {
                            $newUrl = GI_URLUtils::buildURL($newUrlArray);
                        }
                    }
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
                if(isset($deleteProperties['jqueryAction'])){
                    $jqueryAction = $deleteProperties['jqueryAction'];
                }
            } else {
                $view->setDeleteError('You cannot delete this ' . $model->getTypeTitle() . '.');
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if(empty($jqueryAction)){
            $returnArray['newUrl'] = $newUrl;
            if(!empty('newUrl')){
                if (isset($deleteProperties['newUrlRedirect'])) {
                    $returnArray['newUrlRedirect'] = $deleteProperties['newUrlRedirect'];
                }
                if (isset($deleteProperties['newUrlTargetId'])) {
                    $returnArray['newUrlTargetId'] = $deleteProperties['newUrlTargetId'];
                }
            }
        } else {
            $returnArray['jqueryAction'] = $jqueryAction;
        }
        return $returnArray;
    }
    
    public function actionChangeColour($attributes, $properties = array()) {
        if (!isset($attributes['id']) || empty($properties)) {
            GI_URLUtils::redirectToError(2000);
        }
        $modelId = $attributes['id'];
        $factoryClassName = $properties['factoryClassName'];
        $model = $factoryClassName::getModelById($modelId);
        if (empty($model)) {
            GI_URLUtils::redirectToError(4001);
        }
        $form = new GI_Form('colour_model');
        $view = new GenericColourFormView($form, $model);
        if(isset($properties['message'])){
            $view->setMessage($properties['message']);
        }
        $view->buildForm();
        $success = 0;
        $newUrl = '';
        $jqueryAction = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($model->isEditable()) {
                $newColour = filter_input(INPUT_POST, 'colour');
                $model->changeColour($newColour);
                $newUrlArray = $properties['redirectOnSuccess'];
                if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                    if(isset($properties['refresh']) && $properties['refresh']){
                        $newUrl = 'refresh';
                    } else {
                        $newUrl = GI_URLUtils::buildURL($newUrlArray);
                    }
                    $success = 1;
                } else {
                    GI_URLUtils::redirect($newUrlArray);
                }
                if(isset($attributes['jqueryAction'])){
                    $jqueryAction = $attributes['jqueryAction'];
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if(empty($jqueryAction)){
            $returnArray['newUrl'] = $newUrl;
        } else {
            $returnArray['jqueryAction'] = $jqueryAction;
        }
        return $returnArray;
    }
    
    public function actionDeletedView($attributes, GI_Model $model){
        $deletedView = $model->getDeletedDetailView();
        $returnArray = GI_Controller::getReturnArray($deletedView);
        $returnArray['breadcrumbs'] = $model->getBreadcrumbs();
        $returnArray['breadcrumbs'][] = array(
            'label' => 'DELETED'
        );
        return $returnArray;
    }
    
    public function addAutocompNavToResults(&$results, $totalCount, $itemsPerPage, $pageNumber = NULL){
        if(is_null($pageNumber)){
            if (!empty($itemsPerPage) && $totalCount > $itemsPerPage) {
                $moreResult = array(
                    'preventDefault' => 1,
                    'liClass' => 'more_results',
                    'autoResult' => '&hellip;',
                );
                $results[] = $moreResult;
            }
        } elseif($totalCount > 0) {
            $pageCount = ceil($totalCount / $itemsPerPage);
            $prevPage = $pageNumber - 1;
            $nextPage = $pageNumber + 1;
            
            if ($pageNumber != 1) {
                $prevResult = array(
                    'preventDefault' => 1,
                    'liClass' => 'prev_results',
                    'autoResult' => '&laquo; <b>Prev</b>',
                    'pageNumber' => $prevPage
                );
                array_unshift($results, $prevResult);
            }
            
            if ($pageNumber != $pageCount) {
                $nextResult = array(
                    'preventDefault' => 1,
                    'liClass' => 'next_results',
                    'autoResult' => '<b>Next &raquo;</b>',
                    'pageNumber' => $nextPage
                );
                $results[] = $nextResult;
            }
        }
        return true;
    }

    public function viewContextNotificationSettings($eventType, GI_Model $subjectModel, $windowTitle = 'Notification Settings', $viewWrapId = 'main_inner_window_view_wrap') {
        $sampleEvent = EventFactory::buildNewModel($eventType);
        $view = new EventNotificationSettingsView($sampleEvent, $subjectModel);
        $view->setWindowTitle($windowTitle);
        if (!empty($viewWrapId)) {
            $view->setViewWrapId($viewWrapId);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function editContextNotificationSettings($attributes, $subjectModel) {
        if (empty($subjectModel) || !isset($attributes['eventId'])) {
            GI_URLUtils::redirectToError(3000);
        }
        $event = EventFactory::getModelById($attributes['eventId']);
        if (empty($event)) {
            GI_URLUtils::redirectToError(3000);
        }
        $event->setSubjectModel($subjectModel);
        $form = new GI_Form('event_notifications');
        $view = new EventNotificationFormView($form, $event);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($event->handleNotificationFormSubmission($form)) {
            $success = 1;
            $newUrl = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function addContextRole($attributes, $subjectModel) {
        if (empty($subjectModel)) {
            GI_URLUtils::redirectToError(3000);
        }
        $contextRole = ContextRoleFactory::buildNewModel();
        $form = new GI_Form('project_role');
        $view = $contextRole->getFormView($form);
        $view->setContextTitle('Project');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($contextRole->handleFormSubmission($form, $subjectModel)) {
            $success = 1;
            $newUrl = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function editContextRole($attributes, $subjectModel, $contextTitle = '') {
        if (empty($subjectModel)) {
            GI_URLUtils::redirectToError(3000);
        }
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(3000);
        }
        $contextRole = ContextRoleFactory::getModelById($attributes['id']);
        if (empty($contextRole)) {
            GI_URLUtils::redirectToError(3000);
        }
        if (!empty($subjectModel->getId()) && empty($contextRole->getProperty('item_id'))) {
            $updatedContextRole = ContextRoleFactory::buildNewModel();
            $updatedContextRole->setPropertiesFromSourceModel($contextRole);
            $updatedContextRole->setProperty('item_id', $subjectModel->getId());
            $contextRole = $updatedContextRole;
        }
        $form = new GI_Form('context_role');
        $view = $contextRole->getFormView($form);
        $view->setContextTitle($contextTitle);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($contextRole->handleFormSubmission($form, $subjectModel)) {
            $success = 1;
            $newUrl = 'refresh';
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

    public function actionDeleteContextRole($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(3000);
        }
        $contextRole = ContextRoleFactory::getModelById($attributes['id']);
        if (empty($contextRole) || !$contextRole->isDeleteable()) {
            GI_URLUtils::redirectToError(3000);
        }
        if (isset($attributes['itemId'])) {
            $itemId = $attributes['itemId'];
            $contextRoleItemId = $contextRole->getProperty('item_id');
            if (!empty($itemId) && empty($contextRoleItemId) || empty($itemId) && !empty($contextRoleItemId) || $itemId != $contextRoleItemId) {
                GI_URLUtils::redirectToError(3000);
            }
        }

        $form = new GI_Form('delete_context_role');
        $view = new GenericAcceptCancelFormView($form);
        $view->setHeaderText('Delete ' . $contextRole->getTitle());
        $view->setMessageText('Are you sure you want to delete ' . $contextRole->getTitle() . '?');
        $view->setSubmitButtonLabel('Delete');
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($form->wasSubmitted() && $form->validate()) {
            if ($contextRole->softDelete()) {
                $success = 1;
                $newUrl = 'refresh';
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        return $returnArray;
    }

}
