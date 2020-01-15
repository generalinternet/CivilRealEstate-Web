<?php
/**
 * Description of AbstractNoteDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractNoteDetailView extends GI_View {
    
    protected $note;
    protected $uploader;
    
    public function __construct(AbstractNote $note, AbstractGI_Uploader $uploader = NULL) {
        parent::__construct();
        $this->note = $note;
        $this->uploader = $uploader;
    }

    protected function buildView() {
        $this->addHTML('<div class="note_wrap" id="note_id_'.$this->note->getId().'">');
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->addHTML('</div>');
    }

    protected function buildViewHeader() {
        $this->addHTML('<div class="note_header">');
        $this->addUserDetails();
        $this->addTimestamp();
        $this->addHTML('</div>');
    }
    
    protected function addUserDetails($user = NULL) {
        if (empty($user)) {
            $user = $this->note->getUser();
        }
        $avatarHTML = $user->getUserAvatarHTML(30,30);
        $this->addHTML($avatarHTML);
        $this->addHTML('<div class="user_info">')
                ->addHTML('<div class="user_name">');
        $this->addHTML($user->getFullName());
        $this->addHTML('</div>')
                ->addHTML('<div class="user_email">');
        $this->addHTML($user->getProperty('email'));
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addTimestamp() {
        $this->addHTML('<div class="timestamp_wrap">');
        if ($this->note->hasAttachments()) {
            $this->addHTML('<span class="icon dark_gray paperclip"> </span>');
        }
        $timeStamp = $this->note->getTimestampForDisplay();
        $this->addHTML($timeStamp);
        $this->addHTML('</div>');
    }

    protected function buildViewBody() {
        $this->buildSummaryView();
        $this->buildFullView();
    }

    protected function buildSummaryView() {
        $this->addHTML('<div class="summary_view">');
        $this->addHTML('<p>');
        $this->addHTML($this->note->getContent(true));
        $this->addHTML('</p>');
        $this->addHTML('</div>');
    }
    
    protected function buildFullView() {
        $this->addHTML('<div class="full_view">');
        $this->addHTML('<p>');
        $this->addHTML(GI_StringUtils::convertURLs($this->note->getContent()));
        $this->addHTML('</p>');
        $this->addFileViews();
        $this->addNotifiedUsersSection();
        $this->addHTML('</div>');
    }

    protected function addFileViews() {
        $folder = $this->note->getFolder(false);
        if ($folder && $this->note->hasAttachments()) {
            $this->addHTML('<div class="content_files note_uploader">');
            $files = $folder->getFiles();
            foreach ($files as $file) {
                $fileView = $file->getView();
                $fileView->setIsDeleteable(false);
                $fileView->setIsRenamable(false);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
        }
    }

    protected function addNotifiedUsersSection() {
        $notifiedUsers = $this->note->getNotifiedUsers();
        if (!empty($notifiedUsers)) {
            $this->addHTML('<hr />');
            $this->addHTML('<div class="notified_users_wrap">');
            foreach ($notifiedUsers as $notifiedUser) {
                $this->addHTML('<div class="notified_user_wrap">');
                $this->addUserDetails($notifiedUser);
                $this->addHTML('</div>');
            }
            $this->addHTML('</div>');
        }
    }

    protected function buildViewFooter() {
        $this->addHTML('<div class="note_btns">');
        $this->addEditButton();
        $this->addDeleteButton();
        $this->addHTML('</div>');
    }

    protected function addEditButton() {
        if ($this->note->getIsEditable()) {
            $editURL = $this->note->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" class="custom_btn open_modal_form" title="Edit Note" data-modal-class="medium_sized"><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text"></span></a>');
        }
    }
    protected function addDeleteButton() {
        if ($this->note->getIsDeleteable()) {
            $deleteURL = $this->note->getDeleteURL();
            $this->addHTML('<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete Note"><span class="icon_wrap"><span class="icon trash"></span></span><span class="btn_text"></span></a>');
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
