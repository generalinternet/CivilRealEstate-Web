<?php

class RealtyModifyIndexView extends GI_View{
    
//    protected $realtyListings;
//    protected $uiTableView;
//    protected $pageBar;
//    protected $sampleListing;
//    protected $form;
//    protected $searchListings;
//    
//    public function __construct($realtyListings, $uiTableView, $pageBar, $sampleListing, $form, $searchListings = null) {
//        $this->realtyListings = $realtyListings;
//        $this->uiTableView = $uiTableView;
//        $this->pageBar = $pageBar;
//        $this->sampleListing = $sampleListing;
//        $this->form = $form;
//        $this->searchListings = $searchListings;
//        parent::__construct();
//        $this->buildForm();
//        $this->buildView();
//
//        $this->addCSS('resources/css/forms_admin.css');
//        $this->addCSS('resources/css/files_admin.css');
//        $this->addCSS('resources/css/admin.css');
//        $this->addCSS('resources/css/pagination.css');
//    }
//    
//    public function buildView() {
//        $this->addHTML('<div class="content_padding">');
//        
//        $this->addHTML('<h1 class="page_title">Edit MLS Listings</h1>');
//        
//        $this->addHTML('<div class="right_btns">');
//        
//        $this->addHTML($this->form->getForm());
//        
////        if(Permission::verifyByRef('view_realty_listing')){
////            $addURLArray = array(
////                'controller' => 'realty',
////                'action' => 'ModifyListing'
////            );
////
////            $typeRef = $this->sampleListing->getTypeRef();
////            if(!empty($typeRef)){
////                $addURLArray['type'] = $typeRef;
////            }
////            $addURL = GI_URLUtils::buildURL($addURLArray);
////            $this->addHTML('<a href="' . $addURL . '" class="custom_btn" ><span class="icon_wrap"><span class="icon add"></span></span> <span class="btn_text">Add Modified Listing</span></a>');
////        }
//        $this->addHTML('</div>');
//
//        
//        if (sizeof($this->realtyListings) > 0) {
//            $this->addHTML($this->uiTableView->getHTMLView());
//        } else {
//            $this->addHTML('<p>No Listing found.</p>');
//        }
//        
//        $this->addHTML('</div>');
//    }
//    
//    public function buildForm(){
//        $form = $this->form;
//        
//        $form->addField('key', 'text',array(
//            'displayName' => 'Search MLS Number/Address'
//        ));
//        
//        $form->addHTML('<a class="submit_btn">Search</a>');
//        
//        if(count($this->searchListings)){
//            $form->addHTML('<table class="ui_table">
//                                <thead>
//                                    <tr>
//                                        <th>
//                                            Id
//                                        </th>
//                                        <th>
//                                            Address
//                                        </th>
//                                        <th>
//                                            MLS Number
//                                        </th>
//                                        <th>
//                                            Modify Link
//                                        </th>
//                                    </tr>
//                                </thead>');
//
//            foreach($this->searchListings as $listing){
//                $form->addHTML('<tr>
//                                    <td>
//                                        ' . $listing->getProperty('id') . '
//                                    </td>
//                                    <td>
//                                        ' . $listing->getProperty('addr') . '
//                                    </td>
//                                    <td>
//                                        ' . $listing->getProperty('mls_number') . '
//                                    </td>
//                                    <td>
//                                        <a href="' . $listing->getModifyURL() . '">Modify Listing</a>
//                                    </td>
//                                </tr>');
//            }
//            $form->addHTML('</table>');
//        }
//    }
}
