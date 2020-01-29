<?php
/**
 * Description of AbstractContactOrgProfilePublicProfileDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactOrgProfilePublicProfileDetailView extends MainWindowView {
    
    /** @var AbstractContactOrg */
    protected $contactOrg;
    protected $logoWidth = 125;
    protected $logoHeight = 125;
    protected $addProfileWrap = true;
    
    public function __construct(AbstractContactOrg $contactOrg) {
        parent::__construct();
        $this->contactOrg = $contactOrg;
    }
    
    public function setAddProfileWrap($addProfileWrap){
        $this->addProfileWrap = $addProfileWrap;
        return $this;
    }
    
    protected function addViewBodyContent() {
        $this->openProfileWrap();
        $this->addPublicLogo();
        $this->addBizName();
        $this->addAccentColour();
        $this->addOwnerName();
        $this->addWebsiteURL();
        $this->addAddress();
        $this->addPhone();
        $this->addEmail();
        $this->addBizDescription();
        $this->addVideo();
        $this->closeProfileWrap();
    }
    
    protected function openProfileWrap(){
        if(!$this->addProfileWrap){
            return;
        }
        $this->addHTML('<div class="profile_wrap">');
        return $this;
    }
    
    protected function closeProfileWrap(){
        if(!$this->addProfileWrap){
            return;
        }
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addPublicLogo() {
        $this->addHTML($this->contactOrg->getPublicLogoHTML($this->logoWidth, $this->logoHeight));
    }
    
    protected function addAccentColour() {
        $accentColour = $this->contactOrg->getPublicProfileAccentColour();
        if (!empty($accentColour)) {
            
            $this->addHTML('<div  style="background-color:#'.$accentColour.';height:20px;width:20px;"></div>');
        }
    }
    
    protected function addBizName() {
        $name = $this->contactOrg->getPublicProfileBusinessName();
        $this->addContentBlock($name, 'Business Name');
    }
    
    protected function addWebsiteURL() {
        $url = $this->contactOrg->getPublicProfileWebsiteURL();
        if (!empty($url)) {
            $link = GI_StringUtils::convertURLs($url);
            $this->addContentBlock($link, 'Website');
        }
    }
    
    protected function addOwnerName() {
        $ownerName = $this->contactOrg->getPublicProfileOwnerName();
        $this->addContentBlock($ownerName, 'Owner');
    }
    
    protected function addVideo() {
        $videoURL = $this->contactOrg->getPublicProfileVideoURL();
        if (!empty($videoURL)) {
            $link = GI_StringUtils::convertURLs($videoURL);
            $this->addContentBlock($link, 'Video');
        }
    }
    
    protected function addBizDescription() {
        $description = $this->contactOrg->getPublicProfileBusinessDescription();
        $this->addContentBlock($description, 'Description');
    }
    
    protected function addEmail() {
        $emailModel = $this->contactOrg->getPublicEmailModel();
        if (!empty($emailModel)) {
            $emailAddress = $emailModel->getProperty('contact_info_email_addr.email_address');
            $this->addContentBlock($emailAddress, 'Email');
        }
    }
    
    protected function addPhone() {
        $phoneModel = $this->contactOrg->getPublicPhoneModel();
        if (!empty($phoneModel)) {
            $phoneNumber = $phoneModel->getProperty('contact_info_phone_num.phone');
            $this->addContentBlock($phoneNumber, 'Phone');
        }
    }
    
    protected function addAddress() {
        $address = $this->contactOrg->getPublicAddressModel();
        if (!empty($address)) {
            $addressString = $address->getAddressString(true);
            $this->addContentBlock($addressString, 'Address');
        }
    }
    
}