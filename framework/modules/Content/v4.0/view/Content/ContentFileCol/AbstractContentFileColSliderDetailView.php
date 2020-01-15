<?php
/**
 * Description of AbstractContentFileColSliderDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileColSliderDetailView extends AbstractContentFileColDetailView {
    
    protected $thumbnailWidth = 100;
    protected $thumbnailHeight = 100;
    protected $width = 640;
    protected $height = 360;
    
    public function setThumbnailDimensions($width, $height){
        $this->thumbnailWidth = $width;
        $this->thumbnailHeight = $height;
    }
    
    public function setDimensions($width, $height){
        $this->width = $width;
        $this->height = $height;
    }
    
    protected function addFileViews(){
        $folder = $this->content->getFolder(false);
        if($folder){
            $files = $folder->getFiles();
            
            $this->addHTML('<div class="content_slider_wrap">');
            
            $this->addHTML('<div class="content_slider">'); 
                $fileArrayValues = array_values($files);
                $firstFile = array_shift($fileArrayValues);
                $firstFileView = $firstFile->getSizedView($this->width, $this->height);
                $this->addHTML($firstFileView->getHTMLView());
            $this->addHTML('</div>');
            
            $this->addHTML('<div class="content_slider_nav">');
                foreach($files as $file){
                    $fileURL = $file->getResizedImage($this->width, $this->height);
                    $fileThumbnailView = $file->getSizedView($this->thumbnailWidth, $this->thumbnailHeight);
                    $this->addHTML('<a href="' . $fileURL . '" target="_blank" class="content_slider_change_slide">' . $fileThumbnailView->getHTMLView() . '</a>');
                }
            $this->addHTML('</div>');
            
            $this->addHTML('</div>');
        }
    }
    
}
