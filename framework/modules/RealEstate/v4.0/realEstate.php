<?php
/**
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

define('MODULE_REALESTATE_VER', 'v4.0');
//*** Abstract

// Abstract Domain
require_once 'model/Domain/REListing/AbstractREListing.php';
require_once 'model/Domain/REListing/AbstractREListingRes.php';
require_once 'model/Domain/REListing/AbstractREListingCom.php';
require_once 'model/Domain/REListing/AbstractREListingResMod.php';
require_once 'model/Domain/REListing/AbstractREListingComMod.php';
require_once 'model/Domain/REListingStatus/AbstractREListingStatus.php';

// Abstract Factory
require_once 'model/Factory/AbstractREListingFactory.php';
require_once 'model/Factory/AbstractREListingStatusFactory.php';

// Abstract View
require_once 'view/AbstractREIndexView.php';
require_once 'view/AbstractREDetailView.php';
require_once 'view/AbstractREFormView.php';
require_once 'view/AbstractREModFormView.php';
require_once 'view/AbstractRESearchFormView.php';
require_once 'view/AbstractRECatalogView.php';
require_once 'view/REListing/AbstractREListingSearchFormView.php';

//*** Concrete
$curIncludePath = get_include_path();
set_include_path('concrete/modules/RealEstate/' . MODULE_REALESTATE_VER);

//Concrete Domain
require_once 'model/Domain/REListing/REListing.php';
require_once 'model/Domain/REListing/REListingRes.php';
require_once 'model/Domain/REListing/REListingCom.php';
require_once 'model/Domain/REListing/REListingResMod.php';
require_once 'model/Domain/REListing/REListingComMod.php';
require_once 'model/Domain/REListingStatus/REListingStatus.php';

//Concrete Factory
require_once 'model/Factory/REListing/REListingFactory.php';
require_once 'model/Factory/REListingStatus/REListingStatusFactory.php';

//Concrete View
require_once 'view/re_indexView.php';
require_once 'view/re_detailView.php';
require_once 'view/re_formView.php';
require_once 'view/re_modFormView.php';
require_once 'view/re_searchFormView.php';
require_once 'view/re_catalogView.php';
require_once 'view/REListing/reListing_searchFormView.php';

set_include_path($curIncludePath);