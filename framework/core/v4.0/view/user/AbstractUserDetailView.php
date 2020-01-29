<?php
/**
 * Description of AbstractUserDetailView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
abstract class AbstractUserDetailView extends MainWindowView {

    /** @var AbstractUser */
    protected $user;
    protected $notificationsTableView;
    protected $viewBuilt = false;
    

    public function __construct(AbstractUser $user, $notificationsTableView = NULL) {
        parent::__construct();
        $this->user = $user;
        $this->notificationsTableView = $notificationsTableView;
        $this->addSiteTitle(Lang::getString('users'));
        $fullName = $this->user->getFullName();
        $this->addSiteTitle($fullName);
        $avatarString = $this->user->getUserAvatarHTML();
        $this->setWindowTitle($avatarString . '<span class="inline_block">' . $fullName . '</span>');
        $listBarURL = NULL;
        $profileView = false;
        $profile = GI_URLUtils::getAttribute('profile');
        if($profile && $profile == 1){
            $profileView = true;
        }
        if($profileView){
            $notification = NotificationFactory::buildNewModel();
            if($notification){
                $listBarURL = $notification->getListBarURL();
            }
        } else {
            $listBarURL = $user->getListBarURL();
        }
        if(!empty($listBarURL)){
            $this->setListBarURL($listBarURL);
        }
    }
    
    public function setNotificationTableView(AbstractUITableView $notificationTableView = NULL) {
        $this->notificationsTableView = $notificationTableView;
    }
 
    public function addViewBodyContent() {
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addDetailsSection();
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addRolesSection();
        $this->addHTML('</div>')
                ->addHTML('</div>');

        $this->addHTML('<div class="columns halves">');
        $this->addAdditionalDetailSection();
//        $this->addNotificationsSection();
        $this->addFilesSection();
        $this->addLogsSection();
        $this->addSettingsAdvancedBlock();
        $this->addHTML('</div>');
    }

    protected function addRolesSection(){
        $this->addHTML('<div class="detail_section role_section left_label_content">');
        $roles = $this->user->getRoles();
        if (!empty($roles)) {
            if (count($roles) == 1) {
                $roleTitle = 'Role';
            } else {
                $roleTitle = 'Roles';
            }
            $this->addContentBlockTitle($roleTitle)
                    ->addHTML('<div class="content_block"><ul>');
            foreach ($roles as $role) {
                $this->addHTML('<li class="with_edit">' . $role->getProperty('title') . '</li>');
            }
            $this->addHTML('</ul></div>');
        }
        $this->addHTML('</div>');
    }
    
    protected function addDetailsSection(){
        $this->addHTML('<div class="detail_section left_label_content">');
        $email = $this->user->getProperty('email');
        $this->addContentBlock($email, 'Email');

        $mobile = $this->user->getProperty('mobile');
        $this->addContentBlock($mobile, 'Mobile');
        $this->addHTML('</div>');
    }
    
    protected function addAdditionalDetailSection(){
        $userId = $this->user->getId();
        if(dbConnection::isModuleInstalled('contact') && !Permission::verifyByRef('all_warehouses', $userId)){
            $this->addHTML('<div class="column">');
                $this->addHTML('<div class="content_group ajax_link_wrap">');
                $warehouseAssignments = AssignedToContactFactory::getByUserId($userId, 'assigned_to_warehouse');
                if (Permission::verifyByRef('assign_contacts') && $userId != Login::getUserId()) {
                    $this->addHTML('<div class="right_btns">');
                    $assignToContactURL = GI_URLUtils::buildURL(array(
                        'controller' => 'contact',
                        'action' => 'addAssignedToContact',
                        'userId' => $userId,
                        'type' => 'assigned_to_warehouse',
                        'contactType' => 'warehouse'
                    ));
                    $this->addHTML('<a href="' . $assignToContactURL . '" title="Assign User to Contact" class="custom_btn open_modal_form" data-modal-class="medium_sized"><span class="icon_wrap"><span class="icon primary plus"></span></span><span class="btn_text">Assign</span></a>');
                    $this->addHTML('</div>');
                }
                $this->addHTML('<h2 class="content_group_title">Warehouse</h2>');
                if($warehouseAssignments){
                    foreach($warehouseAssignments as $warehouseAssignment){
                        $this->addHTML('<div class="content_block block_with_right_btns">');
                        if($warehouseAssignment->isDeleteable()){
                            $deleteAssignedURL = GI_URLUtils::buildURL(array(
                                'controller'=>'contact',
                                'action'=>'deleteAssignedToContact',
                                'id' => $warehouseAssignment->getId(),
                                'userId' => $userId
                            ));
                            $this->addHTML('<div class="right_btns"><a href="' . $deleteAssignedURL . '" title="Delete Assignment" class="custom_btn open_modal_form"><span class="icon_wrap"><span class="icon primary trash"></span></span></a></div>');
                        }
                        $warehouse = $warehouseAssignment->getContact();
                        $this->addHTML('<a href="' . $warehouse->getViewURL() . '" title="View ' . $warehouse->getName() . '">' . $warehouse->getName() . '</a>');
                        $this->addHTML('</div>');
                    }
                } else {
                    $this->addHTML('<p class="content_block no_model_message">No assigned warehouses found.</p>');
                }
                $this->addHTML('</div>');
            $this->addHTML('</div>');
        }
    }
    
    /**
     * @deprecated since version 4.0
     */
//    protected function addNotificationsSection(){
//        if ($this->notificationsTableView) {
//            $this->addHTML('<div class="column">');
//                $this->addHTML('<div class="content_group ajax_link_wrap">')
//                    ->addHTML('<div class="right_btns">')
//                    ->addNotificationsBtn()
//                    ->addHTML('</div>')
//                        ->addHTML('<h2 class="content_group_title">Notifications</h2>')
//                        ->addHTML($this->notificationsTableView->getHTMLView())
//                    ->addHTML('</div>');
//            $this->addHTML('</div>');
//        }
//    }
    
    protected function addWindowBtns(){
        $this->addEditBtn();
        $this->addDeleteBtn();
        $this->addSendConfirmEmailBtn();
    }
    
    protected function addEditBtn(){
        if ($this->user->isEditable()) {
            $editURL = $this->user->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" class="custom_btn" title="Edit User"><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit Profile</span></a>');
        }
    }
    
    protected function addDeleteBtn(){
        if ($this->user->isDeleteable()) {            
            $deleteURL = $this->user->getDeleteURL();
            $this->addHTML('<a href="' . $deleteURL . '" class="custom_btn open_modal_form" title="Delete User"><span class="icon_wrap"><span class="icon primary trash"></span></span><span class="btn_text">Delete Profile</span></a>');
        }
    }
    
    protected function addSendConfirmEmailBtn(){
        if (!$this->user->getIsConfirmed()) {
            $confirmEmailURL = GI_URLUtils::buildURL(array(
                'controller' => 'user',
                'action' => 'sendConfirmationEmail',
                'id' => $this->user->getId(),
            ));
            $this->addHTML('<a href="' . $confirmEmailURL . '" class="custom_btn open_modal_form" title="Send Confirmation Email"><span class="icon_wrap"><span class="icon primary email"></span></span><span class="btn_text">Send Confirmation Email</span></a>');
        }
    }

    protected function addNotificationsBtn() {
        $markNotificationsViewedURL = GI_URLUtils::buildURL(array(
            'controller' => 'notification',
            'action' => 'markAllNotificationsSeen',
            'id' => Login::getUserId(),
        ));
        $this->addHTML('<a href="' . $markNotificationsViewedURL . '" title="Mark All Notifications Viewed" class="custom_btn open_modal_form" ><span class="icon_wrap"><span class="icon primary check"></span></span><span class="btn_text">Mark All Viewed</span></a>');
        return $this;
    }
    
    protected function addFilesSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $view->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">')
                ->addHTML('<h4>Avatars</h4>');
        $this->addImageSection($view);
        $view->addHTML('</div>')
                ->addHTML('<div class="column">')
                ->addHTML('<h4>Files</h4>');
        $this->addFileSection($view);
        $view->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addImageSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $folder = $this->user->getSubFolderByRef('profile_pictures');
        if($folder){
            $this->addFolderContents($view, $folder, 'avatars');
            
        }
    }
    
    protected function addFileSection(GI_View $view = NULL) {
        if(is_null($view)){
            $view = $this;
        }
        $folder = $this->user->getFolder();
        if($folder){
            $this->addFolderContents($view, $folder);
        }
    }
    
    protected function addFolderContents(GI_View $view = NULL, $folder = NULL, $fileTypeTerm = 'files'){
        if(empty($folder)){
            return;
        }
        if(is_null($view)){
            $view = $this;
        }
        $view->addHTML('<div class="content_files">');
        $files = $folder->getFiles();
        if(!empty($files)){
            foreach($files as $file){
                $fileView = $file->getView();
                $fileView->setIsDeleteable(false);
                $fileView->setIsRenamable(false);
                $view->addHTML($fileView->getHTMLView());
            }
        } else {
            $view->addHTML('<p class="no_model_message content_block">No ' . $fileTypeTerm . ' found.</p>');
        }
        $view->addHTML('</div>');
    }

    protected function addLogsSection() {
        if (Permission::verifyByRef('view_user_activity_logs') || (($this->user->getId() == Login::getUserId()) && Permission::verifyByRef('view_my_user_activity_logs'))) {
            $uploader = $this->user->getEventsLogUploader();
            if (!empty($uploader)) {
                $this->addHTML($uploader->getHTMLView());
            }
        }
    }
    
    protected function addSettingsAdvancedBlock($classNames = '') {
        if (!((Permission::verifyByRef('super_admin') || Login::getUserId() == $this->user->getId()))) {
            return;
        }
        $isOpenOnLoad = false;
        $attrs = array(
            'controller'=>'user',
            'action'=>'viewNotificationSettings',
            'userId'=>$this->user->getId(),
            'contentOnly'=>'1',
            'ajax'=>1
        );
        $attrs['tabbed'] = 1;
        $attrs['addAddBtn'] = 0;
        $ajaxAutoLoadUrl = GI_URLUtils::buildURL($attrs);

        $btnOptionsArray = NULL;
        
        $targetRef = 'settings';
        $advClassNames = $classNames . ' ' . $this->targetRefPrefix . $targetRef;
        $headerIcon = 'gear';
        $headerTitle = 'Settings';
        $isAddToSidebar = false;
        $this->addAdvancedBlock($headerTitle, NULL, $btnOptionsArray, NULL, $isOpenOnLoad, '--', NULL, $targetRef, $advClassNames, $headerIcon, $isAddToSidebar, $ajaxAutoLoadUrl);
    }

}
