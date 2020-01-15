<?php
/**
 * Description of GI_Model
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContentFileColImageDetailView extends AbstractContentDetailView{
    
    /**
     * No content title
     */
    protected function addContentTitle(){
    }
    
    /**
     * Shows image according to size and align
     */
    protected function addFileViews(){
        $folder = $this->content->getFolder(false);
        if($folder){
            $this->addHTML('<div class="content_files content_filecol_image">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $imageSize = $this->content->getProperty('content_file_col.image_size');
                if (!empty($imageSize)) {
                    $sizeArray = $this->content->getImageSizeArray($imageSize);
                    $fileView = $file->getSizedViewKeepRatio($sizeArray[0], $sizeArray[1]);
                } else {
                    $fileView = $file->getSizedView();
                }
                $imageAlign = $this->content->getProperty('content_file_col.image_align');
                $imageAlignText = '';
                if (!empty($imageAlign)) {
                    $imageAlignText = 'style="text-align:'.$imageAlign.'"';
                }
                $this->addHTML('<div class="post_image" '.$imageAlignText.'>');
                $this->addHTML($fileView->getHTMLView());
                $this->addHTML('</div>');
            }
            $this->addHTML('</div>');
        }
    }
}
