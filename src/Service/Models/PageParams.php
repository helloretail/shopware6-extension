<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageParams
{
    public PageFilters $pageFilters;

    /**
     * @param PageFilters $pageFilters
     */
    public function __construct(PageFilters $pageFilters)
    {
        $this->pageFilters = $pageFilters;
    }

    /**
     * @return PageFilters
     */
    public function getPageFilters(): PageFilters
    {
        return $this->pageFilters;
    }

    /**
     * @param PageFilters $pageFilters
     */
    public function setPageFilters(PageFilters $pageFilters): void
    {
        $this->pageFilters = $pageFilters;
    }
}