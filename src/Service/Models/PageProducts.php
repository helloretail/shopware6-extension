<?php declare(strict_types=1);

namespace Helret\HelloRetail\Service\Models;

class PageProducts
{
    public function __construct(
        public int $start,
        public int $count,
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

}