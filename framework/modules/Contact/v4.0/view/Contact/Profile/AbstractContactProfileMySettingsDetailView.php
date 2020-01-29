<?php
/**
 * Description of AbstractContactProfileMySettingsDetailView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactProfileMySettingsDetailView extends MainWindowView {
    
    /* @var AbstractContactInd */
    protected $contactInd;
    protected $user;


    public function __construct(AbstractContactInd $contactInd) {
        parent::__construct();
        $this->contactInd = $contactInd;
        $this->user = $contactInd->getUser();
    }

    protected function addViewBodyContent() {
        $this->addContactSection();
        $this->addSystemAccessSection();
    }
    
    protected function addContactSection() {
        $this->addHTML('<h2>Contact</h2>');
        $name = $this->contactInd->getProperty('contact_ind.first_name') . ' ' . $this->contactInd->getProperty('contact_ind.last_name');
        $this->addContentBlock($name, 'Name');
        $this->addContactInfoBlock();
    }

    protected function addContactInfoBlock(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $contactInfoAllTypesArray = $this->contactInd->getContactInfoArray();
        foreach ($contactInfoAllTypesArray as $contactInfoArray) {
            foreach ($contactInfoArray as $contactInfo) {
                $contactInfoDetailView = $contactInfo->getDetailView();
                $detailContent = $contactInfoDetailView->getHTMLView();
                if (!empty($detailContent)) {
                    $view->addHTML('<div class="content_block_wrap">');
                    $view->addHTML($detailContent);
                    $view->addHTML('</div>');
                }
            }
        }
    }

    protected function addSystemAccessSection() {
        if (empty($this->user)) {
            return;
        }
        $this->addHTML('<hr />');
        $this->addHTML('<h2>System Access</h2>');
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col sml">');
        $this->addAvatar();
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addContentBlock($this->user->getProperty('email'), 'Login Email');
        $this->addHTML('</div>')
                ->addHTML('</div>');
        $this->addEventNotificationSettingsSection();
    }
    
    protected function addAvatar() { 
        if (!empty($this->user)) {
            $avatarView = $this->user->getUserAvatarView();
            if (!empty($avatarView)) {
                $this->addHTML('<span class="avatar_wrap inline_block has_img">' . $avatarView->getHTMLView() . '</span>');
            } else {
                $this->addHTML($this->user->getAvatarPlaceHolderHTML());
            }
            
        }
    }
        
    
    protected function addEventNotificationSettingsSection() {
        $url = GI_URLUtils::buildURL(array(
            'controller'=>'user',
            'action'=>'viewNotificationSettings',
            'userId'=>$this->user->getId(),
            'contentOnly'=>'1',
            'ajax'=>1
        ));
        $this->addHTML('<hr />');
        $this->addHTML('<h2>Notification Settings</h2>');
        $this->addHTML('<div class="ajaxed_contents auto_load" data-url="'.$url.'"></div>');
    }

}
