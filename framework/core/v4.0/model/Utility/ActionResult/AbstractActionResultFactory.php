<?php
/**
 * Description of AbstractActionResultFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractActionResultFactory {
    
    /**
     * @param string $type
     * @return AbstractActionResult
     */
    public static function buildActionResult($type = ''){
        $actionResult = new ActionResult();
        return $actionResult;
    }
    
}
