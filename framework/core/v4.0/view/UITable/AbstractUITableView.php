<?php
/**
 * Description of AbstractUITableView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.5
 */
abstract class AbstractUITableView extends GI_View {
    
    /**
     * @var GI_Model[]
     */
    protected $models;
    protected $uiTableCols;
    /** @var GI_PageBarView */
    protected $pageBar = NULL;
    protected $noModelMessage = 'No data found.';
    protected $loadMore = false;
    protected $loadPrev = false;
    protected $showPageBar = true;
    protected $getRowsCalled = false;
    protected $tableWrapId = '';
    protected $tableClass = array();
    protected $rowsAreSelectable = false;
    protected $tableDataURLProps = NULL;
    protected $noModelMessageLocked = false;
    protected $getURLMethod = NULL;
    protected $targetId = NULL;
    protected $reverseLoadBtns = false;
    protected $listBodyId = '';
    
    public function __construct($models = array(), $uiTableCols = NULL, GI_PageBarView $pageBar = NULL) {
        parent::__construct();
        $this->setModels($models);
        $this->setUITableCols($uiTableCols);
        $this->setPageBar($pageBar);
    }
    
    public function setModels($models){
        $this->models = $models;
        return $this;
    }
    
    /**
     * @return GI_Model[]
     */
    public function getModels(){
        return $this->models;
    }
    
    public function setUITableCols($uiTableCols){
        $this->uiTableCols = $uiTableCols;
        return $this;
    }
    
    /**
     * @param boolean $loadMore
     * @return \AbstractUITableView
     */
    public function setLoadMore($loadMore){
        $this->loadMore = $loadMore;
        return $this;
    }
    
    /**
     * @param boolean $loadPrev
     * @return \AbstractUITableView
     */
    public function setLoadPrev($loadPrev){
        $this->loadPrev = $loadPrev;
        return $this;
    }
    
    /**
     * @param boolean $tableDataURLProps
     * @return \AbstractUITableView
     */
    public function setTableDataURLProps($tableDataURLProps){
        $this->tableDataURLProps = $tableDataURLProps;
        return $this;
    }
    
    /**
     * @param string $getURLMethod
     * @return \AbstractUITableView
     */
    public function setGetURLMethod($getURLMethod){
        $this->getURLMethod = $getURLMethod;
        return $this;
    }
    
    /**
     * @param string $targetId
     * @return \AbstractUITableView
     */
    public function setTargetId($targetId){
        $this->targetId = $targetId;
        return $this;
    }
    
    /**
     * @param GI_PageBarView $pageBar
     * @return \AbstractUITableView
     */
    public function setPageBar(GI_PageBarView $pageBar = NULL){
        $this->pageBar = $pageBar;
        return $this;
    }
    
    public function getQueryId(){
        if($this->pageBar){
            return $this->pageBar->getQueryId();
        }
        return NULL;
    }
    
    public function getTotalCount(){
        if($this->pageBar){
            return $this->pageBar->getTotalCount();
        }
        return 0;
    }
    
    /**
     * @param string $html
     * @return \AbstractUITableView
     */
    public function addPageBarRightHTML($html){
        if($this->pageBar){
            $this->pageBar->addRightHTML($html);
        }
        return $this;
    }
    
    /**
     * @param boolean $showPageBar
     * @return \AbstractUITableView
     */
    public function setShowPageBar($showPageBar){
        $this->showPageBar = $showPageBar;
        return $this;
    }
    
    protected function addPageBar(){
        if($this->showPageBar){
            if($this->pageBar){
                $this->addHTML($this->pageBar->getHTMLView());
            }
        }
        return $this;
    }
    
    protected function addLoadMore(){
        if($this->loadMore){
            if($this->pageBar){
                $loadMoreBtn = $this->pageBar->getLoadMoreBtn();
                $loadMoreBtn->setReverseLoadBtns($this->reverseLoadBtns);
                $this->addHTML($loadMoreBtn->getHTMLView());
            }
        }
        return $this;
    }
    
    protected function addLoadPrev(){
        if($this->loadPrev){
            if($this->pageBar){
                $loadPrevBtn = $this->pageBar->getLoadPrevBtn();
                $loadPrevBtn->setReverseLoadBtns($this->reverseLoadBtns);
                $this->addHTML($loadPrevBtn->getHTMLView());
            }
        }
        return $this;
    }
    
    public function setTableWrapId($tableWrapId){
        $this->tableWrapId = $tableWrapId;
        return $this;
    }
    
    public function addTableClass($class) {
        if (!in_array($class, $this->tableClass)) {
            array_push($this->tableClass, $class);
        }
        return $this;
    }
    
    public function setListBodyId($listBodyId){
        $this->listBodyId = $listBodyId;
        return $this;
    }
    
    public function setRowsAreSelectable($rowsAreSelectable){
        $this->rowsAreSelectable = $rowsAreSelectable;
        return $this;
    }
    
    public function setReverseLoadBtns($reverseLoadBtns) {
        $this->reverseLoadBtns = $reverseLoadBtns;
        return $this;
    }
    
    protected function getTableClass(){
        if(empty($this->tableClass)){
            $this->addTableClass('ui_table');
        }
        
        return implode(' ', $this->tableClass);
    }
    
    protected function openTableWrap(){
        $tableWrapIdAttr = '';
        if(!empty($this->tableWrapId)){
            $tableWrapIdAttr = 'id="' . $this->tableWrapId . '"';
        }
        
        $tableDataURLAttr = '';
        if(!empty($this->tableDataURLProps)){
            $tableDataURLAttr = 'data-init-load="' . GI_URLUtils::buildURL($this->tableDataURLProps) . '"';
        }
        $this->addHTML('<div ' . $tableWrapIdAttr . ' class="ui_table_wrap" ' . $tableDataURLAttr . '>');
        return $this;
    }
    
    protected function closeTableWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openTable(){
        $this->addHTML('<table class="' . $this->getTableClass() . '">');
        return $this;
    }
    
    protected function closeTable(){
        $this->addHTML('</table>');
        return $this;
    }
    
    protected function buildTable() {
        $this->openTableWrap();
        
        $this->addPageBar();
        
        if ($this->reverseLoadBtns) {
            $this->addLoadMore();
        } else {
            $this->addLoadPrev();
        }
        if(!empty($this->models)){
            $this->openTable();
            
            $this->buildTableHeader();
            
            $this->buildTableBody();
            
            $this->buildTableFooter();
            
            $this->closeTable();
        } else {
            $this->addNoModelMessage();
        }
        
        if (!$this->reverseLoadBtns) {
            $this->addLoadMore();
        } else {
            $this->addLoadPrev();
        }
        
        $this->closeTableWrap();
    }
    
    public function setNoModelMessage($noModelMessage, $lockNoModelMessage = false){
        if(!$this->noModelMessageLocked){
        $this->noModelMessage = $noModelMessage;
        }
        if($lockNoModelMessage){
            $this->noModelMessageLocked = true;
        }
        return $this;
    }
    
    protected function addNoModelMessage(){
        $this->addHTML('<p class="no_model_message">' . $this->noModelMessage . '</p>');
    }

    protected function buildTableHeader() {
        if (count($this->uiTableCols) > 0) {
            $this->addHTML('<thead>');
            $this->addHTML('<tr>');
            foreach ($this->uiTableCols as $uiTableCol) {
                $this->buildHeaderCell($uiTableCol);
            }
            $this->addHTML('</tr>');
            $this->addHTML('</thead>');
        }
        return $this;
    }
    
    protected function buildRows(){
        foreach ($this->models as $model) {
            $this->buildRow($model);
        }
    }
    
    public function getRows(){
        $this->getRowsCalled = true;
        $this->buildRows();
        return $this->html;
    }
    
    protected function buildTableBody(){
        $this->addHTML('<tbody class="ui_list_body"');
        if(!empty($this->listBodyId)){
            $this->addHTML(' id="' . $this->listBodyId . '"');
        }
        $this->addHTML('>');
            $this->buildRows();
        $this->addHTML('</tbody>');
        return $this;
    }
    
    protected function buildTableFooter() {
//        $this->addHTML('<tfoot>');
//        $this->addHTML('</tfoot>');
        return $this;
    }

    protected function buildHeaderCell(UITableCol $uiTableCol) {
        $cssHeaderClass = $uiTableCol->getCSSHeaderClass();
        $headerHoverTitle = $uiTableCol->getHeaderHoverTitle();
        $this->addHTML('<th class="' . $cssHeaderClass . '" title="' . $headerHoverTitle . '">');
        $headerTitle = $uiTableCol->getHeaderTitle();
        $this->addHTML($headerTitle);
        $this->addHTML('</th>');
    }

    protected function buildRow($model) {
        $this->openModelRow($model);
        foreach ($this->uiTableCols as $tableCol) {
            $this->buildCell($model, $tableCol);
        }
        $this->closeModelRow($model);
    }
    
    protected function getRowClass($model){
        $rowClass = '';
        if(is_a($model, 'GI_Model')){
            if(!$model->getProperty('status')){
                $rowClass = 'deleted';
            }
        }
        if($this->rowsAreSelectable){
            $rowClass .= ' selectable';
        }
        
        $seconds = GI_Time::getSecondsBetween($model->getProperty('inception'));
        if($seconds < 300 && $model->getProperty('last_mod_by') == Login::getUserId()){
            $rowClass .= ' new';
        }
        return $rowClass;
    }
    
    protected function openModelRow($model){
        $rowClass = $this->getRowClass($model);
        $this->addHTML('<tr class="' . $rowClass . '" data-model-id="' . $model->getProperty('id') . '">');
        return $this;
    }
    
    protected function closeModelRow($model){
        $this->addHTML('</tr>');
        return $this;
    }
    
    protected function buildCell($model, UITableCol $uiTableCol) {
        $cssClass = $uiTableCol->getCssClass();
        $cellHoverTitleMethodName = $uiTableCol->getCellHoverTitleMethodName();
        $cellHoverTitle = '';
        if(!empty($cellHoverTitleMethodName)){
            $cellHoverTitle = $model->$cellHoverTitleMethodName();
        }
        
        $methodName = $uiTableCol->getMethodName();
        $methodAttributes = $uiTableCol->getMethodAttributes();
        if (!empty($methodAttributes)) {
            $val = call_user_func_array(array(
                $model,
                $methodName
            ), $methodAttributes);
        } else {
            $val = $model->$methodName();
        }
        
        if(empty($cellHoverTitle)){
            $cellHoverTitle = GI_Sanitize::htmlAttribute($val);
        }
        
        $cellURLMethodName = $uiTableCol->getCellURLMethodName();
        if (!empty($cellURLMethodName)) {
            $cellURL = $model->$cellURLMethodName();
            if(!empty($cellURL)){
                $cssClass .= ' linked';
                $val = '<a href="' . $cellURL . '" title="' . $cellHoverTitle . '">' . $val . '</a>';
            }
        }
        
        $this->openModelCell($cssClass, $cellHoverTitle);
        $this->addHTML($val);
        $this->closeModelCell();
    }
    
    protected function openModelCell($cssClass, $cellHoverTitle){
        $this->addHTML('<td class="' . $cssClass . '" title="' . $cellHoverTitle . '">');
        return $this;
    }
    
    protected function closeModelCell(){
        $this->addHTML('</td>');
        return $this;
    }
    
    public function beforeReturningView() {
        if($this->getRowsCalled){
            $this->html = '';
        }
        $this->buildTable();
    }
    
    public function addDefaultSelectRowColumn($modelClass = NULL){
        if(empty($modelClass)){
            if(!$this->models){
                return false;
            }
            $reversedArray = array_reverse($this->models);
            $sampleModel = array_pop($reversedArray);
            if($sampleModel){
                $modelClass = get_class($sampleModel);
            }
            if(empty($modelClass)){
                return false;
            }
        }
        $onoffTableCol = UITableCol::buildUITableColFromArray(array(
            'header_title' => $modelClass::getSelectAllRowsOnOff(),
            'header_hover_title' => 'Select All',
            'css_header_class' => 'select_all_column',
            'method_name' => 'getSelectRowOnOff',
            'css_class' => 'select_row_column',
            'cell_hover_title_method_name' => 'getSelectRowOnOffHoverTitle'
        ));

        //to put the column at the beginning of any uiTableCol array
        array_unshift($this->uiTableCols, $onoffTableCol);
        return $this;
    }

}
