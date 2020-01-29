<?php
/**
 * Description of AbstractContactOrgDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.1.0
 */
abstract class AbstractContactOrgDetailView extends AbstractContactDetailView {
    
    protected $addDiscounts = true;
    protected $addInterestRates = true;
// $addSalesOrders and $addPurchaseOrders default value is changed to true 
//    protected $addSalesOrders = true;
//    protected $addPurchaseOrders = true;

    protected function addWindowTitle(){
        $name = $this->contact->getRealName();
        $doingBusAs = $this->contact->getProperty('contact_org.doing_bus_as');
        $fullyQualifiedName = $this->contact->getFullyQualifiedName();
        $avatarString = $this->contact->getAvatarHTML();
        
        $mainTitle = $avatarString . '<span class="inline_block">' . $name;
        if (!empty($doingBusAs)) {
            $mainTitle .= '<span class="sub_head"><span class="thin" title="Doing Business As">DBA</span> ' . $doingBusAs . '</span>';
        }
        if (ProjectConfig::getContactUseFullyQualifiedName() && !empty($fullyQualifiedName)) {
            $mainTitle .= '<span class="sub_head"><span class="thin" title="Fully Qualified Name">FQN</span> ' . $fullyQualifiedName . '</span>';
        }
        $mainTitle .= '</span>';
        $this->addMainTitle($mainTitle, 'main_head has_avatar');
        return $this;
    }

}
