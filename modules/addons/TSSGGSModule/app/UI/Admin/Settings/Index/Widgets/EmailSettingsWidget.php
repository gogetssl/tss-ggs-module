<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Settings\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Emails as EmailsController;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;

/**
 * Settings > Email notifications: every e-mail the module has, whether it is on, one-click enable / disable.
 * The buttons post to the Emails page's toggle action (same token, permission and activity log), which comes back here.
 */
class EmailSettingsWidget extends Widget
{
    const EMAILS = 'addonmodules.php?module=TSSGGSModule&mg-page=emails';

    public function loadHtml(): void
    {
        $this->setTitle($this->translate('title'));
        $this->setIcon('bell-outline');
        $this->setSubtitle($this->translate('subtitle'));

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText('<div class="tssggs-emails">' . $this->table() . '</div>');
        $this->addElement($text);
    }

    protected function table(): string
    {
        if(!EmailsController::can(EmailsController::VIEW))
        {
            return '<div class="tssggs-note is-bad">' . $this->e($this->t('no_permission', ['permission' => EmailsController::VIEW])) . '</div>';
        }

        $session = $_SESSION[EmailsController::SESSION] ?? [];
        unset($_SESSION[EmailsController::SESSION]['notice'], $_SESSION[EmailsController::SESSION]['error']);
        $html = '';
        if(!empty($session['error']))
        {
            $html .= '<div class="tssggs-note is-bad">' . $this->e($session['error']) . '</div>';
        }
        if(!empty($session['notice']))
        {
            $html .= '<div class="tssggs-note is-ok">' . $this->e($this->t('notice_' . $session['notice'])) . '</div>';
        }

        $can   = EmailsController::can(EmailsController::EDIT);
        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        $rows  = [];
        foreach(EmailManager::overview() as $row)
        {
            $rows[$row['group']][] = $row;
        }

        $html .= '<table class="em-list"><thead><tr><th>' . $this->e($this->translate('col_email')) . '</th><th>' . $this->e($this->translate('col_when')) . '</th><th>'
            . $this->e($this->translate('col_status')) . '</th><th></th></tr></thead><tbody>';
        foreach(EmailCatalog::GROUPS as $group)
        {
            if(empty($rows[$group]))
            {
                continue;
            }
            // a section title: coloured bar + title + what the e-mails are about + how many (colours in migration.css)
            $count = count($rows[$group]);
            $html .= '<tr class="em-gap"><td colspan="4"></td></tr>'
                . '<tr class="em-group em-group--' . $this->e($group) . '"><td colspan="4"><span class="em-group__dot"></span>'
                . '<b class="em-group__title">' . $this->e($this->t('group_' . $group)) . '</b>'
                . '<small class="em-group__desc">' . $this->e($this->t('group_' . $group . '_desc')) . '</small>'
                . '<em class="em-group__count">' . $this->e($this->t($count === 1 ? 'group_count_one' : 'group_count', ['count' => $count])) . '</em></td></tr>';
            foreach($rows[$group] as $row)
            {
                $status = $row['disabled'] ? '<span class="em-tag is-off">' . $this->e($this->t('badge_disabled')) . '</span>'
                    : '<span class="em-tag is-on">' . $this->e($this->translate('enabled')) . '</span>';
                if(!$row['sent'])
                {
                    $status .= ' <span class="em-tag">' . $this->e($this->t('badge_unused')) . '</span>';
                }
                if($row['customised'])
                {
                    $status .= ' <span class="em-tag is-edited">' . $this->e($this->t('badge_customised')) . '</span>';
                }

                $actions = '<a class="lu-btn lu-btn--sm lu-btn--default" href="' . self::EMAILS . '&key=' . rawurlencode($row['key']) . '">' . $this->e($this->translate('edit')) . '</a>';
                if($can)
                {
                    $actions = '<form method="post" action="' . self::EMAILS . '&mg-action=toggle" style="display:inline">'
                        . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="key" value="' . $this->e($row['key']) . '">'
                        . '<input type="hidden" name="return" value="settings">'
                        . ($row['disabled'] ? '' : '<input type="hidden" name="disable" value="1">')
                        . '<button type="submit" class="lu-btn lu-btn--sm ' . ($row['disabled'] ? 'lu-btn--success' : 'lu-btn--default') . '">'
                        . $this->e($row['disabled'] ? $this->t('enable') : $this->t('disable')) . '</button></form> ' . $actions;
                }

                $html .= '<tr' . ($row['disabled'] ? ' class="is-off"' : '') . '><td><strong>' . $this->e($this->t('name_' . $row['slug'])) . '</strong></td><td class="tssggs-muted">'
                    . $this->e($this->t('when_' . $row['slug'])) . '</td><td>' . $status . '</td><td class="em-actions">' . $actions . '</td></tr>';
            }
        }

        return $html . '</tbody></table>';
    }

    protected function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    }

    /** texts shared with the Emails page */
    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.emails']);
    }
}
