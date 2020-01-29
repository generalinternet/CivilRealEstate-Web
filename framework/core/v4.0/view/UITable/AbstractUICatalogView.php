<?php
/**
 * Description of AbstractUICatalogView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractUICatalogView extends AbstractUITableView {
    
    protected $showPageBar = false;
    protected $loadPrev = true;
    protected $loadMore = true;
    protected $loadLinksWithAJAX = true;
    
    protected $catalogItemViewMethod = 'getCatalogItemView';

    protected function getTableClass(){
        if(empty($this->tableClass)){
            $this->addTableClass('ui_table');
            $this->addTableClass('ui_catalog');
        }
        if($this->loadLinksWithAJAX){
            $this->addTableClass('ajax_link_wrap');
        }
        return parent::getTableClass();
    }
    
    public function setCurId($curId){
        $this->curId = $curId;
    }
    
    public function setLoadLinksWithAJAX($loadLinksWithAJAX){
        $this->loadLinksWithAJAX = $loadLinksWithAJAX;
        return $this;
    }
   
    protected function openTable(){
        $this->addHTML('<div class="' . $this->getTableClass() . '">');
        return $this;
    }
    
    protected function closeTable(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildTableHeader() {
        return $this;
    }
    
    protected function buildTableBody(){
        $this->addHTML('<div class="ui_list_body"');
        if(!empty($this->listBodyId)){
            $this->addHTML(' id="' . $this->listBodyId . '"');
        }
        $this->addHTML('>');
            $this->buildRows();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openModelRow($model){
        return $this;
    }
    
    protected function closeModelRow($model){
        return $this;
    }
    
    protected function openModelCell($cssClass, $cellHoverTitle, $headerTitle){
        return $this;
    }
    
    protected function closeModelCell(){
        return $this;
    }
    
    public function setCatalogItemViewMethod($catalogItemViewMethod){
        $this->catalogItemViewMethod = $catalogItemViewMethod;
        return $this;
    }
    
    protected function buildRow($model) {
        $catalogItemViewMethod = $this->catalogItemViewMethod;
        if(!method_exists($model, $catalogItemViewMethod)){
            return;
        }
        $catalogItemView = $model->$catalogItemViewMethod();
        if($catalogItemView){
            $this->addHTML($catalogItemView->getHTMLView());
        }
    }
    
}
