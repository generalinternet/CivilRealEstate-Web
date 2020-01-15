<?php
/**
 * Description of AbstractContentTextDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentTextDetailView extends AbstractContentDetailView {
    
    /** @var AbstractContentText */
    protected $content = NULL;
    
    protected function buildViewGuts() {
        $content = $this->content->getContent(true);
        if(!empty($content)){
            $this->addHTML('<p>' . $content . '</p>');
        }
        parent::buildViewGuts();
    }
    
}
