<?php

/***********************************************************************************************************************
 *                                                GENERAL                                                              *
 ***********************************************************************************************************************/

$_LANG['datatableItemsSelected']         = 'Items Selected';
$_LANG['bootstrapswitch']['disabled']    = 'Disabled';
$_LANG['bootstrapswitch']['enabled']     = 'Enabled';

// *********** PAGE NOT FOUND **************

//Title and description from component
$_LANG['admin.breadcrumbs.404']                  = '404 Error - Page Not Found';
$_LANG['client.breadcrumbs.404']                 = '404 Error - Page Not Found';

// *********** ERROR PAGE **************

$_LANG['addonCA']['errorPage']['title']       = 'AN ERROR OCCURRED';
$_LANG['addonCA']['errorPage']['description'] = 'An error occurred. Please contact administrator and pass the details:';
$_LANG['addonCA']['errorPage']['button']      = 'Return to the previous page';
$_LANG['addonCA']['errorPage']['error']       = 'ERROR';

$_LANG['addonCA']['errorPage']['errorCode']    = 'Error Code';
$_LANG['addonCA']['errorPage']['errorToken']   = 'Error Token';
$_LANG['addonCA']['errorPage']['errorTime']    = 'Time';
$_LANG['addonCA']['errorPage']['errorMessage'] = 'Message';

/***********************************************************************************************************************
 *                                                   ERROR MESSAGES                                                    *
 ***********************************************************************************************************************/

$_LANG['errorCodeMessage']['Uncategorised error occured']         = 'Unexpected Error';
$_LANG['errorCodeMessage']['Database error']                      = 'Database error';
$_LANG['errorCodeMessage']['Provided controller does not exists'] = 'Page not found';
$_LANG['errorCodeMessage']['Invalid Error Code!']                 = 'Unexpected Error';

$_LANG['generalError']           = 'Something has gone wrong. Check the logs and contact the administrator.';
$_LANG['generalErrorClientArea'] = 'Something has gone wrong. Contact the administrator.';
$_LANG['permissionsStorage']     = ':storage_path: settings are not sufficient. Please set up permissions to the \'storage\' folder as writable.';
$_LANG['undefinedAction']        = 'Undefined Action';
$_LANG['formValidationError']                                       = 'A form validation error has occurred';

$_LANG['cronJob.error.cronMustByCalledViaCli.console']  = 'Script must be called via cli. Exiting.';
$_LANG['cronJob.error.cronMustByCalledViaCli.log']      = 'Cron job ":cronJobName:" was not called via cli. Cron: :cronFullName:';

/***********************************************************************************************************************
 *                                                   VALIDATORS                                                        *
 ***********************************************************************************************************************/

$_LANG['validation.accepted'] = 'This field must be accepted.';
$_LANG['validation.accepted_if'] = 'This field must be accepted when :other is :value.';
$_LANG['validation.active_url'] = 'This field must be a valid URL.';
$_LANG['validation.after'] = 'This field must be a date after :date.';
$_LANG['validation.after_or_equal'] = 'This field must be a date after or equal to :date.';
$_LANG['validation.alpha'] = 'This field must only contain letters.';
$_LANG['validation.alpha_dash'] = 'This field must only contain letters, numbers, dashes, and underscores.';
$_LANG['validation.alpha_num'] = 'This field must only contain letters and numbers.';
$_LANG['validation.array'] = 'This field must be an array.';
$_LANG['validation.ascii'] = 'This field must only contain single-byte alphanumeric characters and symbols.';
$_LANG['validation.before'] = 'This field must be a date before :date.';
$_LANG['validation.before_or_equal'] = 'This field must be a date before or equal to :date.';
$_LANG['validation.between.array'] = 'This field must have between :min and :max items.';
$_LANG['validation.between.file'] = 'This field must be between :min and :max kilobytes.';
$_LANG['validation.between.numeric'] = 'This field must be between :min and :max.';
$_LANG['validation.between.string'] = 'This field must be between :min and :max characters.';
$_LANG['validation.boolean'] = 'This field must be true or false.';
$_LANG['validation.can'] = 'This field contains an unauthorized value.';
$_LANG['validation.confirmed'] = 'This field confirmation does not match.';
$_LANG['validation.contains'] = 'This field is missing a required value.';
$_LANG['validation.current_password'] = 'The password is incorrect.';
$_LANG['validation.date'] = 'This field must be a valid date.';
$_LANG['validation.date_equals'] = 'This field must be a date equal to :date.';
$_LANG['validation.date_format'] = 'This field must match the format :format.';
$_LANG['validation.decimal'] = 'This field must have :decimal decimal places.';
$_LANG['validation.declined'] = 'This field must be declined.';
$_LANG['validation.declined_if'] = 'This field must be declined when :other is :value.';
$_LANG['validation.different'] = 'This field and :other must be different.';
$_LANG['validation.digits'] = 'This field must be :digits digits.';
$_LANG['validation.digits_between'] = 'This field must be between :min and :max digits.';
$_LANG['validation.dimensions'] = 'This field has invalid image dimensions.';
$_LANG['validation.distinct'] = 'This field has a duplicate value.';
$_LANG['validation.doesnt_end_with'] = 'This field must not end with one of the following: :values.';
$_LANG['validation.doesnt_start_with'] = 'This field must not start with one of the following: :values.';
$_LANG['validation.email'] = 'This field must be a valid email address.';
$_LANG['validation.ends_with'] = 'This field must end with one of the following: :values.';
$_LANG['validation.enum'] = 'The selected value is invalid.';
$_LANG['validation.exists'] = 'The selected value is invalid.';
$_LANG['validation.extensions'] = 'This field must have one of the following extensions: :values.';
$_LANG['validation.file'] = 'This field must be a file.';
$_LANG['validation.filled'] = 'This field must have a value.';
$_LANG['validation.gt.array'] = 'This field must have more than :value items.';
$_LANG['validation.gt.file'] = 'This field must be greater than :value kilobytes.';
$_LANG['validation.gt.numeric'] = 'This field must be greater than :value.';
$_LANG['validation.gt.string'] = 'This field must be greater than :value characters.';
$_LANG['validation.gte.array'] = 'This field must have :value items or more.';
$_LANG['validation.gte.file'] = 'This field must be greater than or equal to :value kilobytes.';
$_LANG['validation.gte.numeric'] = 'This field must be greater than or equal to :value.';
$_LANG['validation.gte.string'] = 'This field must be greater than or equal to :value characters.';
$_LANG['validation.hex_color'] = 'This field must be a valid hexadecimal color.';
$_LANG['validation.image'] = 'This field must be an image.';
$_LANG['validation.in'] = 'The selected value is invalid.';
$_LANG['validation.in_array'] = 'This field must exist in :other.';
$_LANG['validation.integer'] = 'This field must be an integer.';
$_LANG['validation.ip'] = 'This field must be a valid IP address.';
$_LANG['validation.ipv4'] = 'This field must be a valid IPv4 address.';
$_LANG['validation.ipv6'] = 'This field must be a valid IPv6 address.';
$_LANG['validation.json'] = 'This field must be a valid JSON string.';
$_LANG['validation.list'] = 'This field must be a list.';
$_LANG['validation.lowercase'] = 'This field must be lowercase.';
$_LANG['validation.lt.array'] = 'This field must have less than :value items.';
$_LANG['validation.lt.file'] = 'This field must be less than :value kilobytes.';
$_LANG['validation.lt.numeric'] = 'This field must be less than :value.';
$_LANG['validation.lt.string'] = 'This field must be less than :value characters.';
$_LANG['validation.lte.array'] = 'This field must not have more than :value items.';
$_LANG['validation.lte.file'] = 'This field must be less than or equal to :value kilobytes.';
$_LANG['validation.lte.numeric'] = 'This field must be less than or equal to :value.';
$_LANG['validation.lte.string'] = 'This field must be less than or equal to :value characters.';
$_LANG['validation.mac_address'] = 'This field must be a valid MAC address.';
$_LANG['validation.max.array'] = 'This field must not have more than :max items.';
$_LANG['validation.max.file'] = 'This field must not be greater than :max kilobytes.';
$_LANG['validation.max.numeric'] = 'This field must not be greater than :max.';
$_LANG['validation.max.string'] = 'This field must not be greater than :max characters.';
$_LANG['validation.max_digits'] = 'This field must not have more than :max digits.';
$_LANG['validation.mimes'] = 'This field must be a file of type: :values.';
$_LANG['validation.mimetypes'] = 'This field must be a file of type: :values.';
$_LANG['validation.min.array'] = 'This field must have at least :min items.';
$_LANG['validation.min.file'] = 'This field must be at least :min kilobytes.';
$_LANG['validation.min.numeric'] = 'This field must be at least :min.';
$_LANG['validation.min.string'] = 'This field must be at least :min characters.';
$_LANG['validation.min_digits'] = 'This field must have at least :min digits.';
$_LANG['validation.missing'] = 'This field must be missing.';
$_LANG['validation.missing_if'] = 'This field must be missing when :other is :value.';
$_LANG['validation.missing_unless'] = 'This field must be missing unless :other is :value.';
$_LANG['validation.missing_with'] = 'This field must be missing when :values is present.';
$_LANG['validation.missing_with_all'] = 'This field must be missing when :values are present.';
$_LANG['validation.multiple_of'] = 'This field must be a multiple of :value.';
$_LANG['validation.not_in'] = 'The selected value is invalid.';
$_LANG['validation.not_regex'] = 'This field format is invalid.';
$_LANG['validation.numeric'] = 'This field must be a number.';
$_LANG['validation.password.letters'] = 'This field must contain at least one letter.';
$_LANG['validation.password.mixed'] = 'This field must contain at least one uppercase and one lowercase letter.';
$_LANG['validation.password.numbers'] = 'This field must contain at least one number.';
$_LANG['validation.password.symbols'] = 'This field must contain at least one symbol.';
$_LANG['validation.password.uncompromised'] = 'The given value has appeared in a data leak. Please choose a different value.';
$_LANG['validation.present'] = 'This field must be present.';
$_LANG['validation.present_if'] = 'This field must be present when :other is :value.';
$_LANG['validation.present_unless'] = 'This field must be present unless :other is :value.';
$_LANG['validation.present_with'] = 'This field must be present when :values is present.';
$_LANG['validation.present_with_all'] = 'This field must be present when :values are present.';
$_LANG['validation.prohibited'] = 'This field is prohibited.';
$_LANG['validation.prohibited_if'] = 'This field is prohibited when :other is :value.';
$_LANG['validation.prohibited_unless'] = 'This field is prohibited unless :other is in :values.';
$_LANG['validation.prohibits'] = 'This field prohibits :other from being present.';
$_LANG['validation.regex'] = 'This field format is invalid.';
$_LANG['validation.required'] = 'This field is required.';
$_LANG['validation.required_array_keys'] = 'This field must contain entries for: :values.';
$_LANG['validation.required_if'] = 'This field is required when :other is :value.';
$_LANG['validation.required_if_accepted'] = 'This field is required when :other is accepted.';
$_LANG['validation.required_if_declined'] = 'This field is required when :other is declined.';
$_LANG['validation.required_unless'] = 'This field is required unless :other is in :values.';
$_LANG['validation.required_with'] = 'This field is required when :values is present.';
$_LANG['validation.required_with_all'] = 'This field is required when :values are present.';
$_LANG['validation.required_without'] = 'This field is required when :values is not present.';
$_LANG['validation.required_without_all'] = 'This field is required when none of :values are present.';
$_LANG['validation.same'] = 'This field must match :other.';
$_LANG['validation.size.array'] = 'This field must contain :size items.';
$_LANG['validation.size.file'] = 'This field must be :size kilobytes.';
$_LANG['validation.size.numeric'] = 'This field must be :size.';
$_LANG['validation.size.string'] = 'This field must be :size characters.';
$_LANG['validation.starts_with'] = 'This field must start with one of the following: :values.';
$_LANG['validation.string'] = 'This field must be a string.';
$_LANG['validation.timezone'] = 'This field must be a valid timezone.';
$_LANG['validation.unique'] = 'This value has already been taken.';
$_LANG['validation.uploaded'] = 'This field failed to upload.';
$_LANG['validation.uppercase'] = 'This field must be uppercase.';
$_LANG['validation.url'] = 'This field must be a valid URL.';
$_LANG['validation.ulid'] = 'This field must be a valid ULID.';
$_LANG['validation.uuid'] = 'This field must be a valid UUID.';

/***********************************************************************************************************************
 *                                             ACL RESOURCES FOR PACKAGES                                              *
 ***********************************************************************************************************************/

//*********** Access Control **************
$_LANG['acl.resources.accessControl']                           = 'Access Control';
$_LANG['acl.resources.accessControl.resources']                 = 'Resources';
$_LANG['acl.resources.accessControl.resources.edit']            = 'Edit';
$_LANG['acl.resources.accessControl.rules']                     = 'Rules';
$_LANG['acl.resources.accessControl.rules.create']              = 'Create';
$_LANG['acl.resources.accessControl.rules.edit']                = 'Edit';
$_LANG['acl.resources.accessControl.rules.delete']              = 'Delete';
$_LANG['acl.resources.accessControl.logs']                      = 'Logs';
$_LANG['acl.resources.accessControl.logs.delete']               = 'Delete';

//*********** Logs **************
$_LANG['acl.resources.logs']                = 'Logs';
$_LANG['acl.resources.logs.export']         = 'Export CSV';
$_LANG['acl.resources.logs.delete']         = 'Delete Logs';
$_LANG['acl.resources.logs.settings']       = 'Settings';
$_LANG['acl.resources.logs.settings.edit']  = 'Edit';
$_LANG['acl.resources.logs.details']        = 'Show Details';
$_LANG['acl.resources.logs.delete_menu']    = 'Delete Logs Manually';

//*********** Translations **************
$_LANG['acl.resources.translations']                = 'Translations';
$_LANG['acl.resources.translations.create']         = 'Create';
$_LANG['acl.resources.translations.update']         = 'Update';
$_LANG['acl.resources.translations.delete']         = 'Delete';
$_LANG['acl.resources.translations.clone']          = 'Clone';
$_LANG['acl.resources.translations.export']         = 'Export';
$_LANG['acl.resources.translations.import']         = 'Import';
$_LANG['acl.resources.translations.langs']          = 'Langs';
$_LANG['acl.resources.translations.langs.edit']     = 'Edit';
$_LANG['acl.resources.translations.langs.delete']   = 'Delete';