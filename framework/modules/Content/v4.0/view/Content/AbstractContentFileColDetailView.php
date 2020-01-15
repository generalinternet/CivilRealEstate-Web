<?php
/**
 * Description of AbstractContentFileColDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentFileColDetailView extends AbstractContentDetailView {
    
    protected function buildViewGuts() {
        $content = $this->content->getProperty('content_file_col.content');
        if(!empty($content)){
            $this->addHTML('<p>' . GI_StringUtils::nl2brHTML($content) . '</p>');
        }
        parent::buildViewGuts();
    }
    
}
