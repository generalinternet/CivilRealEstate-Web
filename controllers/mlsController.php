<?php
require_once 'framework/modules/MLS/' . MODULE_MLS_VER . '/controller/AbstractMLSController.php';

class MLSController extends AbstractMLSController{

    protected function sendContactEmail($attributes){
        $firstName = filter_input(INPUT_POST, 'first_name');
        $lastName = filter_input(INPUT_POST, 'last_name');
        $email = filter_input(INPUT_POST, 'r_email');
        $phone = filter_input(INPUT_POST, 'phone');
        $mlsNumber = filter_input(INPUT_POST, 'mls_number');
        
        $emailView = new GenericEmailView();

        $emailView->addLineBreak();

        $emailView->startBlock()
                ->startParagraph()
                    ->addHTML('Name: <b>' . trim($firstName . ' ' . $lastName) . '</b><br/>')
                    ->addHTML('Email: <b>' . $email . '</b>')
                    ->addHTML('<br/>Phone: <b>' . $phone . '</b>');

        if(!empty($mlsNumber)){
            $emailView->addHTML('MLS Number: <b>' . $mlsNumber . '</b>');
        }

        $emailView
            ->closeParagraph()
            ->addParagraph(nl2br($message))
            ->closeBlock();

        $giEmail = new GI_Email();

        $giEmail->addTo('nicholas.watson@generalinternet.ca', 'Nicholas Watson')
                ->addBCC('david.kolby@generalinternet.ca', 'David Kolby')
                ->setFrom(ProjectConfig::getServerEmailAddr(), ProjectConfig::getServerEmailName())
                ->setSubject('MLS Listing Form Message')
                ->useEmailView($emailView);


        if($giEmail->send()){
            $newAttributes = $attributes;
            $newAttributes['sent'] = 1;
            GI_URLUtils::redirect($newAttributes);
        }
    }
    
    public function actionView($attributes){
        if (!isset($attributes['id'])) {
            GI_URLUtils::redirectToError(2000);
        }

        $id = $attributes['id'];
        $listing = MLSListingFactory::getModelById($id);
        if (empty($listing)) {
            GI_URLUtils::redirectToError(4001);
        }
        
        $detailContactForm = new GI_Form('detail_contact');

        $isSent = false;
        if(isset($attributes['sent']) && $attributes['sent'] == 1){
            $isSent = true;
        }

        $view = $listing->getDetailView();
        $view->setSent($isSent);
        $view->setForm($detailContactForm);

        if($detailContactForm->wasSubmitted() && $detailContactForm->validate()){
            $this->sendContactEmail($attributes);
        }


        $returnArray = GI_Controller::getReturnArray($view);
        $returnArray['breadcrumbs'] = $listing->getBreadcrumbs();
        return $returnArray;
    }

}