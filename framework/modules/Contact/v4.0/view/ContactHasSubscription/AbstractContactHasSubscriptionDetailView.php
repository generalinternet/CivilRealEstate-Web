<?php

abstract class AbstractContactHasSubscriptionDetailView extends MainWindowView {
    
    protected $contactHasSubscription;
    
    
    public function __construct(AbstractContactHasSubscription $contactHasSubscription) {
        parent::__construct();
        $this->contactHasSubscription = $contactHasSubscription;
    }
    
    protected function addViewBodyContent() {
        $this->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addSubscriptionSection();
        $this->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addContactSpecificSection();
        $this->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addSubscriptionSection() {
        $subscription = $this->contactHasSubscription->getSubscription();
        if (!empty($subscription)) {
            $detailView = $subscription->getDetailView();
            if (!empty($detailView)) {
                $detailView->setOnlyBodyContent(true);
                $this->addHTML($detailView->getHTMLView());
            }
        }
    }

    protected function addContactSpecificSection() {
        if ($this->contactHasSubscription->isActive()) {
            $endDate = $this->contactHasSubscription->getEndDate(true);
            if (!empty($endDate)) {
                //effective until = end date
                $this->addHTML('Effective Until ' . $endDate);
            } else {
                $nextPaymentDate = $this->contactHasSubscription->getNextPaymentDate(true);
                if (!empty($nextPaymentDate)) {
                    $this->addHTML('Next Payment on ' . $nextPaymentDate);
                }
            }
        } else {
            //effective date = start date
            $startDate = $this->contactHasSubscription->getStartDate(true);
            $this->addHTML('Effective on ' . $startDate);
        }
    }



}
