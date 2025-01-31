<?php declare(strict_types=1);

namespace Helret\HelloRetail\Models;

class ProductModel
{
    public function __construct(protected array $data)
    {
    }

    public function getStart(): int
    {
        return $this->data['start'] ?? 0;
    }

    public function getTotalCount(): int
    {
        return $this->data['totalCount'] ?? 0;
    }

    public function getIds(): array
    {
        $ids = [];
        foreach ($this->getResults() as $result) {
            $ids[] = $result['extraData']['id'];
        }

        return $ids;
    }

    public function getResults(): iterable
    {
        if (!isset($this->data['results'])) {
            return;
        }

        yield from $this->data['results'];
    }

    public function getFilters(): iterable
    {
        if (!isset($this->data['filters'])) {
            return;
        }

        yield from $this->data['filters'];
    }

    public function getData(): array
    {
        return $this->data;
    }
}
