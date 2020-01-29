<?php
/**
 * Description of AbstractUIRolodexView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractUIRolodexView extends AbstractUITableView {
    
    protected $curId = NULL;
    protected $getURLMethod = 'getViewURL';
    protected $showPageBar = false;
    protected $loadPrev = true;
    protected $loadMore = true;
    protected $loadLinksWithAJAX = true;
    protected $tileIdPrefix = '';

    protected function getTableClass(){
        if(empty($this->tableClass)){
            $this->addTableClass('ui_table');
            $this->addTableClass('ui_tiles');
        }
        if($this->loadLinksWithAJAX){
            $this->addTableClass('ajax_link_wrap');
        }
        return parent::getTableClass();
    }
    
    public function setCurId($curId){
        $this->curId = $curId;
    }
    
    public function setTileIdPrefix($tileIdPrefix){
        $this->tileIdPrefix = $tileIdPrefix;
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
    
    protected function getRowClass($model){
        $rowClass = parent::getRowClass($model);
        
        if ($model->getId() == $this->curId ) {
            $rowClass .= ' current';
        }
        
        if(method_exists($model, 'getTileRowClass') && !empty($model->getTileRowClass())){
            $rowClass .= ' '.$model->getTileRowClass();
        }
            
        return $rowClass;
    }
    
    protected function openModelRow($model){
        $rowClass = $this->getRowClass($model);
        $url = '';
        if(!empty($this->getURLMethod)){
            $urlMethod = $this->getURLMethod;
            $url = $model->$urlMethod();
            $rowClass .= ' tile_link';
        }
        $dataURL = '';
        if(!empty($url)){
            $dataURL .= 'data-url="' . $url . '"';
        }
        $targetId = '';
        if (!empty($this->targetId)) {
            $targetId .= 'data-target-id="' . $this->targetId . '"';
        }
        if (!empty($this->tileIdPrefix)) {
            $tileId = $this->tileIdPrefix.$model->getProperty('id');
        } else {
            $tileId = $model->getTypeRef().'_'.$model->getProperty('id');
        }
        $this->addHTML('<div class="ui_tile ' . $rowClass . '" id="'.$tileId.'" data-model-id="' . $model->getProperty('id') . '" ' . $dataURL . ' '.$targetId.'>');
            $this->addHTML('<div class="ui_tile_content">');
        return $this;
    }
    
    protected function closeModelRow($model){
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openModelCell($cssClass, $cellHoverTitle, $headerTitle){
        $this->addHTML('<div class="ui_tile_cell ' . $cssClass . '" title="' . $cellHoverTitle . '" data-label="' . $headerTitle . '">');
        return $this;
    }
    
    protected function closeModelCell(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildRow($model) {
        $cardView = $model->getUICardView();
        if($cardView){
            if ($model->getId() == $this->curId ) {
                $cardView->addCardClass('current');
            }
            $this->addHTML($cardView->getHTMLView());
        } else {
            parent::buildRow($model);
        }
    }
    
}
