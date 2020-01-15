<?php

define ('STRINGS', serialize(array(
    'add' => 'add',
    'add_columns'=>'add columns',
    'add_core_columns'=>'add core columns',
    'add_entry' => 'Add Entry',
    'edit_entry' => 'Edit Entry',
    'add_field' => 'add field',
    'add_forms'=>'add forms',
    'add_html' => 'Add HTML',
    'add_role'=>'add role',
    'add_roles'=>'add roles',
    'add_role_group'=>'add role group',
//    'add_role_rank'=>'add role rank',
//    'add_role_ranks'=>'add role ranks',
    'add_tables'=>'add tables',
    'add_user' => 'add user',
    'add_users'=>'add users',
    'admin' => 'admin',
    'archived' => 'archived',
    'assigned' => 'assigned',
    'authored' => 'authored',
    'available_items' => 'available items',
    'carrier' => 'carrier',
    'cced' => 'CCed',
    'change' => 'change',
    'changed' => 'changed',
    'close' => 'close',
    'closed' => 'closed',
    'communication'=>'communication',
    'contacts' => 'contacts',
    'create' => 'create',
    'create_column' => 'create column',
    'create_form' => 'create form',
    'create_table'=>'create table',
    'dashboard' => 'dashboard',
    'deleted' => 'deleted',
    'delete_forms'=>'delete forms',
    'delete_roles'=>'delete roles',
    'delete_role_ranks'=>'delete role ranks',
    'delete_users'=>'delete users',
    'save' => 'save',
    'cancel' => 'cancel',
    'delete' => 'delete',
    'update' => 'update',
    'edit' => 'edit',
    'editable'=>'editable',
    'edit_core_table_row'=>'edit core table row',
    'edit_forms'=>'edit forms',
//    'edit_role_rank' => 'edit role rank',
//    'edit_role_ranks'=>'edit role ranks',
    'edit_role'=>'edit role',
    'edit_roles'=>'edit roles',
    'edit_role_group'=>'edit role group',
    'edit_table_row'=>'edit table row',
    'edit_user' => 'edit user',
    'edit_users'=>'edit users',
    'email' => 'email',
    'email_address' => 'email address',
    'email_templates' => "email templates",
    'enable_sort' => 'enable sort',
    'file' => 'file',
    'files' => 'files',
    'filter'=>'filter',
    'first_name' => 'first name',
    'form' => 'form',
    'form_layout'=>'form layout',
    'form_title'=>'form title',
    'forms' => 'forms',
    'group' => 'group',
    'groups' => 'groups',
    'highest' => 'highest',
    'html_content' => 'html content',
    'home' => 'home',
    'hot_list' => 'hot list',
    'insert_core_table_row'=>'insert core table row',
    'insert_table_row'=>'insert table row',
    'language' => 'language',
    'last_name' => 'last name',
    'link_form_to_table'=>'link form to table?',
    'log_in' => 'log in',
    'log_out' => 'log out',
//    'max_communication_rank'=>'max communication rank',
    'my_contacts' => 'my contacts',
    'my_files' => 'my files',
    'mobile' => 'mobile',
    'module' => 'module',
    'modules' => 'modules',
    'name' => 'name',
    'names' =>'names',
    'new_password' => 'new password',
    'notification'=>'notification',
    'notifications'=>'notifications',
    'options'=>'options',
    'organizations' => 'organizations',
    'password' => 'password',
    'permission' => 'permission',
    'permissions' => 'permissions',
    'primary_table' => 'primary table',
    'project' => 'project',
    'projects' => 'projects',
    'rank' => 'rank',
    're_enter_password' => 're-enter password',
    're_enter_new_password' => 're-enter new password',
    'reset_password' => 'reset password',
    'save_password' => 'save password',
    'role' => 'role',
    'roles' => 'roles',
    'role_group'=>'role group',
    'role_groups'=>'role groups',
//    'role_rank' => 'role rank',
//    'role_ranks' => 'role ranks',
    'save_html' => 'save html',
    'save_form' => 'save form',
    'setting' => 'setting',
    'settings' => 'settings',
    'shared' => 'shared',
    'shared_lists' => 'shared lists',
    'submit' => 'submit',
    'table' => 'table',
    'tables'=>'tables',
    'task' => 'task',
    'tasks' => 'tasks',
    'title' => 'title',
    'type' => 'type',
    'types' => 'types',
    'user' => 'user',
    'users' => 'users',
    'view_core_tables'=>'view core tables',
    'view_roles'=>'view roles',
    'view_role_ranks'=>'view role ranks',
    'view_role_groups'=>'view role groups',
    'view_tables' => 'view tables',
    'view_users'=>'view users',
    'upload' => 'upload',
    'upload_file' => 'upload file',
    'upload_files' => 'upload files',
    'upload_image' => 'upload image',
    'upload_images' => 'upload images',
    'register' => 'register',
    'search' => 'search',
    'phone' => 'phone',
    'phone_number' => 'phone number',
    'login_email' => 'Login Email'
)));

define ('ERROR_CODES', serialize(array(
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '412' => 'Precondition Failed',
    '414' => 'Request-URI Too Long',
    '415' => 'Unsupported Media Type',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '528491' => 'We couldn’t find the error you were looking for. This is an error within an error...</p><p><img src="resources/media/inception_error.jpg" title="An error within an error" alt="error within an error" />',
    '1234' => 'This isn’t a real error.',
    '1000' => 'Unable to save.',
    '1010' => 'Zip file could not be created.',
    '2000' => 'Missing or improper attributes provided.',
    '2001' => 'Unable to perform the specified action.',
    '2010' => 'Missing method.',
    '2500' => 'Missing data from AJAX response.',
    '3000' => 'Unable to delete.',
    '4000' => 'Unable to locate item.',
    '4001' => 'Unable to locate item with specified ID.',
    '5000' => 'Unable to send email',
    '6000' => 'Quickbooks Error - Message from Quickbooks Online:',
)));
