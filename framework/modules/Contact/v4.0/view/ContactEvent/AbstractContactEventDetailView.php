<?php
/**
 * Description of AbstractContactEventDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactEventDetailView extends GI_View {
    
    protected $contactEvent;
    protected $refresh = false;
    
    public function __construct(AbstractContactEvent $contactEvent) {
        parent::__construct();
        $this->contactEvent = $contactEvent;
    }
    
    public function setRefresh($refresh){
        $this->refresh = $refresh;
        return $this;
    }

    public function buildDetailView(){
        $this->addButtonsSection();
        $this->addHeaderTitle();
        $this->addContactName();
        $this->addHTML('<div class="columns thirds top_align">');
            $this->addHTML('<div class="column">');
                $this->addFileView();
            $this->addHTML('</div>');
            $this->addHTML('<div class="column two_thirds">');
                $this->addStartEndDateTimeView();
                $this->addCreatedByName();
                $this->addNotesView();
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
        return $this;
    }

    protected function closeViewWrap() {
        $this->addHTML('</div>');
        return $this;
    }

    protected function buildView() {
        $this->openViewWrap();
        $this->buildDetailView();
        $this->addLinkedContactsSection();
        $this->closeViewWrap();
    }

    protected function addHeaderTitle() {
        $title = $this->contactEvent->getProperty('title');
        if (empty($title)) {
            $title = $this->contactEvent->getTypeTitle();
        }
        
        $this->addHTML('<h1 class="main_head">' . $title . '</h1>');
    }
    
    protected function addContactName() {
        $this->addHTML('<h2 class="subtitle">' . $this->contactEvent->getContactName() . '</h2>');
    }
    
    protected function addButtonsSection() {
        $this->addHTML('<div class="right_btns">');
        if ($this->contactEvent->isDeleteable()) {
            $deleteURL = $this->contactEvent->getDeleteURL();
            if ($this->refresh) {
                $deleteURL .= '&refresh=1';
            }
            $this->addHTML('<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete">'.GI_StringUtils::getIcon('trash').'<span class="btn_text">Delete</span></a>');
        }

        if ($this->contactEvent->isEditable()) {
            $editURL = $this->contactEvent->getEditURL();
            if ($this->refresh) {
                $editURL .= '&refresh=1';
            }
            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn open_modal_form" data-modal-class="medium_sized">'.GI_StringUtils::getIcon('edit').'<span class="btn_text">Edit</span></a>');
        }

        $this->addHTML('</div>');
    }
    
    /**
     * Add StartDate EndDate View
     */
    protected function  addStartEndDateTimeView() {
        $startDateTime = $this->contactEvent->getDisplayStartDateTime();
        $endDateTime = $this->contactEvent->getDisplayEndDateTime();
        $this->addContentBlock(GI_Time::formatFromDateTimeToDateTime($startDateTime, $endDateTime), 'Date & Time');
    }
    
    protected function addCreatedByName() {
        $this->addContentBlock($this->contactEvent->getCreatedByName(), 'Created By');
    }
    
    /**
     * Add notes View
     */
    protected function addNotesView() {
        $notes = $this->contactEvent->getProperty('notes');
        $this->addContentBlock($notes, 'Notes');
    }
    /**
     * @todo implement addLinkedContactsSection
     */
    protected function addLinkedContactsSection() {
    }
    
    /**
     * Add attached file view
     */
    protected function addFileView(){
        $this->addHTML('<h3 class="content_block_title">Attached Files</h3>');
        $folder = $this->contactEvent->getFolder(false);
        if($folder){
            $this->addHTML('<div class="content_files">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $fileView = $file->getView();
                $fileView->setIsDeleteable(false);
                $fileView->setIsRenamable(false);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
