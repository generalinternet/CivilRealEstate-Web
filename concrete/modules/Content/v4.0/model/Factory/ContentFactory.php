<?php

class ContentFactory extends AbstractContentFactory {
    
    public static $OPITIONS_INVEST_STATUS = array(
        'new' => 'Funding',
        'funded' => 'Fully Funded',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
    );
    
    public static $OPITIONS_PROPERTY_TYPE = array(
        'residential' => 'Residential',
        'multi_family' => 'Multi-Family',
        'commercial' => 'Commercial',
        'industrial' => 'Industrial',
    );

    public static $OPITIONS_CORPORATION_CATEGORY = array(
        'tech' => 'Tech',
        'renewable' => 'Renewable',
        'applied_sciences' => 'Applied Sciences',
        'micro_finance' => 'Micro Finance',
    );

    public static $INVESTMENT_CATEGORY_COLOR = array(
        'micro_finance' => '#C9328A',
        'tech' => '#E40D0C',
        'renewable' => '#E07A17',
        'applied_sciences' => '#027AD7',
        'commercial' => '#D8D13E',
        'residential' => '#A2D1B7', 
        'multi_family' => '#1E00B0',
        'industrial' => '#3ABCFA',
        'opportunities' => '#FFCA00',
        'start' => '#79775D',
        'kids' => '#B1B292',
        'realestate' => '#498586',
        'realestate_investment_opportunities' => '#94E3FA',
    );

    public static $OPITIONS_REALESTATE_INVEST_TYPE = array(
        'buy_hold' => 'Buy & Hold',
        'buy_fix_hold' => 'Buy, Fix & Hold',
        'buy_flip' => 'Buy & Flip',
        'buy_fix_flip' => 'Buy, Fix & Flip',
    );
    
    protected static $indexableTypeRefs = array(
        'investment',
    );
    public static $HISTORY_INVEST_STATUS = array(
        'funded', 'completed'
    );
    public static $CURRENT_INVEST_STATUS = array(
        'new','funded','in_progress'
    );

    public static $OPITIONS_REALESTATE_INVEST_EXIT_STRATEGY = array(
        'Exit Strategy 1' => 'Exit Strategy 1',
        'Exit Strategy 2' => 'Exit Strategy 2',
    );

    public static $OPTION_REALESTATE_ZONING_DESCIPTIONS = array(
        'Zoning Description 1' => 'Zoning Description 1',
        'Zoning Description 2' => 'Zoning Description 2',
    );
    
    public static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
//            case 'service':
//            case 'website':
//            case 'software':
//            case 'digital_strategy':
//            case 'social_media':
//            case 'search':
//                $model = new ContentBaseService($map);
//                break;
//            case 'web_vid':
//                $model = new ContentBaseServiceWebVid($map);
//                break;
//            case 'web_vid_loc':
//                $model = new ContentBaseWebVidLoc($map);
//                break;
//            case 'web_vid_int':
//                $model = new ContentBaseWebVidInt($map);
//                break;
//            case 'web_vid_cut':
//                $model = new ContentBaseWebVidCut($map);
//                break;
//            case 'web_vid_extra':
//                $model = new ContentBaseWebVidExtra($map);
//                break;
//            case 'test':
//            case 'test_sub':
//                return new ContentBase($map);
//                break;
            case 'investment':
                $model = new ContentInvestment($map);
                break;
            case 'corporation':
            case 'start':
            case 'opportunities':
            case 'kids':
            case 'applied_sciences':
            case 'micro_finance':
                $model = new ContentInvestmentCorporation($map);
                break;
            case 'realestate':
                $model = new ContentInvestmentRealestate($map);
                break;
            case 'realestate_investment_opportunities':
                $model = new ContentInvestmentRealestateInvestmentOpportunity($map);
                break;
            default:
                return parent::buildModelByTypeRef($typeRef, $map);
                break;
        }
        
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
//            case 'service':
//                $typeRefs = array('base', 'service', 'service');
//                break;
//            case 'website':
//                $typeRefs = array('base', 'service', 'website');
//                break;
//            case 'web_vid':
//                $typeRefs = array('base', 'service', 'web_vid');
//                break;
//            case 'software':
//                $typeRefs = array('base', 'service', 'software');
//                break;
//            case 'digital_strategy':
//                $typeRefs = array('base', 'service', 'digital_strategy');
//                break;
//            case 'social_media':
//                $typeRefs = array('base', 'service', 'social_media');
//                break;
//            case 'search':
//                $typeRefs = array('base', 'service', 'search');
//                break;
//            case 'web_vid_loc':
//                $typeRefs = array('base', 'web_vid_loc');
//                break;
//            case 'web_vid_int':
//                $typeRefs = array('base', 'web_vid_int');
//                break;
//            case 'web_vid_cut':
//                $typeRefs = array('base', 'web_vid_cut');
//                break;
//            case 'web_vid_extra':
//                $typeRefs = array('base', 'web_vid_extra');
//                break;
//            case 'test':
//                $typeRefs = array('base', 'test', 'test');
//                break;
//            case 'test_sub':
//                $typeRefs = array('base', 'test', 'test_sub');
//                break;
            case 'investment':
                $typeRefs = array('investment', 'investment');
                break;
            case 'corporation':
                $typeRefs = array('investment', 'corporation', 'corporation');
                break;
            case 'start':
                $typeRefs = array('investment', 'corporation', 'start');
                break;
            case 'opportunities':
                $typeRefs = array('investment', 'corporation', 'opportunities');
                break;
            case 'kids':
                $typeRefs = array('investment', 'corporation', 'kids');
                break;
            case 'realestate_investment_opportunities':
                $typeRefs = array('investment', 'realestate', 'realestate_investment_opportunities');
                break;
            case 'realestate':
                $typeRefs = array('investment', 'realestate', 'realestate');
                break;
            default:
                $typeRefs = parent::getTypeRefArrayFromTypeRef($typeRef);
                break;
        }
        return $typeRefs;
    }
    
    /**
     * Check if the ref exists in the content table.
     * If exists, add index number to the original ref
     * @param string $ref
     * @return string
     */
    public static function getUniqueRef($ref) {
        $existingContents = static::search()
                ->filter('ref', $ref)
                ->select();
        if (!empty($existingContents)) {
            $ref .= '_'.count($existingContents);
        } 
        return $ref;
    }
    
    public static function getInvestmentContents($typeRef = 'investment', $general = true, $investStatusArray = array(), $idsAsKey = false, $isFeatured = false) {
        $dataSearch = static::search()
                ->filterByTypeRef($typeRef, $general);
        if (empty($investStatusArray)) {
            $investStatusArray = ContentFactory::$CURRENT_INVEST_STATUS;
        }
        $dataSearch->filterIn('content_investment.invest_status', $investStatusArray);
        if($isFeatured){
            $dataSearch->filterIn('content_investment.is_featured_investment', array(1));
        }
        $dataSearch->orderBy('content_investment.due_date', 'DESC');
        return $dataSearch->select($idsAsKey);
    }
    
    public static function formatMoney($amount, $shortTerm = true, $unit = '$', $withCommas = true) {
        $string = '<span class="price-unit">'.$unit.'</span><span class="price">';
        if ($shortTerm) {
            if ($amount <1000) {
                $string .= number_format($amount);
            } else if ($amount <1000000) {
                $string .= number_format($amount/1000).'K';
            } else if ($amount <1000000000) {
                $string .= number_format($amount/1000000, 2).'M';
            } else if ($amount <1000000000000) {
                $string .= number_format($amount/1000000000, 2).'B';
            } else {
                $string .= number_format($amount);
            }
        } else {
            GI_StringUtils::formatMoney($amount, $withCommas);
        }
        
        $string .= '</span>';
        return $string;
    }
    
    public static function getFeaturedYoutubeVideoId(AbstractContent $content) {
        $featuredYoutubeVideoURL = $content->getProperty('content_investment.featured_youtube_video_url');
        if (!empty($featuredYoutubeVideoURL)) {
            $youTubeId = preg_replace('~
            # Match non-linked youtube URL in the wild. (Rev:20130823)
            https?://         # Required scheme. Either http or https.
            (?:[0-9A-Z-]+\.)? # Optional subdomain.
            (?:               # Group host alternatives.
              youtu\.be/      # Either youtu.be,
            | youtube\.com    # or youtube.com followed by
              \S*             # Allow anything up to VIDEO_ID,
              [^\w\-\s]       # but char before ID is non-ID char.
            )                 # End host alternatives.
            ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
            (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
            (?!               # Assert URL is not pre-linked.
              [?=&+%\w.-]*    # Allow URL (query) remainder.
              (?:             # Group pre-linked alternatives.
                [\'"][^<>]*>  # Either inside a start tag,
              | </a>          # or inside <a> element text contents.
              )               # End recognized pre-linked alts.
            )                 # End negative lookahead assertion.
            [?=&amp;+%\w.-]*        # Consume any URL (query) remainder.
            ~ix', 
            '$1',
            $featuredYoutubeVideoURL);
            return $youTubeId;
        }
        return '';
    }

    public static function getYearBuiltOptions(){
        $ranges = range(1975, date('Y'));
        $newArr = array();
        foreach($ranges as $year){
            $newArr[$year] = $year;
        }
        return $newArr;
    }
}

