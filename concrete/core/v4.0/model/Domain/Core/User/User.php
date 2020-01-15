<?php
/**
 * Description of User
 * Place methods here that are specific for the client's application
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class User extends AbstractUser {
    
    
    /**
     * @var UserDetailWe 
     */
    protected $primeUserDetail;


    /**
     * Form submit handler
     * 
     * @param GI_Form $form
     * @return boolean. false if not submitted or failed to save
     */
    public function handleStepSignupFormSubmission(GI_Form $form, $step) {
        if ($form->wasSubmitted() && $form->validate()) {
            //Step registeration
            switch ($step) {
                case 1:
                    //Names & email
                    $email = trim(filter_input(INPUT_POST, 'r_email'));
                    $userId = $this->getId();
                    if(UserFactory::existingEmail($email, $userId)){
                        $user = UserFactory::getByEmail($email);
                        $password = $user->getProperty('pass');
                        if (!empty($password)) {
                            $loginURL = GI_URLUtils::buildURL(array(
                                'controller' => 'login',
                                'action' => 'index',
                            ));
                            $form->addFieldError('r_email', 'existing', 'This email is already being used by another user. If it\'s your email, Click <a href="'.$loginURL.'"><b>HERE</b></a> to login.');
                        } else {
                            $signupURL = GI_URLUtils::buildURL(array(
                                'controller' => 'user',
                                'action' => 'signup',
                                'step' => 2,
                                'ajax' => 0,
                                'id' => $user->getId(),
                            ));
                            $form->addFieldError('r_email', 'existing', 'This email is already being used by another user. If it\'s your email, Click <a href="'.$signupURL.'"><b>HERE</b></a> to continue.');
                        }
                    }

                    if ($form->fieldErrorCount()) {
                        return false;
                    }

                    $mobile = trim(filter_input(INPUT_POST, 'mobile'));
                    $firstName = filter_input(INPUT_POST, 'first_name');
                    $lastName = filter_input(INPUT_POST, 'last_name');
                    $language = filter_input(INPUT_POST, 'language');
                    $investorType = filter_input(INPUT_POST, 'investor_type');
                    $roleId = (int) filter_input(INPUT_POST, 'role_id');

                    if(!empty($userId)){
                        return $this->updateBasicInfo($firstName, $lastName, $email, $mobile, $roleId, $language, $investorType);
                    }
                    
                    $_SESSION['is_signing_up'] = true;
                    $_SESSION['signup_first_name'] = $firstName;
                    $_SESSION['signup_last_name'] = $lastName;
                    $_SESSION['signup_r_email'] = $email;
                    $_SESSION['signup_mobile'] = $mobile;
                    $_SESSION['signup_language'] = $language;
                    $_SESSION['signup_role_id'] = $roleId;
                    $_SESSION['signup_investor_type'] = $investorType;

                    return true;

                    break;

                case 2:
                    // validate password
                    $password = filter_input(INPUT_POST, 'new_password');
                    $repeatPassword = filter_input(INPUT_POST, 'repeat_password');
                    if (!empty($password)) {
                        if ($password !== $repeatPassword) {
                            $form->addFieldError('repeat_password', 'mismatch', 'You must re-enter the same password as above.');
                        }
                    }
                    if ($form->fieldErrorCount()) {
                        return false;
                    }
                    $reason = '';
                    $strict = false;
                    if(!$this->validatePassword($password, $reason, $strict)){
                        $form->addFieldError('new_password', 'invalid', $reason);
                        return false;
                    }

                    // save previous infos
                    if(isset($_SESSION['is_signing_up']) && $_SESSION['is_signing_up']){
                        $this->signUpWithBasicInfo();
                    }
                    //Password
                    $salt = $this->generateSalt();
                    $this->setProperty('salt', $salt);
                    $pass = $this->generateSaltyPass($password, $salt);
                    $this->setProperty('pass', $pass);
                        
                    $internal = 0;
                    if ($this->save()) {
                        if (!$this->saveUserAsContact($internal, $form)) {
                            return false;
                        }
                     }
                    break;

                case 3:
                    $userDetail = $this->getPrimeUserDetail();
                    if (!empty($userDetail)) {
                        if (!$userDetail->handleGeneralProfileFormSubmission($form)) {
                            return false;
                        }
                    }
                    break;
                case 4:
                    $userDetail = $this->getPrimeUserDetail();
                    if (!empty($userDetail)) {
                        if (!$userDetail->handleInvestmentProfileFormSubmission($form)) {
                            return false;
                        }
                    }
                    
                    break;
                case 5:
                    $agreementForm = AgreementFormFactory::getAgreementFormByTypeRef();
                    if (!empty($agreementForm)) {
                        $agreement = $agreementForm->getAgreementByUser($this);
                        if (!empty($agreement)) {
                            if (!$agreement->handleFormSubmission($form)) {
                                return false;
                            }
                            // update investor type to accredited after sign agreements
                            $userDetail = $this->getPrimeUserDetail();
                            $userDetail->toggleAccreditedInvestorType(true);
                        }
                    }
                    
                default:
            }
            return true;
        }
        return false;
    }

    public function getSignupFormView(GI_Form $form) {
        $formView = new UserSignupFormView($form, $this);
        return $formView;
    }
    
    /**
     * Get user details
     * @return type
     */
    public function getUserDetails() {
        //Get user details by user id
        return UserDetailFactory::search()
                ->filter('user_id', $this->getProperty('id'))
                ->select();
    }
    
    /**
     * Get the first user detail
     * @return type
     */
    public function getPrimeUserDetail() {
        if (empty($this->primeUserDetail)) {
            $userDetails = $this->getUserDetails();
            if (!empty($userDetails)) {
                $this->primeUserDetail = $userDetails[0];
            } else {
                $userDetail = UserDetailFactory::buildNewModel('we');
                $this->primeUserDetail = $userDetail;
            }
        }
        $this->primeUserDetail->setProperty('user_id', $this->getId());
        return $this->primeUserDetail;
    }
    
    public function validatePassword($newPassword, &$reason = '', $strict = true){
        if ($strict) {
            return parent::validatePassword($newPassword, $reason);
        } else {
            $badPass = false;
//            $curSalt = $this->getProperty('salt');
//            $curSaltyPass = $this->getProperty('pass');
//            $newSaltyPass = $this->generateSaltyPass($newPassword, $curSalt);
//            if($newSaltyPass == $curSaltyPass){
//                if(!empty($reason)){
//                    $reason .= '<br/>';
//                }
//                $reason .= 'Cannot be the same as the old password.';
//                $badPass = true;
//            }

            if(!GI_StringUtils::validatePassword($newPassword, $reason)){
                $badPass = true;
            }

            if($badPass){
                return false;
            }
            GI_StringUtils::generateRandomString();
            return true;
        }   
        
    }

    protected function signUpWithBasicInfo(){
        $email = $_SESSION['signup_r_email'];
        $mobile = $_SESSION['signup_mobile'];
        $firstName = $_SESSION['signup_first_name'];
        $lastName = $_SESSION['signup_last_name'];
        $language = $_SESSION['signup_language'];
        $roleId = (int) $_SESSION['signup_role_id'];
        $investorType = $_SESSION['signup_investor_type'];
        
        if(empty($language)){
            $language = 'english';
        }

        $isUpdated = $this->updateBasicInfo($firstName, $lastName, $email, $mobile, $roleId, $language, $investorType);
        if(!$isUpdated){
            return false;
        }

        unset($_SESSION['is_signing_up']);
        unset($_SESSION['signup_r_email']);
        unset($_SESSION['signup_mobile']);
        unset($_SESSION['signup_first_name']);
        unset($_SESSION['signup_last_name']);
        unset($_SESSION['signup_language']);
        unset($_SESSION['signup_role_id']);
        unset($_SESSION['signup_investor_type']);

        return true;
    }

    protected function updateBasicInfo($firstName, $lastName, $email, $mobile, $roleId, $language, $investorType){
        $this->setProperty('first_name', $firstName);
        $this->setProperty('last_name', $lastName);
        $this->setProperty('email', $email);
        $this->setProperty('mobile', $mobile);
        $this->setProperty('language', $language);

        if (!$this->save()) {
            return false;
        }

        if(!$this->setAndSaveUserRoles($roleId)){
            return false;
        }

        $userDetail = $this->getPrimeUserDetail();
        $userDetail->setProperty('user_detail_we.investor_type', $investorType);
        if (!$userDetail->save()) {
            return false;
        }

        return true;
    }

    public static function validateSignUp($routeAttr){
        $step = 1;
        if(isset($routeAttr['step'])){
            $step = $routeAttr['step'];
        }

        // step: choose password 
        // need to have credentials before setting password
        if(
            $step == 2 && !Login::isLoggedIn() && (
                !isset($_SESSION['is_signing_up']) ||
                !$_SESSION['is_signing_up']
            )
        ){
            GI_URLUtils::redirect(array(
                'controller' => 'user',
                'action' => 'signup'
            ));
            return false;
        }

        // if isset userId but user's no longer exist
        if(isset($routeAttr['id'])){
            $user = UserFactory::getModelById($routeAttr['id']);
            if(empty($user)){
                GI_URLUtils::redirect(array(
                    'controller' => 'user',
                    'action' => 'signup'
                ));
                return;
            }
        }

        return true;
    }

    public static function setSignUpSourceFactor($type, $ref){
        $_SESSION['signUpSource'] = $type;
        $_SESSION['signUpSourceRef'] = $ref;
    }

    public static function getSignUpSourceURL(){
        if(!isset($_SESSION['signUpSource']) || !isset($_SESSION['signUpSourceRef'])){
            return false;
        }
        $type = $_SESSION['signUpSource'];
        $ref = $_SESSION['signUpSourceRef'];
        
        unset($_SESSION['signUpSource']);
        unset($_SESSION['signUpSourceRef']);

        return GI_URLUtils::buildCleanURL(array(
            'controller' => 'static',
            'action' => $type,
            'ref' => $ref,
        ));
    }
}
