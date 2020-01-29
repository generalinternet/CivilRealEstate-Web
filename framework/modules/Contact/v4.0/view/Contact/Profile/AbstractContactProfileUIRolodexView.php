<?php

class AbstractContactProfileUIRolodexView extends UIRolodexView {
    
    protected $usePublicCardView = false;
    
    public function setUsePublicCardView($usePublicCardView = true) {
        $this->usePublicCardView = $usePublicCardView;
    }

    protected function buildRow($model) {
        if ($this->usePublicCardView) {
            $cardView = $model->getPublicProfileUICardView();
        } else {
            $cardView = $model->getProfileUICardView();
        }
        if ($cardView) {
            if ($model->getId() == $this->curId) {
                $cardView->addCardClass('current');
            }
            $this->addHTML($cardView->getHTMLView());
        } else {
            parent::buildRow($model);
        }
    }

}
