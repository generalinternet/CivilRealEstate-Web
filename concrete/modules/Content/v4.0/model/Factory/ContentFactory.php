<?php

class ContentFactory extends AbstractContentFactory {
    
    public static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'service':
            case 'website':
            case 'software':
            case 'digital_strategy':
            case 'social_media':
            case 'search':
                $model = new ContentBaseService($map);
                break;
            case 'web_vid':
                $model = new ContentBaseServiceWebVid($map);
                break;
            case 'web_vid_loc':
                $model = new ContentBaseWebVidLoc($map);
                break;
            case 'web_vid_int':
                $model = new ContentBaseWebVidInt($map);
                break;
            case 'web_vid_cut':
                $model = new ContentBaseWebVidCut($map);
                break;
            case 'web_vid_extra':
                $model = new ContentBaseWebVidExtra($map);
                break;
            case 'web_vid_gear':
                $model = new ContentBaseWebVidGear($map);
                break;
            case 'website_features':
                $model = new ContentBaseWebsiteFeatures($map);
                break;
            case 'website_content':
                $model = new ContentBaseWebsiteContent($map);
                break;
            case 'website_page':
                $model = new ContentBaseWebsitePage($map);
                break;
            case 'website_marketing':
                $model = new ContentBaseWebsiteMarketing($map);
                break;
            case 'test':
            case 'test_sub':
                return new ContentBase($map);
                break;
            default:
                return parent::buildModelByTypeRef($typeRef, $map);
                break;
        }
        
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'service':
                $typeRefs = array('base', 'service', 'service');
                break;
            case 'website':
                $typeRefs = array('base', 'service', 'website');
                break;
            case 'web_vid':
                $typeRefs = array('base', 'service', 'web_vid');
                break;
            case 'software':
                $typeRefs = array('base', 'service', 'software');
                break;
            case 'digital_strategy':
                $typeRefs = array('base', 'service', 'digital_strategy');
                break;
            case 'social_media':
                $typeRefs = array('base', 'service', 'social_media');
                break;
            case 'search':
                $typeRefs = array('base', 'service', 'search');
                break;
            case 'web_vid_loc':
                $typeRefs = array('base', 'web_vid_loc');
                break;
            case 'web_vid_int':
                $typeRefs = array('base', 'web_vid_int');
                break;
            case 'web_vid_cut':
                $typeRefs = array('base', 'web_vid_cut');
                break;
            case 'web_vid_extra':
                $typeRefs = array('base', 'web_vid_extra');
                break;
            case 'web_vid_gear':
                $typeRefs = array('base', 'web_vid_gear');
                break;
            case 'website_features':
                $typeRefs = array('base', 'website_features');
                break;
            case 'website_content':
                $typeRefs = array('base', 'website_content');
                break;
            case 'website_page':
                $typeRefs = array('base', 'website_page');
                break;
            case 'website_marketing':
                $typeRefs = array('base', 'website_marketing');
                break;
            case 'test':
                $typeRefs = array('base', 'test', 'test');
                break;
            case 'test_sub':
                $typeRefs = array('base', 'test', 'test_sub');
                break;
            default:
                $typeRefs = parent::getTypeRefArrayFromTypeRef($typeRef);
                break;
        }
        return $typeRefs;
    }
    
}
