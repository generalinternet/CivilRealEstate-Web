<?php

define('FRMWK_CORE_VER', 'v4.0');
$curIncludePath = get_include_path();
set_include_path($curIncludePath . '/' . FRMWK_CORE_VER);

//Abstract Service
require_once 'service/AbstractWidgetService.php';
require_once 'service/AbstractLogService.php';
require_once 'service/AbstractEventService.php';
require_once 'service/AbstractNotificationService.php';
require_once 'service/AbstractAWSService.php';
require_once 'service/AbstractAlertService.php';
require_once 'service/AbstractKeyService.php';
require_once 'service/AbstractSessionService.php';

// Abstract Domain
require_once 'model/Domain/Tag/AbstractTag.php';
require_once 'model/Domain/Tag/AbstractTagInventory.php';
require_once 'model/Domain/Tag/AbstractTagFEOSOption.php';
require_once 'model/Domain/Tag/AbstractTagContactSubcat.php';
require_once 'model/Domain/Tag/AbstractTagLocation.php';
require_once 'model/Domain/RoleGroup/AbstractRoleGroup.php';
require_once 'model/Domain/Region/AbstractRegion.php';
require_once 'model/Domain/Tax/AbstractTax.php';
require_once 'model/Domain/TaxRegion/AbstractTaxRegion.php';
require_once 'model/Domain/Notification/AbstractNotification.php';
require_once 'model/Domain/ApplicableTax/AbstractApplicableTax.php';
require_once 'model/Domain/Permission/AbstractPermission.php';
require_once 'model/Domain/User/AbstractUser.php';
require_once 'model/Domain/User/AbstractUserUnconfirmed.php';
require_once 'model/Domain/Login/AbstractLogin.php';
require_once 'model/Domain/Role/AbstractRole.php';
require_once 'model/Domain/Currency/AbstractCurrency.php';
require_once 'model/Domain/UserDetail/AbstractUserDetail.php';
require_once 'model/Domain/WorkerScriptRunTime/AbstractWorkerScriptRunTime.php';
require_once 'model/Domain/TimeInterval/AbstractTimeInterval.php';
require_once 'model/Domain/LabourRate/AbstractLabourRate.php';
require_once 'model/Domain/UserHasLabourRate/AbstractUserHasLabourRate.php';
require_once 'model/Domain/Table/AbstractTable.php';
require_once 'model/Domain/TableColumn/AbstractTableColumn.php';
require_once 'model/Domain/Settings/AbstractSettings.php';
require_once 'model/Domain/Settings/AbstractSettingsQB.php';
require_once 'model/Domain/Settings/AbstractSettingsNotif.php';
require_once 'model/Domain/Settings/AbstractSettingsPayment.php';
require_once 'model/Domain/PricingRegion/AbstractPricingRegion.php';
require_once 'model/Domain/PricingRegionIncl/AbstractPricingRegionIncl.php';
require_once 'model/Domain/EcoFee/AbstractEcoFee.php';
require_once 'model/Domain/EcoFee/AbstractEcoFeeByContainerSize.php';
require_once 'model/Domain/FOBShippingType/AbstractFOBShippingType.php';
require_once 'model/Domain/TaxCollectionType/AbstractTaxCollectionType.php';
require_once 'model/Domain/PermissionCategory/AbstractPermissionCategory.php';
require_once 'model/Domain/Note/AbstractNote.php';
require_once 'model/Domain/Note/AbstractNotePrivate.php';
require_once 'model/Domain/Note/AbstractNoteSystem.php';
require_once 'model/Domain/PricingUnit/AbstractPricingUnit.php';
require_once 'model/Domain/PricingUnit/AbstractPricingUnitDistance.php';
require_once 'model/Domain/PricingUnit/AbstractPricingUnitVolume.php';
require_once 'model/Domain/RecentActivity/AbstractRecentActivity.php';
require_once 'model/Domain/RuleAction/AbstractRuleAction.php';
require_once 'model/Domain/RuleGroup/AbstractRuleGroup.php';
require_once 'model/Domain/RuleGroup/AbstractRuleGroupSalesOrder.php';
require_once 'model/Domain/RuleGroup/AbstractRuleGroupDiscount.php';
require_once 'model/Domain/RuleCondition/AbstractRuleCondition.php';
require_once 'model/Domain/RuleCondition/AbstractRuleConditionMath.php';
require_once 'model/Domain/RuleCondition/AbstractRuleConditionMathPP.php';
require_once 'model/Domain/RuleCondition/AbstractRuleConditionMathPV.php';
require_once 'model/Domain/Rule/AbstractRule.php';
require_once 'model/Domain/Document/AbstractDocument.php';
require_once 'model/Domain/ContextRole/AbstractContextRole.php';
require_once 'model/Domain/Event/AbstractEvent.php';
require_once 'model/Domain/Event/AbstractEventPayment.php';
require_once 'model/Domain/EventNotifies/AbstractEventNotifies.php';
require_once 'model/Domain/Alert/AbstractAlert.php';
require_once 'model/Domain/Subscription/AbstractSubscription.php';
require_once 'model/Domain/Subscription/AbstractSubscriptionStripe.php';
require_once 'model/Domain/UserActionRequired/AbstractUserActionRequired.php';
require_once 'model/Domain/UserActionRequired/AbstractUserActionRequiredRedirect.php';
require_once 'model/Domain/InterfacePerspective/AbstractInterfacePerspective.php';
require_once 'model/Domain/UserLinkToApp/AbstractUserLinkToApp.php';

// Abstract Factory
require_once 'model/Factory/AbstractTagFactory.php';
require_once 'model/Factory/AbstractPricingUnitFactory.php';
require_once 'model/Factory/AbstractRoleGroupFactory.php';
require_once 'model/Factory/AbstractRegionFactory.php';
require_once 'model/Factory/AbstractTaxFactory.php';
require_once 'model/Factory/AbstractTaxRegionFactory.php';
require_once 'model/Factory/AbstractNotificationFactory.php';
require_once 'model/Factory/AbstractApplicableTaxFactory.php';
require_once 'model/Factory/AbstractPermissionFactory.php';
require_once 'model/Factory/AbstractUserFactory.php';
require_once 'model/Factory/AbstractLoginFactory.php';
require_once 'model/Factory/AbstractRoleFactory.php';
require_once 'model/Factory/AbstractCurrencyFactory.php';
require_once 'model/Factory/AbstractUserDetailFactory.php';
require_once 'model/Factory/AbstractWorkerScriptRunTimeFactory.php';
require_once 'model/Factory/AbstractTimeIntervalFactory.php';
require_once 'model/Factory/AbstractLabourRateFactory.php';
require_once 'model/Factory/AbstractUserHasLabourRateFactory.php';
require_once 'model/Factory/AbstractTableFactory.php';
require_once 'model/Factory/AbstractTableColumnFactory.php';
require_once 'model/Factory/AbstractSettingsFactory.php';
require_once 'model/Factory/AbstractPricingRegionFactory.php';
require_once 'model/Factory/AbstractPricingRegionInclFactory.php';
require_once 'model/Factory/AbstractFOBShippingTypeFactory.php';
require_once 'model/Factory/AbstractTaxCollectionTypeFactory.php';
require_once 'model/Factory/AbstractNoteFactory.php';
require_once 'model/Factory/AbstractEcoFeeFactory.php';
require_once 'model/Factory/AbstractRuleActionFactory.php';
require_once 'model/Factory/AbstractRuleGroupFactory.php';
require_once 'model/Factory/AbstractRuleConditionFactory.php';
require_once 'model/Factory/AbstractRuleFactory.php';
require_once 'model/Factory/AbstractPermissionCategoryFactory.php';
require_once 'model/Factory/AbstractDocumentFactory.php';
require_once 'model/Factory/AbstractRecentActivityFactory.php';
require_once 'model/Factory/AbstractEventFactory.php';
require_once 'model/Factory/AbstractEventNotifiesFactory.php';
require_once 'model/Factory/AbstractContextRoleFactory.php';
require_once 'model/Factory/AbstractSubscriptionFactory.php';
require_once 'model/Factory/AbstractUserActionRequiredFactory.php';
require_once 'model/Factory/AbstractInterfacePerspectiveFactory.php';
require_once 'model/Factory/AbstractUserLinkToAppFactory.php';

// Abstract View
require_once 'view/UITable/AbstractUITableView.php';
require_once 'view/UITable/AbstractUITableRowView.php';
require_once 'view/UITable/AbstractUIRolodexView.php';
require_once 'view/UITable/AbstractUICardView.php';
require_once 'view/UITable/AbstractUICatalogView.php';
require_once 'view/dashboard/AbstractDashboardIndexView.php';
require_once 'view/dashboard/AbstractDashboardWidgetView.php';
require_once 'view/dashboard/AbstractDashboardChartWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardPOTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardSOTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardShippingTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardReceivingTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardContactEventHistoryTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardContactClientEventHistoryTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardYTDSalesChartWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardYTDProfitLossChartWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardActiveUsersTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardRecentActivityTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardMySalesTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardSalesBySalespersonTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardAPARTableWidgetView.php';
require_once 'view/dashboard/widgets/AbstractDashboardSalesComparisonTableWidgetView.php';
require_once 'view/dashboard/widgets/OrderValues/AbstractDashboardOrderValuesTableWidgetView.php';
require_once 'view/dashboard/widgets/OrderValues/AbstractDashboardSalesOrderValuesTableWidgetView.php';
require_once 'view/dashboard/widgets/OrderValues/AbstractDashboardPurchaseOrderValuesTableWidgetView.php';
require_once 'view/tag/AbstractTagListView.php';
require_once 'view/tag/AbstractTagDetailView.php';
require_once 'view/tag/AbstractTagListFormView.php';
require_once 'view/tag/AbstractTagFormView.php';
require_once 'view/tag/AbstractTagIndexView.php';
require_once 'view/tag/AbstractTagInventoryFormView.php';
require_once 'view/import/AbstractImportFileFormView.php';
require_once 'view/generic/AbstractGenericAcceptCancelFormView.php';
require_once 'view/generic/AbstractGenericDeleteFormView.php';
require_once 'view/generic/AbstractGenericCancelFormView.php';
require_once 'view/generic/AbstractGenericProgressBarView.php';
require_once 'view/generic/AbstractGenericEmailView.php';
require_once 'view/generic/AbstractGenericColourFormView.php';
require_once 'view/generic/AbstractGenericDeletedDetailView.php';
require_once 'view/generic/AbstractGenericListBarView.php';
require_once 'view/generic/AbstractGenericMainWindowView.php';
require_once 'view/generic/AbstractGenericView.php';
require_once 'view/generic/AbstractGenericPasswordRuleView.php';
require_once 'view/generic/Elements/AbstractBirdBeakMenuView.php';
require_once 'view/generic/Elements/AbstractBirdBeakMenuBtnView.php';
require_once 'view/user/AbstractUserIndexView.php';
require_once 'view/user/AbstractUserDetailView.php';
require_once 'view/user/AbstractUserFormView.php';
require_once 'view/user/AbstractUserSearchFormView.php';
require_once 'view/user/Notification/AbstractNotificationUITableView.php';
require_once 'view/user/Notification/AbstractUserNotificationSettingsView.php';
require_once 'view/user/Notification/AbstractUserNotificationSettingsFormView.php';
require_once 'view/roleGroup/AbstractRoleGroupFormView.php';
require_once 'view/roleGroup/AbstractRoleGroupIndexView.php';
require_once 'view/roleGroup/AbstractRoleGroupDetailView.php';
require_once 'view/login/AbstractLoginIndexView.php';
require_once 'view/login/AbstractLoginRegisterView.php';
require_once 'view/login/AbstractLoginForgotPasswordView.php';
require_once 'view/login/AbstractLoginResetPasswordView.php';
require_once 'view/login/AbstractLoginStillHereView.php';
require_once 'view/login/AbstractLoginConfirmEmailFormView.php';
require_once 'view/login/AbstractLoginResendConfirmationEmailFormView.php';
require_once 'view/login/AbstractLoginConfirmationSentView.php';
require_once 'view/role/AbstractRoleDetailView.php';
require_once 'view/role/AbstractRoleFormView.php';
require_once 'view/pricingRegion/AbstractPricingRegionIndexView.php';
require_once 'view/pricingRegion/AbstractPricingRegionDetailView.php';
require_once 'view/pricingRegion/AbstractPricingRegionFormView.php';
require_once 'view/region/AbstractRegionEcoFeeDetailView.php';
require_once 'view/region/AbstractRegionEcoFeeFormView.php';
require_once 'view/region/AbstractRegionEcoFeeIndexView.php';
require_once 'view/region/AbstractRegionQBSettingsIndexView.php';
require_once 'view/region/AbstractRegionQBSettingsDetailView.php';
require_once 'view/region/AbstractRegionQBSettingsFormView.php';
require_once 'view/ecoFee/AbstractEcoFeeFormView.php';
require_once 'view/ecoFee/AbstractEcoFeeByContainerSizeFormView.php';
require_once 'view/permission/AbstractPermissionIndexView.php';
require_once 'view/permission/AbstractPermissionDetailView.php';
require_once 'view/permission/AbstractPermissionFormView.php';
require_once 'view/permission/AbstractPermissionDeniedView.php';
require_once 'view/permission/AbstractPermissionSearchFormView.php';
require_once 'view/layout/AbstractLayoutView.php';
require_once 'view/layout/AbstractMainLayoutView.php';
require_once 'view/layout/AbstractLoginLayoutView.php';
require_once 'view/layout/AbstractPublicLayoutView.php';
require_once 'view/layout/AbstractIconView.php';
require_once 'view/admin/AbstractAdminIndexView.php';
require_once 'view/admin/AbstractAdminEditCurrencyFormView.php';
require_once 'view/admin/AbstractAdminEchoView.php';

require_once 'view/labourRate/AbstractLabourRateFormView.php';
require_once 'view/labourRate/AbstractUserHasLabourRateFormView.php';

require_once 'view/generic/Tabs/AbstractGenericTabView.php';
require_once 'view/generic/Tabs/AbstractGenericTabWrapView.php';

require_once 'view/generic/Overlay/AbstractGenericOverlayGridView.php';
require_once 'view/generic/Overlay/AbstractGenericOverlayWrapView.php';

require_once 'view/note/AbstractNoteIndexView.php';
require_once 'view/note/AbstractNoteSearchFormView.php';
require_once 'view/note/AbstractNoteDetailView.php';
require_once 'view/note/AbstractNoteFormView.php';
require_once 'view/note/AbstractNoteThreadView.php';
require_once 'view/note/NotePrivate/AbstractNotePrivateDetailView.php';
require_once 'view/note/NotePrivate/AbstractNotePrivateFormView.php';
require_once 'view/note/AbstractNoteThreadView.php';

require_once 'view/rule/AbstractRuleFormView.php';
require_once 'view/rule/AbstractRuleDetailView.php';

require_once 'view/ruleGroup/AbstractRuleGroupIndexView.php';
require_once 'view/ruleGroup/AbstractRuleGroupPreAddFormView.php';
require_once 'view/ruleGroup/AbstractRuleGroupFormView.php';
require_once 'view/ruleGroup/AbstractRuleGroupDetailView.php';
require_once 'view/ruleGroup/AbstractRuleGroupResultSummaryView.php';

require_once 'view/ruleCondition/AbstractRuleConditionFormView.php';
require_once 'view/ruleCondition/AbstractRuleConditionDetailView.php';
require_once 'view/ruleCondition/Math/AbstractRuleConditionMathFormView.php';
require_once 'view/ruleCondition/Math/AbstractRuleConditionMathDetailView.php';
require_once 'view/ruleCondition/Math/AbstractRuleConditionMathPVFormView.php';
require_once 'view/ruleCondition/Math/AbstractRuleConditionMathPPFormView.php';

require_once 'view/franchise/AbstractFranchiseChangeFormView.php';

require_once 'view/quickBooks/AbstractQuickBooksBarView.php';

require_once 'view/timeInterval/AbstractTimeIntervalDetailView.php';
require_once 'view/timeInterval/AbstractTimeIntervalFormView.php';
require_once 'view/timeInterval/AbstractTimeIntervalTooltipView.php';
require_once 'view/timeInterval/AbstractTimeIntervalDeleteFormView.php';

require_once 'view/settings/AbstractSettingsDetailView.php';
require_once 'view/settings/AbstractSettingsFormView.php';
require_once 'view/settings/AbstractSettingsIndexView.php';
require_once 'view/settings/qb/AbstractSettingsQBDetailView.php';
require_once 'view/settings/qb/AbstractSettingsQBFormView.php';

require_once 'view/recentActivity/AbstractRecentActivityIndexView.php';
require_once 'view/recentActivity/AbstractRecentActivitySearchFormView.php';

require_once 'view/event/AbstractEventNotificationSettingsView.php';
require_once 'view/event/AbstractEventNotificationFormView.php';

require_once 'view/contextRole/AbstractContextRoleFormView.php';

require_once 'view/subscription/AbstractSubscriptionFormView.php';
require_once 'view/subscription/AbstractSubscriptionDetailView.php';

require_once 'view/paymentProcessor/AbstractPaymentProcessorCreditCardFormView.php';
require_once 'view/paymentProcessor/AbstractStripePaymentProcessorCreditCardFormView.php';
require_once 'view/paymentProcessor/paymentTypes/AbstractCreditCardDetailView.php';
require_once 'view/paymentProcessor/paymentHistory/AbstractPaymentProcessorPaymentHistoryView.php';
require_once 'view/paymentProcessor/paymentHistory/AbstractStripePaymentProcessorPaymentHistoryView.php';

// Abstract Utility
require_once 'model/Utility/Report/AbstractRepGen.php';
require_once 'model/Utility/PaymentProcessor/AbstractPaymentProcessor.php';
require_once 'model/Utility/PaymentProcessor/AbstractStripePaymentProcessor.php';
require_once 'model/Utility/Tag/AbstractTagInstaller.php';
require_once 'model/Utility/Tag/AbstractLocationTagInstaller.php';

//vCard ICS
require_once 'model/Utility/VCard/AbstractGI_VCard.php';
require_once 'model/Utility/VCard/AbstractGI_VCardFactory.php';
require_once 'model/Utility/ICS/AbstractGI_ICS.php';
require_once 'model/Utility/ICS/AbstractGI_ICSFactory.php';
require_once 'model/Utility/ActionResult/AbstractActionResult.php';
require_once 'model/Utility/ActionResult/AbstractActionResultFactory.php';

require_once 'model/Utility/Calculator/AbstractSubsetSumsCalculator.php';
require_once 'model/Utility/Event/AbstractEventInstaller.php';
require_once 'model/Utility/ContextRole/AbstractContextRoleInstaller.php';

//*** End Abstract

//Concrete
set_include_path('concrete/core/' . FRMWK_SUPER_VER);
require_once 'model/audit/Login_Audit.php';
require_once 'model/language/lang.php';
require_once 'model/Type/GenericTypeModel.php';
require_once 'model/UITable/UITableCol.php';
require_once 'model/Factory/Type/TypeModelFactory.php';

//Concete Service
require_once 'service/WidgetService.php';
require_once 'service/LogService.php';
require_once 'service/EventService.php';
require_once 'service/NotificationService.php';
require_once 'service/AWSService.php';
require_once 'service/AlertService.php';
require_once 'service/KeyService.php';
require_once 'service/SessionService.php';

//Concrete Domain
require_once 'model/Domain/Core/Currency/Currency.php';
require_once 'model/Domain/Core/Tag/Tag.php';
require_once 'model/Domain/Core/Tag/TagInventory.php';
require_once 'model/Domain/Core/Tag/TagFEOSOption.php';
require_once 'model/Domain/Core/Tag/TagContactSubcat.php';
require_once 'model/Domain/Core/Tag/TagLocation.php';
require_once 'model/Domain/Core/Document/Document.php';
require_once 'model/Domain/Core/PricingUnit/PricingUnit.php';
require_once 'model/Domain/Core/PricingUnit/PricingUnitDistance.php';
require_once 'model/Domain/Core/PricingUnit/PricingUnitVolume.php';
require_once 'model/Domain/Core/RoleGroup/RoleGroup.php';
require_once 'model/Domain/Core/Region/Region.php';
require_once 'model/Domain/Core/Tax/Tax.php';
require_once 'model/Domain/Core/TaxRegion/TaxRegion.php';
require_once 'model/Domain/Core/Notification/Notification.php';
require_once 'model/Domain/Core/ApplicableTax/ApplicableTax.php';
require_once 'model/Domain/Core/Permission/Permission.php';
require_once 'model/Domain/Core/User/User.php';
require_once 'model/Domain/Core/User/UserUnconfirmed.php';
require_once 'model/Domain/Core/Login/Login.php';
require_once 'model/Domain/Core/Role/Role.php';
require_once 'model/Domain/Core/UserDetail/UserDetail.php';
require_once 'model/Domain/Core/WorkerScriptRunTime/WorkerScriptRunTime.php';
require_once 'model/Domain/Core/TimeInterval/TimeInterval.php';
require_once 'model/Domain/Core/LabourRate/LabourRate.php';
require_once 'model/Domain/Core/UserHasLabourRate/UserHasLabourRate.php';
require_once 'model/Domain/Core/Table/Table.php';
require_once 'model/Domain/Core/TableColumn/TableColumn.php';
require_once 'model/Domain/Core/Settings/Settings.php';
require_once 'model/Domain/Core/Settings/SettingsQB.php';
require_once 'model/Domain/Core/Settings/SettingsNotif.php';
require_once 'model/Domain/Core/Settings/SettingsPayment.php';
require_once 'model/Domain/Core/PricingRegion/PricingRegion.php';
require_once 'model/Domain/Core/PricingRegionIncl/PricingRegionIncl.php';
require_once 'model/Domain/Core/EcoFee/EcoFee.php';
require_once 'model/Domain/Core/EcoFee/EcoFeeByContainerSize.php';
require_once 'model/Domain/Core/FOBShippingType/FOBShippingType.php';
require_once 'model/Domain/Core/TaxCollectionType/TaxCollectionType.php';
require_once 'model/Domain/Core/Note/Note.php';
require_once 'model/Domain/Core/Note/NotePrivate.php';
require_once 'model/Domain/Core/Note/NoteSystem.php';
require_once 'model/Domain/Core/RecentActivity/RecentActivity.php';
require_once 'model/Domain/Core/RuleAction/RuleAction.php';
require_once 'model/Domain/Core/RuleGroup/RuleGroup.php';
require_once 'model/Domain/Core/RuleGroup/RuleGroupSalesOrder.php';
require_once 'model/Domain/Core/RuleGroup/RuleGroupDiscount.php';
require_once 'model/Domain/Core/RuleCondition/RuleCondition.php';
require_once 'model/Domain/Core/RuleCondition/RuleConditionMath.php';
require_once 'model/Domain/Core/RuleCondition/RuleConditionMathPV.php';
require_once 'model/Domain/Core/RuleCondition/RuleConditionMathPP.php';
require_once 'model/Domain/Core/Rule/Rule.php';
require_once 'model/Domain/Core/PermissionCategory/PermissionCategory.php';
require_once 'model/Domain/Core/Event/Event.php';
require_once 'model/Domain/Core/Event/EventPayment.php';
require_once 'model/Domain/Core/ContextRole/ContextRole.php';
require_once 'model/Domain/Core/EventNotifies/EventNotifies.php';
require_once 'model/Domain/Core/Alert/Alert.php';
require_once 'model/Domain/Core/Subscription/Subscription.php';
require_once 'model/Domain/Core/Subscription/SubscriptionStripe.php';
require_once 'model/Domain/Core/UserActionRequired/UserActionRequired.php';
require_once 'model/Domain/Core/UserActionRequired/UserActionRequiredRedirect.php';
require_once 'model/Domain/Core/InterfacePerspective/InterfacePerspective.php';
require_once 'model/Domain/Core/UserLinkToApp/UserLinkToApp.php';

//Concrete Factory
require_once 'model/Factory/TagFactory.php';
require_once 'model/Factory/DocumentFactory.php';
require_once 'model/Factory/PricingUnitFactory.php';
require_once 'model/Factory/RoleGroupFactory.php';
require_once 'model/Factory/RegionFactory.php';
require_once 'model/Factory/TaxFactory.php';
require_once 'model/Factory/TaxRegionFactory.php';
require_once 'model/Factory/NotificationFactory.php';
require_once 'model/Factory/ApplicableTaxFactory.php';
require_once 'model/Factory/PermissionFactory.php';
require_once 'model/Factory/UserFactory.php';
require_once 'model/Factory/LoginFactory.php';
require_once 'model/Factory/RoleFactory.php';
require_once 'model/Factory/CurrencyFactory.php';
require_once 'model/Factory/UserDetailFactory.php';
require_once 'model/Factory/WorkerScriptRunTimeFactory.php';
require_once 'model/Factory/TimeIntervalFactory.php';
require_once 'model/Factory/LabourRateFactory.php';
require_once 'model/Factory/UserHasLabourRateFactory.php';
require_once 'model/Factory/TableFactory.php';
require_once 'model/Factory/TableColumnFactory.php';
require_once 'model/Factory/SettingsFactory.php';
require_once 'model/Factory/PricingRegionFactory.php';
require_once 'model/Factory/PricingRegionInclFactory.php';
require_once 'model/Factory/EcoFeeFactory.php';
require_once 'model/Factory/FOBShippingTypeFactory.php';
require_once 'model/Factory/TaxCollectionTypeFactory.php';
require_once 'model/Factory/NoteFactory.php';
require_once 'model/Factory/RuleActionFactory.php';
require_once 'model/Factory/RuleGroupFactory.php';
require_once 'model/Factory/RuleConditionFactory.php';
require_once 'model/Factory/RuleFactory.php';
require_once 'model/Factory/PermissionCategoryFactory.php';
require_once 'model/Factory/RecentActivityFactory.php';
require_once 'model/Factory/EventFactory.php';
require_once 'model/Factory/EventNotifiesFactory.php';
require_once 'model/Factory/ContextRoleFactory.php';
require_once 'model/Factory/SubscriptionFactory.php';
require_once 'model/Factory/UserActionRequiredFactory.php';
require_once 'model/Factory/InterfacePerspectiveFactory.php';
require_once 'model/Factory/UserLinkToAppFactory.php';

//Concrete View
require_once 'view/dashboard/dashboard_indexView.php';
require_once 'view/dashboard/widgets/dashboard_poTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_soTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_shippingTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_receivingTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_contactEventHistoryTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_contactClientEventHistoryTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_ytdSalesChartWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_ytdProfitLossChartWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_activeUsersTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_recentActivityTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_mySalesTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_salesBySalespersonTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_APARTableWidgetView.php';
require_once 'view/dashboard/widgets/dashboard_salesComparisonTableWidgetView.php';
require_once 'view/dashboard/widgets/OrderValues/dashboard_salesOrderValuesTableWidgetView.php';
require_once 'view/dashboard/widgets/OrderValues/dashboard_purchaseOrderValuesTableWidgetView.php';
require_once 'view/admin/admin_indexView.php';
require_once 'view/admin/admin_editCurrencyFormView.php';
require_once 'view/admin/admin_echoView.php';
require_once 'view/admin/admin_reportsView.php';
require_once 'view/tag/tag_detailView.php';
require_once 'view/tag/tag_listView.php';
require_once 'view/tag/tag_listFormView.php';
require_once 'view/tag/tag_formView.php';
require_once 'view/tag/tag_indexView.php';
require_once 'view/tag/tagInventory_formView.php';
require_once 'view/import/import_fileFormView.php';
require_once 'view/UITable/UITableView.php';
require_once 'view/UITable/UITableRowView.php';
require_once 'view/UITable/UIRolodexView.php';
require_once 'view/UITable/UICardView.php';
require_once 'view/UITable/UICatalogView.php';
die('TEST 15');
//login views
require_once 'view/login/login_indexView.php';
require_once 'view/login/login_registerView.php';
require_once 'view/login/login_forgotPasswordView.php';
require_once 'view/login/login_resetPasswordView.php';
require_once 'view/login/login_stillHereView.php';
require_once 'view/login/login_confirmEmailFormView.php';
require_once 'view/login/login_resendConfirmationEmailFormView.php';
require_once 'view/login/login_confirmationSentView.php';
//layout views
require_once 'view/layout/mainLayoutView.php';
require_once 'view/layout/loginLayoutView.php';
require_once 'view/layout/publicLayoutView.php';
require_once 'view/layout/basicMenuView.php';
require_once 'view/layout/pageBarView.php';
require_once 'view/layout/loadMoreBtn.php';
require_once 'view/layout/loadPrevBtn.php';
require_once 'view/layout/genericLayoutView.php';
require_once 'view/layout/iconView.php';
//user views
require_once 'view/user/user_indexView.php';
require_once 'view/user/user_detailView.php';
require_once 'view/user/user_formView.php';
require_once 'view/user/user_searchFormView.php';
require_once 'view/user/Notification/notification_uiTableView.php';
require_once 'view/user/Notification/user_notificationSettingsView.php';
require_once 'view/user/Notification/user_notificationSettingsFormView.php';
//role views
require_once 'view/role/role_detailView.php';
require_once 'view/role/role_formView.php';
//role group views
require_once 'view/roleGroup/roleGroup_formView.php';
require_once 'view/roleGroup/roleGroup_indexView.php';
require_once 'view/roleGroup/roleGroup_detailView.php';
//settings
require_once 'view/settings/settings_indexView.php';
//permission views
require_once 'view/permission/permission_indexView.php';
require_once 'view/permission/permission_detailView.php';
require_once 'view/permission/permission_formView.php';
require_once 'view/permission/permission_deniedView.php';
require_once 'view/permission/permission_searchFormView.php';
//generic views
require_once 'view/generic/generic_acceptCancelFormView.php';
require_once 'view/generic/generic_deleteFormView.php';
require_once 'view/generic/generic_cancelFormView.php';
require_once 'view/generic/generic_progressBarView.php';
require_once 'view/generic/generic_emailView.php';
require_once 'view/generic/generic_colourFormView.php';
require_once 'view/generic/generic_deletedDetailView.php';
require_once 'view/generic/generic_listBarView.php';
require_once 'view/generic/generic_mainWindowView.php';
require_once 'view/generic/generic_view.php';
require_once 'view/generic/generic_passwordRuleView.php';
require_once 'view/generic/Elements/BirdBeakMenuView.php';
require_once 'view/generic/Elements/BirdBeakMenuBtnView.php';

//labour rate views
require_once 'view/labourRate/labourRate_formView.php';
require_once 'view/labourRate/userHasLabourRate_formView.php';
//tab views
require_once 'view/generic/Tabs/generic_tabView.php';
require_once 'view/generic/Tabs/generic_tabWrapView.php';
//overlay views
require_once 'view/generic/Overlay/generic_overlayGridView.php';
require_once 'view/generic/Overlay/generic_overlayWrapView.php';

//pricing region views
require_once 'view/pricingRegion/pricingRegion_indexView.php';
require_once 'view/pricingRegion/pricingRegion_detailView.php';
require_once 'view/pricingRegion/pricingRegion_formView.php';
//note views
require_once 'view/note/note_indexView.php';
require_once 'view/note/note_searchFormView.php';
require_once 'view/note/note_detailView.php';
require_once 'view/note/note_formView.php';
require_once 'view/note/note_threadView.php';
require_once 'view/note/NotePrivate/notePrivate_detailView.php';
require_once 'view/note/NotePrivate/notePrivate_formView.php';
//region views
require_once 'view/region/region_ecoFeeDetailView.php';
require_once 'view/region/region_ecoFeeFormView.php';
require_once 'view/region/region_ecoFeeIndexView.php';
require_once 'view/region/region_qbSettingsIndexView.php';
require_once 'view/region/region_qbSettingsFormView.php';
require_once 'view/region/region_qbSettingsDetailView.php';
//eco fee views
require_once 'view/ecoFee/ecoFee_formView.php';
require_once 'view/ecoFee/ecoFee_byContainerSizeFormView.php';

//rule views
require_once 'view/rule/rule_formView.php';
require_once 'view/rule/rule_detailView.php';

require_once 'view/ruleGroup/ruleGroup_indexView.php';
require_once 'view/ruleGroup/ruleGroup_preAddFormView.php';
require_once 'view/ruleGroup/ruleGroup_formView.php';
require_once 'view/ruleGroup/ruleGroup_detailView.php';
require_once 'view/ruleGroup/ruleGroup_resultSummaryView.php';

require_once 'view/ruleCondition/ruleCondition_formView.php';
require_once 'view/ruleCondition/ruleCondition_detailView.php';
require_once 'view/ruleCondition/Math/ruleConditionMath_formView.php';
require_once 'view/ruleCondition/Math/ruleConditionMath_detailView.php';
require_once 'view/ruleCondition/Math/ruleConditionMathPP_formView.php';
require_once 'view/ruleCondition/Math/ruleConditionMathPV_formView.php';

require_once 'view/franchise/franchise_changeFormView.php';

require_once 'view/quickBooks/quickBooks_barView.php';

require_once 'view/timeInterval/timeInterval_detailView.php';
require_once 'view/timeInterval/timeInterval_formView.php';
require_once 'view/timeInterval/timeInterval_tooltipView.php';
require_once 'view/timeInterval/timeInterval_deleteFormView.php';

require_once 'view/settings/qb/settings_qbDetailView.php';
require_once 'view/settings/qb/settings_qbFormView.php';

require_once 'view/recentActivity/recentActivity_indexView.php';
require_once 'view/recentActivity/recentActivity_searchFormView.php';

require_once 'view/event/EventNotificationSettingsView.php';
require_once 'view/event/EventNotificationFormView.php';

require_once 'view/contextRole/contextRole_formView.php';

require_once 'view/subscription/subscription_formView.php';
require_once 'view/subscription/subscription_detailView.php';

require_once 'view/paymentProcessor/stripePaymentProcessor_creditCardFormView.php';
require_once 'view/paymentProcessor/paymentTypes/creditCard_detailView.php';
require_once 'view/paymentProcessor/paymentHistory/stripePaymentProcessor_paymentHistoryView.php';

require_once 'view/test/test_formView.php';

//** Outputs
require_once 'model/Domain/Output/OutputPDF.php';
require_once 'view/layout/pdfLayoutView.php';

// Concrete Utility
require_once 'model/Utility/PaymentProcessor/StripePaymentProcessor.php';

//vCard ICS
require_once 'model/Utility/VCard/GI_VCard.php';
require_once 'model/Utility/VCard/GI_VCardFactory.php';
require_once 'model/Utility/ICS/GI_ICS.php';
require_once 'model/Utility/ICS/GI_ICSFactory.php';
require_once 'model/Utility/ActionResult/ActionResult.php';
require_once 'model/Utility/ActionResult/ActionResultFactory.php';

require_once 'model/Utility/Calculator/SubsetSumsCalculator.php';
require_once 'model/Utility/Event/EventInstaller.php';
require_once 'model/Utility/ContextRole/ContextRoleInstaller.php';
require_once 'model/Utility/Tag/LocationTagInstaller.php';

set_include_path('');
//Non-routed Controllers
require_once 'controllers/notificationController.php';

set_include_path($curIncludePath);

require_once 'file.php';
