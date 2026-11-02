<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\CsrfToken;
use ModulesGarden\TSSGGSModule\App\Libs\EmailCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\EmailManager;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Emails\Index\EmailsContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Helper;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use WHMCS\User\Admin\Permission;

/**
 * Emails page: edit the module's e-mails per language, preview, test, reset, enable / disable.
 * Every action is POSTed with the admin token and needs the same WHMCS permission as the WHMCS e-mail template editor.
 */
class Emails extends AbstractController implements AdminAreaInterface
{
    const SESSION = 'tssggs_emails';

    const VIEW      = 'View Email Templates';
    const EDIT      = 'Create/Edit Email Templates';
    const LANGUAGES = 'Manage Email Template Languages';

    public function index()
    {
        return Helper\view()->addElement(EmailsContainer::class);
    }

    /** A posted field as typed: WHMCS HTML-encodes request values (< becomes &lt;), which would be saved into the template */
    protected static function posted($name, $default = "")
    {
        return html_entity_decode((string)($_POST[$name] ?? $default), ENT_QUOTES, "UTF-8");
    }

    public static function can($permission)
    {
        return Permission::currentAdminHasPermissionName($permission);
    }

    public function save()
    {
        return $this->run(function($key, $language) {
            $this->require(self::EDIT);
            $this->requireLanguagePermission($language);
            $this->keepDraft($key, $language);
            EmailManager::save($key, $language, self::posted('subject'), self::posted('message'), $language === '' ? EmailManager::options($_POST) : []);
            $this->forgetDraft();

            return 'saved';
        });
    }

    public function preview()
    {
        return $this->run(function($key, $language) {
            $this->require(self::VIEW);
            $this->keepDraft($key, $language);
            $rendered = EmailManager::render($key, $language, self::posted('subject'), self::posted('message'));
            $_SESSION[self::SESSION]['preview'] = ['subject' => $rendered['subject'], 'body' => $rendered['body']];

            return null;
        });
    }

    public function test()
    {
        return $this->run(function($key, $language) {
            $this->require(self::EDIT);
            $this->keepDraft($key, $language);
            $saved = EmailManager::load($key, $language);
            EmailManager::sendTest($key, $language, self::posted('subject', $saved['subject']), self::posted('message', $saved['message']));

            return 'test_sent';
        });
    }

    public function reset()
    {
        return $this->run(function($key, $language) {
            $this->require(self::EDIT);
            EmailManager::reset($key);
            $this->forgetDraft();

            return 'reset';
        });
    }

    public function removeLanguage()
    {
        return $this->run(function($key, $language) {
            $this->require(self::LANGUAGES);
            EmailManager::removeLanguage($key, $language);
            $this->forgetDraft();

            return 'language_removed';
        });
    }

    public function toggle()
    {
        return $this->run(function($key, $language) {
            $this->require(self::EDIT);
            EmailManager::setDisabled($key, !empty($_POST['disable']));

            return !empty($_POST['disable']) ? 'disabled' : 'enabled';
        });
    }

    /**
     * Token, valid template key / language, then the action; the page opens on the same template and language afterwards
     */
    protected function run(callable $action)
    {
        $key      = (string)($_POST['key'] ?? '');
        $language = strtolower(trim((string)($_POST['lang'] ?? '')));
        try
        {
            if(!CsrfToken::valid($_POST['token'] ?? ''))
            {
                throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
            }
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
            }
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        header('Location: addonmodules.php?module=TSSGGSModule&mg-page=emails' . ($key !== '' ? '&key=' . rawurlencode($key) : '') . ($language !== '' ? '&lang=' . rawurlencode($language) : ''));
        exit;
    }

    protected function require($permission)
    {
        if(!self::can($permission))
        {
            throw new \RuntimeException('Your administrator role does not have the WHMCS permission "' . $permission . '"');
        }
    }

    protected function requireLanguagePermission($language)
    {
        if($language !== '')
        {
            $this->require(self::LANGUAGES);
        }
    }

    /**
     * The text being edited survives a preview, a test mail or an error
     */
    protected function keepDraft($key, $language)
    {
        $_SESSION[self::SESSION]['draft'] = ['key' => $key, 'lang' => $language, 'subject' => self::posted('subject'), 'message' => self::posted('message')]
            + array_map('strval', array_intersect_key($_POST, array_flip(['fromname', 'fromemail', 'copyto', 'blind_copy_to', 'plaintext', 'disabled'])));
    }

    protected function forgetDraft()
    {
        unset($_SESSION[self::SESSION]['draft'], $_SESSION[self::SESSION]['preview']);
    }
}
