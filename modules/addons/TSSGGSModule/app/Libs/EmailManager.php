<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use WHMCS\Database\Capsule;
use WHMCS\Mail\Emailer;
use WHMCS\Mail\Message;
use WHMCS\Mail\Template;

/**
 * Edits the module's e-mails where WHMCS keeps them (tblemailtemplates, one row per language, language '' = default)
 * so sending, e-mail history and hooks stay WHMCS's own. Rendering and test mails use WHMCS's own e-mail engine.
 */
class EmailManager
{
    const TABLE = 'tblemailtemplates';

    /**
     * Creates missing templates and renames the ones of another edition / of 1.2.x (what the first send would do)
     */
    public static function ensureAll()
    {
        foreach(EmailCatalog::all() as $key => $definition)
        {
            EmailTemplates::resolveTemplate($key, $definition['subject'], $definition['message']);
        }
    }

    public static function name($key)
    {
        return Edition::templateName($key);
    }

    /**
     * Per template: the default row, the languages that have their own text, customised / disabled state
     */
    public static function overview()
    {
        self::ensureAll();

        $result = [];
        foreach(EmailCatalog::all() as $key => $definition)
        {
            $rows = Capsule::table(self::TABLE)->where('name', self::name($key))->get(['id', 'language', 'subject', 'message', 'disabled']);
            $default   = null;
            $languages = [];
            foreach($rows as $row)
            {
                if((string)$row->language === '')
                {
                    $default = $row;
                }
                else
                {
                    $languages[] = strtolower($row->language);
                }
            }

            $result[$key] = $definition + [
                'name'       => self::name($key),
                'row'        => $default,
                'languages'  => $languages,
                'customised' => $default && (self::normalize($default->subject) !== self::normalize($definition['subject']) || self::normalize($default->message) !== self::normalize($definition['message'])),
                'disabled'   => $default && (int)$default->disabled === 1,
            ];
        }

        return $result;
    }

    /**
     * Language names WHMCS ships (lang/*.php) plus the ones that already have a text
     */
    public static function languages()
    {
        $names = [];
        foreach((array)glob(ROOTDIR . '/lang/*.php') as $file)
        {
            $name = strtolower(basename($file, '.php'));
            if($name !== 'index')
            {
                $names[$name] = $name;
            }
        }
        foreach(Capsule::table(self::TABLE)->where('name', 'like', '% - %')->where('language', '!=', '')->distinct()->pluck('language') as $language)
        {
            $names[strtolower($language)] = strtolower($language);
        }
        ksort($names);

        return array_values($names);
    }

    /**
     * Subject and text for the editor. A language without its own text starts from the default text.
     */
    public static function load($key, $language)
    {
        $name = self::name($key);
        $row  = Capsule::table(self::TABLE)->where('name', $name)->where('language', (string)$language)->first();
        $own  = (bool)$row;
        if(!$row)
        {
            $row = Capsule::table(self::TABLE)->where('name', $name)->where('language', '')->first();
        }

        // sender, copies and plain-text belong to the default text (WHMCS ignores them on language versions)
        $default = Capsule::table(self::TABLE)->where('name', $name)->where('language', '')->first();

        return [
            'subject'       => $row ? (string)$row->subject : '',
            'message'       => $row ? (string)$row->message : '',
            'own'           => $own,
            'disabled'      => $row && (int)$row->disabled === 1,
            'fromname'      => $default ? (string)$default->fromname : '',
            'fromemail'     => $default ? (string)$default->fromemail : '',
            'copyto'        => $default ? (string)$default->copyto : '',
            'blind_copy_to' => $default ? (string)$default->blind_copy_to : '',
            'plaintext'     => $default && (int)$default->plaintext === 1,
        ];
    }

    /**
     * Sender / copy / plain-text / disabled options from a form: addresses are checked, lists are comma separated
     */
    public static function options(array $input)
    {
        $emails = function($value) {
            $list = array_filter(array_map('trim', explode(',', (string)$value)), 'strlen');
            foreach($list as $address)
            {
                if(!filter_var($address, FILTER_VALIDATE_EMAIL))
                {
                    throw new \InvalidArgumentException('Invalid e-mail address: ' . $address);
                }
            }

            return implode(',', $list);
        };

        return [
            'fromname'      => trim((string)($input['fromname'] ?? '')),
            'fromemail'     => $emails($input['fromemail'] ?? ''),
            'copyto'        => $emails($input['copyto'] ?? ''),
            'blind_copy_to' => $emails($input['blind_copy_to'] ?? ''),
            'plaintext'     => !empty($input['plaintext']) ? 1 : 0,
            'disabled'      => !empty($input['disabled']) ? 1 : 0,
        ];
    }

    /**
     * Saves after a test render (a Smarty error is refused, so no broken e-mail ever reaches a client)
     */
    public static function save($key, $language, $subject, $message, array $options = [])
    {
        $subject = trim((string)$subject);
        $message = (string)$message;
        if($subject === '' || trim(strip_tags($message)) === '')
        {
            throw new \InvalidArgumentException('The subject and the text can not be empty');
        }
        self::render($key, $language, $subject, $message);

        $name = self::name($key);
        $now  = date('Y-m-d H:i:s');
        if((string)$language === '')
        {
            Capsule::table(self::TABLE)->where('name', $name)->where('language', '')->update(['subject' => $subject, 'message' => $message, 'updated_at' => $now] + $options);

            return;
        }

        $default = Capsule::table(self::TABLE)->where('name', $name)->where('language', '')->first();
        Capsule::table(self::TABLE)->updateOrInsert(
            ['name' => $name, 'language' => (string)$language],
            ['type' => $default ? $default->type : 'product', 'subject' => $subject, 'message' => $message, 'disabled' => $default ? $default->disabled : 0,
             'custom' => 1, 'plaintext' => 0, 'attachments' => '', 'fromname' => '', 'fromemail' => '', 'copyto' => '', 'blind_copy_to' => '', 'updated_at' => $now, 'created_at' => $now]
        );
    }

    public static function reset($key)
    {
        $definition = EmailCatalog::find($key);
        if(!$definition)
        {
            throw new \InvalidArgumentException('Unknown template');
        }
        self::ensureAll();
        Capsule::table(self::TABLE)->where('name', self::name($key))->where('language', '')
            ->update(['subject' => $definition['subject'], 'message' => $definition['message'], 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public static function removeLanguage($key, $language)
    {
        if((string)$language === '')
        {
            throw new \InvalidArgumentException('The default text can not be removed - reset it instead');
        }

        return Capsule::table(self::TABLE)->where('name', self::name($key))->where('language', (string)$language)->delete();
    }

    /**
     * A disabled WHMCS template is not sent - applies to every language of it
     */
    public static function setDisabled($key, $disabled)
    {
        Capsule::table(self::TABLE)->where('name', self::name($key))->update(['disabled' => $disabled ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // ---- rendering ---- //

    /**
     * Service used as sample data (client name, product, domain): the newest service of the module
     */
    public static function sampleServiceId()
    {
        return (int)Capsule::table('tblhosting')->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', 'TSSGGSModule')->orderBy('tblhosting.id', 'desc')->value('tblhosting.id');
    }

    /**
     * Renders with WHMCS's e-mail engine: ['subject' => .., 'body' => full HTML]
     * @throws \Exception on a Smarty error or when there is no service to take sample data from
     */
    public static function render($key, $language, $subject, $message)
    {
        $serviceId = self::sampleServiceId();
        if(!$serviceId)
        {
            throw new \RuntimeException('Preview needs one service of this module to take sample client data from');
        }

        $template = Template::factoryFromArray(['type' => 'product', 'name' => self::name($key), 'subject' => $subject, 'message' => $message, 'disabled' => 0, 'plaintext' => 0, 'language' => (string)$language]);
        $emailer  = Emailer::factory(Message::createFromTemplate($template), $serviceId, EmailCatalog::sample($key, $serviceId));
        $rendered = $emailer->preview();

        return ['subject' => $rendered->getSubject(), 'body' => $rendered->getBody()];
    }

    /**
     * Sends the rendered text to the administrators (system e-mails) - never to the sample client
     */
    public static function sendTest($key, $language, $subject, $message)
    {
        $rendered = self::render($key, $language, $subject, $message);
        $result   = localAPI('SendAdminEmail', [
            'customsubject' => '[TEST] ' . $rendered['subject'],
            'custommessage' => '<p><em>Test of the e-mail "' . htmlspecialchars(EmailCatalog::slug($key)) . '" (' . ($language === '' ? 'default text' : htmlspecialchars($language)) . '), sample data.</em></p>'
                . self::bodyOnly($rendered),
            'type'          => 'system',
        ]);
        if(($result['result'] ?? '') !== 'success')
        {
            throw new \RuntimeException($result['message'] ?? 'WHMCS could not send the test e-mail');
        }
    }

    protected static function bodyOnly(array $rendered)
    {
        // the admin e-mail gets WHMCS's own wrapper; send the rendered content of the template without the client wrapper
        $body = $rendered['body'];
        if(preg_match('/<body[^>]*>(.*)<\/body>/is', $body, $match))
        {
            $body = $match[1];
        }

        return $body;
    }

    protected static function normalize($text)
    {
        return trim(preg_replace('/\s+/', ' ', (string)$text));
    }
}
