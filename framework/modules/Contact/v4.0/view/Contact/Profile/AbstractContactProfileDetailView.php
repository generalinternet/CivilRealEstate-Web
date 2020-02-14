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
    
    protected function openTagsWrap(GI_View $view = NULL){
        if(empty($view)){
            $view = $this;
        }
        $view->addHTML('<div class="tag_items_wrap">');
        return $this;
    }
    
    protected function closeTagsWrap(GI_View $view = NULL){
        if(empty($view)){
            $view = $this;
        }
        $view->addHTML('</div>');
        return $this;
    }
    
    protected function addTagSectionContent(GI_View $view = NULL){
        if(empty($view)){
            $view = $this;
        }
        $tagContextData = $this->contact->getTagContextData();
        if(empty($tagContextData)){
            return;
        }
        foreach($tagContextData as $contextRef => $contextData){
            $this->openTagsWrap($view);
                $allTagTitles = $this->contact->getTagTitles($contextData['typeRef'], $contextRef);
                $term = 'Categories';
                if(isset($contextData['plTitle'])){
                    $term = $contextData['plTitle'];
                } elseif(isset($contextData['title'])){
                    $term = $contextData['title'];
                }
                if(count($allTagTitles) == 1){
                    $term = 'Category';
                    if(isset($contextData['title'])){
                        $term = $contextData['title'];
                    }
                }
                $view->addHTML('<span class="tag_items_label">' . $term . '</span>');
                if(empty($allTagTitles)){
                    $view->addHTML('<span class="tag_item empty">--</span>');
                } else {
                    foreach($allTagTitles as $tagTitle){
                        $view->addHTML('<span class="tag_item">');
                            $view->addHTML($tagTitle);
                        $view->addHTML('</span>');
                    }
                }
            $this->closeTagsWrap($view);
        }
    }
    
}
