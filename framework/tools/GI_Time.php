<?php
/**
 * Description of GI_Time
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.8
 */
class GI_Time{
    
    protected static $timezone = 'GMT';
    protected static $defaultDateFormat = 'M jS, Y';
    protected static $defaultTimeFormat = 'g:i a';
    protected static $defaultDateTimeFormat = 'M jS, Y g:i a';
    protected static $csvDateFormat = 'Y-m-d';
    protected static $csvTimeFormat = 'g:i a';
    protected static $csvDateTimeFormat = 'Y-m-d g:i a';
    protected static $standardDateFormat = 'Y-m-d';
    protected static $standardTimeFormat = 'H:i:s';
    protected static $standardDateTimeFormat = 'Y-m-d H:i:s';
    protected static $fieldDateFormat = 'Y-m-d';
    protected static $fieldTimeFormat = 'h:i a';
    protected static $fieldDateTimeFormat = 'Y-m-d h:i a';
    protected static $userTimezone = NULL;
    protected static $gmtTimezone = NULL;
    
    public static function setTimezone($timezone){
        date_default_timezone_set($timezone);
        static::$timezone = $timezone;
    }
    
    public static function getTimezone(){
        return static::$timezone;
    }
    
    public static function getTime(){
        $dateTime = new DateTime();
        return $dateTime->format(static::$standardTimeFormat);
    }
    
    public static function getDate() {
        $dateTime = new DateTime();
        return $dateTime->format(static::$standardDateFormat);
    }
    
    public static function getDateTime(){
        $dateTime = new DateTime();
        return $dateTime->format(static::$standardDateTimeFormat);
    }
    
    public static function getDisplayDateFormat(){
        if(GI_CSV::csvExporting()){
            return static::$csvDateFormat;
        } else {
            return static::$defaultDateFormat;
        }
    }
    
    public static function getDisplayTimeFormat(){
        if(GI_CSV::csvExporting()){
            return static::$csvTimeFormat;
        } else {
            return static::$defaultTimeFormat;
        }
    }
    
    public static function getDisplayDateTimeFormat(){
        if(GI_CSV::csvExporting()){
            return static::$csvDateTimeFormat;
        } else {
            return static::$defaultDateTimeFormat;
        }
    }
    
    public static function getGMTTimezone(DateTime $dateTimeObj = NULL){
        if(is_null(static::$gmtTimezone)){
            static::$gmtTimezone = new DateTimeZone('GMT');
        }
        
        if(!empty($dateTimeObj)){
            $dateTimeObj->setTimezone(static::$gmtTimezone);
        }
        return static::$gmtTimezone;
    }
    
    public static function getUserTimezone(DateTime $dateTimeObj = NULL){
        if(is_null(static::$userTimezone)){
            static::$userTimezone = new DateTimeZone(static::$timezone);
        }
        
        if(!empty($dateTimeObj)){
            $dateTimeObj->setTimezone(static::$userTimezone);
        }
        return static::$userTimezone;
    }
    
    /**
     * @param DateTime $dateTimeObj
     * @param string $timeZoneString
     * @return \DateTime
     */
    public static function convertDateTimeToTimeZone(DateTime &$dateTimeObj, $timeZoneString = 'GMT'){
        $timeZone = new DateTimeZone($timeZoneString);
        $dateTimeObj->setTimezone($timeZone);
        
        return $dateTimeObj;
    }
    
    /**
     * @param string $dateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $mode Options: (datetime, date, time)
     * @return string Formatted for DB insert
     */
    public static function formatToGMT($dateTime, $mode = 'datetime'){
        if(!empty($dateTime)){
            $dateTimeObj = new DateTime($dateTime);
            if($mode==='date'){
                $justDate = $dateTimeObj->format('Y-m-d');
                $justDateObj = new DateTime($justDate);
                $returnGMT = static::formatDateTime($justDateObj, $mode);
            } else {
                static::getGMTTimezone($dateTimeObj);
                $returnGMT = static::formatDateTime($dateTimeObj, $mode);
            }
        } else {
            return NULL;
        }
        return $returnGMT;
    }
    
    /**
     * @param string $dateTime DateTime in GMT format (ex. 1990-10-15 9:00 PM)
     * @param string $mode Options: (datetime, date, time)
     * @return string Formatted for user display
     */
    public static function formatToUserTime($dateTime, $mode = 'datetime'){
        if(!empty($dateTime)){
            if($mode==='date'){
                $dateTimeObj = new DateTime($dateTime);
                $returnUserTime = static::formatDateTime($dateTimeObj, $mode);
            } else {
                $dateTimeObj = new DateTime($dateTime, static::getGMTTimezone());
                static::getUserTimezone($dateTimeObj);
                $returnUserTime = static::formatDateTime($dateTimeObj, $mode);
            }
        } else {
            return NULL;
        }
        return $returnUserTime;
    }
    
    /**
     * @param type $time Timestamp
     * @param string $mode Options: (datetime, date, time)
     * @return string Formatted date or time depending on mode
     * @deprecated since version 2.0.0
     */
    public static function formatTime($time, $mode = 'datetime'){
        switch($mode){
            case 'datetime':
                $formattedDateTime = date(static::$standardDateTimeFormat,$time);
                break;
            case 'date':
                $formattedDateTime = date(static::$standardDateFormat,$time);
                break;
            case 'time':
                $formattedDateTime = date(static::$standardTimeFormat,$time);
                break;
        }
        return $formattedDateTime;
    }
    
    /**
     * @param DateTime $dateTimeObj
     * @param string $mode Options: (datetime, date, time)
     * @return string Formatted date or time depending on mode
     */
    public static function formatDateTime(DateTime $dateTimeObj, $mode = 'datetime'){
        switch($mode){
            case 'datetime':
                $format = static::$standardDateTimeFormat;
                break;
            case 'date':
                $format = static::$standardDateFormat;
                break;
            case 'time':
                $format = static::$standardTimeFormat;
                break;
        }
        $formattedDateTime = $dateTimeObj->format($format);
        return $formattedDateTime;
    }
    
    /**
     * @param DateInterval $dateInterval
     * @return integer
     */
    public static function getSeconds(DateInterval $dateInterval){
        $days = $dateInterval->days;
        $hours = ($days*24) + $dateInterval->h;
        $minutes = ($hours*60) + $dateInterval->i;
        $seconds = ($minutes*60) + $dateInterval->s;
        return $seconds;
    }
    
    /**
     * @param string $startDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $endDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @return integer number of seconds between two dates
     * @throws Exception if $dateTime is after $since
     */
    public static function getSecondsBetween($startDateTime, $endDateTime = ''){
        $startDateTimeObj = new DateTime($startDateTime);
        $endDateTimeObj = new DateTime($endDateTime);
        $dateInterval = $startDateTimeObj->diff($endDateTimeObj);
        $seconds = static::getSeconds($dateInterval);
        if($seconds < 0){
            throw new Exception('[@param $startDateTime] cannot be after [@param $endDateTime]');
        }
        return $seconds;
    }
    
    /**
     * @param string $sinceDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $upToDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @return int Timestamp for $dateTime - $since
     * @throws Exception if $dateTime is after $since
     * @deprecated since version 2.0.0
     */
    public static function getTimeSince($sinceDateTime, $upToDateTime = ''){
        if(empty($upToDateTime)){
            $upToDateTime = time();
        } else {
            $upToDateTime = strtotime($upToDateTime);
        }
        $timeSince = round($upToDateTime - strtotime($sinceDateTime));
        if($timeSince < 0 ){
            throw new Exception('[@param $dateTime] cannot be after [@param $since]');
        }
        return $timeSince;
    }
    
    /** 
     * @param string $sinceDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $upToDateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param int $decimals Number of decimal places to round to
     * @return string Time since $since (ex. "45 s", "3 m", "5 hrs")
     */
    public static function formatTimeSince($sinceDateTime, $upToDateTime = '', $decimals = 0, $dayCount = false){
        try{
            $secsAgo = static::getSecondsBetween($sinceDateTime, $upToDateTime);
            //static::getTimeSince($sinceDateTime, $upToDateTime);
        } catch (Exception $ex) {
            //@todo add Exception to log
            return;
        }
        
        if($secsAgo < 60){
            $timeSince = $secsAgo.' s';
        } elseif($secsAgo >=60 && $secsAgo < 3600){
            $minsAgo = round($secsAgo/60, $decimals);
            $timeSince = $minsAgo.' m';
        } elseif($secsAgo >= 3600 && $secsAgo < 86400){
            $hoursAgo = round($secsAgo/3600, $decimals);
            if($hoursAgo == 1){
                $hourText = 'hr';
            } else {
                $hourText = 'hrs';
            }
            $timeSince = $hoursAgo.' '.$hourText;
        } elseif($secsAgo >= 86400 && $secsAgo < 31536000){
            if($dayCount){
                $daysAgo = round($secsAgo/86400, $decimals);
                if($daysAgo == 1){
                    $dayText = 'day';
                } else {
                    $dayText = 'days';
                }
                $timeSince = $daysAgo.' '.$dayText;
            } else {
                $timeSince = date('M j', strtotime($sinceDateTime));
            }
        } else {
            if($dayCount){
                $yearsAgo = round($secsAgo/31536000, $decimals);
                if($yearsAgo == 1){
                    $yearText = 'year';
                } else {
                    $yearText = 'years';
                }
                $timeSince = $yearsAgo.' '.$yearText;
            } else {
                $timeSince = date('M Y', strtotime($sinceDateTime));
            }
        }
        
        return $timeSince;
    }
    
    /**
     * @param string $dateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $from DateTime (ex. 1990-10-15 9:00 PM)
     * @return int Timestamp for $dateTime - $from
     * @throws Exception if $dateTime is before $from
     * @deprecated since version 2.0.0
     */
    public static function getTimeUntil($dateTime, $from = ''){
        if(empty($from)){
            $from = time();
        } else {
            $from = strtotime($from);
        }
        $timeUntil = round(strtotime($dateTime) - $from);
        if($timeUntil < 0 ){
            throw new Exception('[@param $dateTime] cannot be before [@param $from]');
        }
        return $timeUntil;
    }
    
    /** 
     * @param string $dateTime DateTime (ex. 1990-10-15 9:00 PM)
     * @param string $from DateTime (ex. 1990-10-15 9:00 PM)
     * @param int $decimals Number of decimal places to round to
     * @return string Time since $since (ex. "45 s", "3 m", "5 hrs")
     */
    public static function formatTimeUntil($dateTime, $from = '', $decimals = 0, $dayCount = false){
        try{
            $secsUntil = static::getSecondsBetween($from, $dateTime);
            //$secsUntil = static::getTimeUntil($dateTime, $from);
        } catch (Exception $ex) {
            //@todo add Exception to log
            return;
        }
        
        if($secsUntil < 60){
            $timeUntil = $secsUntil.' s';
        } elseif($secsUntil >=60 && $secsUntil < 3600){
            $minsUntil = round($secsUntil/60, $decimals);
            $timeUntil = $minsUntil.' m';
        } elseif($secsUntil >= 3600 && $secsUntil < 86400){
            $hoursUntil = round($secsUntil/3600, $decimals);
            if($hoursUntil == 1){
                $hourText = 'hr';
            } else {
                $hourText = 'hrs';
            }
            $timeUntil = $hoursUntil.' '.$hourText;
        } elseif($secsUntil >= 86400 && $secsUntil < 31536000){
            $daysUntil = round($secsUntil/86400, $decimals);
            if($dayCount){
                if($daysUntil == 1){
                    $dayText = 'day';
                } else {
                    $dayText = 'days';
                }
                $timeUntil = $daysUntil.' '.$dayText;
            } else {
                $timeUntil = $daysUntil.' d';
            }
        } else {
            if($dayCount){
                $yearsUntil = round($secsUntil/31536000, $decimals);
                if($yearsUntil == 1){
                    $yearText = 'year';
                } else {
                    $yearText = 'years';
                }
                $timeUntil = $yearsUntil.' '.$yearText;
            } else {
                $timeUntil = date('M Y', strtotime($dateTime));
            }
        }
        return $timeUntil;
    }
    
    /**
     * @param string $startDate Date (ex. 1990-10-15)
     * @param string $endDate Date (ex. 1990-10-15)
     * @param string $dash The separator to be used between the dates
     * @return string A formatted string that shows from dateA - dateB
     */
    public static function formatFromDateToDate($startDate, $endDate = NULL, $dash = ' &mdash; '){
        if(empty($endDate) || $startDate == $endDate){
            return static::formatDateForDisplay($startDate);
        }
        $startDateTimeObj = new DateTime($startDate);
        $endDateTimeObj = new DateTime($endDate);
        
        $startMonth = $startDateTimeObj->format('M');
        $endMonth = $endDateTimeObj->format('M');
        $startYear = $startDateTimeObj->format('Y');
        $endYear = $endDateTimeObj->format('Y');
        $dateToDate = $startDateTimeObj->format('M jS');
        if($startMonth === $endMonth && $startYear === $endYear){
            $dateToDate .= $dash . $endDateTimeObj->format('jS');
            $dateToDate .= ', ' . $startYear;
        } elseif($startYear === $endYear){
            $dateToDate .= $dash . $endDateTimeObj->format('M jS');
            $dateToDate .= ', ' . $startYear;
        } else {
            $dateToDate .= ', ' . $startYear;
            $dateToDate .= $dash . static::formatDateForDisplay($endDate);
        }
        return $dateToDate;
    }
    
    public static function formatFromTimeToTime($startTime, $endTime = NULL, $dash = ' &dash; '){
        if(empty($endTime) || $startTime == $endTime){
            return static::formatTimeForDisplay($startTime);
        }
        
        $startDateTimeObj = new DateTime($startTime);
        $endDateTimeObj = new DateTime($endTime);
        
        $startTimeOfDay = $startDateTimeObj->format('a');
        $endTimeOfDay = $endDateTimeObj->format('a');
        $timeToTime = $startDateTimeObj->format('g:i');
        if($startTimeOfDay != $endTimeOfDay){
            $timeToTime .= ' ' . $startTimeOfDay;
        }
        $timeToTime .= $dash;
        $timeToTime .= $endDateTimeObj->format('g:i');
        $timeToTime .= ' ' . $endTimeOfDay;
        return $timeToTime;
    }
    
    public static function formatFromDateTimeToDateTime($startDateTime, $endDateTime = NULL, $dash = ' &mdash; '){
        if(empty($endDateTime) || $startDateTime == $endDateTime){
            return static::formatDateTimeForDisplay($startDateTime);
        }
        $startDateTimeObj = new DateTime($startDateTime);
        $endDateTimeObj = new DateTime($endDateTime);
        
        $startDate = $startDateTimeObj->format(static::$defaultDateFormat);
        $endDate = $endDateTimeObj->format(static::$defaultDateFormat);
        if ($startDate == $endDate) {
            $dateTimeToDateTime = static::formatDateForDisplay($startDateTime) . ' ' . static::formatFromTimeToTime($startDateTime, $endDateTime, $dash);
        } else {
            $dateTimeToDateTime = $startDateTimeObj->format(static::$defaultDateTimeFormat) . $dash . $endDateTimeObj->format(static::$defaultDateTimeFormat);
        }
        
        return $dateTimeToDateTime;
    }
    
    /**
     * @param DateTime|string $date Date (ex. 1990-10-15)
     * @param string $customFormat
     * @return string A formatted date string
     */
    public static function formatDateForDisplay($date, $customFormat = NULL){
        if(empty($date)){
            return NULL;
        }
        $dateObj = $date;
        if(!is_a($date, 'DateTime')){
            $dateObj = new DateTime($date);
        }
        if(empty($customFormat)){
            $format = static::getDisplayDateFormat();
        } else {
            $format = $customFormat;
        }
        $formattedDate = $dateObj->format($format);
        return $formattedDate;
    }
    
    /**
     * @param DateTime|string $time Date (ex. 9:00 PM)
     * @param string $customFormat
     * @return string A formatted time string
     */
    public static function formatTimeForDisplay($time, $customFormat = NULL){
        if(empty($time)){
            return NULL;
        }
        $timeObj = $time;
        if(!is_a($time, 'DateTime')){
            $timeObj = new DateTime($time);
        }
        if(empty($customFormat)){
            $format = static::getDisplayTimeFormat();
        } else {
            $format = $customFormat;
        }
        $formattedTime = $timeObj->format($format);
        return $formattedTime;
    }
    
    /**
     * @param DateTime|string $dateTime Date (ex. 1990-10-15 9:00 PM)
     * @param string $customFormat
     * @return string A formatted date time string
     */
    public static function formatDateTimeForDisplay($dateTime, $customFormat = NULL){
        if(empty($dateTime)){
            return NULL;
        }
        $dateTimeObj = $dateTime;
        if(!is_a($dateTime, 'DateTime')){
            try{
                $dateTimeObj = new DateTime($dateTime);
            } catch (Exception $ex) {
                return NULL;
            }
        }
        if(empty($customFormat)){
            $format = static::getDisplayDateTimeFormat();
        } else {
            $format = $customFormat;
        }
        $formattedDateTime = $dateTimeObj->format($format);
        return $formattedDateTime;
    }
    
    /**
     * @param string $date Date (ex. 1990-10-15)
     * @return string A formatted date string for a field
     */
    public static function formatDateForField($date = ''){
        if(empty($date)){
            return NULL;
        }
        $dateObj = new DateTime($date);
        return $dateObj->format(static::$fieldDateFormat);
    }
    
    /**
     * @param string $time Date (ex. 9:00 PM)
     * @return string A formatted time string for a field
     */
    public static function formatTimeForField($time = ''){
        if(empty($time)){
            return NULL;
        }
        $timeObj = new DateTime($time);
        return $timeObj->format(static::$fieldTimeFormat);
    }
    
    /**
     * @param string $dateTime Date (ex. 9:00 PM)
     * @return string A formatted date time string for a field
     */
    public static function formatDateTimeForField($dateTime = ''){
        if(empty($dateTime)){
            return NULL;
        }
        $dateTimeObj = new DateTime($dateTime);
        return $dateTimeObj->format(static::$fieldDateTimeFormat);
    }

    /**
     * @param DateTime $dateTime
     * @return DateTime[]
     */
    public static function getFiscalYearStartAndEndDates(DateTime $dateTime = NULL) {
        if (empty($dateTime)) {
            $dateTime = new DateTime();
        }
        $fiscalYearStart = new DateTime($dateTime->format('Y') . '-' . DEFAULT_FISCAL_YEAR_START . ' 00:00:00');
        $startDate = new DateTime($fiscalYearStart->format(static::$standardDateTimeFormat));
        if ($dateTime < $fiscalYearStart) {
            $startDate->modify("-1 year");
        }
        $endDate = new DateTime($startDate->format(static::$standardDateTimeFormat));
        $endDate->modify('+1 year');
        $endDate->modify('-1 second');
        return array(
            'start' => $startDate,
            'end' => $endDate,
        );
    }
    
    /**
     * @param String $month numeric representation of a month. I.e. '04' for April
     * @param String $day numeric representation of a day. I.e. '09' for the ninth
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return \DateTime represents the M-D-Y between $startDate and $endDate, null otherwise.
     */
    public static function getCompleteDateInRange($month, $day, DateTime $startDate, DateTime $endDate) {
        if ($startDate > $endDate) {
            return NULL;
        }
        $partialDateString = '-'.$month.'-'.$day . ' 00:00:00';
        $dateTime1 = new DateTime($startDate->format('Y') . $partialDateString);
        if ($dateTime1 >= $startDate && $dateTime1 <= $endDate) {
            return $dateTime1;
        }
        $dateTime2 = new DateTime($endDate->format('Y') . $partialDateString);
        if ($dateTime2 >= $startDate && $dateTime2 <= $endDate) {
            return $dateTime2;
        }
        return NULL;
    }

    /**
     * @param DateTime $start
     * @return string[]
     */
    public static function getFiscalYearOptionsArray(DateTime $start) {
        $currentDate = new DateTime();
        $options = array();
        $fiscalYearStartAndEnd = static::getFiscalYearStartAndEndDates($start);
        $fiscalStart = $fiscalYearStartAndEnd['start'];
        $fiscalEnd = $fiscalYearStartAndEnd['end'];
        while ($fiscalStart < $currentDate) {
            $startString = $fiscalStart->format('Y');
            $endString = $fiscalEnd->format('Y');
            if ($startString == $endString) {
                $value = $startString;
            } else {
                $value = $startString . '/' . $endString;
            }
            $options[$startString . '_' . $endString] = $value;
            
            $fiscalStart->modify("+1 years");
            $fiscalEnd->modify("+1 years");
        }
        return $options;
    }
    
    /**
     * @deprecated - To be removed in version 4
     * @param type $date
     * @return type
     */
    public static function getFiscalYearStartAndEndDatesOld($date = NULL) {
        if (empty($date)) {
             $date = date('Y-m-d');
             $dateYear = date('Y');
        } else {
            $dateArray = explode('-', $date);
            $dateYear = $dateArray[0];
        }
        $fiscalStartThisYearString = $dateYear . '-' . DEFAULT_FISCAL_YEAR_START;
        $fiscalEndObject = new DateTime($fiscalStartThisYearString);
        $fiscalEndObject->sub(new DateInterval('P1D'));
        $fiscalStartObject = new DateTime($fiscalStartThisYearString);
        $todayObject = new DateTime($date);
        $todayObject->add(new DateInterval('P1D'));
        if ($todayObject > $fiscalStartObject) {
            $startOfYear = $fiscalStartObject->format('Y-m-d');
            $fiscalEndObject->add(new DateInterval('P1Y'));
            $fiscalEndNextYear = $fiscalEndObject->format('Y-m-d');
            $endOfYear = $fiscalEndNextYear;
        } else {
            $fiscalStartObject->sub(new DateInterval('P1Y'));
            $fiscalStartLastYearString = $fiscalStartObject->format('Y-m-d');
            $startOfYear = $fiscalStartLastYearString;
            $fiscalEndThisYear = $fiscalEndObject->format('Y-m-d');
            $endOfYear = $fiscalEndThisYear;
        }
        return array(
            'start' => $startOfYear,
            'end' => $endOfYear
        );
    }
    
    public static function isDateInRange($date = NULL, $startDate = NULL, $endDate = NULL){
        $today = static::getDate();
        if(empty($date)){
            $date = $today;
        }
        if(empty($startDate)){
            $startDate = $today;
        }
        if(empty($endDate)){
            $endDate = $today;
        }
        if($startDate == $endDate && $startDate == $date){
            return true;
        }
        
        $needleDateTimeObj = new DateTime($date);
        $startDateTimeObj = new DateTime($startDate);
        $endDateTimeObj = new DateTime($endDate);
        
        if($needleDateTimeObj >= $startDateTimeObj && $needleDateTimeObj <= $endDateTimeObj){
            return true;
        }
        return false;
    }
    
    public static function getReportingPeriodOptions(DateTime $fiscalYearStart, DateTime $fiscalYearEnd, $includeDates = false) {
        $reportingPeriodOptions = array();
        if ($includeDates) {
        $yearStart = $fiscalYearStart->format('Y');
        $yearEnd = $fiscalYearEnd->format('Y');
        if ($yearStart == $yearEnd) {
            $yearString = $yearStart;
        } else {
            $yearString = $yearStart . '/' . $yearEnd;
        }
        } else {
            $yearString = 'Annual';
        }
        $reportingPeriodOptions['annual'] = $yearString;
        $reportingPeriodOptions['q1'] = 'Q1';
        $reportingPeriodOptions['q2'] = 'Q2';
        $reportingPeriodOptions['q3'] = 'Q3';
        $reportingPeriodOptions['q4'] = 'Q4';
        while ($fiscalYearStart < $fiscalYearEnd) {
            $key = $fiscalYearStart->format('m');
            if ($includeDates) {
                $monthString = $fiscalYearStart->format('F Y');
            } else {
                $monthString = $fiscalYearStart->format('F');
            }
            $reportingPeriodOptions[$key] = $monthString;
            
            $fiscalYearStart->modify("+1 month");
        }
        
        return $reportingPeriodOptions;
    }
    
    public static function getDatesByFiscalYearAndReportingPeriod(DateTime $fiscalYearStart, DateTime $fiscalYearEnd, $reportingPeriodKey) {
        $dates = array();
        switch ($reportingPeriodKey) {
            case 'annual':
                $dates['start'] = $fiscalYearStart->format('Y-m-d');
                $dates['end'] = $fiscalYearEnd->format('Y-m-d');
                break;
            case 'q1':
                $dates['start'] = $fiscalYearStart->format('Y-m-d');
                $fiscalYearStart->modify("+3 months");
                $fiscalYearStart->modify("-1 day");
                $dates['end'] = $fiscalYearStart->format('Y-m-d');
                break;
            case 'q2':
                $fiscalYearStart->modify("+3 months");
                $dates['start'] = $fiscalYearStart->format('Y-m-d');
                $fiscalYearStart->modify("+3 months");
                $fiscalYearStart->modify("-1 day");
                $dates['end'] = $fiscalYearStart->format('Y-m-d');
                break;
            case 'q3':
                $fiscalYearStart->modify("+6 months");
                $dates['start'] = $fiscalYearStart->format('Y-m-d');
                $fiscalYearStart->modify("+3 months");
                $fiscalYearStart->modify("-1 day");
                $dates['end'] = $fiscalYearStart->format('Y-m-d');
                break;
            case 'q4':
                $fiscalYearStart->modify("+9 months");
                $dates['start'] = $fiscalYearStart->format('Y-m-d');
                $fiscalYearStart->modify("+3 months");
                $fiscalYearStart->modify("-1 day");
                $dates['end'] = $fiscalYearStart->format('Y-m-d');
                break;
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '06':
            case '07':
            case '08':
            case '09':
            case '10':
            case '11':
            case '12':
                $monthDateTime = new DateTime($fiscalYearStart->format('Y') . '-' . $reportingPeriodKey . '-01');
                if ($monthDateTime < $fiscalYearStart) {
                    $monthDateTime->modify("+1 year");
                }
                $dates['start'] = $monthDateTime->format('Y-m-d');
                $monthDateTime->modify("+1 month");
                $monthDateTime->modify("-1 day");
                $dates['end'] = $monthDateTime->format('Y-m-d');
                break;
        }
        return $dates;
    }

    public static function getFirstDateOfMonth($date = NULL, $format = 'Y-m-01') {
        if (empty($date)) {
            $dateTime = new DateTime();
        } else {
            $dateTime = new DateTime($date);
}
        
        return $dateTime->format($format);
    }
    
    public static function getLastDateOfMonth($date = NULL, $format = 'Y-m-t') {
        if (empty($date)) {
            $dateTime = new DateTime();
        } else {
            $dateTime = new DateTime($date);
        }
        return $dateTime->format($format);
    }
    
    /**
     * @param DateTime $startDateObj
     * @param DateTime $endDateObj
     * @return array of days of the week strings
     */
    public static function getContainedWeekDays(DateTime $startDateObj, DateTime $endDateObj = NULL){
        $containedWeekDays = array();
        if(empty($endDateObj) || $startDateObj == $endDateObj){
            $containedWeekDays = array(strtolower($startDateObj->format('D')));
            return $containedWeekDays;
        }
        
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($startDateObj, $interval, $endDateObj);
        
        $count = 0;
        foreach($period as $dateTimeObj){
            if($count == 7){
                break;
            }
            $containedWeekDays[] = strtolower($dateTimeObj->format('D'));
            $count++;
        }
        
        return $containedWeekDays;
    }
    
    public static function isAtLeast($dateTimeSting){
        $now = new DateTime();
        $min = new DateTime($dateTimeSting);
        
        if($now >= $min){
            return true;
        }
        return false;
    }
    
    public static function isAtMost($dateTimeSting){
        $now = new DateTime();
        $max = new DateTime($dateTimeSting);
        
        if($now <= $max){
            return true;
        }
        return false;
    }
    
}
