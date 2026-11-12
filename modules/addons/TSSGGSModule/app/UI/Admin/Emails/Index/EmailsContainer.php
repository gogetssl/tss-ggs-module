<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Emails\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Emails as EmailsController;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Admin page "Emails": the module's client e-mails by event, edited per language where WHMCS keeps them.
 * The text is edited with the TinyMCE that ships with WHMCS (assets/js/tinymce), like on WHMCS's own template page.
 * (Texts of the legacy modules are copied by the migration itself.)
 */
class EmailsContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=emails';

    /**
     * Page script, run by the onerror of an image (scripts in the rendered text are not executed): loads WHMCS's TinyMCE
     * and defines tssggsRich(on) and tssggsInsert(text) for the toggle and the merge-field chips
     */
    const EDITOR_JS = "var id='tssggs-email-message';window.tssggsRich=function(on){if(on){if(window.tinymce&&!tinymce.get(id)){tinymce.init({selector:'#'+id,height:420,menubar:false,branding:false,plugins:'link lists code table autolink',toolbar:'undo redo | styleselect bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',relative_urls:false,convert_urls:false,remove_script_host:false,verify_html:false,entity_encoding:'raw'})}}else if(window.tinymce&&tinymce.get(id)){tinymce.get(id).remove()}};window.tssggsInsert=function(t){var e=window.tinymce&&tinymce.get(id);if(e){e.insertContent(t)}else{var a=document.getElementById(id),s=a.selectionStart;a.value=a.value.slice(0,s)+t+a.value.slice(a.selectionEnd);a.focus()}};var s=document.createElement('script');s.src='../assets/js/tinymce/tinymce.min.js';s.onload=function(){tssggsRich(document.getElementById('tssggs-rich').checked)};s.onerror=function(){document.getElementById('tssggs-rich').checked=false;document.getElementById('tssggs-rich-note').style.display='block'};document.head.appendChild(s);";

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
        $language  = strtolower((string)($_GET['lang'] ?? ($session['draft']['lang'] ?? '')));
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

        return $html . (\ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings::enabled('hideInfoMessages') ? '' : $this->explanation())
            . '<div class="em-layout"><div class="em-card em-nav">' . $this->nav($overview, $key) . '</div>'
            . '<div class="em-card">' . $this->editor($overview[$key], $language, $languages, $session) . '</div></div>';
    }

    /** What the page is for and how the e-mails behave */
    protected function explanation()
    {
        $item = function($key) {
            return '<li>' . $this->e($this->t($key)) . '</li>';
        };

        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
            . '<h3>' . $this->e($this->t('about_title')) . '</h3>'
            . '<p>' . $this->e($this->t('intro')) . '</p>'
            . '<ul>' . $item('about_edit') . $item('about_fields') . '</ul>'
            . '</div></div>';
    }

    protected function nav(array $overview, $current)
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
                $html .= '<a class="' . ($row['key'] === $current ? 'is-current' : '') . '" href="' . self::BASE . '&key=' . rawurlencode($row['key']) . '"><span>'
                    . $this->e($this->t('name_' . $row['slug'])) . '</span><span>' . $tags . '</span></a>';
            }
        }

        return $html;
    }

    protected function editor(array $row, $language, array $languages, array $session)
    {
        $key   = $row['key'];
        $draft = isset($session['draft']) && $session['draft']['key'] === $key && $session['draft']['lang'] === $language ? $session['draft'] : null;
        $data  = EmailManager::load($key, $language);
        $text  = $draft ? $draft + $data : $data;
        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        $can   = EmailsController::can(EmailsController::EDIT);
        $ro    = $can ? '' : ' readonly';

        $html = '<div class="em-head"><h3>' . $this->e($this->t('name_' . $row['slug'])) . '</h3><p>' . $this->e($this->t('when_' . $row['slug'])) . '</p>'
            . '<p class="tssggs-muted">' . $this->e($this->t('whmcs_name')) . ' <code>' . $this->e($row['name']) . '</code></p></div>'
            . $this->tabs($row, $language, $languages);

        if($language !== '' && !$data['own'])
        {
            $html .= '<div class="tssggs-note is-warn">' . $this->e($this->t('language_new', ['language' => ucfirst($language)])) . '</div>';
        }
        elseif($language === '')
        {
            $html .= '<p class="tssggs-muted" style="margin-top:0">' . $this->e($this->t('language_default_hint')) . '</p>';
        }

        $html .= '<form method="post" action="' . self::BASE . '&mg-action=save" onsubmit="if(window.tinymce){tinymce.triggerSave()}">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="key" value="' . $this->e($key) . '"><input type="hidden" name="lang" value="' . $this->e($language) . '">';

        // sender, copies, plain text and disabled belong to the default text only (as in WHMCS)
        if($language === '')
        {
            $html .= '<div class="em-opts">'
                . '<label>' . $this->e($this->t('opt_from')) . '</label><span class="em-inline"><input class="form-control" name="fromname" placeholder="' . $this->e($this->t('opt_from_name')) . '" value="' . $this->e($text['fromname']) . '"' . $ro . '>'
                . '<input class="form-control" name="fromemail" placeholder="' . $this->e($this->t('opt_from_email')) . '" value="' . $this->e($text['fromemail']) . '"' . $ro . '></span>'
                . '<label>' . $this->e($this->t('opt_copy')) . '</label><input class="form-control" name="copyto" placeholder="' . $this->e($this->t('opt_list_hint')) . '" value="' . $this->e($text['copyto']) . '"' . $ro . '>'
                . '<label>' . $this->e($this->t('opt_blind')) . '</label><input class="form-control" name="blind_copy_to" placeholder="' . $this->e($this->t('opt_list_hint')) . '" value="' . $this->e($text['blind_copy_to']) . '"' . $ro . '>'
                . '<label>' . $this->e($this->t('opt_plain')) . '</label><span><input type="checkbox" name="plaintext" value="1"' . (!empty($text['plaintext']) ? ' checked' : '') . ($can ? '' : ' disabled') . ' onchange="document.getElementById(\'tssggs-rich\').checked=!this.checked;tssggsRich(!this.checked)"> ' . $this->e($this->t('opt_plain_hint')) . '</span>'
                . '<label>' . $this->e($this->t('opt_disable')) . '</label><span><input type="checkbox" name="disabled" value="1"' . ($row['disabled'] ? ' checked' : '') . ($can ? '' : ' disabled') . '> ' . $this->e($this->t('opt_disable_hint')) . '</span>'
                . '</div>';
        }

        $html .= '<label class="em-label">' . $this->e($this->t('subject')) . '</label>'
            . '<input class="form-control" name="subject" value="' . $this->e($text['subject']) . '"' . $ro . '>'
            . '<div class="em-labelrow"><label class="em-label">' . $this->e($this->t('message')) . '</label>'
            . '<label class="em-rich"><input type="checkbox" id="tssggs-rich" onchange="tssggsRich(this.checked)"' . (empty($text['plaintext']) ? ' checked' : '') . '> ' . $this->e($this->t('rich_editor')) . '</label></div>'
            . '<textarea class="form-control em-message" id="tssggs-email-message" name="message"' . $ro . '>' . $this->e($text['message']) . '</textarea>'
            . '<p class="tssggs-note is-warn" id="tssggs-rich-note" style="display:none">' . $this->e($this->t('rich_failed')) . '</p>'
            . $this->fields($row)
            . '<img src="x:x" alt="" style="display:none" onerror="' . $this->e(self::EDITOR_JS) . '">';

        if($can)
        {
            $html .= '<div class="em-bar">'
                . $this->button($this->t('save'), 'lu-btn lu-btn--success', 'save')
                . $this->button($this->t('preview'), 'lu-btn lu-btn--default', 'preview')
                . $this->button($this->t('test'), 'lu-btn lu-btn--default', 'test', $this->t('confirm_test'))
                . '<span class="em-spacer"></span>';
            if($language === '')
            {
                $html .= $this->button($this->t('reset'), 'lu-btn lu-btn--default is-danger', 'reset', $this->t('confirm_reset'));
            }
            elseif($data['own'])
            {
                $html .= $this->button($this->t('remove_language', ['language' => ucfirst($language)]), 'lu-btn lu-btn--default is-danger', 'removeLanguage', $this->t('confirm_remove_language'));
            }
            $html .= '</div>';
        }
        $html .= '</form>';

        if(!empty($session['preview']))
        {
            $html .= '<div class="em-preview"><label class="em-label">' . $this->e($this->t('preview_title')) . '</label><p><strong>' . $this->e($this->t('subject')) . ':</strong> ' . $this->e($session['preview']['subject']) . '</p>'
                . '<iframe sandbox="" srcdoc="' . $this->e($session['preview']['body']) . '"></iframe>'
                . '<p class="tssggs-muted">' . $this->e($this->t('preview_hint')) . '</p></div>';
        }

        return $html;
    }

    /**
     * Default + the languages that have a text of their own as tabs; the rest in one "add language" list
     */
    protected function tabs(array $row, $language, array $languages)
    {
        $link = function($lang) use ($row) {
            return self::BASE . '&key=' . rawurlencode($row['key']) . ($lang !== '' ? '&lang=' . rawurlencode($lang) : '');
        };
        $html = '<div class="em-tabs"><a class="' . ($language === '' ? 'is-current' : '') . '" href="' . $link('') . '">' . $this->e($this->t('language_tab_default')) . '</a>';
        $free = [];
        foreach($languages as $name)
        {
            if(in_array($name, $row['languages'], true) || $name === $language)
            {
                $html .= '<a class="' . ($name === $language ? 'is-current' : '') . '" href="' . $link($name) . '">' . $this->e(ucfirst($name)) . '</a>';
            }
            else
            {
                $free[] = $name;
            }
        }
        if($free && EmailsController::can(EmailsController::EDIT))
        {
            $html .= '<select class="form-control" onchange="if(this.value){location.href=this.value}"><option value="">+ ' . $this->e($this->t('add_language')) . '</option>';
            foreach($free as $name)
            {
                $html .= '<option value="' . $this->e($link($name)) . '">' . $this->e(ucfirst($name)) . '</option>';
            }
            $html .= '</select>';
        }

        return $html . '</div>';
    }

    protected function fields(array $row)
    {
        $chip = function($name, $own) {
            return '<a href="#" class="em-chip' . ($own ? ' is-own' : '') . '" onclick="tssggsInsert(' . $this->e(json_encode('{$' . $name . '}')) . ');return false;">' . $this->e('{$' . $name . '}') . '</a>';
        };

        $html = '<div class="em-fields"><small>' . $this->e($this->t('fields_own')) . '</small>';
        foreach($row['fields'] as $name)
        {
            $html .= $chip($name, true);
        }
        $html .= '<small>' . $this->e($this->t('fields_standard')) . '</small>';
        foreach(array_diff(EmailCatalog::STANDARD_FIELDS, $row['fields']) as $name)
        {
            $html .= $chip($name, false);
        }

        return $html . '</div>';
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
