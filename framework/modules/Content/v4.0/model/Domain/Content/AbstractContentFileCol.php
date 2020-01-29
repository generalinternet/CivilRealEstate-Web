<?php
/**
 * Description of AbstractContentFileCol
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileCol extends AbstractContent{
    
    /** Image size **/
    public static $OPTIONS_SIZE = array(
        'large' => 'Large Size (800px)',
        'medium' => 'Medium Size (400px)',
        'small' => 'Small Size (200px)',
        'square' => 'Square (400 x 400)',
        'thumbnail' => 'Thumbnail (88 x 70)',
    );
    
    protected $SIZE_MAP = array(
        'large' => array(800, 0),
        'medium' => array(400, 0),
        'small' =>  array(200, 0),
        'square' => array(400, 400),
        'thumbnail' => array(88, 70),
    );
    
    /** Image align **/
    public static $OPTIONS_ALIGN = array(
        'left' => 'Left',
        'center' => 'Centre',
        'right' => 'Right',
    );
    
    /**
     * @return \ContentPageDetailView
     */
    public function getView() {
        $contentView = new ContentFileColDetailView($this);
        return $contentView;
    }
    
    /**
     * @param \GI_Form $form
     * @param boolean $buildForm
     * @return \ContentFileColFormView
     */
    public function getFormView(\GI_Form $form, $buildForm = true) {
        $contentFormView = new ContentFileColFormView($form, $this, false);
        $uploader = $this->getUploader($form);
        $contentFormView->setUploader($uploader);
        if($buildForm){
            $contentFormView->buildForm();
        }
        return $contentFormView;
    }
    
    public function handleFormSubmission(\GI_Form $form) {
        if(parent::handleFormSubmission($form)){
            $content = filter_input(INPUT_POST, $this->getFieldName('content'));
            $this->setProperty('content_file_col.content', $content);
            
            $imageSize = filter_input(INPUT_POST, $this->getFieldName('image_size'));
            $this->setProperty('content_file_col.image_size', $imageSize);
            
            $imageAign = filter_input(INPUT_POST, $this->getFieldName('image_align'));
            $this->setProperty('content_file_col.image_align', $imageAign);
            
            if($this->save()){
                return true;
            }
        }
        return false;
    }
    
    public function getImageSizeArray($image_size) {
        return $this->SIZE_MAP[$image_size];
    }
}
