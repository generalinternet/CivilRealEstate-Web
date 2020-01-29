<?php
/**
 * Description of AbstractContactProfileDetailView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.1.1
 */
abstract class AbstractContactProfileDetailView extends MainWindowView {

    /** @var AbstractContact */
    protected $contact = NULL;
    protected $addQuickbooksBar = false;
    protected $hasOverlay = true;
    protected $curTab = '';
    protected $addContactInfoAndTags = true;

    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
        $title = $contact->getDisplayName();
        $this->setWindowTitle($title);
    }
    

    
    public function setCurTab($curTab) {
        $this->curTab = $curTab;
        return $this;
    }

    public function hasOverlay($hasOverlay) {
        $this->hasOverlay = $hasOverlay;
    }

//    protected function addViewBodyContent() {
//        
//    }
}
