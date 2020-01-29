<?php
/**
 * Description of AbstractEventService
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractEventService extends GI_Service {
    
    protected static $eventsToProcess = array();
    
    public static function addEvent(AbstractEvent $event) {
        static::$eventsToProcess[] = $event;
    }
    
    public static function processEvents() {
        
        if (!empty(static::$eventsToProcess)) {
            foreach (static::$eventsToProcess as $key => $event) {
                if ($event->process()) {
                    unset(static::$eventsToProcess[$key]);
                }
            }
        }
        
        return true;
    }
}

