<?php
/**
 * Description of GI_PageBarView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.3
 */
abstract class GI_PageBarView extends GI_View {

    protected $linkArray = array();
    protected $itemsPerPage;
    protected $totalCount;
    protected $currentPage;
    protected $pageLinks;
    protected $pgnFirst = '&laquo;';
    protected $pgnPrev = '&lsaquo;';
    protected $pgnNext = '&rsaquo;';
    protected $pgnLast = '&raquo;';
    protected $pgnMore = '...';
    protected $showMore = true;
    protected $useAjax = false;
    protected $showCount = true;
    protected $rightHTML = '';

    public function __construct($linkArray, $itemsPerPage, $totalCount, $currentPage = 1, $pageLinks = 3) {
        parent::__construct();
        $this->setLinkArray($linkArray);
        $this->setItemsPerPage($itemsPerPage);
        $this->setTotalCount($totalCount);
        $this->setCurrentPage($currentPage);
        $this->setPageLinks($pageLinks);
    }
    
    /**
     * @param string $html
     * @return \GI_PageBarView
     */
    public function addRightHTML($html){
        $this->rightHTML .= $html;
        return $this;
    }
    
    public function setLinkArray($linkArray){
        $this->linkArray = $linkArray;
        if(!isset($this->linkArray['queryId'])){
            $this->linkArray['queryId'] = 0;
        }
        return $this;
    }
    
    public function getQueryId(){
        if(!isset($this->linkArray['queryId'])){
            $this->linkArray['queryId'] = 0;
        }
        return $this->linkArray['queryId'];
    }
    
    public function setItemsPerPage($itemsPerPage){
        $this->itemsPerPage = $itemsPerPage;
        if(empty($this->itemsPerPage) && !empty($this->totalCount)){
            $this->itemsPerPage = $this->totalCount;
        }
        return $this;
    }
    
    public function setTotalCount($totalCount){
        $this->totalCount = $totalCount;
        if(empty($this->itemsPerPage)){
            $this->setItemsPerPage($this->totalCount);
        }
        return $this;
    }
    
    public function setCurrentPage($currentPage){
        $this->currentPage = $currentPage;
        return $this;
    }
    
    /**
     * @param boolean $showCount
     * @return \GI_PageBarView
     */
    public function setShowCount($showCount){
        $this->showCount = $showCount;
        return $this;
    }
    
    /**
     * @param boolean $useAjax
     * @return \GI_PageBarView
     */
    public function setUseAjax($useAjax){
        $this->useAjax = $useAjax;
        return $this;
    }
    
    /**
     * @param boolean $showMore
     * @return \GI_PageBarView
     */
    public function setShowMore($showMore){
        $this->showMore = $showMore;
        return $this;
    }
    
    /**
     * @param integer $pageLinks
     * @return \GI_PageBarView
     */
    public function setPageLinks($pageLinks){
        $this->pageLinks = $pageLinks;
        return $this;
    }
    
    public function getTotalCount($returnString = false, $numericSpans = true){
        if($returnString){
            $startIndex = (($this->currentPage - 1) * $this->itemsPerPage) + 1;
            $endIndex = $this->currentPage * $this->itemsPerPage;
            if($endIndex > $this->totalCount){
                $endIndex = $this->totalCount;
            }
            $startIndexString = $startIndex;
            $endIndexString = $endIndex;
            $totalCountString = $this->totalCount;
            if($numericSpans){
                $startIndexString = '<span class="start_index numeric_index">' . $startIndex . '</span>';
                $endIndexString = '<span class="end_index numeric_index">' . $endIndexString . '</span>';
                $totalCountString = '<span class="last_index numeric_index">' . $totalCountString . '</span>';
            }
            return 'Showing ' . $startIndexString . ' to ' . $endIndexString . ' of ' . $totalCountString;
        }
        return $this->totalCount;
    }
    
    protected function addLimitOptions(){
        if(!ProjectConfig::showResultsPerPagePicker()){
            return;
        }
        $limitOptions = array(
            10,
            25,
            50,
            100
        );
        $curLimit = ProjectConfig::getUITableItemsPerPage();
        if(!in_array($curLimit, $limitOptions)){
            $limitOptions[] = $curLimit;
        }
        sort($limitOptions);
        $this->addHTML('<div class="result_limit_bar"><span class="limit_label">Results Per Page</span><ul>');
        foreach($limitOptions as $limitOption){
            $class = '';
            if($limitOption == $curLimit){
                $class .= 'current';
            }
            $term = 'results';
            if($limitOption == 1){
                $term = 'result';
            }
            $pgnFirstLinkArray = $this->linkArray;
            $pgnFirstLinkArray['pageNumber'] = 1;
            $dataAjax = 0;
            if($this->useAjax){
                $dataAjax = 1;
            }
            $pgnFirstLink = GI_URLUtils::buildURL($pgnFirstLinkArray);
            $this->addHTML('<li class="' . $class . '"><a href="' . $pgnFirstLink . '" class="limit_btn" data-limit="' . $limitOption . '" data-ajax="' . $dataAjax . '" title="Limit to ' . $limitOption . ' ' . $term . ' per page">' . $limitOption . '</a></li>');
        }
        $this->addHTML('</ul></div>');
    }

    protected function buildBar() {
        if($this->totalCount <= 0){
            return NULL;
        }
        
        $paginationBtnClass = '';
        if($this->useAjax){
            $paginationBtnClass = 'pagination_btn';
        }
        
        $queryId = $this->getQueryId();
        $pageCount = ceil($this->totalCount / $this->itemsPerPage);
        
        $this->addHTML('<div class="pagination_bar');
        if ($pageCount > 1) {
            $this->addHTML(' multi_pages');
        }
        $this->addHTML('">');
        if($this->showCount){
            $this->addHTML('<div class="total_count">' . $this->getTotalCount(true) . '</div>');
        }
        
        $this->addLimitOptions();
        
        if ($pageCount > 1) {
            $linksAfter = $this->pageLinks;
            if ($this->currentPage - $this->pageLinks <= 0) {
                $linksAfter += $this->pageLinks - $this->currentPage + 1;
            }
            $linksBefore = $this->pageLinks;
            if ($this->currentPage + $this->pageLinks > $pageCount) {
                $linksBefore += $this->pageLinks - ($pageCount - $this->currentPage);
            }
            $firstLink = $this->currentPage - $linksBefore;
            if ($firstLink <= 0) {
                $firstLink = 1;
            }
            $lastLink = $this->currentPage + $linksAfter;
            if ($lastLink > $pageCount) {
                $lastLink = $pageCount;
            }
            $prevPage = $this->currentPage - 1;
            $nextPage = $this->currentPage + 1;
            
            $this->addHTML('<ul class="pagination" data-query="' . $queryId . '">');
            if ($this->currentPage != 1) {
                $pgnFirstLinkArray = $this->linkArray;
                $pgnFirstLinkArray['pageNumber'] = 1;
                $pgnFirstLink = GI_URLUtils::buildURL($pgnFirstLinkArray);
                $this->addHTML('<li class="first"><a class="' . $paginationBtnClass . '" href="'.$pgnFirstLink.'" data-page="1" title="First Page">' . $this->pgnFirst . '</a></li>');
            }
            if ($this->currentPage != 1) {
                $prevPageLinkArray = $this->linkArray;
                $prevPageLinkArray['pageNumber'] = $prevPage;
                $prevPageLink = GI_URLUtils::buildURL($prevPageLinkArray);
                $this->addHTML('<li class="prev"><a class="' . $paginationBtnClass . '" href="'.$prevPageLink.'" data-page="' . $prevPage . '" title="Previous Page">' . $this->pgnPrev . '</a></li>');
            }
            if ($this->showMore && $firstLink > 1) {
                $this->addHTML('<li class="more">' . $this->pgnMore . '</li>');
            }
            for ($i = $firstLink; $i <= $lastLink; $i++) {
                $linkClass = '';
                if ($i == $this->currentPage) {
                    $linkClass = 'current';
                }
                $currentPageLinkArray = $this->linkArray;
                $currentPageLinkArray['pageNumber'] = $i;
                $currentPageLink = GI_URLUtils::buildURL($currentPageLinkArray);
                $this->addHTML('<li class="' . $linkClass . '"><a class="' . $paginationBtnClass . '" href="'.$currentPageLink.'" data-page="' . $i . '" title="Jump to Page ' . $i . '">' . $i . '</a></li>');
            }
            if ($this->showMore && $lastLink < $pageCount) {
                $this->addHTML('<li class="more">' . $this->pgnMore . '</li>');
            }
            if ($this->currentPage != $pageCount) {
                $nextPageLinkArray = $this->linkArray;
                $nextPageLinkArray['pageNumber'] = $nextPage;
                $nextPageLink = GI_URLUtils::buildURL($nextPageLinkArray);
                $this->addHTML('<li class="next"><a class="' . $paginationBtnClass . '" href="'.$nextPageLink.'" data-page="' . $nextPage . '" title="Next Page">' . $this->pgnNext . '</a></li>');
            }
            if ($this->currentPage != $pageCount) {
                $lastPageLinkArray = $this->linkArray;
                $lastPageLinkArray['pageNumber'] = $pageCount;
                $lastPageLink = GI_URLUtils::buildURL($lastPageLinkArray);
                $this->addHTML('<li class="last"><a class="' . $paginationBtnClass . '" href="'.$lastPageLink.'" data-page="' . $pageCount . '" title="Last Page">' . $this->pgnLast . '</a></li>');
            }
            $this->addHTML('</ul>');
        }
        if($this->rightHTML){
            $this->addHTML('<div class="right_side">' . $this->rightHTML . '</div>');
        }
        $this->addHTML('</div>');
    }
    
    /**
     * @return \LoadMoreBtn
     */
    public function getLoadMoreBtn(){
        $loadMoreBtn = new LoadMoreBtn($this->linkArray, $this->itemsPerPage, $this->totalCount, $this->currentPage);
        return $loadMoreBtn;
    }
    
    /**
     * @return \LoadPrevBtn
     */
    public function getLoadPrevBtn(){
        $loadPrevBtn = new LoadPrevBtn($this->linkArray, $this->itemsPerPage, $this->totalCount, $this->currentPage);
        return $loadPrevBtn;
    }
    
    public function beforeReturningView() {
        $this->buildBar();
    }
    
}
