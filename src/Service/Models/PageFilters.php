<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageFilters
{
    public array $hierarchies = [];

    /**
     * @param array $hierarchies
     */
    public function __construct(array $hierarchies)
    {
        $this->hierarchies = $hierarchies;
    }

    /**
     * @return array
     */
    public function getHierarchies(): array
    {
        return $this->hierarchies;
    }

    /**
     * @param array $hierarchies
     */
    public function setHierarchies(array $hierarchies): void
    {
        $this->hierarchies = $hierarchies;
    }
}