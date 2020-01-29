<?php
/**
 * Description of AbstractContactCatIndexView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactCatIndexView extends ListWindowView {
    
    /** @var AbstractContact[] */
    protected $models = array();
    /** @var AbstractContactCat */
    protected $sampleModel = NULL;
    protected $catType = 'client'; //Default catType

    public function __construct($models, AbstractUITableView $uiTableView, AbstractContactCat $sampleModel, GI_SearchView $searchView = NULL) {
        parent::__construct($models, $uiTableView, $sampleModel, $searchView);
        $this->addSiteTitle('Contacts');
        $typeTitle = $this->sampleModel->getTypeTitle();
        $typeRef = $this->sampleModel->getTypeRef();
        $windowIcon = 'contacts';
        if(!empty($typeRef) && $typeTitle != 'Category'){
            $this->addSiteTitle($typeTitle);
            $this->catType = $typeRef;
            $windowIcon = $typeRef;
        }
        $this->setWindowTitle($this->sampleModel->getViewTitle());
        $this->setWindowIcon($windowIcon);
        $this->setListItemTitle($this->sampleModel->getViewTitle());
        
        //Add class to the right btn in order to manage style for smaller screens
        if ($this->sampleModel->isAddable()) {
            $this->setViewHeaderClass('multiple_header_btns');
        }
    }
    
    public function setListTitle($listTitle){
        $this->setWindowTitle($listTitle);
        return $this;
    }
    
    protected function addAddIndBtn(){
        if ($this->sampleModel->isAddable()) {
            $addIndURL = GI_URLUtils::buildURL(array(
                'controller'=>'contact',
                'action'=>'add',
                'type'=>'ind',
                'catType'=>$this->catType,
            ));
            $this->addHTML('<a href="' . $addIndURL . '" title="Add Individuals" class="custom_btn">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Individual</span></a>');
        }
    }
    
    protected function addAddOrgBtn(){
        if ($this->sampleModel->isAddable()) {
            $addOrgURL = GI_URLUtils::buildURL(array(
                'controller'=>'contact',
                'action'=>'add',
                'type'=>'org',
                'catType'=>$this->catType,
            ));
            $this->addHTML('<a href="' . $addOrgURL . '" title="Add Organization" class="custom_btn">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Organization</span></a>');
        }

    }
    
    protected function addWindowBtns() {
        $this->addAddIndBtn();
        $this->addAddOrgBtn();
    }
    
    protected function addTypeSelector() {
        $typeRefs = ContactCatFactory::getTypesArray();
        if (isset($typeRefs['category'])) {
            unset($typeRefs['category']);
        }
        if (!empty($typeRefs)) {
            $viewableCnt = 0;
            $linkHTML = '';
            foreach ($typeRefs as $typeRef => $typeTitle) {
                $contactCat = ContactCatFactory::buildNewModel($typeRef);
                if ($contactCat->isIndexViewable()) {
                    $viewableCnt++;
                    $indexURL = GI_URLUtils::buildURL(array(
                        'controller'=>'contact',
                        'action'=>'catIndex',
                        'type'=>$typeRef,
                        'fullView'=> 1,
                    ));
                    $linkHTML .= '<a href="'.$indexURL.'" class="other_btn ajax_link '.(($this->sampleModel->getTypeRef() == $typeRef)? ' current':'').'" data-target-id="list_bar">'.$typeTitle.'</a>';
                }
            }
            
            if ($viewableCnt > 1) {
                $this->addHTML('<div class="top_selector">');
                    $this->addHTML($linkHTML);
                $this->addHTML('</div>');
            }
        }
    }
}
