<?php
/**
 * Description of AbstractContentText
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentText extends AbstractContent{
    
    protected $formattedContent = '';
    
    /**
     * @return \ContentTextDetailView
     */
    public function getView() {
        $contentView = new ContentTextDetailView($this);
        return $contentView;
    }
    
    public function getViewTitle($plural = true) {
        $title = 'Text';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentTextFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentTextFormView($form, $this, $buildForm);
        return $contentFormView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if(parent::handleFormSubmission($form)){
            $content = filter_input(INPUT_POST, $this->getFieldName('content'));
            $this->setProperty('content_text.content', $content);
            
            if($this->save()){
                return true;
            }
        }
        return false;
    }
    
    /** Get a content value **/
    public function getContent($formatted = false) {
        $content = $this->getProperty('content_text.content');
        $finalContent = $content;
        if($formatted){
            if(empty($this->formattedContent)){
                $embedYoutube = GI_StringUtils::embedYouTube($content);
                $finalContent = GI_StringUtils::nl2brHTML($embedYoutube);
                $this->formattedContent = $finalContent;
            } else {
                return $this->formattedContent;
            }
        }
        return $finalContent;
    }
    
}
