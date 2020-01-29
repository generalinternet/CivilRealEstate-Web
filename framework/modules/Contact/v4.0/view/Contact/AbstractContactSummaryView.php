<?php
/**
 * Description of AbstractContactSummaryView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactSummaryView extends GI_View {
    
    /** @var Contact */
    protected $contact;
    protected $controllerName;
    protected $relationship;
    
    public function __construct(AbstractContact $contact, AbstractContactRelationship $relationship = NULL) {
        parent::__construct();
        $this->contact = $contact;
        $this->relationship = $relationship;
    }
    
    protected function openViewWrap(){
        $viewURL = $this->contact->getViewURL();
        $name = $this->contact->getName();
        $this->addHTML('<a href="' . $viewURL . '" class="contact_summary ajax_link" title="View ' . $name . '">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</a>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $name = $this->contact->getName();
            $this->addHTML($name);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
        parent::beforeReturningView();
    }
    
}
