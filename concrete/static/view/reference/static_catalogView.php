<?php

class StaticCatalogView extends GI_View {

    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Catalog View Example');
    }
    
    protected function buildView() {
        $this->addHTML('<div class="view_wrap">');
            $this->addHTML('<div class="view_header">');
            $this->addMainTitle('Catalog View Example');
            $this->addHTML('</div>');
            $this->addHTML('<div class="view_body">');
                
                $itemsPerPage = ProjectConfig::getUITableItemsPerPage();
            
                $search = PermissionFactory::search()
                        ->setItemsPerPage($itemsPerPage);
                $models = $search->select();
                $linkArray = array(
                    'controller' => 'permission',
                    'action' => 'index',
                    'queryId' => $search->getQueryId(),
                    'catalog' => 1,
                    'targetId' => NULL
                );
                $totalCount = $search->getCount();
                $pageBar = new PageBarView($linkArray, $itemsPerPage, $totalCount);
                $uiTableCols = NULL;
                $catalogView = new UICatalogView($models, $uiTableCols, $pageBar);
                $this->addHTML($catalogView->getHTMLView());
                
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
