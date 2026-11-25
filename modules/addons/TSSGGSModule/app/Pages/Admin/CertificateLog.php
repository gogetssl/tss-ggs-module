<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstall\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Html;
use WHMCS\Database\Capsule;

/**
 * Admin page "Certificate Log": the certificate events of all services (order, validation, reissue, installation),
 * with filters and clearing. Templates: resources/templates/admin/certificateLog.
 */
class CertificateLog extends Page
{
    const LANG       = 'admin.certificate_log';
    const BODY_CLASS = 'tssggs-migration tssggs-log';
    const PER_PAGE   = 50;
    const TYPES      = ['success', 'info', 'error'];

    /** The page; htmx (filters, pager) gets only the list */
    public function index()
    {
        $data = $this->listData();

        return $this->request->isHtmx()
            ? $this->fragment('admin/certificateLog/list', $data)
            : $this->view('admin/certificateLog/index', $data + ['showAbout' => !GeneralSettings::enabled('hideInfoMessages'), 'aboutOther' => $this->aboutOther()]);
    }

    /** POST older=<days>|all: deletes the events, then shows the list again */
    public function postClear()
    {
        $older = $this->request->string('older');
        // only "all" or a number of days: anything else deletes nothing
        if($older === 'all' || (ctype_digit($older) && (int)$older > 0))
        {
            $query = Capsule::table(AutoInstallQueue::logsTable());
            if($older !== 'all')
            {
                $query->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . (int)$older . ' days')));
            }
            $this->flash($this->t('cleared', ['count' => (int)$query->delete()]));
        }

        return $this->request->isHtmx() ? $this->fragment('admin/certificateLog/list', $this->listData()) : $this->redirect();
    }

    /** The note on what is not logged here, with the link to the Logs page (ready HTML) */
    protected function aboutOther(): string
    {
        $link = '<a href="' . Html::e(Page::pageUrl('logs')) . '">' . Html::e($this->t('logs_page')) . '</a>';

        return str_replace('[[logs]]', $link, Html::e($this->t('about_other', ['logs' => '[[logs]]'])));
    }

    protected function listData(): array
    {
        $filter = [
            'service' => max(0, $this->request->int('service')),
            'type'    => $this->request->oneOf('type', self::TYPES),
            'search'  => $this->request->string('search'),
        ];
        $page = max(1, $this->request->int('p', 1));

        $query = Capsule::table(AutoInstallQueue::logsTable() . ' as l')
            ->leftJoin('tblhosting as h', 'h.id', '=', 'l.service_id')
            ->leftJoin('tblclients as c', 'c.id', '=', 'l.client_id');
        if($filter['service'])
        {
            $query->where('l.service_id', $filter['service']);
        }
        if($filter['type'])
        {
            $query->where('l.type', $filter['type']);
        }
        if($filter['search'] !== '')
        {
            $search = $filter['search'];
            $query->where(function($q) use ($search) {
                $q->where('l.message', 'like', '%' . $search . '%')->orWhere('h.domain', 'like', '%' . $search . '%');
            });
        }
        $total = (clone $query)->count();
        $rows  = $query->orderBy('l.id', 'desc')->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE)
            ->get(['l.*', 'h.domain', 'c.firstname', 'c.lastname', 'c.companyname']);

        $events = [];
        foreach($rows as $row)
        {
            $client   = trim($row->firstname . ' ' . $row->lastname) . ($row->companyname ? ' (' . $row->companyname . ')' : '');
            $events[] = [
                'date'      => $row->created_at,
                'serviceId' => (int)$row->service_id,
                'clientId'  => (int)$row->client_id,
                'domain'    => (string)$row->domain,
                'client'    => $client !== '' ? $client : '#' . $row->client_id,
                'type'      => (string)$row->type,
                'message'   => (string)$row->message,
            ];
        }

        return [
            'events' => $events,
            'filter' => $filter,
            'types'  => self::TYPES,
            'pageNo' => $page,
            'pages'  => (int)ceil($total / self::PER_PAGE),
            'total'  => $total,
            'flash'  => $this->takeFlash(),
        ];
    }
}
