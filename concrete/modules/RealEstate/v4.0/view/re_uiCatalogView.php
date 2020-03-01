<?php

class REUICatalogView extends UICatalogView{
    protected $isOpenHouse = false;

    public function __construct($models = array(), $uiTableCols = NULL, GI_PageBarView $pageBar = NULL, $isOpenHouse) {
        parent::__construct();
        $this->setModels($models);
        $this->setUITableCols($uiTableCols);
        $this->setPageBar($pageBar);
        $this->isOpenHouse = $isOpenHouse;
    }

    protected function buildRow($model) {
        $catalogItemView = $model->getCatalogItemWithOpenHouseView($this->isOpenHouse);
        if($catalogItemView){
            $this->addHTML($catalogItemView->getHTMLView());
        }
    }
}