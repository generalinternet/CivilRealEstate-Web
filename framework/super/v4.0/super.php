<?php

define('FRMWK_SUPER_VER', 'v4.0');
$curIncludePath = get_include_path();

//Error Handling (include first to capture all errors)
require_once 'GI_Error/AbstractGI_Error.php';
require_once 'GI_Error/AbstractGI_ErrorFactory.php';
require_once 'GI_Log/AbstractGI_Log.php';
require_once 'GI_Log/AbstractGI_LogFactory.php';
set_include_path('concrete/super/' . FRMWK_SUPER_VER);
require_once 'GI_Error/GI_Error.php';
require_once 'GI_Error/GI_ErrorFactory.php';
require_once 'GI_Log/GI_Log.php';
require_once 'GI_Log/GI_LogFactory.php';

//Tools
set_include_path('framework/tools');
require_once 'GI_ErrorHandler.php';
require_once 'GI_Time.php';
require_once 'GI_Email.php';
require_once 'GI_SMS.php';
require_once 'GI_URLUtils.php';
require_once 'GI_Sanitize.php';
require_once 'GI_StringUtils.php';
require_once 'GI_Colour.php';
require_once 'GI_Device.php';
require_once 'GI_Measurement.php';
require_once 'GI_CSV.php';
require_once 'GI_Math.php';
require_once 'GI_LogFile.php';

set_include_path($curIncludePath . '/' . FRMWK_SUPER_VER);
require_once 'AbstractGI_Index.php';

//AbstractConfig
require_once 'GI_DBConfig.php';
require_once 'GI_ProjectConfig.php';
require_once 'GI_ApplicationConfig.php';
require_once 'GI_DBConnection.php';
require_once 'GI_S3Connection.php';
require_once 'GI_QBConnection.php';
//Framework
require_once 'GI_Object.php';
require_once 'GI_AuditModel.php';
require_once 'GI_View.php';
require_once 'GI_WidgetView.php';
require_once 'GI_SidebarView.php';
require_once 'GI_Controller.php';
require_once 'GI_MenuFactory.php';
require_once 'GI_MenuView.php';
require_once 'GI_SearchView.php';
require_once 'GI_PageBarView.php';
require_once 'GI_LoadMoreBtn.php';
require_once 'GI_Model.php';
require_once 'GI_ModelFactory.php';
require_once 'GI_TypeModelFactory.php';
require_once 'GI_Service.php';
require_once 'GI_FormRowView/GI_FormRowView.php';
require_once 'GI_FormRowView/GI_FormRowTaxableView.php';
require_once 'GI_FormRowableModel/GI_FormRowableModel.php';
require_once 'GI_FormRowableModel/GI_FormRowableTaxableModel.php';
require_once 'GI_FormStepView.php';
//GI_Autoload
require_once 'GI_Autoload/GI_Autoload.php';
//GI_DataMap
require_once 'GI_DataMap/GI_DataMap.php';
require_once 'GI_DataMap/GI_DataMapEntry.php';
//GI_DAO
require_once 'GI_DAO/GI_DAO.php';
require_once 'GI_DAO/AbstractTableColumnDAO.php';
require_once 'GI_DAO/AbstractTableDAO.php';

//Concrete
set_include_path('concrete/super/' . FRMWK_SUPER_VER);
require_once 'GI_Index.php';

require_once 'GI_DataMap/GenericDataMap.php';
require_once 'GI_DataMap/GenericDataMapEntry.php';
require_once 'GI_DAO/GenericDAO.php';
require_once 'GI_DAO/TableDAO.php';
require_once 'GI_DAO/TableColumnDAO.php';

set_include_path($curIncludePath);

set_include_path('config');

//Config
require_once 'client_config/config.database.php';
if(file_exists('config/client_config/config.keys.php')){
    require_once 'client_config/config.keys.php';
}
require_once 'client_config/config.project.php';
require_once 'client_config/config.info.php';
require_once 'client_config/config.rets.php';

require_once 'definitions/geo_definitions.php';
require_once 'definitions/code_lang_definitions.php';

require_once 'dbConfig.php';
require_once 'projectConfig.php';
require_once 'applicationConfig.php';

require_once 'connections/dbConnection.php';
require_once 'connections/S3Connection.php';
require_once 'connections/QBConnection.php';

// Include New Window Views
set_include_path('');
$abCoreIncludePath = 'framework/core/v4.0';
$conCoreIncludePath = 'concrete/core/v4.0';
require_once $abCoreIncludePath . '/view/layout/Windows/AbstractWindowView.php';
require_once $conCoreIncludePath . '/view/layout/Windows/WindowView.php';
require_once $abCoreIncludePath . '/view/layout/Windows/AbstractMainWindowView.php';
require_once $abCoreIncludePath . '/view/layout/Windows/AbstractListWindowView.php';
require_once $conCoreIncludePath . '/view/layout/Windows/MainWindowView.php';
require_once $conCoreIncludePath . '/view/layout/Windows/ListWindowView.php';
require_once $abCoreIncludePath . '/view/layout/Windows/AbstractSidebarView.php';
require_once $conCoreIncludePath . '/view/layout/Windows/SidebarView.php';
require_once $abCoreIncludePath . '/view/layout/Windows/AbstractFormStepView.php';
require_once $conCoreIncludePath . '/view/layout/Windows/FormStepView.php';

set_include_path('concrete/static');
require_once 'view/static_errorView.php';

set_include_path($curIncludePath);
