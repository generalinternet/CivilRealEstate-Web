<?php

class ContentInvestmentSliderItemDetailView extends ContentInvestmentListItemDetailView{

    protected function openViewWrap() {
        $this->addHTML('<div class="investment investment_theme_slider">');
        return $this;
    }
}
