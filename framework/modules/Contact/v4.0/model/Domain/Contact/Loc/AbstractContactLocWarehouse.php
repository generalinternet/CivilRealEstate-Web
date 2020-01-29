<?php
/**
 * Description of AbstractContactLocWarehouse
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.1
 */
abstract class AbstractContactLocWarehouse extends AbstractContactLoc {

    public function getViewTitle($plural = true) {
        $title = 'Warehouse';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getDetailView() {
        $detailView = new ContactLocWarehouseDetailView($this);
        return $detailView;
    }

    public function getFormView(GI_Form $form) {
        return new ContactLocWarehouseFormView($form, $this);
    }

    public function getIsAddable() {
        if (Permission::verifyByRef('add_warehouses')) {
            return true;
        }
        return false;
    }

    public function getIsEditable() {
        if (Permission::verifyByRef('edit_warehouses')) {
            return true;
        }
        return false;
    }

    /**
     * @param string $contactTypeRef
     * @return array
     */
    public function getBreadcrumbs($contactTypeRef = NULL) {
        $breadcrumbs = array();
        
        $bcLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'warehouseIndex'
        ));
        $breadcrumbs[] = array(
            'label' => 'All Warehouses',
            'link' => $bcLink
        );
        
        $contactId = $this->getId();
        if (!is_null($contactId)) {
            
            $parent = $this->getParentContactOrg();
            if($parent){
                $breadcrumbs[] = array(
                    'label' => $parent->getName(),
                    'link' => $parent->getViewURL()
                );
            }
            
            $breadcrumbs[] = array(
                'label' => $this->getName(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }

    public function setAddressModel(AbstractContactInfoAddress $addressModel) {
        $this->addressModel = $addressModel;
    }

}
