<?php
/**
 * Description of AbstractContactEventIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactEventIndexView extends ListWindowView {
    
    /** @var AbstractContactEvent[] */
    protected $models = array();
    /** @var AbstractContactEvent */
    protected $sampleModel = NULL;
    protected $showBtns = true;
    protected $contact;
    protected $catType;
    protected $addTitle = true;
    protected $title = '';
    protected $addTypeSelector = false;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractContactEvent $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Contact History');
       // $typeTitle = $this->sampleModel->getTypeTitle();
        $viewTitle = $this->sampleModel->getViewTitle();
        $this->catType = 'client';//Default
        $this->setWindowTitle($viewTitle);
        $this->setWindowIcon('event');
        $this->setListItemTitle($viewTitle);
        $this->title = $viewTitle;
    }
    
    public function setShowBtns($showBtns) {
        $this->showBtns = $showBtns;
    }
    
    public function setContact($contact) {
        $this->contact = $contact;
    }
    
    public function setAddTitle($addTitle){
        $this->addTitle = $addTitle;
        return $this;
    }
    
    public function setCatType($catType) {
        $this->catType = $catType;
    }
    
    protected function addAddBtn() {
        if (!empty($this->contact) && $this->contact->isEventAddable()) {
            $addURL = $this->contact->getAddEventURL();
            $this->addHTML('<a href="' . $addURL . '" title="Add" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Add</span></a>');
        }
    }

    protected function addWindowBtns() {
        if ($this->showBtns) {
            $this->addAddBtn();
        }
    }
    
    protected function addWindowTitle(){
        if ($this->addOuterWrap) {
            parent::addWindowTitle();
        }
    }
    
    protected function addTable(){
        if ($this->addOuterWrap) {
            parent::addTable();
        } else {
            $this->addEventList();
        }
        
        return $this;
    }
    
    protected function addEventList() {
        if (count($this->models) > 0) {
            $this->addHTML('<div class="contact_events">');
            foreach ($this->models as $contactEvent) {
                $this->addHTML('<div class="contact_event">');
                    $this->addEventBtns($contactEvent);
                    $this->addEventContent($contactEvent);
                $this->addHTML('</div><!--.columns-->');
            }
            $this->addHTML('</div><!--.contact_events-->');
        } else {
            $this->addHTML('<p class="no_data_found">No '.$this->title.' found.</p>');
        }
    }
    
    protected function addEventBtns($contactEvent) {
        $btnHTML = '';
        if ($contactEvent->isDeleteable()) {
            $deleteURL = $contactEvent->getDeleteURL();
            $btnHTML .= '<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete">'.GI_StringUtils::getIcon('trash').'</a>';
        }
        if ($contactEvent->isEditable()) {
            $editURL = $contactEvent->getEditURL();
            $btnHTML .= '<a href="' . $editURL . '" title="Edit" class="custom_btn edit_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('edit').'</a>';
        }
        if (!empty($btnHTML)) {
            $this->addHTML('<div class="right_btns">');
            $this->addHTML($btnHTML);
            $this->addHTML('</div><!--.right_btns-->');
        }
    }
    
    protected function addEventContent($contactEvent) {
        $this->addHTML('<div class="contact_event_content">');
            $this->addEventTitle($contactEvent);
            $this->addEventNotes($contactEvent);
        $this->addHTML('</div><!--.contact_event-->');
    }
    
    protected function addEventTitle($contactEvent) {
        $viewURL = $contactEvent->getViewURL();
        $title = $contactEvent->getProperty('title');
        if (empty($title)) {
            $title = $contactEvent->getTypeTitle();
        }
        $hasAttachedFiles = $contactEvent->hasAttachedFiles();
        if ($hasAttachedFiles) {
            $title .= '<span class="has_attached_files" title="Has attached files">'.GI_StringUtils::getIcon('paperclip').'</span>';
        }
        $startDateTime = $contactEvent->getDisplayStartDateTime();
        $endDateTime = $contactEvent->getDisplayEndDateTime();
        
        $this->addHTML('<h3 class="title">');
        if ($contactEvent->isViewable()) {
            $this->addHTML('<a href="' . $viewURL . '" class="open_modal_form" data-modal-class="medium_sized">'.$title.'</a>');
        } else {
            $this->addHTML('<span>'.$title.'</span>');
        }

        $this->addHTML('<span class="event_time">'. GI_Time::formatFromDateTimeToDateTime($startDateTime, $endDateTime).', '. $contactEvent->getCreatedByName().'</span>')
        ->addHTML('</h3>');
    }
    
    protected function addEventNotes($contactEvent) {
        $notes = $contactEvent->getProperty('notes');
        if (!empty($notes)) {
            $this->addHTML('<p class="event_note">')
                    ->addHTML($notes);
            $this->addHTML('</p>');
        }
    }
    
    public function setAddTypeSelector($addTypeSelector = true) {
        $this->addTypeSelector = $addTypeSelector;
    }
    
    protected function addTypeSelector() {
        if ($this->addOuterWrap && $this->addTypeSelector) {
            $typeRefs = ContactCatFactory::getTypesArray();
            if (isset($typeRefs['category'])) {
                unset($typeRefs['category']);
            }
            if (!empty($typeRefs)) {
                $viewableCnt = 0;
                $linkHTML = '';
                foreach ($typeRefs as $typeRef => $typeTitle) {
                    $contactCat = ContactCatFactory::buildNewModel($typeRef);
                    if ($contactCat->isIndexViewable()) {
                        $viewableCnt++;
                        $indexURL = GI_URLUtils::buildURL(array(
                            'controller'=>'contactevent',
                            'action'=>'index',
                            'catType'=>$typeRef,
                            'fullView'=> 1,
                        ));
                        $linkHTML .= '<a href="'.$indexURL.'" class="other_btn ajax_link '.(($this->catType == $typeRef)? ' current':'').'" data-target-id="list_bar">'.$typeTitle.'</a>';
                    }
                }

                if ($viewableCnt > 1) {
                    $this->addHTML('<div class="top_selector">');
                        $this->addHTML($linkHTML);
                    $this->addHTML('</div>');
                }

            }
        }
    }
        
}
