<?php
/**
 * Description of GI_Email
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
class GI_Email{
    
    protected $from = '';
    protected $fromName = '';
    protected $replyTo = '';
    protected $replyToName = '';
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $subject = '';
    protected $body = '';
    protected $html = true;
    protected $boundry = '';
    protected $forceSend = false;
    protected $mandrillTags = array();
    
    public function __construct($html = true){
        $this->html = $html;
        $this->addMandrillTag(ProjectConfig::getMandrillDefaultTag());
    }
    
    public function setForceSend($forceSend){
        $this->forceSend = $forceSend;
        return $this;
    }
    
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }
    
    public function setBody($body){
        $this->body = $body;
        return $this;
    }
    
    public function setFrom($email, $name = NULL){
        $this->from = $email;
        $this->fromName = $name;
        return $this;
    }
    
    public function getFromEmail(){
        if(empty($this->from)){
            return ProjectConfig::getServerEmailAddr();
        }
        return $this->from;
    }
    
    public function getFromName(){
        if(empty($this->fromName)){
            return ProjectConfig::getServerEmailName();
        }
        return $this->fromName;
    }
    
    public function setReplyTo($email, $name = NULL){
        $this->replyTo = $email;
        $this->replyToName = $name;
        return $this;
    }
    
    public function addTo($email, $name = NULL){
        $this->to[$email] = $name;
        return $this;
    }
    
    public function addCC($email, $name = NULL){
        $this->cc[$email] = $name;
        return $this;
    }
    
    public function addBCC($email, $name = NULL){
        $this->bcc[$email] = $name;
        return $this;
    }
    
    public function addMandrillTag($tag){
        $this->mandrillTags[] = $tag;
        return $this;
    }
    
    public function addMandrillTags($tags){
        $this->mandrillTags = array_merge($this->mandrillTags, $tags);
        return $this;
    }
    
    public function getMandrillTags(){
        return $this->mandrillTags;
    }
    
    public function getBody($forOnScreenDisplay = false){
        if(!empty(ProjectConfig::getMandrillAPIKey()) && ProjectConfig::isMandrillEnabled()){
            return $this->body;
        }
        $boundary = $this->getBoundry();
        $body = '';
        if(!$forOnScreenDisplay){
            $body = "\r\n\r\n--" . $boundary . "\r\n";

            //plain text
            $body .= 'Content-type: text/plain; charset="utf-8"' . "\r\n\r\n";
            $body .= nl2br($this->getPlainText());

            $body .= "\r\n\r\n--" . $boundary . "\r\n";
            $body .= 'Content-type: text/html; charset="utf-8"' . "\r\n\r\n";
        }
        $body .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $body .= '<html xmlns="http://www.w3.org/1999/xhtml">';
        $body .= '<head>';
            $body .= '<meta http-equiv="Content-type" content="text/html;charset=utf-8" />';
            $body .= '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';
            $body .= '<title>' . $this->getSubject() . '</title>';
        $body .= '</head>';
            $body .= '<body>';
                $body .= $this->body;
            $body .= '</body>';
        $body .= '</html>';
        
        if(!$forOnScreenDisplay){
            $body .= "\r\n\r\n--" . $boundary . "--";
        }
        return $body;
    }
    
    protected function getPlainText(){
        $cleanedLinks = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '"></a>$1<a "', $this->body);
        $tagsAsLineBreaks = preg_replace('/\n+/', "\n", preg_replace('#<[^>]+>#', "\n", $cleanedLinks));
        $plainText = strip_tags($tagsAsLineBreaks);
        return $plainText;
    }
    
    protected function getSubject(){
        return $this->subject;
    }
    
    protected function getEmailHeaderString($email, $name = NULL){
        $headerString = '';
        if(!is_null($name)){
            $headerString .= $name . ' <';
        }
        $headerString .= $email;
        if(!is_null($name)){
            $headerString .= '>';
        }
        
        return $headerString;
    }
    
    protected function getEmailToString($emails = array()){
        $emailString = '';
        foreach($emails as $email => $name){
            if(!empty($emailString)){
                $emailString .= ', ';
            }
            $emailString .= $this->getEmailHeaderString($email, $name);
        }
        
        return $emailString;
    }
    
    protected function getTo(){
        $toString = '';
        foreach($this->to as $email => $name){
            if(!empty($toString)){
                $toString .= ', ';
            }
            $toString .= $email;
        }
        return $toString;
    }
    
    protected function validateReplyTo(){
        if(empty($this->replyTo)){
            $this->replyTo = $this->getFromEmail();
            $this->replyToName = $this->getFromName();
        }
    }
    
    protected function displayContent($content){
        $newContent = '<pre><code>';
        $newContent .= str_replace(array(
            '<',
            '>'
        ),array(
            '&lt;',
            '&gt;'
        ), $content);
        $newContent .= '</code></pre>';
        return $newContent;
    }
    
    protected function getBoundry(){
        if(empty($this->boundry)){
            $this->boundry = md5(mt_rand());
        }
        
        return $this->boundry;
    }
    
    public function send(){
        if(!empty(ProjectConfig::getMandrillAPIKey()) && ProjectConfig::isMandrillEnabled()){
            return $this->sendWithMandrill();
        }
        $boundary = $this->getBoundry();
        $this->validateReplyTo();
        
        $headers = 'Return-Path: ' . $this->getEmailHeaderString($this->replyTo, $this->replyToName) . ' ' . "\r\n";
        $headers .= 'Reply-To: ' . $this->getEmailHeaderString($this->replyTo, $this->replyToName) . ' ' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'From: ' . $this->getEmailHeaderString($this->getFromEmail(), $this->getFromName()) . ' ' . "\r\n";
        $headers .= 'To: ' . $this->getEmailToString($this->to) . "\r\n";
        if(!empty($this->cc)){
            $headers .= 'CC: ' . $this->getEmailToString($this->cc) . "\r\n";
        }
        if(!empty($this->bcc)){
            $headers .= 'BCC: ' . $this->getEmailToString($this->bcc) . "\r\n";
        }
        $headers .= 'Content-Type: multipart/alternative; boundary=' . $boundary . "\r\n";
        
        if(!DEV_MODE || $this->forceSend){
            return mail($this->getTo(), $this->getSubject(), $this->getBody(), $headers);
        } else {
            //assume that while in dev_mode on localhost that email is working
            return true;
        }
    }
    
    public function useEmailView(AbstractGenericEmailView $emailView){
        $emailView->setTitle($this->getSubject());
        $this->body = $emailView->getHTMLView();
        return $this;
    }
    
    public function sendWithMandrill(){
        if(DEV_MODE && !$this->forceSend){
            return true;
        }
        try {
            $sendTo = array();
            foreach($this->to as $email => $name){
                $sendTo[] = array(
                    'email' => $email,
                    'name' => $name,
                    'type' => 'to'
                );
            }
            foreach($this->cc as $email => $name){
                $sendTo[] = array(
                    'email' => $email,
                    'name' => $name,
                    'type' => 'cc'
                );
            }
            foreach($this->bcc as $email => $name){
                $sendTo[] = array(
                    'email' => $email,
                    'name' => $name,
                    'type' => 'bcc'
                );
            }
            
            $mandrill = new Mandrill(ProjectConfig::getMandrillAPIKey());
            $message = array(
                'html' => $this->body,
                'subject' => $this->getSubject(),
                'from_email' => $this->getFromEmail(),
                'from_name' => $this->getFromName(),
                'to' => $sendTo,
                'headers' => array('Reply-To' => $this->replyTo),
                'important' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'auto_text' => true,
                'inline_css' => false,
                'view_content_link' => false,
                /*
                'attachments' => array(
                    array(
                        'type' => 'text/plain',
                        'name' => 'myfile.txt',
                        'content' => 'ZXhhbXBsZSBmaWxl'
                    )
                ),
                'images' => array(
                    array(
                        'type' => 'image/png',
                        'name' => 'IMAGECID',
                        'content' => 'ZXhhbXBsZSBmaWxl'
                    )
                )
                 */
            );
            
            $subaccount = ProjectConfig::getMandrillSubaccount();
            if(!empty($subaccount)){
                $message['subaccount'] = $subaccount;
            }
            
            $tags = $this->getMandrillTags();
            if(!empty($tags)){
                $message['tags'] = $tags;
            }
            
            $async = false;
            $ip_pool = 'Main Pool';
            $send_at = GI_Time::getDateTime();
            $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
            return true;
            /*
            Array
            (
                [0] => Array
                    (
                        [email] => recipient.email@example.com
                        [status] => sent
                        [reject_reason] => hard-bounce
                        [_id] => abc123abc123abc123abc123abc123
                    )

            )
            */
        } catch(Mandrill_Error $e) {
            trigger_error('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
}
