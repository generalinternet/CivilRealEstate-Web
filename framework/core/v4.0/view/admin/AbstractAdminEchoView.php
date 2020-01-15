<?php
/**
 * Description of AbstractAdminEchoView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractAdminEchoView extends MainWindowView {

    protected $echoedContent = '';
    protected $footHTML = '';
    
    public function __construct() {
        parent::__construct();
        $this->addSiteTitle('Logs');
        $this->setWindowTitle('Logs');
    }
    
    protected function addViewBodyContent() {
        $this->addHTML('<pre>')
            ->addHTML($this->echoedContent)
            ->addHTML('</pre>')
            ->addHTML($this->footHTML);
    }
    
    public function varDumpThis($content){
        ob_start();
        var_dump($content);
        $result = ob_get_clean();
        $this->echoedContent .= $result . "\n\n";
        return $this;
    }
    
    public function printThis($content){
        $this->echoedContent .= print_r($content, true);
        $this->echoedContent .= "\n";
        return $this;
    }
    
    public function tab(){
        $this->echoedContent .= "\t";
        return $this;
    }
    
    public function echoThis($content = ''){
        $this->echoedContent .= $content . "\n";
        return $this;
    }
    
    public function addString($string){
        $this->echoedContent .= $string;
        return $this;
    }
    
    public function addFootHTML($html){
        $this->footHTML .= $html;
        return $this;
    }
    
    public function addHTMLOverride($html){
        return $this->addHTML($html);
    }
    
}
