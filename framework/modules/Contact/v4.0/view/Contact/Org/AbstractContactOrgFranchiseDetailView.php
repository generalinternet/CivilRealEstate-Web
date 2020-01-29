<?php
/**
 * Description of AbstractContactOrgFranchiseDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactOrgFranchiseDetailView extends AbstractContactOrgDetailView {

    protected function addAssignedToContactsSection(GI_View $view = NULL) {
        if (Permission::verifyByRef('franchise_head_office')) {
            parent::addAssignedToContactsSection($view);
        }
    }

}
