<?php
/**
 * Description of AbstractRuleGroupDiscount
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupDiscount extends AbstractRuleGroup {

    protected static $subjectPropertyOptions = array(
        'units_purchased_on_order' => array(
            'title' => 'Units Purchased on Order',
            'method_name' => 'getUnitsPurchasedOnOrder'
        ),
//        'units_purchased' => array(
//            'title' => 'Units Purchased',
//            'method_name' => 'getUnitsPurchased'
//        ),
//        'amount_spent' => array(
//            'title' => 'Amount Spent',
//            'method_name' => 'getAmountSpent'
//        ),
    );

    /** @return AbstractInvDiscount */
    public function getSubjectModel() {
        return $this->subjectModel;
    }

    /** @param AbstractInvDiscount $model */
    public function setSubjectModel(GI_Model $model) {
        $this->subjectModel = $model;
    }

}
