<?php
/**
 * Description of AbstractGI_VCard
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractGI_VCard{
    
    protected $fileName = '';
    protected $class = 'PUBLIC'; //PUBLIC, PRIVATE, CONFIDENTIAL
    protected $revisionDate;
    protected $vCard = '';
    
    protected $displayName = NULL;
    protected $firstName = NULL;
    protected $lastName = NULL;
    protected $addName = NULL;
    protected $namePrefix = NULL;
    protected $nameSuffix = NULL;
    protected $nickname = NULL;
    protected $gender = NULL;
    protected $title = NULL;
    protected $role = NULL;
    protected $department = NULL;
    protected $company = NULL;
    protected $workPOBox = NULL;
    protected $workExtAddr = NULL;
    protected $workAddrStreet = NULL;
    protected $workAddrCity = NULL;
    protected $workAddrRegion = NULL;
    protected $workAddrCode = NULL;
    protected $workAddrCountry = NULL;
    protected $homePOBox = NULL;
    protected $homeExtAddr = NULL;
    protected $homeAddrStreet = NULL;
    protected $homeAddrCity = NULL;
    protected $homeAddrRegion = NULL;
    protected $homeAddrCode = NULL;
    protected $homeAddrCountry = NULL;
    protected $phoneOffice = NULL;
    protected $phoneHome = NULL;
    protected $phoneCell = NULL;
    protected $phoneFax = NULL;
    protected $phonePager = NULL;
    protected $email = NULL;
    protected $email2 = NULL;
    protected $url = NULL;
    protected $photo = NULL;
    protected $birthday = NULL;
    protected $timezone = NULL;
    protected $sortString = NULL;
    protected $note = NULL;
    
    public function __construct($fileName = NULL){
        $this->setFileName($fileName);
    }
    
    protected function addLine($line){
        $this->vCard .= $line . "\r\n"; 
       return $this;
    }
    
    protected function addWorkAddrLine(){
        $addr = '';
        $addrPO = $this->getWorkPOBox();
        $addrExt = $this->getWorkExtAddr();
        $addrStreet = $this->getWorkAddrStreet();
        $addrCity = $this->getWorkAddrCity();
        $addrRegion = $this->getWorkAddrRegion();
        $addrCode = $this->getWorkAddrCode();
        $addrCountry = $this->getWorkAddrCountry();
        
        GI_StringUtils::appendToString($addrPO, $addr, ';');
        GI_StringUtils::appendToString($addrExt, $addr, ';');
        GI_StringUtils::appendToString($addrStreet, $addr, ';');
        GI_StringUtils::appendToString($addrCity, $addr, ';');
        GI_StringUtils::appendToString($addrRegion, $addr, ';');
        GI_StringUtils::appendToString($addrCode, $addr, ';');
        GI_StringUtils::appendToString($addrCountry, $addr, ';');
        
        $this->addLine('ADR;type=WORK:' . $addr);
    }
    
    protected function addHomeAddrLine(){
        $addr = '';
        $addrPO = $this->getHomePOBox();
        $addrExt = $this->getHomeExtAddr();
        $addrStreet = $this->getHomeAddrStreet();
        $addrCity = $this->getHomeAddrCity();
        $addrRegion = $this->getHomeAddrRegion();
        $addrCode = $this->getHomeAddrCode();
        $addrCountry = $this->getHomeAddrCountry();
        
        GI_StringUtils::appendToString($addrPO, $addr, ';');
        GI_StringUtils::appendToString($addrExt, $addr, ';');
        GI_StringUtils::appendToString($addrStreet, $addr, ';');
        GI_StringUtils::appendToString($addrCity, $addr, ';');
        GI_StringUtils::appendToString($addrRegion, $addr, ';');
        GI_StringUtils::appendToString($addrCode, $addr, ';');
        GI_StringUtils::appendToString($addrCountry, $addr, ';');
        
        $this->addLine('ADR;type=HOME:' . $addr);
    }
    
    public function buildCard(){
        $revDateObj = new DateTime($this->getRevisionDate());
        $revDateString = $revDateObj->format('Ymd') . 'T' . $revDateObj->format('His');
        $this->addLine('BEGIN:VCARD')
                ->addLine('VERSION:4.0')
                ->addLine('CLASS:' . $this->getClass())
                ->addLine('PRODID:-//General Internet//NONSGML GI//EN')
                ->addLine('REV:' . $revDateString)
                ->addLine('FN:' . $this->getDisplayName())
                ->addLine('N:' . $this->getLastName() . ';'
                        . $this->getFirstName() . ';'
                        . $this->getAddName() . ';'
                        . $this->getNamePrefix() . ';'
                        . $this->getNameSuffix());
        
        $nickname = $this->getNickname();
        if(!empty($nickname)){
            $this->addLine('NICKNAME:' . $nickname);
        }
        
        $gender = $this->getGender();
        if(!empty($gender)){
            $this->addLine('GENDER:' . $gender);
        }
        
        $title = $this->getTitle();
        if(!empty($title)){
            $this->addLine('TITLE:' . $title);
        }
        
        $company = $this->getCompany();
        if(!empty($company)){
            $org = 'ORG:' . $company;
            $department = $this->getDepartment();
            if(!empty($department)){
                $org .= ';' . $department;
            }
            $this->addLine($org);
        }
        
        $this->addWorkAddrLine();
        
        $this->addHomeAddrLine();
            
        $email = $this->getEmail();
        if(!empty($email)){
            $this->addLine('EMAIL;type=INTERNET,pref:' . $email);
        }
        
        $email2 = $this->getEmail2();
        if(!empty($email2)){
            $this->addLine('EMAIL;type=INTERNET:' . $email2);
        }
        
        $phoneOffice = $this->getPhoneOffice();
        if(!empty($phoneOffice)){
            $this->addLine('TEL;type=WORK,voice:' . $phoneOffice);
        }
        
        $phoneHome = $this->getPhoneHome();
        if(!empty($phoneHome)){
            $this->addLine('TEL;type=HOME,voice:' . $phoneHome);
        }
        
        $phoneCell = $this->getPhoneCell();
        if(!empty($phoneCell)){
            $this->addLine('TEL;type=CELL,voice:' . $phoneCell);
        }
        
        $phoneFax = $this->getPhoneFax();
        if(!empty($phoneFax)){
            $this->addLine('TEL;type=WORK,fax:' . $phoneFax);
        }
        
        $phonePager = $this->getPhonePager();
        if(!empty($phonePager)){
            $this->addLine('TEL;type=WORK,pager:' . $phonePager);
        }
        
        $url = $this->getURL();
        if(!empty($url)){
            $this->addLine('URL;type=WORK:' . $url);
        }
            
        $birthday = $this->getBirthday();
        if(!empty($birthday)){
            $birthdayObj = new DateTime($birthday);
            $this->addLine('BDAY:' . $birthdayObj->format('Ymd'));
        }
            
   	$role = $this->getRole();
        if(!empty($role)){
            $this->addLine('ROLE:' . $role);
        }
        
        $note = $this->getNote();
        if(!empty($note)){
            $this->addLine('NOTE:' . $note);
        }
        
        $this->addLine('TZ:' . $this->getTimezone());
        
        $this->addLine('END:VCARD');
    }
    
    public function downloadVCard(){
        $vCard = $this->getVCard();
        
        $fileName = $this->getFilename(true);
        
        header('Content-type: text/directory');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Pragma: public');
        echo $vCard;
        return true;
    }
    
    public function getVCard(){
        if(empty($this->vCard)){
            $this->buildCard();
        }
        
        return $this->vCard;
    }
    
    public function getFilename($withExt = false) {
        if(empty($this->fileName)){
            $this->fileName = $this->getDisplayName();
        }
        $this->fileName = GI_Sanitize::filename($this->fileName);
        if($withExt){
            return $this->fileName . '.vcf';
        }
        return $this->fileName;
    }

    public function getClass() {
        return $this->class;
    }

    public function getRevisionDate() {
        if(empty($this->revisionDate)){
            $this->revisionDate = GI_Time::getDateTime();
        }
        return $this->revisionDate;
    }

    public function getDisplayName() {
        if(empty($this->displayName)){
            $this->displayName = trim($this->getFirstName() . ' ' . $this->getLastName());
        }
        return $this->displayName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getAddName() {
        return $this->addName;
    }

    public function getNamePrefix() {
        return $this->namePrefix;
    }

    public function getNameSuffix() {
        return $this->nameSuffix;
    }

    public function getNickname() {
        return $this->nickname;
    }
    
    public function getGender(){
        $gender = strtoupper($this->gender);
        switch($gender){
            case 'MALE':
            case 'MAN':
            case 'BOY':
            case 'M':
                $this->gender = 'M';
                break;
            case 'FEMALE':
            case 'WOMAN':
            case 'GIRL':
            case 'F':
                $this->gender = 'F';
                break;
            default:
                $this->gender = NULL;
                break;
        }
        
        return $this->gender;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getRole() {
        return $this->role;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function getCompany() {
        return $this->company;
    }

    public function getWorkPOBox() {
        return $this->workPOBox;
    }

    public function getWorkExtAddr() {
        return $this->workExtAddr;
    }

    public function getWorkAddrStreet() {
        return $this->workAddrStreet;
    }

    public function getWorkAddrCity() {
        return $this->workAddrCity;
    }

    public function getWorkAddrRegion() {
        return $this->workAddrRegion;
    }

    public function getWorkAddrCode() {
        return $this->workAddrCode;
    }

    public function getWorkAddrCountry() {
        return $this->workAddrCountry;
    }

    public function getHomePOBox() {
        return $this->homePOBox;
    }

    public function getHomeExtAddr() {
        return $this->homeExtAddr;
    }

    public function getHomeAddrStreet() {
        return $this->homeAddrStreet;
    }

    public function getHomeAddrCity() {
        return $this->homeAddrCity;
    }

    public function getHomeAddrRegion() {
        return $this->homeAddrRegion;
    }

    public function getHomeAddrCode() {
        return $this->homeAddrCode;
    }

    public function getHomeAddrCountry() {
        return $this->homeAddrCountry;
    }

    public function getPhoneOffice() {
        return $this->phoneOffice;
    }

    public function getPhoneHome() {
        return $this->phoneHome;
    }

    public function getPhoneCell() {
        return $this->phoneCell;
    }

    public function getPhoneFax() {
        return $this->phoneFax;
    }

    public function getPhonePager() {
        return $this->phonePager;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEmail2() {
        return $this->email2;
    }

    public function getURL() {
        return $this->url;
    }

    public function getPhoto() {
        return $this->photo;
    }

    public function getBirthday() {
        return $this->birthday;
    }

    public function getTimezone() {
        if(empty($this->timezone)){
            $dateObj = new DateTime();
            $this->timezone = $dateObj->getTimezone();
        }
        return $this->timezone;
    }

    public function getSortString() {
        if(empty($this->sortString)){
            $this->sortString = $this->getLastName();
        }
        if(emptY($this->sortString)){
            $this->sortString = $this->getCompany();
        }
        return $this->sortString;
    }

    public function getNote() {
        return $this->note;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
        return $this;
    }

    public function setClass($class) {
        $this->class = $class;
        return $this;
    }

    public function setRevisionDate($revisionDate) {
        $this->revisionDate = $revisionDate;
        return $this;
    }

    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
        return $this;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    public function setAddName($addName) {
        $this->addName = $addName;
        return $this;
    }

    public function setNamePrefix($namePrefix) {
        $this->namePrefix = $namePrefix;
        return $this;
    }

    public function setNameSuffix($nameSuffix) {
        $this->nameSuffix = $nameSuffix;
        return $this;
    }

    public function setNickname($nickname) {
        $this->nickname = $nickname;
        return $this;
    }
    
    public function setGender($gender){
        $this->gender = $gender;
        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setRole($role) {
        $this->role = $role;
        return $this;
    }

    public function setDepartment($department) {
        $this->department = $department;
        return $this;
    }

    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    public function setWorkPOBox($workPOBox) {
        $this->workPOBox = $workPOBox;
        return $this;
    }

    public function setWorkExtAddr($workExtAddr) {
        $this->workExtAddr = $workExtAddr;
        return $this;
    }

    public function setWorkAddrStreet($workAddrStreet) {
        $this->workAddrStreet = $workAddrStreet;
        return $this;
    }

    public function setWorkAddrCity($workAddrCity) {
        $this->workAddrCity = $workAddrCity;
        return $this;
    }

    public function setWorkAddrRegion($workAddrRegion) {
        $this->workAddrRegion = $workAddrRegion;
        return $this;
    }

    public function setWorkAddrCode($workAddrCode) {
        $this->workAddrCode = $workAddrCode;
        return $this;
    }

    public function setWorkAddrCountry($workAddrCountry) {
        $this->workAddrCountry = $workAddrCountry;
        return $this;
    }

    public function setHomePOBox($homePOBox) {
        $this->homePOBox = $homePOBox;
        return $this;
    }

    public function setHomeExtAddr($homeExtAddr) {
        $this->homeExtAddr = $homeExtAddr;
        return $this;
    }

    public function setHomeAddrStreet($homeAddrStreet) {
        $this->homeAddrStreet = $homeAddrStreet;
        return $this;
    }

    public function setHomeAddrCity($homeAddrCity) {
        $this->homeAddrCity = $homeAddrCity;
        return $this;
    }

    public function setHomeAddrRegion($homeAddrRegion) {
        $this->homeAddrRegion = $homeAddrRegion;
        return $this;
    }

    public function setHomeAddrCode($homeAddrCode) {
        $this->homeAddrCode = $homeAddrCode;
        return $this;
    }

    public function setHomeAddrCountry($homeAddrCountry) {
        $this->homeAddrCountry = $homeAddrCountry;
        return $this;
    }

    public function setPhoneOffice($phoneOffice) {
        $this->phoneOffice = $phoneOffice;
        return $this;
    }

    public function setPhoneHome($phoneHome) {
        $this->phoneHome = $phoneHome;
        return $this;
    }

    public function setPhoneCell($phoneCell) {
        $this->phoneCell = $phoneCell;
        return $this;
    }

    public function setPhoneFax($phoneFax) {
        $this->phoneFax = $phoneFax;
        return $this;
    }

    public function setPhonePager($phonePager) {
        $this->phonePager = $phonePager;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setEmail2($email2) {
        $this->email2 = $email2;
        return $this;
    }

    public function setURL($url) {
        $this->url = $url;
        return $this;
    }

    public function setPhoto($photo) {
        $this->photo = $photo;
        return $this;
    }

    public function setBirthday($birthday) {
        $this->birthday = $birthday;
        return $this;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
        return $this;
    }

    public function setSortString($sortString) {
        $this->sortString = $sortString;
        return $this;
    }

    public function setNote($note) {
        $this->note = $note;
        return $this;
    }
    
}
