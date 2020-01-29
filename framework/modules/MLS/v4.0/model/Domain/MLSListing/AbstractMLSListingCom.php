<?php
/**
 * Description of AbstractMLSListingCom
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSListingCom extends AbstractMLSListing{
    
    protected $retsType = 'com';
    
    protected static $addImportCols = array(
        'mls_listing_com.lease_or_sale' => 'lease_or_sale',
        'mls_listing_com.business_type_minor' => 'business_type_minor',
        'mls_listing_com.business_type_major' => 'business_type_major',
        'mls_listing_com.addr_number_low' => 'addr_number_low',
        'mls_listing_com.addr_search_number' => 'addr_search_number',
        'mls_listing_com.broker_reciprocity' => 'broker_reciprocity',
        'mls_listing_com.region' => 'region',
        'mls_listing_com.pid_num' => 'pid_num',
        'mls_listing_com.zoning_land_use' => 'zoning_land_use',
        'mls_listing_com.legal_desc' => 'legal_desc',
        'mls_listing_com.region_code' => 'region_code',
        'mls_listing_com.member_board_affiliation' => 'member_board_affiliation',
        'mls_listing_com.nearest_town' => 'nearest_town',
        'mls_listing_com.lease_rate_sqft_per_annum' => 'lease_rate_sqft_per_annum',
        'mls_listing_com.num_of_units' => 'num_of_units',
        'mls_listing_com.subject_space_sq_ft' => 'subject_space_sq_ft'
    );
    
    protected static $tagFields = array(
        'com_type' => 'mls_com_prop_type',
        'com_prop_type' => 'mls_com_prop_type'
    );
    
    protected static $storeImages = true;
    
    protected static $importOpenHouses = false;
    
    public function getViewTitle($plural = true) {
        $title = 'Commercial ';
        $title .= parent::getViewTitle($plural);
        return $title;
    }
    
}
