<?php

/**
 * Description of AbstractTag
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.1
 */
abstract class AbstractTag extends GI_Model {

    /** @var AbstractTag[] */
    protected $parentTags = array();
    
    protected $installDefinition = array();
    
    public function getInstallDefinition() {
        return $this->installDefinition;
    }

    public function getDetailView() {
        $detailView = new TagDetailView($this);
        return $detailView;
    }
    
    /** @return string */
    public function getViewURL() {
        return NULL;
    }

    public function getTypesArray() {
        $typesArray = TagFactory::getTypesArray($this->getTypeRef());
        return $typesArray;
    }
    
    /**
     * Used by TagInstaller
     * @param Mixed[] $definition
     */
    public function setInstallDefinition($definition) {
        $this->installDefinition = $definition;
    }

    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
          //  $targetTypeRef = filter_input(INPUT_POST, 'type_ref');
            $targetTypeRef = $this->getTypeRef(); //No type picking on form
            $thisModelId = $this->getProperty('id');
            if (!empty($thisModelId) || $targetTypeRef === $this->getTypeRef()) {
                $title = filter_input(INPUT_POST, 'title');
                $ref = GI_Sanitize::ref($title);
                $colour = filter_input(INPUT_POST, 'colour');
                $position = filter_input(INPUT_POST, 'position');
                if (empty($thisModelId)) {
                    $existingTagArray = TagFactory::search()
                            ->filterByTypeRef($targetTypeRef)
                            ->filter('ref', $ref)
                            ->select();
                    if (!empty($existingTagArray)) {
                        return true;
                    }
                }
                $this->setProperty('tag.title', $title);
                $this->setProperty('tag.ref', $ref);
                $this->setProperty('tag.colour', $colour);
                $this->setProperty('tag.pos', $position);
                $saved = $this->save();
                if($saved){
                    $pTagIds = filter_input(INPUT_POST, 'p_tag_ids');
                    if(!empty($pTagIds)){
                        $parentTags = TagFactory::getByIds($pTagIds);
                        TagFactory::adjustParentTags($this, $parentTags);
                    }
                    return true;
                }
            } else {
                $newTagModel = TagFactory::buildNewModel($targetTypeRef);
                return $newTagModel->handleFormSubmission($form);
            }
        }
        return false;
    }

    public function getIsSystem() {
        if ($this->getProperty('system')) {
            return true;
        }
        return false;
    }

    public function getIsIndexViewable() {
        return false;
    }

    public function getColourHTML() {
        $colourHTML = '<span class="avatar_wrap inline_block" style="background: #' . $this->getProperty('colour') . ';"></span>';
        return $colourHTML;
    }
    
    public function getColourSmallCircleHTML() {
        return '<span class="avatar_wrap circle sml_circle inline_block" style="background: #' . $this->getProperty('colour') . ';"></span>';
    }

    public function getTitle() {
        return $this->getProperty('title');
    }

    public function getEditButtonHTML() {
        if (!$this->isEditable()) {
            return '';
        }
        $editURL = $this->getEditURL();
        return '<a href="' . $editURL . '" class="custom_btn open_modal_form" title="Edit ' . $this->getTitle() . '"><span class="icon_wrap"><span class="icon primary edit"></span></span></a>';
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
                    'controller' => 'tag',
                    'action' => 'edit',
                    'id' => $this->getProperty('id'),
        ));
    }

    public function getBreadcrumbs() {
        return array(
            array(
                'label' => $this->getViewTitle(true),
                'link' => GI_URLUtils::buildURL(array(
                    'controller' => 'tag',
                    'action' => 'index',
                    'type' => $this->getTypeRef(),
                )),
            ),
        );
    }

    /**
     * @return UITableCol[]
     */
    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => '',
                'method_name' => 'getColourHTML',
                'css_class' => 'avatar_cell',
                'css_header_class' => 'avatar_cell'
            ),
            array(
                'header_title' => 'Label',
                'method_name' => 'getTitle',
            ),
            array(
                'header_title' => 'Position',
                'method_name' => 'getPosition',
            ),
            array(
                'header_title' => '',
                'method_name' => 'getEditButtonHTML',
                'css_class' => 'avatar_cell',
                'css_header_class' => 'avatar_cell'
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Colour
            array(
                'method_name' => 'getColourHTML',
            ),
            //Title
            array(
                'method_name' => 'getTitle',
            ),
            //Position
            array(
                'method_name' => 'getPosition',
            ),
            //EditBtn
            array(
                'method_name' => 'getEditButtonHTML',
                'css_class' => 'icon_cell',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }
    
    public function getPosition() {
        return $this->getProperty('pos');
    }
    
    public function getUICardView() {
        $cardView = new UICardView($this);
        $cardView->setTitle($this->getTitle());
//        $cardView->setSummary($this->getEmailAddress());
//        $cardView->setSubtitle($this->getRolesTitleString());
//        $cardView->setAvatarHTML($this->getColourHTML());
        $cardView->setAddTab(true);
        $cardView->setTabColour('#' . $this->getColour());
        $cardView->setTabTitle('&nbsp;');
        $cardView->setTopRight($this->getEditButtonHTML());
        return $cardView;
    }

    public function getAutocompResult($term = NULL, $valueColumn = 'id') {
        $title = $this->getProperty('title');
        $colour = $this->getProperty('colour');
        $colourStyle = '';
        $colourClass = '';
        if (!is_null($colour)) {
            $colourStyle = 'style="background:#' . $colour . ';"';
        } else {
            $colourClass = 'default';
        }
        $colourSpan = '<span class="colour circle ' . $colourClass . '" ' . $colourStyle . '></span>';

        $autoResult = '<span class="result_text">';
        $autoResult .= $colourSpan;
        $autoResult .= '<span class="inline_block">';

        $autoResult .= GI_StringUtils::markTerm($term, $title);
        $autoResult .= '</span>';
        $autoResult .= '</span>';

        $result = array(
            'label' => $title,
            'value' => $this->getProperty($valueColumn),
            'autoResult' => $autoResult,
        );
        return $result;
    }
    
    public function getFormView(GI_Form $form) {
        return new TagFormView($form, $this);
    }
    
    public function getIsAddable() {
        return true;
    }
    
    public function getIsEditable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('edit_tags')){
            return true;
        }
        return false;
    }
    
    public function getIsDeleteable() {
        if($this->getProperty('uid') == Login::getUserId() || Permission::verifyByRef('delete_tags')){
            return true;
        }
        return false;
    }
    
    public function getParentTags(){
        if(!$this->getId()){
            return;
        }
        if(empty($this->parentTags)){
            $this->parentTags = TagFactory::getParentTags($this);
        }
        return $this->parentTags;
    }
    
    public function getPTagString(){
        if(!$this->getId()){
            return;
        }
        $search = TagFactory::getParentTagSearch($this);
        $search->setSelectColumns(array(
            'id'
        ));
        $results = $search->select();
        $ids = array_column($results, 'id');
        $string = implode(',', $ids);
        return $string;
    }
    
    public function getListBarURLAttrs() {
        $attrs = array(
            'controller' => 'tag',
            'action' => 'index',
            'type' => $this->getTypeRef()
        );
        return $attrs;
    }

    public function softDelete() {
        $parentTags = $this->getParentTags();
        if (!empty($parentTags)) {
            foreach ($parentTags as $parentTag) {
                if (!TagFactory::unlinkChildFromParent($this, $parentTag)) {
                    return false;
                }
            }
        }
        return parent::softDelete();
    }
    
    public function save() {
        if(empty($this->getProperty('id')) && empty($this->getProperty('colour'))){
            $this->setProperty('colour', GI_Colour::getRandomColour());
        }
        return parent::save();
    }

}
