<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Providers;

use ModulesGarden\TSSGGSModule\Core\DataProviders\QueryDataProvider;
use WHMCS\Database\Capsule;

/**
 * Log search that also finds the entries of a service: "#71" (the service ID shown in Related Item), a domain, a product
 * or client name, or the vendor order ID. The service is kept inside the entry data, so those entries are found
 * through it, next to the usual search in the ID, message, type and date columns.
 */
class LogsQueryProvider extends QueryDataProvider
{
    const MAX_SERVICES = 200;

    protected function setupGlobalSearch(): self
    {
        if (!$this->searchFor)
        {
            return $this;
        }

        $term       = trim((string)$this->searchFor);
        $serviceOnly = strpos($term, '#') === 0;
        $plain      = trim(ltrim($term, '#'));
        $serviceIds = $plain === '' ? [] : $this->serviceIds($plain);

        $this->query->where(function($query) use ($serviceOnly, $serviceIds) {
            if (!$serviceOnly)
            {
                foreach ($this->columns as $column)
                {
                    $this->searchAsGlobal($column, $query, $this->searchFor);
                }
            }

            if ($serviceIds)
            {
                $query->orWhereRaw('data REGEXP ?', ['"service":(' . implode('|', array_map('intval', $serviceIds)) . ')[,}]']);
            }
            elseif ($serviceOnly)
            {
                $query->whereRaw('1 = 0');
            }
        });

        return $this;
    }

    /** IDs of the services a search term points at */
    protected function serviceIds(string $term): array
    {
        $like  = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        $query = Capsule::table('tblhosting as h')
            ->leftJoin('tblproducts as p', 'p.id', '=', 'h.packageid')
            ->leftJoin('tblclients as c', 'c.id', '=', 'h.userid')
            ->leftJoin('tblsslorders as o', 'o.serviceid', '=', 'h.id')
            ->where(function($q) use ($term, $like) {
                if (ctype_digit($term))
                {
                    $q->where('h.id', (int)$term)->orWhere('o.remoteid', $term);
                }
                $q->orWhere('h.domain', 'like', $like)
                    ->orWhere('p.name', 'like', $like)
                    ->orWhere('c.companyname', 'like', $like)
                    ->orWhereRaw("CONCAT(c.firstname, ' ', c.lastname) LIKE ?", [$like]);
            });

        return $query->limit(self::MAX_SERVICES)->pluck('h.id')->map(function($id) {
            return (int)$id;
        })->all();
    }
}
