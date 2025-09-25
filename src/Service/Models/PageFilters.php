<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageFilters
{
    public function __construct(public array $filters)
    {
    }

    public function getHierarchies(): array
    {
        return $this->filters;
    }

    public function setHierarchies(array $hierarchies): void
    {
        $this->filters = $hierarchies;
    }
}
