<?php
/**
 * Description of AbstractTagListView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractTagListView extends GI_View {
    
    protected $tags;
    protected $listTitle = 'Tags';
    
    public function __construct($tags) {
        parent::__construct();
        $this->tags = $tags;
    }
    
    public function setListTitle($listTitle) {
        $this->listTitle = $listTitle;
        return $this;
    }

    public function buildView() {
        if (!empty($this->tags)) {
            $this->addContentBlockTitle($this->listTitle);
            $this->addHTML('<div class="content_block_list">');
            foreach ($this->tags as $tag) {
                $system = $tag->getProperty('system');
                if (empty($system)) {
                    $tagDetailView = $tag->getDetailView();
                    if (!empty($tagDetailView)) {
                        $this->addHTML($tagDetailView->getHTMLView());
                    }
                }
            }
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
