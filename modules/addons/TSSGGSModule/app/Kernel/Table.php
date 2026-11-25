<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * A list with search, sorting and paging (template admin/partials/table.tpl).
 *
 *   $table = Table::make($this->request, 'queue')
 *       ->column('id', $this->t('id'), true, Table::SEARCH_INT)      // name, title, sortable, searched as
 *       ->column('domain', $this->t('domain'), true, Table::SEARCH_TEXT)
 *       ->sortBy('id', 'desc')
 *       ->rowTemplate('admin/automationQueue/row')
 *       ->run($query);                                               // an Illuminate query builder
 *
 * run() reads the rows of a database query; rows() takes rows that are already there (arrays), e.g. a report.
 * The result goes to the view as it is ({include file='admin/partials/table.tpl' table=$table}); the page may change
 * $table['rows'] before that. The table reloads itself through htmx: the request then carries table=<id>
 * (isReload()), and the action answers with the fragment admin/partials/table.
 * Request values: tq (search), ts / td (sort column / direction), tp (page), tl (rows per page).
 * keep() names further request values the table sends along when it reloads (the filters of the page).
 */
class Table
{
    const SEARCH_TEXT = 'text';
    const SEARCH_INT  = 'int';
    const ALL         = 99999999;

    protected $request;
    protected $id;
    protected $columns     = [];
    protected $sort        = '';
    protected $dir         = 'asc';
    protected $lengths     = [10, 25, 50];
    protected $rowTemplate = '';
    protected $actions     = false;
    protected $keep        = [];
    protected $toolbar     = '';
    protected $mass        = null;
    protected $searcher    = null;

    public static function make(Request $request, string $id): self
    {
        $table          = new self();
        $table->request = $request;
        $table->id      = $id;

        return $table;
    }

    /**
     * @param string $name   column of the query / key of the row
     * @param string $search '' (not searched) | SEARCH_TEXT (contains) | SEARCH_INT (equal, when the search is a number)
     */
    public function column(string $name, string $title, bool $sortable = false, string $search = ''): self
    {
        $this->columns[$name] = ['name' => $name, 'title' => $title, 'sortable' => $sortable, 'search' => $search];

        return $this;
    }

    public function sortBy(string $column, string $direction = 'asc'): self
    {
        $this->sort = $column;
        $this->dir  = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    /** @param int[] $lengths choices of rows per page; the first is the default */
    public function perPage(array $lengths): self
    {
        $this->lengths = array_values(array_map('intval', $lengths));

        return $this;
    }

    /** @param string $template prints the <td> cells of one row ($row); $actions: the rows end with a cell of buttons */
    public function rowTemplate(string $template, bool $actions = false): self
    {
        $this->rowTemplate = $template;
        $this->actions     = $actions;

        return $this;
    }

    /** @param string $template buttons shown on the right of the search (the template sees $table) */
    public function toolbar(string $template): self
    {
        $this->toolbar = $template;

        return $this;
    }

    /**
     * Rows can be ticked: the title of the first column gets a "tick all" box, and while rows are ticked a bar
     * with their number and the buttons of $template is shown. The row template prints the row's own box in its
     * first cell: <input type="checkbox" name="ids[]" value="..." data-row-check> (see admin/products/row.tpl).
     */
    public function selectable(string $template): self
    {
        $this->mass = $template;

        return $this;
    }

    /** @param array $values name => value the table sends along when it reloads (filters, the report's name) */
    public function keep(array $values): self
    {
        $this->keep = $values + $this->keep;

        return $this;
    }

    /**
     * A search of the page's own for run(): $searcher($query, $search, $columns) adds the conditions to $query.
     * $columns($q) adds the usual search in the searched columns to a (sub)query - for a page that searches there too.
     */
    public function searchUsing(callable $searcher): self
    {
        $this->searcher = $searcher;

        return $this;
    }

    /** This request is the table reloading itself (search, sort, page) */
    public function isReload(): bool
    {
        return $this->request->isHtmx() && $this->request->string('table') === $this->id;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return array the table for the template
     */
    public function run($query): array
    {
        $state = $this->state();

        if($state['search'] !== '')
        {
            $columns = $this->columns;
            $search  = $state['search'];
            $usual   = function($q) use ($columns, $search) {
                foreach($columns as $column)
                {
                    if($column['search'] === self::SEARCH_TEXT)
                    {
                        $q->orWhere($column['name'], 'like', '%' . $search . '%');
                    }
                    elseif($column['search'] === self::SEARCH_INT && ctype_digit($search))
                    {
                        $q->orWhere($column['name'], '=', (int)$search);
                    }
                }
            };
            if($this->searcher)
            {
                ($this->searcher)($query, $search, $usual);
            }
            else
            {
                $query->where($usual);
            }
        }

        $total = (clone $query)->count();
        $pages = max(1, (int)ceil($total / $state['length']));
        $page  = min($pages, max(1, $state['page']));
        if($state['sort'] !== '')
        {
            $query->orderBy($state['sort'], $state['dir']);
        }
        $rows = [];
        foreach($query->offset(($page - 1) * $state['length'])->limit($state['length'])->get() as $row)
        {
            $rows[] = (array)$row;
        }

        return $this->result($state, $rows, $total, $page, $pages);
    }

    /**
     * @param array[] $rows all the rows (arrays keyed by column name); values may be HTML - the search looks at the text
     * @return array the table for the template
     */
    public function rows(array $rows): array
    {
        $state = $this->state();

        if($state['search'] !== '')
        {
            $searched = array_keys(array_filter($this->columns, function($column) {
                return $column['search'] !== '';
            }));
            $search   = $state['search'];
            $rows     = array_values(array_filter($rows, function($row) use ($searched, $search) {
                foreach($searched as $name)
                {
                    if(stripos(strip_tags((string)($row[$name] ?? '')), $search) !== false)
                    {
                        return true;
                    }
                }

                return false;
            }));
        }

        if($state['sort'] !== '')
        {
            $name = $state['sort'];
            $sign = $state['dir'] === 'desc' ? -1 : 1;
            usort($rows, function($a, $b) use ($name, $sign) {
                $x = $a[$name] ?? '';
                $y = $b[$name] ?? '';

                return $sign * (is_numeric($x) && is_numeric($y) ? $x <=> $y : strcasecmp(strip_tags((string)$x), strip_tags((string)$y)));
            });
        }

        $total = count($rows);
        $pages = max(1, (int)ceil($total / $state['length']));
        $page  = min($pages, max(1, $state['page']));

        return $this->result($state, array_slice($rows, ($page - 1) * $state['length'], $state['length']), $total, $page, $pages);
    }

    /** @return array search, sort, dir, length, page as the request asks for them */
    protected function state(): array
    {
        $sortable = array_keys(array_filter($this->columns, function($column) {
            return $column['sortable'];
        }));
        $sort   = $this->request->oneOf('ts', $sortable, $this->sort);
        $length = $this->request->int('tl', $this->lengths[0]);

        return [
            'search' => $this->request->string('tq'),
            'sort'   => $sort,
            'dir'    => $sort === $this->request->string('ts') ? $this->request->oneOf('td', ['asc', 'desc'], 'asc') : $this->dir,
            'length' => in_array($length, $this->lengths, true) || $length === self::ALL ? $length : $this->lengths[0],
            'page'   => $this->request->int('tp', 1),
        ];
    }

    protected function result(array $state, array $rows, int $total, int $page, int $pages): array
    {
        $columns = [];
        foreach($this->columns as $column)
        {
            $sorted    = $column['name'] === $state['sort'];
            $columns[] = $column + [
                'class'   => $column['sortable'] ? 'sorting' . ($sorted ? ' sorting_' . $state['dir'] : '') : '',
                // a click on the sorted column turns the direction round
                'nextDir' => $sorted && $state['dir'] === 'asc' ? 'desc' : 'asc',
            ];
        }

        return [
            'id'          => $this->id,
            'columns'     => $columns,
            'rows'        => $rows,
            'rowTemplate' => $this->rowTemplate,
            'actions'     => $this->actions,
            'keep'        => $this->keep,
            'toolbar'     => $this->toolbar,
            'mass'        => (string)$this->mass,
            'searchable'  => (bool)array_filter($this->columns, function($column) {
                return $column['search'] !== '';
            }),
            'search'      => $state['search'],
            'sort'        => $state['sort'],
            'dir'         => $state['dir'],
            'page'        => $page,
            'pages'       => $pages,
            'pageList'    => self::pageList($page, $pages),
            'total'       => $total,
            'length'      => $state['length'],
            'lengths'     => $this->lengths,
            'all'         => self::ALL,
        ];
    }

    /** @return array page numbers to show, 0 for a gap: 1 … 4 5 6 … 20 */
    public static function pageList(int $page, int $pages): array
    {
        if($pages <= 7)
        {
            return range(1, $pages);
        }
        $list = [1];
        $from = max(2, $page - 1);
        $to   = min($pages - 1, $page + 1);
        if($from > 2)
        {
            $list[] = 0;
        }
        for($i = $from; $i <= $to; $i++)
        {
            $list[] = $i;
        }
        if($to < $pages - 1)
        {
            $list[] = 0;
        }
        $list[] = $pages;

        return $list;
    }
}
