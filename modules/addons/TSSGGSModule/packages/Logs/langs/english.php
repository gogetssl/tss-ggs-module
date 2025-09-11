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