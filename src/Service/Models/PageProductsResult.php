<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageProductsResult
{
    protected array $productIds;

    public function __construct(
        protected int $start,
        protected int $count,
        protected int $total,
    ) {
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getProductIds(): array
    {
        return $this->productIds;
    }

    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}