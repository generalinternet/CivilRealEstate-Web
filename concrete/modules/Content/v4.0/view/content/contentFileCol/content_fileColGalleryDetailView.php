<?php

class ContentFileColGalleryDetailView extends AbstractContentFileColGalleryDetailView{
    protected function addContentTitle(){
        $title = $this->content->getTitle();
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
        $title = $this->content->getTitle();
        if(empty($title)){
            $title = "Photos";
        }
        $html = '';
        $html .= '<div class="row investment__detail-info-row row investment__detail-info-row_type_image-gallery">';
        $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">'.$title.'</h3></div>';

        $folder = $this->content->getFolder(false);
        if($folder){
            $html .= '<div class="col-xs-12">';
            $files = $folder->getFiles();
            foreach($files as $file){
                $imageURL = $file->getFileURL();
                $html .= '<img src="'.$imageURL.'" alt="'.SITE_TITLE.'" class="investment__general-info-item-image">';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
