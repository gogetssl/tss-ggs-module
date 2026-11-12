<?php

$_LANG['admin.menu.logs']        = 'Logs';
$_LANG['admin.breadcrumbs.Logs'] = 'Logs';

//TYPES
$_LANG['packages.logs.support.translations.logs_type_translator.alert']     = 'Alert';
$_LANG['packages.logs.support.translations.logs_type_translator.critical']  = 'Critical';
$_LANG['packages.logs.support.translations.logs_type_translator.debug']     = 'Debug';
$_LANG['packages.logs.support.translations.logs_type_translator.emergency'] = 'Emergency';
$_LANG['packages.logs.support.translations.logs_type_translator.error']     = 'Error';
$_LANG['packages.logs.support.translations.logs_type_translator.info']      = 'Info';
$_LANG['packages.logs.support.translations.logs_type_translator.notice']    = 'Notice';
$_LANG['packages.logs.support.translations.logs_type_translator.warning']   = 'Warning';

//Delete Log popup
$_LANG['packages.logs.modals.delete_log_modal.title']                  = 'Delete Log';
$_LANG['packages.logs.modals.delete_log_modal.description']            = 'Are you sure that you want to delete this log?';
$_LANG['packages.logs.forms.delete_log_form.delete_success']           = 'The selected log has been deleted successfully';
$_LANG['packages.logs.forms.mass_delete_form.delete_success']          = 'The selected logs have been deleted successfully';
$_LANG['packages.logs.forms.delete_log_form.deletingLogsIsNotAllowed'] = "Deleting logs is not permitted";

//Log table
$_LANG['packages.logs.pages.logs_data_table.id']           = 'ID';
$_LANG['packages.logs.pages.logs_data_table.type']         = 'Type';
$_LANG['packages.logs.pages.logs_data_table.date']         = 'Date';
$_LANG['packages.logs.pages.logs_data_table.message']      = 'Message';
$_LANG['packages.logs.pages.logs_data_table.related_item'] = 'Related Item';

//Logs table buttons
$_LANG['packages.logs.buttons.show_data_button.title'] = 'Show Details';

//Details log modal
$_LANG['packages.logs.modals.show_data_modal.title'] = 'Details';
$_LANG['packages.logs.forms.show_data_form.data']    = 'Log Insights';

//Edit Modal
$_LANG['components.dropdown_menu.dropdown_menu.more_actions']                           = 'Actions';
$_LANG['packages.logs.buttons.edit_configuration.title']                                = 'Settings';
$_LANG['packages.logs.modals.edit_configuration_modal.title']                           = 'Edit Settings';
$_LANG['packages.logs.forms.edit_configuration_form.types']                             = 'Log Types';
$_LANG['packages.logs.forms.edit_configuration_form.types_description']                 = 'Choose the types of logs to include. If no types are selected, all log types will be recorded by default.';
$_LANG['packages.logs.forms.edit_configuration_form.auto_prune']                        = 'Clear Automatically';
$_LANG['packages.logs.forms.edit_configuration_form.auto_prune_description']            = 'Enable to clear logs automatically after the defined number of days.';
$_LANG['packages.logs.forms.edit_configuration_form.auto_prune_older_than']             = 'Delete Logs Older Than';
$_LANG['packages.logs.forms.edit_configuration_form.auto_prune_older_than_description'] = 'Enter the number of days after which the logs will be deleted.';
$_LANG['packages.logs.forms.edit_configuration_form.update_success']                    = 'Settings have been saved successfully';
$_LANG['packages.logs.forms.edit_configuration_form.formValidationError']               = 'Please enter correct values';

$_LANG['packages.logs.providers.configuration_provider.attributes.auto_prune'] = 'Clear Automatically';
$_LANG['packages.logs.providers.configuration_provider.values.auto_prune.1']   = 'enabled';


//Clean log
$_LANG['packages.logs.buttons.clean_log_button.title']        = 'Clean log';
$_LANG['packages.logs.modals.clean_log_modal.title']          = 'Clean log';
$_LANG['packages.logs.modals.clean_log_modal.description']    = 'Delete all entries of the module log? This cannot be undone. The Certificate Log and the WHMCS logs are not affected.';
$_LANG['packages.logs.forms.clean_log_form.delete_success']   = 'The log has been cleaned';
$_LANG['packages.logs.forms.clean_log_form.deletingLogsIsNotAllowed'] = 'Deleting logs is not permitted';
$_LANG['packages.logs.forms.edit_configuration_form.logging']             = 'Module Log';
$_LANG['packages.logs.forms.edit_configuration_form.logging_description'] = 'Record the technical events of the module (cron results, errors, order actions). With it off, nothing new is written here.';
$_LANG['packages.logs.forms.edit_configuration_form.api_log']             = 'API requests';
$_LANG['packages.logs.forms.edit_configuration_form.api_log_description'] = 'Record every request to the certificate gateway with its answer. Needs Module Log on.';

$_LANG['packages.logs.forms.edit_configuration_form.api_log_reads']             = 'Also log successful reads';
$_LANG['packages.logs.forms.edit_configuration_form.api_log_reads_description'] = 'By default only failed requests and requests that change something are logged. Turn this on only while debugging: the crons read every pending order every few minutes.';

//Export CSV Modal
$_LANG['packages.logs.buttons.export_csv_button.title']                    = 'Export CSV';
$_LANG['packages.logs.modals.export_csv_modal.title']                      = 'Export CSV';
$_LANG['packages.logs.modals.export_csv_modal.submit']                     = 'Confirm';
$_LANG['packages.logs.forms.export_csv_form.from']                         = 'From';
$_LANG['packages.logs.forms.export_csv_form.from_description']             = 'Choose the starting date for log export.';
$_LANG['packages.logs.forms.export_csv_form.to']                           = 'To';
$_LANG['packages.logs.forms.export_csv_form.to_description']               = 'Select the ending date for logs export.';
$_LANG['packages.logs.forms.export_csv_form.types']                        = 'Log Types';
$_LANG['packages.logs.forms.export_csv_form.types_description']            = 'Choose log types for export. Leave the field empty to export all types.';
$_LANG['packages.logs.forms.export_csv_form.allTypes']                     = 'All Types';
$_LANG['packages.logs.forms.export_csv_form.formValidationError']          = 'Please enter correct values';
$_LANG['packages.logs.providers.export_provider.logsExportedSuccessfully'] = 'The logs have been exported successfully';
$_LANG['packages.logs.providers.export_provider.noLogsForThisTherms']      = "No logs found for the specified time frame. Please choose another time frame.";

//Menu Delete Modal
$_LANG['packages.logs.buttons.menu_delete_button.title']                     = 'Delete';
$_LANG['packages.logs.modals.menu_delete_modal.title']                       = 'Delete Logs Manually';
$_LANG['packages.logs.forms.menu_delete_form.types']                         = 'Log Types';
$_LANG['packages.logs.forms.menu_delete_form.types_description']             = 'Choose log types for deletion. Leave the field empty to delete all types.';
$_LANG['packages.logs.forms.menu_delete_form.delete_older_than']             = 'Delete Logs Older Than';
$_LANG['packages.logs.forms.menu_delete_form.delete_older_than_description'] = 'Enter the number of days after which the logs will be deleted.';
$_LANG['packages.logs.forms.menu_delete_form.delete_success']                = 'The selected logs have been deleted successfully';
$_LANG['packages.logs.forms.menu_delete_form.deletingLogsIsNotAllowed']      = "Deleting logs is not permitted";
$_LANG['packages.logs.forms.menu_delete_form.formValidationError']           = 'Please enter correct values';

//Mass Delete Modal
$_LANG['packages.logs.buttons.mass_delete_button.DeleteMass'] = "Delete";
$_LANG['packages.logs.modals.mass_delete_modal.title']        = 'Delete Logs';
$_LANG['packages.logs.modals.mass_delete_modal.description']  = 'Are you sure that you want to delete the selected logs?';

//Formatters
$_LANG['packages.logs.formatters.related_item.order']   = "Order";
$_LANG['packages.logs.formatters.related_item.invoice'] = "Invoice";
$_LANG['packages.logs.formatters.related_item.ticket']  = "Ticket";

$_LANG['packages.logs.widgets.summary.aboutTitle'] = 'What you will find here';
$_LANG['packages.logs.widgets.summary.aboutIntro'] = 'The technical log of the module: what the crons and the order actions did, and every request sent to the certificate gateway with its answer. Use it to find out why an order, validation or sync failed.';
$_LANG['packages.logs.widgets.summary.aboutApi'] = 'API requests: each call to the certificate gateway is logged as API, with the method, URL, HTTP code and duration.';
$_LANG['packages.logs.widgets.summary.aboutError'] = 'Error entries are failures: an API answer with an error code, an exception in a cron, or an action that could not finish.';
$_LANG['packages.logs.widgets.summary.aboutDetails'] = 'Show details opens the full request and response (keys and passwords are masked), the service and client concerned, and the other entries of the same service around that time.';
$_LANG['packages.logs.widgets.summary.aboutRelated'] = 'Related Item links to the service the entry is about. To see everything logged for one service, search for its number (#71), its domain, a product or client name, or the vendor order ID.';
$_LANG['packages.logs.widgets.summary.aboutSettings'] = 'Entries are kept for the period set under Actions > Settings, then removed automatically; Clean log empties the log at once. For the order history of one certificate see the Certificate Log page.';
$_LANG['packages.logs.widgets.summary.infoNote'] = 'Completed actions and successful API calls.';
$_LANG['packages.logs.widgets.summary.errorNote'] = 'Failures. Open Show details to see the request, the answer and the cause.';
$_LANG['packages.logs.widgets.summary.warningNote'] = 'Things that worked but need attention.';
$_LANG['packages.logs.widgets.summary.debugNote'] = 'Extra technical detail.';
$_LANG['packages.logs.widgets.summary.totalNote'] = 'All entries in the log. Click Show to list them.';
$_LANG['packages.logs.widgets.summary.show']           = 'Show';
$_LANG['packages.logs.widgets.summary.alertTitle']     = 'Alerts';
$_LANG['packages.logs.widgets.summary.criticalTitle']  = 'Critical';
$_LANG['packages.logs.widgets.summary.debugTitle']     = 'Debug';
$_LANG['packages.logs.widgets.summary.emergencyTitle'] = 'Emergency';
$_LANG['packages.logs.widgets.summary.errorTitle']     = 'Error';
$_LANG['packages.logs.widgets.summary.warningTitle']   = 'Warning';
$_LANG['packages.logs.widgets.summary.successTitle']   = 'Success';
$_LANG['packages.logs.widgets.summary.noticeTitle']    = 'Notice';
$_LANG['packages.logs.widgets.summary.infoTitle']      = 'Info';
$_LANG['packages.logs.widgets.summary.totalTitle']     = 'Total';
$_LANG['packages.logs.forms.edit_configuration_form.api_mode'] = 'API requests';
$_LANG['packages.logs.forms.edit_configuration_form.api_mode_description'] = 'Failures = the gateway returned an error. Changes = orders, validations, reissues, cancellations. Reads = the crons checking every pending order every few minutes (they flood the log; use Everything only while debugging).';
$_LANG['packages.logs.forms.edit_configuration_form.events'] = 'Events';
$_LANG['packages.logs.forms.edit_configuration_form.events_description'] = 'Status changes, notifications, renewal invoices, price updates. Errors are always recorded.';
$_LANG['packages.logs.forms.edit_configuration_form.debug_services'] = 'Debug one order: service IDs';
$_LANG['packages.logs.forms.edit_configuration_form.debug_services_description'] = 'For example 71, 58. Everything is recorded for these services, including reads, whatever the settings above say. Empty = off.';
$_LANG['packages.logs.forms.edit_configuration_form.debug_hours'] = 'Record for';
$_LANG['packages.logs.forms.edit_configuration_form.debug_hours_description'] = 'Saving starts this period again. Debug mode ends by itself.';
$_LANG['packages.logs.widgets.summary.debugTitle'] = 'Debug mode is on.';
$_LANG['packages.logs.widgets.summary.debugText'] = 'Everything is being recorded for services :services until :until, including every gateway request and event. This can fill the log quickly.';
$_LANG['packages.logs.widgets.summary.debugStop'] = 'Stop now';
$_LANG['packages.logs.widgets.summary.aboutLevels'] = 'What is recorded is set in Actions > Settings: gateway requests (off, failures only, failures and changes, or everything), events, and a debug mode that records everything for chosen services for a few hours.';
