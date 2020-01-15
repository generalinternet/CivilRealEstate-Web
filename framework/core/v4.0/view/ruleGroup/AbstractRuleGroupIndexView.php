<?php
/**
 * Description of AbstractRuleGroupIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupIndexView extends GI_View {
        /** @var AbstractRuleGroup[] */
    protected $ruleGroups;
    /** @var UITableView */
    protected $uiTableView;
    /** @var AbstractOrder */
    protected $sampleRuleGroup;
    /** @var GI_SearchView */
    protected $searchView = NULL;
    protected $addWrap = true;


    public function __construct($ruleGroups, AbstractUITableView $uiTableView, AbstractRuleGroup $sampleRuleGroup, GI_SearchView $searchView = NULL) {
        parent::__construct();
        $this->ruleGroups = $ruleGroups;
        $this->uiTableView = $uiTableView;
        $this->sampleRuleGroup = $sampleRuleGroup;
        $this->searchView = $searchView;
        $this->addSiteTitle($sampleRuleGroup->getViewTitle(true));
    }
    

    /**
     * @param boolean $addWrap
     * @return \AbstractOrderIndexView
     */
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }
    
    protected function openViewWrap() {
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }

    protected function closeViewWrap() {
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }

    protected function addAddBtn() {
        if ($this->sampleRuleGroup->isAddable()) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'rule',
                        'action' => 'preAddRuleGroup'
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Rule Group" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon add"></span></span><span class="btn_text">Add Rule Group</span></a>');
        }
    }

    protected function addSearchBtn() {
//        if($this->searchView){
//            $title = $this->sampleRuleGroup->getViewTitle();
//
//            $this->addHTML('<span title="Search ' . $title . '" class="custom_btn gray open_search_box" data-box="' . $this->searchView->getBoxId() . '" ><span class="icon_wrap"><span class="icon search"></span></span><span class="btn_text">Search</span></span>');
//        }
    }
    
    protected function addHeaderTitle($headerClass = ''){
        $this->addHTML('<h1>Rule Groups</h1>');
    }
    
//    protected function addFilterWarehouseBtn(){
//        $filterBtn = new InvFilterWarehouseBtnView();
//        $this->addHTML($filterBtn->getHTMLView());
//    }

    protected function buildView() {
        $this->openViewWrap();

        if ($this->searchView) {
            $this->addHTML($this->searchView->getHTMLView());
        }

        $this->addHTML('<div class="right_btns">');
//            $this->addSearchBtn();
            $this->addAddBtn();
        $this->addHTML('</div>');
        $this->addHeaderTitle();

        $this->addTable();
        
        $this->closeViewWrap();
    }
    
    protected function addTable(){
        $this->addHTML($this->uiTableView->getHTMLView());
    }

    public function beforeReturningView() {
        $this->buildView();
    }
}