<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Emails\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Emails as EmailsController;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\App\Libs\LegacyEmailImport;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Admin page "Emails": the module's client e-mails by event, edited per language where WHMCS keeps them.
 */
class EmailsContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=emails';

    public function loadHtml(): void
    {
        $this->setCss('tssggs-migration tssggs-emails');

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($this->page());
        $this->addElement($text);
    }

    protected function page()
    {
        if(!EmailsController::can(EmailsController::VIEW))
        {
            return '<div class="tssggs-note is-bad">' . $this->e($this->t('no_permission', ['permission' => EmailsController::VIEW])) . '</div>';
        }

        $session = $_SESSION[EmailsController::SESSION] ?? [];
        unset($_SESSION[EmailsController::SESSION]['draft'], $_SESSION[EmailsController::SESSION]['notice'],$_SESSION[EmailsController::SESSION]['error'], $_SESSION[EmailsController::SESSION]['preview'], $_SESSION[EmailsController::SESSION]['count']);

        $overview  = EmailManager::overview();
        $key       = isset($_GET['key']) && isset($overview[$_GET['key']]) ? $_GET['key'] : (isset($session['draft']['key']) && isset($overview[$session['draft']['key']]) ? $session['draft']['key'] : array_key_first($overview));
        $languages = EmailManager::languages();
        $language  = strtolower((string)($_GET['lang'] ?? ''));
        $language  = in_array($language, $languages, true) ? $language : '';

        $html = '';
        if(!empty($session['error']))
        {
            $html .= '<div class="tssggs-note is-bad">' . $this->e($session['error']) . '</div>';
        }
        if(!empty($session['notice']))
        {
            $html .= '<div class="tssggs-note is-ok">' . $this->e($this->t('notice_' . $session['notice'], ['count' => (int)($session['count'] ?? 0)])) . '</div>';
        }

        $html .= '<p class="tssggs-muted">' . $this->e($this->t('intro')) . '</p>'
            . '<div style="display:flex;gap:22px;align-items:flex-start;flex-wrap:wrap;">'
            . '<div style="flex:0 0 300px;max-width:100%;">' . $this->list($overview, $key, $language) . '</div>'
            . '<div style="flex:1 1 560px;min-width:0;">' . $this->editor($overview[$key], $language, $languages, $session) . '</div>'
            . '</div>'
            . $this->legacy();

        return $html;
    }

    protected function list(array $overview, $current, $language)
    {
        $groups = [];
        foreach($overview as $key => $row)
        {
            $groups[$row['group']][] = $row;
        }

        $html = '';
        foreach(EmailCatalog::GROUPS as $group)
        {
            if(empty($groups[$group]))
            {
                continue;
            }
            $html .= '<h4 style="margin:14px 0 6px;">' . $this->e($this->t('group_' . $group)) . '</h4><ul style="list-style:none;margin:0;padding:0;">';
            foreach($groups[$group] as $row)
            {
                $badges = ($row['customised'] ? $this->badge($this->t('badge_customised'), '#9a6300') : '')
                    . ($row['disabled'] ? $this->badge($this->t('badge_disabled'), '#a8322d') : '')
                    . (!$row['sent'] ? $this->badge($this->t('badge_unused'), '#6b7480') : '')
                    . ($row['languages'] ? $this->badge(count($row['languages']) . ' ' . $this->t('badge_languages'), '#0f5d8f') : '');
                $html  .= '<li style="margin:2px 0;"><a href="' . self::BASE . '&key=' . rawurlencode($row['key']) . ($language !== '' ? '&lang=' . rawurlencode($language) : '') . '" style="display:block;padding:6px 8px;border-radius:4px;'
                    . ($row['key'] === $current ? 'background:#e3eef6;font-weight:600;' : '') . '">' . $this->e($this->t('name_' . $row['slug'])) . $badges . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    protected function editor(array $row, $language, array $languages, array $session)
    {
        $key   = $row['key'];
        $draft = isset($session['draft']) && $session['draft']['key'] === $key && $session['draft']['lang'] === $language ? $session['draft'] : null;
        $data  = EmailManager::load($key, $language);
        $text  = $draft ?: $data;
        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        $can   = EmailsController::can(EmailsController::EDIT);

        $html = '<h3 style="margin-top:0;">' . $this->e($this->t('name_' . $row['slug'])) . '</h3>'
            . '<p>' . $this->e($this->t('when_' . $row['slug'])) . '</p>'
            . '<p class="tssggs-muted">' . $this->e($this->t('whmcs_name')) . ' <code>' . $this->e($row['name']) . '</code></p>';

        // language selector
        $html .= '<form method="get" action="addonmodules.php" style="margin-bottom:12px;"><input type="hidden" name="module" value="TSSGGSModule"><input type="hidden" name="mg-page" value="emails"><input type="hidden" name="key" value="' . $this->e($key) . '">'
            . '<label style="font-weight:600;margin-right:8px;">' . $this->e($this->t('language')) . '</label><select name="lang" class="form-control" style="display:inline-block;width:auto;" onchange="this.form.submit()">'
            . '<option value="">' . $this->e($this->t('language_default')) . '</option>';
        foreach($languages as $name)
        {
            $html .= '<option value="' . $this->e($name) . '"' . ($name === $language ? ' selected' : '') . '>' . $this->e(ucfirst($name)) . (in_array($name, $row['languages'], true) ? ' ✓' : '') . '</option>';
        }
        $html .= '</select></form>';

        if($language !== '' && !$data['own'])
        {
            $html .= '<div class="tssggs-note is-warn">' . $this->e($this->t('language_new', ['language' => ucfirst($language)])) . '</div>';
        }
        elseif($language === '')
        {
            $html .= '<p class="tssggs-muted">' . $this->e($this->t('language_default_hint')) . '</p>';
        }

        // editor form
        $html .= '<form method="post" action="' . self::BASE . '&mg-action=save">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="key" value="' . $this->e($key) . '"><input type="hidden" name="lang" value="' . $this->e($language) . '">'
            . '<label style="font-weight:600;">' . $this->e($this->t('subject')) . '</label>'
            . '<input class="form-control" name="subject" value="' . $this->e($text['subject']) . '" style="margin-bottom:10px;"' . ($can ? '' : ' readonly') . '>'
            . '<label style="font-weight:600;">' . $this->e($this->t('message')) . '</label>'
            . '<textarea class="form-control" id="tssggs-email-message" name="message" rows="16" style="font-family:Consolas,monospace;font-size:12px;"' . ($can ? '' : ' readonly') . '>' . $this->e($text['message']) . '</textarea>'
            . '<p style="margin:8px 0;"><strong>' . $this->e($this->t('fields')) . '</strong> ' . $this->fields($row) . '</p>'
            . '<div style="display:flex;flex-wrap:wrap;gap:8px;margin:12px 0;">';

        if($can)
        {
            $html .= $this->button($this->t('save'), 'lu-btn lu-btn--success', 'save')
                . $this->button($this->t('preview'), 'lu-btn lu-btn--default', 'preview')
                . $this->button($this->t('test'), 'lu-btn lu-btn--default', 'test', $this->t('confirm_test'));
            if($language === '')
            {
                $html .= $this->button($this->t('reset'), 'lu-btn lu-btn--default', 'reset', $this->t('confirm_reset'))
                    . '<button class="lu-btn lu-btn--default" type="submit" formaction="' . self::BASE . '&mg-action=toggle" name="disable" value="' . ($row['disabled'] ? '0' : '1') . '">' . $this->e($row['disabled'] ? $this->t('enable') : $this->t('disable')) . '</button>';
            }
            elseif($data['own'])
            {
                $html .= $this->button($this->t('remove_language', ['language' => ucfirst($language)]), 'lu-btn lu-btn--default', 'removeLanguage', $this->t('confirm_remove_language'));
            }
        }
        $html .= '</div></form>';

        if(!empty($session['preview']))
        {
            $html .= '<h4>' . $this->e($this->t('preview_title')) . '</h4><p><strong>' . $this->e($this->t('subject')) . ':</strong> ' . $this->e($session['preview']['subject']) . '</p>'
                . '<iframe sandbox="" style="width:100%;height:420px;border:1px solid #d9dee5;background:#fff;" srcdoc="' . $this->e($session['preview']['body']) . '"></iframe>'
                . '<p class="tssggs-muted">' . $this->e($this->t('preview_hint')) . '</p>';
        }

        return $html;
    }

    protected function fields(array $row)
    {
        $html  = '';
        $names = array_merge($row['fields'], EmailCatalog::STANDARD_FIELDS);
        foreach(array_unique($names) as $name)
        {
            $insert = '{$' . $name . '}';
            $html  .= '<a href="#" onclick="var t=document.getElementById(\'tssggs-email-message\');var s=t.selectionStart;t.value=t.value.slice(0,s)+' . $this->e(json_encode($insert)) . '+t.value.slice(t.selectionEnd);t.focus();return false;" '
                . 'style="display:inline-block;margin:2px 4px 2px 0;padding:2px 7px;border:1px solid #d9dee5;border-radius:4px;font-family:Consolas,monospace;font-size:12px;">' . $this->e($insert) . '</a>';
        }

        return $html;
    }

    protected function button($label, $class, $action, $confirm = '')
    {
        return '<button class="' . $class . '" type="submit" formaction="' . self::BASE . '&mg-action=' . $action . '"' . ($confirm !== '' ? ' onclick="return confirm(' . $this->e(json_encode($confirm)) . ')"' : '') . '>' . $this->e($label) . '</button>';
    }

    protected function badge($label, $color)
    {
        return ' <span style="font-size:11px;font-weight:600;color:' . $color . ';border:1px solid ' . $color . ';border-radius:3px;padding:0 4px;">' . $this->e($label) . '</span>';
    }

    /**
     * Legacy module texts that can be copied - nothing is selected by default
     */
    protected function legacy()
    {
        if(!EmailsController::can(EmailsController::EDIT))
        {
            return '';
        }
        $candidates = LegacyEmailImport::candidates();
        if(!$candidates)
        {
            return '';
        }

        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        $html  = '<h3 style="margin-top:28px;">' . $this->e($this->t('legacy_title')) . '</h3><p class="tssggs-muted">' . $this->e($this->t('legacy_intro')) . '</p>'
            . '<form method="post" action="' . self::BASE . '&mg-action=import" onsubmit="return confirm(' . $this->e(json_encode($this->t('legacy_confirm'))) . ')"><input type="hidden" name="token" value="' . $this->e($token) . '">'
            . '<table class="table table-condensed"><thead><tr><th style="width:30px"></th><th>' . $this->e($this->t('legacy_from')) . '</th><th>' . $this->e($this->t('legacy_to')) . '</th><th>' . $this->e($this->t('legacy_state')) . '</th></tr></thead><tbody>';
        foreach($candidates as $candidate)
        {
            $notes = [];
            if($candidate['same'])
            {
                $notes[] = '<span class="tssggs-muted">' . $this->e($this->t('legacy_same')) . '</span>';
            }
            if($candidate['languages'])
            {
                $notes[] = $this->e($this->t('legacy_languages', ['count' => $candidate['languages']]));
            }
            if($candidate['replaces_edited'])
            {
                $notes[] = '<span style="color:#9a6300;">' . $this->e($this->t('legacy_replaces')) . '</span>';
            }
            if($candidate['unknown'])
            {
                $notes[] = '<span style="color:#a8322d;">' . $this->e($this->t('legacy_unknown', ['fields' => implode(', ', array_map(function($f) { return '{$' . $f . '}'; }, $candidate['unknown']))])) . '</span>';
            }
            $html .= '<tr><td><input type="checkbox" name="legacy[]" value="' . $this->e($candidate['id']) . '"' . ($candidate['same'] ? ' disabled' : '') . '></td><td>' . $this->e($candidate['legacy']) . ' <small class="tssggs-muted">(' . strtoupper($candidate['source']) . ')</small></td>'
                . '<td>' . $this->e($this->t('name_' . EmailCatalog::slug($candidate['key']))) . '</td><td>' . implode('<br>', $notes) . '</td></tr>';
        }

        return $html . '</tbody></table><button class="lu-btn lu-btn--default" type="submit">' . $this->e($this->t('legacy_copy')) . '</button></form>';
    }

    protected function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    }

    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.emails']);
    }
}
