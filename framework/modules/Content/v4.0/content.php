<?php
/**
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */

define('MODULE_CONTENT_VER', 'v4.0');

//*** Abstract

// Abstract Domain
require_once 'model/Domain/Content/AbstractContent.php';
require_once 'model/Domain/Content/AbstractContentText.php';
require_once 'model/Domain/Content/ContentText/AbstractContentTextCode.php';
require_once 'model/Domain/Content/ContentText/AbstractContentTextWYSIWYG.php';
require_once 'model/Domain/Content/ContentText/AbstractContentTextVideo.php';
require_once 'model/Domain/Content/AbstractContentPage.php';
require_once 'model/Domain/Content/AbstractContentFileCol.php';
require_once 'model/Domain/Content/ContentFileCol/AbstractContentFileColSlider.php';
require_once 'model/Domain/Content/ContentFileCol/AbstractContentFileColGallery.php';
require_once 'model/Domain/Content/ContentFileCol/AbstractContentFileColImage.php';
require_once 'model/Domain/ContentAvailableChildType/AbstractContentAvailableChildType.php';
require_once 'model/Domain/ContentInContent/AbstractContentInContent.php';
require_once 'model/Domain/Content/AbstractContentRef.php';

// Abstract Factory
require_once 'model/Factory/AbstractContentFactory.php';
require_once 'model/Factory/AbstractContentAvailableChildTypeFactory.php';
require_once 'model/Factory/AbstractContentInContentFactory.php';

// Abstract View
require_once 'view/Content/AbstractContentFormView.php';
require_once 'view/Content/AbstractContentDetailView.php';
require_once 'view/Content/AbstractContentTextFormView.php';
require_once 'view/Content/AbstractContentTextDetailView.php';
require_once 'view/Content/ContentText/AbstractContentTextCodeFormView.php';
require_once 'view/Content/ContentText/AbstractContentTextCodeDetailView.php';
require_once 'view/Content/ContentText/AbstractContentTextWYSIWYGFormView.php';
require_once 'view/Content/ContentText/AbstractContentTextWYSIWYGDetailView.php';
require_once 'view/Content/ContentText/AbstractContentTextVideoFormView.php';
require_once 'view/Content/ContentText/AbstractContentTextVideoDetailView.php';
require_once 'view/Content/AbstractContentPageFormView.php';
require_once 'view/Content/AbstractContentPageDetailView.php';
require_once 'view/Content/AbstractContentFileColDetailView.php';
require_once 'view/Content/AbstractContentFileColFormView.php';
require_once 'view/Content/ContentFileCol/AbstractContentFileColSliderDetailView.php';
require_once 'view/Content/ContentFileCol/AbstractContentFileColGalleryDetailView.php';
require_once 'view/Content/ContentFileCol/AbstractContentFileColImageDetailView.php';
require_once 'view/Content/ContentFileCol/AbstractContentFileColImageFormView.php';
require_once 'view/AbstractContentIndexView.php';
require_once 'view/AbstractContentSearchFormView.php';
require_once 'view/Content/AbstractContentRefDetailView.php';
require_once 'view/Content/AbstractContentRefFormView.php';
//*** End Abstract

//### Concrete
$curIncludePath = get_include_path();
set_include_path('concrete/modules/Content/' . MODULE_CONTENT_VER);

//Concrete Domain
require_once 'model/Domain/Content/Content.php';

require_once 'model/Domain/Content/ContentText.php';
require_once 'model/Domain/Content/ContentText/ContentTextCode.php';
require_once 'model/Domain/Content/ContentText/ContentTextWYSIWYG.php';
require_once 'model/Domain/Content/ContentText/ContentTextVideo.php';

require_once 'model/Domain/Content/ContentPage.php';

require_once 'model/Domain/Content/ContentFileCol.php';
require_once 'model/Domain/Content/ContentFileCol/ContentFileColSlider.php';
require_once 'model/Domain/Content/ContentFileCol/ContentFileColGallery.php';
require_once 'model/Domain/Content/ContentFileCol/ContentFileColImage.php';
require_once 'model/Domain/ContentAvailableChildType/ContentAvailableChildType.php';
require_once 'model/Domain/ContentInContent/ContentInContent.php';

require_once 'model/Domain/Content/ContentRef.php';

//Concrete Factory
require_once 'model/Factory/ContentFactory.php';
require_once 'model/Factory/ContentAvailableChildTypeFactory.php';
require_once 'model/Factory/ContentInContentFactory.php';

//Concrete View
require_once 'view/content/content_formView.php';
require_once 'view/content/content_detailView.php';
require_once 'view/content/content_textFormView.php';
require_once 'view/content/content_textDetailView.php';
require_once 'view/content/contentText/content_textCodeDetailView.php';
require_once 'view/content/contentText/content_textCodeFormView.php';
require_once 'view/content/contentText/content_textWYSIWYGDetailView.php';
require_once 'view/content/contentText/content_textWYSIWYGFormView.php';
require_once 'view/content/contentText/content_textVideoDetailView.php';
require_once 'view/content/contentText/content_textVideoFormView.php';
require_once 'view/content/content_pageFormView.php';
require_once 'view/content/content_pageDetailView.php';
require_once 'view/content/content_fileColFormView.php';
require_once 'view/content/content_fileColDetailView.php';
require_once 'view/content/contentFileCol/content_fileColSliderDetailView.php';
require_once 'view/content/contentFileCol/content_fileColGalleryDetailView.php';
require_once 'view/content/contentFileCol/content_fileColImageFormView.php';
require_once 'view/content/contentFileCol/content_fileColImageDetailView.php';
require_once 'view/content/content_refFormView.php';
require_once 'view/content/content_refDetailView.php';

require_once 'view/content_indexView.php';
require_once 'view/content_searchFormView.php';
set_include_path($curIncludePath);
//### End Concrete
