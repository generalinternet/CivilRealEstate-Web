<?php
/**
 * Description of GI_Controller
 *
 * @author General Internet
 * @copyright  2017 General Internet
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
            if (!empty($view->getModalClass())) {
                $returnArray['modalClass'] = $view->getModalClass();
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
    
}
