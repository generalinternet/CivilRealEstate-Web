<?php
/**
 * Description of AbstractPaymentFactory  
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'payment';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'income':
                $model = new PaymentIncome($map);
                break;
            case 'expense':
                $model = new PaymentExpense($map);
                break;
            default:
                $model = new Payment($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'income':
                $typeRefs = array('income');
                break;
            case 'expense':
                $typeRefs = array('expense');
                break;
            case 'payment':
                $typeRefs = array('payment');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    public static function getPaymentsByIncome(AbstractIncome $income, $sumOnly = false, $mostRecentOnly = false) {
        $incomeId = $income->getProperty('id');
        $paymentTableName = dbConfig::getDbPrefix() . 'payment';
        $search = PaymentFactory::search()
                ->join('payment_income', 'parent_id', $paymentTableName, 'id', 'pi')
                ->filter('pi.income_id', $incomeId)
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if ($mostRecentOnly) {
            $search->setItemsPerPage(1);
            $search->orderBy('date', 'DESC');
        }
        if ($sumOnly) {
            $sumAmountArray = $search->sum('amount');
            return (float) $sumAmountArray['amount'];
        }
        return $search->select();
    }

    public static function getPaymentsByExpense(AbstractExpense $expense, $sumOnly = false, $mostRecentOnly = false) {
        $expenseId = $expense->getProperty('id');
        $paymentTableName = dbConfig::getDbPrefix() . 'payment';
        $search = PaymentFactory::search()
                ->join('payment_expense', 'parent_id', $paymentTableName, 'id', 'pe')
                ->filter('pe.expense_id', $expenseId)
                ->filter('void', 0)
                ->filter('cancelled', 0);
        if ($mostRecentOnly) {
            $search->setItemsPerPage(1);
            $search->orderBy('date', 'DESC');
        }
        if ($sumOnly) {
            $sumAmountArray = $search->sum('amount');
            return $sumAmountArray['amount'];
        }
        return $search->select();
    }
    
    /**
     * 
     * @param string $typeRef
     * @param AbstractGroupPayment $groupPayment
     * @return AbstractPayment
     */
    public static function createPaymentFromGroupPaymentBalance($typeRef, AbstractGroupPayment $groupPayment) {
        $sortableBalance = (float) $groupPayment->getProperty('sortable_balance');
        if ($sortableBalance > 0) {
            $balance = $groupPayment->getBalance(false, false);
            $groupPaymentDate = $groupPayment->getProperty('date');
            $blankPayment = static::buildNewModel($typeRef);
            $blankPayment->setProperty('group_payment_id', $groupPayment->getProperty('id'));
            $blankPayment->setProperty('amount', $balance);
            $blankPayment->setProperty('date', $groupPaymentDate);
            $blankPayment->setProperty('void', 0);
            $blankPayment->setProperty('cancelled', 0);
            return $blankPayment;
        }
        return NULL;
    }

}
