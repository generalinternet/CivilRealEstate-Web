<?php
/**
 * Description of AbstractContentRef
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentRef extends AbstractContent{
    
    /** @var AbstractContent */
    protected $referencedContent = NULL;
    
    /**
     * @return \ContentRefDetailView
     */
    public function getView() {
        $contentView = new ContentRefDetailView($this);
        return $contentView;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentRefFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentRefFormView($form, $this, $buildForm);
        return $contentFormView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if(parent::handleFormSubmission($form)){
            $contentId = filter_input(INPUT_POST, $this->getFieldName('ref_content_id'));
            $this->setProperty('content_ref.ref_content_id', $contentId);
            
            if($this->save()){
                return true;
            }
        }
        return false;
    }
    
    /** @return AbstractContent */
    public function getReferencedContent(){
        if(is_null($this->referencedContent)){
            $this->referencedContent = ContentFactory::getModelById($this->getProperty('content_ref.ref_content_id'));
        }
        return $this->referencedContent;
    }
    
}
