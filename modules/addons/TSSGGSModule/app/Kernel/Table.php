<?php

namespace ModulesGarden\TSSGGSModule\App\Kernel;

/**
 * A list of database rows with search, sorting and paging (template admin/partials/table.tpl).
 *
 *   $table = Table::make($this->request, 'queue')
 *       ->column('id', $this->t('id'), true, Table::SEARCH_INT)      // name, title, sortable, searched as
 *       ->column('domain', $this->t('domain'), true, Table::SEARCH_TEXT)
 *       ->sortBy('id', 'desc')
 *       ->rowTemplate('admin/automationQueue/row')
 *       ->run($query);                                               // an Illuminate query builder
 *
 * The result goes to the view as it is ({include file='admin/partials/table.tpl' table=$table}); the page may change
 * $table['rows'] before that. The table reloads itself through htmx: the request then carries table=<id>, and the
 * action answers with the fragment admin/partials/table.
 * Request values: tq (search), ts / td (sort column / direction), tp (page), tl (rows per page).
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

    public static function make(Request $request, string $id): self
    {
        $table          = new self();
        $table->request = $request;
        $table->id      = $id;

        return $table;
    }

    /**
     * @param string $name   column of the query (also the key of the row)
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
        $sortable = array_keys(array_filter($this->columns, function($column) {
            return $column['sortable'];
        }));
        $search = $this->request->string('tq');
        $sort   = $this->request->oneOf('ts', $sortable, $this->sort);
        $dir    = $sort === $this->request->string('ts') ? $this->request->oneOf('td', ['asc', 'desc'], 'asc') : $this->dir;
        $length = $this->request->int('tl', $this->lengths[0]);
        $length = in_array($length, $this->lengths, true) || $length === self::ALL ? $length : $this->lengths[0];

        if($search !== '')
        {
            $columns = $this->columns;
            $query->where(function($q) use ($columns, $search) {
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
            });
        }

        $total = (clone $query)->count();
        $pages = max(1, (int)ceil($total / $length));
        $page  = min($pages, max(1, $this->request->int('tp', 1)));
        if($sort !== '')
        {
            $query->orderBy($sort, $dir);
        }
        $rows = [];
        foreach($query->offset(($page - 1) * $length)->limit($length)->get() as $row)
        {
            $rows[] = (array)$row;
        }

        $columns = [];
        foreach($this->columns as $column)
        {
            $sorted    = $column['name'] === $sort;
            $columns[] = $column + [
                'class'   => $column['sortable'] ? 'sorting' . ($sorted ? ' sorting_' . $dir : '') : '',
                // a click on the sorted column turns the direction round
                'nextDir' => $sorted && $dir === 'asc' ? 'desc' : 'asc',
            ];
        }

        return [
            'id'          => $this->id,
            'columns'     => $columns,
            'rows'        => $rows,
            'rowTemplate' => $this->rowTemplate,
            'actions'     => $this->actions,
            'searchable'  => (bool)array_filter($this->columns, function($column) {
                return $column['search'] !== '';
            }),
            'search'      => $search,
            'sort'        => $sort,
            'dir'         => $dir,
            'page'        => $page,
            'pages'       => $pages,
            'pageList'    => self::pageList($page, $pages),
            'total'       => $total,
            'length'      => $length,
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
