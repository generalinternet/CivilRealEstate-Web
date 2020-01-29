<?php
/**
 * Description of AbstractContactIndDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.2
 */
abstract class AbstractContactIndDetailView extends AbstractContactDetailView {

    protected $addDiscounts = true;
// $addSalesOrders default value is changed to true    
//    protected $addSalesOrders = true;
    
    protected function addUserInfoSection(GI_View $view = NULL) {
        if (is_null($view)) {
            $view = $this;
        }
        $user = $this->contact->getUser();
        if (!empty($user->getProperty('id'))) {
            $userEmail = $user->getProperty('email');
            $view->addHTML('<div class="content_block_wrap">');
            $view->addContentBlock($userEmail, 'Login Email');
            $view->addHTML('</div>');
        }
    }

    protected function addWindowTitle() {
        $name = $this->contact->getRealName();
        $fullyQualifiedName = $this->contact->getFullyQualifiedName();
        $avatarString = $this->contact->getAvatarHTML();
        $mainTitle = $avatarString . '<span class="inline_block">' . $name;
        if (ProjectConfig::getContactUseFullyQualifiedName() && !empty($fullyQualifiedName)) {
            $mainTitle .= '<span class="sub_head"><span class="thin" title="Fully Qualified Name">FQN</span> ' . $fullyQualifiedName . '</span>';
        }
        $mainTitle .= '</span>';
        $this->addMainTitle($mainTitle, 'main_head has_avatar');
        return $this;
    }

}
