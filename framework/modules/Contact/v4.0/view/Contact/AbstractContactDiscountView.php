<?php
/**
 * Description of AbstractContactDiscountView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactDiscountView extends GI_View {

    /** @var Contact */
    protected $contact;
    protected $addWrap = true;
    /** @var GI_Form */
    protected $form = NULL;

    public function __construct(AbstractContact $contact) {
        parent::__construct();
        $this->contact = $contact;
        $this->addSiteTitle($this->contact->getName());
        $this->addSiteTitle('Discounts');
    }
    
    /**
     * @param boolean $addWrap
     * @return \AbstractInvStockIndexView
     */
    public function setAddWrap($addWrap){
        $this->addWrap = $addWrap;
        return $this;
    }

    protected function openViewWrap(){
        if($this->addWrap){
            $this->addHTML('<div class="content_padding">');
        }
        return $this;
    }
    
    protected function closeViewWrap(){
        if($this->addWrap){
            $this->addHTML('</div>');
        }
        return $this;
    }
    
    protected function addHeaderSection() {
        $name = $this->contact->getName();
        $avatarString = $this->contact->getAvatarHTML();
        
        $this->addHTML('<h2 class="main_head">' . $avatarString . '<span class="inline_block">' . $name . '</span></h2>');
    }
    
    protected function addButtonsSection() {
        $this->addHTML('<div class="right_btns">');
        $contactId = $this->contact->getId();
        if ($this->contact->isEditable()) {
            $addURL = GI_URLUtils::buildURL(array(
                'controller' => 'inventory',
                'action' => 'addDiscount',
                'contactId' => $contactId
            ));
            $this->addHTML('<a href="' . $addURL . '" title="Add Discount" class="custom_btn open_modal_form">'.GI_StringUtils::getIcon('add').'<span class="btn_text">Add Discount</span></a>');
        }
        $this->addHTML('</div>');
    }
    
    protected function buildView() {
        $this->openViewWrap();
        
        $this->addButtonsSection();
        
        if($this->addWrap){
            $this->addHeaderSection();
        }
        
        $this->addHTML('<h2>Discounts</h2>');
        
        $this->addDiscountTable();

        $this->closeViewWrap();
    }
    
    protected function addDiscountTable(){
        $discounts = $this->contact->getInvDiscounts();
        if($discounts){
            $this->addHTML('<div class="flex_table">');
            $headerRowAdded = false;
            foreach($discounts as $discount){
                $view = $discount->getDetailView();
                if($view){
                    if(!$headerRowAdded){
                        $view->getHeaderRow($this);
                        $headerRowAdded = true;
                    }
                    $view->setAddWrap(false);
                    $this->addHTML($view->getHTMLView());
                }
            }
            $this->addHTML('</div>');
        } else {
            $this->addHTML('<p class="no_model_message">No discounts yet.</p>');
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
