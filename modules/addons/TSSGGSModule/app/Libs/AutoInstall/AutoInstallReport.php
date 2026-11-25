<?php

namespace TSSGGSModule\App\Libs\AutoInstall;

use WHMCS\Database\Capsule;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Support\GeneralSettings;

/**
 * AutoInstall report for the administrators (M-21): what AutoInstall did without anyone watching - new orders,
 * renewals and automatic reissues installed on cPanel, the ones that failed, and the ones still waiting after
 * LATE_DAYS. One row per job; a daily e-mail (WHMCS daily cron) lists what is new since the last one. Nothing new, no
 * e-mail. Kept apart from the Certificate Log, which can be switched off.
 */
class AutoInstallReport
{
    const KIND_ORDER   = 'order';
    const KIND_RENEWAL = 'renewal';
    const KIND_REISSUE = 'reissue';

    const PENDING   = 'pending';
    const INSTALLED = 'installed';
    const FAILED    = 'failed';
    const STOPPED   = 'stopped';

    /** a job still waiting after this many days is reported (once) */
    const LATE_DAYS = 3;
    const KEEP_DAYS = 90;
    /** rows per section in the e-mail */
    const MAX_ROWS  = 100;


    public static function table()
    {
        return Edition::table('AutoInstallReport');
    }

    /** A job starts: an earlier unfinished job of the service is closed (replaced) */
    public static function start($serviceId, $kind, $domain)
    {
        try
        {
            Capsule::table(self::table())->where('service_id', (int)$serviceId)->where('status', self::PENDING)
                ->update(['status' => self::STOPPED, 'finished_at' => date('Y-m-d H:i:s'), 'detail' => 'Replaced by a new job']);
            Capsule::table(self::table())->insert([
                'service_id' => (int)$serviceId,
                'client_id'  => (int)Capsule::table('tblhosting')->where('id', (int)$serviceId)->value('userid'),
                'domain'     => (string)$domain,
                'kind'       => $kind,
                'status'     => self::PENDING,
                'started_at' => date('Y-m-d H:i:s'),
            ]);
        }
        catch(\Throwable $e)
        {
            // the report never stops AutoInstall itself
        }
    }

    /**
     * The job of the service ended: installed, failed (with the reason) or stopped (AutoInstall turned off).
     * Called where AutoInstall saves its status (AutoInstallHelpers::save) and for a reissue that gave up.
     */
    public static function finish($serviceId, $status, $detail = '')
    {
        try
        {
            Capsule::table(self::table())->where('service_id', (int)$serviceId)->where('status', self::PENDING)
                ->update(['status' => $status, 'finished_at' => date('Y-m-d H:i:s'), 'detail' => (string)$detail]);
        }
        catch(\Throwable $e)
        {
        }
    }

    /**
     * Daily: e-mails what is new since the last e-mail (finished jobs, and jobs waiting over LATE_DAYS)
     * @return int jobs reported
     */
    public static function notify()
    {
        Capsule::table(self::table())->where('started_at', '<', date('Y-m-d H:i:s', strtotime('-' . self::KEEP_DAYS . ' days')))->delete();

        $finished = Capsule::table(self::table())->whereIn('status', [self::INSTALLED, self::FAILED])->whereNull('emailed_at')->orderBy('id')->get()->all();
        $late     = Capsule::table(self::table())->where('status', self::PENDING)->whereNull('late_at')
            ->where('started_at', '<', date('Y-m-d H:i:s', strtotime('-' . self::LATE_DAYS . ' days')))->orderBy('id')->get()->all();
        // the jobs stopped by a new configuration or a manual reissue are not reported
        Capsule::table(self::table())->where('status', self::STOPPED)->whereNull('emailed_at')->update(['emailed_at' => date('Y-m-d H:i:s')]);
        if(!$finished && !$late)
        {
            return 0;
        }

        $sections = [
            self::FAILED    => array_values(array_filter($finished, function($r) { return $r->status === self::FAILED; })),
            'late'          => $late,
            self::INSTALLED => array_values(array_filter($finished, function($r) { return $r->status === self::INSTALLED; })),
        ];
        $summary = self::summary($sections);
        $mark    = function() use ($finished, $late) {
            $now = date('Y-m-d H:i:s');
            if($finished)
            {
                Capsule::table(self::table())->whereIn('id', array_map(function($r) { return $r->id; }, $finished))->update(['emailed_at' => $now]);
            }
            if($late)
            {
                Capsule::table(self::table())->whereIn('id', array_map(function($r) { return $r->id; }, $late))->update(['late_at' => $now]);
            }
        };

        if(!GeneralSettings::enabled('autoInstallReportEmail'))
        {
            $mark();

            return 0;
        }
        $result = localAPI('SendAdminEmail', [
            'customsubject' => Edition::name() . ': AutoInstall report - ' . $summary,
            'custommessage' => self::emailHtml($sections),
            'type'          => 'system',
        ]);
        if(($result['result'] ?? '') !== 'success')
        {
            GeneralSettings::activity('AutoInstall report e-mail to the administrators failed: ' . ($result['message'] ?? 'unknown error'));

            return 0; // tried again with the next daily run
        }
        $mark();
        GeneralSettings::activity('AutoInstall report e-mailed: ' . $summary);

        return count($finished) + count($late);
    }

    protected static function summary(array $sections)
    {
        $parts = [];
        foreach([self::INSTALLED => 'installed', self::FAILED => 'failed', 'late' => 'waiting over ' . self::LATE_DAYS . ' days'] as $key => $label)
        {
            if($sections[$key])
            {
                $parts[] = count($sections[$key]) . ' ' . $label;
            }
        }

        return implode(', ', $parts);
    }

    protected static function emailHtml(array $sections)
    {
        $h     = function($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
        $kinds = [self::KIND_ORDER => 'New order', self::KIND_RENEWAL => 'Renewal', self::KIND_REISSUE => 'Automatic reissue'];
        $admin = self::adminUrl();
        $titles = [
            self::FAILED    => ['Failed - install the certificate manually', 'What went wrong'],
            'late'          => ['Waiting for more than ' . self::LATE_DAYS . ' days - the domain is probably not validated yet', 'Waiting since'],
            self::INSTALLED => ['Installed on cPanel', 'Installed'],
        ];
        $html = '<p>What AutoInstall did since the last report: new certificates, renewals and automatic reissues on the clients\' cPanel accounts.</p>';
        foreach($titles as $key => list($title, $column))
        {
            if(!$sections[$key])
            {
                continue;
            }
            $html .= '<h3>' . $h($title) . ' (' . count($sections[$key]) . ')</h3><table border="1" cellpadding="4" cellspacing="0">'
                . '<tr><th>Domain</th><th>Service</th><th>Job</th><th>Started</th><th>' . $h($column) . '</th></tr>';
            foreach(array_slice($sections[$key], 0, self::MAX_ROWS) as $row)
            {
                $link = $admin . 'clientsservices.php?userid=' . (int)$row->client_id . '&id=' . (int)$row->service_id;
                $last = $key === self::FAILED ? $row->detail : ($key === 'late' ? $row->started_at : $row->finished_at);
                $html .= '<tr><td>' . $h($row->domain) . '</td><td><a href="' . $h($link) . '">#' . (int)$row->service_id . '</a></td><td>'
                    . $h($kinds[$row->kind] ?? $row->kind) . '</td><td>' . $h(substr((string)$row->started_at, 0, 16)) . '</td><td>' . $h($last) . '</td></tr>';
            }
            $html .= '</table>' . (count($sections[$key]) > self::MAX_ROWS ? '<p>... and ' . (count($sections[$key]) - self::MAX_ROWS) . ' more.</p>' : '');
        }
        $html .= '<p>The details of each service are in the module\'s Certificate Log. This e-mail can be turned off in Settings &gt; Automated SSL &gt; AutoInstall (cPanel).</p>';

        return $html;
    }

    /** https://example.com/admin/ - the WHMCS admin area, for the links in the e-mail */
    protected static function adminUrl()
    {
        $system = rtrim((string)\WHMCS\Config\Setting::getValue('SystemURL'), '/');
        $folder = 'admin';
        try
        {
            if(class_exists('\App') && method_exists('\App', 'get_admin_folder_name'))
            {
                $folder = (string)\App::get_admin_folder_name();
            }
        }
        catch(\Throwable $e)
        {
        }

        return $system . '/' . trim($folder, '/') . '/';
    }
}
