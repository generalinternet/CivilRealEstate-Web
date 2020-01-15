<?php

class ContentInvestmentDetailView extends ContentDetailView{

    /** @var ContentInvestment  */
    protected $content;

    protected function buildViewGuts(){
        $this->addHTML('<div class="auto_columns thirds">');
            $this->buildMediaSection();
            $this->addHTML('<div class="content_group">');
                $this->addHTML('<h2 class="content_group_title">Uploaded Files</h2>');
                parent::buildViewGuts();
            $this->addHTML('</div>');    
        
        $this->addHTML('</div>');

        $this->addHTML('<div class="auto_columns thirds">');
        $this->addHTML('<div class="content_group">');
        $this->addContentBlockWithWrap($this->content->isFeaturedListing(), 'Is featured listing');
        $this->addHTML('</div>');
        $this->addHTML('</div>');
        $this->addHTML('</div>');

        $this->buildInvestmentDetailSection();
//        $this->addHTML('<div class="auto_columns halves">');
//            $this->buildInvestmentDetailSection();
//            $this->buildInvestmentHistorySection();
//        $this->addHTML('</div>');
    }
    
    protected function buildMediaSection(){
        $this->addHTML('<div class="content_group">');
            $this->addHTML('<h2 class="content_group_title">Uploaded Images</h2>');
            $this->addHTML('<div class="auto_columns halves">');
                $featuredImageFolder = $this->content->getFeaturedImageFolder();
                if($featuredImageFolder){
                    $this->addHTML('<div class="content_block_wrap">');
                        $this->addHTML('<h3 class="content_block_title">Featured Image</h3>');
                        $this->addImageFileViews($featuredImageFolder);
                    $this->addHTML('</div>');
                }
                
                // $bannerImageFolder = $this->content->getBannerImageFolder();
                // if($bannerImageFolder){
                //     $this->addHTML('<div class="content_block_wrap banner_image_content">');
                //         $this->addHTML('<h3 class="content_block_title">Banner Image</h3>');
                //         $this->addImageFileViews($bannerImageFolder);
                //     $this->addHTML('</div>');
                // }

                $downloadThumbImgFolder = $this->content->getDownloadThumbImgFolder();
                if($downloadThumbImgFolder){
                    $this->addHTML('<div class="content_block_wrap banner_image_content">');
                        $this->addHTML('<h3 class="content_block_title">Download Thumbnail Image</h3>');
                        $this->addImageFileViews($downloadThumbImgFolder);
                    $this->addHTML('</div>');
                }
            $this->addHTML('</div>');
        $this->addHTML('</div>');
        
        $this->addHTML('<div class="content_group">');
            $this->addHTML('<h2 class="content_group_title">Featured Video</h2>');
            $featuredVideoHTML = $this->content->getFeaturedYoutubeVideoEmbedHTML();
            if(!empty($featuredVideoHTML)){
                $this->addHTML('<div class="content_block_wrap">');
                    $this->addHTML($featuredVideoHTML);
                $this->addHTML('</div>');
            }
        $this->addHTML('</div>');
            
    }
    
    protected function addImageFileViews($folder){
        if($folder){
            $this->addHTML('<div class="content_files">');
            $files = $folder->getFiles();
            foreach($files as $file){
                $fileView = $file->getView();
                $fileView->setIsDeleteable(false);
                $fileView->setIsRenamable(false);
                $this->addHTML($fileView->getHTMLView());
            }
            $this->addHTML('</div>');
        }
    }
    
    protected function buildInvestmentDetailSection(){
        $this->addHTML('<div class="content_group">');
            $this->addHTML('<h2 class="content_group_title">Investment Details</h2>');
            $this->addHTML('<div class="auto_columns quarters">');
                $this->addInvestmentDetailFields();
            $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
    protected function addInvestmentDetailFields(){
        $this->addContentBlockWithWrap($this->content->getInvestmentStatusTitle(), 'Project Status');
        $this->addContentBlockWithWrap($this->content->getDisplayTargetAmt(), 'Target Amount');
        $this->addContentBlockWithWrap($this->content->getDisplayInvestedAmt(), 'Invested Amount');
        $this->addContentBlockWithWrap($this->content->getDisplayFundsRate(), 'Funded');
        $this->addContentBlockWithWrap($this->content->getDisplayDueDate(), 'Due Date');
    }
    
    protected function buildInvestmentHistorySection(){
        $this->addHTML('<div class="content_group has_right_btns">');
            $loginUser = Login::getUser();
            if(!empty($loginUser) && Permission::verifyByRef('add_investment_histories')){
                $addHistoryURL = GI_URLUtils::buildURL(array(
                    'controller' => 'content',
                    'action' => 'addInvestmentHistory',
                    'userId' => $loginUser->getProperty('id'),
                ));
                $this->addHTML('<div class="right_btns"><a href="'.$addHistoryURL.'" class="custom_btn open_modal_form">'.GI_StringUtils::getIcon('plus').' Investment</a></div>');
            }
            $this->addHTML('<h2 class="content_group_title">Investment History</h2>');
        $this->addHTML('</div>');
    }
}
