<?php
/**
 * Description of AbstractSubscriptionDetailView
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractSubscriptionDetailView extends MainWindowView {

    /** @var GI Form */
    protected $form;
    /** @var AbstractSubscription */
    protected $subscription;
    protected $isSelected = false;

    public function __construct(AbstractSubscription $subscription) {
        parent::__construct();
        $this->subscription = $subscription;
    }
    
    public function setForm(GI_Form $form){
        $this->form = $form;
        return $this;
    }
    
    public function setIsSelected($isSelected){
        $this->isSelected = $isSelected;
        return $this;
    }

    protected function addViewBodyContent() {
//        $subscription = $this->subscription;
//        $this->addHTML('<h3>' . $subscription->getProperty('title') . '</h3>');
//        $this->addHTML('<p>' . $subscription->getProperty('description') . '</p>');
//        $this->addHTML('<h4 class="value">$ ' . $subscription->getProperty('price') . '</h4>');
        
        $this->openPackageOption();
        $this->openPackageOptionContent();
        
        $this->addPackageOptionTitle();
        $this->addPackageOptionDesc();
        
        $this->addPackageOptionPrice();
        
        $this->addPackageOptionRadio();
        
        $this->closePackageOptionContent();
        $this->closePackageOption();
    }
    
    protected function openPackageOption(){
        $wrapSubclass = 'block';
        
        if ($this->form && $this->isSelected) {
            $wrapSubclass .= ' selected ';
        }
        
        $this->addHTML('<div class="contact_application package_option ' . $wrapSubclass . '">');
        return $this;
    }
    protected function closePackageOption(){
        $this->addHTML('</div>');
        return $this;
    }
    protected function openPackageOptionContent(){
        $this->addHTML('<div class="package_option_content">');
        return $this;
    }
    protected function closePackageOptionContent(){
        $this->addHTML('</div>');
        return $this;
    }
    protected function addPackageOptionTitle(){
        $title = $this->subscription->getTitle();
        $this->addHTML('<h3>' . $title . '</h3>');
        return $this;
    }
    protected function addPackageOptionDesc(){
        $description = GI_StringUtils::nl2brHTML($this->subscription->getDescription());
        $this->addHTML('<div class="package_desc">' . $description . '</div>');
        return $this;
    }
    protected function addPackageOptionPrice(){
        $this->addHTML('<span class="package_price"><span class="symbol">$</span><span class="value">' . $this->subscription->getProperty('price') . '</span></span>');
        return $this;
    }
    protected function addPackageOptionRadio(){
        if(!$this->form){
            return;
        }
        $value = NULL;
        $subscriptionId = $this->subscription->getId();
        if($this->isSelected){
            $value = $subscriptionId;
        }
        $this->form->addField('subscription_id', 'radio', array(
            'options' => array(
                $subscriptionId => '',
            ),
            'showLabel' => false,
            'value' => $value,
            'stayOn' => true,
        ));
        return $this;
    }
    
    public function addHTML($html) {
        if($this->form){
            return $this->form->addHTML($html);
        }
        return parent::addHTML($html);
    }

}
