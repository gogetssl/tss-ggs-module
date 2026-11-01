<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Emails\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Emails as EmailsController;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Admin page "Emails": the module's client e-mails by event, edited per language where WHMCS keeps them.
 * (Texts of the legacy modules are copied by the migration itself.)
 */
class EmailsContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=emails';
    /** Runs when the WHMCS template page has loaded in the frame: hides its admin chrome and fits the frame to the content */
    const FRAME_JS = "var d=this.contentDocument,s=d.createElement('style');s.textContent='.global-admin-warning,.navigation,#sidebar,#sidebarOpener,.footerbar,#intelligentSearchResults{display:none!important}.contentarea{margin:0!important;padding:0!important;min-height:0!important}body{background:#fff!important;padding:0!important}';d.head.appendChild(s);this.style.height=Math.max(600,d.documentElement.scrollHeight+20)+'px';";


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
        unset($_SESSION[EmailsController::SESSION]['draft'], $_SESSION[EmailsController::SESSION]['notice'], $_SESSION[EmailsController::SESSION]['error'], $_SESSION[EmailsController::SESSION]['preview'], $_SESSION[EmailsController::SESSION]['count']);

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

        return $html . '<p class="tssggs-muted">' . $this->e($this->t('intro')) . '</p>'
            . '<div class="em-layout"><div class="em-card em-nav">' . $this->nav($overview, $key, $language) . '</div>'
            . '<div class="em-card">' . $this->editor($overview[$key], $session) . '</div></div>';
    }

    protected function nav(array $overview, $current, $language)
    {
        $groups = [];
        foreach($overview as $row)
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
            $html .= '<h4>' . $this->e($this->t('group_' . $group)) . '</h4>';
            foreach($groups[$group] as $row)
            {
                $tags = ($row['disabled'] ? $this->tag($this->t('badge_disabled'), 'is-off') : '')
                    . (!$row['sent'] ? $this->tag($this->t('badge_unused'), '') : '')
                    . ($row['customised'] && !$row['disabled'] ? $this->tag($this->t('badge_customised'), 'is-edited') : '');
                $html .= '<a class="' . ($row['key'] === $current ? 'is-current' : '') . '" href="' . self::BASE . '&key=' . rawurlencode($row['key']) . ($language !== '' ? '&lang=' . rawurlencode($language) : '') . '"><span>'
                    . $this->e($this->t('name_' . $row['slug'])) . '</span><span>' . $tags . '</span></a>';
            }
        }

        return $html;
    }

    /**
     * The editor is WHMCS's own template page (visual editor, From / Copy to / Blind copy, attachments, plain text,
     * languages) in a frame with its admin chrome hidden; this page adds the module's extras below it
     */
    protected function editor(array $row, array $session)
    {
        $key   = $row['key'];
        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        $can   = EmailsController::can(EmailsController::EDIT);
        $id    = (int)\WHMCS\Database\Capsule::table(EmailManager::TABLE)->where('name', $row['name'])->where('language', '')->value('id');

        $html = '<div class="em-head"><h3>' . $this->e($this->t('name_' . $row['slug'])) . '</h3><p>' . $this->e($this->t('when_' . $row['slug'])) . '</p>'
            . '<p class="tssggs-muted">' . $this->e($this->t('whmcs_name')) . ' <code>' . $this->e($row['name']) . '</code></p></div>';

        if($row['disabled'])
        {
            $html .= '<div class="tssggs-note is-warn">' . $this->e($this->t('notice_disabled')) . '</div>';
        }
        if(!$row['sent'])
        {
            $html .= '<div class="tssggs-note">' . $this->e($this->t('badge_unused')) . '</div>';
        }

        $html .= '<iframe class="em-frame" id="tssggs-email-frame" src="configemailtemplates.php?action=edit&amp;id=' . $id . '" onload="' . $this->e(self::FRAME_JS) . '"></iframe>'
            . '<div class="em-fields"><small>' . $this->e($this->t('fields_own')) . '</small>';
        foreach($row['fields'] as $name)
        {
            $html .= '<code class="em-chip is-own">{$' . $this->e($name) . '}</code>';
        }
        $html .= '<small>' . $this->e($this->t('fields_standard')) . '</small>';
        foreach(array_diff(EmailCatalog::STANDARD_FIELDS, $row['fields']) as $name)
        {
            $html .= '<code class="em-chip">{$' . $this->e($name) . '}</code>';
        }
        $html .= '</div>';

        if($can)
        {
            $html .= '<form method="post" class="em-bar" action="' . self::BASE . '"><input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="key" value="' . $this->e($key) . '"><input type="hidden" name="lang" value="">'
                . $this->button($this->t('test'), 'lu-btn lu-btn--default', 'test', $this->t('confirm_test'))
                . '<span class="em-spacer"></span>'
                . '<button class="lu-btn lu-btn--default" type="submit" formaction="' . self::BASE . '&mg-action=toggle" name="disable" value="' . ($row['disabled'] ? '0' : '1') . '">' . $this->e($row['disabled'] ? $this->t('enable') : $this->t('disable')) . '</button>'
                . $this->button($this->t('reset'), 'lu-btn lu-btn--default is-danger', 'reset', $this->t('confirm_reset'))
                . '</form>';
        }

        return $html;
    }

    protected function button($label, $class, $action, $confirm = '')
    {
        return '<button class="' . $class . '" type="submit" formaction="' . self::BASE . '&mg-action=' . $action . '"' . ($confirm !== '' ? ' onclick="return confirm(' . $this->e(json_encode($confirm)) . ')"' : '') . '>' . $this->e($label) . '</button>';
    }

    protected function tag($label, $class)
    {
        return ' <span class="em-tag ' . $class . '">' . $this->e($label) . '</span>';
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
