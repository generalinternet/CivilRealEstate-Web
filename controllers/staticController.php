<?php

class StaticController extends GI_Controller {

    protected function handleSubmitedForm($attributes, $isReferralForm = false){
        $firstName = filter_input(INPUT_POST, 'first_name');
        $lastName = filter_input(INPUT_POST, 'last_name');
        $email = filter_input(INPUT_POST, 'r_email');
        $phone = filter_input(INPUT_POST, 'phone');
        $message = filter_input(INPUT_POST, 'message');
        
        $emailView = new GenericEmailView();
        if(!$isReferralForm){
            $emailView->addParagraph('A message has been sent from your contact form.');
        }else{
            $emailView->addParagraph('A message has been sent from your referral form.');
        }
        $emailView->addLineBreak();

        $emailView->startBlock()
                ->startParagraph()
                    ->addHTML('Name: <b>' . trim($firstName . ' ' . $lastName) . '</b><br/>')
                    ->addHTML('Email: <b>' . $email . '</b>');

        if($isReferralForm){
            $referedBy = filter_input(INPUT_POST, 'referred_by');
            $emailView->addHTML('Referred by: <b>' . $referedBy . '</b>');
        }

        if(!empty($phone)){
            $emailView->addHTML('<br/>Phone: <b>' . $phone . '</b>');
        }
        $emailView->closeParagraph()
                ->addParagraph(nl2br($message))
                ->closeBlock();

        $giEmail = new GI_Email();

        $giEmail->addTo(SITE_EMAIL, EMAIL_TITLE)
                ->addCC('david.kolby@generalinternet.ca', 'David Kolby')
                ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                ->setSubject('Contact Form Message')
                ->useEmailView($emailView);

        if($isReferralForm){
            $giEmail->setSubject('Referral Form Message');
        }

        if($giEmail->send()){
            $newAttributes = $attributes;
            $newAttributes['sent'] = 1;
            GI_URLUtils::redirect($newAttributes);
        }
    }
    
    public function actionContact($attributes){
        $form = new GI_Form('contact_form');
        $form->setBotValidation(true);
        $view = new StaticContactView($form, $attributes);
        
        if(isset($attributes['sent']) && $attributes['sent'] == 1){
            $view->setSent(true);
        }
        
        if($form->wasSubmitted() && $form->validate()){
            $this->handleSubmitedForm($attributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => $view->getSiteTitle(),
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => GI_URLUtils::getAction()
                ))
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }
    
    public function actionReferrals($attributes){
        $form = new GI_Form('referral_form');
        $form->setBotValidation(true);
        $view = new StaticReferralsView($form, $attributes);
        
        if(isset($attributes['sent']) && $attributes['sent'] == 1){
            $view->setSent(true);
        }
        
        if($form->wasSubmitted() && $form->validate()){
            $isReferralForm = true;
            $this->handleSubmitedForm($attributes, $isReferralForm);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => $view->getSiteTitle(),
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => GI_URLUtils::getAction()
                ))
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }

    public function actionCharity($attributes){
        $form = new GI_Form('charity_form');
        $form->setBotValidation(true);
        $view = new StaticCharityView($form, $attributes);
        
        if(isset($attributes['sent']) && $attributes['sent'] == 1){
            $view->setSent(true);
        }
        
        if($form->wasSubmitted() && $form->validate()){
            $firstName = filter_input(INPUT_POST, 'first_name');
            $lastName = filter_input(INPUT_POST, 'last_name');
            $email = filter_input(INPUT_POST, 'r_email');
            $phone = filter_input(INPUT_POST, 'phone');
            $charityName = filter_input(INPUT_POST, 'charity_name');
            $pickLater = filter_input(INPUT_POST, 'pick_later', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $buyOrSell = filter_input(INPUT_POST, 'buy_or_sell');
            
            $emailView = new GenericEmailView();
            $emailView->addParagraph('A message has been sent from your charity form.');

            $emailView->addLineBreak();

            $emailView->startBlock()
                    ->startParagraph()
                        ->addHTML('Name: <b>' . trim($firstName . ' ' . $lastName) . '</b><br/>');

            if(!empty($charityName)){
                $emailView->addHTML('Charity Name: <b>' . $charityName . '</b>');
            }else if(!empty($pickLater)){
                $emailView->addHTML('Charity Name: <i>pick later</i>');
            }

            if(!empty($email)){
                $emailView->addHTML('Email: <b>' . $email . '</b>');
            }
            if(!empty($phone)){
                $emailView->addHTML('<br/>Phone: <b>' . $phone . '</b>');
            }

            if(!empty($buyOrSell)){
                $emailView->addHTML('Buying or Selling: <b>' . $buyOrSell . '</b>');
            }

            $giEmail = new GI_Email();

            $giEmail->addTo(SITE_EMAIL, EMAIL_TITLE)
                    ->addCC('david.kolby@generalinternet.ca', 'David Kolby')
                    ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                    ->setSubject('Contact Form Message')
                    ->useEmailView($emailView);

            if($giEmail->send()){
                $newAttributes = $attributes;
                $newAttributes['sent'] = 1;
                GI_URLUtils::redirect($newAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => $view->getSiteTitle(),
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => GI_URLUtils::getAction()
                ))
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }
    
    public function actionNotify($attributes){
        $form = NULL;
        if(Login::isLoggedIn()){
            $form = new GI_Form('notify_form');
        }
        
        $view = new StaticNotifyView($form);
        
        if(isset($attributes['sent']) && $attributes['sent'] == 1){
            $view->setSent(true);
        }
        
        if(Login::isLoggedIn()){
            if($form->wasSubmitted() && $form->validate()){
                $subject = filter_input(INPUT_POST, 'subject');
                $msg = filter_input(INPUT_POST, 'message');
                $userIdString = filter_input(INPUT_POST, 'user_ids');
                $userIds = explode(',', $userIdString);
                foreach($userIds as $userId){
                    $userToNotify = UserFactory::getModelById($userId);

                    if($userToNotify){
                        Notification::notifyUser($userToNotify, $subject, NULL, $msg);
                    }
                }
                $newAttributes = $attributes;
                $newAttributes['sent'] = 1;
                GI_URLUtils::redirect($newAttributes);
            }
        }
        $returnArray = GI_Controller::getReturnArray($view);
        $breadcrumbs = array(
            array(
                'label' => $view->getSiteTitle(),
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'static',
                    'action' => GI_URLUtils::getAction()
                ))
            ),
        );
        $returnArray['breadcrumbs'] = $breadcrumbs;
        return $returnArray;
    }
    
    public function actionViewNotification($attributes){
        if(!Login::isLoggedIn()){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $curUserId = Login::getUserId();
        
        if(!isset($attributes['id'])){
            GI_URLUtils::redirectToError(2000);
        }
        
        $notificationId = $attributes['id'];
        $notification = NotificationFactory::getModelById($notificationId);
        if(!$notification){
            GI_URLUtils::redirectToError(4001);
        }
        
        if($notification->getProperty('to_id') != $curUserId){
            GI_URLUtils::redirectToAccessDenied();
        }
        
        $view = new StaticViewNotificationView($notification);
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $notification->getBreadcrumbs();
        return $returnArray;
    }
    
    public function actionClearSession($attributes){
        Login::destroySession();
        Header('Location: .');
        die();
    }
    
    public function __call($methodCall, $args){
        $attributes = $args[0];
        $trimWord = 'action';
        if (substr($methodCall, 0, strlen($trimWord)) === $trimWord) {
            $action = substr($methodCall, strlen($trimWord));
        } else {
            $action = $methodCall;
        }
        $staticContentClass = 'Static'.$action.'View';
        if(class_exists($staticContentClass)){
            $view = new $staticContentClass();
            $breadcrumbs = array(
                array(
                    'label' => $view->getSiteTitle(),
                    'link' => GI_URLUtils::buildURL(array(
                        'controller' => 'static',
                        'action' => strtolower($action)
                    ))
                ),
            );
        } elseif(file_exists('controllers/' . strtolower($attributes['action']) . 'Controller.php')){
            GI_URLUtils::redirect(array(
                'controller' => $attributes['action'],
                'action' => 'index'
            ));
        } else {       
            $view = new StaticErrorView($attributes['controller'],$attributes['action'], $attributes);
        }
        $returnArray = GI_Controller::getReturnArray($view);
        if(isset($breadcrumbs) && !empty($breadcrumbs)){
            $returnArray['breadcrumbs'] = $breadcrumbs;
        }
        return $returnArray;
    }
    
    public function errorAction($controller, $action, $attributes){
        $view = new StaticErrorView($controller, $action, $attributes, 'action');
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Error',
                'link' => GI_URLUtils::buildURL($attributes)
            )
        );
        return $returnArray;
    }
    
    public function errorController($controller, $action, $attributes){
        $view = new StaticErrorView($controller, $action, $attributes, 'controller');
        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Error',
                'link' => GI_URLUtils::buildURL($attributes)
            )
        );
        return $returnArray;
    }
    
    public function actionError($attributes){
        $view = new StaticErrorView($attributes['controller'], $attributes['action'], $attributes, 'error_code');
        $returnArray = GI_Controller::getReturnArray($view);
        if(isset($attributes['errorMsg'])){
            $attributes['errorMsg'] = urlencode($attributes['errorMsg']);
        }
        if(isset($attributes['returnURL'])){
            $attributes['returnURL'] = urlencode($attributes['returnURL']);
        }
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Error',
                'link' => GI_URLUtils::buildURL($attributes)
            )
        );
        return $returnArray;
    }
    
    public function actionEmail($attributes){
        $emailView = new GenericEmailView();
        $emailView->addParagraph('This is a <b>test email</b>. Just testing to see how <i>Mandrill</i> works. Starting with an intro paragraph for the email.');
        $emailView->addParagraph('Then continuing on with another paragraph below to see how spacing is.');
        $emailView->startParagraph()
                ->addHTML('Then maybe adding a ')
                ->addLink('link', 'http://generalinternet.ca')
                ->addHTML(' to something.')
                ->closeParagraph();
        $emailView->addLineBreak();
        $emailView->addParagraph('But maybe we can get a little fancier and add a');
        $emailView->startParagraph()
                ->addButton('Button', 'http://generalinternet.ca')
                ->closeParagraph();
        
        $emailView->startBlock()
                ->addParagraph('Blocks can be used to bring focus to certain bits of text. (ex. passwords)')
                ->addParagraph('Email: <b>gi@generalinternet.ca</b><br/>Password: <b>********</b>')
                ->closeBlock();
        
        $emailView->setBlockBGColour('#7899b0')
                ->setBlockColour('#fff')
                ->startBlock()
                ->addParagraph('Multiple blocks can be added with various colours.')
                ->closeBlock();
        
        $emailView->addParagraph('That’s enough of an example,<br/><b>Boxy</b><br/>General Internet Team');
                
        $giEmail = new GI_Email();

        $giEmail->addTo('nicholas.watson@generalinternet.ca', 'Nicholas Watson')
                ->addCC('david.kolby@generalinternet.ca', 'Michael Kelly')
                ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                ->setSubject('Test Mandrill Email')
                ->useEmailView($emailView);
        /*
        if($giEmail->send()){
            
        }
         */
        echo $giEmail->getBody();
        die();
    }
    
    public function actionLoadedContent($attributes){
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = '<h3>I’m Ajax Loaded Content</h3><p>Lorem ipsum dolor sit amet, <b>consectetur adipiscing</b> elit. Donec quis erat risus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam eget ipsum in risus ullamcorper hendrerit. Sed <i>massa neque</i>, tincidunt viverra velit non, fringilla bibendum nisi. Donec dignissim euismod mi ut tempor. Suspendisse id egestas arcu. Aliquam in consectetur purus. Sed tempus erat non felis luctus, eu hendrerit tortor consectetur. Nullam vel arcu vitae tellus efficitur ornare sed vel purus. </p>';
        return $returnArray;
    }
    
    public function actionWait($attributes){
        $waitTime = 5;
        if(isset($attributes['waitTime'])){
            $waitTime = $attributes['waitTime'];
        }
        sleep($waitTime);
        $returnArray = GI_Controller::getReturnArray();
        $returnArray['mainContent'] = 'Thanks for waiting.';
        $returnArray['success'] = 1;
        return $returnArray;
    }
    
    public function actionMessage($attributes){
        $messageData = array(
            'type' => 'success',
            'title' => 'Thank you for signing up.'
        );
        $view = new StaticMessageView($messageData);
        $returnArray = GI_Controller::getReturnArray($view);
        if(isset($attributes['returnURL'])){
            $attributes['returnURL'] = urlencode($attributes['returnURL']);
        }
        $returnArray['breadcrumbs'] = array(
            array(
                'label' => 'Message',
                'link' => GI_URLUtils::buildURL($attributes)
            )
        );
        return $returnArray;
    }
}
