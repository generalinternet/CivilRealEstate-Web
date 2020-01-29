<?php
/**
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.10
 */

define('MODULE_CONTACT_VER', 'v4.0');

//*** Abstract

// Abstract Domain
require_once 'model/Domain/Contact/AbstractContact.php';
require_once 'model/Domain/Contact/AbstractContactInd.php';
require_once 'model/Domain/Contact/Org/AbstractContactOrg.php';
require_once 'model/Domain/Contact/Org/AbstractContactOrgFranchise.php';
require_once 'model/Domain/Contact/Loc/AbstractContactLoc.php';
require_once 'model/Domain/Contact/Loc/AbstractContactLocWarehouse.php';
require_once 'model/Domain/ContactInfo/AbstractContactInfo.php';
require_once 'model/Domain/ContactInfo/AbstractContactInfoAddress.php';
require_once 'model/Domain/ContactInfo/AbstractContactInfoEmailAddr.php';
require_once 'model/Domain/ContactInfo/AbstractContactInfoPhoneNum.php';
require_once 'model/Domain/ContactTerms/AbstractContactTerms.php';
require_once 'model/Domain/ContactCat/AbstractContactCat.php';
require_once 'model/Domain/ContactCat/AbstractContactCatClient.php';
require_once 'model/Domain/ContactCat/AbstractContactCatVendor.php';
require_once 'model/Domain/ContactCat/AbstractContactCatInternal.php';
require_once 'model/Domain/ContactEvent/AbstractContactEvent.php';
require_once 'model/Domain/ContactRelationship/AbstractContactRelationship.php';
require_once 'model/Domain/AssignedToContact/AbstractAssignedToContact.php';
require_once 'model/Domain/ContactQB/AbstractContactQB.php';
require_once 'model/Domain/ContactQB/AbstractContactQBCustomer.php';
require_once 'model/Domain/ContactQB/AbstractContactQBSupplier.php';
require_once 'model/Domain/ContactScheduled/AbstractContactScheduled.php';
require_once 'model/Domain/Application/AbstractContactApplication.php';
require_once 'model/Domain/Application/AbstractContactApplicationClient.php';
require_once 'model/Domain/ApplicationStatus/AbstractContactApplicationStatus.php';
require_once 'model/Domain/Suspension/AbstractSuspension.php';

// Abstract Factory
require_once 'model/Factory/AbstractContactFactory.php';
require_once 'model/Factory/AbstractContactInfoFactory.php';
require_once 'model/Factory/AbstractContactTermsFactory.php';
require_once 'model/Factory/AbstractContactCatFactory.php';
require_once 'model/Factory/AbstractContactEventFactory.php';
require_once 'model/Factory/AbstractContactRelationshipFactory.php';
require_once 'model/Factory/AbstractAssignedToContactFactory.php';
require_once 'model/Factory/AbstractContactQBFactory.php';
require_once 'model/Factory/AbstractContactScheduledFactory.php';
require_once 'model/Factory/AbstractContactApplicationStatusFactory.php';
require_once 'model/Factory/AbstractContactApplicationFactory.php';
require_once 'model/Factory/AbstractSuspensionFactory.php';

//Abstract Utility
require_once 'model/Utility/AbstractQBContactImporter.php';

// Abstract View
require_once 'view/Contact/AbstractContactDetailView.php';
require_once 'view/Contact/AbstractContactFormView.php';
require_once 'view/Contact/AbstractContactSummaryView.php';
require_once 'view/Contact/AbstractContactManageRelationshipFormView.php';
require_once 'view/Contact/AbstractContactDiscountView.php';
require_once 'view/Contact/AbstractContactExportToQBFormView.php';
require_once 'view/Contact/AbstractContactImportFromQBFormView.php';
require_once 'view/AbstractContactIndexView.php';
require_once 'view/AbstractContactSearchFormView.php';
require_once 'view/AbstractContactCatIndexView.php';
require_once 'view/Contact/Ind/AbstractContactIndDetailView.php';
require_once 'view/Contact/Ind/AbstractContactIndFormView.php';
require_once 'view/Contact/Ind/AbstractContactIndSummaryView.php';
require_once 'view/Contact/Ind/AbstractContactIndConfirmEmailFormView.php';
require_once 'view/Contact/Org/AbstractContactOrgDetailView.php';
require_once 'view/Contact/Org/AbstractContactOrgFormView.php';
require_once 'view/Contact/Org/AbstractContactOrgSummaryView.php';
require_once 'view/Contact/Org/AbstractContactOrgFranchiseFormView.php';
require_once 'view/Contact/Org/AbstractContactOrgFranchiseDetailView.php';
require_once 'view/Contact/Loc/AbstractContactLocDetailView.php';
require_once 'view/Contact/Loc/AbstractContactLocFormView.php';
require_once 'view/Contact/Loc/AbstractContactLocSummaryView.php';
require_once 'view/Contact/Loc/LocWarehouse/AbstractContactLocWarehouseDetailView.php';
require_once 'view/Contact/Loc/LocWarehouse/AbstractContactLocWarehouseFormView.php';
require_once 'view/ContactInfo/AbstractContactInfoFormView.php';
require_once 'view/ContactInfo/Address/AbstractContactInfoAddressDetailView.php';
require_once 'view/ContactInfo/Address/AbstractContactInfoAddressFormView.php';
require_once 'view/ContactInfo/Address/AbstractContactInfoLocationAddressFormView.php';
require_once 'view/ContactInfo/EmailAddr/AbstractContactInfoEmailAddrDetailView.php';
require_once 'view/ContactInfo/EmailAddr/AbstractContactInfoEmailAddrFormView.php';
require_once 'view/ContactInfo/PhoneNum/AbstractContactInfoPhoneNumDetailView.php';
require_once 'view/ContactInfo/PhoneNum/AbstractContactInfoPhoneNumFormView.php';
require_once 'view/ContactCat/AbstractContactCatDetailView.php';
require_once 'view/ContactCat/AbstractContactCatFormView.php';
require_once 'view/ContactCat/AbstractContactCatSearchFormView.php';
require_once 'view/ContactCat/AbstractContactCatChangeSubFormView.php';
require_once 'view/ContactCat/Vendor/AbstractContactCatVendorDetailView.php';
require_once 'view/ContactCat/Vendor/AbstractContactCatVendorFormView.php';
require_once 'view/ContactCat/Client/AbstractContactCatClientDetailView.php';
require_once 'view/ContactCat/Client/AbstractContactCatClientFormView.php';
require_once 'view/ContactCat/Client/AbstractContactCatClientChangeSubFormView.php';
require_once 'view/ContactEvent/AbstractContactEventDetailView.php';
require_once 'view/ContactEvent/AbstractContactEventFormView.php';
require_once 'view/ContactEvent/AbstractContactEventEditView.php';
require_once 'view/ContactEvent/AbstractContactEventIndexView.php';
require_once 'view/ContactEvent/AbstractContactEventSearchFormView.php';
require_once 'view/ContactRelationship/AbstractContactRelationshipFormView.php';
require_once 'view/ContactRelationship/AbstractContactRelationshipsDetailView.php';
require_once 'view/AssignedToContact/AbstractAssignedToContactFormView.php';
require_once 'view/AssignedToContact/AbstractAssignedToContactsDetailView.php';
require_once 'view/ContactQB/AbstractContactQBIndexTabView.php';
require_once 'view/ContactQB/AbstractContactQBIndexView.php';
require_once 'view/ContactQB/AbstractContactQBDetailView.php';
require_once 'view/ContactQB/AbstractContactQBCreateOrLinkFormView.php';
require_once 'view/ContactQB/AbstractContactQBSearchView.php';
require_once 'view/ContactQB/AbstractContactQBLinkFormView.php';
require_once 'view/ContactQB/Customer/AbstractContactQBCustomerDetailView.php';
require_once 'view/Application/AbstractContactApplicationFormView.php';
require_once 'view/Application/AbstractContactApplicationClientFormView.php';
require_once 'view/Suspension/AbstractSuspensionFormView.php';
require_once 'view/Suspension/AbstractSuspensionDetailView.php';
require_once 'view/Suspension/AbstractSuspensionTableView.php';
require_once 'view/Suspension/AbstractSuspensionSummaryView.php';

//Profile
require_once 'view/Contact/Profile/AbstractContactProfileIndexView.php';
require_once 'view/Contact/Profile/AbstractContactProfileDetailView.php';
require_once 'view/Contact/Profile/AbstractContactProfileFormView.php';
require_once 'view/Contact/Profile/AbstractContactProfileUIRolodexView.php';
require_once 'view/Contact/Profile/AbstractContactProfileUICardView.php';
require_once 'view/Contact/Profile/AbstractContactProfileMySettingsDetailView.php';
require_once 'view/Contact/Profile/AbstractContactProfileSearchFormView.php';
require_once 'view/Contact/Profile/Ind/AbstractContactIndProfileDetailView.php';
require_once 'view/Contact/Profile/Ind/AbstractContactIndProfileFormView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfileDetailView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfileFormView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfileBasicFormView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfileUICardView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfilePublicProfileDetailView.php';
require_once 'view/Contact/Profile/Org/AbstractContactOrgProfilePublicProfileFormView.php';
require_once 'view/Contact/Profile/Org/Client/AbstractContactOrgClientProfileDetailView.php';
require_once 'view/Contact/Profile/Org/Client/AbstractContactOrgClientProfileFormView.php';
require_once 'view/Contact/Profile/Org/Vendor/AbstractContactOrgVendorProfileDetailView.php';
require_once 'view/Contact/Profile/Org/Vendor/AbstractContactOrgVendorProfileFormView.php';
require_once 'view/Contact/Profile/Org/Internal/AbstractContactOrgInternalProfileDetailView.php';

require_once 'view/Payment/AbstractContactPaymentsDetailView.php';
require_once 'view/Payment/AbstractContactPaymentMethodDetailView.php';
require_once 'view/Payment/AbstractContactPaymentMethodFormView.php';

//*** End Abstract

//### Concrete
$curIncludePath = get_include_path();
set_include_path('concrete/modules/Contact/' . MODULE_CONTACT_VER);

//Concrete Domain
require_once 'model/Domain/Contact/Contact.php';
require_once 'model/Domain/Contact/ContactInd.php';
require_once 'model/Domain/Contact/Org/ContactOrg.php';
require_once 'model/Domain/Contact/Org/ContactOrgFranchise.php';
require_once 'model/Domain/Contact/Loc/ContactLoc.php';
require_once 'model/Domain/Contact/Loc/ContactLocWarehouse.php';
require_once 'model/Domain/ContactInfo/ContactInfo.php';
require_once 'model/Domain/ContactInfo/ContactInfoEmailAddr.php';
require_once 'model/Domain/ContactInfo/ContactInfoPhoneNum.php';
require_once 'model/Domain/ContactInfo/ContactInfoAddress.php';
require_once 'model/Domain/ContactTerms/ContactTerms.php';
require_once 'model/Domain/ContactCat/ContactCat.php';
require_once 'model/Domain/ContactCat/ContactCatVendor.php';
require_once 'model/Domain/ContactCat/ContactCatClient.php';
require_once 'model/Domain/ContactCat/ContactCatInternal.php';
require_once 'model/Domain/ContactEvent/ContactEvent.php';
require_once 'model/Domain/ContactRelationship/ContactRelationship.php';
require_once 'model/Domain/AssignedToContact/AssignedToContact.php';
require_once 'model/Domain/ContactQB/ContactQBSupplier.php';
require_once 'model/Domain/ContactQB/ContactQBCustomer.php';
require_once 'model/Domain/ContactScheduled/ContactScheduled.php';
require_once 'model/Domain/Application/ContactApplication.php';
require_once 'model/Domain/Application/ContactApplicationClient.php';
require_once 'model/Domain/ApplicationStatus/ContactApplicationStatus.php';
require_once 'model/Domain/Suspension/Suspension.php';

//Concrete Factory
require_once 'model/Factory/ContactFactory.php';
require_once 'model/Factory/ContactInfoFactory.php';
require_once 'model/Factory/ContactTermsFactory.php';
require_once 'model/Factory/ContactCatFactory.php';
require_once 'model/Factory/ContactEventFactory.php';
require_once 'model/Factory/ContactRelationshipFactory.php';
require_once 'model/Factory/AssignedToContactFactory.php';
require_once 'model/Factory/ContactQBFactory.php';
require_once 'model/Factory/ContactScheduledFactory.php';
require_once 'model/Factory/ContactApplicationStatusFactory.php';
require_once 'model/Factory/ContactApplicationFactory.php';
require_once 'model/Factory/SuspensionFactory.php';

//Concrete Utility
require_once 'model/Utility/QBContactImporter.php';

//Concrete View
require_once 'view/contact_indexView.php';
require_once 'view/contact_catIndexView.php';
require_once 'view/contact/contact_detailView.php';
require_once 'view/contact/contact_formView.php';
require_once 'view/contact/contact_summaryView.php';
require_once 'view/contact/contact_manageRelationshipFormView.php';
require_once 'view/contact/contact_discountView.php';
require_once 'view/contact/contact_exportToQBFormView.php';
require_once 'view/contact/contact_importFromQBFormView.php';
require_once 'view/contact/org/contact_orgDetailView.php';
require_once 'view/contact/org/contact_orgSummaryView.php';
require_once 'view/contact/org/contact_orgFormView.php';
require_once 'view/contact/org/contact_orgFranchiseFormView.php';
require_once 'view/contact/org/contact_orgFranchiseDetailView.php';
require_once 'view/contact/ind/contact_indFormView.php';
require_once 'view/contact/ind/contact_indDetailView.php';
require_once 'view/contact/ind/contact_indSummaryView.php';
require_once 'view/contact/ind/contact_indConfirmEmailFormView.php';
require_once 'view/contact/loc/contact_locFormView.php';
require_once 'view/contact/loc/contact_locDetailView.php';
require_once 'view/contact/loc/contact_locSummaryView.php';
require_once 'view/contact/loc/locWarehouse/contact_locWarehouseDetailView.php';
require_once 'view/contact/loc/locWarehouse/contact_locWarehouseFormView.php';

require_once 'view/contactInfo/contactInfo_formView.php';
require_once 'view/contactInfo/address/contactInfo_addressFormView.php';
require_once 'view/contactInfo/address/contactInfo_addressDetailView.php';
require_once 'view/contactInfo/address/contactInfo_locationAddressFormView.php';
require_once 'view/contactInfo/emailAddr/contactInfo_emailAddrFormView.php';
require_once 'view/contactInfo/emailAddr/contactInfo_emailAddrDetailView.php';
require_once 'view/contactInfo/phoneNum/contactInfo_phoneNumFormView.php';
require_once 'view/contactInfo/phoneNum/contactInfo_phoneNumDetailView.php';

require_once 'view/contact_searchFormView.php';

require_once 'view/contactCat/contactCat_formView.php';
require_once 'view/contactCat/contactCat_detailView.php';
require_once 'view/contactCat/contactCat_searchFormView.php';
require_once 'view/contactCat/vendor/contactCat_vendorFormView.php';
require_once 'view/contactCat/vendor/contactCat_vendorDetailView.php';
require_once 'view/contactCat/client/contactCat_clientFormView.php';
require_once 'view/contactCat/client/contactCat_clientDetailView.php';
require_once 'view/contactCat/client/contactCat_clientChangeSubFormView.php';

require_once 'view/contactEvent/contactEvent_formView.php';
require_once 'view/contactEvent/contactEvent_detailView.php';
require_once 'view/contactEvent/contactEvent_editView.php';
require_once 'view/contactEvent/contactEvent_indexView.php';
require_once 'view/contactEvent/contactEvent_searchFormView.php';

require_once 'view/contactRelationship/contactRelationship_formView.php';
require_once 'view/contactRelationship/contactRelationships_detailView.php';

require_once 'view/assignedToContact/assignedToContact_formView.php';
require_once 'view/assignedToContact/assignedToContacts_detailView.php';

require_once 'view/contactQB/contactQB_indexTabView.php';
require_once 'view/contactQB/contactQB_indexView.php';
require_once 'view/contactQB/contactQB_detailView.php';
require_once 'view/contactQB/contactQB_createOrLinkFormView.php';
require_once 'view/contactQB/contactQB_searchView.php';
require_once 'view/contactQB/contactQB_linkFormView.php';
require_once 'view/contactQB/Customer/contactQBCustomer_detailView.php';

require_once 'view/application/contact_applicationFormView.php';
require_once 'view/application/contact_applicationClientFormView.php';

require_once 'view/contact/profile/ind/contactInd_profileDetailView.php';
require_once 'view/contact/profile/ind/contactInd_profileFormView.php';

require_once 'view/contact/profile/contact_profileIndexView.php';
require_once 'view/contact/profile/contact_profileUIRolodexView.php';
require_once 'view/contact/profile/contact_profileUICardView.php';
require_once 'view/contact/profile/contact_profileMySettingsDetailView.php';
require_once 'view/contact/profile/contact_profileSearchFormView.php';
require_once 'view/contact/profile/org/contactOrg_profileDetailView.php';
require_once 'view/contact/profile/org/contactOrg_profileFormView.php';
require_once 'view/contact/profile/org/contactOrg_profileBasicFormView.php';
require_once 'view/contact/profile/org/contactOrg_profileUICardView.php';
require_once 'view/contact/profile/org/contactOrg_profilePublicProfileDetailView.php';
require_once 'view/contact/profile/org/contactOrg_profilePublicProfileFormView.php';
require_once 'view/contact/profile/org/client/contactOrgClient_profileDetailView.php';
require_once 'view/contact/profile/org/client/contactOrgClient_profileFormView.php';
require_once 'view/contact/profile/org/vendor/contactOrgVendor_profileDetailView.php';
require_once 'view/contact/profile/org/vendor/contactOrgVendor_profileFormView.php';
require_once 'view/contact/profile/org/internal/contactOrgInternal_profileDetailView.php';

require_once 'view/payment/contact_paymentMethodDetailView.php';
require_once 'view/payment/contact_paymentMethodFormView.php';
require_once 'view/payment/contact_paymentsDetailView.php';

require_once 'view/suspension/suspension_formView.php';
require_once 'view/suspension/suspension_detailView.php';
require_once 'view/suspension/suspension_tableView.php';
require_once 'view/suspension/suspension_summaryView.php';

set_include_path($curIncludePath);
//### End Con