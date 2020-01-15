<?php
/**
 * Description of GI_Object
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.1
 */
abstract class GI_Object {

    public static function getStaticPropertyValueFromChild($propertyName) {
        if (property_exists(get_called_class(), $propertyName)) {
            $propertyValue = static::$$propertyName;
        } else {
            $propertyValue = ApplicationConfig::getProperty($propertyName);
        }
        return $propertyValue;
    }

}
