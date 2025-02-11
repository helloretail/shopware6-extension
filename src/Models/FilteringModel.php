<?php declare(strict_types=1);

namespace Helret\HelloRetail\Models;

use Helret\HelloRetail\Enum\FilterType;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilteringModel
{
    protected AggregationResultCollection $collection;

    public function __construct(protected array $filters)
    {
        $this->collection = new AggregationResultCollection();
    }

    public function get(): iterable
    {
        yield from $this->filters;
    }

    public function parseCollection(ContainerInterface $container, Context $context): static
    {
        foreach ($this->getFormattedFilters() as $filter) {
            $aggregation = $filter->getAsAggregationResult($container, $context);
            if ($aggregation) {
                $this->collection->add($aggregation);
            }
        }

        return $this;
    }

    public function getCollection(): AggregationResultCollection
    {
        return $this->collection;
    }

    /**
     * @return Filter[]
     */
    public function getFormattedFilters(): array
    {
        $filters = [];
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

            $filters[] = new Filter(
                $type,
                $filter['name'],
                $value,
                $filter['settings']['title'] ?? null
            );
        }

        return $filters;
    }
}
