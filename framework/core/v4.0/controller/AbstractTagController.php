<?php
/**
 * Description of AbstractTagController
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagController extends GI_Controller {
    
    public function actionAdd($attributes) {
        if (!isset($attributes['type'])) {
            $type = 'tag';
        } else {
            $type = $attributes['type'];
        }
        if(!Permission::verifyByRef('add_tags')){
            GI_URLUtils::redirectToAccessDenied();
        }
        $tag = TagFactory::buildNewModel($type);
        if (empty($tag)) {
            GI_URLUtils::redirectToError(4000);
        }
        if(isset($attributes['title'])){
            $tag->setProperty('title', $attributes['title']);
        }
        $form = new GI_Form('tag');
        $view = $tag->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        $autocompId = NULL;
        if ($tag->handleFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'tag',
                'action' => 'index',
                'type' => $tag->getTypeRef()
            );
            LogService::logActivity(GI_URLUtils::buildURL($newUrlAttributes), $tag->getViewTitle(false) . ': ' . $tag->getProperty('title') , 'plus', 'add');
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                $success = 1;
                $autocompId = $tag->getId();
                if (isset($attributes['refresh']) && $attributes['refresh'] = 1) {
                    $newUrl = 'refresh';
                }
            } else {
                GI_URLUtils::redirect($newUrlAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        if (!empty($newUrl)) {
            $returnArray['newUrl'] = $newUrl;
        }
        if (!empty($autocompId)) {
            $returnArray['autocompId'] = $autocompId;
        }
        return $returnArray;
    }

    public function actionEdit($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }
        $id = $attributes['id'];
        $tag = TagFactory::getModelById($id);
        if(!$tag->isEditable()){
            GI_URLUtils::redirectToAccessDenied();
        }
        if (empty($tag)) {
            GI_URLUtils::redirectToError(4001);
        }
        $form = new GI_Form('tag');
        $view = $tag->getFormView($form);
        $view->buildForm();
        $success = 0;
        $newUrl = NULL;
        if ($tag->handleFormSubmission($form)) {
            $newUrlAttributes = array(
                'controller' => 'tag',
                'action' => 'index',
                'type' => $tag->getTypeRef()
            );
            LogService::logActivity(GI_URLUtils::buildURL($newUrlAttributes), $tag->getViewTitle(false) . ': ' . $tag->getProperty('title') , 'pencil', 'edit');
            if (isset($attributes['ajax']) && $attributes['ajax'] == 1) {
                if (isset($attributes['targetId'])) {
                    $newUrlAttributes['targetId'] = $attributes['targetId'];
                } else {
                    $newUrlAttributes['targetId'] = 'list_bar';
                }
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
            if ($success) {
                $returnArray['newUrlRedirect'] = 1;
            }
        }
        return $returnArray;
    }

    public function actionIndex($attributes) {
        if (!isset($attributes['type'])) {
            $type = 'tag';
        } else {
            $type = $attributes['type'];
        }
        
        $sampleTag = TagFactory::buildNewModel($type);
        if (!$sampleTag->getIsIndexViewable()) {
            GI_URLUtils::redirectToAccessDenied();
        }
        if (isset($attributes['pageNumber'])) {
            $pageNumber = $attributes['pageNumber'];
        } else {
            $pageNumber = 1;
        }

        if (isset($attributes['queryId'])) {
            $queryId = $attributes['queryId'];
        } else {
            $queryId = NULL;
        }
        
        if (isset($attributes['targetId'])) {
            $targetId = $attributes['targetId'];
        } else {
            $targetId = 'list_bar';
            GI_URLUtils::setAttribute('targetId', 'list_bar');
        }

        //$tagClass = get_class($sampleTag);
        $tagSearch = TagFactory::search()
                ->filterByTypeRef($type)
                ->setPageNumber($pageNumber)
                ->setItemsPerPage(ProjectConfig::getUITableItemsPerPage())
                ->setQueryId($queryId);

        //    $sampleTag->addCustomFiltersToDataSearch($tagSearch);

        $pageBarLinkArray = $attributes;

        //$searchView = $tagClass::getSearchForm($tagSearch, $type);
        $searchView = NULL;
        $tags = $tagSearch->orderBy('pos')
                ->select();
        $pageBar = $tagSearch->getPageBar($pageBarLinkArray);
        if ($targetId == 'list_bar') {
            //Tile style view
            $uiTableCols = $sampleTag->getUIRolodexCols();
            $uiTableView = new UIRolodexView($tags, $uiTableCols, $pageBar);
            $uiTableView->setLoadMore(true);
            $uiTableView->setShowPageBar(false);
            if(isset($attributes['curId']) && $attributes['curId'] != ''){
                $uiTableView->setCurId($attributes['curId']);
            }
        } else {
            //List style view
            $uiTableCols = $sampleTag->getUITableCols();
            $uiTableView = new UITableView($tags, $uiTableCols, $pageBar);
            //$uiTableView->setNoModelMessage('No ' . strtolower($sampleTag->getViewTitle(true)) . ' found.');
        }

        $view = new TagIndexView($tags, $uiTableView, $sampleTag, $searchView);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $sampleTag->getBreadcrumbs();
        
        return $returnArray;
    }

    public function actionAutocompTag($attributes) {
        if ((!isset($attributes['ajax']) || !$attributes['ajax'] == 1)) {
            $returnArray = GI_Controller::getReturnArray();
            return $returnArray;
        }
        if (isset($attributes['dbType'])) {
            TagFactory::setDBType($attributes['dbType']);
        }
        $valueColumn = 'title';
        if (isset($attributes['valueColumn'])) {
            $valueColumn = $attributes['valueColumn'];
        }
        if (isset($attributes['curVal'])) {
            $curVal = $attributes['curVal'];
            $curVals = explode(',', $curVal);
            $results = array(
                'label' => array(),
                'value' => array(),
                'autoResult' => array()
            );
            foreach ($curVals as $tagId) {
                $tag = NULL;
                if ($valueColumn == 'id') {
                    $tag = TagFactory::getModelById($tagId);
                } else {
                    $tags = TagFactory::search()
                            ->filter($valueColumn, $tagId)
                            ->select();
                    if ($tags) {
                        $tag = $tags[0];
                    }
                }
                if ($tag) {
                    $acResult = $tag->getAutocompResult(NULL, $valueColumn);
                    foreach ($acResult as $key => $val) {
                        if (!isset($results[$key])) {
                            $results[$key] = array();
                        }
                        $results[$key][] = $val;
                    }
                }
            }
            return $results;
        } else {
            if (isset($_REQUEST['term'])) {
                $term = $_REQUEST['term'];
            } else {
                $term = '';
            }
            $itemLimit= ProjectConfig::getAutocompleteItemLimit();
            if (isset($attributes['itemLimit'])) {
                $itemLimit = $attributes['itemLimit'];
            }
            $termRef = GI_Sanitize::ref($term);
            $tagSearch = TagFactory::search()
                    ->setItemsPerPage($itemLimit);
            $pageNumber = 1;
            if(isset($attributes['pageNumber'])){
                $pageNumber = (int) $attributes['pageNumber'];
                $tagSearch->setPageNumber($pageNumber);
            }
            if (isset($attributes['type']) && !empty($attributes['type'])) {
                $typeRefs = explode(',', $attributes['type']);
                $tagSearch->filterGroup();
                foreach ($typeRefs as $typeRef) {
                    $tagSearch->filterByTypeRef($typeRef);
                    $tagSearch->orIf();
                }
                $tagSearch->closeGroup();
                $tagSearch->andIf();
            }
            if (!empty($termRef)) {
                $tagSearch->filterLike('ref', '%' . $termRef . '%')
                        ->orderByLikeScore('ref', $termRef);
            }
            
            if(isset($attributes['notIds']) && !empty($attributes['notIds'])){
                $notIds = explode(',', $attributes['notIds']);
                $tagSearch->filterNotIn('id', $notIds);
            }
            $tags = $tagSearch->select();
            $results = array();
            foreach ($tags as $tag) {
                $acResults = $tag->getAutocompResult($term, $valueColumn);
                $results[] = $acResults;
            }
            
            $count = $tagSearch->getCount();
            $this->addAutocompNavToResults($results, $count, $itemLimit, $pageNumber);
            
            if (isset($attributes['autocompField']) && Permission::verifyByRef('add_tags') && isset($attributes['addTag']) && $attributes['addTag']) {
                $autocompField = $attributes['autocompField'];
                $addURLProps = array(
                    'controller' => 'tag',
                    'action' => 'add',
                    'ajax' => 1
                );
                $addTagType = 'tag';
                if(isset($attributes['type'])){
                    $addTagType = $attributes['type'];
                    $addURLProps['type'] = $addTagType;
                }
                $sampleTag = TagFactory::buildNewModel($addTagType);
                $tagTypeTitle = $sampleTag->getTypeTitle();
                $addTitle = 'Add ' . $tagTypeTitle;
                if($addTagType != 'tag'){
                    $addTitle .= ' Tag';
                    $tagTypeTitle .= ' Tag';
                }
                $addHoverTitle = $addTitle;
                if(!empty($term)){
                    $addTitle = 'Add ' . $term . ' <span class="sml_text">[' . $tagTypeTitle . ']</span>';
                    $addURLProps['title'] = $term;
                }
                $addURL = GI_URLUtils::buildURL($addURLProps, false, true);
                // $autocompField = $attributes['autocompField'];
                $results[] = array(
                    'preventDefault' => 1,
                    'jqueryAction' => 'giModalOpenAjaxContent("' . $addURL . '","small_sized",function(){ $("#gi_modal").data("autocomplete-field","' . $autocompField . '"); });',
                    'liClass' => 'custom_btn',
                    'hoverTitle' => $addHoverTitle,
                    'autoResult' => '<span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">' . $addTitle . '</span>'
                );
            }
            
            return $results;
        }
    }
    
}
