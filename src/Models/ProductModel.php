<?php declare(strict_types=1);

namespace Helret\HelloRetail\Models;

use Generator;

class ProductModel
{
    protected ?FilterModel $filters = null;

    public function __construct(protected array $data)
    {
        if (isset($this->data['filters']) && is_array($this->data['filters'])) {
            $this->filters = new FilterModel($this->data['filters']);
        }
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

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return Generator<string, Filter>
     */
    public function getFilters(): Generator
    {
        if (!$this->filters) {
            return;
        }

        foreach ($this->filters->getFormattedFilters() as $filter) {
            yield $filter->getName() => $filter;
        }
    }

    public function hasData(): bool
    {
        if ($this->getIds()) {
            return true;
        }

        if ($this->filters?->getFormattedFilters()->current()) {
            return true;
        }

        return false;
    }
}
