<?php
/**
 * Description of AbstractContentFileColGalleryDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileColGalleryDetailView extends AbstractContentFileColDetailView {
    
    protected $width = 200;
    protected $height = 200;
    
    public function setDimensions($width, $height){
        $this->width = $width;
        $this->height = $height;
    }
    
    protected function addFileViews(){
        $folder = $this->content->getFolder(false);
        if($folder){
            $this->addHTML('<div class="content_files content_gallery">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $fileView = $file->getSizedView($this->width, $this->height);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
        }
    }
    
}
