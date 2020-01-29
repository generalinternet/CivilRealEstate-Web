<?php

/**
 * Description of AbstractContactCatClientDetailView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactCatClientDetailView extends AbstractContactCatDetailView {

    protected function buildViewBody() {
        $defaultPricingRegion = PricingRegionFactory::getModelById($this->contactCat->getProperty('contact_cat_client.default_pricing_region_id'));
        if (!empty($defaultPricingRegion)) {
            $this->addHTML('<div class="content_block_wrap">');
            $this->addContentBlock($defaultPricingRegion->getProperty('title'), 'Pricing Region');
            $this->addHTML('</div>');
        }
    }

}
