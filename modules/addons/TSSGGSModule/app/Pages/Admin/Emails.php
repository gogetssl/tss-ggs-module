<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Emails\EmailCatalog;
use TSSGGSModule\App\Libs\Emails\EmailManager;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use WHMCS\User\Admin\Permission;

/**
 * Admin page "Emails": the module's client e-mails by event, edited per language where WHMCS keeps them; preview,
 * test, reset, enable / disable. The text is edited with the TinyMCE that ships with WHMCS (resources/assets/js/
 * pages/emails.js). Every change is a form post and needs the same WHMCS permission as the WHMCS e-mail template
 * editor. Templates: resources/templates/admin/emails.
 */
class Emails extends Page
{
    const LANG       = 'admin.emails';
    const BODY_CLASS = 'tssggs-migration tssggs-emails';
    const SESSION    = 'tssggs_emails';

    const VIEW      = 'View Email Templates';
    const EDIT      = 'Create/Edit Email Templates';
    const LANGUAGES = 'Manage Email Template Languages';

    /** What changed, in the WHMCS activity log (WHMCS adds the administrator); a preview changes nothing */
    const LOGGED = [
        'saved'            => 'edited',
        'test_sent'        => 'test e-mail sent',
        'reset'            => 'reset to the default text',
        'language_removed' => 'language removed',
        'disabled'         => 'disabled',
        'enabled'          => 'enabled',
    ];

    const OPTION_FIELDS = ['fromname', 'fromemail', 'copyto', 'blind_copy_to', 'plaintext', 'disabled'];

    public static function can($permission)
    {
        return Permission::currentAdminHasPermissionName($permission);
    }

    public function index()
    {
        if(!self::can(self::VIEW))
        {
            return $this->view('admin/emails/index', ['denied' => $this->t('no_permission', ['permission' => self::VIEW])]);
        }

        $session = $_SESSION[self::SESSION] ?? [];
        unset($_SESSION[self::SESSION]['draft'], $_SESSION[self::SESSION]['notice'], $_SESSION[self::SESSION]['error'], $_SESSION[self::SESSION]['preview'], $_SESSION[self::SESSION]['count']);

        $overview  = EmailManager::overview();
        $key       = $this->request->string('key');
        $key       = isset($overview[$key]) ? $key : (isset($session['draft']['key'], $overview[$session['draft']['key']]) ? $session['draft']['key'] : array_key_first($overview));
        $languages = EmailManager::languages();
        $language  = strtolower($this->request->string('lang', (string)($session['draft']['lang'] ?? '')));
        $language  = in_array($language, $languages, true) ? $language : '';

        $row   = $overview[$key];
        $draft = isset($session['draft']) && $session['draft']['key'] === $key && $session['draft']['lang'] === $language ? $session['draft'] : null;
        $data  = EmailManager::load($key, $language);

        return $this->view('admin/emails/index', [
            'denied'      => '',
            'error'       => (string)($session['error'] ?? ''),
            'notice'      => !empty($session['notice']) ? $this->t('notice_' . $session['notice'], ['count' => (int)($session['count'] ?? 0)]) : '',
            'showAbout'   => !GeneralSettings::enabled('hideInfoMessages'),
            'groups'      => $this->groups($overview, $key),
            'key'         => $key,
            'slug'        => (string)$row['slug'],
            'whmcsName'   => (string)$row['name'],
            'isDisabled'  => (bool)$row['disabled'],
            'language'    => $language,
            'languageName' => ucfirst($language),
            'tabs'        => $this->tabs($row, $language, $languages),
            'own'         => (bool)$data['own'],
            'text'        => $draft ? $draft + $data : $data,
            'canEdit'     => self::can(self::EDIT),
            'ownFields'   => array_values($row['fields']),
            'moreFields'  => array_values(array_diff(EmailCatalog::STANDARD_FIELDS, $row['fields'])),
            'preview'     => !empty($session['preview']) ? $session['preview'] : null,
        ]);
    }

    public function postSave()
    {
        return $this->run(function($key, $language) {
            $this->need(self::EDIT);
            if($language !== '')
            {
                $this->need(self::LANGUAGES);
            }
            $this->keepDraft($key, $language);
            EmailManager::save($key, $language, $this->posted('subject'), $this->posted('message'), $language === '' ? EmailManager::options($_POST) : []);
            $this->forgetDraft();

            return 'saved';
        });
    }

    public function postPreview()
    {
        return $this->run(function($key, $language) {
            $this->need(self::VIEW);
            $this->keepDraft($key, $language);
            $rendered = EmailManager::render($key, $language, $this->posted('subject'), $this->posted('message'));
            $_SESSION[self::SESSION]['preview'] = ['subject' => $rendered['subject'], 'body' => $rendered['body']];

            return null;
        });
    }

    public function postTest()
    {
        return $this->run(function($key, $language) {
            $this->need(self::EDIT);
            $this->keepDraft($key, $language);
            $saved = EmailManager::load($key, $language);
            EmailManager::sendTest($key, $language, $this->posted('subject', $saved['subject']), $this->posted('message', $saved['message']));

            return 'test_sent';
        });
    }

    public function postReset()
    {
        return $this->run(function($key) {
            $this->need(self::EDIT);
            EmailManager::reset($key);
            $this->forgetDraft();

            return 'reset';
        });
    }

    public function postRemoveLanguage()
    {
        return $this->run(function($key, $language) {
            $this->need(self::LANGUAGES);
            EmailManager::removeLanguage($key, $language);
            $this->forgetDraft();

            return 'language_removed';
        });
    }

    public function postToggle()
    {
        return $this->run(function($key) {
            $this->need(self::EDIT);
            $disable = (bool)$this->request->input('disable');
            EmailManager::setDisabled($key, $disable);

            return $disable ? 'disabled' : 'enabled';
        });
    }

    /** Valid template key / language, then the action; the page opens on the same template and language afterwards */
    protected function run(callable $action): Response
    {
        $key      = $this->request->string('key');
        $language = strtolower($this->request->string('lang'));
        try
        {
            if($key !== '' && !EmailCatalog::find($key))
            {
                throw new \InvalidArgumentException('Unknown template');
            }
            if($language !== '' && !in_array($language, EmailManager::languages(), true))
            {
                throw new \InvalidArgumentException('Unknown language');
            }

            $notice = $action($key, $language);
            if($notice)
            {
                $_SESSION[self::SESSION]['notice'] = $notice;
                $this->logChange($notice, $key, $language);
            }
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        // Settings > Email notifications posts here too and goes back there
        if($this->request->string('return') === 'settings')
        {
            return Response::redirect(self::pageUrl('settings', 'index', ['settings-page' => 'emailSettings']));
        }

        return $this->redirect('index', ['key' => $key, 'lang' => $language]);
    }

    /** @return array[] group => rows of the list on the left */
    protected function groups(array $overview, string $current): array
    {
        $byGroup = [];
        foreach($overview as $row)
        {
            $byGroup[$row['group']][] = [
                'key'        => (string)$row['key'],
                'slug'       => (string)$row['slug'],
                'current'    => $row['key'] === $current,
                'disabled'   => (bool)$row['disabled'],
                'unused'     => empty($row['sent']),
                'customised' => $row['customised'] && !$row['disabled'],
            ];
        }
        $groups = [];
        foreach(EmailCatalog::GROUPS as $group)
        {
            if(!empty($byGroup[$group]))
            {
                $groups[$group] = $byGroup[$group];
            }
        }

        return $groups;
    }

    /** Default + the languages that have a text of their own as tabs; the rest in one "add language" list */
    protected function tabs(array $row, string $language, array $languages): array
    {
        $tabs = ['own' => [], 'free' => []];
        foreach($languages as $name)
        {
            $tab = ['name' => ucfirst($name), 'lang' => $name, 'current' => $name === $language];
            $tabs[in_array($name, $row['languages'], true) || $name === $language ? 'own' : 'free'][] = $tab;
        }

        return $tabs;
    }

    protected function logChange(string $notice, string $key, string $language): void
    {
        if(!isset(self::LOGGED[$notice]) || $key === '')
        {
            return;
        }
        $what = self::LOGGED[$notice];
        if($notice === 'saved' && $language === '')
        {
            // the default text's form also carries the on / off switch
            $what .= ' (' . ($this->request->input('disabled') ? 'disabled' : 'enabled') . ')';
        }
        \logActivity('E-mail template "' . EmailManager::name($key) . '"' . ($language !== '' ? ' [' . $language . ']' : '') . ' ' . $what);
    }

    protected function need(string $permission): void
    {
        if(!self::can($permission))
        {
            throw new \RuntimeException('Your administrator role does not have the WHMCS permission "' . $permission . '"');
        }
    }

    /** A posted field as typed: WHMCS HTML-encodes request values (< becomes &lt;), which would be saved into the template */
    protected function posted(string $name, string $default = ''): string
    {
        $value = $this->request->input($name, $default);

        return html_entity_decode(is_scalar($value) ? (string)$value : $default, ENT_QUOTES, 'UTF-8');
    }

    /** The text being edited survives a preview, a test mail or an error */
    protected function keepDraft(string $key, string $language): void
    {
        $draft = ['key' => $key, 'lang' => $language, 'subject' => $this->posted('subject'), 'message' => $this->posted('message')];
        foreach(self::OPTION_FIELDS as $field)
        {
            if($this->request->input($field) !== null)
            {
                $draft[$field] = $this->request->string($field);
            }
        }
        $_SESSION[self::SESSION]['draft'] = $draft;
    }

    protected function forgetDraft(): void
    {
        unset($_SESSION[self::SESSION]['draft'], $_SESSION[self::SESSION]['preview']);
    }
}
