<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class RecommendationContext
{
    public function __construct(
        public array $hierarchies = [],
        public string $brand = "",
        public array $urls = []
    ) {
    }

    public function getHierarchies(): array
    {
        return $this->hierarchies;
    }

    public function setHierarchies(array $hierarchies): void
    {
        $this->hierarchies = $hierarchies;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }
}