<?php declare(strict_types=1);

namespace Helret\HelloRetail\Models;

use Helret\HelloRetail\Enum\FilterType;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\Bucket;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\StatsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Filter
{
    public function __construct(
        protected FilterType $type,
        protected string $name,
        protected mixed $value,
        protected ?string $title = null
    ) {
    }

    public function getType(): FilterType
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAsAggregationResult(ContainerInterface $container, Context $context): ?AggregationResult
    {
        if ($this->name === 'price') {
            return new StatsResult(
                name: 'price',
                min: $this->value[RangeFilter::GTE],
                max: $this->value[RangeFilter::LTE],
                avg: null,
                sum: null
            );
        } elseif ($this->type === FilterType::LIST) {
            $field = match ($this->name) {
                'extraDataList.optionIds',
                'extraDataList.propertyIds',
                'extraData.optionIds',
                'extraData.propertyIds' => [
                    'entity' => PropertyGroupOptionDefinition::ENTITY_NAME,
                    'name' => $this->name === 'extraDataList.optionIds' ?
                        'options' :
                        'properties',
                ],
                'extraData.manufacturerId' => [
                    'entity' => ProductManufacturerDefinition::ENTITY_NAME,
                    'name' => 'manufacturer',
                ],
                default => null
            };
            if (!$field) {
                return null;
            }

            /** @var EntityRepository $repository */
            $repository = $container->get(("{$field['entity']}.repository"));
            $criteria = new Criteria();
            $criteria->setIds($this->getValueSet());
            $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);

            if ($field['entity'] === PropertyGroupOptionDefinition::ENTITY_NAME) {
                $criteria->addFields(['id']);
                $idResult = $repository->searchIds($criteria, $context);

                $buckets = [];
                foreach ($this->value as $item) {
                    $query = str_replace("$this->name:", '', $item['query']);
                    if ($idResult->has($query)) {
                        $buckets[] = new Bucket(
                            key: $query,
                            count: $item['count'] ?? 0,
                            result: null
                        );
                    }
                }

                return new TermsResult(name: $field['name'], buckets: $buckets);
            }


            $entities = $repository->search($criteria, $context)->getEntities();
            return new EntityResult(
                name: $field['name'],
                entities: $entities
            );
        }

        return null;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getValueSet(): mixed
    {
        if ($this->type !== FilterType::LIST || !is_array($this->value)) {
            return $this->getValue();
        }

        return array_map(
            fn(string $query) => str_replace("$this->name:", '', $query),
            array_column($this->value, 'query')
        );
    }
}
