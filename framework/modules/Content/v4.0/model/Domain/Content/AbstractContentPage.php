<?php
/**
 * Description of AbstractContentPage
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentPage extends AbstractContent{
    
    /**
     * @return \ContentPageDetailView
     */
    public function getView() {
        $contentView = new ContentPageDetailView($this);
        return $contentView;
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Page';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentPageFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentPageFormView($form, $this, false);
        $contentFormView->setShowRef(true);
        if($buildForm){
            $contentFormView->buildForm();
        }
        return $contentFormView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if(parent::handleFormSubmission($form)){
            
            if($this->save()){
                return true;
            }
        }
        return false;
    }
    
    public function getIsIndexViewable() {
        if(Permission::verifyByRef('view_content_page_index')){
            return true;
        }
        return false;
    }
    
}
