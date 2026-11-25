<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Kernel\Response;
use ModulesGarden\TSSGGSModule\App\Kernel\Table;
use ModulesGarden\TSSGGSModule\App\Libs\Logs\LogBook;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\LogPolicy;

/**
 * Admin page "Logs": the technical log of the module - a card per type with its number of entries, then the list
 * with search (also by service: "#71", a domain, a client), the details of an entry, deleting, the CSV export and
 * what is recorded (Actions > Settings). Templates: resources/templates/admin/logs; the log itself: App\Libs\Logs\LogBook.
 */
class Logs extends Page
{
    const LANG = 'admin.logs';

    public function index()
    {
        $table = $this->table();
        if($table['isReload'])
        {
            return $this->fragment('admin/partials/table', ['table' => $table]);
        }
        $data = $this->listData($table);

        return $this->request->isHtmx()
            ? $this->fragment('admin/logs/list', $data)
            : $this->view('admin/logs/index', $data + ['showAbout' => !GeneralSettings::enabled('hideInfoMessages')]);
    }

    /** GET id: the "Details" dialog of an entry */
    public function details()
    {
        $details = LogBook::details($this->request->int('id'));

        return $this->fragment('admin/logs/details', ['json' => $details ? LogBook::highlight($details) : '', 'missing' => $details === null]);
    }

    /** POST id: deletes one entry */
    public function postDelete()
    {
        $this->attempt(function() {
            LogBook::delete([$this->request->int('id')]);
        }, $this->t('deleted'));

        return $this->changed();
    }

    /** POST ids[]: deletes the ticked entries */
    public function postDeleteMany()
    {
        $this->attempt(function() {
            LogBook::delete((array)$this->request->input('ids', []));
        }, $this->t('deletedMany'));

        return $this->changed();
    }

    /** POST: empties the log */
    public function postClean()
    {
        $this->attempt(function() {
            LogBook::clean();
        }, $this->t('cleaned'));

        return $this->changed();
    }

    /** GET: the "Delete Logs Manually" dialog (types, older than) */
    public function prune()
    {
        return $this->fragment('admin/logs/prune', ['types' => $this->typeNames(LogBook::usedTypes()), 'error' => '']);
    }

    public function postPrune()
    {
        $days = $this->request->string('delete_older_than');
        if($days !== '' && (!ctype_digit($days) || (int)$days > 9999))
        {
            return $this->fragment('admin/logs/prune', ['types' => $this->typeNames(LogBook::usedTypes()), 'error' => $this->t('wrongValues')])->intoDialog();
        }
        $this->attempt(function() use ($days) {
            LogBook::prune((array)$this->request->input('types', []), (int)$days);
        }, $this->t('deletedMany'));

        return $this->changed()->closesDialog();
    }

    /** GET: the "Edit Settings" dialog - what is recorded and for how long it is kept */
    public function settings()
    {
        return $this->fragment('admin/logs/settings', $this->settingsData(LogBook::settings(), ''));
    }

    public function postSettings()
    {
        $posted = $this->request->posted();
        try
        {
            LogBook::saveSettings($posted);
        }
        catch(\Throwable $e)
        {
            $error = $e instanceof \InvalidArgumentException ? $this->t('wrongValues') : $e->getMessage();
            $on    = function($name) use ($posted) {
                return ($posted[$name] ?? '') === 'on';
            };

            // the dialog again, with what was chosen
            return $this->fragment('admin/logs/settings', $this->settingsData(['logging' => $on('logging'), 'events' => $on('events'), 'auto_prune' => $on('auto_prune'), 'types' => (array)($posted['types'] ?? [])] + $posted + LogBook::settings(), $error))->intoDialog();
        }
        $this->flash($this->t('settingsSaved'));

        return $this->changed()->closesDialog();
    }

    /** GET: the "Export CSV" dialog (period, types) */
    public function export()
    {
        return $this->fragment('admin/logs/export', LogBook::period() + ['types' => $this->typeNames(LogBook::usedTypes())]);
    }

    /** GET from, to, types[]: the entries as a CSV file */
    public function download()
    {
        $csv = LogBook::csv($this->request->string('from'), $this->request->string('to'), (array)$this->request->input('types', []));
        if($csv === null)
        {
            $this->flash($this->t('exportEmpty'), 'bad');

            return $this->redirect();
        }

        return Response::download($csv, 'logs.csv');
    }

    /** POST: ends debug mode before its time runs out */
    public function postStopDebug()
    {
        $this->attempt(function() {
            LogBook::stopDebug();
        });

        return $this->changed();
    }

    /** After a change: the cards and the list again */
    protected function changed(): Response
    {
        return $this->request->isHtmx() ? $this->fragment('admin/logs/list', $this->listData($this->table())) : $this->redirect();
    }

    /** The type the list is narrowed to (the "Show" button of a card); '' = every type */
    protected function type(): string
    {
        return $this->request->oneOf('type', LogBook::LEVELS, '');
    }

    protected function table(): array
    {
        $type  = $this->type();
        $table = Table::make($this->request, 'logs')
            ->column('id', $this->t('col_id'), true, Table::SEARCH_INT)
            ->column('related_item', $this->t('col_related_item'))
            ->column('message', $this->t('col_message'), true, Table::SEARCH_TEXT)
            ->column('type', $this->t('col_type'), true, Table::SEARCH_TEXT)
            ->column('date', $this->t('col_date'), true, Table::SEARCH_TEXT)
            ->sortBy('id', 'desc')
            ->rowTemplate('admin/logs/row', true)->toolbar('admin/logs/toolbar')->selectable('admin/logs/mass')
            ->keep(['type' => $type])
            ->searchUsing([LogBook::class, 'search']);
        $reload = $table->isReload();
        $table  = $table->run(LogBook::query($type));

        $kinds = ['invoice' => $this->t('related_invoice'), 'ticket' => $this->t('related_ticket'), 'order' => $this->t('related_order')];
        foreach($table['rows'] as &$row)
        {
            $row = LogBook::present($row);
            if($row['related'] && isset($row['related']['kind']))
            {
                $row['related']['text'] .= ' ' . $kinds[$row['related']['kind']];
            }
        }

        return $table + ['isReload' => $reload];
    }

    /** @return array what the cards and the list need */
    protected function listData(array $table): array
    {
        $cards = [];
        foreach(LogBook::counts() as $type => $count)
        {
            $noteKey = static::LANG . '.' . $type . 'Note';
            $cards[] = [
                'type'   => $type,
                'count'  => $count,
                'title'  => $this->t($type . 'Title'),
                'note'   => $this->lang->has($noteKey) ? $this->lang->get($noteKey) : '',
                'icon'   => $type === 'total' ? 'layers' : (LogBook::TYPES[$type]['icon'] ?? 'information-outline'),
                // the colour of the "Show" button: as the old cards had it
                'button' => ['emergency' => 'danger', 'alert' => 'danger', 'critical' => 'danger', 'error' => 'danger', 'warning' => 'warning', 'notice' => 'info', 'info' => 'info'][$type] ?? '',
                'url'    => self::pageUrl('logs', 'index', $type === 'total' ? [] : ['type' => $type]),
            ];
        }

        $debug    = '';
        $services = LogPolicy::debugServices();
        if($services)
        {
            $until = LogPolicy::debugUntil();
            $debug = $this->t('debugText', ['services' => '#' . implode(', #', $services), 'until' => date(date('Y-m-d', $until) === date('Y-m-d') ? 'H:i' : 'Y-m-d H:i', $until)]);
        }

        return ['table' => $table, 'cards' => $cards, 'debug' => $debug, 'flash' => $this->takeFlash(), 'type' => $this->type()];
    }

    /** @return array type => its name, for the type dropdowns */
    protected function typeNames(array $types): array
    {
        $names = [];
        foreach($types as $type)
        {
            $names[$type] = $this->t('type_' . $type);
        }

        return $names;
    }

    protected function settingsData(array $values, string $error): array
    {
        $modes = $hours = [];
        foreach(LogPolicy::API_MODES as $mode)
        {
            $modes[$mode] = $this->lang->get('admin.log_policy.apiMode_' . $mode);
        }
        foreach(LogPolicy::DEBUG_HOURS as $hour)
        {
            $hours[(string)$hour] = $this->lang->get('admin.log_policy.hours_' . $hour);
        }

        return ['values' => $values, 'modes' => $modes, 'hours' => $hours, 'types' => $this->typeNames(LogBook::LEVELS), 'error' => $error];
    }
}
