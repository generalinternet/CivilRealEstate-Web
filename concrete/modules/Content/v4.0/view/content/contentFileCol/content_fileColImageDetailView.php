<?php

class ContentFileColImageDetailView extends AbstractContentFileColImageDetailView{
    protected function addContentTitle(){
        $title = $this->content->getTitle();
        if(empty($title)){
            return;
        }
        $refString = '';
        $titleTag = DEFAULT_CONTENT_BLOCK_TITLE_TAG;
        $this->addHTML('<hr>');
        $this->addHTML('<' . $titleTag . ' class="content_block_title"><span class="inline_block">' . $title . '</span>' . $refString . '</' . $titleTag . '>');
    }
    protected function addFileViews(){
        $folder = $this->content->getFolder(false);
        if($folder){
            $this->addHTML('<div class="content_block">');
            $this->addHTML('<div class="content_files content_gallery">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $fileView = $file->getSizedView($this->width, $this->height);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
            $this->addHTML('</div>');
        }
    }

    public function getPublicViewHTML(){
        $html = '';
        $html .= '<div class="row investment__detail-info-row row investment__detail-info-row_type_image-gallery">';

        $folder = $this->content->getFolder(false);
        if($folder){
            $html .= '<div class="col-xs-12">';
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
                $html .= '<div class="post_image" '.$imageAlignText.'>';
                $html .= $fileView->getHTMLView();
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
