<?php

class ContentTextDetailView extends AbstractContentTextDetailView{
    
    protected function addContentTitle(){
        $this->addHTML('<hr>');
        $title = $this->content->getTitle();
        if(empty($title)){
            return NULL;
        }
        $refString = '';
        $titleTag = DEFAULT_CONTENT_BLOCK_TITLE_TAG;
        $this->addHTML('<' . $titleTag . ' class="content_block_title"><span class="inline_block">' . $title . '</span>' . $refString . '</' . $titleTag . '>');
    }
    protected function buildViewGuts() {
        $content = $this->content->getContent(true);
        if(!empty($content)){
            $this->addHTML('<div class="content_block">');
            $this->addHTML('<p>' . $content . '</p>');
            $this->addHTML('</div>');
        }
    }

    public function getPublicViewHTML(){
        $title = $this->content->getTitle();
        $content = $this->content->getContent(true);
        $html = '';
 
        if(empty($content) && empty($title)){
            return $html;
        }

        $html .= '<div class="row investment__detail-info-row row investment__detail-info-row_type_text">';
        if(!empty($title)){
            $html .= '<div class="col-xs-12"><h3 class="investment__general-info-title">'.$title.'</h3></div>';
        }
        $html .= '<div class="col-xs-12">';
        $html .= '<p class="investment__general-info-item-summary no-margin-bottom">'.$content.'</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
