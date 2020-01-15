<?php
/**
 * Description of AbstractGI_ICS
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractGI_ICS {
    
    protected $fileName = '';
    protected $ics = '';
    
    protected $eventName = NULL;
    protected $startTime = NULL;
    protected $endTime = NULL;
    protected $description = NULL;
    protected $organizer = NULL;
    protected $organizerEmail = NULL;
    protected $url = NULL;
    protected $location = NULL;
    
    protected $alarm1 = NULL;
    protected $alarm2 = NULL;
    
    public function __construct($fileName = NULL){
        $this->setFileName($fileName);
    }
    
    protected function addLine($line){
        $this->ics .= $line . "\r\n"; 
        return $this;
    }
    
    protected function addOrganizerLine(){
        $organizer = $this->getOrganizer();
        if(!empty($organizer)){
            $orgLine = 'ORGANIZER;CN=' . GI_Sanitize::escapeForICS($organizer);
            $organizerEmail = $this->getOrganizerEmail();
            if(!empty($organizerEmail)){
                $orgLine .= ':MAILTO:' . $organizerEmail;
            }
            $this->addLine($orgLine);
        }
        return $this;
    }
    
    protected function addAlarms(){
        $alarm1 = $this->getAlarm1();
        if(!empty($alarm1)){
            $this->addLine('BEGIN:VALARM')
                    ->addLine('TRIGGER:-PT' . $alarm1)
                    ->addLine('ACTION:DISPLAY')
                    ->addLine('DESCRIPTION:Reminder')
                    ->addLine('END:VALARM');
        }
        
        $alarm2 = $this->getAlarm2();
        if(!empty($alarm2)){
            $this->addLine('BEGIN:VALARM')
                    ->addLine('TRIGGER:-PT' . $alarm2)
                    ->addLine('ACTION:DISPLAY')
                    ->addLine('DESCRIPTION:Reminder')
                    ->addLine('END:VALARM');
        }
        return $this;
    }
    
    public function buildICS(){
        $dateObj = new DateTime();
        GI_Time::convertDateTimeToTimeZone($dateObj, 'GMT');
        $timeString = $dateObj->format('Ymd\THis\Z');
        
        $this->addLine('BEGIN:VCALENDAR')
                ->addLine('VERSION:2.0')
                ->addLine('PRODID:-//General Internet//NONSGML v1.0//EN')
                ->addLine('CALSCALE:GREGORIAN');
        
        //$this->addLine('METHOD:REQUEST'); //outlook req
        
        $startTimeObj = new DateTime($this->getStartTime());
        GI_Time::convertDateTimeToTimeZone($startTimeObj, 'GMT');
        $startTimeString = $startTimeObj->format('Ymd\THis\Z');
        
        $endTimeObj = new DateTime($this->getEndTime());
        GI_Time::convertDateTimeToTimeZone($endTimeObj, 'GMT');
        $endTimeString = $endTimeObj->format('Ymd\THis\Z');
        
        if($endTimeObj < $startTimeObj){
            $tempTimeString = $startTimeString;
            $startTimeString = $endTimeString;
            $endTimeString = $tempTimeString;
        }
        
        $this->addLine('BEGIN:VEVENT')
                ->addLine('DTSTART:' . $startTimeString)
                ->addLine('DTEND:' . $endTimeString)
                ->addLine('DTSTAMP:' . $timeString)
                ->addLine('UID:' . uniqid() . 'gi')
                ->addOrganizerLine()
                ->addLine('SUMMARY:' . GI_Sanitize::escapeForICS($this->getEventName()));
        
        $location = $this->getLocation();
        if($location){
            $this->addLine('LOCATION:' . GI_Sanitize::escapeForICS($location));
        }
        
        $description = $this->getDescription();
        if($description){
            $this->addLine('DESCRIPTION:' . GI_Sanitize::escapeForICS($description));
        }
        
        $url = $this->getURL();
        if($url){
            $this->addLine('URL:' . $url);
        }
                
        $this->addAlarms()
                ->addLine('END:VEVENT');
                
        $this->addLine('END:VCALENDAR');
    }
    
    public function downloadICS(){
        $ics = $this->getICS();
        
        $fileName = $this->getFilename(true);
        
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $fileName);
        echo $ics;
        die();
    }
    
    public function getICS(){
        if(empty($this->ics)){
            $this->buildICS();
        }
        
        return $this->ics;
    }
    
    public function setFileName($fileName) {
        $this->fileName = $fileName;
        return $this;
    }
    
    public function setEventName($eventName){
        $this->eventName = $eventName;
        return $this;
    }
    
    public function setStartTime($startTime){
        $this->startTime = $startTime;
        return $this;
    }
    
    public function setEndTime($endTime){
        $this->endTime = $endTime;
        return $this;
    }
    
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }
    
    public function setOrganizer($organizer){
        $this->organizer = $organizer;
        return $this;
    }
    
    public function setOrganizerEmail($organizerEmail){
        $this->organizerEmail = $organizerEmail;
        return $this;
    }
    
    public function setURL($url){
        $this->url = $url;
        return $this;
    }
    
    public function setAddr($addrStreet = NULL, $addrCity = NULL, $addrRegion = NULL, $addrCode = NULL, $addrCountry = NULL, $addrStreetTwo = NULL){
        $this->setLocation(GI_StringUtils::buildAddrString($addrStreet, $addrCity, $addrRegion, $addrCode, $addrCountry, false, $addrStreetTwo));
        return $this;
    }
    
    public function setLocation($location){
        $this->location = $location;
        return $this;
    }
    
    protected function validateAlarm($time){
        $timeBefore = strtoupper($time);
        switch($timeBefore){
            case '0M':
            case '5M':
            case '15M':
            case '30M':
            case '1H':
            case '2H':
            case '24H':
            case '48H':
            case '168H':
                return $timeBefore;
                break;
            case '1D':
                return '24H';
                break;
            case '2D':
                return '48H';
                break;
            case '7D':
            case '1W':
                return '168H';
                break;
            default: 
                return NULL;
                break;
        }
    }
    
    /**
     * Number of minutes/hours/days before to set alarm
     * 
     * @param string $alarm1 (only: 0M, 5M, 15M, 30M, 1H, 2H, 1D, 2D, 1W)
     * @return \AbstractGI_ICS
     */
    public function setAlarm1($alarm1){
        $this->alarm1 = $this->validateAlarm($alarm1);
        return $this;
    }
    
    /**
     * Number of minutes/hours/days before to set alarm
     * 
     * @param string $alarm2 (only: 0M, 5M, 15M, 30M, 1H, 2H, 1D, 2D, 1W)
     * @return \AbstractGI_ICS
     */
    public function setAlarm2($alarm2){
        $this->alarm2 = $this->validateAlarm($alarm2);
        return $this;
    }
    
    public function getFilename($withExt = false) {
        if(empty($this->fileName)){
            $this->fileName = $this->getEventName();
        }
        $this->fileName = GI_Sanitize::filename($this->fileName);
        if($withExt){
            return $this->fileName . '.ics';
        }
        return $this->fileName;
    }
    
    public function getEventName(){
        return $this->eventName;
    }
    
    public function getStartTime(){
        return $this->startTime;
    }
    
    public function getEndTime(){
        return $this->endTime;
    }
    
    public function getDescription(){
        return $this->description;
    }
    
    public function getOrganizer(){
        return $this->organizer;
    }
    
    public function getOrganizerEmail(){
        return $this->organizerEmail;
    }
    
    public function getURL(){
        return $this->url;
    }
    
    public function getLocation(){
        return $this->location;
    }
    
    public function getAlarm1(){
        return $this->alarm1;
    }
    
    public function getAlarm2(){
        return $this->alarm2;
    }
    
}
