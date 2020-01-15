<?php

abstract class AbstractNoteThreadView extends GI_View {
    
    protected $notes;
    protected $model;
    protected $fullView = false;
    protected $itemsPerPage = 3;


    public function __construct($notes = NULL, $model = NULL) {
        parent::__construct();
        $this->notes = $notes;
        $this->model = $model;
    }
    
    /**
     * @param Boolean$fullView
     */
    public function setFullView($fullView) {
        $this->fullView = $fullView;
    }
    
    public function setItemsPerPage($itemsPerPage) {
        $this->itemsPerPage = $itemsPerPage;
    }

    protected function buildView() {
            if ($this->fullView) {
                $this->openViewWrap();
            }
            $this->buildViewHeader();
            $this->buildViewBody();
            $this->buildViewFooter();
            if ($this->fullView) {
                $this->closeViewWrap();
            }
    }

    protected function buildViewHeader() {
        $this->addThreadTitle();
        $this->addNoteField();
    }

    protected function addNoteField() {
        $fc = str_replace('Factory', '', $this->model->getFactoryClassName());
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'note',
                    'action' => 'add',
                    'modelId' => $this->model->getProperty('id'),
                    'fc' => $fc,
        ));
        $this->addHTML('<div class="ajaxed_contents auto_load" data-url="' . $addURL . '"></div>');
    }
    
    protected function buildViewBody() {
        $this->buildNotesThread();
    }
    
    protected function addThreadTitle() {
        $title = 'Notes';
        if (!empty($this->model)) {
            $title = $this->model->getViewTitle(false) . ' ' . $title;
        }
        $this->addHTML('<h3>'.$title.'</h3>');
    }
    
    protected function buildNotesThread() {
        $loadMoreURL = GI_URLUtils::buildURL($this->getAjaxLoadURLAttrs());
        $this->addHTML('<div id="notes_thread" class="notes_thread ajaxed_contents auto_load" data-url="'.$loadMoreURL.'">');
//        $this->addHTML('<div class="notes_thread">');
//        if (!empty($this->notes)) {
//            foreach ($this->notes as $note) {
//                if ($note->getIsViewable()) {
//                    $this->addNoteToThread($note);
//                }
//            }
//        }
//        $this->addHTML($this->getLoadMoreButtonHTML());
        $this->addHTML('</div>');
    }

    public function getLoadMoreButtonHTML($pageNumber = 2) {
        $type = 'note';
        if (!empty($this->notes)) {
            $type = $this->notes[0]->getTypeRef();
        }
        $notes = NoteFactory::getNotesLinkedToModel($this->model, $type, $pageNumber, $this->itemsPerPage);
        if (!empty($notes)) {
            $loadMoreURLAttrs = $this->getAjaxLoadURLAttrs();
            $loadMoreURLAttrs['page'] = $pageNumber;
            $loadMoreURL = GI_URLUtils::buildURL($loadMoreURLAttrs);
            return '<span id="load_more_notes" class="other_btn load_more_btn loading_auto_min_height" data-url="'.$loadMoreURL.'">Load More...</span>';
        }
            
        return '';
    }
    
    public function getAjaxLoadURLAttrs() {
        $type = 'note';
        if (!empty($this->notes)) {
            $type = $this->notes[0]->getTypeRef();
        }
        $fc = str_replace('Factory', '', $this->model->getFactoryClassName());
        return array(
                    'controller' => 'note',
                    'action' => 'GetNotesAndButtonHTML',
                    'type'=>$type,
                    'modelId' => $this->model->getProperty('id'),
                    'fc' => $fc,
                );
    }

    protected function addNoteToThread(AbstractNote $note) {
        $this->addHTML($note->getView()->getHTMLView());
    }
    
    protected function buildViewFooter() {
        
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    
}