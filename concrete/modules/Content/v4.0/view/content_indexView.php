<?php

class ContentIndexView extends AbstractContentIndexView{
    
    public function __construct($models, AbstractUITableView $uiTableView, AbstractContent $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->setWindowIcon($this->sampleModel->getWindowIcon());
    }
}
