<?php
/**
 * Description of AbstractAdminIndexView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
abstract class AbstractAdminIndexView extends GI_View {
    
    public function __construct() {
        parent::__construct();
        $this->buildView();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML('<h2>Admin</h2>');
        $this->addHTML('<div class="gear_wrap">')
                ->addHTML('<div class="gears"></div>')
                ->addHTML('<p>Admin is in development.</p>')
                ->addHTML('</div>');
        $this->closeViewWrap();
    }
    
}
