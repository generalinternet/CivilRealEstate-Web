<?php

/**
 * Description of AbstractRecentActivityIndexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractRecentActivityIndexView extends ListWindowView {

    /** @var AbstractRecentActivity[] */
    protected $models = array();
    /** @var AbstractRecentActivity */
    protected $sampleModel = NULL;
    protected $tabbed = false;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractRecentActivity $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle($sampleModel->getViewTitle(true));
        $this->setWindowTitle($this->sampleModel->getIndexTitle());
        $this->setWindowIcon('activity');
        $this->setListItemTitle($sampleModel->getViewTitle());
    }
    
    public function setIsTabbed($tabbed = false) {
        $this->tabbed = $tabbed;
    }
}