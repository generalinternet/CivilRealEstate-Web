<?php
/**
 * Description of AbstractMLSListingRes
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingRes extends AbstractMLSListing{
    
    protected $retsType = 'res';
    
    protected static $addImportCols = array(
        'mls_listing_res.list_date' => 'list_date',
        'mls_listing_res.features' => 'features',
        'mls_listing_res.site_influences' => 'site_influences',
        'mls_listing_res.internet_remarks' => 'internet_remarks',
        'mls_listing_res.publish_listing_on_internet' => 'publish_listing_on_internet',
        'mls_listing_res.depth' => 'depth',
        'mls_listing_res.street_region_code' => 'street_region_code',
        'mls_listing_res.title_to_land' => 'title_to_land',
        'mls_listing_res.view' => 'view',
        'mls_listing_res.view_specify' => 'view_specify',
        'mls_listing_res.age' => 'age',
        'mls_listing_res.gross_taxes' => 'gross_taxes',
        'mls_listing_res.tax_year' => 'tax_year',
        'mls_listing_res.frontage' => 'frontage',
        'mls_listing_res.frontage_metric' => 'frontage_metric',
        'mls_listing_res.fireplaces' => 'fireplaces',
        'mls_listing_res.floor_area_finished' => 'floor_area_finished',
        'mls_listing_res.floor_area_total' => 'floor_area_total',
        'mls_listing_res.full_baths' => 'full_baths',
        'mls_listing_res.half_baths' => 'half_baths',
        'mls_listing_res.maint_fee' => 'maint_fee',
        'mls_listing_res.num_floor_lvls' => 'num_floor_lvls',
        'mls_listing_res.total_baths' => 'total_baths',
        'mls_listing_res.total_bedrooms' => 'total_bedrooms',
        'mls_listing_res.basement_area' => 'basement_area',
        'mls_listing_res.home_style' => 'home_style'
    );
    
    protected static $tagFields = array(
        'dwelling_type' => 'mls_dwelling'
    );
    
    protected static $importOpenHouses = true;
    
    public function getViewTitle($plural = true) {
        $title = 'Residential ';
        $title .= parent::getViewTitle($plural);
        return $title;
    }
    
}
