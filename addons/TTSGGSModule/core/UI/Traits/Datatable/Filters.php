<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Traits\Datatable;

use ModulesGarden\TTSGGSModule\Core\UI\Helpers\ContainerElementsConstants;
use ModulesGarden\TTSGGSModule\Core\UI\Widget\DataTable\Filters\Helpers\FilterInterface;

/**
 * Filters related functions
 * Filters Trait
 */
trait Filters
{
    protected $filters = [];
    protected $filtersPerRow = 4;

    public function addFilter(FilterInterface $filter)
    {
        $this->initFiltersContainer();

        $filter->setParentId($this->id);
        $this->addElement($filter, ContainerElementsConstants::FILTERS);

        return $this;
    }

    protected function initFiltersContainer()
    {
        if (!$this->elementContainerExists(ContainerElementsConstants::FILTERS))
        {
            $this->addNewElementsContainer(ContainerElementsConstants::FILTERS);
        }
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return int
     */
    public function getFiltersPerRow()
    {
        return $this->filtersPerRow;
    }

    public function hasFilters()
    {
        if (count($this->filters) > 0)
        {
            return true;
        }

        return false;
    }

    public function setFiltersPerRowCount($filtersCount = null)
    {
        $count = (int)$filtersCount;
        if ($count > 0)
        {
            $this->filtersPerRow = $count;
        }
    }
}
