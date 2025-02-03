<?php declare(strict_types=1);

namespace Helret\HelloRetail\Models;

use Generator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Helret\HelloRetail\Enum\FilterType;

class FilterModel
{
    public function __construct(protected array $filters)
    {
    }

    public function getFilters(): iterable
    {
        yield from $this->filters;
    }

    /**
     * @return Generator<Filter>
     */
    public function getFormattedFilters(): Generator
    {
        foreach ($this->filters as $filter) {
            $type = FilterType::tryFrom(strtolower($filter['settings']['type'] ?? ''));
            if (!$type) {
                continue;
            }

            $value = match ($type) {
                FilterType::RANGE => [
                    RangeFilter::GTE => $filter['min'],
                    RangeFilter::LTE => $filter['max'],
                ],
                FilterType::LIST => $filter['values'],
                default => null,
            };

            yield new Filter(
                $type,
                $filter['name'],
                $value,
                $filter['settings']['title'] ?? null
            );
        }
    }
}
