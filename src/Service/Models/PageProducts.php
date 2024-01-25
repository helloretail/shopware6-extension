<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageProducts
{
    public int $start;
    public int $count;
    public array $fields;
    public array $filters;

    /**
     * @param int $start
     * @param int $count
     * @param array $fields
     * @param array $filters
     */
    public function __construct(int $start, int $count, array $fields, array $filters)
    {
        $this->start = $start;
        $this->count = $count;
        $this->fields = $fields;
        $this->filters = $filters;
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
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }
}