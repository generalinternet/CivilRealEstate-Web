<?php
/**
 * Description of AbstractGI_ICSFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractGI_ICSFactory {
    
    /**
     * @return AbstractGI_ICS
     */
    public static function buildICS($fileName = NULL){
        $ics = new GI_ICS($fileName);
        return $ics;
    }
    
}
