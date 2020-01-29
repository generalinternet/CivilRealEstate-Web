<?php
/**
 * Description of AbstractTagInstaller
 * 
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractTagInstaller extends GI_Object {

    protected static $tagType = 'tag';
    protected static $tagDefinitions = array();
    //Reporting - //TODO
    protected $newTagsAddedReport = array();
    protected $oldTagsRemoveReport = array();
    protected $newLinksCreatedReport = array();
    protected $oldLinksSeveredReport = array();
    protected $failures = array();

    public function installTags() {
        $tagsToRemove = $this->getExistingTagsByRef();
        $tags = array();
        $definitions = static::$tagDefinitions;
        if (!empty($definitions)) {
            foreach ($definitions as $definition) {
                $ref = $definition['ref'];
                if (isset($tagsToRemove[$ref])) {
                    $tag = $tagsToRemove[$ref];
                    unset($tagsToRemove[$ref]);
                    if (!$this->updateTagPropertiesFromDefinition($tag, $definition)) {
                        //TODO - add failure
                        continue;
                    }
                } else {
                    $tag = $this->installTag($definition);
                }
                if (empty($tag)) {
                    //TODO - add failure string
                    continue;
                }
                $tag->setInstallDefinition($definition);
                $tags[] = $tag;
            }
        }
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $installDefinition = $tag->getInstallDefinition();
                if (!empty($installDefinition) && isset($installDefinition['parent_tag_refs'])) {
                    if (!$this->updateTagParentConnections($tag, $installDefinition['parent_tag_refs'])) {
                        continue;
                    }
                }
            }
        }
        if (!empty($tagsToRemove)) {
            foreach ($tagsToRemove as $tagToRemove) {
                if (!$tagToRemove->softDelete()) {
                    //TODO - failure message
                }
                //TODO - old tags remove report - add string
            }
        }
        return true;
    }
    
    protected function getExistingTagsByRef() {
        $search = TagFactory::search();
        $search->filterByTypeRef(static::$tagType);
        $tags = $search->select();
        $tagsByRef = array();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $tagsByRef[$tag->getProperty('ref')] = $tag;
            }
        }
        return $tagsByRef;
    }

    /**
     * @param Mixed[] $tagDefinition
     * @return AbstractTag
     */
    protected function installTag($tagDefinition) {
        $tag = NULL;
        $ref = $tagDefinition['ref'];
        $search = TagFactory::search();
        $search->filterByTypeRef(static::$tagType)
                ->filter('ref', $ref);
        $results = $search->select();
        if (!empty($results)) {
            $tag = $results[0];
        }
        if (empty($tag)) {
            $softDeletedSearch = TagFactory::search();
            $softDeletedSearch->filter('status', 0)
                    ->filterByTypeRef(static::$tagType)
                    ->filter('ref', $ref);
            $softDeletedResults = $softDeletedSearch->select();
            if (!empty($softDeletedResults)) {
                $softDeletedTag = $softDeletedResults[0];
                if ($softDeletedTag->unSoftDelete() && $tag->save()) {
                    $tag = $softDeletedTag;
                }
            }
        }
        if (empty($tag)) {
            $tag = TagFactory::buildNewModel(static::$tagType);
        }
        if (!$this->updateTagPropertiesFromDefinition($tag, $tagDefinition)) {
            return NULL;
        }
        return $tag;
    }

    protected function updateTagPropertiesFromDefinition(AbstractTag $tag, $tagDefinition) {
        $tag->setProperty('ref', $tagDefinition['ref']);
        $tag->setProperty('title', $tagDefinition['title']);
        $tag->setProperty('colour', $tagDefinition['colour']);
        $tag->setProperty('system', $tagDefinition['system']);
        $tag->setProperty('pos', $tagDefinition['pos']);
        return $tag->save();
    }
    
    /**
     * 
     * @param AbstractTag $tag
     * @param String $parentTagRefs
     * @return boolean Description
     */
    protected function updateTagParentConnections(AbstractTag $tag, $parentTagRefs) {
       $parentTagsToRemove = $this->getExistingParentTagsByRef($tag);
        if (!empty($parentTagRefs)) {
            foreach ($parentTagRefs as $parentTagRef) {
                if (isset($parentTagsToRemove[$parentTagRef])) {
                    unset($parentTagsToRemove[$parentTagRef]);
                } else {
                    $parentTag = TagFactory::getModelByRefAndTypeRef($parentTagRef, static::$tagType);
                    if (empty($parentTag)) {
                        continue;
                        //TODO - failure message
                    } 
                    if (!TagFactory::linkChildToParent($tag, $parentTag)) {
                        //TODO - failure message
                    }
                }
            }
        }

       if (!empty($parentTagsToRemove)) {
           foreach ($parentTagsToRemove as $parentTagToRemove) {
               if (!TagFactory::unlinkChildFromParent($tag, $parentTagToRemove)) {
                   //TODO - error message
                   continue;
               } else {
                   //old link severed report
               }
           }
       }
        
        return true;
    }
    
    protected function getExistingParentTagsByRef(AbstractTag $tag) {
        $existingParentTagsByRef = array();
        $parentTags = $tag->getParentTags();
        if (!empty($parentTags)) {
            foreach ($parentTags as $parentTag) {
                $existingParentTagsByRef[$parentTag->getProperty('ref')] = $parentTag;
            }
        }
        
        return $existingParentTagsByRef;
    }

}
