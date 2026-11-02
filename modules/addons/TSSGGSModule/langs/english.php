<?php

// main menu
$_LANG['admin.menu.home']       = 'Dashboard';
$_LANG['admin.menu.products']   = 'Products';
$_LANG['admin.menu.import']     = 'Import';
$_LANG['admin.menu.statistics'] = 'Statistics';
$_LANG['admin.menu.reports']    = 'Reports';
$_LANG['admin.menu.pricing']    = 'Pricing';
$_LANG['admin.menu.settings']   = 'Settings';
$_LANG['admin.menu.automationQueue'] = 'Automation Queue';
$_LANG['admin.menu.migration']       = 'Migration';
$_LANG['admin.menu.more']            = 'More';
$_LANG['admin.breadcrumbs.Migration_index'] = 'Migration';

$_LANG['admin.home.index.home_container.title']                 = 'Dashboard';
$_LANG['admin.home.index.home_container.period']                = 'Last month';
$_LANG['admin.home.index.home_container.attention_title']       = 'Automation needs attention';
$_LANG['admin.home.index.home_container.attention_body']        = 'Cron jobs are not running. Certificate processing and price updates may be affected.';
$_LANG['admin.home.index.home_container.attention_button']      = 'Review system';
$_LANG['admin.home.index.home_container.kpi_sales']             = 'Total sales';
$_LANG['admin.home.index.home_container.kpi_sales_detail']      = 'Paid sales · :currency';
$_LANG['admin.home.index.home_container.kpi_profit']            = 'Gross profit';
$_LANG['admin.home.index.home_container.kpi_profit_detail']     = 'Last month · :currency';
$_LANG['admin.home.index.home_container.kpi_issued']            = 'Certificates issued';
$_LANG['admin.home.index.home_container.kpi_issued_detail']     = 'Last month';
$_LANG['admin.home.index.home_container.kpi_expiring']          = 'Expiring certificates';
$_LANG['admin.home.index.home_container.kpi_expiring_detail']   = 'Within the next 90 days';
$_LANG['admin.home.index.home_container.version']               = 'Module version :version';

$_LANG['admin.home.index.widgets.overview_widget.title']        = 'Sales overview';
$_LANG['admin.home.index.widgets.overview_widget.statistics']   = 'All statistics';
$_LANG['admin.home.index.widgets.overview_widget.paid']         = 'Paid';
$_LANG['admin.home.index.widgets.overview_widget.unpaid']       = 'Unpaid';
$_LANG['admin.home.index.widgets.overview_widget.overdue']      = 'Overdue';
$_LANG['admin.home.index.widgets.overview_widget.period']       = 'Last month';

$_LANG['admin.home.index.widgets.announcements_widget.title']       = 'Announcements';
$_LANG['admin.home.index.widgets.announcements_widget.latest']      = 'Latest updates';
$_LANG['admin.home.index.widgets.announcements_widget.none']        = 'No announcements.';
$_LANG['admin.home.index.widgets.announcements_widget.unavailable'] = 'Announcements could not be loaded: :error';

$_LANG['admin.home.index.widgets.support_widget.title']          = 'Support assistance';
$_LANG['admin.home.index.widgets.support_widget.phone']          = 'Telephone: :phone';
$_LANG['admin.home.index.widgets.support_widget.contact']        = 'Contact support';
$_LANG['admin.home.index.widgets.support_widget.contact_vendor'] = 'Contact :vendor support';

$_LANG['admin.home.index.widgets.system_check_widget.title']     = 'System health';
$_LANG['admin.home.index.widgets.system_check_widget.ok']        = 'All running';
$_LANG['admin.home.index.widgets.system_check_widget.attention'] = 'Action required';
$_LANG['admin.home.index.widgets.system_check_widget.tasks']     = 'Scheduled tasks';
$_LANG['admin.home.index.widgets.system_check_widget.never']     = 'Never';
$_LANG['admin.home.index.widgets.system_check_widget.error']     = 'Error';
$_LANG['admin.home.index.widgets.system_check_widget.review']    = 'Review cron configuration';
$_LANG['admin.home.index.widgets.system_check_widget.gateway']         = 'Gateway connection';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_ok']      = 'Connected (:ms ms)';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_error']   = 'Unreachable';
$_LANG['admin.home.index.widgets.system_check_widget.gateway_recheck'] = 'Check the connection again';

$_LANG['admin.home.index.widgets.migration_widget.title']           = 'Legacy module migration';
$_LANG['admin.home.index.widgets.migration_widget.intro']           = 'Move your existing SSL services to this module.';
$_LANG['admin.home.index.widgets.migration_widget.services']        = ':n active services';
$_LANG['admin.home.index.widgets.migration_widget.choice_migrate']  = 'to be migrated';
$_LANG['admin.home.index.widgets.migration_widget.choice_skip']     = 'kept standalone';
$_LANG['admin.home.index.widgets.migration_widget.last_run']        = 'Migration #:id · :status';
$_LANG['admin.home.index.widgets.migration_widget.last_run_counts'] = ':done migrated, :failed failed, :skipped skipped';
$_LANG['admin.home.index.widgets.migration_widget.backups_until']   = 'Backups kept until :date';
$_LANG['admin.home.index.widgets.migration_widget.open']            = 'Open migration';

$_LANG['admin.home.index.widgets.partner_widget.title'] = 'Your partner growth manager';
$_LANG['admin.home.index.widgets.partner_widget.none']  = 'No partner growth manager assigned yet.';

$_LANG['admin.home.index.widgets.reporting_widget.title']      = 'Module issue reporting';
$_LANG['admin.home.index.widgets.reporting_widget.info']       = 'Share the module activity log with our support team: it lists errors and helps troubleshoot issues you or your clients run into.';
$_LANG['admin.home.index.widgets.reporting_widget.sendReport'] = 'Send report';


$_LANG['admin.migration.title']                 = 'Legacy module migration';
$_LANG['admin.migration.none_found']            = 'No legacy GoGetSSL or TheSSLStore module was found on this installation.';
$_LANG['admin.migration.card_products']         = 'Products';
$_LANG['admin.migration.card_products_sub']     = ':hidden hidden, :retired retired';
$_LANG['admin.migration.card_services']         = 'Active services';
$_LANG['admin.migration.card_orders']           = 'SSL orders';
$_LANG['admin.migration.card_subscriptions']    = 'Subscriptions';
$_LANG['admin.migration.card_autoinstall']      = 'AutoInstall (cPanel)';
$_LANG['admin.migration.card_queue_open']       = ':n open tasks';
$_LANG['admin.migration.card_unconfigured']     = 'Not configured';
$_LANG['admin.migration.card_unconfigured_sub'] = ':pre pre-ordered at the vendor, :unpaid unpaid';
$_LANG['admin.migration.support_full']          = 'Migration supported.';
$_LANG['admin.migration.support_partial']       = 'Migration supported with limits.';
$_LANG['admin.migration.support_unsupported']   = 'This legacy version cannot be migrated.';
$_LANG['admin.migration.files_missing']         = 'The legacy module files are no longer on the server (data only): a rollback restores the data but not a working legacy module.';
$_LANG['admin.migration.unsupported_found']     = 'Not supported by this module and left on the legacy module:';
$_LANG['admin.migration.other_modules']         = 'Other SSL modules present (not touched):';
$_LANG['admin.migration.choice_current']        = 'Decision from the setup wizard:';
$_LANG['admin.migration.choice_migrate']        = 'migrate';
$_LANG['admin.migration.choice_skip']           = 'install standalone (skip)';
$_LANG['admin.migration.choice_none']           = 'none yet';
$_LANG['admin.migration.btn_report']            = 'Download dry-run report (CSV)';
$_LANG['admin.migration.btn_start']             = 'Start migration';
$_LANG['admin.migration.btn_skip']              = 'Skip (keep standalone)';
$_LANG['admin.migration.credentials_already_set'] = 'API credentials: this module has its own.';
$_LANG['admin.migration.credentials_available']   = 'API credentials: will be copied from the legacy module if still empty when the migration starts.';
$_LANG['admin.migration.credentials_missing']     = 'API credentials: none in this module nor in the legacy module; enter them in Settings before migrating.';
$_LANG['admin.migration.start_defaults']        = 'Start migration: backup tables first; AutoInstall private keys are fetched from cPanel (TheSSLStore); the legacy addon is deactivated after the switch; legacy log tables are included in the backup.';
$_LANG['admin.migration.btn_start_nokeys']      = 'Start without fetching cPanel keys';
$_LANG['admin.migration.btn_start_keeplegacy']  = 'Start and keep the legacy addon active';
$_LANG['admin.migration.confirm_start']         = 'Start the migration now? Backup tables are created first. Do not configure SSL services while it runs.';
$_LANG['admin.migration.unsupported_version_hint'] = 'Update the legacy module to its latest version and reload this page, or keep it as a standalone module.';
$_LANG['admin.migration.run_title']             = 'Migration #:id (:source)';
$_LANG['admin.migration.run_stale']             = 'No progress for more than 10 minutes. The run continues while this page stays open; or roll it back.';
$_LANG['admin.migration.verify_result']         = 'Verification: :checked migrated services checked, :active active SSL services with complete data, :left legacy order rows left.';
$_LANG['admin.migration.exceptions']            = ':n exceptions (left on the legacy module)';
$_LANG['admin.migration.col_object']            = 'Object';
$_LANG['admin.migration.col_status']            = 'Status';
$_LANG['admin.migration.col_reason']            = 'Reason';
$_LANG['admin.migration.cron_title']            = 'Cron jobs';
$_LANG['admin.migration.cron_remove']           = 'Remove these legacy cron lines from the server:';
$_LANG['admin.migration.cron_add']              = 'Make sure these cron lines of this module exist:';
$_LANG['admin.migration.legacy_deactivated']    = 'Legacy addon deactivated: :result';
$_LANG['admin.migration.yes']                   = 'yes';
$_LANG['admin.migration.no']                    = 'no (deactivate it manually under Setup > Addon Modules)';
$_LANG['admin.migration.btn_log']               = 'Download migration log (CSV)';
$_LANG['admin.migration.backups_until']         = 'Backups kept until :date';
$_LANG['admin.migration.backups_deleted']       = 'Backups deleted; rollback no longer possible.';
$_LANG['admin.migration.btn_extend']            = 'Keep backups 14 more days';
$_LANG['admin.migration.btn_delete_backups']    = 'Delete backups now';
$_LANG['admin.migration.btn_rollback']          = 'Roll back this migration';
$_LANG['admin.migration.confirm_delete']        = 'Delete the backup tables? A rollback is no longer possible afterwards.';
$_LANG['admin.migration.confirm_rollback']      = 'Roll back? Products, orders and settings are restored from the backups and the legacy addon is reactivated. Services sold on this module since the migration are kept.';
$_LANG['admin.migration.runner_done']           = 'migrated';
$_LANG['admin.migration.runner_skipped']        = 'skipped';
$_LANG['admin.migration.runner_failed']         = 'failed';
$_LANG['admin.migration.runner_pending']        = 'pending';
$_LANG['admin.migration.runner_keep_open']      = 'Running. Keep this page open until it finishes.';
$_LANG['admin.migration.runner_finished']       = 'Finished.';
$_LANG['admin.migration.runner_reload']         = 'Reload to see the result';
$_LANG['admin.migration.state_preparing']       = 'Preparing (backups)';
$_LANG['admin.migration.state_running']         = 'Migrating services';
$_LANG['admin.migration.state_switching']       = 'Switching products and orders';
$_LANG['admin.migration.state_completed']       = 'Completed';
$_LANG['admin.migration.state_failed']          = 'Failed';
$_LANG['admin.migration.state_rolled_back']     = 'Rolled back';

$_LANG['admin.breadcrumbs.Configuration']       = 'Module Installation';
$_LANG['admin.menu.configuration']              = 'Module Installation';
$_LANG['admin.breadcrumbs.Configuration_step1'] = 'Step 1';
$_LANG['admin.breadcrumbs.Configuration_step2'] = 'Step 2';
$_LANG['admin.breadcrumbs.Configuration_step3'] = 'Step 3';
$_LANG['admin.breadcrumbs.Configuration_step4'] = 'Step 4';
$_LANG['admin.breadcrumbs.Configuration_step5'] = 'Step 5';
$_LANG['admin.breadcrumbs.Configuration_step6'] = 'Step 6';
$_LANG['admin.breadcrumbs.Configuration_step7'] = 'Step 7';

$_LANG['instalation.wizard.installation_steps']  = 'Installation steps';
$_LANG['instalation.wizard.module_installation'] = 'Module Installation';
$_LANG['instalation.wizard.step_of']             = 'Step %d of %d';
$_LANG['instalation.wizard.step1_name']          = 'Welcome';
$_LANG['instalation.wizard.step1_desc']          = 'Introduction';
$_LANG['instalation.wizard.step2_name']          = 'Compatibility check';
$_LANG['instalation.wizard.step2_desc']          = 'System requirements';
$_LANG['instalation.wizard.step3_name']          = 'Legacy modules';
$_LANG['instalation.wizard.step3_desc']          = 'Migrate existing data';
$_LANG['instalation.wizard.step4_name']          = 'API connection';
$_LANG['instalation.wizard.step4_desc']          = 'Credentials';
$_LANG['instalation.wizard.step5_name']          = 'Choose products';
$_LANG['instalation.wizard.step5_desc']          = 'Import catalog';
$_LANG['instalation.wizard.step6_name']          = 'Finalization';
$_LANG['instalation.wizard.step6_desc']          = 'Review & finish';

$_LANG['admin.configuration.content.step1.feature1_title'] = 'Import products';
$_LANG['admin.configuration.content.step1.feature1_desc']  = 'Bulk-create SSL products';
$_LANG['admin.configuration.content.step1.feature2_title'] = 'Auto pricing';
$_LANG['admin.configuration.content.step1.feature2_desc']  = 'Sync with vendor prices';
$_LANG['admin.configuration.content.step1.feature3_title'] = 'Automated issuance';
$_LANG['admin.configuration.content.step1.feature3_desc']  = 'Orders processed via API';

$_LANG['admin.configuration.content.step1.step1_title']   = 'STEP 1. Welcome';
$_LANG['admin.configuration.content.step1.first_p']       = 'Thank you for installing the <b>:moduleName</b>!';
$_LANG['admin.configuration.content.step1.second_p']      = 'This add-on will assist you in importing products such as SSL certificates and other security offerings into your WHMCS. All you need is an account, and you can start selling these products.';
$_LANG['admin.configuration.content.step1.third_p']       = 'Click the <b>"Next"</b> button so we can check whether your WHMCS installation is compatible.';
$_LANG['admin.configuration.content.step1.next_step_btn'] = 'Next step';

$_LANG['admin.configuration.content.step2.step2_title']                 = 'STEP 2. System Compatibility Check';
$_LANG['admin.configuration.content.step2.intro_passed']    = 'Your server meets the requirements. Warnings do not block the installation, but should be fixed.';
$_LANG['admin.configuration.content.step2.intro_failed']    = 'Some requirements are not met. Fix the failed items and reload this page to continue.';
$_LANG['admin.configuration.content.step2.col_check']       = 'Check';
$_LANG['admin.configuration.content.step2.col_required']    = 'Required';
$_LANG['admin.configuration.content.step2.col_found']       = 'Found';
$_LANG['admin.configuration.content.step2.col_status']      = 'Status';
$_LANG['admin.configuration.content.step2.status_passed']   = 'Passed';
$_LANG['admin.configuration.content.step2.status_failed']   = 'Failed';
$_LANG['admin.configuration.content.step2.status_warning']  = 'Warning';
$_LANG['admin.configuration.content.step2.check_php']       = 'PHP version';
$_LANG['admin.configuration.content.step2.hint_php']        = 'Upgrade PHP for WHMCS. The PHP version used by the cron jobs must meet the requirement too.';
$_LANG['admin.configuration.content.step2.check_whmcs']     = 'WHMCS version';
$_LANG['admin.configuration.content.step2.hint_whmcs']      = 'Upgrade WHMCS to a supported version.';
$_LANG['admin.configuration.content.step2.check_ext_curl']      = 'PHP extension: cURL';
$_LANG['admin.configuration.content.step2.hint_ext_curl']       = 'Needed for all API requests. Install or enable php-curl.';
$_LANG['admin.configuration.content.step2.check_ext_openssl']   = 'PHP extension: OpenSSL';
$_LANG['admin.configuration.content.step2.hint_ext_openssl']    = 'Needed to generate CSRs and private keys. Install or enable php-openssl.';
$_LANG['admin.configuration.content.step2.check_ext_json']      = 'PHP extension: JSON';
$_LANG['admin.configuration.content.step2.hint_ext_json']       = 'Needed for API requests. Install or enable php-json.';
$_LANG['admin.configuration.content.step2.check_ext_mbstring']  = 'PHP extension: mbstring';
$_LANG['admin.configuration.content.step2.hint_ext_mbstring']   = 'Install or enable php-mbstring.';
$_LANG['admin.configuration.content.step2.check_ext_pdo_mysql'] = 'PHP extension: PDO MySQL';
$_LANG['admin.configuration.content.step2.hint_ext_pdo_mysql']  = 'Install or enable php-mysql (pdo_mysql).';
$_LANG['admin.configuration.content.step2.check_storage']   = 'Storage folders';
$_LANG['admin.configuration.content.step2.hint_storage']    = 'The web server user must be able to write to modules/addons/TSSGGSModule/storage and its subfolders app, crons and logs.';
$_LANG['admin.configuration.content.step2.check_gateway']   = 'API gateway connection';
$_LANG['admin.configuration.content.step2.hint_gateway']    = 'The server could not reach :subject over HTTPS. Allow outgoing HTTPS (port 443) to this address; the API connection is tested again in step 4.';
$_LANG['admin.configuration.content.step2.next_step_btn']               = 'Next step';

$_LANG['admin.configuration.content.step3.step3_title']   = 'STEP 3. Legacy modules found';
$_LANG['admin.configuration.content.step3.intro_one']     = 'An earlier :modules module is installed on this WHMCS. Its products, orders, subscriptions and AutoInstall certificates can be moved to this module so that renewals, reissues and e-mails continue to work here.';
$_LANG['admin.configuration.content.step3.intro_many']    = 'Earlier :modules modules are installed on this WHMCS. Their products, orders, subscriptions and AutoInstall certificates can be moved to this module so that renewals, reissues and e-mails continue to work here. Choose per module:';
$_LANG['admin.configuration.content.step3.and']           = 'and';
$_LANG['admin.configuration.content.step3.version_unknown'] = 'version unknown';
$_LANG['admin.configuration.content.step3.support_full']    = 'Migration supported';
$_LANG['admin.configuration.content.step3.support_partial'] = 'Migration supported (partially)';
$_LANG['admin.configuration.content.step3.support_unsupported'] = 'Version too old';
$_LANG['admin.configuration.content.step3.fact_products']   = ':n products';
$_LANG['admin.configuration.content.step3.fact_services']   = ':active active services (:total in total)';
$_LANG['admin.configuration.content.step3.fact_orders']     = ':n SSL orders, :unconfigured not configured yet, :unpaid unpaid';
$_LANG['admin.configuration.content.step3.fact_subscriptions'] = ':acme ACME and :ais AutoInstall SSL subscriptions, :autoinstall certificates installed via cPanel';
$_LANG['admin.configuration.content.step3.unsupported_hint'] = 'Update the legacy module to its latest version first, then run the migration from the Migration page, or install this module as a new standalone module.';
$_LANG['admin.configuration.content.step3.runs_later']      = 'Nothing is changed yet. The migration starts from the Finalization step, after your API credentials are in place.';
$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Next step';

//$_LANG['admin.configuration.content.step3.step3_title']   = 'STEP 4. Vendor selection';
//$_LANG['admin.configuration.content.step3.next_step_btn'] = 'Next step';

$_LANG['admin.configuration.content.step4.step4_title']   = 'STEP 4. Connected to API';
$_LANG['admin.configuration.content.step4.next_step_btn'] = 'Next step';
$_LANG['admin.configuration.content.step4.credentials_prefilled'] = 'API credentials were copied from the :modules module. Test the connection and save, or replace them.';

$_LANG['admin.configuration.content.step5.step5_title']   = 'STEP 5. Choose Products';
$_LANG['admin.configuration.content.step5.next_step_btn'] = 'Next step';

$_LANG['admin.configuration.content.step6.step6_title'] = 'STEP 6. Finalization';
$_LANG['admin.configuration.content.step6.finalize']    = 'Finalize';


$_LANG['admin.configuration.shared.forms.vendor_selection_form.save']                           = 'Next Step';
$_LANG['admin.configuration.shared.providers.vendor_selection_provider.error_vendor']           = 'Please select one of the vendor';
$_LANG['admin.configuration.shared.providers.vendor_selection_provider.successVendorSelection'] = 'The configuration has been saved successfully';
$_LANG['admin.configuration.shared.providers.finalize_provider.successFinalize']                = 'The configuration has been saved successfully';
$_LANG['admin.configuration.shared.forms.vendor_selection_form.update_success']                 = 'The vendor configuration has been saved successfully';
$_LANG['admin.settings.index.forms.settings_form.next_step']                                    = 'Next Step';
$_LANG['admin.settings.index.providers.settings_provider.successAPI']                           = 'The API configuration has been saved successfully';

$_LANG['admin.configuration.shared.forms.finalize_form.finalize'] = 'Finalize';

$_LANG['admin.configuration.content.step6.title_list'] = 'Next steps';
$_LANG['admin.configuration.content.step6.item_list1'] = '1. Please visit <b>Pricing</b> section to enable products you wish to start reselling';
$_LANG['admin.configuration.content.step6.item_list2'] = '2. Setup <b>Renewal</b> rules in Settings -> Renewals';

$_LANG['admin.configuration.content.step6.title_alert']    = '<b>Congratulations!</b> You have successfully finalized the module installation';
$_LANG['admin.configuration.content.step6.step6_api']      = '<b>API Configuration</b> completed and operating';
$_LANG['admin.configuration.content.step6.step6_products'] = '<b>%s Products imported</b> and ready to final setup';


//===[Dashboard]========================================================================================================

$_LANG['admin.breadcrumbs.Home'] = 'Dashboard';
//update
//overview


//reporting
$_LANG['admin.home.index.modals.reporting_modal.sendReport']    = 'Send Report';
$_LANG['admin.home.index.forms.reporting_form.whmcsUrl']        = 'WHMCS URL';
$_LANG['admin.home.index.forms.reporting_form.moduleVersion']   = 'Module Version';
$_LANG['admin.home.index.forms.reporting_form.tssPartnerId']    = 'TheSSLSTORE Partner ID';
$_LANG['admin.home.index.forms.reporting_form.ggsPartnerId']    = 'GoGetSSL Partner ID';
$_LANG['admin.home.index.forms.reporting_form.phpVersion']      = 'PHP Version';
$_LANG['admin.home.index.forms.reporting_form.productionMode']  = 'Production Mode';
$_LANG['admin.home.index.forms.reporting_form.cronStatus']      = 'CRON Status';
$_LANG['admin.home.index.forms.reporting_form.fromDate']        = 'From Date';
$_LANG['admin.home.index.forms.reporting_form.toDate']          = 'To Date';
$_LANG['admin.home.index.forms.reporting_form.noModifiedFiles'] = 'Congratulations! No modified files';
$_LANG['admin.home.index.forms.reporting_form.modifiedFile']    = 'Attention! We found 1 modified file';
$_LANG['admin.home.index.forms.reporting_form.modifiedFiles']   = 'Attention! We found :modifiedCount modified files';

$_LANG['admin.home.index.forms.reporting_form.issueDate']           = 'Issue Date';
$_LANG['admin.home.index.forms.reporting_form.modifiedModuleFiles'] = 'Modified Module Files';
$_LANG['admin.home.index.forms.reporting_form.issueDetails']        = 'Issue Details';


$_LANG['admin.home.index.forms.reporting_form.send']     = 'Send';
$_LANG['admin.home.index.forms.reporting_form.download'] = 'Download Log File';
$_LANG['admin.home.index.forms.reporting_form.cancel']   = 'Cancel';

$_LANG['admin.home.index.providers.reporting_provider.logDownloadedSuccessfully'] = 'Success';

$_LANG['admin.home.index.forms.reporting_form.send_success'] = 'Your activity log was successfully dispatched to our team! We will reach out to you shortly with more information regarding your issue.';


//system check


//Announcements

//manager

//support


//assistance


//systemcheck

//===[Statistics]=======================================================================================================

$_LANG['admin.breadcrumbs.Statistics']                               = 'Statistics';
$_LANG['admin.statistics.index.widgets.total_income_widget.title']   = 'Total Income';
$_LANG['admin.statistics.index.widgets.product_income_widget.title'] = 'Product Income (Top 5)';
$_LANG['admin.statistics.index.widgets.resellers_widget.title']      = 'Most Profitable Resellers';
$_LANG['admin.statistics.index.widgets.invoices_widget.title']       = 'Latest Invoices';

$_LANG['admin.statistics.index.graphs.total_income_graph.IncomeGraphSetTitle'] = 'Income :currencySymbol (Last Six Months)';

$_LANG['admin.statistics.index.widgets.resellers_widget.clientId'] = 'ID';
$_LANG['admin.statistics.index.widgets.resellers_widget.client']   = 'Client';
$_LANG['admin.statistics.index.widgets.resellers_widget.paid']     = 'Paid';

$_LANG['admin.statistics.index.widgets.invoices_widget.invoiceId'] = 'ID';
$_LANG['admin.statistics.index.widgets.invoices_widget.client']    = 'Client';
$_LANG['admin.statistics.index.widgets.invoices_widget.status']    = 'Status';
$_LANG['admin.statistics.index.widgets.invoices_widget.amount']    = 'Amount';
$_LANG['admin.statistics.index.widgets.invoices_widget.date']      = 'Date';


//===[Import]===========================================================================================================
$_LANG['admin.breadcrumbs.Import']                   = 'Import';
$_LANG['admin.import.index.import_container.single'] = 'Single Order';
$_LANG['admin.import.index.import_container.csv']    = 'From CSV';


$_LANG['admin.import.index.forms.import_form.remoteOrderId']       = 'Order ID';
$_LANG['admin.import.index.forms.import_form.clientId']            = 'Client';
$_LANG['admin.import.index.forms.import_form.payMethod']           = 'Payment Method';
$_LANG['admin.import.index.forms.import_form.vendor']              = 'Provider';
$_LANG['admin.import.index.forms.import_form.generateInvoice']     = 'Generate Invoice';
$_LANG['admin.import.index.forms.import_form.import']              = 'Import';
$_LANG['admin.import.index.forms.import_form.create_success']      = 'Import completed successfully';
$_LANG['admin.import.index.forms.import_form.productImportErrors'] = 'Errors occurred during import, please check logs for details.';

$_LANG['admin.import.index.forms.import_form_csv.csv']                 = 'CSV';
$_LANG['admin.import.index.forms.import_form_csv.vendor']              = 'Provider';
$_LANG['admin.import.index.forms.import_form_csv.generateInvoice']     = 'Generate Invoice';
$_LANG['admin.import.index.forms.import_form_csv.import']              = 'Import';
$_LANG['admin.import.index.forms.import_form_csv.create_success']      = 'Import completed successfully';
$_LANG['admin.import.index.forms.import_form_csv.productImportErrors'] = 'Errors occurred during import, please check logs for details.';

$_LANG['admin.import.index.forms.template_download_form.download']        = 'Download CSV Template';
$_LANG['admin.import.index.providers.template_download_provider.success'] = 'Success';


//===[Reports]==========================================================================================================

$_LANG['admin.breadcrumbs.Reports'] = 'Reports';

//tabs
$_LANG['admin.reports.index.reports_container.renewalReport']       = 'Renewal Report';
$_LANG['admin.reports.index.reports_container.datewiseSalesReport'] = 'Datewise Sales Report';
$_LANG['admin.reports.index.reports_container.profitLossReport']    = 'Profit & Loss Report';

//renewal datatable
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.date']           = 'Date';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.storeId']        = 'Store ID';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.clientDetails']  = 'Client Details';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.productDetails'] = 'Product Details';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.issueDate']      = 'Issue Date';
$_LANG['admin.reports.index.data_tables.renewal.array_data_provider.data_table.expirationDate'] = 'Expiration Date';
//renewal datatable filters
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.renewalPeriod']   = 'Selection';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.all']     = 'All';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last30']  = 'Expired in 30-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last60']  = 'Expired in 60-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.last90']  = 'Expired in 90-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.lastAll'] = 'All Expired';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next30']  = 'Expires in 30-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next60']  = 'Expires in 60-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.next90']  = 'Expires in 90-days';
$_LANG['admin.reports.index.data_tables.renewal.providers.filter_provider.nextAll'] = 'All upcoming expirations';
$_LANG['admin.reports.index.data_tables.renewal.forms.filter_form.search']          = 'Search';
//download button
$_LANG['admin.reports.index.data_tables.renewal.forms.download_form.downloadCsv']                     = 'Export to CSV';
$_LANG['admin.reports.index.data_tables.renewal.providers.download_provider.csvExportedSuccessfully'] = 'Successfully exported';

//Datewise Sales datatable
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.date']           = 'Date';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.storeId']        = 'Store ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.clientDetails']  = 'Client Details';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.productDetails'] = 'Product Details';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.salesAmount']    = 'Sales Amt';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.cost']           = 'Cost';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.payment']        = 'Payment';
$_LANG['admin.reports.index.data_tables.datewise_sales.array_data_provider.data_table.paymentStatus']  = 'Payment Status';
//Datewise Sales datatable filters
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.fromDate']          = 'From Date';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.toDate']            = 'To Date';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.brand']             = 'Brand';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.productId']         = 'Product Name';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.sslStatus']         = 'SSL Status';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.vendorOrderId']     = 'Vendor Order ID';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.all']       = 'All';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.active']    = 'Active';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.pending']   = 'Pending';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.expired']   = 'Expired';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.filter_provider.cancelled'] = 'Cancelled';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.search']            = 'Search';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.advancedSearch']    = 'Advanced Search';
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.filter_form.basicSearch']       = 'Simple Search';
//download button
$_LANG['admin.reports.index.data_tables.datewise_sales.forms.download_form.downloadCsv']                     = 'Export to CSV';
$_LANG['admin.reports.index.data_tables.datewise_sales.providers.download_provider.csvExportedSuccessfully'] = 'Successfully exported';
//header
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.paid']    = 'Paid:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.unpaid']  = 'Unpaid:';
$_LANG['admin.reports.index.data_tables.datewise_sales.widgets.header.overdue'] = 'Overdue:';


//ProfitLoss Datatable
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.date']           = 'Date';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.storeId']        = 'Store ID';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.clientDetails']  = 'Client Details';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.productDetails'] = 'Product Details';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.type']           = 'Type';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.brand']          = 'Brand';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.status']         = 'Status';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.salesAmount']    = 'Sales Amt';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.cost']           = 'Cost';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.grossProfit']    = 'Gross Profit';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.payment']        = 'Payment';
$_LANG['admin.reports.index.data_tables.profit_loss.array_data_provider.data_table.paymentStatus']  = 'Payment Status';
//ProfitLoss Datatable filters
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.fromDate'] = 'From Date';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.toDate']   = 'To Date';
$_LANG['admin.reports.index.data_tables.profit_loss.forms.filter_form.search']   = 'Search';
//download button
$_LANG['admin.reports.index.data_tables.profit_loss.forms.download_form.downloadCsv']                     = 'Export to CSV';
$_LANG['admin.reports.index.data_tables.profit_loss.providers.download_provider.csvExportedSuccessfully'] = 'Successfully exported';
//header
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.paid']             = 'Paid:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.totalGrossProfit'] = 'Total Gross Profit:';
$_LANG['admin.reports.index.data_tables.profit_loss.widgets.header.certificateCount'] = 'No. of certificate issued:';

//shared

$_LANG['admin.reports.index.data_tables.shared.records_provider.country'] = 'Country:';
$_LANG['admin.reports.index.data_tables.shared.records_provider.domain']  = 'Domain:';

//===[Settings]=========================================================================================================

$_LANG['admin.breadcrumbs.Settings'] = 'Settings';
//sidebar

$_LANG['admin.settings.index.widgets.sidebar.title']           = 'Settings';
$_LANG['admin.settings.index.widgets.sidebar.apiSettings']     = 'API Settings';
$_LANG['admin.settings.index.widgets.sidebar.sslSettings']     = 'SSL Certificates';
$_LANG['admin.settings.index.widgets.sidebar.financeSettings'] = 'Finance Settings';
$_LANG['admin.settings.index.widgets.sidebar.cronSettings']    = 'CRON Settings';


//api settings

$_LANG['admin.settings.index.widgets.api_settings_widget.title']               = 'API Settings';
$_LANG['admin.settings.index.forms.api_settings_form.tss']                     = 'The SSL STORE API Settings';
$_LANG['admin.settings.index.forms.api_settings_form.ggs']                     = 'GoGetSSL API Settings';
$_LANG['admin.settings.index.forms.api_settings_form.tssLivePartnerCode']      = 'Live Partner Code';
$_LANG['admin.settings.index.forms.api_settings_form.tssLiveAuthToken']        = 'Live Auth Token';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLivePartnerCode']      = 'Partner Code';
$_LANG['admin.settings.index.forms.api_settings_form.ggsLiveAuthToken']        = 'Auth Token';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestPartnerCode']      = 'Sandbox Partner Code';
$_LANG['admin.settings.index.forms.api_settings_form.tssTestAuthToken']        = 'Sandbox Auth Token';
$_LANG['admin.settings.index.forms.api_settings_form.OperationMode']           = 'Operation Mode';
$_LANG['admin.settings.index.forms.api_settings_form.save']                    = 'Save';
$_LANG['admin.settings.index.forms.api_settings_form.next_step']               = 'Next Step';
$_LANG['admin.settings.index.forms.api_settings_form.testConnection']          = 'Test Connection';
$_LANG['admin.settings.index.forms.api_settings_form.update_success']          = 'Settings updated successfully';
$_LANG['admin.settings.index.forms.api_settings_form.testconnection_success']  = 'Successfully connected';
$_LANG['admin.settings.index.providers.api_settings_provider.connectionError'] = 'Connection Error';
$_LANG['admin.settings.index.providers.api_settings_provider.successAPI']      = 'The connection was successful';


//ssl settings
//validation
$_LANG['admin.settings.index.widgets.ssl_settings_widget.title']                            = 'SSL Certificates';
$_LANG['admin.settings.index.forms.ssl_settings_form.sslValidation']                        = 'SSL Validation';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation']               = 'Disable Email Validation';
$_LANG['admin.settings.index.forms.ssl_settings_form.disableEmailValidation_description']   = 'No Email validation will be available for all SSL certificates, only HTTP/DNS';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders']               = 'Auto Admin/Tech Details For DV Orders';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoDetailsForDvOrders_description']   = 'The module will use the Customer\'s Profile for Admin/Tech details';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes']            = 'Wildcard DNS Validation Timeout (minutes)';
$_LANG['admin.settings.index.forms.ssl_settings_form.wildcardDnsTimeoutMinutes_description'] = 'AutoInstall (cPanel): if a wildcard certificate DNS validation is not completed within this many minutes, the system will automatically switch to Email validation and notify the client. Default: 65';
$_LANG['admin.settings.index.providers.ssl_settings_provider.wildcardDnsTimeoutMinutesInvalidValue'] = 'The value should be a whole number of minutes greater than 0';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled']             = 'Skip cPanel TLS Certificate Verification';
$_LANG['admin.settings.index.forms.ssl_settings_form.cpanelTlsVerifyDisabled_description'] = 'AutoInstall (cPanel): do not verify the WHM/cPanel server TLS certificate. Only enable this for servers with a self-signed certificate - the WHM credentials are sent with every request. Default: off';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails']             = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.ssl_settings_form.useFixedTechnicalDetails_description'] = '';
$_LANG['admin.settings.index.forms.ssl_settings_form.firstName']                            = 'First Name';
$_LANG['admin.settings.index.forms.ssl_settings_form.lastName']                             = 'Last Name';
$_LANG['admin.settings.index.forms.ssl_settings_form.companyName']                          = 'Organization Name';
$_LANG['admin.settings.index.forms.ssl_settings_form.jobTitle']                             = 'Job Title';
$_LANG['admin.settings.index.forms.ssl_settings_form.emailAddress']                         = 'Email Address';
$_LANG['admin.settings.index.forms.ssl_settings_form.address']                              = 'Address';
$_LANG['admin.settings.index.forms.ssl_settings_form.city']                                 = 'City';
$_LANG['admin.settings.index.forms.ssl_settings_form.zipCode']                              = 'Zip Code';
$_LANG['admin.settings.index.forms.ssl_settings_form.stateRegion']                          = 'State/Region';
$_LANG['admin.settings.index.forms.ssl_settings_form.postCode']                             = 'Postal Code';
$_LANG['admin.settings.index.forms.ssl_settings_form.country']                              = 'Country';
//validations
$_LANG['admin.settings.index.forms.firstName.customAttributes.useFixedTechnicalDetails']    = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.firstName.customValues.useFixedTechnicalDetails.1']      = 'Enabled';
$_LANG['admin.settings.index.forms.lastName.customAttributes.useFixedTechnicalDetails']     = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.lastName.customValues.useFixedTechnicalDetails.1']       = 'Enabled';
$_LANG['admin.settings.index.forms.companyName.customAttributes.useFixedTechnicalDetails']  = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.companyName.customValues.useFixedTechnicalDetails.1']    = 'Enabled';
$_LANG['admin.settings.index.forms.jobTitle.customAttributes.useFixedTechnicalDetails']     = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.jobTitle.customValues.useFixedTechnicalDetails.1']       = 'Enabled';
$_LANG['admin.settings.index.forms.emailAddress.customAttributes.useFixedTechnicalDetails'] = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.emailAddress.customValues.useFixedTechnicalDetails.1']   = 'Enabled';
$_LANG['admin.settings.index.forms.address.customAttributes.useFixedTechnicalDetails']      = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.address.customValues.useFixedTechnicalDetails.1']        = 'Enabled';
$_LANG['admin.settings.index.forms.city.customAttributes.useFixedTechnicalDetails']         = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.city.customValues.useFixedTechnicalDetails.1']           = 'Enabled';
$_LANG['admin.settings.index.forms.zipCode.customAttributes.useFixedTechnicalDetails']      = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.zipCode.customValues.useFixedTechnicalDetails.1']        = 'Enabled';
$_LANG['admin.settings.index.forms.stateRegion.customAttributes.useFixedTechnicalDetails']  = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.stateRegion.customValues.useFixedTechnicalDetails.1']    = 'Enabled';
$_LANG['admin.settings.index.forms.postCode.customAttributes.useFixedTechnicalDetails']     = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.postCode.customValues.useFixedTechnicalDetails.1']       = 'Enabled';
$_LANG['admin.settings.index.forms.country.customAttributes.useFixedTechnicalDetails']      = 'Use Fixed Technical Details';
$_LANG['admin.settings.index.forms.country.customValues.useFixedTechnicalDetails.1']        = 'Enabled';

//csr generator
$_LANG['admin.settings.index.forms.ssl_settings_form.csrGenerator']            = 'CSR Generator';
$_LANG['admin.settings.index.forms.ssl_settings_form.enableCsrGenerator']      = 'Enable CSR Generator';
$_LANG['admin.settings.index.forms.ssl_settings_form.useProfileDetailsForCsr'] = 'Use Profile Details For CSR';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultCsrCountry']       = 'Default CSR Country';


$_LANG['admin.settings.index.forms.ssl_settings_form.renewals']                                                 = 'Renewals (Standard Billing)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringOrders']                                          = 'Recurring Orders';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeOrders']                                            = 'One-time Orders';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonBeforeExpiration']                       = 'Visible "Renew" Button Before Expiration';
$_LANG['admin.settings.index.forms.ssl_settings_form.visibleRenewButtonAfterExpiration']                        = 'Visible "Renew" Button After Expiration';
$_LANG['admin.settings.index.forms.ssl_settings_form.renewOrderViaExistingOrder']                               = 'Renew Order Via Existing Order';
$_LANG['admin.settings.index.forms.ssl_settings_form.automaticProcessingOfRenewalOrders']                       = 'Automatic Process of Renewal Orders';
$_LANG['admin.settings.index.forms.ssl_settings_form.setTheUnpaidStatusForRenewalInvoices']                     = 'Set The Unpaid Status For Renewal Invoices';
$_LANG['admin.settings.index.forms.ssl_settings_form.additionalDaysForRenewalOrder']                            = 'Additional Days For Renewal Order (Sectigo)';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire']                              = 'Auto Reissue Days Before Expiration';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoReissueDaysBeforeExpire_description']                  = 'AutoInstall (cPanel) certificates are reissued automatically this many days before the certificate expires, as long as the subscription continues. 0 disables automatic reissue. Default: 7';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoice']                   = 'Create Automatic Renewal Invoice';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpiry']                                = 'Days Before Expiry';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications']                     = 'Send Expiration Notifications';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotifications_description']         = 'Days before the expiry on which the client gets a reminder e-mail, comma separated, 0-30. Works with or without automatic renewal invoices: with the invoice switch off this is reminder-only mode.';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsInvalidValue'] = 'Values should be in the range of 0-30';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeCreateAutomaticRenewalInvoice']                     = 'Create Automatic Renewal Invoice';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeDaysBeforeExpiry']                                  = 'Days Before Expiry';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications']                       = 'Send Expiration Notifications';
$_LANG['admin.settings.index.forms.ssl_settings_form.oneTimeSendExpirationNotifications_description']           = 'Comma separated, from 0-30';
$_LANG['admin.settings.index.providers.ssl_settings_provider.oneTimeSendExpirationNotificationsInvalidValue']   = 'Values should be in the range of 0-30';

$_LANG['admin.settings.index.forms.ssl_settings_form.renewalsSubscription']                                     = 'Renewals (Subscription Billing)';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringCreateAutomaticRenewalInvoiceSubscription']       = 'Create Automatic Renewal Invoice';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringDaysBeforeExpirySubscription']                    = 'Days Before Expiry';
$_LANG['admin.settings.index.forms.ssl_settings_form.recurringSendExpirationNotificationsSubscription']         = 'Send Expiration Notifications';
$_LANG['admin.settings.index.providers.ssl_settings_provider.recurringSendExpirationNotificationsSubscription'] = 'Values should be in the range of 0-30';

$_LANG['admin.settings.index.forms.ssl_settings_form.save']           = 'Save';
$_LANG['admin.settings.index.forms.ssl_settings_form.update_success'] = 'Settings updated successfully';

//finance settings

$_LANG['admin.settings.index.widgets.finance_settings_widget.title']     = 'Finance Settings';
$_LANG['admin.settings.index.forms.finance_settings_form.majorSettings'] = 'Major Settings';
$_LANG['admin.settings.index.forms.finance_settings_form.exchangeRate']  = 'Exchange Rate';

$_LANG['admin.settings.index.forms.finance_settings_form.profitMargin'] = 'Desired Profit Margin';
$_LANG['admin.settings.index.forms.finance_settings_form.currency']     = 'Default Currency';
$_LANG['admin.settings.index.forms.finance_settings_form.rate']         = 'Set The Rate';
$_LANG['admin.settings.index.forms.finance_settings_form.info']         = 'Your WHMCS doesn\'t have a USD currency defined. Your default currency is :defaultCurrency. Please provide a USD to :defaultCurrency conversion rate.<br><br>For example, if rate is  1.20 and the product price is 1.00 USD, then in WHMCS, the price will be set to 1.20 :defaultCurrency.';


$_LANG['admin.settings.index.forms.finance_settings_form.save']           = 'Save';
$_LANG['admin.settings.index.forms.finance_settings_form.update_success'] = 'Settings updated successfully';

//cron settings
$_LANG['admin.settings.index.widgets.cron_settings_widget.title'] = 'CRON Settings';

//cron settings titles
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_processing'] = '<b>Task runner (optional)</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ProductPricingUpdate'] = 'Prices and catalogue update';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificatesProcessing'] = 'Pending orders check';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLCertificates'] = 'Issued orders sync';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_RenewalNotifyCertificate'] = 'Renewals, reminders and expiry';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_ReSyncProducts'] = '<b>Catalogue refresh</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommandTitle_SSLAutoInstall'] = 'AutoInstall queue (cPanel)';

//cron settings descriptions
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ProductPricingUpdate'] = 'Reads the reseller catalogue and prices from the gateway, refreshes the product configuration, announcements and your account manager, and updates the selling price of every product that has automatic pricing on (cost + your margin). Recommended: once a day.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificates'] = 'Asks the gateway which orders changed since the last run (renewed, expired, cancelled, revalidated) and updates those services; it also re-checks up to 50 orders that were not updated for a day. Once a day is enough: an issued certificate does not change often.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_RenewalNotifyCertificate'] = 'Once a day: expiry and reissue reminder e-mails, automatic renewal invoices, ACME / AutoInstall subscription invoices, and the termination (with vendor cancellation) of services that were not paid by their expiry date. Do not disable it if you bill renewals automatically.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLCertificatesProcessing'] = 'Follows the orders that were submitted and wait for the certificate authority. When a certificate is issued it is stored, the due date is set and the certificate e-mail is sent. Issued orders are not checked here again. Recommended: every 5 minutes, so clients get their certificate quickly.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_ReSyncProducts'] = 'Refreshes the product configuration (configurable options, SAN limits), the announcements shown on the dashboard and the details of your account manager. Recommended: once a day. Command to schedule:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_SSLAutoInstall'] = 'Works through the AutoInstall queue: domain validation, installation on cPanel, renewals and automatic reissue. It runs whenever the module cron runs (every 5 minutes).';
$_LANG['admin.settings.index.widgets.cron_settings_widget.cronCommand_processing'] = 'Task runner of the module framework. This version queues nothing for it, so this line is optional. Last run: ';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1'] = 'How often it runs';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2'] = 'How often it runs';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3'] = 'How often it runs';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4'] = 'Catalogue refresh - how often it runs';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5'] = 'How often it runs';
$_LANG['admin.settings.index.forms.cron_configuration_form.save']                                 = 'Save';
$_LANG['admin.settings.index.forms.cron_configuration_form.update_success']                       = 'The cron settings updated successfully';


//===[Products]=========================================================================================================

$_LANG['admin.breadcrumbs.Products'] = 'Products';

// Automation Queue (AutoInstall cron tasks)
$_LANG['admin.breadcrumbs.AutomationQueue'] = 'Automation Queue';
$_LANG['admin.automation_queue.index.automation_queue_container.queueInfo'] = 'AutoInstall tasks (validation, installation, reissue) processed by the cron. A task in the Error status will not be processed again until it is reset.';

$_LANG['admin.automation_queue.index.data_tables.queue.data_table.id']       = 'ID';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.service']  = 'Service';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.domain']   = 'Domain';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.type']     = 'Type';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.status']   = 'Status';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.attempts'] = 'Attempts';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.message']  = 'Message';
$_LANG['admin.automation_queue.index.data_tables.queue.data_table.updated']  = 'Last Update';

$_LANG['admin.automation_queue.index.data_tables.queue.buttons.reset_task_button.resetTask'] = 'Reset Task';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.title']       = 'Reset Task';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.submit']      = 'Submit';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.cancel']      = 'Cancel';
$_LANG['admin.automation_queue.index.data_tables.queue.modals.reset_task_modal.description'] ='Are you sure you want to reset this task? Its status will be changed to Pending and the attempts counter will be cleared, so the cron will process it again during the next run.';
$_LANG['admin.automation_queue.index.data_tables.queue.forms.reset_task_form.update_success'] = 'The task has been reset and will be processed by the next cron run';

//info
$_LANG['admin.products.index.products_container.productsInfo'] = 'To import a product, please select it and click the Import button.';
$_LANG['admin.configuration.content.step5.productsInfo']       = 'To import a product, please select it and click the Import button.';

//Datatable
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.id']          = 'No';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.productName'] = 'Product Name';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.brand']       = 'Brand';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.validation']  = 'Validation';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.provider']    = 'Provider';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.status']      = 'Status';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing']     = 'Billing type';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_recurring'] = 'Recurring';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_onetime']   = 'One time';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.billing_free']      = 'Free';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.imported']    = 'Imported';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.import']      = 'Import';


$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.reSync']           = 'Re-Sync Products';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.reSync'] = 'Re-Sync Products';
$_LANG['admin.products.index.data_tables.products.modals.re_sync_modal.description']      = 'Are you sure you want to re-synchronize the products?';
$_LANG['admin.products.index.data_tables.products.forms.re_sync_form.create_success']     = 'Successfully synchronized products';


//import form
$_LANG['admin.products.index.data_tables.products.modals.import_modal.title']               = 'Import';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypeIndividual'] = 'Import Product With Individual Pricing';
$_LANG['admin.products.index.data_tables.products.forms.import_form.pricingTypePercent']    = 'Import Product With % Margin';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroup']          = 'Product Group';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productGroupName']      = 'Product Group Name';
$_LANG['admin.products.index.data_tables.products.forms.import_form.currency']              = 'Currency';
$_LANG['admin.products.index.data_tables.products.forms.import_form.rate']                  = 'Set The Rate';
$_LANG['admin.products.index.data_tables.products.forms.import_form.info']                  = 'Your WHMCS doesn\'t have a USD currency defined. Your default currency is :defaultCurrency. Please provide a USD to :defaultCurrency conversion rate.<br><br>For example, if rate is  1.20 and the product price is 1.00 USD, then in WHMCS, the price will be set to 1.20 :defaultCurrency.';


$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin']             = 'Profit Margin';
$_LANG['admin.products.index.data_tables.products.forms.import_form.profitMargin_description'] = '% Margin will apply to the price of all the products. You can change it later on in the Pricing';
$_LANG['admin.products.index.data_tables.products.forms.import_form.description']              = 'Note: The price of all the products will be setup base on the "Base Conv. Rate" of the selected Currency Code and USD Currency Code. Make sure you have set up the correct "Base Conv. Rate" for each currency. (The default price of all the products from TheSSLStore API is in USD)';
$_LANG['admin.products.index.data_tables.products.forms.import_form.create_success']           = 'Import completed successfully';
$_LANG['admin.products.index.data_tables.products.forms.import_form.productCreationFailed']    = 'Import Failed';


//product configuration form
$_LANG['admin.products.index.data_tables.products.modals.configuration_modal.title']                  = 'Product Configuration';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.remoteProductName']        = 'Product';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.productName']              = 'Product Name';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.description']              = 'Description';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.autoSetup']                = 'Auto Setup';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOrder']   = 'Automatically setup the product as soon as an order is placed';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupPayment'] = 'Automatically setup the product as soon as the first payment is received';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOn']      = 'Automatically setup the product when you manually accept a pending order';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.autoSetupOff']     = 'Do not automatically setup this product';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.hidden']                   = 'Product Hidden';

$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableSan']        = 'Enable SANs';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedSan']      = 'Included Single SANs';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.enableWildcard']   = 'Enable Wildcard SANs';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.includedWildcard'] = 'Included Wildcard SANs';


$_LANG['admin.products.index.data_tables.products.forms.configuration_form.update_success'] = 'Configuration updated successfully';

//default option
$_LANG['choseProductGroup']     = '--- Chose Product Group ---';
$_LANG['createNewProductGroup'] = '--- New Product Group ---';


//===[Pricing]==========================================================================================================

$_LANG['admin.breadcrumbs.Pricing'] = 'Pricing';
//Datatable
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.id']          = 'No';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.productName'] = 'Product Name';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.brand']       = 'Brand';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.validation']  = 'Validation';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.provider']    = 'Provider';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.disabled']    = 'Disabled';

$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPriceStrict'] = 'Annually';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.annuallyPrice']       = 'Annually/One-Time';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.bienniallyPrice']     = 'Biennially';
$_LANG['admin.pricing.index.data_tables.pricing.array_data_provider.data_table.trienniallyPrice']    = 'Triennially';

//pricing form
$_LANG['admin.pricing.index.data_tables.pricing.modals.pricing_modal.title']        = 'Pricing';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.options']        = 'Options';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.annuallyStrict'] = 'Annually';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.annually']       = 'Annually/One-Time';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.biennially']     = 'Biennially';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.triennially']    = 'Triennially';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.update_success'] = 'Prices updated successfully';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.autoUpdate']     = 'Auto Update';
$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.oneTime']        = 'One Time';

$_LANG['admin.pricing.index.data_tables.pricing.forms.pricing_form.info'] = 'Auto Update Information';

$_LANG['acme_order_requires_at_least_one_san'] = 'To order this product you must select at least 1 Additional Single domain SAN or Additional Wildcard SAN.';




$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs']   = 'GoGetSSL module (SSLCENTERWHMCS)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_ggs_description'] = 'Migrate: products, orders, subscriptions and AutoInstall certificates move to this module after the next steps (backups first, rollback for 14 days). Standalone: the legacy module keeps running untouched.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss']   = 'TheSSLStore module (thesslstorefullv2)';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.choice_tss_description'] = 'Migrate: products, orders, subscriptions and AutoInstall certificates move to this module after the next steps (backups first, rollback for 14 days). Standalone: the legacy module keeps running untouched.';
$_LANG['admin.configuration.shared.forms.legacy_choice_form.next_step_btn'] = 'Next step';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_migrate'] = 'Migrate to this module';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.option_skip']    = 'Install as a new standalone module';
$_LANG['admin.configuration.shared.providers.legacy_choice_provider.saved']          = 'Saved';

//general settings
$_LANG['admin.settings.index.widgets.sidebar.generalSettings']                               = 'General (all products)';
$_LANG['admin.settings.index.widgets.general_settings_widget.title']                         = 'General Settings';
$_LANG['admin.settings.index.forms.general_settings_form.clientArea']                        = 'Client Area';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary']                     = 'SSL Summary';
$_LANG['admin.settings.index.forms.general_settings_form.clientSummary_description']         = 'Show the client\'s SSL orders (all, unpaid, processing, expiring soon) on the client area home page and in the sidebar';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates']                  = 'Pages With The SSL Sidebar';
$_LANG['admin.settings.index.forms.general_settings_form.sidebarTemplates_description']      = 'Comma separated WHMCS template names, e.g. clientareaproducts,clientareaproductdetails. Empty = every page except the client home page';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays']                   = 'Expires Soon (days)';
$_LANG['admin.settings.index.forms.general_settings_form.expiresSoonDays_description']       = 'A certificate is shown as "Expires Soon" this many days before its due date';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain']              = 'SSL Certificates By Domain';
$_LANG['admin.settings.index.forms.general_settings_form.certificatesByDomain_description']  = 'Client area page listing every domain with its current certificate and the history of the earlier ones';
$_LANG['admin.settings.index.forms.general_settings_form.lifecycle']                         = 'Service Lifecycle';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder']             = 'Cancel Vendor Order On Terminate';
$_LANG['admin.settings.index.forms.general_settings_form.terminateCancelsOrder_description'] = 'Terminating the service in WHMCS also cancels the certificate order / subscription at the vendor';
$_LANG['admin.settings.index.forms.general_settings_form.messages']                          = 'Client Messages';
$_LANG['admin.settings.index.forms.general_settings_form.processingGuide']                   = 'Processing Guide';
$_LANG['admin.settings.index.forms.general_settings_form.processingGuide_description']       = 'Shown to the client while the certificate is being validated (HTML allowed). Can be overridden per product';
$_LANG['admin.settings.index.forms.general_settings_form.issuedMessage']                     = 'Issued SSL Message';
$_LANG['admin.settings.index.forms.general_settings_form.issuedMessage_description']         = 'Shown to the client once the certificate is issued, e.g. installation instructions (HTML allowed). Can be overridden per product';
$_LANG['admin.settings.index.forms.general_settings_form.certificateEmailTemplate']          = 'Certificate Email Template';
$_LANG['admin.settings.index.forms.general_settings_form.certificateEmailTemplate_description'] = 'Template used when the certificate is sent to the client';
$_LANG['admin.settings.index.forms.general_settings_form.agreement']                         = 'Subscriber Agreement';
$_LANG['admin.settings.index.forms.general_settings_form.requireAgreement']                  = 'Require Agreement';
$_LANG['admin.settings.index.forms.general_settings_form.requireAgreement_description']      = 'The client has to accept the certificate authority\'s subscriber agreement before the order is placed';
$_LANG['admin.settings.index.forms.general_settings_form.agreementText']                     = 'Agreement Text';
$_LANG['admin.settings.index.forms.general_settings_form.agreementText_description']         = 'Optional text shown above the checkbox (HTML allowed). The link to the agreement of the product\'s certificate authority is added automatically';
$_LANG['admin.settings.index.forms.general_settings_form.save']                              = 'Save';
$_LANG['admin.settings.index.providers.general_settings_provider.moduleTemplate']            = 'Module template (Send Certificate)';

//product configuration: per-product settings
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.payType']                  = 'Payment Type';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.months']                   = 'Certificate Term';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.commission']               = 'Profit Margin (%)';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.commission_placeholder']   = 'Global margin from Finance Settings';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.processingGuide']          = 'Processing Guide';
$_LANG['admin.products.index.data_tables.products.forms.configuration_form.issuedMessage']            = 'Issued SSL Message';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeRecurring'] = 'Recurring';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeOnetime']   = 'One Time';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.payTypeFree']       = 'Free';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsBillingCycle'] = 'From the billing cycle';
$_LANG['admin.products.index.data_tables.products.providers.configuration_provider.monthsValue']      = ':months months';

//certificate log
$_LANG['admin.menu.certificateLog']                   = 'Certificate Log';
$_LANG['admin.breadcrumbs.CertificateLog_index']      = 'Certificate Log';
$_LANG['admin.certificate_log.service_id']             = 'Service ID';
$_LANG['admin.certificate_log.all_types']              = 'All events';
$_LANG['admin.certificate_log.type_success']           = 'Success';
$_LANG['admin.certificate_log.type_info']              = 'Info';
$_LANG['admin.certificate_log.type_error']             = 'Error';
$_LANG['admin.certificate_log.search']                 = 'Message or domain';
$_LANG['admin.certificate_log.filter']                 = 'Filter';
$_LANG['admin.certificate_log.clear_30']               = 'Clear older than 30 days';
$_LANG['admin.certificate_log.clear_all']              = 'Clear all';
$_LANG['admin.certificate_log.confirm_clear_30']       = 'Delete the events older than 30 days?';
$_LANG['admin.certificate_log.confirm_clear_all']      = 'Delete all certificate events? Clients will no longer see the activity of their certificates.';
$_LANG['admin.certificate_log.cleared']                = ':count events deleted.';
$_LANG['admin.certificate_log.date']                   = 'Date';
$_LANG['admin.certificate_log.service']                = 'Service';
$_LANG['admin.certificate_log.client']                 = 'Client';
$_LANG['admin.certificate_log.type']                   = 'Event';
$_LANG['admin.certificate_log.message']                = 'Message';
$_LANG['admin.certificate_log.empty']                  = 'No events found.';
$_LANG['admin.certificate_log.page']                   = 'Page :page of :pages (:total events)';

//import: map to an existing service
$_LANG['admin.import.index.forms.import_form.serviceId'] = 'Attach To Existing Service ID (optional)';
$_LANG['admin.settings.index.forms.general_settings_form.update_success']                    = 'Settings updated successfully';

//pricing grid
$_LANG['admin.pricing_grid.currency']          = 'Currency';
$_LANG['admin.pricing_grid.margin']            = 'Margin %';
$_LANG['admin.pricing_grid.scope_currency']    = 'this currency';
$_LANG['admin.pricing_grid.scope_all']         = 'all currencies';
$_LANG['admin.pricing_grid.apply_margin']      = 'Apply margin & save all';
$_LANG['admin.pricing_grid.confirm_margin']    = 'Set every enabled price to the reseller cost plus this margin?';
$_LANG['admin.pricing_grid.export']            = 'Export CSV';
$_LANG['admin.pricing_grid.import']            = 'Import CSV';
$_LANG['admin.pricing_grid.no_products']       = 'No products of this module yet - import them on the Products page.';
$_LANG['admin.pricing_grid.grid_help']         = 'Prices are in the client\'s billing currency. An empty price disables that billing cycle. Red fields are below your reseller cost. "Auto" products are repriced by the price update cron.';
$_LANG['admin.pricing_grid.product']           = 'Product';
$_LANG['admin.pricing_grid.auto']              = 'Auto';
$_LANG['admin.pricing_grid.auto_title']        = 'Update the price automatically from the reseller cost and the margin';
$_LANG['admin.pricing_grid.term_12']           = '1 year';
$_LANG['admin.pricing_grid.term_24']           = '2 years';
$_LANG['admin.pricing_grid.term_36']           = '3 years';
$_LANG['admin.pricing_grid.term']              = 'Months';
$_LANG['admin.pricing_grid.not_in_catalogue']  = 'not in the gateway catalogue';
$_LANG['admin.pricing_grid.item_base']         = 'Certificate';
$_LANG['admin.pricing_grid.item_sans']         = 'Additional SAN';
$_LANG['admin.pricing_grid.item_sans_wildcard'] = 'Additional wildcard SAN';
$_LANG['admin.pricing_grid.item']              = 'Item';
$_LANG['admin.pricing_grid.disabled']          = 'disabled';
$_LANG['admin.pricing_grid.no_cost']           = 'no reseller price';
$_LANG['admin.pricing_grid.cost']              = 'cost :cost';
$_LANG['admin.pricing_grid.below_cost']        = 'below cost';
$_LANG['admin.pricing_grid.save_all']          = 'Save all';
$_LANG['admin.pricing_grid.preview_title']     = 'CSV import preview';
$_LANG['admin.pricing_grid.preview_summary']   = ':accepted price changes, :rejected rejected rows, :unchanged unchanged.';
$_LANG['admin.pricing_grid.old_price']         = 'Current';
$_LANG['admin.pricing_grid.new_price']         = 'New';
$_LANG['admin.pricing_grid.reseller_cost']     = 'Reseller cost';
$_LANG['admin.pricing_grid.rejected_title']    = 'Rejected rows';
$_LANG['admin.pricing_grid.line']              = 'Line';
$_LANG['admin.pricing_grid.data']              = 'Row';
$_LANG['admin.pricing_grid.reason']            = 'Reason';
$_LANG['admin.pricing_grid.import_confirm']    = 'Import :count changes';
$_LANG['admin.pricing_grid.import_cancel']     = 'Cancel';
$_LANG['admin.pricing_grid.notice_saved']      = ':count prices saved.';
$_LANG['admin.pricing_grid.notice_margin_applied'] = ':count prices updated from the reseller cost.';
$_LANG['admin.pricing_grid.notice_imported']   = ':count prices imported.';
$_LANG['admin.settings.index.forms.general_settings_form.pricing']                       = 'Pricing';
$_LANG['admin.settings.index.forms.general_settings_form.keepMarginOnSync']              = 'Keep Margin On Sync';
$_LANG['admin.settings.index.forms.general_settings_form.keepMarginOnSync_description']  = 'Products with manual prices ("Auto" off on the Pricing page) follow reseller cost changes, so their margin stays the same';
$_LANG['admin.settings.index.forms.general_settings_form.belowCostEmail']                = 'Below-Cost Email';
$_LANG['admin.settings.index.forms.general_settings_form.belowCostEmail_description']    = 'E-mail the administrators when prices are below the reseller cost (checked by the price update cron, sent once per change)';

//products: lifecycle
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.manage']                  = 'Manage';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_imported']          = 'Imported';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_not_imported']      = 'Not imported';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_hidden']            = 'Hidden';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_retired']           = 'Retired';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_removed']           = 'Removed by the gateway';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.badge_options']           = 'Check options';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_hide']             = 'Hide';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_show']             = 'Show';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_retire']           = 'Retire';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_unretire']         = 'Un-retire';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_fixOptions']       = 'Fix options';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.action_cancelRenewals']   = 'Cancel all renewals';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_retire']          = 'Retire this product? It can no longer be ordered; existing services keep working.';
$_LANG['admin.products.index.data_tables.products.array_data_provider.data_table.confirm_cancel_renewals'] = 'Create a cancellation request (end of billing period) for every active service of this product?';
$_LANG['admin.products.index.products_container.notice_hidden']             = ':product is hidden from the order form.';
$_LANG['admin.products.index.products_container.notice_shown']              = ':product is visible on the order form.';
$_LANG['admin.products.index.products_container.notice_retired']            = ':product is retired.';
$_LANG['admin.products.index.products_container.notice_unretired']          = ':product is active again (still hidden - use Show to offer it).';
$_LANG['admin.products.index.products_container.notice_options_fixed']      = 'Configurable options of :product checked. Changes: :detail';
$_LANG['admin.products.index.products_container.notice_renewals_cancelled'] = 'Cancellation requests created for :detail services of :product.';

//commissions
$_LANG['admin.menu.commissions']                = 'Commissions';
$_LANG['admin.breadcrumbs.Commissions_index']   = 'Commissions';
$_LANG['admin.commissions.intro']               = 'Raise or lower the price of SSL products for a client: +10 adds 10 % to the product price, -5 is a 5 % discount. The most specific rule wins: client + product, then client + all products, then all clients + product. Applies to new orders in the cart, on the standard and the Lagom order form.';
$_LANG['admin.commissions.client']              = 'Client';
$_LANG['admin.commissions.product']             = 'Product';
$_LANG['admin.commissions.commission']          = 'Commission %';
$_LANG['admin.commissions.all_clients']         = 'All clients';
$_LANG['admin.commissions.all_products']        = 'All SSL products';
$_LANG['admin.commissions.save']                = 'Add / change';
$_LANG['admin.commissions.updated']             = 'Updated';
$_LANG['admin.commissions.delete']              = 'Delete';
$_LANG['admin.commissions.confirm_delete']      = 'Delete this commission rule?';
$_LANG['admin.commissions.empty']               = 'No commission rules.';
$_LANG['admin.commissions.notice_saved']        = 'Commission rule saved.';
$_LANG['admin.commissions.notice_deleted']      = 'Commission rule deleted.';

//emails
$_LANG['admin.menu.emails']                              = 'Emails';
$_LANG['admin.breadcrumbs.Emails_index']                 = 'Emails';
$_LANG['admin.emails.intro']                             = 'The e-mails this module sends to your clients. They are stored as WHMCS e-mail templates, so sending, the e-mail history and your WHMCS hooks work as usual; here you only see and edit the SSL ones, per language.';
$_LANG['admin.emails.about_title']                       = 'What you will find here';
$_LANG['admin.emails.about_edit']                        = 'Pick an e-mail in the list, edit its subject and message per language, and save. Preview shows what a client receives.';
$_LANG['admin.emails.about_fields']                      = 'Merge fields are replaced with client and order data when the e-mail is sent. Click one to insert it.';
$_LANG['admin.emails.no_permission']                    = 'Your administrator role needs the WHMCS permission ":permission" to open this page.';
$_LANG['admin.emails.group_order']                       = 'Order';
$_LANG['admin.emails.group_validation']                  = 'Validation';
$_LANG['admin.emails.group_issued']                      = 'Issued certificate';
$_LANG['admin.emails.group_renewals']                    = 'Renewals and reminders';
$_LANG['admin.emails.group_problems']                    = 'Problems';
$_LANG['admin.emails.badge_customised']                  = 'edited';
$_LANG['admin.emails.badge_disabled']                    = 'disabled';
$_LANG['admin.emails.badge_unused']                      = 'not sent';
$_LANG['admin.emails.badge_languages']                   = 'lang.';
$_LANG['admin.emails.whmcs_name']                        = 'WHMCS template:';
$_LANG['admin.emails.language']                          = 'Language';
$_LANG['admin.emails.language_default']                  = 'Default (every language without its own text)';
$_LANG['admin.emails.language_default_hint']             = 'Clients whose language has no text of its own receive this one.';
$_LANG['admin.emails.language_new']                      = 'There is no :language text yet - the default text is shown. Saving creates the :language version; clients with this language will get it.';
$_LANG['admin.emails.subject']                           = 'Subject';
$_LANG['admin.emails.message']                           = 'Text (HTML, Smarty merge fields)';
$_LANG['admin.emails.fields']                            = 'Merge fields (click to insert):';
$_LANG['admin.emails.save']                              = 'Save';
$_LANG['admin.emails.preview']                           = 'Preview';
$_LANG['admin.emails.test']                              = 'Send test to administrators';
$_LANG['admin.emails.confirm_test']                      = 'Send this text, filled with sample data, to the administrators who receive system e-mails? Clients receive nothing.';
$_LANG['admin.emails.reset']                             = 'Reset to default';
$_LANG['admin.emails.confirm_reset']                     = 'Replace the default text with the module\'s original text? Language versions stay as they are.';
$_LANG['admin.emails.enable']                            = 'Enable';
$_LANG['admin.emails.disable']                           = 'Disable';
$_LANG['admin.emails.remove_language']                   = 'Remove the :language version';
$_LANG['admin.emails.confirm_remove_language']           = 'Remove this language version? Clients with this language will receive the default text.';
$_LANG['admin.emails.preview_title']                     = 'Preview';
$_LANG['admin.emails.preview_hint']                      = 'Rendered by WHMCS with the data of the newest SSL service (client, product); the other fields are samples.';
$_LANG['admin.emails.notice_saved']                      = 'The e-mail was saved.';
$_LANG['admin.emails.notice_test_sent']                  = 'The test e-mail was sent to the administrators.';
$_LANG['admin.emails.notice_reset']                      = 'The default text was reset.';
$_LANG['admin.emails.notice_language_removed']           = 'The language version was removed.';
$_LANG['admin.emails.notice_disabled']                   = 'The e-mail is disabled: WHMCS will not send it.';
$_LANG['admin.emails.notice_enabled']                    = 'The e-mail is enabled.';
$_LANG['admin.emails.name_configuration_required']       = 'Configuration required';
$_LANG['admin.emails.when_configuration_required']       = 'Sent when a certificate is ordered and the client has to configure it (CSR, domains, validation).';
$_LANG['admin.emails.name_acme_subscription_configuration'] = 'ACME subscription - configuration required';
$_LANG['admin.emails.when_acme_subscription_configuration'] = 'Sent when an ACME subscription is ordered and has to be configured.';
$_LANG['admin.emails.name_dns_validation_switched_to_email'] = 'DNS validation switched to email';
$_LANG['admin.emails.when_dns_validation_switched_to_email'] = 'Sent when DNS validation did not complete and AutoInstall switched the order to email validation.';
$_LANG['admin.emails.name_validation_warning']           = 'Validation problem (file method)';
$_LANG['admin.emails.when_validation_warning']           = 'Sent when the CA could not verify the validation file after several checks.';
$_LANG['admin.emails.name_send_certificate']             = 'Send certificate';
$_LANG['admin.emails.when_send_certificate']             = 'Sent when the certificate is issued and when the client or an administrator sends it again; the certificate and the CA bundle are attached.';
$_LANG['admin.emails.name_certificate_installed']        = 'Certificate installed (AutoInstall)';
$_LANG['admin.emails.when_certificate_installed']        = 'Sent after the certificate was installed on the client\'s cPanel account.';
$_LANG['admin.emails.name_service_expiration']           = 'Service expiration';
$_LANG['admin.emails.when_service_expiration']           = 'Sent by the renewal cron on the days listed in SSL Settings (expiration notifications) before the service expires.';
$_LANG['admin.emails.name_reissue']                      = 'Reissue reminder';
$_LANG['admin.emails.when_reissue']                      = 'Sent 30 days before the certificate ends, asking the client to reissue it (not for AutoInstall services that reissue automatically).';
$_LANG['admin.emails.name_renewal_started']              = 'Renewal started (AutoInstall)';
$_LANG['admin.emails.when_renewal_started']              = 'Sent when the automatic renewal of an AutoInstall certificate was started.';
$_LANG['admin.emails.name_renewal_complete']             = 'Renewal complete (AutoInstall)';
$_LANG['admin.emails.when_renewal_complete']             = 'Sent after a renewed certificate was installed automatically.';
$_LANG['admin.emails.name_certificate_autorenewed']      = 'Certificate autorenewed';
$_LANG['admin.emails.when_certificate_autorenewed']      = 'Sent when the renewal order of a certificate was placed with the CA.';
$_LANG['admin.emails.name_acme_subscription_expiration'] = 'ACME subscription - renewal invoice';
$_LANG['admin.emails.when_acme_subscription_expiration'] = 'Sent when the renewal invoice of an ACME subscription was created (if enabled in SSL Settings).';
$_LANG['admin.emails.name_acme_subscription_renewal']    = 'ACME subscription - renewal paid';
$_LANG['admin.emails.when_acme_subscription_renewal']    = 'Sent when the renewal invoice of an ACME subscription was paid.';
$_LANG['admin.emails.name_renewal']                      = 'Renewal (not used)';
$_LANG['admin.emails.when_renewal']                      = 'Defined for compatibility with the GoGetSSL module; this version does not send it.';
$_LANG['admin.emails.name_auto_reissue_failed']          = 'Automatic reissue failed';
$_LANG['admin.emails.when_auto_reissue_failed']          = 'Sent when the automatic reissue before expiry failed after all attempts.';

$_LANG['admin.breadcrumbs.Emails_index']        = 'Emails';
$_LANG['admin.breadcrumbs.Emails']              = 'Emails';
$_LANG['admin.emails.language_tab_default']     = 'Default';
$_LANG['admin.emails.add_language']             = 'Add language';
$_LANG['admin.emails.fields_own']               = 'Fields of this e-mail (click to insert)';
$_LANG['admin.emails.fields_standard']          = 'Fields of every e-mail';
$_LANG['admin.emails.opt_from']                 = 'From';
$_LANG['admin.emails.opt_from_name']            = 'Name (empty: company name)';
$_LANG['admin.emails.opt_from_email']           = 'E-mail address (empty: the WHMCS default)';
$_LANG['admin.emails.opt_copy']                 = 'Copy to';
$_LANG['admin.emails.opt_blind']                = 'Blind copy to';
$_LANG['admin.emails.opt_list_hint']            = 'E-mail addresses separated by a comma';
$_LANG['admin.emails.opt_plain']                = 'Plain text';
$_LANG['admin.emails.opt_plain_hint']           = 'Send this e-mail in plain text only';
$_LANG['admin.emails.opt_disable']              = 'Disable';
$_LANG['admin.emails.opt_disable_hint']         = 'Do not send this e-mail';
$_LANG['admin.emails.rich_editor']              = 'Rich-text editor';
$_LANG['admin.emails.rich_failed']              = 'The visual editor could not be loaded (assets/js/tinymce of WHMCS); the text is edited as HTML source.';
$_LANG['admin.emails.name_renewal_invoice_created']  = 'Renewal invoice created';
$_LANG['admin.emails.when_renewal_invoice_created']  = 'Sent when this module creates a renewal invoice. Disabled by default: while it is disabled WHMCS sends its own invoice e-mail; enable it to send this text instead.';
$_LANG['admin.settings.index.forms.general_settings_form.terminateUnpaidOnExpiry']             = 'Terminate Unpaid Services On Expiry';
$_LANG['admin.settings.index.forms.general_settings_form.terminateUnpaidOnExpiry_description'] = 'A service whose expiry date has passed without a payment is terminated, its order / subscription is cancelled at the vendor and its unpaid renewal invoice is cancelled. If the vendor can not be reached the service stays open and the next cron run tries again. Applies to subscription (non one-time) services.';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetRegular']                  = 'Regular Certificates: Due Date Days Before Expiry';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetRegular_description']      = 'A single (non-subscription) certificate falls due this many days before its expiry (0 = on the expiry date). Applied the next time the order is synchronised.';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetSubscription']             = 'Subscriptions: Due Date Days Before End';
$_LANG['admin.settings.index.forms.general_settings_form.dueDateOffsetSubscription_description'] = 'A multi-year subscription falls due this many days before the subscription ends (0 = on the end date). Applied the next time the order is synchronised.';
$_LANG['admin.settings.index.forms.general_settings_form.defaultWebServer']             = 'Default Web Server Type';
$_LANG['admin.settings.index.forms.general_settings_form.defaultWebServer_description'] = 'Sent with every order as webserver_type so clients are never asked. Leave empty when your gateway does not need it.';
$_LANG['admin.emails.name_product_discontinued']     = 'Product discontinued';
$_LANG['admin.emails.when_product_discontinued']     = 'Sent to the client with the renewal invoice when the gateway no longer sells the service\'s product (retired or removed), so renewal under the same product may be refused.';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools']             = 'SSL Tools For Clients';
$_LANG['admin.settings.index.forms.general_settings_form.sslTools_description'] = 'Client area page with a CSR / certificate decoder, key match, format converter, CSR generator and SSL checker. They run on this server and call no API.';
$_LANG['admin.settings.index.forms.general_settings_form.downloads']                           = 'Certificate Downloads';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_zip']                  = 'All formats (ZIP)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_crt']                  = 'Certificate (.crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_ca']                   = 'CA bundle (.ca-bundle)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_fullchain']            = 'Full chain (.crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_nginx']                = 'Nginx (chained .crt)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_apache']               = 'Apache (.crt + .ca-bundle, ZIP)';
$_LANG['admin.settings.index.forms.general_settings_form.downloadFormat_pkcs7']                = 'IIS / Tomcat (PKCS#7 .p7b)';
$_LANG['admin.settings.index.forms.ssl_settings_form.ordering']                = 'Ordering And Agreement';
$_LANG['admin.settings.index.forms.ssl_settings_form.delivery']                = 'Delivery And Messages';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDates']                = 'Due Dates';
$_LANG['admin.settings.index.forms.ssl_settings_form.autoInstall']             = 'AutoInstall (cPanel)';
$_LANG['admin.settings.index.providers.ssl_settings_provider.moduleTemplate']  = 'Module template (Send Certificate)';
$_LANG['admin.settings.index.widgets.automated_settings_widget.title']         = 'Automated SSL (AutoInstall, ACME and plan subscriptions)';
$_LANG['admin.settings.index.widgets.sidebar.automatedSettings']               = 'Automated SSL';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide']                   = 'Processing Guide';
$_LANG['admin.settings.index.forms.ssl_settings_form.processingGuide_description']       = 'Shown to the client while the certificate is being validated (HTML allowed). Can be overridden per product';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage']                     = 'Issued SSL Message';
$_LANG['admin.settings.index.forms.ssl_settings_form.issuedMessage_description']         = 'Shown to the client once the certificate is issued, e.g. installation instructions (HTML allowed). Can be overridden per product';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate']          = 'Certificate Email Template';
$_LANG['admin.settings.index.forms.ssl_settings_form.certificateEmailTemplate_description'] = 'Template used when the certificate is sent to the client';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement']                  = 'Require Agreement';
$_LANG['admin.settings.index.forms.ssl_settings_form.requireAgreement_description']      = 'The client has to accept the certificate authority\'s subscriber agreement before the order is placed';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText']                     = 'Agreement Text';
$_LANG['admin.settings.index.forms.ssl_settings_form.agreementText_description']         = 'Optional text shown above the checkbox (HTML allowed). The link to the agreement of the product\'s certificate authority is added automatically';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetRegular']                  = 'Regular Certificates: Due Date Days Before Expiry';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetRegular_description']      = 'A single (non-subscription) certificate falls due this many days before its expiry (0 = on the expiry date). Applied the next time the order is synchronised.';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetSubscription']             = 'Subscriptions: Due Date Days Before End';
$_LANG['admin.settings.index.forms.ssl_settings_form.dueDateOffsetSubscription_description'] = 'A multi-year subscription falls due this many days before the subscription ends (0 = on the end date). Applied the next time the order is synchronised.';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultWebServer']             = 'Default Web Server Type';
$_LANG['admin.settings.index.forms.ssl_settings_form.defaultWebServer_description'] = 'Sent with every order as webserver_type so clients are never asked. Leave empty when your gateway does not need it.';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_zip']                  = 'All formats (ZIP)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_crt']                  = 'Certificate (.crt)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_ca']                   = 'CA bundle (.ca-bundle)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_fullchain']            = 'Full chain (.crt)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_nginx']                = 'Nginx (chained .crt)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_apache']               = 'Apache (.crt + .ca-bundle, ZIP)';
$_LANG['admin.settings.index.forms.ssl_settings_form.downloadFormat_pkcs7']                = 'IIS / Tomcat (PKCS#7 .p7b)';
$_LANG['admin.home.index.widgets.documentation_widget.title']        = 'Module documentation';
$_LANG['admin.home.index.widgets.documentation_widget.info']         = 'Installation guides, configuration reference and troubleshooting in one place.';
$_LANG['admin.home.index.widgets.documentation_widget.open_thesslstore'] = 'TheSSLStore documentation';
$_LANG['admin.home.index.widgets.documentation_widget.open_gogetssl']    = 'GoGetSSL documentation';
$_LANG['admin.home.index.widgets.next_steps_widget.title']           = 'Next steps after module installation';
$_LANG['admin.home.index.widgets.next_steps_widget.chip']            = 'Finish setup';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products']        = 'Setup products';
$_LANG['admin.home.index.widgets.next_steps_widget.step_products_detail'] = 'Create products and add a short description';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing']         = 'Set pricing';
$_LANG['admin.home.index.widgets.next_steps_widget.step_pricing_detail']  = 'Choose your selling prices for certificates';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals']        = 'Check renewal settings';
$_LANG['admin.home.index.widgets.next_steps_widget.step_renewals_detail'] = 'Review how certificate renewals are handled';

$_LANG['admin.home.index.widgets.system_check_widget.name_ProductPricingUpdate'] = 'Prices and catalogue update';
$_LANG['admin.home.index.widgets.system_check_widget.name_ReSyncProducts'] = 'Catalogue refresh';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificates'] = 'Issued orders sync';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLCertificatesProcessing'] = 'Pending orders check';
$_LANG['admin.home.index.widgets.system_check_widget.name_RenewalNotifyCertificate'] = 'Renewals, reminders and expiry';
$_LANG['admin.home.index.widgets.system_check_widget.name_SSLAutoInstall'] = 'AutoInstall queue (cPanel)';
$_LANG['admin.home.index.widgets.system_check_widget.name_cronScript'] = 'Module scheduler';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron1_description'] = 'Recommended: every day.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron2_description'] = 'Recommended: every day.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron3_description'] = 'Recommended: every day.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron4_description'] = 'Refreshes the product configuration (configurable options, SAN limits), the announcements shown on the dashboard and the details of your account manager. Recommended: once a day.';
$_LANG['admin.settings.index.forms.cron_configuration_form.cron5_description'] = 'Recommended: every 5 minutes.';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineTitle'] = '<b>One cron line for the whole module</b>';
$_LANG['admin.settings.index.widgets.cron_settings_widget.oneLineInfo'] = 'Schedule this single line (every 5 minutes). It starts every job of the module; each job runs only when it is due, as set below, so nothing else has to be scheduled. If you do not add it, the module starts its jobs from the WHMCS cron (where the server allows it).';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobsTitle'] = 'What the jobs do';
$_LANG['admin.settings.index.widgets.cron_settings_widget.advanced'] = 'To run one job on its own (for a test, or to schedule it separately) use';
$_LANG['admin.settings.index.widgets.cron_settings_widget.jobWord'] = 'job';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_ProductPricingUpdate'] = 'Prices and catalogue update';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificatesProcessing'] = 'Pending orders check';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLCertificates'] = 'Issued orders sync';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_RenewalNotifyCertificate'] = 'Renewals, reminders and expiry';
$_LANG['admin.settings.index.widgets.cron_settings_widget.name_SSLAutoInstall'] = 'AutoInstall queue (cPanel)';
$_LANG['admin.settings.index.widgets.cron_settings_widget.lastRun'] = 'Last run:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.neverRun'] = 'never';
$_LANG['admin.settings.index.widgets.cron_settings_widget.command'] = 'Run it now (for a test):';
$_LANG['admin.settings.index.widgets.cron_settings_widget.frequency'] = 'How often it runs:';
$_LANG['admin.settings.index.widgets.cron_settings_widget.everyStart'] = 'every time the module cron line above starts (every 5 minutes).';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_ProductPricingUpdate'] = 'Prices and catalogue update';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_ProductPricingUpdate'] = 'Reads the reseller catalogue and prices from the gateway, refreshes the product configuration, announcements and your account manager, and updates the selling price of every product that has automatic pricing on (cost + your margin). Recommended: once a day.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificatesProcessing'] = 'Pending orders check';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificatesProcessing'] = 'Follows the orders that were submitted and wait for the certificate authority. When a certificate is issued it is stored, the due date is set and the certificate e-mail is sent. Issued orders are not checked here again. Recommended: every 5 minutes, so clients get their certificate quickly.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLCertificates'] = 'Issued orders sync';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLCertificates'] = 'Asks the gateway which orders changed since the last run (renewed, expired, cancelled, revalidated) and updates those services; it also re-checks up to 50 orders that were not updated for a day. Once a day is enough: an issued certificate does not change often.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_RenewalNotifyCertificate'] = 'Renewals, reminders and expiry';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_RenewalNotifyCertificate'] = 'Once a day: expiry and reissue reminder e-mails, automatic renewal invoices, ACME / AutoInstall subscription invoices, and the termination (with vendor cancellation) of services that were not paid by their expiry date. Do not disable it if you bill renewals automatically.';
$_LANG['admin.settings.index.forms.cron_configuration_form.name_SSLAutoInstall'] = 'AutoInstall queue (cPanel)';
$_LANG['admin.settings.index.forms.cron_configuration_form.about_SSLAutoInstall'] = 'Works through the AutoInstall queue: domain validation, installation on cPanel, renewals and automatic reissue. It runs whenever the module cron runs (every 5 minutes).';
$_LANG['admin.settings.index.forms.cron_configuration_form.lastRun'] = 'Last run:';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRun'] = 'never';
$_LANG['admin.settings.index.forms.cron_configuration_form.runNow'] = 'Run it now (for a test):';
$_LANG['admin.settings.index.forms.cron_configuration_form.frequency'] = 'How often it runs:';
$_LANG['admin.settings.index.forms.cron_configuration_form.everyStart'] = 'every time the module cron line above starts (every 5 minutes).';$_LANG['admin.settings.index.forms.cron_configuration_form.healthy'] = 'Healthy';
$_LANG['admin.settings.index.forms.cron_configuration_form.failing'] = 'Failing';
$_LANG['admin.settings.index.forms.cron_configuration_form.neverRunBadge'] = 'Not run yet';
$_LANG['admin.settings.index.forms.cron_configuration_form.seeLog'] = 'details in the module log';
$_LANG['admin.settings.index.forms.cron_configuration_form.howOften'] = 'How often it runs';
$_LANG['admin.settings.index.widgets.cron_settings_widget.subtitle'] = 'Scheduled tasks that keep prices, orders and certificates up to date';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copy'] = 'Copy';
$_LANG['admin.settings.index.widgets.cron_settings_widget.copied'] = 'Copied';
$_LANG['admin.settings.index.forms.cron_configuration_form.saveChanges'] = 'Save changes';
$_LANG['admin.settings.index.forms.cron_configuration_form.noUnsaved'] = 'No unsaved changes';
$_LANG['admin.settings.index.forms.cron_configuration_form.unsaved'] = 'Unsaved changes';
$_LANG['admin.breadcrumbs.CertificateLog'] = 'Certificate Log';
$_LANG['admin.breadcrumbs.Commissions'] = 'Commissions';
$_LANG['admin.breadcrumbs.Migration'] = 'Migration';
$_LANG['admin.home.index.widgets.system_check_widget.never'] = 'Never run';
$_LANG['admin.home.index.widgets.system_check_widget.failing'] = 'Failing';
$_LANG['admin.home.index.widgets.system_check_widget.review'] = 'Cron settings';
$_LANG['admin.home.index.widgets.gateway_widget.title'] = 'API Gateway connection';
$_LANG['admin.home.index.widgets.gateway_widget.connected'] = 'Connected';
$_LANG['admin.home.index.widgets.gateway_widget.unreachable'] = 'Unreachable';
$_LANG['admin.home.index.widgets.gateway_widget.response'] = 'Response time';
$_LANG['admin.home.index.widgets.gateway_widget.none'] = 'No gateway configured';
$_LANG['admin.home.index.widgets.gateway_widget.credentials'] = 'API credentials';
$_LANG['admin.home.index.widgets.gateway_widget.credentials_detail'] = 'Stored encrypted';
$_LANG['admin.home.index.widgets.gateway_widget.manage'] = 'Manage';
$_LANG['admin.home.index.widgets.gateway_widget.recheck'] = 'Check the connection again';
$_LANG['admin.home.index.widgets.attention_widget.title'] = 'Needs your attention';
$_LANG['admin.home.index.widgets.attention_widget.count'] = ':n items';
$_LANG['admin.home.index.widgets.attention_widget.count_one'] = '1 item';
$_LANG['admin.home.index.widgets.attention_widget.none'] = 'Nothing needs your attention';
$_LANG['admin.home.index.widgets.attention_widget.none_detail'] = 'All scheduled tasks ran without errors and the gateway is reachable.';
$_LANG['admin.home.index.widgets.attention_widget.never_title'] = ':task has never run';
$_LANG['admin.home.index.widgets.attention_widget.never_text'] = 'Add the module cron line from CRON Settings, or check that the WHMCS cron runs.';
$_LANG['admin.home.index.widgets.attention_widget.cron'] = 'Cron settings';
$_LANG['admin.home.index.widgets.attention_widget.failing_title'] = ':task is failing';
$_LANG['admin.home.index.widgets.attention_widget.log'] = 'Open log';
$_LANG['admin.home.index.widgets.attention_widget.gateway_title'] = ':gateway cannot be reached';
$_LANG['admin.home.index.widgets.attention_widget.api'] = 'API settings';
$_LANG['admin.home.index.widgets.attention_widget.configure_title'] = ':n orders are waiting for the client';
$_LANG['admin.home.index.widgets.attention_widget.configure_title_one'] = '1 order is waiting for the client';
$_LANG['admin.home.index.widgets.attention_widget.configure_text'] = 'The client has not sent a CSR for these orders yet.';
$_LANG['admin.home.index.widgets.attention_widget.orders'] = 'View orders';
$_LANG['admin.home.index.widgets.attention_widget.name_ProductPricingUpdate'] = 'Prices and catalogue update';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificatesProcessing'] = 'Pending orders check';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLCertificates'] = 'Issued orders sync';
$_LANG['admin.home.index.widgets.attention_widget.name_RenewalNotifyCertificate'] = 'Renewals, reminders and expiry';
$_LANG['admin.home.index.widgets.attention_widget.name_SSLAutoInstall'] = 'AutoInstall queue (cPanel)';
$_LANG['admin.home.index.widgets.pipeline_widget.title'] = 'Certificate pipeline';
$_LANG['admin.home.index.widgets.pipeline_widget.open'] = 'Open certificate list';
$_LANG['admin.home.index.widgets.pipeline_widget.configure'] = 'Awaiting configuration';
$_LANG['admin.home.index.widgets.pipeline_widget.configure_detail'] = 'The client has not sent a CSR';
$_LANG['admin.home.index.widgets.pipeline_widget.validation'] = 'Validation';
$_LANG['admin.home.index.widgets.pipeline_widget.validation_detail'] = 'With the certificate authority';
$_LANG['admin.home.index.widgets.pipeline_widget.issued'] = 'Issued';
$_LANG['admin.home.index.widgets.pipeline_widget.issued_detail'] = 'Ready or installed';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring'] = 'Expiring in 90 days';
$_LANG['admin.home.index.widgets.pipeline_widget.expiring_detail'] = 'Renewal due soon';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid'] = ':n waiting for payment';
$_LANG['admin.home.index.widgets.pipeline_widget.unpaid_detail'] = 'Orders not paid yet';
$_LANG['admin.home.index.widgets.pipeline_widget.expired'] = ':n expired';
$_LANG['admin.home.index.widgets.pipeline_widget.expired_detail'] = 'Past their end date';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals'] = 'Renewal report';
$_LANG['admin.home.index.widgets.pipeline_widget.renewals_detail'] = 'Upcoming renewals';
$_LANG['admin.home.index.widgets.overview_widget.title'] = 'Sales and margin';
$_LANG['admin.home.index.widgets.overview_widget.invoiced'] = 'Invoiced';
$_LANG['admin.home.index.widgets.overview_widget.cost'] = 'Reseller cost';
$_LANG['admin.home.index.widgets.overview_widget.profit'] = 'Gross profit';
$_LANG['admin.home.index.widgets.overview_widget.margin'] = 'Margin on cost';
$_LANG['admin.home.index.widgets.overview_widget.split'] = 'Last month: paid :paid, unpaid :unpaid, overdue :overdue.';
$_LANG['admin.home.index.widgets.overview_widget.low'] = 'The margin is below :margin%: raise it on the Pricing page.';
$_LANG['admin.home.index.widgets.overview_widget.pricing'] = 'Adjust prices and margin';
$_LANG['admin.home.index.widgets.announcements_widget.latest'] = 'From the certificate authorities';
$_LANG['admin.home.index.widgets.migration_widget.run_short'] = 'Run #:id :status';
$_LANG['admin.home.index.widgets.support_widget.title'] = 'Help';
$_LANG['admin.home.index.widgets.support_widget.phone'] = 'Support line';
$_LANG['admin.home.index.widgets.support_widget.email'] = 'E-mail support';
$_LANG['admin.home.index.widgets.support_widget.write'] = 'Write';
$_LANG['admin.home.index.widgets.support_widget.docs'] = 'Documentation';
$_LANG['admin.home.index.widgets.support_widget.docs_detail'] = 'Install, configure, troubleshoot';
$_LANG['admin.home.index.widgets.support_widget.open'] = 'Open';
$_LANG['admin.home.index.widgets.support_widget.manager'] = 'Your partner growth manager';
$_LANG['admin.home.index.widgets.support_widget.report'] = 'Module logs for support';
$_LANG['admin.home.index.widgets.support_widget.report_detail'] = 'Send the activity report to support';
$_LANG['admin.home.index.widgets.support_widget.report_button'] = 'Send report';
$_LANG['admin.home.index.widgets.next_steps_widget.lead'] = 'Finish setting up';
$_LANG['admin.home.index.widgets.system_check_widget.yesterday'] = 'Yesterday :time';
$_LANG['admin.home.index.widgets.migration_widget.removed_on'] = 'This panel is removed on :date, :days days after the migration.';
$_LANG['admin.home.index.widgets.support_widget.phone_detail'] = 'Telephone support';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured'] = 'Not configured';
$_LANG['admin.home.index.widgets.gateway_widget.not_configured_detail'] = 'Enter the API credentials';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_title'] = ':gateway API credentials are missing';
$_LANG['admin.home.index.widgets.attention_widget.gateway_missing_text'] = 'Without them the module cannot order or check certificates. Enter them in API Settings.';
$_LANG['admin.home.index.widgets.automated_ssl_banner.label'] = 'Automated SSL promotion';
$_LANG['admin.home.index.widgets.automated_ssl_banner.eyebrow'] = 'New · Automated SSL · ACME & AIS';
$_LANG['admin.home.index.widgets.automated_ssl_banner.title'] = 'Start offering Automated SSL to your clients';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_1'] = 'Certificates issue themselves in minutes';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_2'] = 'Renewals happen before expiry — every time';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_3'] = 'No more manual CSRs or validation files';
$_LANG['admin.home.index.widgets.automated_ssl_banner.message_4'] = 'Zero downtime from expired certificates';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_issue'] = 'Auto-Issue';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_renew'] = 'Auto-Renew';
$_LANG['admin.home.index.widgets.automated_ssl_banner.chip_install'] = 'Auto-Install';
$_LANG['admin.home.index.widgets.automated_ssl_banner.cta'] = 'Enable Automated SSL';
$_LANG['admin.home.index.widgets.automated_ssl_banner.note'] = 'Powered by the ACME and AIS protocols — set it up once, it runs forever.';
$_LANG['admin.certificate_log.about_title'] = 'What you will find here';
$_LANG['admin.certificate_log.about_intro'] = 'What the module did with each certificate order: ordering, validation, issuing, cPanel installation, reissue, renewal, extra SANs and admin changes.';
$_LANG['admin.certificate_log.about_service'] = 'Filter by service ID, or search a domain or any word of the message.';
$_LANG['admin.certificate_log.about_steps'] = 'The tag at the start of a message is the step: [Order], [Validate], [Install], [Reissue], [AutoReissue], [Renew].';
$_LANG['admin.certificate_log.about_types'] = 'Success: step done. Info: progress. Error: step failed, some are retried automatically. Entries stay until you clear them.';
$_LANG['admin.certificate_log.about_other'] = 'Gateway (API) errors and cron failures are not recorded here. See the :logs page.';
$_LANG['admin.certificate_log.logs_page'] = 'Logs';
$_LANG['admin.settings.index.widgets.sidebar.logsSettings'] = 'Logs';
$_LANG['admin.settings.index.widgets.logs_settings_widget.title'] = 'Logs';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLogs'] = 'Module logs';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLogs_about'] = 'Kept in the module and shown in its own pages. Switching one off stops new entries; the entries already stored stay until you clear them.';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog'] = 'Certificate Log';
$_LANG['admin.settings.index.forms.logs_settings_form.certificateLog_description'] = 'The history of each certificate order (order, validation, issue, installation, reissue, renewal, extra SANs, administrator changes), on the Certificate Log page';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog'] = 'Module Log';
$_LANG['admin.settings.index.forms.logs_settings_form.internalLog_description'] = 'Technical messages of the module (gateway and cron errors, notices), on the Logs page. With it off, failures are harder to trace';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsLogs'] = 'WHMCS logs';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsLogs_about'] = 'Written into WHMCS itself and read there, not in this module.';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog'] = 'WHMCS Activity Log';
$_LANG['admin.settings.index.forms.logs_settings_form.whmcsActivityLog_description'] = 'Write certificate events (order placed, reissue, validation, installation, cancellation) to the WHMCS activity log';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog'] = 'WHMCS Module Log';
$_LANG['admin.settings.index.forms.logs_settings_form.moduleLog_description'] = 'Write the gateway and cPanel requests and responses to the WHMCS module log (Utilities > Logs > Module Log). It also has to be enabled in WHMCS. Passwords, keys and private keys are never written';
$_LANG['admin.settings.index.forms.logs_settings_form.save'] = 'Save';
$_LANG['admin.settings.index.forms.logs_settings_form.update_success'] = 'Settings updated successfully';
