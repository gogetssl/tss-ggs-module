<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Certificates\Concerns;

use ModulesGarden\TSSGGSModule\App\Helpers\EmailTemplates;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Support\ClientText;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateBundle;

/**
 * Certificate files: the previous certificate kept during a reissue, the downloadable bundle, the certificate e-mail.
 * Part of CertificateService (used there as a trait).
 */
trait CertificateFiles
{
    /**
     * Before a renewal replaces the stored order: keep a copy of the issued certificate, which stays valid until
     * the renewal is issued (removed again by forgetPreviousCertificate once that happens)
     */
    public static function keepPreviousCertificate($serviceId)
    {
        $row = Request::where('serviceid', (int)$serviceId)->where('name', 'certificate')->first();
        if(!$row || $row->status !== 'active' || empty($row->request))
        {
            return;
        }
        Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->delete();
        Request::insert([
            'serviceid'  => (int)$serviceId,
            'name'       => 'previousCertificate',
            'request'    => $row->request,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function forgetPreviousCertificate($serviceId)
    {
        Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->delete();
    }

    /** The kept certificate while it is still valid, else [] */
    public static function previousCertificate($serviceId)
    {
        $row    = Request::where('serviceid', (int)$serviceId)->where('name', 'previousCertificate')->first();
        $stored = $row && $row->request ? (json_decode(\decrypt($row->request), true) ?: []) : [];
        $end    = $stored['orderFiles']['validity']['end'] ?? '';

        return $end !== '' && strtotime($end) > time() ? $stored : [];
    }

    /**
     * Certificate the client may download: the issued one, or during a renewal the previous one that is still valid
     */
    public static function downloadableBundle($serviceId)
    {
        if(!self::serviceOpen($serviceId))
        {
            throw new \Exception(ClientText::get('certificateNotAvailable', 'The certificate is not available: the service has been cancelled or terminated.'));
        }
        if(self::status($serviceId) === 'active')
        {
            return self::bundle($serviceId);
        }
        $previous = self::previousCertificate($serviceId);
        if($previous)
        {
            return CertificateBundle::fromOrder($previous);
        }

        throw new \Exception(ClientText::get('notIssuedYet', 'The certificate has not been issued yet'));
    }

    /**
     * E-mails the certificate to the client; the template can be chosen on the settings page
     */
    public static function sendCertificate($serviceId)
    {
        self::requireUsable($serviceId);
        $stored = self::stored($serviceId);
        $bundle = CertificateBundle::fromOrder($stored);
        $cn     = $stored['orderData']['orderData']['common_name']['name'] ?? '';

        $files = [];
        $dir   = self::attachmentsDir();
        foreach(['crt' => $bundle->crt(), 'ca-bundle' => $bundle->ca()] as $ext => $content)
        {
            if(trim($content) === '')
            {
                continue;
            }
            $name = (int)$serviceId . '_' . CertificateBundle::fileName($cn) . '.' . $ext;
            if($dir !== null)
            {
                file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $content);
                $files[] = ['displayname' => CertificateBundle::fileName($cn) . '.' . $ext, 'filename' => $name];
            }
            else
            {
                $path = tempnam(sys_get_temp_dir(), 'tssggs');
                file_put_contents($path, $content);
                $files[] = ['displayname' => CertificateBundle::fileName($cn) . '.' . $ext, 'path' => $path];
            }
        }

        $fields = [
            'domain'          => $cn,
            'ssl_certyficate' => nl2br($bundle->ca()),
            'crt_code'        => nl2br($bundle->crt()),
        ];

        $template = self::certificateTemplate();
        try
        {
            if($template !== '')
            {
                \sendMessage($template, (int)$serviceId, $fields, false, $files);
            }
            else
            {
                EmailTemplates::sendEmail(EmailTemplates::SEND_CERTIFICATE_TEMPLATE, (int)$serviceId, $fields, $files);
            }
        }
        finally
        {
            // the attachments are read while the e-mail is sent: the copies (temp folder or the WHMCS attachments
            // folder) are not kept - before, one pair per certificate e-mail stayed in the attachments folder
            foreach($files as $file)
            {
                if(isset($file['path']))
                {
                    @unlink($file['path']);
                }
                elseif($dir !== null && !empty($file['filename']) && basename($file['filename']) === $file['filename'])
                {
                    @unlink($dir . DIRECTORY_SEPARATOR . $file['filename']);
                }
            }
        }

        self::event($serviceId, 'info', 'Certificate sent to the client by email');
    }

    /**
     * Templates the certificate e-mail can use besides the module's own "Send Certificate": Product/Service templates
     * (sendMessage() sends them to the service's client) that an administrator created, without the module's own ones
     * (a choice saved before, e.g. migrated from the earlier module, stays selectable - see the settings providers).
     * A General template is never offered: WHMCS addresses it to a client ID, and a service ID there reaches another
     * client.
     *
     * @return string[] template names
     */
    public static function certificateTemplates()
    {
        // without the templates of this module under any of its names (both editions, the earlier names) and of the
        // legacy GoGetSSL module ("SSLCenter - ...", imported by App\Libs\Emails\LegacyEmailImport)
        $modules = array_map(function($module) { return $module . ' - '; }, array_merge(Edition::knownNames(), ['SSLCenter']));
        $names   = [];
        foreach(Capsule::table('tblemailtemplates')->where('type', 'product')->where('language', '')->where('custom', 1)->orderBy('name')->pluck('name') as $name)
        {
            foreach($modules as $prefix)
            {
                if(strpos((string)$name, $prefix) === 0)
                {
                    continue 2;
                }
            }
            $names[] = (string)$name;
        }

        return $names;
    }

    /**
     * The chosen certificate e-mail template when it is a Product/Service template that exists, '' (the module's own
     * template) otherwise - also for a General template chosen before 1.19.0
     */
    public static function certificateTemplate()
    {
        $template = trim((string)GeneralSettings::get('certificateEmailTemplate'));
        if($template === '')
        {
            return '';
        }
        $type = Capsule::table('tblemailtemplates')->where('name', $template)->where('language', '')->value('type');
        if($type !== 'product')
        {
            GeneralSettings::activity('Certificate e-mail template "' . $template . '" is not a Product/Service template - the module template is used');

            return '';
        }

        return $template;
    }

    /**
     * "Certificate issued" e-mail: sent by whichever sync first sees the order go from waiting (pending / processing)
     * to issued - the processing cron, the status-update cron, admin "Sync Certificate" or the client page refresh.
     * No previous status (an order migrated or imported as already issued) sends nothing.
     */
    public static function notifyIssued($serviceId, $previousStatus, $newStatus)
    {
        if($newStatus !== 'active' || !in_array((string)$previousStatus, ['pending', 'processing'], true))
        {
            return false;
        }

        try
        {
            self::sendCertificate($serviceId);

            return true;
        }
        catch(\Throwable $e)
        {
            self::event($serviceId, 'error', 'Certificate issued, but the certificate e-mail could not be sent: ' . $e->getMessage());

            return false;
        }
    }
}
