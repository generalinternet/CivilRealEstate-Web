<?php
/**
 * Description of AbstractNoteController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractNoteController extends GI_Controller {
    
    public function actionIndex($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'note';
        }
        
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }
        
        if(isset($attributes['queryId'])){
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        $noteSearch = NoteFactory::search()
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);
        
        $pageBarLinkArray = array(
            'controller' => 'note',
            'action' => 'index'
        );
        
        if(!empty($type)){
            $noteSearch->filterByTypeRef($type);
            $pageBarLinkArray['type'] = $type;
        }
        
        $sampleNote  = NoteFactory::buildNewModel($type);
        $noteClass = get_class($sampleNote);
        
        $searchView = $noteClass::getSearchForm($noteSearch, $type);
        
        $notes = $noteSearch->select();
        $pageBar = $noteSearch->getPageBar($pageBarLinkArray);
        
        $uiTableCols = $noteClass::getUITableCols();
        $uiTableView = new UITableView($notes, $uiTableCols, $pageBar);
        
        if(isset($attributes['ajax']) && $attributes['ajax'] == 1){
            if(isset($attributes['onlyRows']) && $attributes['onlyRows'] == 1){
                $returnArray['uiTableRows'] = $uiTableView->getRows();
            } else {
                $returnArray['uiTable'] = $uiTableView->getHTMLView();
            }
        } else {
            $view = new NoteIndexView($notes, $uiTableView, $sampleNote, $searchView);
            $returnArray = GI_Controller::getReturnArray($view);
            $returnArray['breadcrumbs'] = $sampleNote->getBreadcrumbs();
        }
        
        return $returnArray;
    }
    
    public function actionView($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $note = NoteFactory::getModelById($id);
        if (empty($note)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$note->isViewable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = $note->getView();
        
        if(isset($attributes['tab'])){
            $view->setCurTab($attributes['tab']);
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $note->getBreadcrumbs();
        return $returnArray;
    }
    
    public function actionAdd($attributes) {
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'note';
        }
        
        $form = new GI_Form('add_note');
        
        $note = NoteFactory::buildNewModel($type);
        if (is_null($note)) {
            GI_URLUtils::redirectToError(4000);
        }
        
        if(!$note->isAddable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $model = NULL;
        if (isset($attributes['modelId']) && isset($attributes['fc'])) {
            $modelId = $attributes['modelId'];
            $fc = $attributes['fc'];
            $factoryClassName = $fc . 'Factory';
            $model = $factoryClassName::getModelById($modelId);
        }
        $view = $note->getFormView($form, false);
        $view->setAddFormHeader(false);
        $view->buildForm();
        $success = 0;
        $jQueryAction = NULL;
        if ($note->handleFormSubmission($form, $model)) {
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
//                $detailView = $note->getView();
//                $postableContent = json_encode($detailView->getHTMLView());
//                $jQueryAction = 'postNote(' . $postableContent . ');';
                //Reload the whole thread because adding just a new note causes wrong thread data after loading more
                $jQueryAction = 'reloadNotesThread();';
                
                //Reload ajax contents to clear form
                $reloadContents = 1;
                $success = 1;
            } else {
                $newUrlAttributes = array(
                    'controller' => 'note',
                    'action' => 'view',
                    'id' => $note->getId()
                );
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($jQueryAction)) {
            $returnArray['jqueryAction'] = $jQueryAction;
        }
        $breadcrumbs = $note->getBreadcrumbs();
        $addLink = GI_URLUtils::buildURL(array(
                    'controller' => 'note',
                    'action' => 'add',
                    'type' => $type
        ));
        $breadcrumbs[] = array(
            'label' => 'Add',
            'link' => $addLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts();
            if (isset($reloadContents)) {
                $returnArray['reloadContents'] = 1;
            }
        }
        
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        
        $id = $attributes['id'];
        $note = NoteFactory::getModelById($id);
        if (empty($note)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$note->isEditable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $form = new GI_Form('edit_note');
        $view = $note->getFormView($form, false);
        
        if (isset($attributes['step'])) {
            $view->setStartStep($attributes['step']);
        }

        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($note->handleFormSubmission($form, NULL)) {
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                //$newUrl = 'refresh';
                $detailView = $note->getView();
                $postableContent = json_encode($detailView->getHTMLView());
                $jQueryAction = 'swapNote(' . $postableContent . ', '.$note->getId().');';
                $success = 1;
                
            } else {
                $newUrlAttributes = array(
                    'controller' => 'note',
                    'action' => 'view',
                    'id' => $note->getId()
                );
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        if (!empty($jQueryAction)) {
            $returnArray['jqueryAction'] = $jQueryAction;
        }
        $breadcrumbs = $note->getBreadcrumbs();
        $editLink = $note->getEditURL();
        $breadcrumbs[] = array(
            'label' => 'Edit',
            'link' => $editLink
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $returnArray['jqueryCallbackAction'] = $view->getUploaderScripts();
        }
        return $returnArray;
    }

    public function actionDelete($attributes, $deleteProperties = array()) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $note = NoteFactory::getModelById($id);
        if (empty($note)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        if(!$note->isDeleteable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        $redirectProps = array(
            'controller' => 'note',
            'action' => 'index'
        );
        
        if(isset($attributes['type'])){
            $redirectProps['type'] = $attributes['type'];
        }
        
        $deleteProperties = array(
            'factoryClassName' => 'NoteFactory',
            'redirectOnSuccess' => $redirectProps
        );
        if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
            $deleteProperties['jqueryAction'] = 'reloadNotesThread();';
            $returnArray = parent::actionDelete($attributes, $deleteProperties);
            unset($returnArray['newUrl']);
            return $returnArray;
        } else {
            return parent::actionDelete($attributes, $deleteProperties);
        }
    }
    
    public function actionGetNotesAndButtonHTML($attributes) {
//        if (!isset($attributes['ajax']) || $attributes != '1') {
//            return array();
//        }
        if (!(isset($attributes['modelId']) && isset($attributes['fc']))) {
            return array();
        }
        if (isset($attributes['page'])) {
            $pageNumber = (int) $attributes['page'];
        } else {
            $pageNumber = 1;
        }
        if (isset($attributes['qty'])) {
            $qty = (int) $attributes['qty'];
        } else {
            $qty = 3;
        }
        $fc = $attributes['fc'];
        $modelId = $attributes['modelId'];
        $factoryClass = $fc . 'Factory';
        $model = $factoryClass::getModelById($modelId);
        if (empty($model)) {
            return array();
        }
        if (isset($attributes['type'])) {
            $type = $attributes['type'];
        } else {
            $type = 'note';
        }
//        $notes = NoteFactory::getNotesLinkedToModel($model, $type, $pageNumber, $qty);
//        $html = '';
//        if (!empty($notes)) {
//            foreach ($notes as $note) {
//                $detailView = $note->getView();
//                $html .= $detailView->getHTMLView();
//            }
//            if (count($notes) == $qty) {
//                $threadView = new NoteThreadView(NULL, $model);
//                $buttonHTML = $threadView->getLoadMoreButtonHTML($pageNumber + 1);
//                $html .= $buttonHTML;
//            }
//        }
        $html = Note::getNotesThreadHTML($model, $type, $pageNumber, $qty);
        
        return array(
            'mainContent'=>$html,
        );
    }
    
}
