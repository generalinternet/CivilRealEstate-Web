<?php
/**
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

define('MODULE_MLS_VER', 'v4.0');
//*** Abstract
//
//// Interfaces
//require_once 'framework/interface/MLS/model/iMLSListing.php';
//require_once 'framework/interface/MLS/model/iMLSFirm.php';
//require_once 'framework/interface/MLS/model/iMLSRealtor.php';
//require_once 'framework/interface/MLS/model/iMLSArea.php';
//require_once 'framework/interface/MLS/model/iMLSCity.php';
//require_once 'framework/interface/MLS/model/iMLSSubArea.php';
//require_once 'framework/interface/MLS/model/iMLSListingImage.php';
//require_once 'framework/interface/MLS/model/iMLSOpenHouse.php';

// Abstract Domain
require_once 'model/Domain/MLSArea/AbstractMLSArea.php';
require_once 'model/Domain/MLSCity/AbstractMLSCity.php';
require_once 'model/Domain/MLSFirm/AbstractMLSFirm.php';
require_once 'model/Domain/MLSSubArea/AbstractMLSSubArea.php';
require_once 'model/Domain/MLSListing/AbstractMLSListing.php';
require_once 'model/Domain/MLSListing/AbstractMLSListingRes.php';
require_once 'model/Domain/MLSListing/AbstractMLSListingCom.php';
require_once 'model/Domain/MLSListingImage/AbstractMLSListingImage.php';
require_once 'model/Domain/MLSOpenHouse/AbstractMLSOpenHouse.php';
require_once 'model/Domain/MLSRealtor/AbstractMLSRealtor.php';

// Abstract Factory
require_once 'model/Factory/AbstractMLSAreaFactory.php';
require_once 'model/Factory/AbstractMLSCityFactory.php';
require_once 'model/Factory/AbstractMLSFirmFactory.php';
require_once 'model/Factory/AbstractMLSListingFactory.php';
require_once 'model/Factory/AbstractMLSListingImageFactory.php';
require_once 'model/Factory/AbstractMLSOpenHouseFactory.php';
require_once 'model/Factory/AbstractMLSRealtorFactory.php';
require_once 'model/Factory/AbstractMLSSubAreaFactory.php';

// Abstract Views
require_once 'view/AbstractMLSSearchFormView.php';
require_once 'view/AbstractMLSIndexView.php';
require_once 'view/AbstractMLSListingItemView.php';
require_once 'view/AbstractMLSListingDetailView.php';

//*** Concrete
$curIncludePath = get_include_path();
set_include_path('concrete/modules/MLS/' . MODULE_MLS_VER);

//Concrete Domain
require_once 'model/Domain/MLSArea/MLSArea.php';
require_once 'model/Domain/MLSCity/MLSCity.php';
require_once 'model/Domain/MLSFirm/MLSFirm.php';
require_once 'model/Domain/MLSListing/MLSListing.php';
require_once 'model/Domain/MLSListing/MLSListingRes.php';
require_once 'model/Domain/MLSListing/MLSListingCom.php';
require_once 'model/Domain/MLSListingImage/MLSListingImage.php';
require_once 'model/Domain/MLSOpenHouse/MLSOpenHouse.php';
require_once 'model/Domain/MLSRealtor/MLSRealtor.php';
require_once 'model/Domain/MLSSubArea/MLSSubArea.php';


//Concrete Factory
require_once 'model/Factory/MLSAreaFactory.php';
require_once 'model/Factory/MLSCityFactory.php';
require_once 'model/Factory/MLSFirmFactory.php';
require_once 'model/Factory/MLSListingFactory.php';
require_once 'model/Factory/MLSListingImageFactory.php';
require_once 'model/Factory/MLSOpenHouseFactory.php';
require_once 'model/Factory/MLSRealtorFactory.php';
require_once 'model/Factory/MLSSubAreaFactory.php';


//Concrete Views
require_once 'view/mls_searchFormView.php';
require_once 'view/mls_indexView.php';
//
require_once 'view/mls_listingDetailView.php';

set_include_path($curIncludePath);
