<?php
use Twilio\Rest\Client as TwilioClient;
/**
 * Description of GI_SMS
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
class GI_SMS{
    
    protected $message = '';
    protected $phoneNumber = '';
    protected $forceSend = false;
    
    public function __construct($phoneNumber = NULL, $message = NULL) {
        $this->setPhoneNumber($phoneNumber);
        $this->setMessage($message);
    }
    
    public function setForceSend($forceSend){
        $this->forceSend = $forceSend;
        return $this;
    }
    
    public function setPhoneNumber($phoneNumber){
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
    
    public function setMessage($message){
        $this->message = $message;
        return $this;
    }
    
    public function getPhoneNumber(){
        return $this->phoneNumber;
    }
    
    public function getMessage(){
        return $this->message;
    }
    
    public static function getTestPhoneNumber($testType = 'available'){
        switch($testType){
            case 'available':
                $phoneNumber = '+15005550006';
                break;
            case 'invalid':
                $phoneNumber = '+15005550001';
                break;
            case 'unavailable':
                $phoneNumber = '+15005550006';
                break;
        }
        return $phoneNumber;
    }
    
    public function testCredentials($testType = 'available'){
        $testPhoneNumber = static::getTestPhoneNumber($testType);
        $sid = ProjectConfig::getTwilioTestSID();
        $token = ProjectConfig::getTwilioTestAuthToken();
        $client = new TwilioClient($sid, $token);

        try{
            $result = $client->incomingPhoneNumbers->create(
                array(
                    'phoneNumber' => $testPhoneNumber
                )
            );
        } catch (Exception $ex) {
            echo '<pre>';
            die(var_dump($ex));
        }
        

        echo '<pre>';
        die(var_dump($result));
    }
    
    public function sendMessage(){
//        if(DEV_MODE && !$this->forceSend){
//            return true;
//        }
        $phoneNumber = $this->getPhoneNumber();
        $message = $this->getMessage();
        
        if(!empty($phoneNumber) && !empty($message)){
            $sid = ProjectConfig::getTwilioSID();
            $token = ProjectConfig::getTwilioAuthToken();
            
            $fromPhoneNumber = ProjectConfig::getTwilioPhoneNumber();
            $client = new TwilioClient($sid, $token);
            
            try{
                $result = $client->messages->create(
                        $phoneNumber,
                        array(
                            'from' => $fromPhoneNumber,
                            'body' => $message
                            //'mediaUrl' => 'URL_TO_IMAGE.png'
                        )
                );
            } catch (Exception $ex) {
                echo '<pre>';
                die(var_dump($ex));
            }
            return true;
        }
        return false;
    }
    
    public static function formatNumberE164($phoneNumber){
        if(!isset($phoneNumber{3})) { return ''; }
        //Strip out everything but numbers 
        $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);
        //Temp
        return '+1'.$phoneNumber;
    }
    
    //Test
    public function sendTestMessage(){
        $phoneNumber = $this->getPhoneNumber();
        $message = $this->getMessage();
        
        if(!empty($phoneNumber) && !empty($message)){
            $sid = ProjectConfig::getTwilioTestSID();
            $token = ProjectConfig::getTwilioTestAuthToken();
            $fromPhoneNumber = static::getTestPhoneNumber();
            $client = new TwilioClient($sid, $token);
            try{
                $client->lookups
                ->v1
                ->phoneNumbers($fromPhoneNumber)
                ->fetch(array(
                    'countryCode' => 'CA'
                ));
            } catch (\Twilio\Exceptions\TwilioException $ex) {
                if(method_exists($ex, 'getStatusCode')){
                    $statusCode = $ex->getStatusCode();
                    if($statusCode == 404 || $statusCode == 403){
                        return false;
                    }
                }
                echo '<pre>';
                die(var_dump($ex));
            }
            
            try{
                $result = $client->messages->create(
                        $phoneNumber,
                        array(
                            'from' => $fromPhoneNumber,
                            'body' => $message
                            //'mediaUrl' => 'URL_TO_IMAGE.png'
                        )
                );
            } catch (Exception $ex) {
                echo '<pre>';
                die(var_dump($ex));
            }
            return $result;
        }
        return false;
    }
}
