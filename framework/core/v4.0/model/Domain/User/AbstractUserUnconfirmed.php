<?php
/**
 * Description of AbstractUserUnconfirmed
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserUnconfirmed extends AbstractUser {
    
    public function isUnconfirmed() {
        return true;
    }
    
    
}
