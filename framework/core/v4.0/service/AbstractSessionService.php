<?php

/**
 * Description of AbstractSessionService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */

abstract class AbstractSessionService extends GI_Service {
    
    public static function getValue($keys) {
        session_reset();
        $session =& $_SESSION;
        return static::getValueFromSession($keys, $session);
    }

    protected static function getValueFromSession($props, $session = array()) {
        if (!is_array($props)) {
            $props = array($props);
        }
        if (count($props) == 1) {
            $prop = implode('', $props);
            if (isset($session[$prop])) {
                return $session[$prop];
            }
            return NULL;
        }
        $prop = array_shift($props);
        if (!isset($session[$prop])) {
            return NULL;
        }
        return static::getValueFromSession($props, $session[$prop]);
    }

    public static function setValue($keys, $value) {
        session_reset();
        $session =& $_SESSION;
        $result = static::setValueInSession($keys, $value, $session);
        if (ProjectConfig::getUseAdvancedSessionFunctions()) {
            Session::setSaveAllowed(true);
            Session::save();
        }
        return $result;
    }

    protected static function setValueInSession($props, $val, &$session = array()) {
        if (!is_array($props)) {
            $props = array($props);
        }
        if (count($props) == 1) {
            $prop = implode('', $props);
            $session[$prop] = $val;
            return true;
        }
        $prop = array_shift($props);
        if (!isset($session[$prop])) {
            $session[$prop] = array();
        }
        return static::setValueInSession($props, $val, $session[$prop]);
    }

    public static function unsetValue($keys) {
        session_reset();
        $session =& $_SESSION;
        $result = static::unsetValueFromSession($keys, $session);
        if (ProjectConfig::getUseAdvancedSessionFunctions()) {
            Session::setSaveAllowed(true);
            Session::save();
        }
        return $result;
    }

    protected static function unsetValueFromSession($props, &$session = array()) {
        if (!is_array($props)) {
            $props = array($props);
        }
        if (count($props) == 1) {
            $prop = implode('', $props);
            unset($session[$prop]);
            if (!isset($session[$prop])) {
                return true;
            } 
            return false;
        }
        $prop = array_shift($props);
        if (!isset($session[$prop])) {
            $session[$prop] = array();
        }
        return static::unsetValueFromSession($props, $session[$prop]);
    }

}
