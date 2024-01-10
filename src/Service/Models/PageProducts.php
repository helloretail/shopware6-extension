<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageProducts
{
    public int $start;
    public int $count;
    public array $fields;

    /**
     * @param int $start
     * @param int $count
     * @param array $fields
     */
    public function __construct(int $start, int $count, array $fields)
    {
        $this->start = $start;
        $this->count = $count;
        $this->fields = $fields;
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
}