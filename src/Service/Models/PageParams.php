<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageParams
{
    public function __construct(public PageFilters $filters)
    {
    }

    public function getFilters(): PageFilters
    {
        return $this->filters;
    }

    public function setFilters(PageFilters $filters): void
    {
        $this->filters = $filters;
    }
}
