<?php
/**
 * Description of AbstractFileSignature
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractFileSignature extends AbstractFile {
    
    public function getPrintedName(){
        return $this->getProperty('print_name');
    }
    
}
