<?php

namespace Helret\HelloRetail\Service\Models;

class RecommendationContext
{
    public array $hierarchies;
    public string $brand;
    public array $urls;

    /**
     * @param array $hierarchies
     * @param string $brand
     * @param array $urls
     */
    public function __construct(array $hierarchies = [], string $brand = "", array $urls = [])
    {
        $this->hierarchies = $hierarchies;
        $this->brand = $brand;
        $this->urls = $urls;
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

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     */
    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param array $urls
     */
    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }
}