<?php
/**
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

define('MODULE_ACCOUNTING_VER', 'v4.0'); //TEMP - during dev w/ 2 accounting modules

//*** Abstract

// Abstract Domain
require_once 'model/Domain/AccountingElement/AbstractAccountingElement.php';
require_once 'model/Domain/Payment/AbstractPayment.php';
require_once 'model/Domain/Payment/AbstractPaymentExpense.php';
require_once 'model/Domain/Payment/AbstractPaymentIncome.php';
require_once 'model/Domain/Expense/AbstractExpense.php';
require_once 'model/Domain/Expense/AbstractExpenseBill.php';
require_once 'model/Domain/ExpenseItem/AbstractExpenseItem.php';
require_once 'model/Domain/ExpenseItemGroup/AbstractExpenseItemGroup.php';
require_once 'model/Domain/Income/AbstractIncome.php';
require_once 'model/Domain/Income/AbstractIncomeInvoice.php';
require_once 'model/Domain/IncomeItem/AbstractIncomeItem.php';
require_once 'model/Domain/GroupPayment/AbstractGroupPayment.php';
require_once 'model/Domain/GroupPayment/AbstractGroupPaymentCredit.php';
require_once 'model/Domain/GroupPayment/AbstractGroupPaymentRefund.php';
require_once 'model/Domain/GroupPayment/AbstractGroupPaymentImported.php';
require_once 'model/Domain/PaymentAccount/AbstractPaymentAccount.php';
require_once 'model/Domain/QBTaxCode/AbstractQBTaxCode.php';
require_once 'model/Domain/QBTaxRate/AbstractQBTaxRate.php';
require_once 'model/Domain/QBAccount/AbstractQBAccount.php';
require_once 'model/Domain/QBProduct/AbstractQBProduct.php';
require_once 'model/Domain/QBJournalEntry/AbstractQBJournalEntry.php';
require_once 'model/Domain/QBJournalEntry/AbstractQBJournalEntrySalesOrderLine.php';
require_once 'model/Domain/QBJournalEntry/AbstractQBJournalEntryReturnLineReturned.php';
require_once 'model/Domain/QBJournalEntry/AbstractQBJournalEntryReturnLineDamaged.php';
require_once 'model/Domain/QBJournalEntry/AbstractQBJournalEntryInvAdjustmentWaste.php';
require_once 'model/Domain/QBJournalEntryCat/AbstractQBJournalEntryCat.php';
require_once 'model/Domain/RegionQBDefault/AbstractRegionQBDefault.php';
require_once 'model/Domain/RegionQBDefault/AbstractRegionQBDefaultTaxCode.php';
require_once 'model/Domain/RegionQBDefault/AbstractRegionQBDefaultProduct.php';

// Abstract Factory
require_once 'model/Factory/AbstractExpenseFactory.php';
require_once 'model/Factory/AbstractExpenseItemFactory.php';
require_once 'model/Factory/AbstractExpenseItemGroupFactory.php';
require_once 'model/Factory/AbstractIncomeFactory.php';
require_once 'model/Factory/AbstractIncomeItemFactory.php';
require_once 'model/Factory/AbstractPaymentFactory.php';
require_once 'model/Factory/AbstractGroupPaymentFactory.php';
require_once 'model/Factory/AbstractPaymentAccountFactory.php';
require_once 'model/Factory/AbstractAccReportFactory.php';
require_once 'model/Factory/AbstractQBTaxCodeFactory.php';
require_once 'model/Factory/AbstractQBTaxRateFactory.php';
require_once 'model/Factory/AbstractQBAccountFactory.php';
require_once 'model/Factory/AbstractQBProductFactory.php';
require_once 'model/Factory/AbstractQBJournalEntryFactory.php';
require_once 'model/Factory/AbstractQBJournalEntryCatFactory.php';
require_once 'model/Factory/AbstractRegionQBDefaultFactory.php';

//Abstract Utility
require_once 'model/Utility/Reports/AbstractAccReport.php';
require_once 'model/Utility/Reports/AbstractAccReportOverview.php';
require_once 'model/Utility/Reports/AbstractAccReportQB.php';
require_once 'model/Utility/Reports/AbstractAccReportQBApAgingSummary.php';
require_once 'model/Utility/Reports/AbstractAccReportQBArAgingSummary.php';
require_once 'model/Utility/Reports/AbstractAccReportQBCustomerBalance.php';
require_once 'model/Utility/Reports/AbstractAccReportQBProfitAndLoss.php';
require_once 'model/Utility/Reports/AbstractAccReportQBSalesByCustomer.php';
require_once 'model/Utility/Reports/AbstractAccReportSalesBySKU.php';
require_once 'model/Utility/Reports/AbstractAccReportSalesBySalesperson.php';
require_once 'model/Utility/Reports/AbstractAccReportInvCogsSales.php';
require_once 'model/Utility/Reports/AbstractAccReportOrderValues.php';
require_once 'model/Utility/Reports/AbstractAccReportSalesByRegion.php';
require_once 'model/Utility/Reports/AbstractAccReportSalesComparison.php';

// Abstract View
require_once 'view/GroupPayment/AbstractPreApplyGroupPaymentFormView.php';
require_once 'view/GroupPayment/AbstractApplyGroupPaymentToInvoicesFormView.php';
require_once 'view/GroupPayment/AbstractApplyGroupPaymentToBillsFormView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentIndexView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentDetailView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentFormView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentSearchFormView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentCreditSearchFormView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentRefundFormView.php';
require_once 'view/GroupPayment/AbstractCreditIndexView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentCreditFormView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentCreditDetailView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentCreditOutputView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentRefundDetailView.php';
require_once 'view/GroupPayment/AbstractGroupPaymentImportedIndexView.php';
require_once 'view/GroupPayment/AbstractImportPaymentsFileFormView.php';
require_once 'view/Payment/AbstractPaymentTableView.php';
require_once 'view/Payment/AbstractPaymentFormView.php';
require_once 'view/Payment/AbstractPaymentExpenseFormView.php';
require_once 'view/Payment/AbstractPaymentIncomeFormView.php';
require_once 'view/Reports/AbstractAccReportDetailView.php';
require_once 'view/Reports/AbstractAccReportsFormView.php';
require_once 'view/Reports/AbstractAccReportsView.php';
require_once 'view/Reports/AbstractAccReportQBTableView.php';
require_once 'view/Reports/AbstractAccReportQBDetailView.php';
require_once 'view/Reports/AbstractAccReportOverviewDetailView.php';
require_once 'view/Reports/AbstractAccReportSalesBySKUDetailView.php';
require_once 'view/Reports/AbstractAccReportSalesBySalespersonDetailView.php';
require_once 'view/Reports/AbstractAccReportInvCogsSalesDetailView.php';
require_once 'view/Reports/AbstractAccReportSummaryView.php';
require_once 'view/Reports/AbstractAccReportOrderValuesDetailView.php';
require_once 'view/Reports/AbstractAccReportSalesByRegionDetailView.php';
require_once 'view/Reports/AbstractAccReportSalesComparisonDetailView.php';
require_once 'view/PaymentAccount/AbstractPaymentAccountIndexView.php';
require_once 'view/PaymentAccount/AbstractPaymentAccountFormView.php';
require_once 'view/AbstractVoidFormView.php';
require_once 'view/AbstractAccountsPayableIndexView.php';
require_once 'view/AbstractAccountsReceivableIndexView.php';
require_once 'view/AbstractAccountingExportFormView.php';
require_once 'view/AbstractImportPaymentsFormView.php';
require_once 'view/Quickbooks/AbstractExportAdjustmentsToQuickbooksIndexView.php';
require_once 'view/Quickbooks/AbstractExportAdjustmentsToQuickbooksConfirmView.php';
require_once 'view/Quickbooks/AbstractExportAdjustmentsToQuickbooksContentView.php';
require_once 'view/Quickbooks/AdjustmentsExports/AbstractExportAdjustmentsToQBFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/AbstractQBExportSalesOrderLineFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/AbstractQBExportReturnLineReturnedFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/AbstractQBExportReturnLineDamagedFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/AbstractQBExportInvAdjustmentWasteFormView.php';
require_once 'view/Quickbooks/AbstractUpdateMultiInvItemQBSettingsFormView.php';
require_once 'view/Quickbooks/AbstractQBSettingsIndexView.php';
require_once 'view/Quickbooks/QBAccount/AbstractQBAccountIndexView.php';
require_once 'view/Quickbooks/QBAccount/AbstractQBAccountSearchView.php';
require_once 'view/Quickbooks/QBProduct/AbstractQBProductIndexView.php';
require_once 'view/Quickbooks/QBProduct/AbstractQBProductSearchView.php';

//*** End Abstract

//### Concrete
$curIncludePath = get_include_path();
//set_include_path('concrete/modules/Accounting/' . MODULE_ACCOUNTING_VER);
set_include_path('concrete/modules/Accounting/' . MODULE_ACCOUNTING_VER);

//Concrete Domain
require_once 'model/Domain/Expense/Expense.php';
require_once 'model/Domain/Expense/ExpenseBill.php';
require_once 'model/Domain/ExpenseItem/ExpenseItem.php';
require_once 'model/Domain/ExpenseItemGroup/ExpenseItemGroup.php';
require_once 'model/Domain/Income/Income.php';
require_once 'model/Domain/Income/IncomeInvoice.php';
require_once 'model/Domain/IncomeItem/IncomeItem.php';
require_once 'model/Domain/Payment/Payment.php';
require_once 'model/Domain/Payment/PaymentExpense.php';
require_once 'model/Domain/Payment/PaymentIncome.php';
require_once 'model/Domain/GroupPayment/GroupPayment.php';
require_once 'model/Domain/GroupPayment/GroupPaymentCredit.php';
require_once 'model/Domain/GroupPayment/GroupPaymentRefund.php';
require_once 'model/Domain/GroupPayment/GroupPaymentImported.php';
require_once 'model/Domain/PaymentAccount/PaymentAccount.php';
require_once 'model/Domain/QBTaxCode/QBTaxCode.php';
require_once 'model/Domain/QBTaxRate/QBTaxRate.php';
require_once 'model/Domain/QBAccount/QBAccount.php';
require_once 'model/Domain/QBProduct/QBProduct.php';
require_once 'model/Domain/QBJournalEntry/QBJournalEntry.php';
require_once 'model/Domain/QBJournalEntry/QBJournalEntrySalesOrderLine.php';
require_once 'model/Domain/QBJournalEntry/QBJournalEntryReturnLineReturned.php';
require_once 'model/Domain/QBJournalEntry/QBJournalEntryReturnLineDamaged.php';
require_once 'model/Domain/QBJournalEntry/QBJournalEntryInvAdjustmentWaste.php';
require_once 'model/Domain/QBJournalEntryCat/QBJournalEntryCat.php';
require_once 'model/Domain/RegionQBDefault/RegionQBDefaultTaxCode.php';
require_once 'model/Domain/RegionQBDefault/RegionQBDefaultProduct.php';

//Concrete Factory
require_once 'model/Factory/ExpenseFactory.php';
require_once 'model/Factory/ExpenseItemFactory.php';
require_once 'model/Factory/ExpenseItemGroupFactory.php';
require_once 'model/Factory/IncomeFactory.php';
require_once 'model/Factory/IncomeItemFactory.php';
require_once 'model/Factory/PaymentFactory.php';
require_once 'model/Factory/GroupPaymentFactory.php';
require_once 'model/Factory/PaymentAccountFactory.php';
require_once 'model/Factory/AccReportFactory.php';
require_once 'model/Factory/QBTaxCodeFactory.php';
require_once 'model/Factory/QBTaxRateFactory.php';
require_once 'model/Factory/QBAccountFactory.php';
require_once 'model/Factory/QBProductFactory.php';
require_once 'model/Factory/QBJournalEntryFactory.php';
require_once 'model/Factory/QBJournalEntryCatFactory.php';
require_once 'model/Factory/RegionQBDefaultFactory.php';

//Concrete Utility
require_once 'model/Utility/Reports/AccReportOverview.php';
require_once 'model/Utility/Reports/AccReportQBApAgingSummary.php';
require_once 'model/Utility/Reports/AccReportQBArAgingSummary.php';
require_once 'model/Utility/Reports/AccReportQBCustomerBalance.php';
require_once 'model/Utility/Reports/AccReportQBProfitAndLoss.php';
require_once 'model/Utility/Reports/AccReportQBSalesByCustomer.php';
require_once 'model/Utility/Reports/AccReportSalesBySKU.php';
require_once 'model/Utility/Reports/AccReportSalesBySalesperson.php';
require_once 'model/Utility/Reports/AccReportInvCogsSales.php';
require_once 'model/Utility/Reports/AccReportOrderValues.php';
require_once 'model/Utility/Reports/AccReportSalesByRegion.php';
require_once 'model/Utility/Reports/AccReportSalesComparison.php';

//Concrete View
require_once 'view/GroupPayment/accounting_preApplyGroupPaymentFormView.php';
require_once 'view/GroupPayment/accounting_applyGroupPaymentToInvoicesFormView.php';
require_once 'view/GroupPayment/accounting_applyGroupPaymentToBillsFormView.php';
require_once 'view/GroupPayment/accounting_groupPaymentIndexView.php';
require_once 'view/GroupPayment/accounting_groupPaymentDetailView.php';
require_once 'view/GroupPayment/accounting_groupPaymentFormView.php';
require_once 'view/GroupPayment/accounting_groupPaymentSearchFormView.php';
require_once 'view/GroupPayment/accounting_creditIndexView.php';
require_once 'view/GroupPayment/accounting_groupPaymentCreditFormView.php';
require_once 'view/GroupPayment/accounting_groupPaymentCreditDetailView.php';
require_once 'view/GroupPayment/accounting_groupPaymentCreditSearchFormView.php';
require_once 'view/GroupPayment/accounting_groupPaymentCreditOutputView.php';
require_once 'view/GroupPayment/accounting_groupPaymentRefundFormView.php';
require_once 'view/GroupPayment/accounting_groupPaymentRefundDetailView.php';
require_once 'view/GroupPayment/accounting_groupPaymentImportedIndexView.php';
require_once 'view/GroupPayment/accounting_importPaymentsFileFormView.php';
require_once 'view/Payment/accounting_paymentTableView.php';
require_once 'view/Payment/accounting_paymentFormView.php';
require_once 'view/Payment/accounting_paymentExpenseFormView.php';
require_once 'view/Payment/accounting_paymentIncomeFormView.php';
require_once 'view/accounting_voidFormView.php';
require_once 'view/accounting_accountsPayableIndexView.php';
require_once 'view/accounting_accountsReceivableIndexView.php';
require_once 'view/accounting_exportFormView.php';
require_once 'view/Reports/accounting_accReportsFormView.php';
require_once 'view/Reports/accounting_accReportsView.php';
require_once 'view/Reports/accounting_accReportQBTableView.php';
require_once 'view/Reports/accounting_accReportQBDetailView.php';
require_once 'view/Reports/accounting_accReportOverviewDetailView.php';
require_once 'view/Reports/accounting_accReportSalesBySKUDetailView.php';
require_once 'view/Reports/accounting_accReportSalesBySalespersonDetailView.php';
require_once 'view/Reports/accounting_accReportInvCogsSalesDetailView.php';
require_once 'view/Reports/accounting_accReportSummaryView.php';
require_once 'view/Reports/accounting_accReportOrderValuesDetailView.php';
require_once 'view/Reports/accounting_accReportSalesByRegionDetailView.php';
require_once 'view/Reports/accounting_accReportSalesComparisonDetailView.php';
require_once 'view/PaymentAccount/accounting_paymentAccountFormView.php';
require_once 'view/PaymentAccount/accounting_paymentAccountIndexView.php';
require_once 'view/Quickbooks/accounting_exportAdjustmentsToQuickbooksConfirmView.php';
require_once 'view/Quickbooks/accounting_exportAdjustmentsToQuickbooksIndexView.php';
require_once 'view/Quickbooks/accounting_exportAdjustmentsToQuickbooksContentView.php';
require_once 'view/Quickbooks/accounting_updateMultiInvItemQBSettingsFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/accounting_exportAdjustmentsToQBFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/accounting_qbExportSalesOrderLineFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/accounting_qbExportReturnLineReturnedFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/accounting_qbExportReturnLineDamagedFormView.php';
require_once 'view/Quickbooks/AdjustmentsExports/accounting_qbExportInvAdjustmentWasteFormView.php';
require_once 'view/Quickbooks/accounting_qbSettingsIndexView.php';
require_once 'view/Quickbooks/QBAccount/qbAccount_indexView.php';
require_once 'view/Quickbooks/QBAccount/qbAccount_searchView.php';
require_once 'view/Quickbooks/QBProduct/qbProduct_indexView.php';
require_once 'view/Quickbooks/QBProduct/qbProduct_searchView.php';

set_include_path($curIncludePath);
//### End Con