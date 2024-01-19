<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageProductsResult
{
    protected int $start;
    protected int $count;
    protected int $total;
    protected array $productIds;

    /**
     * @param int $start
     * @param int $count
     * @param int $total
     * @param array $result
     */
    public function __construct(int $start, int $count, int $total, array $result)
    {
        $this->start = $start;
        $this->count = $count;
        $this->total = $total;
        $this->productIds = $result;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @param array $productIds
     */
    public function setProductIds(array $productIds): void
    {
        $this->productIds = $productIds;
    }
}