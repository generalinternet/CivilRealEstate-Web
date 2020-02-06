<?php

class RECatalogView extends AbstractRECatalogView{
    
    protected function openListingWrap(){
        $this->addHTML('<div class="relisting-item">');
        return $this;
    }

    protected function addListingContent(){
        $this->addFeaturedImage();

        $this->addHTML('<div class="relisting-item__main-content-wrap">');
            $this->addHTML('<div class="relisting-item__overview-wrap">');
                $this->addHTML('<div class="relisting-item__title-description">');
                    $this->addListingTitle();
                    // TODO: address
                    // TODO: bedroom, bathroom ...
                    $this->addSummary();
                $this->addHTML('</div>');
                $this->addHTML('<div class="relisting-item__view-button-wrap">');
                    $this->addViewCTA();
                $this->addHTML('</div>');
            $this->addHTML('</div>');
            $this->addHTML('<div class="relisting-item__features-wrap">');
                // TODO: square footage
                $this->addListingPrice();
                // TODO: favourite
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
}
