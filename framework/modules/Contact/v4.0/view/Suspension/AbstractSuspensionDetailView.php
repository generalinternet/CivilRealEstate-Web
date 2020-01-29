<?php
/**
 * Description of AbstractSuspensionDetailView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */

abstract class AbstractSuspensionDetailView extends MainWindowView {
    
    protected $suspension;
    
    public function __construct(AbstractSuspension $suspension) {
        parent::__construct();
        $this->suspension = $suspension;
    }
    
    protected function addViewBodyContent() {
       
    }

}