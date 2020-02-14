<?php

abstract class AbstractTagDetailView extends GI_View {

    /**
     * @var AbstractTag
     */
    protected $tag;

    public function __construct(AbstractTag $tag) {
        parent::__construct();
        $this->tag = $tag;
        $this->buildView();
    }

    protected function buildView() {
        $tagColour = $this->tag->getProperty('tag.colour');
        $tagTitle = $this->tag->getProperty('tag.title');
        $this->addHTML('<span class="tag_item" ><span class="tri" style="border-top-color: #' . $tagColour . ';"></span>'.$tagTitle.'</span>');
    }

}
