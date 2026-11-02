<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\CertificateLog\Index;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\App\Libs\AutoInstallQueue;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use WHMCS\Database\Capsule;

/**
 * Admin page "Certificate Log": filterable list of the certificate events (TSSGGSModule_AutoInstallLogs)
 */
class CertificateLogContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const PER_PAGE = 50;
    const BASE     = 'addonmodules.php?module=TSSGGSModule&mg-page=certificateLog';
    const COLORS   = ['success' => '#1f7a4d', 'error' => '#a8322d', 'info' => '#5b6673'];

    public function loadHtml(): void
    {
        $this->setCss('tssggs-migration tssggs-log');
        AutoInstallQueue::createTables();

        $service = (int)($_GET['service'] ?? 0);
        $type    = in_array($_GET['type'] ?? '', ['success', 'info', 'error'], true) ? $_GET['type'] : '';
        $search  = trim((string)($_GET['search'] ?? ''));
        $page    = max(1, (int)($_GET['p'] ?? 1));

        $query = Capsule::table(AutoInstallQueue::LOGS_TABLE . ' as l')
            ->leftJoin('tblhosting as h', 'h.id', '=', 'l.service_id')
            ->leftJoin('tblclients as c', 'c.id', '=', 'l.client_id');
        if($service)
        {
            $query->where('l.service_id', $service);
        }
        if($type)
        {
            $query->where('l.type', $type);
        }
        if($search !== '')
        {
            $query->where(function($q) use ($search) {
                $q->where('l.message', 'like', '%' . $search . '%')->orWhere('h.domain', 'like', '%' . $search . '%');
            });
        }
        $total = (clone $query)->count();
        $rows  = $query->orderBy('l.id', 'desc')->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE)
            ->get(['l.*', 'h.domain', 'c.firstname', 'c.lastname', 'c.companyname']);

        $html = '';
        if(isset($_SESSION['tssggs_log_notice']))
        {
            $html .= '<div class="tssggs-note is-ok">' . $this->e($this->t('cleared', ['count' => (int)$_SESSION['tssggs_log_notice']])) . '</div>';
            unset($_SESSION['tssggs_log_notice']);
        }

        $html .= $this->explanation() . $this->filters($service, $type, $search) . $this->table($rows) . $this->pager($page, $total, $service, $type, $search);

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);
        $this->addElement($text);
    }

    /** What the page is for and what the entries mean, then a separate note on what is not logged here */
    protected function explanation()
    {
        $item = function($key) {
            return '<li>' . $this->e($this->t($key)) . '</li>';
        };
        $logs = '<a href="addonmodules.php?module=TSSGGSModule&mg-page=logs">' . $this->e($this->t('logs_page')) . '</a>';

        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
            . '<h3>' . $this->e($this->t('about_title')) . '</h3>'
            . '<p>' . $this->e($this->t('about_intro')) . '</p>'
            . '<ul>' . $item('about_service') . $item('about_steps') . $item('about_types') . '</ul>'
            . '</div></div>'
            . '<div class="tssggs-note">' . $this->t('about_other', ['logs' => $logs]) . '</div>';
    }

    protected function filters($service, $type, $search)
    {
        $html = '<form method="get" action="addonmodules.php" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px;">'
            . '<input type="hidden" name="module" value="TSSGGSModule"><input type="hidden" name="mg-page" value="certificateLog">'
            . '<input class="form-control" style="width:130px" name="service" type="number" min="1" placeholder="' . $this->e($this->t('service_id')) . '" value="' . ($service ?: '') . '">'
            . '<select class="form-control" style="width:150px" name="type"><option value="">' . $this->e($this->t('all_types')) . '</option>';
        foreach(array_keys(self::COLORS) as $option)
        {
            $html .= '<option value="' . $option . '"' . ($type === $option ? ' selected' : '') . '>' . $this->e($this->t('type_' . $option)) . '</option>';
        }
        $html .= '</select><input class="form-control" style="width:260px" name="search" placeholder="' . $this->e($this->t('search')) . '" value="' . $this->e($search) . '">'
            . '<button class="lu-btn lu-btn--primary" type="submit">' . $this->e($this->t('filter')) . '</button><span style="flex:1"></span>';

        $token = function_exists('generate_token') ? \generate_token('plain') : '';
        foreach(['30', 'all'] as $older)
        {
            $html .= '<a class="lu-btn lu-btn--default" href="' . self::BASE . '&mg-action=clear&older=' . $older . '&token=' . $this->e($token) . '"'
                . ' onclick="return confirm(' . $this->e(json_encode($this->t('confirm_clear_' . $older))) . ')">' . $this->e($this->t('clear_' . $older)) . '</a>';
        }

        return $html . '</form>';
    }

    protected function table($rows)
    {
        $html = '<table class="table table-striped"><thead><tr><th style="width:150px">' . $this->e($this->t('date')) . '</th><th style="width:140px">' . $this->e($this->t('service')) . '</th><th style="width:200px">'
            . $this->e($this->t('client')) . '</th><th style="width:80px">' . $this->e($this->t('type')) . '</th><th>' . $this->e($this->t('message')) . '</th></tr></thead><tbody>';
        foreach($rows as $row)
        {
            $client = trim($row->firstname . ' ' . $row->lastname) . ($row->companyname ? ' (' . $row->companyname . ')' : '');
            $html  .= '<tr><td>' . $this->e($row->created_at) . '</td>'
                . '<td><a href="clientsservices.php?userid=' . (int)$row->client_id . '&id=' . (int)$row->service_id . '">#' . (int)$row->service_id . '</a>'
                . ($row->domain ? '<br><small>' . $this->e($row->domain) . '</small>' : '') . '</td>'
                . '<td><a href="clientssummary.php?userid=' . (int)$row->client_id . '">' . $this->e($client ?: '#' . $row->client_id) . '</a></td>'
                . '<td style="color:' . (self::COLORS[$row->type] ?? '#333') . ';font-weight:600">' . $this->e($this->t('type_' . $row->type)) . '</td>'
                . '<td>' . $this->e($row->message) . '</td></tr>';
        }
        if(!count($rows))
        {
            $html .= '<tr><td colspan="5" style="text-align:center">' . $this->e($this->t('empty')) . '</td></tr>';
        }

        return $html . '</tbody></table>';
    }

    protected function pager($page, $total, $service, $type, $search)
    {
        $pages = (int)ceil($total / self::PER_PAGE);
        if($pages <= 1)
        {
            return '';
        }

        $query = http_build_query(array_filter(['service' => $service ?: null, 'type' => $type ?: null, 'search' => $search !== '' ? $search : null]));
        $html  = '<div style="display:flex;gap:6px;align-items:center;">' . $this->e($this->t('page', ['page' => $page, 'pages' => $pages, 'total' => $total]));
        if($page > 1)
        {
            $html .= ' <a class="lu-btn lu-btn--default" href="' . self::BASE . '&' . $query . '&p=' . ($page - 1) . '">&laquo;</a>';
        }
        if($page < $pages)
        {
            $html .= ' <a class="lu-btn lu-btn--default" href="' . self::BASE . '&' . $query . '&p=' . ($page + 1) . '">&raquo;</a>';
        }

        return $html . '</div>';
    }

    protected function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    }

    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.certificate_log']);
    }
}
