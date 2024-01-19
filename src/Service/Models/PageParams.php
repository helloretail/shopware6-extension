<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageParams
{
    public PageFilters $filters;

    /**
     * @param PageFilters $pageFilters
     */
    public function __construct(PageFilters $pageFilters)
    {
        $this->filters = $pageFilters;
    }

    /**
     * @return PageFilters
     */
    public function getFilters(): PageFilters
    {
        return $this->filters;
    }

    /**
     * @param PageFilters $filters
     */
    public function setFilters(PageFilters $filters): void
    {
        $this->filters = $filters;
    }
}