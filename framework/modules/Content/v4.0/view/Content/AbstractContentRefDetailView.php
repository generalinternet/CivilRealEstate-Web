<?php
/**
 * Description of AbstractContentRefDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentRefDetailView extends AbstractContentDetailView {
    
    /** @var AbstractContentRef  */
    protected $content;
    
    public function buildView() {
        $refContent = $this->content->getReferencedContent();
        if($refContent){
            $refView = $refContent->getView();
            $refView->setReferenceOnly(true);
            $refView->setDisplayAsChild($this->displayAsChild);
            $this->addHTML($refView->getHTMLView());
        }
        $this->viewBuilt = true;
    }
    
}
