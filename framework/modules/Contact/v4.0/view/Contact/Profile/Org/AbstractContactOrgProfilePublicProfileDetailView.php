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
    protected $addEditBtn = false;
    protected $showMoreSection = true;
    
    public function __construct(AbstractContactOrg $contactOrg) {
        parent::__construct();
        $this->contactOrg = $contactOrg;
    }
    
    public function setAddProfileWrap($addProfileWrap){
        $this->addProfileWrap = $addProfileWrap;
        return $this;
    }
    
    public function setAddEditBtn($addEditBtn){
        $this->addEditBtn = $addEditBtn;
        return $this;
    }
    
    public function setShowMoreSection($showMoreSection){
        $this->showMoreSection = $showMoreSection;
        return $this;
    }
    
    protected function addWindowBtns() {
        parent::addWindowBtns();
        $this->addEditBtn();
    }
    
    protected function addEditBtn(){
        if($this->addEditBtn && $this->contactOrg->isEditable()){
            $editAttrs = $this->contactOrg->getEditProfileURLAttrs();
            $editAttrs['step'] = 30;
            $editURL = GI_URLUtils::buildURL($editAttrs);
            $this->addHTML('<a href="' . $editURL . '" title="Edit" class="custom_btn">'.GI_StringUtils::getIcon('edit').'<span class="btn_text">Edit</span></a>');
        }
    }
    
    protected function openProfileWrap(){
        if(!$this->addProfileWrap){
            return;
        }
        $styleString = '';
        $accentColour = $this->contactOrg->getPublicProfileAccentColour();
        if (!empty($accentColour)) {
            $styleString .= 'border-color: #' . $accentColour . ';';
        }
        $this->addHTML('<div class="public_profile_wrap" style="' . $styleString . '" >');
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
    
    protected function addViewBodyContent() {
        $this->openProfileWrap();
        $this->addPublicProfileContent();
        $this->closeProfileWrap();
    }
    
    protected function addPublicProfileContent(){
        $this->addPublicLogo();
        $this->addHTML('<div class="public_profile_info">');
        $this->addBtns();
        $this->addBizName();
        $this->addOwnerName();
        $this->addContactDetails();
        $this->addMoreSection();
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function addBtns(){
        $this->addHTML('<div class="right_btns">');
        $this->addEditBtn();
        $this->addHTML('</div>');
    }
    
    protected function addContactDetails() {
        $this->addHTML('<div class="auto_columns public_profile_contact_info_wrap">');
        $this->addPhone();
        $this->addEmail();
        $this->addWebsiteURL();
        $this->addVideo();
        $this->addHTML('</div>');
        $this->addAddress();
    }

    protected function addBizName() {
        $name = $this->contactOrg->getPublicProfileBusinessName();
        $this->addHTML('<h3 class="public_profile_title">' . $name . '</h3>');
        return $this;
    }

    protected function addOwnerName() {
        $ownerName = $this->contactOrg->getPublicProfileOwnerName();
        if (!empty($ownerName)) {
            $this->addHTML('<h4 class="public_profile_sub_title public_profile_contact_info">' . GI_StringUtils::getSVGIcon('contact', '1.2em') . '<span class="info">' . $ownerName . '</span></h4>');
        }
        return $this;
    }

    protected function addPhone() {
        $phoneModel = $this->contactOrg->getPublicPhoneModel();
        if (!empty($phoneModel)) {
            $phoneNumber = $phoneModel->getProperty('contact_info_phone_num.phone');
            if (!empty($phoneNumber)) {
                $this->addHTML('<span class="public_profile_contact_info phone">' . GI_StringUtils::getSVGIcon('phone02', '1.2em') . '<span class="info">' . $phoneNumber . '</span></span>');
            }
        }
        return $this;
    }

    protected function addVideo() {
        $videoURL = $this->contactOrg->getPublicProfileVideoURL();
        if (!empty($videoURL)) {
            $link = GI_StringUtils::fixLink($videoURL);
            if (!empty($link)) {
                $this->addHTML('<a href="' . $link . '" target="_blank" class="public_profile_contact_info url">' . GI_StringUtils::getSVGIcon('web', '1.2em') . '<span class="info">' . $link . '</span></a>');
            }
        }
    }
    
    protected function getMoreSectionId(){
        return 'public_profile_more_' . $this->contactOrg->getId();
    }
    
    protected function addMoreBtn(){
        if(!$this->getAddMoreSection()){
            return;
        }
        $this->addHTML('<span class="advanced_btn custom_btn" data-adv-id="' . $this->getMoreSectionId() . '"><span class="btn_text">View Details</span></span>');
    }
    
    protected function getAddMoreSection(){
        $description = $this->contactOrg->getPublicProfileBusinessDescription();
        if(!empty($description)){
            return true;
        }
        return false;
    }
    
    protected function addMoreSection(){
        if(!$this->showMoreSection){
            return;
        }
        if(!$this->getAddMoreSection()){
            return;
        }
        $this->addHTML('<div id="' . $this->getMoreSectionId() . '" class="advanced">');
        $this->addMoreBtn();
        $this->addHTML('<div class="advanced_content">');
        $this->addBizDescription();
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }

    protected function addBizDescription() {
        $description = $this->contactOrg->getPublicProfileBusinessDescription();
        if (!empty($description)) {
            $this->addHTML('<div class="public_profile_contact_info desc"><p>' . GI_StringUtils::nl2brHTML($description) . '</p></div>');
        }
    }

    protected function addEmail() {
        $emailModel = $this->contactOrg->getPublicEmailModel();
        if (!empty($emailModel)) {
            $emailAddress = $emailModel->getProperty('contact_info_email_addr.email_address');
            if(empty($emailAddress)){
                return;
            }
            $this->addHTML('<span class="public_profile_contact_info email">' . GI_StringUtils::getSVGIcon('email02', '1.2em') . '<span class="info">' . $emailAddress . '</span></span>');
            
        }
    }

    protected function addWebsiteURL() {
        $url = $this->contactOrg->getPublicProfileWebsiteURL();
        if (!empty($url)) {
            $link = GI_StringUtils::fixLink($url);
            if (!empty($link)) {
                $this->addHTML('<a href="' . $link . '" target="_blank" class="public_profile_contact_info url">' . GI_StringUtils::getSVGIcon('home', '1.2em') . '<span class="info">' . $link . '</span></a>');
            }
        }
    }

    protected function addAddress() {
        $address = $this->contactOrg->getPublicAddressModel();
        if (!empty($address)) {
            $addressString = $address->getAddressString();
            if (!empty($addressString) && trim(str_replace(',','',$addressString)) !== '') {
                $this->addHTML('<span class="public_profile_contact_info address">' . GI_StringUtils::getSVGIcon('map_pin', '1.2em') . '<span class="info">' . $addressString . '</span></span>');
            }
        }
    }
    
}