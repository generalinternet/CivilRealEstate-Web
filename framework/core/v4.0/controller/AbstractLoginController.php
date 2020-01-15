<?php
/**
 * Description of AbstractLoginController
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.7
 */
class AbstractLoginController extends GI_Controller {

    protected $saltypass = NULL;
    protected $loginForm = NULL;

    public function actionIndex($attributes) {
        $this->loginForm = new GI_Form('login_form');
        $form = $this->loginForm;
        $view = new LoginIndexView($form);
        if(isset($attributes['ajax']) && $attributes['ajax'] == 1){
            $view->setAjax(true);
        }
        $success = 0;
        $jqueryAction = '';
        
        if(LoginFactory::logIn($form)){
            if(isset($attributes['ajax']) && $attributes['ajax'] == 1){
                $success = 1;
                $jqueryAction = 'sessionReset();';
            } else {
                static::redirectAfterLogin();
            }
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['isLogin'] = 1;
        if(!empty($jqueryAction)){
            $returnArray['success'] = $success;
            $returnArray['jqueryAction'] = $jqueryAction;
        }
        return $returnArray;
    }

    protected function validateCredentials($email, $password) {
        $userArray = UserFactory::search()
                    ->filter('email', $email)
                    ->select();
        if (!empty($userArray)) {
            $user = $userArray[0];
            $pass = $user->getProperty('pass');
            $salt = $user->getProperty('salt');
            $saltypass = $user->generateSaltyPass($password, $salt);
            if ($saltypass === $pass) {
                $this->saltypass = $saltypass;
                Login::setUserId($user->getProperty('id'));
                return true;
            } else {
                $this->loginForm->addFieldError('password', 'invalid', 'Invalid password.');
            }
        } else {
            $this->loginForm->addFieldError('email', 'invalid', 'Invalid email.');
        }

        return false;
    }

    protected function getLogKey() {
        return LoginFactory::getLogKey();
    }

    protected function refreshLogin($logKey) {
        return LoginFactory::refreshLogin($logKey);
    }

    protected function createNewLogin() {
        return LoginFactory::createNewLogin();
    }

    public function actionLogout() {
        $userId = Login::getUserId();
        $loginArray = LoginFactory::search()
                ->filter('user_id', $userId)
                ->select();
        $login = $loginArray[0];
        $login->logOut();
        
        static::redirectAfterLogout();
    }

    public function actionForgotPassword() {
        $form = new GI_Form('forgotPassword');
        $view = new LoginForgotPasswordView($form);
        $emailIssue = false;
        if ($form->wasSubmitted() && $form->validate()) {
            $botDetection = filter_input(INPUT_POST, 'email');
            if(empty($botDetection)){
                $email = filter_input(INPUT_POST, 'rEmail');
                $userArray = UserFactory::search()
                        ->filter('email', $email)
                        ->select();
                if (count($userArray) > 0) {
                    //user exists
                    $user = $userArray[0];
                    $giEmail = NULL;
                    if($user->sendForgotPassEmail($giEmail)){
                            $view->setThanks(true);
                            if(DEV_MODE){
                                echo $giEmail->getBody(true);
                                die();
                            }
                        } else {
                            $emailIssue = true;
                        }
                    } else {
                    //user not found
                    $emailIssue = true;
                }
            } else {
                //bot field filled out
                $emailIssue = true;
            }
        }
        if($emailIssue){
            $form->addFieldError('rEmail', 'error', 'There was an issue sending your email, please try again.');
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionRequestNewPass($attributes) {
        $logKey = '';
        if(isset($attributes['logKey'])){
            $logKey = $attributes['logKey'];
        }
        $loginAuditArray = Login_Audit::getByProperties(array(
            'log_key' => $logKey
        ));
        if (!empty($logKey) && count($loginAuditArray) > 0) {
            $loginAudit = $loginAuditArray[0];
            $userId = $loginAudit->getProperty('user_id');
            Login::setUserId($userId);
            Login::setUserLang();
            $user = UserFactory::getModelById($userId);
            
            $form = new GI_Form('reset_pass');
            if ($form->wasSubmitted() && $form->validate()) {
                $newPassword = filter_input(INPUT_POST, 'password');
                $newPasswordConf = filter_input(INPUT_POST, 'password_conf');
                if ($newPassword !== $newPasswordConf) {
                    $form->addFieldError('password_conf', 'miamatch', 'Must match above password.');
                } else {
                    if(!$user->validatePassword($newPassword, $reason)){
                        $form->addFieldError('password', 'invalid', $reason);
                    } else {
                        $salt = $user->generateSalt();
                        $newPass = $user->generateSaltyPass($newPassword, $salt);
                        $user->setProperty('salt', $salt);
                        $user->setProperty('pass', $newPass);
                        $user->setProperty('force_pass_reset', 0);

                        if ($user->save()) {
                            $newLogKey = $this->createNewLogin();
                            $_SESSION['log_key'] = $newLogKey;
                            static::redirectAfterRequestPassword();
                        } else {
                            GI_URLUtils::redirectToError(1000);
                        }
                    }
                }
            }
            $view = new LoginResetPasswordView($form);

            $returnArray = GI_Controller::getReturnArray($view);
            return $returnArray;
        } else {
            GI_URLUtils::redirectToError(2000);
        }
    }
    
    public function actionForcePassReset($attributes){
        $user = Login::getUser();
        if(!$user){
            GI_URLUtils::redirect(array(
                'controller' => 'login',
                'action' => 'index'
            ));
        }
        $attributes['logKey'] = $user->getLogKey();
        return static::actionRequestNewPass($attributes);
    }
    
    public function actionStillHere($attributes){
        $form = new GI_Form('still_here');
        $view = new LoginStillHereView($form);
        $success = 0;
        $jqueryAction = '';
        
        $forceLogOut = true;
        
        if (isset($_SESSION['log_key'])) {
            $loginArray = LoginFactory::search()
                    ->filter('log_key', $_SESSION['log_key'])
                    ->select();
            if ($loginArray) {
                $forceLogOut = false;
                $login = $loginArray[0];
                
                if(filter_input(INPUT_COOKIE, 'remember_me') != NULL){
                    $success = 1;
                    $jqueryAction = 'showContent = false; sessionReset();';
                }
                
                $loginTime = strtotime($login->getProperty('last_mod'));
                $thirtyMinsBeforeNow = strtotime('-25 minutes');

                if($loginTime > $thirtyMinsBeforeNow){
                    $resetTime = ($loginTime - $thirtyMinsBeforeNow) / 60;
                    $success = 1;
                    $jqueryAction = 'showContent = false; sessionReset(' . $resetTime . '); stopTitleBlink();';
                }
                
                if ($form->wasSubmitted() && $form->validate()) {
                    $login->setProperty('active', 1);
                    if($login->save()){
                        $success = 1;
                        $jqueryAction = 'sessionReset();';
                    }
                }
            }
        }
        
        if($forceLogOut){
            $success = 1;
            $jqueryAction = 'showContent = false; sessionExpired();';
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['success'] = $success;
        $returnArray['jqueryAction'] = $jqueryAction;
        return $returnArray;
    }
    
    public function actionRegister($attributes){
        $form = new GI_Form('register');
        
        $user = UserFactory::buildNewModel();
        $view = new LoginRegisterView($form, $user);
        
        if ($user->handleFormSubmission($form)) {
            if (!ProjectConfig::registerRequiresConfirmation()) {
                $user = UserFactory::changeModelType($user, 'user');
                if (!$user->save()) {
                    GI_URLUtils::redirectToError(1000);
                }
            LoginFactory::loginAsUser($user);
        }
            static::redirectAfterRegistration($user);
        }
        
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public function actionConfirmEmail($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $id = $attributes['id'];
        $user = UserFactory::getModelById($id);
        if (empty($user) || !empty($user->getProperty('confirmed'))) {
            GI_URLUtils::redirectToError();
        }
        if (!empty($attributes['code'] && ($user->getProperty('confirm_code') != $attributes['code']))) {
            GI_URLUtils::redirectToError();
        }
        $confirmCodeSentDate = $user->getProperty('confirm_code_sent_date');
        if (!empty($confirmCodeSentDate)) {
            $confirmExpiryDateTime = new DateTime($confirmCodeSentDate);
            $confirmExpiryDateTime->modify("+".USER_CONFIRM_TTL." minutes");
            $currentDateTime = new DateTime(GI_Time::getDateTime());
            if ($currentDateTime > $confirmExpiryDateTime) {
                GI_URLUtils::redirect(array(
                    'controller'=>'login',
                    'action'=>'sendConfirmationEmail',
                    'id'=>$id,
                    're'=>1
                ));
            }
        }
        $form = new GI_Form('confirm_email');
        $addCodeField = false;
        $view = new LoginConfirmEmailFormView($form, $user);
        if (empty($attributes['code'])) {
            $addCodeField = true;
        }
        $view->setAddCodeField($addCodeField);
        $view->buildForm();
        if ($form->wasSubmitted() && $form->validate()) {
            $fieldErrors = false;
            $password = filter_input(INPUT_POST, 'password');
            $passwordTwo = filter_input(INPUT_POST, 'password_two');
            if ($addCodeField) {
                $confirmCode = filter_input(INPUT_POST, 'code');
                if (empty($confirmCode) || $confirmCode != $user->getProperty('confirm_code')) {
                    $fieldErrors = true;
                    $form->addFieldError('code', 'wrong_code', 'Incorrect Code');
                }
            } 
            if ($password !== $passwordTwo) {
                $form->addFieldError('password_two', 'mismatch', 'You must re-enter the same password as above.');
                $fieldErrors = true;
            }
            if (!$fieldErrors) {
                $salt = GI_StringUtils::generateRandomString(8, false, true, true, true, 2, 1);
                $saltypass = $user->generateSaltyPass($password, $salt);
                $user->setProperty('pass', $saltypass);
                $user->setProperty('salt', $salt);
                $user->setProperty('confirmed', 1);
                if ($user->save()) {
                    LoginFactory::loginAsUser($user);
                    static::redirectAfterLogin();
                } else {
                    GI_URLUtils::redirectToError();
                }
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionSendConfirmationEmail($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $id = $attributes['id'];
        $user = UserFactory::getModelById($id);
        if (empty($user)) {
            GI_URLUtils::redirectToError();
        }
        if (!empty($user->getProperty('confirmed'))) {
            GI_URLUtils::redirect(array(
                'controller'=>'login',
                'action'=>'index'
            ));
        }
        $form = new GI_Form('send_email');
        $view = new LoginResendConfirmationEmailFormView($form);
        if (isset($attributes['re']) && $attributes['re'] == 1) {
            $message = 'Your confirmation code has expired. Please enter your email address to receive another confirmation email.';
            $view->setMessage($message);
        }
        $view->buildForm();
        $view->buildView();
        if ($form->wasSubmitted() && $form->validate()) {
            $submittedEmail = filter_input(INPUT_POST, 'email_address');
            if ($submittedEmail == $user->getProperty('email')) {
                $sendEmail = true;
                $confirmCodeSentDate = $user->getProperty('confirm_code_sent_date');
                if (!empty($confirmCodeSentDate)) {
                    $confirmUnlockDateTime = new DateTime($confirmCodeSentDate);
                    $confirmUnlockDateTime->modify("+3 minutes");
                    $currentDateTime = new DateTime(GI_Time::getDateTime());
                    if ($currentDateTime < $confirmUnlockDateTime) {
                        $sendEmail = false;
                    }
                }
                if ($sendEmail && !$user->sendConfirmEmailAddressEmail()) {
                    GI_URLUtils::redirectToError(5000);
                }
            }
            GI_URLUtils::redirect(array(
                'controller'=>'login',
                'action'=>'confirmationSent',
                'id'=>$id
            ));
        }
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }

    public function actionConfirmationSent($attributes) {
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError();
        }
        $userId = $attributes['id'];
        $user = UserFactory::getModelById($userId);
        if (empty($user)) {
            GI_URLUtils::redirectToError();
        }
        $view = new LoginConfirmationSentView($user);
        $returnArray = GI_Controller::getReturnArray($view);
        return $returnArray;
    }
    
    public static function redirectAfterLogin(){
        $attributes = GI_URLUtils::getAttributes();
        $targetController = $attributes['controller'];
        $targetAction = $attributes['action'];
        if ($targetController === 'login' && $targetAction == 'index') {
            $attributes['controller'] = GI_ProjectConfig::getDefaultConroller();
            $attributes['action'] = GI_ProjectConfig::getDefaultAction();
        }
        GI_URLUtils::redirect($attributes);
    }
    
    public static function redirectAfterLogout(){
        GI_URLUtils::redirect(array(
            'controller' => 'login',
            'action' => 'index'
        ));
    }
    
    public static function redirectAfterRegistration(){
        GI_URLUtils::redirect(array(
            'controller' => GI_ProjectConfig::getDefaultConroller(),
            'action' => GI_ProjectConfig::getDefaultAction()
        ));
    }
    
    public static function redirectAfterRequestPassword(){
        GI_URLUtils::redirect(array(
            'controller' => GI_ProjectConfig::getDefaultConroller(),
            'action' => GI_ProjectConfig::getDefaultAction()
        ));
    }

}
