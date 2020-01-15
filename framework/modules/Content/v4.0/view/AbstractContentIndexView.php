<?php
/**
 * Description of AbstractContentIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentIndexView extends ListWindowView {
    
    /** @var AbstractContent[] */
    protected $models = array();
    /** @var AbstractContent */
    protected $sampleModel = NULL;

    public function __construct($models, AbstractUITableView $uiTableView, AbstractContent $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Content');
        $typeTitle = $this->sampleModel->getViewTitle();
        if(!empty($this->sampleModel->getTypeRef()) && $typeTitle != 'Content'){
            $this->addSiteTitle($typeTitle);
        }
        $this->setWindowTitle($this->sampleModel->getViewTitle());
        $this->setWindowIcon('content');
        $this->setListItemTitle($this->sampleModel->getViewTitle());
    }
    
    protected function addAddBtn(){
        if($this->sampleModel->isAddable()){
            $addTitle = $this->sampleModel->getViewTitle(false);
            $addURLArray = array(
                'controller' => 'content',
                'action' => 'add'
            );
            $typeRef = $this->sampleModel->getTypeRef();
            if(!empty($typeRef)){
                $addURLArray['type'] = $typeRef;
            }
            $addURL = GI_URLUtils::buildURL($addURLArray);
            $this->addHTML('<a href="' . $addURL . '" title="' . $addTitle . '" class="custom_btn" >' . GI_StringUtils::getIcon('add') . '<span class="btn_text">' . $addTitle . '</span></a>');
        }
    }
    
    protected function addTypeSelector() {
        $typeRefs = ContentFactory::getIndexableTypeRefs();
        if (!empty($typeRefs)) {
            $viewableCnt = 0;
            $linkHTML = '';
            foreach ($typeRefs as $typeRef => $typeTitle) {
                $content = ContentFactory::buildNewModel($typeRef);
                if ($content->isIndexViewable()) {
                    $viewableCnt++;
                    $indexURL = $content->getIndexURL();
                    $linkHTML .= '<a href="'.$indexURL.'" class="other_btn '.(($this->sampleModel->getTypeRef() == $typeRef)? ' current':'').'">'.$typeTitle.'</a>';
                }
            }
            
            if ($viewableCnt > 1) {
                $this->addHTML('<div class="top_selector">');
                    $this->addHTML($linkHTML);
                $this->addHTML('</div>');
            }
        }
    }
    
    protected function addWindowBtns() {
        $this->addAddBtn();
    }
    
}
