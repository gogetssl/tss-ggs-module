<?php

namespace TSSGGSModule\App\Libs\Settings;

use TSSGGSModule\App\Kernel\Lang;
use TSSGGSModule\App\Libs\Emails\EmailCatalog;
use TSSGGSModule\App\Libs\Emails\EmailManager;
use TSSGGSModule\App\Pages\Admin\Emails;

/**
 * Settings > Email notifications: every e-mail the module has, by group, with its state. The Enable / Disable buttons
 * post to the Emails page's toggle action (same permission and activity log), which comes back to this screen and
 * leaves its message in the Emails page's session entry.
 */
class EmailScreen
{
    public static function data(Lang $lang): array
    {
        if(!Emails::can(Emails::VIEW))
        {
            return ['denied' => $lang->get('admin.emails.no_permission', ['permission' => Emails::VIEW]), 'groups' => [], 'canEdit' => false, 'error' => '', 'notice' => ''];
        }

        $session = $_SESSION[Emails::SESSION] ?? [];
        unset($_SESSION[Emails::SESSION]['notice'], $_SESSION[Emails::SESSION]['error']);

        $byGroup = [];
        foreach(EmailManager::overview() as $row)
        {
            $byGroup[$row['group']][] = [
                'key'        => (string)$row['key'],
                'slug'       => (string)$row['slug'],
                'disabled'   => (bool)$row['disabled'],
                'unused'     => empty($row['sent']),
                'customised' => (bool)$row['customised'],
            ];
        }
        $groups = [];
        foreach(EmailCatalog::GROUPS as $group)
        {
            if(!empty($byGroup[$group]))
            {
                $count           = count($byGroup[$group]);
                $groups[$group] = ['rows' => $byGroup[$group], 'count' => $lang->get('admin.emails.' . ($count === 1 ? 'group_count_one' : 'group_count'), ['count' => $count])];
            }
        }

        return [
            'denied'  => '',
            'error'   => (string)($session['error'] ?? ''),
            'notice'  => !empty($session['notice']) ? $lang->get('admin.emails.notice_' . $session['notice']) : '',
            'groups'  => $groups,
            'canEdit' => Emails::can(Emails::EDIT),
        ];
    }
}
