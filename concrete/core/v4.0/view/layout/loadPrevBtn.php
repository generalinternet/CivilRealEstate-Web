<?php

class LoadPrevBtn extends GI_LoadMoreBtn {
    protected $label = 'Load Prev';
    protected $cssClass = 'load_more_btn prev_btn';
    protected $step = -1;
    
    protected function hasPageToLoad() {
        return ($this->currentPage > 1);
    }
}
